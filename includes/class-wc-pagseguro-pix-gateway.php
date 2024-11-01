<?php
/**
 * Gateway class
 *
 * @package WooCommerce_PagSeguro/Classes/Gateway
 * @version 2.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gateway.
 */
class WC_PagSeguro_PIX_Gateway extends WC_Payment_Gateway {
	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'pagseguro_pix';
		$this->icon               = apply_filters( 'woocommerce_pagseguro__pix_icon', plugins_url( 'public/images/pague-com-pix.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields         = false;
		$this->method_title       = __( 'PagSeguro Pix', 'virtuaria-pagseguro-pix' );
		$this->method_description = __( 'Pague com PIX.', 'virtuaria-pagseguro-pix' );

		$this->supports = array( 'products', 'refunds' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title         = $this->get_option( 'title' );
		$this->description   = $this->get_option( 'description' );
		$this->environment   = $this->get_option( 'environment' );
		$this->token         = $this->get_option( 'token' );
		$this->key_pix       = $this->get_option( 'key_pix' );
		$this->mcc           = $this->get_option( 'mcc' );
		$this->receive       = $this->get_option( 'receive' );
		$this->city          = $this->get_option( 'city' );
		$this->validate      = $this->get_option( 'validate' );
		$this->payment_token = $this->get_option( 'payment_token' );
		$this->debug         = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' === $this->debug ) {
			if ( function_exists( 'wc_get_logger' ) ) {
				$this->log = wc_get_logger();
			} else {
				$this->log = new WC_Logger();
			}
		}

		$this->tag = 'pagseguro_pix';

		// Set the API.
		$this->api = new WC_Pagseguro_PIX_API( $this );

		// // Main actions.
		// add_action( 'woocommerce_api_wc_pagseguro_pix_gateway', array( $this, 'ipn_handler' ) );
		// add_action( 'valid_pagseguro_pix_ipn_request', array( $this, 'update_order_status' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'update_ssl_files_cert' ), 20 );

		// // Transparent checkout actions.
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'checkout_scripts' ) );

		add_action( 'admin_init', array( $this, 'register_webhook' ) );
		add_action( 'rest_api_init', array( $this, 'endpoint_update_pix' ) );
		add_filter( 'virtuaria_api_not_authenticated_access', array( $this, 'allow_api_pagseguro_pix' ), 10, 2 );

		add_action( 'pagseguro_pix_update_product_status', array( $this, 'check_order_paid' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_additional_charge_box' ), 10, 2 );
		add_action( 'save_post_shop_order', array( $this, 'do_additional_charge' ) );
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$available = 'yes' === $this->get_option( 'enabled' );

		if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
			$available = false;
		}

		return $available;
	}

	/**
	 * Checkout scripts.
	 */
	public function checkout_scripts() {
		if ( is_checkout() && $this->is_available() && ! get_query_var( 'order-received' ) ) {
			wp_enqueue_script(
				'pagseguro-pix',
				PAGSEGURO_PIX_URL . 'public/js/pix.js',
				array( 'jquery' ),
				filemtime( PAGSEGURO_PIX_DIR . 'public/js/pix.js' ),
				true
			);

			wp_localize_script(
				'pagseguro-pix',
				'ajaxurl',
				admin_url( 'admin-ajax.php' )
			);
		}

		if ( is_order_received_page() ) {
			wp_enqueue_style(
				'thank-you-pix',
				PAGSEGURO_PIX_URL . 'public/css/thankyou.css',
				array(),
				filemtime( PAGSEGURO_PIX_DIR . 'public/css/thankyou.css' ),
			);

			wp_enqueue_script(
				'thank-you-pix',
				PAGSEGURO_PIX_URL . 'public/js/thankyou.js',
				array( 'jquery' ),
				filemtime( PAGSEGURO_PIX_DIR . 'public/js/thankyou.js' ),
				true
			);
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Habilitar', 'virtuaria-pagseguro-pix' ),
				'type'    => 'checkbox',
				'label'   => __( 'Habilita o método de Pagamento', 'virtuaria-pagseguro-pix' ),
				'default' => 'yes',
			),
			'title'       => array(
				'title'       => __( 'Título', 'virtuaria-pagseguro-pix' ),
				'type'        => 'text',
				'description' => __( 'Isto controla o título exibido ao usuário durante o checkout.', 'virtuaria-pagseguro-pix' ),
				'desc_tip'    => true,
				'default'     => __( 'PagSeguro PIX', 'virtuaria-pagseguro-pix' ),
			),
			'description' => array(
				'title'       => __( 'Descrição', 'virtuaria-pagseguro-pix' ),
				'type'        => 'textarea',
				'description' => __( 'Controla a descrição exibida ao usuário durante o checkout.', 'virtuaria-pagseguro-pix' ),
				'default'     => __( 'Pague com PagSeguro PIX.', 'virtuaria-pagseguro-pix' ),
			),
			'integration' => array(
				'title'       => __( 'Integração', 'virtuaria-pagseguro-pix' ),
				'type'        => 'title',
				'description' => '',
			),
			'environment' => array(
				'title'       => __( 'Ambiente', 'virtuaria-petrox' ),
				'type'        => 'select',
				'description' => __( 'Selecione Sanbox para testes ou Produção para vendas reais.', 'virtuaria-pagseguro-pix' ),
				'options'     => array(
					'sandbox'    => 'Sandbox',
					'production' => 'Produção',
				),
			),
			'token'       => array(
				'title'       => __( 'Token', 'virtuaria-pagseguro-pix' ),
				'type'        => 'text',
				'description' => __( 'Informe seu token do Pagseguro. Isto é necessário para o processamento do pagamento.', 'virtuaria-pagseguro-pix' ),
				'default'     => '',
			),
			'key_pix'     => array(
				'title'       => __( 'Chave PIX', 'virtuaria-pagseguro-pix' ),
				'type'        => 'text',
				'description' => __( 'Chave PIX cadastrada no painel da conta no pagseguro.', 'virtuaria-pagseguro-pix' ),
				'default'     => '',
			),
			'mcc'         => array(
				'title'       => __( 'MCC', 'virtuaria-pagseguro-pix' ),
				'type'        => 'text',
				'description' => sprintf(
					/* translators: %s: link saiba mais sobre MCC. */
					__( 'Código da Categoria do Comerciante (Merchant Category Code). <strong>4 dígitos</strong>. Mais informações <a href="%s" target="_blank">aqui</a>.', 'virtuaria-pagseguro-pix' ),
					'https://pt.wikipedia.org/wiki/Merchant_category_code'
				),
				'default'     => '8999',
			),
			'receive'     => array(
				'title'       => __( 'Nome do Recebedor', 'virtuaria-pagseguro-pix' ),
				'type'        => 'text',
				'description' => __( 'Nome do beneficiário da conta PIX.', 'virtuaria-pagseguro-pix' ),
				'default'     => '',
			),
			'city'        => array(
				'title'       => __( 'Cidade do Recebedor', 'virtuaria-pagseguro-pix' ),
				'type'        => 'text',
				'description' => __( 'Cidade do beneficiário da conta PIX.', 'virtuaria-pagseguro-pix' ),
				'default'     => '',
			),
			'validate'    => array(
				'title'       => __( 'Validade da Cobrança', 'virtuaria-pagseguro-pix' ),
				'type'        => 'select',
				'description' => __( 'Define o limite de tempo para aceitar pagamentos com PIX.', 'virtuaria-pagseguro-pix' ),
				'options'     => array(
					'1800'  => '30 Minutos',
					'3600'  => '1 hora',
					'5400'  => '1 hora e 30 minutos',
					'7200'  => '2 horas',
					'9000'  => '2 horas e 30 minutos',
					'10800' => '3 horas',
				),
				'default'     => '1800',
			),
		);

		if ( current_user_can( 'install_themes' ) ) {
			$this->form_fields['ssl_key']  = array(
				'title'       => __( 'Chave do Certificado (KEY File)', 'virtuaria-pagseguro-pix' ),
				'type'        => 'textarea',
				'description' => __( 'Certificado fornecido pelo pagseguro <code>(.key)</code>.', 'virtuaria-pagseguro-pix' ),
				'default'     => '',
			);
			$this->form_fields['ssl_cert'] = array(
				'title'       => __( 'Segredo do Certificado (PEM File)', 'virtuaria-pagseguro-pix' ),
				'type'        => 'textarea',
				'description' => __( 'Conteúdo do certificado fornecido pelo pagseguro <code>(.pem)</code>.', 'virtuaria-pagseguro-pix' ),
				'default'     => '',
			);

			if ( empty( $this->get_option( 'environment' ) ) || 'sandbox' === $this->get_option( 'environment' ) ) {
				$this->form_fields['payment_token'] = array(
					'title'       => __( 'Token para simular pagamento', 'virtuaria-pagseguro-pix' ),
					'type'        => 'text',
					'description' => sprintf(
						/* translators: %1$s: link da documentação sobre o token. %2$s: link para simular transação. */
						__( 'Informe seu token do Pagseguro Sandbox (ambiente de testes). Você pode  obtê-lo no painel da sua conta no PagSeguro através deste <a href="%1$s" target="_blank">link</a> e usá-lo somente para testes. Este token será usado somente para simular pagamento via sandbox. Para simular o pagamento de uma compra via Pix, use este <a href="%2$s" target="_blank">link.</a>', 'virtuaria-pagseguro-pix' ),
						'https://dev.pagseguro.uol.com.br/reference/get-access-token',
						admin_url( 'edit.php?post_type=shop_order' ) . '&txid=IDDATRANSACAO'
					),
					'default'     => '',
				);
			}
			$this->form_fields['testing'] = array(
				'title'       => __( 'Testes', 'virtuaria-pagseguro-pix' ),
				'type'        => 'title',
				'description' => '',
			);
			$this->form_fields['debug']   = array(
				'title'       => __( 'Debug Log', 'virtuaria-pagseguro-pix' ),
				'type'        => 'checkbox',
				'label'       => __( 'Habilitar registro de log', 'virtuaria-pagseguro-pix' ),
				'default'     => 'no',
				/* translators: %s: log page link */
				'description' => __( 'Registra eventos de comunição com a API e erros', 'virtuaria-pagseguro-pix' ),
			);
		}

		$this->form_fields['virtuaria'] = array(
			'title'       => __( '<a href="https://virtuaria.com.br" target="blank">Tecnologia Virtuaria</a>', 'virtuaria-pagseguro-pix' ),
			'type'        => 'title',
			'description' => '',
		);
	}

	/**
	 * Send email notification.
	 *
	 * @param string $recipient Email recipient.
	 * @param string $subject   Email subject.
	 * @param string $title     Email title.
	 * @param string $message   Email message.
	 */
	protected function send_email( $recipient, $subject, $title, $message ) {
		$mailer = WC()->mailer();

		$mailer->send( $recipient, $subject, $mailer->wrap_message( $title, $message ) );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$charged = $this->api->new_payment( $this->get_formated_order( $order ) );

		if ( $charged ) {
			$qr_code = $this->generate_qr_code( $charged['location'] );
			update_post_meta( $order_id, '_qrcode', $qr_code );

			$order->update_status( 'on-hold', __( 'Pagamento com Pagseguro PIX', 'virtuaria-pagseguro-pix' ) );

			wc_reduce_stock_levels( $order_id );
			// Remove cart.
			WC()->cart->empty_cart();

			$args = array( $order_id );
			if ( ! wp_next_scheduled( 'pagseguro_pix_update_product_status', $args ) ) {
				wp_schedule_single_event(
					strtotime( 'now' ) + $this->get_option( 'validate' ) + 1800,
					'pagseguro_pix_update_product_status',
					$args
				);
			}

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		} else {
			wc_add_notice( 'Não foi possível processar a sua compra. Por favor, tente novamente mais tarde.', 'error' );

			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
	}

	/**
	 * Endpoint to receive notify about pix.
	 */
	public function endpoint_update_pix() {
		register_rest_route(
			'api',
			'/pagseguro_pix',
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'ipn_handler' ),
			)
		);
	}

	/**
	 * IPN handler.
	 *
	 * @param WP_REST_Request $request the request.
	 */
	public function ipn_handler( $request ) {
		$params = $request->get_params()[0];
		if ( $params ) {
			$this->log->add( $this->tag, 'IPN request...', WC_Log_Levels::INFO );

			if ( isset( $params['endToEndId'] ) ) {
				$this->log->add( $this->tag, wp_json_encode( $params ), WC_Log_Levels::INFO );
				$this->update_order_status( $params );
				$this->log->add( $this->tag, 'Valid IPN request', WC_Log_Levels::INFO );
				header( 'HTTP/1.1 200 OK' );
				exit();
			} else {
				$this->log->add( $this->tag, 'IPN request REJECT', WC_Log_Levels::ERROR );
				$this->log->add( $this->tag, $request->get_body(), WC_Log_Levels::ERROR );
				wp_die( esc_html__( 'PagSeguro Request Unauthorized', 'virtuaria-pagseguro-pix' ), esc_html__( 'PagSeguro Request Unauthorized', 'virtuaria-pagseguro-pix' ), array( 'response' => 401 ) );
			}
		}
	}

	/**
	 * Allow pagseguro pix api requet.
	 *
	 * @param boolean $allow receive or not request.
	 * @param string  $route the route.
	 */
	public function allow_api_pagseguro_pix( $allow, $route ) {
		if ( false !== strpos( $route, '/api/pagseguro_pix' ) ) {
			$allow = true;
		}
		return $allow;
	}

	/**
	 * Update order status.
	 *
	 * @param array $posted PagSeguro post data.
	 */
	private function update_order_status( $posted ) {
		if ( isset( $posted['txId'] ) ) {
			$id    = (int) str_replace( $this->get_prefix_transaction(), '', $posted['txId'] );
			$order = wc_get_order( $id );

			// Check if order exists.
			if ( ! $order ) {
				$txid = explode( 'A', $posted['txId'] );
				if ( function_exists( 'get_current_blog_id' ) ) {
					$id = $txid[3];
				} else {
					$id = $txid[2];
				}

				$order = wc_get_order( $id );
				if ( ! $order ) {
					$this->log->add( $this->tag, 'pedido não encontrado', WC_Log_Levels::ERROR );
					$this->log->add( $this->tag, $id, WC_Log_Levels::ERROR );
					return;
				}
			}

			$payment_id = get_post_meta( $id, '_endToEndId', true );

			if ( $posted['devolucoes'] ) {
				if ( $posted['valor'] === $order->get_total_refunded() && 'DEVOLVIDO' === $posted['status'] ) {
					$this->log->add( $this->tag, 'Pedido ' . $order->get_order_number() . ' mudou para o status reembolsado.', WC_Log_Levels::INFO );
					$order->update_status( 'refunded', __( 'PagSeguro: Pagamento reembolsado.', 'virtuaria-pagseguro-pix' ) );
					$this->send_email(
						$order->get_billing_email(),
						/* translators: %s: order number */
						sprintf( __( 'Pagamento para o pedido %s reembolsado', 'virtuaria-pagseguro-pix' ), $order->get_order_number() ),
						__( 'Pagamento reembolsado', 'virtuaria-pagseguro-pix' ),
						/* translators: %s: order number */
						sprintf( __( 'Order %s has been marked as refunded by PagSeguro.', 'virtuaria-pagseguro-pix' ), $order->get_order_number() )
					);

					if ( function_exists( 'wc_increase_stock_levels' ) ) {
						wc_increase_stock_levels( $order->get_id() );
					}
					$order->save();
				}
			} elseif ( ! $payment_id || $posted['txId'] !== $order->get_transaction_id() ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( $this->tag, 'PagSeguro: pagamento para o pedido ' . $order->get_order_number() . ' completo', WC_Log_Levels::INFO );
					$this->log->add( $this->tag, 'PagSeguro: pedido ' . $order->get_order_number() . ' mudou para o status processando.', WC_Log_Levels::INFO );
				}
				$order->add_order_note( 'Pagseguro PIX: Cobrança de R$ ' . $posted['valor'] . ' recebida' );
				if ( $txid && $posted['txId'] !== $order->get_transaction_id() ) {
					if ( function_exists( 'get_current_blog_id' ) ) {
						update_post_meta( $order->get_id(), '_endToEndId_charge_' . $txid[0], $posted['endToEndId'] );
					} else {
						update_post_meta( $order->get_id(), '_endToEndId_charge_' . $txid[1], $posted['endToEndId'] );
					}
				} else {
					update_post_meta( $order->get_id(), '_endToEndId', $posted['endToEndId'] );
				}
				$order->update_status( 'processing', __( 'PagSeguro: Pagamento aprovado.', 'virtuaria-pagseguro-pix' ) );
			}
		}
	}

	/**
	 * Thank You page message.
	 *
	 * @param int $order_id Order ID.
	 */
	public function thankyou_page( $order_id ) {
		$qr_code = get_post_meta( $order_id, '_qrcode', true );

		if ( $qr_code ) {
			$validate = $this->formated_validate();
			require_once PAGSEGURO_PIX_DIR . 'templates/payment-instructions.php';
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param  WC_Order $order         Order object.
	 * @param  bool     $sent_to_admin Send to admin.
	 * @param  bool     $plain_text    Plain text or HTML.
	 * @return string
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $sent_to_admin || 'on-hold' !== $order->get_status() || $this->id !== $order->get_payment_method() ) {
			return;
		}

		$qr_code = get_post_meta( $order->get_id(), '_qrcode', true );

		if ( $qr_code ) {
			$validate = $this->formated_validate();
			require_once PAGSEGURO_PIX_DIR . 'templates/payment-instructions.php';
		}
	}

	/**
	 * Process refund order.
	 *
	 * @param  int    $order_id Order ID.
	 * @param  float  $amount Refund amount.
	 * @param  string $reason Refund reason.
	 * @return boolean True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );

		if ( $amount && 'processing' === $order->get_status() ) {
			if ( $this->api->refund_order( $order_id, $amount ) ) {
				$order->add_order_note( 'PagSeguro PIX: Reembolso de R$' . $amount . ' bem sucedido.', 0, true );
				return true;
			}
		} else {
			$order->add_order_note( 'PagSeguro PIX: Não foi possível reembolsar R$' . $amount . '. Verifique o status da transação e o valor a ser reembolsado e tente novamente.', 0, true );
		}

		return false;
	}

	/**
	 * Generate qr code format.
	 *
	 * @param string $location the payment location.
	 */
	private function generate_qr_code( $location ) {
		$qr_code  = '000201';
		$gui      = '0014br.gov.bcb.pix25' . strlen( $location ) . $location;
		$qr_code .= '26' . strlen( $gui ) . $gui;
		$qr_code .= '5204' . $this->mcc;
		$qr_code .= '5303986';
		$qr_code .= '5802BR';
		$qr_code .= '59' . str_pad( strlen( $this->receive ), 2, '0', STR_PAD_LEFT ) . $this->receive;
		$qr_code .= '60' . str_pad( strlen( $this->city ), 2, '0', STR_PAD_LEFT ) . $this->city;
		$qr_code .= '62070503***';
		$qr_code .= $this->get_CRC16( $qr_code );

		return $qr_code;
	}

	/**
	 * Format number if less two digits.
	 *
	 * @param int $number the number.
	 */
	private function zero_left( $number ) {
		if ( $number < 2 ) {
			$number = '0' . $number;
		}
		return $number;
	}

	/**
	 * Método responsável por calcular o valor da hash de validação do código pix
	 *
	 * @param string $payload the payload.
	 * @return string
	 */
	private function get_CRC16( $payload ) {
		// ADICIONA DADOS GERAIS NO PAYLOAD.
		$payload .= '6304';

		// DADOS DEFINIDOS PELO BACEN.
		$polinomio = 0x1021;
		$resultado = 0xFFFF;

		// CHECKSUM.
		$length = strlen( $payload );
		if ( $length > 0 ) {
			for ( $offset = 0; $offset < $length; $offset++ ) {
				$resultado ^= ( ord( $payload[ $offset ] ) << 8 );
				for ( $bitwise = 0; $bitwise < 8; $bitwise++ ) {
					if ( ( $resultado <<= 1 ) & 0x10000 ) {
						$resultado ^= $polinomio;
					}
					$resultado &= 0xFFFF;
				}
			}
		}

		// RETORNA CÓDIGO CRC16 DE 4 CARACTERES.
		return '6304' . strtoupper( dechex( $resultado ) );
	}

	/**
	 * Create webhook to notification about PIX.
	 */
	public function register_webhook() {
		if ( $this->key_pix && ! get_option( 'pagseguro_pix_webhook_create_' . sanitize_key( $this->key_pix ) ) ) {
			$this->api->create_webhook();
		}
	}

	/**
	 * Update content from SSL file certs to integration.
	 */
	public function update_ssl_files_cert() {
		$key  = $this->get_option( 'ssl_key' );
		$cert = $this->get_option( 'ssl_cert' );

		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
		$blog_id = get_current_blog_id();
		$dir     = PAGSEGURO_PIX_DIR . 'auth/' . $blog_id . '/';

		mkdir( PAGSEGURO_PIX_DIR . 'auth/', 0755 );
		mkdir( $dir, 0755 );
		file_put_contents( $dir . 'auth.key', $key );
		file_put_contents( $dir . 'cert.pem', $cert );
	}

	/**
	 * Check status from order. If unpaid cancel order.
	 *
	 * @param int $order_id the args.
	 */
	public function check_order_paid( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order && ! get_post_meta( $order_id, '_endToEndId', true ) ) {
			$order->add_order_note( 'Pagseguro PIX: o limite de tempo para pagamento deste pedido expirou.' );
			$order->update_status( 'cancelled' );
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->tag, 'Pedido #' . $order->get_order_number() . ' mudou para o status cancelado.', WC_Log_Levels::INFO );
			}
		}
	}

	/**
	 * Get the transaction prefix from order.
	 *
	 * @param int $order_id the order id.
	 */
	public function get_prefix_transaction( $order_id = false ) {
		$prefix = '';
		if ( function_exists( 'get_current_blog_id' ) ) {
			if ( ! $order_id ) {
				$prefix = get_current_blog_id() . str_repeat( '00', 11 );
			} else {
				$charges = get_post_meta( $order_id, 'pix_additional_charges', true );
				if ( ! $charges ) {
					$charges = 1;
				} else {
					++$charges;
				}

				$prefix = get_current_blog_id() . 'A' . $charges . 'A' . str_repeat( '00', 9 ) . 'A';
				update_post_meta( $order_id, 'pix_additional_charges', $charges );

			}
		} else {
			if ( ! $order_id ) {
				$prefix = str_repeat( '00', 12 );
			} else {
				$charges = get_post_meta( $order_id, 'pix_additional_charges', true );
				if ( ! $charges ) {
					$charges = 1;
				} else {
					++$charges;
				}

				$prefix = $charges . 'A' . str_repeat( '00', 10 ) . 'A';
				update_post_meta( $order_id, 'pix_additional_charges', $charges );
			}
		}

		return $prefix;
	}

	/**
	 * Add new charge box.
	 *
	 * @param string  $post_type the post type.
	 * @param wp_post $post      the post.
	 */
	public function add_additional_charge_box( $post_type, $post ) {
		if ( 'shop_order' === $post_type ) {
			$order = wc_get_order( $post->ID );
			if ( ! $order
				|| ! in_array( $order->get_status(), array( 'processing', 'on-hold' ), true )
				|| 'pagseguro_pix' !== $order->get_payment_method() ) {
				return;
			}

			$title = 'processing' === $order->get_status() ? 'Cobrança Adicional' : 'Nova Cobrança';
			add_meta_box(
				'additional-charge',
				$title,
				array( $this, 'additional_charge_content_box' ),
				'shop_order',
				'side',
				'high'
			);
		}
	}

	/**
	 * Content from additional charge box.
	 */
	public function additional_charge_content_box() {
		?>
		<label for="pix-chage">Informe o valor a ser cobrado (R$):</label>
		<input type="number" style="width:calc(100% - 42px)" name="pix_charge_value" id="pix-charge" step="0.01" min="0.10"/>
		<button class="pix-additional-charge" style="padding: 3px 4px;vertical-align:middle;color:green;cursor:pointer">
			<span class="dashicons dashicons-money-alt"></span>
		</button>
		<label for="reason-charge" style="margin-top: 5px;">Motivo:</label>
		<input type="text" name="charge_reason" id="reason-charge" style="display:block;max-width:214px;">
		<?php
		wp_nonce_field( 'pix-new-charge', 'pix_charge' );
	}

	/**
	 * Make new charge and send QR code by mail to customer.
	 *
	 * @param int $order_id the order id.
	 */
	public function do_additional_charge( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order
		&& isset( $_POST['pix_charge'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pix_charge'] ) ), 'pix-new-charge' ) ) {
			if ( isset( $_POST['pix_charge_value'] ) && floatval( $_POST['pix_charge_value'] ) > 0 ) {
				$data          = $this->get_formated_order( $order );
				$data['valor'] = number_format( floatval( sanitize_text_field( wp_unslash( $_POST['pix_charge_value'] ) ) ), 2, '.', '' );

				$charged = $this->api->new_payment( $data, true );

				if ( $charged ) {
					$qr_code = $this->generate_qr_code( $charged['location'] );
					if ( $qr_code ) {
						$validate = $this->formated_validate();
						ob_start();
						echo '<p>Olá, ' . esc_html( $order->get_billing_first_name() ) . '.</p>';
						echo '<p><strong>Uma nova cobrança está disponível para seu pedido:</strong></p>';
						remove_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
						wc_get_template(
							'emails/email-order-details.php',
							array(
								'order'         => $order,
								'sent_to_admin' => false,
								'plain_text'    => false,
								'email'         => '',
							)
						);
						add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
						echo '<p style="color:green"><strong style="display:block;">Valor da Nova Cobrança: R$ ' . number_format( $charged['valor']['original'], 2, ',', '.' ) . '.</strong>';
						if ( isset( $_POST['charge_reason'] ) && ! empty( $_POST['charge_reason'] ) ) {
							$reason = 'Motivo: ' . esc_html( sanitize_text_field( wp_unslash( $_POST['charge_reason'] ) ) ) . '.';
						}
						echo wp_kses_post( $reason ) . '</p>';
						require_once PAGSEGURO_PIX_DIR . 'templates/payment-instructions.php';
						$message = ob_get_clean();

						$this->send_email(
							$order->get_billing_email(),
							'[' . get_bloginfo( 'name' ) . '] Nova Cobrança PIX (#' . $order_id . ')',
							'Novo Código de Pagamento Disponível para seu Pedido ',
							$message
						);

						$note = 'Nova cobrança (' . $charged['txid'] . ') no valor de R$ ' . number_format( $charged['valor']['original'], 2, ',', '.' ) . ' enviada.';
						if ( $reason ) {
							$note .= '<br>' . $reason;
						}

						$current_user = wp_get_current_user();
						if ( $current_user && $current_user instanceof WP_User ) {
							$note .= '<br>Criada por: ' . $current_user->display_name;
						}
						$order->add_order_note(
							$note
						);

						return;
					}
				}

				$order->add_order_note( 'Pagseguro PIX: Não foi possível criar a cobrança de ' . $data['valor'] );
			}
		}
	}

	/**
	 * Format validate pix charge.
	 */
	private function formated_validate() {
		$validate = $this->get_option( 'validate' ) / 3600;
		return $validate < 1 ? '30 minutos' : $validate . ' hora(s)';
	}

	/**
	 * Get formated order to new cob.
	 *
	 * @param wc_order $order the order.
	 */
	private function get_formated_order( $order ) {
		$data = array(
			'order_id' => $order->get_id(),
			'cpf'      => preg_replace( '/\D/', '', get_post_meta( $order->get_id(), '_billing_cpf', true ) ),
			'nome'     => $order->get_formatted_billing_full_name(),
			'valor'    => $order->get_total(),
		);

		return $data;
	}
}
