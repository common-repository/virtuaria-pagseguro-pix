<?php
/**
 * Plugin Name: Virtuaria PagSeguro PIX para Woocommerce
 * Plugin URI: https://virtuaria.com.br
 * Description: Adiciona o método de pagamento PagSeguro Pix.
 * Author: Virtuaria
 * Author URI: http://virtuaria.com.br
 * Version: 1.0.1
 * License: GPLv2 or later
 *
 * @package Virtuaria/Payments
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Virtuaria_Pagseguro_PIX' ) ) :
	define( 'PAGSEGURO_PIX_DIR', plugin_dir_path( __FILE__ ) );
	define( 'PAGSEGURO_PIX_URL', plugin_dir_url( __FILE__ ) );
	/**
	 * Class definition.
	 */
	class Virtuaria_Pagseguro_PIX {
		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Singleton constructor.
		 */
		private function __construct() {
			if ( ! class_exists( 'Virtuaria_Pagseguro' ) ) {
				add_action( 'admin_notices', array( $this, 'missing_virtuaria_pagseguro' ) );
			} elseif ( class_exists( 'WC_Payment_Gateway' ) ) {
				$this->load_dependecys();
				add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
				add_action( 'admin_menu', array( $this, 'add_submenu_pix' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'missing_woocommerce' ) );
			}
		}

		/**
		 * Load file dependencys.
		 */
		private function load_dependecys() {
			require_once 'includes/class-wc-pagseguro-pix-gateway.php';
			require_once 'includes/class-wc-pagseguro-pix-api.php';
		}

		/**
		 * Display warning about dependency missing.
		 */
		public function missing_virtuaria_pagseguro() {
			$plugin_name = 'Virtuaria PagSeguro';
			$plugin_path = 'virtuaria-pagseguro/virtuaria-pagseguro.php';
			require_once 'templates/admin/html-notice-missing-dependency.php';
		}

		/**
		 * Display warning about dependency missing.
		 */
		public function missing_woocommerce() {
			$plugin_name = 'Woocommerce';
			$plugin_path = 'woocommerce/woocommerce.php';
			require_once 'templates/admin/html-notice-missing-dependency.php';
		}

		/**
		 * Add Payment method.
		 *
		 * @param array $methods the current methods.
		 */
		public function add_gateway( $methods ) {
			$methods[] = 'WC_PagSeguro_PIX_Gateway';
			return $methods;
		}

		/**
		 * Get templates path.
		 *
		 * @return string
		 */
		public static function get_templates_path() {
			return plugin_dir_path( __FILE__ ) . 'templates/';
		}

		/**
		 * Add submenu pagseguro pix.
		 */
		public function add_submenu_pix() {
			add_submenu_page(
				'pagamentos',
				'Pagseguro PIX',
				'Pagseguro PIX',
				'remove_users',
				'admin.php?page=wc-settings&tab=checkout&section=pagseguro_pix'
			);
		}

		/**
		 * Show manual refund button.
		 */
		public function show_manual_refund_buttons() {
			if ( isset( $_GET['post'] ) && 'shop_order' === get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) ) {
				$order = wc_get_order( sanitize_text_field( wp_unslash( $_GET['post'] ) ) );
				if ( $order && 'pagseguro_pix' === $order->get_payment_method() ) {
					?>
					<style>
						.post-type-shop_order div.refund-actions .do-manual-refund {
							display: inline-block;
						}
					</style>
					<?php
				}
			}
		}
	}

	add_action( 'plugins_loaded', array( 'Virtuaria_Pagseguro_PIX', 'get_instance' ) );

endif;
