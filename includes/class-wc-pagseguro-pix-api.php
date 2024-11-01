<?php
/**
 * Handle API PIX.
 *
 * @package virtuaria.
 */

defined( 'ABSPATH' ) || exit;
use GuzzleHttp\Client;

/**
 * Definition.
 */
class WC_PagSeguro_PIX_API {
	/**
	 * Instance from gateway.
	 *
	 * @var WC_Pagseguro_PIX_Gateway
	 */
	private $gateway;

	/**
	 * Endpoint to API.
	 *
	 * @var string
	 */
	private $endpoint;

	/**
	 * Initialize class.
	 *
	 * @param WC_Pagseguro_PIX_Gateway $gateway the instance from gateway.
	 */
	public function __construct( $gateway ) {
		$this->gateway = $gateway;

		if ( 'sandbox' === $this->gateway->environment ) {
			$this->endpoint = 'https://secure.sandbox.api.pagseguro.com/instant-payments/';
		} else {
			$this->endpoint = 'https://secure.api.pagseguro.com/instant-payments/';
		}

		$this->tag      = 'pagseguro_pix';
		$this->debug_on = 'yes' === $this->gateway->get_option( 'debug' );

		add_action( 'admin_init', array( $this, 'simulate_payment' ) );
	}

	/**
	 * New payment.
	 *
	 * @param wc_order $order        the payment data.
	 * @param boolean  $extra_charge is extra charge.
	 */
	public function new_payment( $order, $extra_charge = false ) {
		$txid = $this->gateway->get_prefix_transaction( $extra_charge ? $order['order_id'] : false ) . $order['order_id'];
		$data = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->gateway->token,
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'body'    => array(
				'txid'               => $txid,
				'calendario'         => array(
					'expiracao' => $this->gateway->validate,
				),
				'devedor'            => array(
					'cpf'  => $order['cpf'],
					'nome' => $order['nome'],
				),
				'valor'              => array(
					'original' => $order['valor'],
				),
				'chave'              => $this->gateway->key_pix,
				'solicitacaoPagador' => get_bloginfo( 'name' ),
			),
			'method'  => 'PUT',
			'timeout' => 120,
		);

		$this->gateway->log->add( $this->tag, 'Request: ' . wp_json_encode( $data ), WC_Log_Levels::INFO );

		$request = $this->do_request(
			$this->endpoint . 'cob/' . $txid,
			$data
		);

		if ( ! $request || 201 !== $request['code'] ) {
			if ( $this->debug_on ) {
				$this->gateway->log->add( $this->tag, 'Erro ao criar cobrança: ' . $request['body'], WC_Log_Levels::ERROR );
			}
			return false;
		}

		if ( $this->debug_on ) {
			$this->gateway->log->add( $this->tag, 'Cobrança criada com sucesso: ' . $request['body'], WC_Log_Levels::INFO );
		}

		if ( ! $extra_charge ) {
			$order = wc_get_order( $order['order_id'] );
			update_post_meta( $order->get_id(), '_txid', $txid );
			$order->set_transaction_id( $txid );
			$order->save();
		}

		return json_decode( $request['body'], true );
	}

	/**
	 * Do refund request.
	 *
	 * @param int    $order_id the order id.
	 * @param string $amount   the amount.
	 */
	public function refund_order( $order_id, $amount ) {
		$id_payment = get_post_meta( $order_id, '_endToEndId', true );
		$order      = wc_get_order( $order_id );

		if ( $order && $id_payment ) {
			$refund_id = get_option( '_unique_refund_id' );

			if ( $refund_id ) {
				++$refund_id;
			} else {
				$refund_id = 1;
			}

			$request = $this->do_request(
				$this->endpoint . 'pix/' . $id_payment . '/devolucao/' . $refund_id,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $this->gateway->token,
						'Content-Type'  => 'application/json',
						'Accept'        => 'application/json',
					),
					'body'    => array(
						'valor' => $amount,
					),
					'method'  => 'PUT',
				)
			);

			$refund = json_decode( $request['body'], true );
			if ( in_array( $request['code'], array( 200, 201 ), true ) && 'EM_PROCESSAMENTO' === $refund['status'] ) {
				if ( $this->debug_on ) {
					$this->gateway->log->add( $this->tag, 'Reembolso de ' . $amount . ' bem sucedido ' . $request['body'], WC_Log_Levels::INFO );
				}
				update_option( '_unique_refund_id', $refund_id );
				return true;
			}

			if ( $this->debug_on ) {
				$this->gateway->log->add( $this->tag, 'Falha ao reembolsar: ' . $request['body'], WC_Log_Levels::ERROR );
				$this->gateway->log->add( $this->tag, $this->endpoint . 'pix/' . $id_payment . '/devolucao/' . $refund_id, WC_Log_Levels::ERROR );
			}

			$order->add_order_note( 'Não foi possível reembolsar este pedido. Tente novamente mais tarde.' );
			$request['body'] = json_decode( $request['body'], true );
			if ( isset( $request['body']['detail'] ) ) {
				$order->add_order_note( $request['body']['detail'] );
			}
		}

		return false;
	}

	/**
	 * Get data about transaction.
	 *
	 * @param int $order_id the order id.
	 */
	public function get_payment_info( $order_id ) {
		$request = $this->do_request(
			$this->endpoint . 'cob/' . $order_id . '?revisao=0',
			array(
				'headers' => array(
					'Accept' => 'application/json',
				),
				'method'  => 'GET',
			)
		);

		if ( 200 !== $request['code'] ) {
			if ( $this->debug_on ) {
				$this->gateway->log->add( $this->tag, 'Falha ao consultar cobrança', WC_Log_Levels::ERROR );
			}
			return false;
		}

		if ( $this->debug_on ) {
			$this->gateway->log->add( $this->tag, 'Resultado da consulta pela cobrança: ' . $request['body'], WC_Log_Levels::INFO );
		}

		return json_decode( $request['body'] );
	}

	/**
	 * Make request.
	 *
	 * @param string $url  the endpoint.
	 * @param array  $data the data.
	 */
	private function do_request( $url, $data ) {
		$cert_dir = PAGSEGURO_PIX_DIR . 'auth/' . get_current_blog_id() . '/';
		require_once PAGSEGURO_PIX_DIR . 'vendor/autoload.php';

		$client = new Client();

		$data['cert']    = $cert_dir . 'cert.pem';
		$data['ssl_key'] = $cert_dir . 'auth.key';
		$data['body']    = wp_json_encode( $data['body'] );
		try {
			$request = $client->request(
				$data['method'],
				$url,
				$data
			);
		} catch ( GuzzleHttp\Exception\ClientException $e ) {
			if ( $this->debug_on ) {
				$this->gateway->log->add(
					$this->tag,
					'Falha na requisição ' . $url . ': ' . $e->getResponse()->getBody(),
					WC_Log_Levels::ERROR
				);
			}
			return false;
		}

		$result = array(
			'code' => $request->getStatusCode(),
			'body' => $request->getBody()->getContents(),
		);

		return $result;
	}

	/**
	 * Simulate PIX payment.
	 */
	public function simulate_payment() {
		if ( isset( $_GET['txid'] ) && 'sandbox' === $this->gateway->environment ) {
			$txid   = sanitize_text_field( wp_unslash( $_GET['txid'] ) );
			$result = $this->do_request(
				'https://sandbox.api.pagseguro.com/pix/pay/' . $txid,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $this->gateway->payment_token,
						'Content-Type'  => 'application/json',
						'Accept'        => 'application/json',
					),
					'body'    => array(
						'status' => 'PAID',
						'tx_id'  => $txid,
					),
					'method'  => 'POST',
				)
			);

			if ( $this->debug_on ) {
				if ( 200 === $result['code'] ) {
					$this->gateway->log->add( $this->tag, 'Pagamento efetuado', WC_Log_Levels::INFO );
				} else {
					$this->gateway->log->add( $this->tag, 'Simulação de pagamento: ' . $result['body'], WC_Log_Levels::ERROR );
				}
			}
		}
	}

	/**
	 * Create webhook to notify about transactions change.
	 */
	public function create_webhook() {
		$data = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->gateway->token,
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
			),
			'body'    => array(
				'webhookUrl' => home_url( 'wp-json/api/pagseguro_pix' ),
			),
			'method'  => 'PUT',
		);

		$result = $this->do_request(
			$this->endpoint . 'webhook/' . $this->gateway->key_pix,
			$data
		);

		if ( $this->debug_on ) {
			$this->gateway->log->add( $this->tag, 'Request Webhook: ' . wp_json_encode( $data ), WC_Log_Levels::INFO );
		}

		if ( $this->debug_on ) {
			if ( 200 === $result['code'] ) {
				$this->gateway->log->add( $this->tag, 'Webhook criada', WC_Log_Levels::INFO );
			} else {
				$this->gateway->log->add( $this->tag, 'Response Webhook: ' . $result['body'], WC_Log_Levels::INFO );
			}
		}

		if ( 200 === $result['code'] ) {
			update_option( 'pagseguro_pix_webhook_create_' . sanitize_key( $this->gateway->key_pix ), true );
		}
	}

	/**
	 * Process ipn request.
	 *
	 * @param array $request the request.
	 */
	public function process_ipn_request( $request ) {
		if ( $this->debug_on ) {
			$this->gateway->log->add( $this->tag, 'IPN request: ' . wp_json_encode( $request ), WC_Log_Levels::INFO );
		}
	}
}
