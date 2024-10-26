<?php
/**
 * Carriers of Argentina for WooCommerce
 *
 * @link              https://kijam.com/
 * @since             1.0.0
 * @package           Kijam
 *
 * @wordpress-plugin
 * Plugin Name: Carriers of Argentina for WooCommerce
 * Plugin URI: http://www.kijam.com/
 * Description: Carriers of Argentina for WooCommerce
 * Author: Kijam López
 * Author URI: https://github.com/kijamve/carriers-of-argentina-for-woocommerce
 * Version: 1.4.6
 * License: GPLv2
 * Text Domain: carriers-of-argentina-for-woocommerce
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_KShippingArgentina' ) ) :

	/**
	 * WooCommerce kshippingargentina main class.
	 */
	class WC_KShippingArgentina {
		/**
		 * Plugin version.
		 *
		 * @var string
		 */

		const VERSION = '1.4.6';

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Vat types.
		 *
		 * @var object
		 */
		public static $vat_types = array(
			'DNI'  => 'DNI',
			'CUIT' => 'CUIT',
			'CUIL' => 'CUIL',
		);

		/**
		 * Initialize the plugin.
		 */
		private function __construct() {
			// Load plugin text domain.
			$this->load_plugin_textdomain();

			// Checks with WooCommerce is installed.
			if ( class_exists( 'WC_Shipping_Method' ) ) {
				if ( ! class_exists( 'KShippingArgentina_API' ) ) {
					include_once dirname( __FILE__ ) . '/includes/class-kshippingargentina-api.php';
					include_once dirname( __FILE__ ) . '/includes/class-wc-kshippingargentina-shipping.php';
					include_once dirname( __FILE__ ) . '/includes/class-wc-kshippingargentina-manager.php';
				}
				KShippingArgentina_API::init();
				add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
				add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping' ) );
				add_filter( 'woocommerce_email_classes', array( $this, 'email_classes' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			}
		}

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
		 * Load the plugin text domain for translation.
		 *
		 * @return void
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'carriers-of-argentina-for-woocommerce' );
			load_textdomain( 'arriers-of-argentina-for-woocommerce', trailingslashit( WP_LANG_DIR ) . 'carriers-of-argentina-for-woocommerce/carriers-of-argentina-for-woocommerce-' . $locale . '.mo' );
			load_plugin_textdomain( 'carriers-of-argentina-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Add shipping function.
		 *
		 * @access public
		 * @param mixed $methods Methods.
		 *
		 * @return  array
		 */
		public function add_shipping( $methods ) {
			$methods['kshippingargentina-shipping'] = 'WC_KShippingArgentina_Shipping';
			return $methods;
		}
		/**
		 * Add a new integration to WooCommerce.
		 *
		 * @param mixed $integrations Integrations classes.
		 *
		 * @return  array
		 */
		public function add_integration( $integrations ) {
			$integrations[] = 'WC_KShippingArgentina_Manager';
			return $integrations;
		}

		/**
		 * Filter email_classes.
		 *
		 * @param mixed $email_classes Email classes.
		 *
		 * @return  array
		 */
		public function email_classes( $email_classes ) {
			require 'includes/class-wc-knewtracking-admin-email.php';
			require 'includes/class-wc-knewtracking-customer-email.php';
			$email_classes['WC_KNewTracking_Admin_Email']    = new WC_KNewTracking_Admin_Email();
			$email_classes['WC_KNewTracking_Customer_Email'] = new WC_KNewTracking_Customer_Email();
			return $email_classes;
		}

		/**
		 * WooCommerce fallback notice.
		 *
		 * @return  void
		 */
		public function woocommerce_missing_notice() {
			// translators: %s Version of WooCommerce.
			echo '<div class="error"><p>' . esc_html( sprintf( __( 'WooCommerce Shipping Argentina depends on the last version of %s to work!', 'carriers-of-argentina-for-woocommerce' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'carriers-of-argentina-for-woocommerce' ) . '</a>' ) ) . '</p></div>';
		}

		/**
		 * Backwards compatibility with version prior to 2.1.
		 *
		 * @return object Returns the main instance of WooCommerce class.
		 */
		public static function woocommerce_instance() {
			if ( function_exists( 'WC' ) ) {
				return WC();
			} else {
				global $woocommerce;
				return $woocommerce;
			}
		}

		/**
		 * Backwards compatibility.
		 *
		 * @return object Returns the main instance of wpdb class.
		 */
		public static function woocommerce_wpdb() {
			global $wpdb;
			return $wpdb;
		}

		/**
		 * Backwards compatibility.
		 *
		 * @return object Returns the main instance of product class.
		 */
		public static function woocommerce_product() {
			global $product;
			return $product;
		}

		/**
		 * Backwards compatibility.
		 *
		 * @return object Returns the main instance of Order class.
		 */
		public static function woocommerce_theorder() {
			global $theorder;
			return $theorder;
		}
	}

	/**
	 * Backwards compatibility.
	 *
	 * @return object Returns the main instance of Order class.
	 */
	function kshippingargentina_get_theorder() {
		global $theorder;
		return $theorder;
	}

	/**
	 * Install function.
	 *
	 * @return void
	 */
	function kshippingargentina_install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'kshippingargentina_cache';
		$sql             = "CREATE TABLE $table_name  (
			`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`cache_id` varchar(100) NOT NULL,
			`data` LONGTEXT NOT NULL,
			`ttl` INT(11) NOT NULL,
			UNIQUE(cache_id),
			INDEX(ttl)
        ) $charset_collate;";
		dbDelta( $sql );
		$table_name = $wpdb->prefix . 'kshippingargentina_report';
		$sql        = "CREATE TABLE $table_name  (
				`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`id_order` INT(11) NOT NULL,
				`carrier` VARCHAR(100) NOT NULL,
				`last_status` VARCHAR(128) DEFAULT NULL,
				`last_status_detail` TEXT DEFAULT NULL,
				`last_sync` DATETIME NOT NULL DEFAULT \'2010-01-01 00:00:00\',
				`first_sync` DATETIME NOT NULL,
				UNIQUE(id_order, carrier),
				INDEX(id_order),
				INDEX(carrier),
				INDEX(last_sync),
				INDEX(last_status),
				INDEX(first_sync)
			) $charset_collate;";
		dbDelta( $sql );
	}

	register_activation_hook( __FILE__, 'kshippingargentina_install' );

	/**
	 * Action links function.
	 *
	 * @param mixed $links Links.
	 *
	 * @return array
	 */
	function kshippingargentina_add_action_links( $links ) {
		$mylinks = array(
			'<a style="font-weight: bold;color: red" href="' . admin_url( 'admin.php?page=wc-settings&tab=integration&section=kshippingargentina-manager' ) . '">Configurar Credenciales</a>',
			'<a style="font-weight: bold;color: red" href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping' ) . '">Configurar Transportistas</a>',
		);
		return array_merge( $links, $mylinks );
	}
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'kshippingargentina_add_action_links' );

	include_once 'functions.php';
	include_once 'functions-labels.php';
	include_once 'functions-boxes.php';

	add_action( 'plugins_loaded', array( 'WC_KShippingArgentina', 'get_instance' ), 0 );

endif;
