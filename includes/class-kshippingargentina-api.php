<?php
/**
 * API Class
 *
 * @package Kijam
 */

/**
 * API Class
 *
 * @since             1.0.0
 */
class KShippingArgentina_API {
	const TRACKING_URL_OCA      = 'http://www5.oca.com.ar/ocaepak/historial/Historial_Piezas.asp?IdOrdenRetiro=$$';
	const TRACKING_URL_ANDREANI = 'https://www.andreani.com/#!/informacionEnvio/@';
	const TRACKING_URL_CORREO   = 'https://www.correoargentino.com.ar/formularios/e-commerce?id=@';

	const OCA_ACCOUNT_LENGTH      = 10;
	const OCA_NAME_LENGTH         = 30;
	const OCA_STREET_LENGTH       = 30;
	const OCA_NUMBER_LENGTH       = 5;
	const OCA_FLOOR_LENGTH        = 6;
	const OCA_APARTMENT_LENGTH    = 4;
	const OCA_POSTCODE_LENGTH     = 4;
	const OCA_LOCALITY_LENGTH     = 30;
	const OCA_PROVINCE_LENGTH     = 30;
	const OCA_CONTACT_LENGTH      = 30;
	const OCA_EMAIL_LENGTH        = 100;
	const OCA_REQUESTOR_LENGTH    = 30;
	const OCA_PHONE_LENGTH        = 30;
	const OCA_MOBILE_LENGTH       = 15;
	const OCA_OBSERVATIONS_LENGTH = 100;
	const OCA_OPERATIVE_LENGTH    = 6;
	const OCA_REMIT_LENGTH        = 30;
	const OCA_ATTR_LENGTH         = 11;

	const OCA_API_SANDBOX = 'http://webservice.oca.com.ar/ePak_Tracking_TEST/Oep_TrackEPak.asmx?wsdl';
	const OCA_API_PROD    = 'http://webservice.oca.com.ar/ePak_tracking/Oep_TrackEPak.asmx?wsdl';

	const CORREO_TRACKING_URL = 'https://www.correoargentino.com.ar/formularios/e-commerce?id=';
	const CORREO_MI_CORREO_API_URL = 'https://api.correoargentino.com.ar/micorreo/v1';
	const CORREO_MI_CORREO_API_SANDBOX_URL = 'https://apitest.correoargentino.com.ar/micorreo/v1';
	
	const ANDREANI_API_PROD    = array(
		'v2' => 'https://apis.andreani.com',
		'v1' => 'https://api.andreani.com',
	);
	const ANDREANI_API_SANDBOX = array(
		'v2' => 'https://apisqa.andreani.com',
		'v1' => 'https://api.qa.andreani.com',
	);

	private static $mutex_file = null;


	/**
	 * OCA Clients array.
	 *
	 * @var array
	 */
	public static $oca_clients = array();

	/**
	 * Config array.
	 *
	 * @var array
	 */
	public static $config = array();

	/**
	 * Cache metadata array.
	 *
	 * @var array
	 */
	public static $cache_metadata = array();

	/**
	 * Config array.
	 *
	 * @var bool
	 */
	public static $show_debug = false;

	/**
	 * Cache array.
	 *
	 * @var array
	 */
	private static $module_cache = array();

	/**
	 * States array.
	 *
	 * @var array
	 */
	private static $kshippingargentina_states = array();

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		self::init();
	}

	/**
	 * Constructor.
	 *
	 * @param mixed $config Old config setting.
	 *
	 * @return void
	 */
	public static function init( $config = false ) {
		if ( $config ) {
			self::$config = $config;
			if ( isset( self::$config['debug'] ) ) {
				self::$show_debug = self::$config['debug'] && 'no' !== self::$config['debug'];
			}
			return;
		}
		if ( isset( self::$config['debug'] ) ) {
			self::$show_debug = self::$config['debug'] && 'no' !== self::$config['debug'];
			return;
		}
		self::$config = get_option( 'woocommerce_kshippingargentina-manager_settings' );
		if ( isset( self::$config['debug'] ) ) {
			self::$show_debug = self::$config['debug'] && 'no' !== self::$config['debug'];
		}
	}

	/**
	 * List offices by postcode.
	 *
	 * @param string $service Services Name: oca/correo_argentino/andreani.
	 * @param int    $postcode Postcode.
	 * @param bool   $sender Is sender.
	 * @param bool   $receiver Is receiver.
	 *
	 * @return array
	 */
	public static function get_office( $service, $postcode, $sender = null, $receiver = null ) {
		self::init();
		if ($service == 'correo_argentino') {
			$service = 'correo_argentino_v2';
		}
		$offices = self::call( "/offices/postcode/$service/$postcode", false, 3600 * 24 * 60 );
		$return  = array();
		foreach ( $offices as $office ) {
			if ( ! $sender || isset( $office['is_sender'] ) && $office['is_sender'] ) {
				if ( ! $receiver || isset( $office['is_receiver'] ) && $office['is_receiver'] ) {
					$return[ $office['iso'] ] = $office;
				}
			}
		}
		if ( empty( $return ) || isset( $offices['statusCode'] ) && ( $offices['statusCode'] < 200 || $offices['statusCode'] >= 300 ) ) {
			self::debug( "get_office error raw-response[/offices/postcode/$service/$postcode - $sender - $receiver]: " . json_encode( $offices ) );
		}
		return count( $return ) > 0 ? $return : false;
	}

	/**
	 * List cities by iso state.
	 *
	 * @param string $state iso state.
	 *
	 * @return array
	 */
	public static function get_cities( $state ) {
		self::init();
		$cities = self::call( "/cities/states/$state", false, 3600 * 24 * 120 );
		$return = array();
		foreach ( $cities as $city ) {
			$return[ $city['name'] ] = $city['name'];
		}
		return count( $return ) > 0 ? $return : array( '' => __( 'Cities not found...', 'carriers-of-argentina-for-woocommerce' ) );
	}

	/**
	 * Quote by postcode.
	 *
	 * @param string $service Services Name: oca/correo_argentino/andreani.
	 * @param array  $packages .
	 * @param string $office ISO Office of Origin.
	 * @param int    $postcode_src Postcode Origin.
	 * @param int    $postcode_dst Postcode Destination.
	 * @param string $product_cuit CUIT of you company for OCA.
	 * @param string $product_type .
	 * @param string $product_client Operaitiva or Contrato of you company for OCA/Andreani.
	 * @param string $fiscal_type cf/py (Only for Correo Argentino).
	 * @param string $type door/office (Only for Correo Argentino).
	 * @param string $velocity express/classic (Only for Correo Argentino).
	 *
	 * @return array
	 */
	public static function get_quote(
		$service,
		$packages,
		$office,
		$postcode_src,
		$postcode_dst,
		$product_cuit,
		$product_type,
		$product_client,
		$fiscal_type,
		$type,
		$velocity
	) {
		self::init();
		$postcode_src = preg_replace( '/[^0-9]/', '', $postcode_src );
		$postcode_dst = preg_replace( '/[^0-9]/', '', $postcode_dst );
		if ( ! $postcode_src || ! $postcode_dst || strlen( $postcode_src ) != 4 || strlen( $postcode_dst ) != 4 ) {
			self::debug( 'Invalid postcode: ', array( $postcode_src, $postcode_dst ) );
			return false;
		}
		$total       = 0;
		$delay       = 0;
		$fiscal_type = strtolower( $fiscal_type );
		$velocity    = strtolower( $velocity );
		$type        = strtolower( $type );
		if ( 'correo_argentino' === $service ) {
			foreach ( $packages as $package ) {
				if ( $package['volume'] / 6000 > $package['weight'] ) {
					$package['weight'] = ceil( $package['volume'] / 6000 );
				}
				$costs = self::call( "/quotes/postcode/correo_argentino/{$package['weight']}/$postcode_src/$postcode_dst" );
				if ( $costs && is_array( $costs ) ) {
					foreach ( $costs as $cost ) {
						if ( isset( $cost['fiscalType'] ) && $cost['fiscalType'] === $fiscal_type ) {
							foreach ( $cost['quotes'] as $quote ) {
								if ( $quote['name'] === $velocity ) {
									$total += $quote[ $type ];
									break 2;
								}
							}
						}
					}
				} else {
					return false;
				}
			}
		} elseif ( 'oca' === $service ) {
			foreach ( $packages as $package ) {
				$costs = self::call( "/quotes/postcode/oca/{$product_cuit}/{$product_type}/{$package['product_cost']}/{$package['weight']}/{$package['volume']}/$postcode_src/$postcode_dst" );
				if ( $costs && isset( $costs['total'] ) ) {
					$total += $costs['total'];
					if ( isset( $costs['delay'] ) ) {
						$delay = max( $delay, $costs['delay'] );
					}
				} else {
					return false;
				}
			}
		} elseif ( 'andreani' === $service ) {
			$office = explode( '#', strtoupper( $office ) );
			$costs  = self::call( "/quotes/postcode/andreani/{$product_client}/{$product_type}/$office[0]/$postcode_dst", $packages );
			if ( $costs && isset( $costs['total'] ) ) {
				$total += $costs['total'];
				if ( isset( $costs['delay'] ) ) {
					$delay = max( $delay, $costs['delay'] );
				}
			}
		}
		return $total > 0 ? array(
			'total' => $total,
			'delay' => $delay,
		) : false;
	}
	/**
	 * Locks a mutex to ensure exclusive access to a critical section of code.
	 *
	 * @return bool true if the mutex was successfully acquired, false otherwise.
	 */
	private static function mutex() {
		global $wpdb;
		$wp_content_dir = WP_CONTENT_DIR;
		$lock_file_path = $wp_content_dir . '/wc-kshippingargentina.lock';
		$lock           = fopen( $lock_file_path, 'w' );
		if ( $lock && flock( $lock, LOCK_EX ) ) {
			self::$mutex_file = $lock;
			return true;
		}
		return false;
	}

	/**
	 * Unlocks the mutex for the kshipping_argentina_mutex.
	 *
	 * @return void
	 */
	private static function unmutex() {
		if ( self::$mutex_file ) {
			flock( self::$mutex_file, LOCK_UN );
			fclose( self::$mutex_file );
			self::$mutex_file = null;
		}
	}
	/**
	 * Call api request.
	 *
	 * @param string $path Path.
	 * @param string $post_data Data.
	 * @param string $ttl TTL Cache.
	 *
	 * @return mixed
	 */
	public static function call( $path, $post_data = false, $ttl = 86400 ) {
		self::init();
		$url = 'https://' . self::$config['api_host'] . $path;
		if ( ! isset( self::$config['api_host'] ) || empty( self::$config['api_key'] ) ) {
			return false;
		}
		if ( $post_data ) {
			if ( ! is_string( $post_data ) ) {
				$post_data = wp_json_encode( $post_data );
			}
			$cache_id = 'call_post_' . md5( self::$config['api_key'] . $post_data . $url );
		} else {
			$cache_id = 'call_get_' . md5( self::$config['api_key'] . $url );
		}
		$result = self::get_cache( $cache_id );
		if ( stristr( $result, 'Too many requests' ) || stristr( $result, 'You have exceeded the' ) ) {
			self::set_cache( $cache_id, 'error', 1 );
		} elseif ( 'error' === $result ) {
			return false;
		} elseif ( ! empty( $result ) ) {
			$api_arr = json_decode( $result, true );
			if ( ! $api_arr ) {
				self::debug( 'Json decode error from CACHE: ' . $result );
				self::set_cache( $cache_id, 'error', 1 );
			} else {
				return $api_arr;
			}
		}
		$config = array(
			'timeout' => 10,
		);
		if ( 'shipping.yipi.app' !== self::$config['api_host'] ) {
			$config['headers'] = array(
				'X-RapidAPI-Host' => self::$config['api_host'],
				'X-RapidAPI-Key'  => self::$config['api_key'],
			);
		} else {
			$data_to_send      = isset( $_SERVER[ base64_decode( 'SFRUUF9IT1NU' ) ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ base64_decode( 'SFRUUF9IT1NU' ) ] ) ) : '';
			$config['headers'] = array(
				'Authorization'                     => 'Bearer ' . self::$config['api_key'],
				base64_decode( 'eC1hcGktZG9tYWlu' ) => $data_to_send,
			);
		}
		if ( 'shipping.yipi.app' !== self::$config['api_host'] && ! self::mutex() ) {
			self::debug( 'Mutex error: ' . $url . ' POST_DATA: ' . $post_data );
			return false;
		}
		$result = self::get_cache( $cache_id );
		if ( 'error' !== $result && ! empty( $result ) ) {
			$api_arr = json_decode( $result, true );
			if ( ! $api_arr ) {
				self::debug( 'Json decode error from CACHE: ' . $result );
				self::set_cache( $cache_id, 'error', 1 );
			} else {
				if ( 'shipping.yipi.app' !== self::$config['api_host'] ) {
					self::unmutex();
				}
				return $api_arr;
			}
		}
		if ( 'shipping.yipi.app' !== self::$config['api_host'] ) {
			$last_request = (int) self::get_cache( 'kshippingargentina-last-request', false );
			self::debug( 'Last request: ' . $last_request );
			$now = time();
			if ( $last_request >= $now - 3 ) {
				self::debug( 'Wait...' );
				sleep( max( 1, 3 - ( $now - $last_request ) ) );
				self::debug( 'End wait.' );
			}
		}
		if ( $post_data ) {
			$config['body']                    = $post_data;
			$config['headers']['Content-Type'] = 'application/json; charset=utf-8';
			$data                              = wp_remote_post( $url, $config );
		} else {
			$data = wp_remote_get( $url, $config );
		}
		self::debug( 'End request: ' . time() );
		if ( 'shipping.yipi.app' !== self::$config['api_host'] ) {
			self::set_cache( 'kshippingargentina-last-request', time() );
			self::unmutex();
		}
		if ( ! is_wp_error( $data ) ) {
			self::debug( 'From API: ', array( $url, $post_data, $data['body'] ) );
			$api_arr = json_decode( $data['body'], true );
			if ( $api_arr && ! stristr( $data['body'], 'Too many requests' ) && ! stristr( $data['body'], 'You have exceeded the' ) ) {
				self::set_cache( $cache_id, $data['body'], $ttl );
				return $api_arr;
			}
			if ( ! stristr( $data['body'], 'Too many requests' ) && ! stristr( $data['body'], 'You have exceeded the' ) ) {
				self::set_cache( $cache_id, 'error', 5 * 60 );
			}
			return false;
		} else {
			self::debug( 'From API error: ', array( $url, $post_data, $data ) );
		}
		self::set_cache( $cache_id, 'error', 5 * 60 );
		return false;
	}


	// ##################################################################
	// ##################################################################
	// ##################################################################
	// ##################################################################

	// ######################## CORREO ARGENTINO ########################

	// ##################################################################
	// ##################################################################
	// ##################################################################
	// ##################################################################

	/**
	 * Get Correo Argentino Token.
	 *
	 * @param mixed $error error.
	 * @param bool  $sandbox sandbox.
	 *
	 * @return mixed
	 */
	public static function get_token_correo( $force = false, &$error = null ) {
		$sandbox = apply_filters( 'kshippingargentina_sandbox', false );
		$key = base64_encode( 'WOOCOMMERCE:Paneles55+' );
		$cache_id = 'token_correo_' . md5( $key );
		$token = self::get_cache( $cache_id );
		
		if ( ! $token || $force ) {
			$config = array(
                'method' => 'POST',
				'timeout' => 10,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Accept' => 'application/json',
					'Connection' => 'keep-alive',
					'Authorization' => 'Basic ' . $key,
				),
			);
			$domain = $sandbox ? self::CORREO_MI_CORREO_API_SANDBOX_URL : self::CORREO_MI_CORREO_API_URL;
			self::debug( 'get_token_correo Request data: ', array( $domain, $config ) );

			$login = wp_remote_post( $domain . '/token', $config );
			
			if ( ! is_wp_error( $login ) ) {
				$response = json_decode( $login['body'], true );
				self::debug( 'get_token_correo Response: ', array( $response ) );
				if ( isset( $response['token'] ) ) {
					$token = $response['token'];
					$expire = new DateTime($response['expire']);
					$now = new DateTime('now');
					$diff = $now->diff($expire);
					$minutes = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
					self::set_cache( $cache_id, $token, $minutes * 60 );
				} else {
					$error = __( 'Invalid Correo Argentino credentials', 'carriers-of-argentina-for-woocommerce' );
					return false;
				}
			} else {
				self::debug( 'get_token_correo Error: ', array( $login ) );
				$error = __( 'Invalid Correo Argentino credentials', 'carriers-of-argentina-for-woocommerce' );
				return false;
			}
		}
		return $token;
	}

	/**
	 * Get Correo Argentino Customer ID.
	 *
	 * @param mixed $error error.
	 * @param bool  $sandbox sandbox.
	 *
	 * @return mixed
	 */
	public static function get_customer_id_correo( &$error = null ) {
		$sandbox = apply_filters( 'kshippingargentina_sandbox', false );
		$key = base64_encode( self::$config['correo_username'] . ':' . self::$config['correo_password'] );
		$cache_id = 'customer_id_correo_' . md5( $key );
		$customer_id = self::get_cache( $cache_id );
		
		if ( ! $customer_id ) {
			$token = self::get_token_correo();
			if ( ! $token ) {
				self::debug( 'Failed to get token' );
				return false;
			}

			$config = array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				),
				'body' => wp_json_encode( array(
					'email' => self::$config['correo_username'],
					'password' => self::$config['correo_password']
				) ),
			);
			self::debug( 'get_customer_id_correo Request data: ', array( $config ) );

			$domain = $sandbox ? self::CORREO_MI_CORREO_API_SANDBOX_URL : self::CORREO_MI_CORREO_API_URL;
			$response = wp_remote_post( $domain . '/users/validate', $config );
			
			if ( ! is_wp_error( $response ) ) {
				$result = json_decode( $response['body'], true );
				$result["status"] = wp_remote_retrieve_response_code($response);
				self::debug( 'get_customer_id_correo Response: ', array( $result ) );
				if ( isset( $result['customerId'] ) && ($result['status'] >= 200 && $result['status'] < 300) ) {
					$customer_id = $result['customerId'];
					self::set_cache( $cache_id, $customer_id, 7 * 24 * 60 * 60 ); // 7 days
				} else {
					$error = __( 'Invalid Correo Argentino credentials', 'carriers-of-argentina-for-woocommerce' );
					return false;
				}
			} else {
				self::debug( 'get_customer_id_correo Error: ', array( $response ) );
				$error = __( 'Invalid Correo Argentino credentials', 'carriers-of-argentina-for-woocommerce' );
				return false;
			}
		}
		return $customer_id;
	}

	/**
	 * Create Correo Argentino Label.
	 *
	 * @param array $request Request.
	 * @param mixed $error error.
	 * @param bool  $sandbox sandbox.
	 *
	 * @return mixed
	 */
	public static function create_label_correo( $request, &$error ) {
		$sandbox = apply_filters( 'kshippingargentina_sandbox', false );
		$token = self::get_token_correo();
		if ( ! $token ) {
			self::debug( 'Failed to get token' );
			return false;
		}
		
		$config = array(
			'timeout' => 10,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
				'Authorization' => 'Bearer ' . $token,
			),
			'body' => wp_json_encode( $request ),
		);

		$domain = $sandbox ? self::CORREO_MI_CORREO_API_SANDBOX_URL : self::CORREO_MI_CORREO_API_URL;
		self::debug( 'create_label_correo Request data: ', array( $config ) );
		$response = wp_remote_post( $domain . '/shipping/import', $config );
		
		if ( ! is_wp_error( $response ) ) {
			$result = json_decode( $response['body'], true );
			$result["status"] = wp_remote_retrieve_response_code($response);
			self::debug( 'create_label_correo Label creation response: ', array( $result ) );
			if ( $result && isset( $result['status'] ) && ($result['status'] >= 200 && $result['status'] < 300) ) {
				return $result;
			}
			if ( isset( $result['message'] ) ) {
				$error = $result['message'];
			}
		} else {
			self::debug( 'Label creation error: ', array( $response ) );
		}
		return false;
	}

	/**
	 * Get Correo Argentino Rates.
	 *
	 * @param string $postal_code Postal Code.
	 * @param string $delivery_type Delivery Type.
	 * @param array  $dimensions Dimensions.
	 * @param array  $settings Settings.
	 *
	 * @return mixed
	 */
	public static function get_rates_correo( $origin_postal_code, $postal_code, $delivery_type, $dimensions, $force = false ) {
		$sandbox = apply_filters( 'kshippingargentina_sandbox', $sandbox );
		if (!isset($dimensions['weight']) || !isset($dimensions['length']) || 
			!isset($dimensions['height']) || !isset($dimensions['width'])) {
			return false;
		}

		$dimensions['weight'] = $dimensions['weight'] * 1000; // Convert to grams

		$dimensions = array_map( function( $dimension ) {
			return (int) $dimension;
		}, $dimensions );

		$settings = get_option( 'woocommerce_kshippingargentina-manager_settings' );

		$token = self::get_token_correo( $force );
		if ( ! $token ) {
			self::debug( 'Failed to get token' );
			return false;
		}

		$customer_id = self::get_customer_id_correo( );
		if ( ! $customer_id ) {
			self::debug( 'Failed to get customer ID' );
			return false;
		}
		$body = wp_json_encode( array(
			'customerId' => $customer_id,
			'postalCodeOrigin' => $origin_postal_code,
			'postalCodeDestination' => $postal_code,
			'deliveredType' => $delivery_type,
			'dimensions' => $dimensions
		) );
		$config = array(
			'timeout' => 10,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
				'Authorization' => 'Bearer ' . $token,
			),
			'body' => $body,
		);

		$cache_id = 'get_rates_correo_' . md5( $body );
		$result = self::get_cache( $cache_id );
		if ( $result ) {
			return $result;
		}

		$domain = $sandbox ? self::CORREO_MI_CORREO_API_SANDBOX_URL : self::CORREO_MI_CORREO_API_URL;
		self::debug( 'get_rates_correo Request data: ', array( $domain, $config ) );

		$response = wp_remote_post( $domain . '/rates', $config );
		
		if ( ! is_wp_error( $response ) ) {
			$result = json_decode( $response['body'], true );
			$result['status'] = wp_remote_retrieve_response_code( $response );
			$result['message'] = wp_remote_retrieve_response_message( $response );
			self::debug( 'get_rates_correo response: ', array( $result ) );
			if ( $result && isset( $result['status'] ) && ($result['status'] >= 200 && $result['status'] < 300) ) {
				self::set_cache( $cache_id, $result, 24 * 60 * 60 ); // 1 day
				return $result;
			} /*else if (! $force ) {
				return self::get_rates_correo( $origin_postal_code, $postal_code, $delivery_type, $dimensions, true );
			}*/ else {
				self::debug( 'get_rates_correo error: ', array( $result ) );
				return false;
			}
		}
		
		self::debug( 'get_rates_correo error: ', array( $response ) );
		return false;
	}

	// ##################################################################
	// ##################################################################
	// ##################################################################
	// ##################################################################

	// ############################ ANDREANI ############################

	// ##################################################################
	// ##################################################################
	// ##################################################################
	// ##################################################################

	/**
	 * Get Andreani Token.
	 *
	 * @param mixed $error error.
	 * @param bool  $sandbox sandbox.
	 *
	 * @return mixed
	 */
	public static function get_token_andreani( &$error, $sandbox = false ) {
		$sandbox  = apply_filters( 'kshippingargentina_sandbox', $sandbox );
		$key      = base64_encode( self::$config['andreani_username'] . ':' . self::$config['andreani_password'] );
		$cache_id = 'token_andreani_' . md5( $key );
		$token    = self::get_cache( $cache_id );
		if ( ! $token ) {
			$config      = array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type'  => 'application/json; charset=utf-8',
					'Authorization' => 'Basic ' . $key,
				),
			);
			$api_version = apply_filters( 'kshippingargentina_andreani_api_version', 'v2' );
			if ( $sandbox ) {
				$domain = self::ANDREANI_API_SANDBOX[ $api_version ];
			} else {
				$domain = self::ANDREANI_API_PROD[ $api_version ];
			}
			$login = wp_remote_get( $domain . '/login', $config );
			if ( ! is_wp_error( $login ) ) {
				$token = wp_remote_retrieve_header( $login, 'x-authorization-token' );
				if ( ! $token ) {
					$error = __( 'User or Password of Andreani is invalid', 'carriers-of-argentina-for-woocommerce' );
					return false;
				}
				self::set_cache( $cache_id, $token, 3600 );
			} else {
				$error = __( 'User or Password of Andreani is invalid', 'carriers-of-argentina-for-woocommerce' );
				return false;
			}
		}
		return $token;
	}

	/**
	 * Get Andreani PDF Label.
	 *
	 * @param string $tc Tracking code.
	 * @param mixed  $error error.
	 * @param bool   $sandbox sandbox.
	 *
	 * @return mixed
	 */
	public static function get_andreani_pdf_label( $tc, &$error, $sandbox = false ) {
		$sandbox     = apply_filters( 'kshippingargentina_sandbox', $sandbox );
		$api_version = apply_filters( 'kshippingargentina_andreani_api_version', 'v2' );
		if ( $sandbox ) {
			$domain = self::ANDREANI_API_SANDBOX[ $api_version ];
		} else {
			$domain = self::ANDREANI_API_PROD[ $api_version ];
		}
		$token = self::get_token_andreani( $error, $sandbox );
		if ( ! $token ) {
			return false;
		}
		$config = array(
			'timeout' => 10,
			'headers' => array(
				'x-authorization-token' => $token,
			),
		);
		$pdf    = wp_remote_get( $domain . '/v2/ordenes-de-envio/' . $tc . '/etiquetas', $config );
		if ( ! is_wp_error( $pdf ) ) {
			return $pdf['body'];
		}
		if ( isset( $pdf['body'] ) && ! empty( $pdf['body'] ) ) {
			$error = __( 'Andreani', 'carriers-of-argentina-for-woocommerce' ) . ': ' . $pdf['body'];
		}
		return false;
	}

	/**
	 * Create Andreani Label.
	 *
	 * @param array $request Request.
	 * @param mixed $error error.
	 * @param bool  $sandbox sandbox.
	 *
	 * @return mixed
	 */
	public static function create_label_andreani( $request, &$error, $sandbox = false ) {
		$sandbox     = apply_filters( 'kshippingargentina_sandbox', $sandbox );
		$api_version = apply_filters( 'kshippingargentina_andreani_api_version', 'v2' );
		if ( $sandbox ) {
			$domain = self::ANDREANI_API_SANDBOX[ $api_version ];
		} else {
			$domain = self::ANDREANI_API_PROD[ $api_version ];
		}
		$token = self::get_token_andreani( $error, $sandbox );
		if ( ! $token ) {
			return false;
		}
		$config = array(
			'timeout' => 10,
			'headers' => array(
				'Content-Type'          => 'application/json; charset=utf-8',
				'x-authorization-token' => $token,
			),
			'body'    => wp_json_encode( $request ),
		);

		self::debug( 'create_label_andreani: ', array( $domain . '/v2/ordenes-de-envio', $config ) );

		$tracking_code = wp_remote_post( $domain . '/v2/ordenes-de-envio', $config );
		if ( ! is_wp_error( $tracking_code ) ) {
			$result = json_decode( $tracking_code['body'], true );
			if ( $result ) {
				return $result;
			}
		}
		if ( isset( $tracking_code['body'] ) && ! empty( $tracking_code['body'] ) ) {
			$error = __( 'Andreani', 'carriers-of-argentina-for-woocommerce' ) . ': ' . $tracking_code['body'];
		}
		return false;
	}

	// ##################################################################
	// ##################################################################
	// ##################################################################
	// ##################################################################

	// ############################# IS OCA #############################

	// ##################################################################
	// ##################################################################
	// ##################################################################
	// ##################################################################

	/**
	 * Cancel OCA Label.
	 *
	 * @param string $tc Tracking code.
	 * @param mixed  $error error.
	 *
	 * @return bool
	 */
	public static function cancel_oca_label( $tc, &$error ) {
		self::init();
		$result = self::call_oca(
			'AnularOrdenGenerada',
			array(
				'usr'           => self::$config['oca_username'],
				'psw'           => self::$config['oca_password'],
				'IdOrdenRetiro' => (int) $tc,
			),
			false,
			null,
			$error
		);
		if ( ! $result ) {
			return false;
		}
		return 100 === (int) $result['IdResult'];
	}

	/**
	 * To ascii.
	 *
	 * @param string $text text.
	 * @param int    $max_length max_length.
	 * @param int    $from_end from_end.
	 *
	 * @return string
	 */
	public static function to_ascii( $text, $max_length, $from_end = false ) {
		$unwanted_array = array(
			'Š' => 'S',
			'š' => 's',
			'Ž' => 'Z',
			'ž' => 'z',
			'À' => 'A',
			'Á' => 'A',
			'Â' => 'A',
			'Ã' => 'A',
			'Ä' => 'A',
			'Å' => 'A',
			'Æ' => 'A',
			'Ç' => 'C',
			'È' => 'E',
			'É' => 'E',
			'Ê' => 'E',
			'Ë' => 'E',
			'Ì' => 'I',
			'Í' => 'I',
			'Î' => 'I',
			'Ï' => 'I',
			'Ñ' => 'N',
			'Ò' => 'O',
			'Ó' => 'O',
			'Ô' => 'O',
			'Õ' => 'O',
			'Ö' => 'O',
			'Ø' => 'O',
			'Ù' => 'U',
			'Ú' => 'U',
			'Û' => 'U',
			'Ü' => 'U',
			'Ý' => 'Y',
			'Þ' => 'B',
			'ß' => 'Ss',
			'à' => 'a',
			'á' => 'a',
			'â' => 'a',
			'ã' => 'a',
			'ä' => 'a',
			'å' => 'a',
			'æ' => 'a',
			'ç' => 'c',
			'è' => 'e',
			'é' => 'e',
			'ê' => 'e',
			'ë' => 'e',
			'ì' => 'i',
			'í' => 'i',
			'î' => 'i',
			'ï' => 'i',
			'ð' => 'o',
			'ñ' => 'n',
			'ò' => 'o',
			'ó' => 'o',
			'ô' => 'o',
			'õ' => 'o',
			'ö' => 'o',
			'ø' => 'o',
			'ù' => 'u',
			'ú' => 'u',
			'û' => 'u',
			'ý' => 'y',
			'þ' => 'b',
			'ÿ' => 'y',
		);
		$clean          = strtr( $text, $unwanted_array );
		if ( $from_end ) {
			return strlen( $clean ) > $max_length ? substr( $clean, -$max_length ) : $clean;
		} else {
			return strlen( $clean ) > $max_length ? substr( $clean, 0, $max_length ) : $clean;
		}
	}

	/**
	 * To xml.
	 *
	 * @param mixed                          $reference label.
	 * @param mixed                          $label label.
	 * @param WC_KShippingArgentina_Shipping $shipping shipping.
	 *
	 * @return string
	 */
	public static function oca_to_xml( $reference, $label, $shipping ) {
		self::init();
		$from_door = $shipping->find_in_store;
		$to_door   = ! $label['office'];

		$config = self::$config;

		$countries_obj = new WC_Countries();
		$states        = $countries_obj->get_states( 'AR' );

		$from_door_office = explode( '#', $label['office_src'] );
		if ( ! isset( $from_door_office[1] ) ) {
			$from_door_office = explode( '#', $shipping->office_src );
		}
		if ( ! isset( $from_door_office[1] ) ) {
			$origin_imposition_center_id = '';
		} else {
			$origin_imposition_center_id = $from_door_office[1];
		}
		$cost_center = $from_door ? '1' : '0';
		$idci        = ! $to_door ? (string) explode( '#', $label['office'] )[1] : '0';

		$xml = '<?xml version="1.0" encoding="iso-8859-1" standalone="yes"?>' . "\n" . '<ROWS>
	<cabecera ver="2.0" nrocuenta="' . self::to_ascii( $shipping->product_client, self::OCA_ACCOUNT_LENGTH ) . '" />
	<origenes>
		<origen  
			calle="' . self::to_ascii( $config['street'], self::OCA_STREET_LENGTH ) . '" 
			nro="' . self::to_ascii( $config['number'], self::OCA_NUMBER_LENGTH ) . '" 
			piso="' . self::to_ascii( $config['floor'], self::OCA_FLOOR_LENGTH ) . '" 
			depto="' . self::to_ascii( $config['apartment'], self::OCA_APARTMENT_LENGTH ) . '" 
			localidad="' . self::to_ascii( $config['city'], self::OCA_LOCALITY_LENGTH ) . '" 
			provincia="' . self::to_ascii( $states[ $config['state'] ], self::OCA_PROVINCE_LENGTH ) . '"
			idfranjahoraria="' . $config['time_slot'] . '"
			cp="' . $config['postcode'] . '"
			contacto="' . self::to_ascii( $config['fullname'], self::OCA_NAME_LENGTH ) . '" 
			email="' . $config['email'] . '"
			solicitante="' . self::to_ascii( $config['fullname'], self::OCA_NAME_LENGTH ) . '" 
			observaciones="' . self::to_ascii( $config['other'], self::OCA_OBSERVATIONS_LENGTH ) . '" 
			
			idcentroimposicionorigen="' . $origin_imposition_center_id . '"
			
			centrocosto="' . $cost_center . '"
			fecha="' . gmdate( 'Ymd', strtotime( '+1 day' ) ) . '" >
			<envios>
				<envio 
							idoperativa="' . self::to_ascii( $shipping->product_type, self::OCA_OPERATIVE_LENGTH ) . '" 
							nroremito="' . self::to_ascii( $reference, self::OCA_REMIT_LENGTH ) . '">
							<destinatario 
								apellido="' . self::to_ascii( $label['last_name'], self::OCA_NAME_LENGTH ) . '" 
								nombre="' . self::to_ascii( $label['first_name'], self::OCA_NAME_LENGTH ) . '" 
								calle="' . self::to_ascii( $label['address_1'], self::OCA_STREET_LENGTH ) . '" 
								nro="' . self::to_ascii( $label['number'], self::OCA_NUMBER_LENGTH ) . '" 
								piso="' . self::to_ascii( $label['floor'], self::OCA_FLOOR_LENGTH ) . '" 
								depto="' . self::to_ascii( $label['apartment'], self::OCA_APARTMENT_LENGTH ) . '" 
								localidad="' . self::to_ascii( $label['city'], self::OCA_LOCALITY_LENGTH ) . '" 
								provincia="' . self::to_ascii( $states[ $label['state'] ], self::OCA_PROVINCE_LENGTH ) . '" 
								cp="' . $label['postcode'] . '" 
								telefono="' . self::to_ascii( preg_replace( '/[^0-9]/', '', $label['other_phone'] ), self::OCA_PHONE_LENGTH ) . '" 
								email="' . self::to_ascii( $label['email'], self::OCA_EMAIL_LENGTH ) . '" 
								idci="' . $idci . '" 
								celular="' . self::to_ascii( preg_replace( '/[^0-9]/', '', $label['prefix_phone'] . $label['phone'] ), self::OCA_MOBILE_LENGTH ) . '" 
								observaciones="' . self::to_ascii( $label['address_2'], self::OCA_OBSERVATIONS_LENGTH ) . '"/>
								<paquetes>';

		foreach ( $label['box']['weight'] as $b_id => $weight ) {
			$xml .= '<paquete
				alto="' . self::to_ascii( $label['box']['height'][ $b_id ], self::OCA_ATTR_LENGTH ) . '"
				ancho="' . self::to_ascii( $label['box']['width'][ $b_id ], self::OCA_ATTR_LENGTH ) . '"
				largo="' . self::to_ascii( $label['box']['depth'][ $b_id ], self::OCA_ATTR_LENGTH ) . '"
				peso="' . self::to_ascii( $label['box']['weight'][ $b_id ], self::OCA_ATTR_LENGTH ) . '"
				valor="' . self::to_ascii( $label['box']['total'][ $b_id ], self::OCA_ATTR_LENGTH ) . '"
				cant="1" />';
		}

		$xml .= '</paquetes>
				</envio>
			</envios>
		</origen>
	</origenes>
</ROWS>';
		return $xml;
	}

	/**
	 * SOAP Client.
	 *
	 * @param string $tc Tracking code.
	 * @param mixed  $error error.
	 * @param bool   $sandbox sandbox.
	 *
	 * @return mixed
	 */
	public static function get_oca_pdf_label( $tc, &$error = null, $sandbox = false ) {
		$sandbox      = apply_filters( 'kshippingargentina_sandbox', $sandbox );
		$url          = 'http://webservice.oca.com.ar/' . ( $sandbox ? 'OEP_Tracking_TEST/Oep_Track.asmx' : 'oep_tracking/Oep_Track.asmx' ) . '/GetPdfDeEtiquetasPorOrdenOrNumeroEnvio';
		$query_string = array(
			'IdOrdenRetiro'    => '', // $id_retiro,
			'NroEnvio'         => $tc,
			'LogisticaInversa' => 'false',
		);
		$config       = array(
			'timeout' => 10,
			'headers' => array(),
			'body'    => http_build_query( $query_string ),
		);
		$data         = wp_remote_post( $url, $config );
		if ( ! is_wp_error( $data ) ) {
			try {
				$b64 = (string) @simplexml_load_string( $data['body'] );
			} catch ( Exception $e ) {
				$b64 = false;
			}
			if ( ! $b64 ) {
				self::debug( 'From API invalid XML: ', array( $url, $query_string, $data['body'] ) );
				if ( null !== $error ) {
					$error = 'Invalid XML: ' . $data['body'];
				}
				return false;
			}
			if ( strlen( $b64 ) < 100 ) {
				self::debug( 'From API invalid XML-len: ', array( $url, $query_string, $data['body'] ) );
				if ( null !== $error ) {
					$error = 'Invalid XML-len: ' . $data['body'];
				}
				return false;
			}
			return base64_decode( $b64 );
		} else {
			self::debug( 'From API error: ', array( $url, $query_string, $data ) );
			if ( null !== $error ) {
				$error = 'Request PDF error 500';
			}
		}
		return false;
	}

	/**
	 * Call soap service of OCA.
	 *
	 * @param string $method method.
	 * @param array  $params params.
	 * @param bool   $return_raw return_raw.
	 * @param string $force_url force_url.
	 * @param mixed  $error error.
	 * @param bool   $sandbox sandbox.
	 *
	 * @return mixed
	 */
	public static function call_oca( $method, $params = array(), $return_raw = false, $force_url = null, &$error = null, $sandbox = false ) {
		$sandbox  = apply_filters( 'kshippingargentina_sandbox', $sandbox );
		$services = array(
			'IngresoORMultiplesRetiros',
			'AnularOrdenGenerada',
			'Tracking_Pieza_ConIdEstado',
		);
		if ( ! in_array( $method, $services, true ) ) {
			self::debug( 'Request OCA services invalid:' . $method, $params );
			return false;
		}

		try {
			if ( $force_url ) {
				$url = $force_url;
			} else {
				$url = $sandbox ? self::OCA_API_SANDBOX : self::OCA_API_PROD;
			}
			$xml = false;
			try {
				self::debug( 'Request OCA ' . $url . ' - ' . $method, $params );
				$response = self::get_oca_soap_client( $url )->{$method}( $params );
				self::debug( 'Response ' . $url . ' - ' . $method, $response );
				if ( $return_raw ) {
					return $response->{$method . 'Result'};
				}
				if ( ! isset( $response->{$method . 'Result'}->any ) ) {
					self::debug( 'Error on ' . $method, $response->{$method . 'Result'} );
					if ( null !== $error ) {
						$error = (string) $response->{$method . 'Result'};
					}
					return false;
				}
				$xml = new SimpleXMLElement( $response->{$method . 'Result'}->any );
			} catch ( Exception $e ) {
				if ( null !== $error ) {
					$error = $e->getMessage();
				}
				self::debug( 'Error on ' . $method . ': ' . $e->getMessage() );
				return false;
			}

			if ( ! count( $xml->children() ) ) {
				self::debug( 'Error on ' . $method . ': No results from OCA webservice' );
				return false;
			}
			$data = json_decode( wp_json_encode( $xml ), true );
			self::debug( 'Response in array ' . $url . ' - ' . $method, $data );
			if ( isset( $data['NewDataSet'] ) && ! empty( $data['NewDataSet'] ) ) {
				return reset( $data['NewDataSet'] );
			} else {
				return reset( $data );
			}
		} catch ( Exception $e ) {
			if ( null !== $error ) {
				$error = $e->getMessage();
			}
			self::debug( 'Error2 on ' . $method . ': ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * SOAP Client.
	 *
	 * @param string $url url.
	 *
	 * @return SoapClient
	 */
	private static function get_oca_soap_client( $url ) {
		self::init();
		if ( isset( self::$oca_clients[ $url ] ) ) {
			return self::$oca_clients[ $url ];
		}
		self::$oca_clients[ $url ] = new SoapClient(
			$url,
			array(
				'trace'      => self::$show_debug,
				'exceptions' => 1,
				'cache_wsdl' => 0,
			)
		);
		return self::$oca_clients[ $url ];
	}

	/**
	 * Write in log.
	 *
	 * @param string $message Message.
	 * @param string $data Data.
	 *
	 * @return void
	 */
	public static function debug( $message, $data = false ) {
		self::init();
		if ( self::$show_debug && is_string( $message ) && ! empty( $message ) ) {
			$logger  = wc_get_logger();
			$context = array( 'source' => 'carriers-of-argentina-for-woocommerce' );
			$logger->debug( $message . ( $data ? ' DATA->#' . wp_json_encode( $data ) . '#' : '' ), $context );
		}
	}

	// ##################################################################
	// ##################################################################
	// ##################################################################
	// ##################################################################

	// ############################ IS CACHE ############################

	// ##################################################################
	// ##################################################################
	// ##################################################################
	// ##################################################################

	/**
	 * Get cache data.
	 *
	 * @param string $cache_id Cache ID.
	 *
	 * @return mixed
	 */
	public static function get_cache( $cache_id, $force_local_cache = true ) {
		$data = false;
		if ( $force_local_cache && isset( self::$module_cache[ $cache_id ] ) && self::$module_cache[ $cache_id ] ) {
			$data = self::$module_cache[ $cache_id ];
			return $data;
		}
		$wpdb = WC_KShippingArgentina::woocommerce_wpdb();
		$d    = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `data`, `ttl` FROM
                    {$wpdb->prefix}kshippingargentina_cache
                WHERE
                    `cache_id` = %s
                LIMIT 1",
				$cache_id
			)
		);
		if ( $d && isset( $d[0] ) && isset( $d[0]->ttl ) ) {
			if ( $d[0]->ttl < time() ) {
				$d = false;
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}kshippingargentina_cache WHERE ttl < %d OR cache_id = %s",
						time(),
						$cache_id
					)
				);
			} else {
				$d = $d[0]->data;
			}
		} else {
			$d = false;
		}
		if ( $d ) {
			$data = json_decode( $d, true );
		}
		return $data;
	}

	/**
	 * Set cache data.
	 *
	 * @param string $cache_id Cache ID.
	 * @param mixed  $value Data.
	 * @param int    $ttl Time in seconds for this cache data.
	 *
	 * @return mixed
	 */
	public static function set_cache( $cache_id, $value, $ttl = 21600 ) {
		$wpdb                            = WC_KShippingArgentina::woocommerce_wpdb();
		$table_name                      = $wpdb->prefix . 'kshippingargentina_cache';
		self::$module_cache[ $cache_id ] = $value;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}kshippingargentina_cache WHERE ttl < %d OR cache_id = %s",
				time(),
				$cache_id
			)
		);
		$wpdb->insert(
			$table_name,
			array(
				'cache_id' => $cache_id,
				'data'     => wp_json_encode( $value ),
				'ttl'      => time() + $ttl,
			)
		);
	}
}
