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

	const TRACKING_URL_OCA      = 'https://www1.oca.com.ar/OEPTrackingWeb/trackingenvio.asp?numero1=@';
	const TRACKING_URL_ANDREANI = '@';
	const TRACKING_URL_CORREO   = '@';

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
				'usr'           => self::$config['user'],
				'psw'           => self::$config['pass'],
				'IdOrdenRetiro' => (int) $tc,
			),
			false,
			null,
			$error
		);
		if ( ! $result ) {
			return false;
		}
		return 100 === (int) $result->IdResult;
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
		$clean = trim( htmlspecialchars( iconv( 'utf-8', 'ascii//TRANSLIT', str_replace( '"', '', $text ) ) ) );
		if ( strpos( $clean, '?' ) !== false ) {
			setlocale( LC_TIME, 'es_ES' );
			$clean = trim( htmlspecialchars( iconv( 'utf-8', 'ascii//TRANSLIT', str_replace( '"', '', $text ) ) ) );
		}

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

		if ( $from_door ) {
			$origin_imposition_center_id = false;
		} else {
			$origin_imposition_center_id = $label['office_src'];
		}
		$cost_center = $from_door ? '1' : '0';
		$idci        = ! $to_door ? (string) $label['office'] : '0';

		$xml = '<?xml version="1.0" encoding="iso-8859-1" standalone="yes"?>' . "\n" . '<ROWS>
			<cabecera ver="2.0" nrocuenta=""/>
			<origenes>
				<origen 
					calle="' . self::to_ascii( $config['street'], self::OCA_STREET_LENGTH ) . '" 
					nro="' . self::to_ascii( $config['number'], self::OCA_NUMBER_LENGTH ) . '" 
					piso="' . self::to_ascii( $config['floor'], self::OCA_FLOOR_LENGTH ) . '" 
					depto="' . self::to_ascii( $config['apartment'], self::OCA_APARTMENT_LENGTH ) . '" 
					localidad="' . self::to_ascii( $config['city'], self::OCA_LOCALITY_LENGTH ) . '" 
					provincia="' . self::to_ascii( $states[ $config['state'] ], self::OCA_PROVINCE_LENGTH ) . '"
					' . ( $from_door ? 'idfranjahoraria="' . $config['time_slot'] . '" ' : 'idcentroimposicionorigen="' . $origin_imposition_center_id . '"' ) . '
					cp="' . $config['postcode'] . '"
					contacto="' . self::to_ascii( $config['fullname'], self::OCA_NAME_LENGTH ) . '" 
					email="' . $config['email'] . '"
					solicitante="' . self::to_ascii( $config['fullname'], self::OCA_NAME_LENGTH ) . '" 
					observaciones="" 
					centrocosto="' . $cost_center . '" 
					fecha="' . gmdate( 'Ymd', strtotime( '+1 day' ) ) . '">
					<envios>
						<envio 
							idoperativa="' . self::to_ascii( $shipping->product_type, self::OCA_OPERATIVE_LENGTH ) . '" 
							nroremito="' . $reference . '">
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
								telefono="' . preg_replace( '/[^0-9]/', '', $label['other_phone'] ) . '" 
								email="' . $label['email'] . '" 
								idci="' . $idci . '" 
								celular="' . preg_replace( '/[^0-9]/', '', $label['prefix_phone'] . $label['phone'] ) . '" 
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
	 * @param bool   $sandbox sandbox.
	 *
	 * @return mixed
	 */
	public static function get_oca_pdf_label( $tc, $sandbox = true ) {
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
			$b64 = (string) simplexml_load_string( $data['body'] );
			if ( ! $b64 ) {
				self::debug( 'From API invalid XML: ', array( $url, $query_string, $data['body'] ) );
				return false;
			}
			if ( strlen( $b64 ) < 100 ) {
				self::debug( 'From API invalid XML-len: ', array( $url, $query_string, $data['body'] ) );
			}
			return base64_decode( $b64 );
		} else {
			self::debug( 'From API error: ', array( $url, $query_string, $data ) );
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
	public static function call_oca( $method, $params = array(), $return_raw = false, $force_url = null, &$error = null, $sandbox = true ) {
		$services = array(
			'IngresoORMultiplesRetiros',
			'AnularOrdenGenerada',
			'Tracking_Pieza_ConIdEstado',
		);
		if ( ! in_array( $method, $services, true ) ) {
			self::debug( 'Request OCA services invalid:' . $method, $params );
			return false;
		}
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

		if ( property_exists( $xml, 'NewDataSet' ) ) {
			$result = reset( $xml->NewDataSet );
		} else {
			$result = reset( $xml );
		}

		return $result;
	}

	/**
	 * SOAP Client.
	 *
	 * @param string $url url.
	 *
	 * @return SoapClient
	 */
	private static function get_oca_soap_client( $url ) {
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
		$data = false;
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