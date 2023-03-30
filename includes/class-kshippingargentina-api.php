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
		$offices = self::call( "/offices/postcode/$service/$postcode", false, 3600 * 24 * 7 );
		$return  = array();
		foreach ( $offices as $office ) {
			if ( ! $sender || isset( $office['is_sender'] ) && $office['is_sender'] ) {
				if ( ! $receiver || isset( $office['is_receiver'] ) && $office['is_receiver'] ) {
					$return[ $office['iso'] ] = $office;
				}
			}
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
		$cities = self::call( "/cities/states/$state", false, 3600 * 24 * 31 );
		$return = array();
		foreach ( $cities as $city ) {
			$return[ $city['name'] ] = $city['name'];
		}
		return count( $return ) > 0 ? $return : array( '' => __( 'Cities not found...', 'wc-kshippingargentina' ) );
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
						if ( $cost['fiscalType'] === $fiscal_type ) {
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
		if ( 'error' === $result ) {
			return false;
		} elseif ( ! empty( $result ) ) {
			$api_arr = json_decode( $result, true );
			if ( $api_arr ) {
				if ( isset( $api_arr['statusCode'] ) && ( $api_arr['statusCode'] < 200 || $api_arr['statusCode'] > 299 ) ) {
					return false;
				}
				// self::debug( 'From CACHE: ', array( $url ) ); //From cache.
				return $api_arr;
			}
			return false;
		}
		$config = array(
			'timeout' => 10,
			'headers' => array(
				'X-RapidAPI-Host' => self::$config['api_host'],
				'X-RapidAPI-Key'  => self::$config['api_key'],
			),
		);
		if ( $post_data ) {
			$config['body']                    = $post_data;
			$config['headers']['Content-Type'] = 'application/json; charset=utf-8';
			$data                              = wp_remote_post( $url, $config );
		} else {
			$data = wp_remote_get( $url, $config );
		}
		if ( ! is_wp_error( $data ) ) {
			self::debug( 'From API: ', array( $url, $post_data, $data['body'] ) );
			self::set_cache( $cache_id, $data['body'], $ttl );
			$api_arr = json_decode( $data['body'], true );
			if ( $api_arr ) {
				if ( isset( $api_arr['statusCode'] ) && ( $api_arr['statusCode'] < 200 || $api_arr['statusCode'] > 299 ) ) {
					self::set_cache( $cache_id, 'error', 3600 );
					return false;
				}
				return $api_arr;
			}
			self::set_cache( $cache_id, 'error', 3600 );
			return false;
		} else {
			self::debug( 'From API error: ', array( $url, $post_data, $data ) );
		}
		self::set_cache( $cache_id, 'error', 3600 );
		return false;
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
			$context = array( 'source' => 'wc-kshippingargentina' );
			$logger->debug( $message . ( $data ? ' DATA->#' . wp_json_encode( $data ) . '#' : '' ), $context );
		}
	}

	/**
	 * Get cache data.
	 *
	 * @param string $cache_id Cache ID.
	 *
	 * @return mixed
	 */
	public static function get_cache( $cache_id ) {
		$data      = false;
		// $cache_id .= '_' . WC_KShippingArgentina::VERSION;
		if ( isset( self::$module_cache[ $cache_id ] ) && self::$module_cache[ $cache_id ] ) {
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
		// $cache_id                       .= '_' . WC_KShippingArgentina::VERSION;
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
