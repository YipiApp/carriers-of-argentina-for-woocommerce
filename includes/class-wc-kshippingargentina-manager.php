<?php
/**
 * Manager Class
 *
 * @package Kijam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Manager Class
 *
 * @since             1.0.0
 * @extends WC_Integration
 */
class WC_KShippingArgentina_Manager extends WC_Integration {
	/**
	 * Instance.
	 *
	 * @var WC_KShippingArgentina_Manager
	 */
	private static $is_load = null;

	/**
	 * Config array.
	 *
	 * @var array
	 */
	public static $config = array();

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->id           = 'kshippingargentina-manager';
		$this->has_fields   = false;
		$this->method_title = __( 'Shipping for Argentina', 'wc-kshippingargentina' );

		// Load the settings.
		$this->init_settings();

		$this->title       = $this->get_option( 'title', 'Shipping for Argentina' );
		$this->description = $this->get_option( 'description', '' );

		self::$is_load = $this;

		// Load the form fields.
		$this->init_form_fields();
		foreach ( $this->form_fields as $key => $data ) {
			$d = $this->get_option( $key, isset( $data['default'] ) ? $data['default'] : '' );
			if ( 'checkbox' === $data['type'] ) {
				$d = ( 'yes' === $d );
			}
			self::$config[ $key ] = $d;
		}

		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );

		KShippingArgentina_API::init( self::$config );
	}
	/**
	 * Returns instance.
	 *
	 * @return WC_KShippingArgentina_Manager
	 */
	public static function get_instance() {
		if ( is_null( self::$is_load ) ) {
			self::$is_load = new self();
		}
		return self::$is_load;
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	protected function using_supported_currency() {
		$currency = get_woocommerce_currency();
		return in_array( $currency, array( 'ARS', 'USD' ), true ) && ( 'ARS' === $currency || 'off' !== self::$config['conversion_option'] );
	}

	/**
	 * Returns conversion rate.
	 *
	 * @param string $currency_src ISO Currency Source.
	 * @param string $currency_dst ISO Currency Destination.
	 *
	 * @return float
	 */
	public function get_conversion_rate( $currency_src, $currency_dst ) {
		static $conversion_rate = null;
		if ( null === $conversion_rate ) {
			$conversion_rate = json_decode( get_option( $this->id . 'conversion_rate', '{}' ) );
		}
		if ( $currency_src === $currency_dst || 'off' === self::$config['conversion_option'] ) {
			return 1.0;
		}
		if ( 'live-rates' === self::$config['conversion_option'] ) {
			if (
				isset( $conversion_rate[ $currency_src ] ) &&
				isset( $conversion_rate[ $currency_src ][ $currency_dst ] ) &&
				$conversion_rate[ $currency_src ][ $currency_dst ]['time'] > time() - 60 * 60 * 12
			) {
				return $conversion_rate[ $currency_src ][ $currency_dst ]['rate'];
			}
			$data = wp_remote_get( 'https://www.live-rates.com/rates' );
			if ( ! is_wp_error( $data ) ) {
				$api_arr = json_decode( $data['body'], true );
				foreach ( $api_arr as $fields ) {
					if ( isset( $fields['currency'] ) && 7 === strlen( $fields['currency'] ) &&
					preg_match( '/[A-Z0-9]{3}\/[A-Z0-9]{3}/', $fields['currency'] ) ) {
						$cur                                   = explode( '/', $fields['currency'] );
						$conversion_rate[ $cur[0] ][ $cur[1] ] = array();
						$conversion_rate[ $cur[0] ][ $cur[1] ]['rate'] = (float) $fields['rate'];
						$conversion_rate[ $cur[0] ][ $cur[1] ]['time'] = time();
						$conversion_rate[ $cur[1] ][ $cur[0] ]         = array();
						$conversion_rate[ $cur[1] ][ $cur[0] ]['rate'] = 1.0 / (float) $fields['rate'];
						$conversion_rate[ $cur[1] ][ $cur[0] ]['time'] = time();
					}
				}
				update_option( $this->id . 'conversion_rate', wp_json_encode( $conversion_rate ) );
			}
			if (
				isset( $conversion_rate[ $currency_src ] ) &&
				isset( $conversion_rate[ $currency_src ][ $currency_dst ] )
			) {
				return $conversion_rate[ $currency_src ][ $currency_dst ]['rate'];
			}
		}
		if ( 'custom' === self::$config['conversion_option'] ) {
			if ( 'ARS' === $currency_dst ) {
				return self::$config['conversion_rate'];
			} else {
				return 1.0 / self::$config['conversion_rate'];
			}
		}
		return 1.0;
	}

	/**
	 * Is available?
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$available = ( 'yes' === $this->settings['enabled'] ) &&
			$this->using_supported_currency();

		return $available;
	}

	/**
	 * Gets the admin url.
	 *
	 * @return string
	 */
	public function admin_url() {
		return admin_url( 'admin.php?page=wc-settings&tab=integration&section=' . $this->id );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = include 'data-settings-kshippingargentina-manager.php';
	}


	/**
	 * Generate HTML Form Fields.
	 *
	 * @param string $key Key name.
	 * @param array  $data Data.
	 *
	 * @return string
	 */
	public function generate_html_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'       => '',
			'type'        => 'html',
			'description' => '',
		);
		$data      = wp_parse_args( $data, $defaults );
		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp">
			<?php echo wp_kses_post( $data['description'] ); ?>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}
}
