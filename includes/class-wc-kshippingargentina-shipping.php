<?php
/**
 * WC_KShippingArgentina_Shipping Class
 *
 * @package Kijam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_KShippingArgentina_Shipping' ) ) :
	/**
	 * Shipping class.
	 *
	 * @since 1.0.0
	 * @extends WC_Shipping_Gateway_KShippingArgentina
	 */
	class WC_KShippingArgentina_Shipping extends WC_Shipping_Method {
		/**
		 * Instance.
		 *
		 * @var WC_KShippingArgentina_Shipping
		 */
		private static $instance = null;

		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $service_type;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $type;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $find_in_store;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $product_type;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $office;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $office_src;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $insurance;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $insurance_active;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $product_cuit;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $product_client;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $velocity;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $fiscal_type;

		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $delay;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $hide_delay;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $free_shipping;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $free_shipping_amount;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $free_shipping_weight;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $shipping_fee;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $shipping_fee_percent;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $min_shipping_amount;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $max_shipping_amount;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $discount_shipping_min_amount;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $discount_shipping_min_weight;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $discount_shipping_percent;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $discount_shipping_amount;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $exclude_state;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $exclude_categories;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $exclude_products;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $exclude_zipcode;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $activated_min_amount;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $activated_max_amount;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $activated_min_weight;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $activated_max_weight;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $invalid_ranges;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $free_shipping_mode;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $free_shipping_state;
		/**
		 * Shipping attribute.
		 *
		 * @var mixed
		 */
		public $free_shipping_zipcode;
		/**
		 * Constructor
		 *
		 * @param int $instance_id Instance ID of Shipping.
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'kshippingargentina-shipping';
			$this->instance_id        = absint( $instance_id );
			$this->method_title       = __( 'Correo Argentino/Andreani/OCA e-Pak', 'carriers-of-argentina-for-woocommerce' );
			$this->method_description = __( 'Use a shipping company from Argentina in this area', 'carriers-of-argentina-for-woocommerce' );
			$this->supports           = array(
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			);
			$this->init();
			self::$instance[ $this->instance_id ] = $this;
		}

		/**
		 * Get instance
		 *
		 * @param int $instance_id Instance ID of Shipping.
		 *
		 * @return WC_KShippingArgentina_Shipping
		 */
		public static function get_instance( $instance_id = 0 ) {
			$id = absint( $instance_id );
			if ( ! isset( self::$instance[ $instance_id ] ) ) {
				return new WC_KShippingArgentina_Shipping( $instance_id );
			}
			return self::$instance[ $id ];
		}

		/**
		 * Init function.
		 */
		private function init() {
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables.
			$this->title = $this->get_option( 'title', $this->method_title );

			$this->service_type     = $this->get_option( 'service_type' );
			$this->product_type     = $this->get_option( 'product_type' );
			$this->type             = $this->get_option( 'type' );
			$this->office           = 'office' === $this->type;
			$this->office_src       = $this->get_option( 'office_src' );
			$this->insurance_active = 'yes' === $this->get_option( 'insurance_active' );
			$this->find_in_store    = 'yes' === $this->get_option( 'find_in_store' );
			$this->insurance        = (float) $this->get_option( 'insurance' );
			$this->velocity         = $this->get_option( 'velocity', 'classic' );
			$this->fiscal_type      = $this->get_option( 'fiscal_type', 'CF' );
			$this->product_cuit     = $this->get_option( 'product_cuit' );
			$this->product_client   = $this->get_option( 'product_client' );

			$this->delay                        = $this->get_option( 'delay' );
			$this->hide_delay                   = 'yes' === $this->get_option( 'hide_delay' );
			$this->free_shipping                = 'yes' === $this->get_option( 'free_shipping' );
			$this->free_shipping_amount         = (float) $this->get_option( 'free_shipping_amount' );
			$this->free_shipping_weight         = (float) $this->get_option( 'free_shipping_weight' );
			$this->shipping_fee                 = (float) $this->get_option( 'shipping_fee' );
			$this->shipping_fee_percent         = (float) $this->get_option( 'shipping_fee_percent' );
			$this->min_shipping_amount          = (float) $this->get_option( 'min_shipping_amount' );
			$this->max_shipping_amount          = (float) $this->get_option( 'max_shipping_amount' );
			$this->discount_shipping_min_amount = (float) $this->get_option( 'discount_shipping_min_amount' );
			$this->discount_shipping_min_weight = (float) $this->get_option( 'discount_shipping_min_weight' );
			$this->discount_shipping_percent    = (float) $this->get_option( 'discount_shipping_percent' );
			$this->discount_shipping_amount     = (float) $this->get_option( 'discount_shipping_amount' );

			$this->exclude_state       = array_filter(
				(array) $this->get_option( 'exclude_state' ),
				function ( $element ) {
					return 'dummy_delete' !== $element && ! empty( trim( $element ) );
				}
			);
			$this->exclude_categories  = array_filter(
				(array) $this->get_option( 'exclude_categories' ),
				function ( $element ) {
					return 'dummy_delete' !== $element && ! empty( trim( $element ) );
				}
			);
			$this->free_shipping_state = array_filter(
				(array) $this->get_option( 'free_shipping_state' ),
				function ( $element ) {
					return 'dummy_delete' !== $element && ! empty( trim( $element ) );
				}
			);

			$this->exclude_products      = (array) $this->get_option( 'exclude_products' );
			$this->exclude_zipcode       = array();
			$this->activated_min_amount  = $this->get_option( 'activated_min_amount' );
			$this->activated_max_amount  = $this->get_option( 'activated_max_amount' );
			$this->activated_min_weight  = $this->get_option( 'activated_min_weight' );
			$this->activated_max_weight  = $this->get_option( 'activated_max_weight' );
			$this->invalid_ranges        = array();
			$this->free_shipping_mode    = $this->get_option( 'free_shipping_mode' );
			$this->free_shipping_zipcode = array();

			// Re-Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Checks that the currency is supported.
			$currency = get_woocommerce_currency();
			if ( ! in_array( $currency, array( 'ARS', 'USD' ), true ) ) {
				add_action( 'admin_notices', array( $this, 'currency_not_supported_message' ) );
			}
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

			$exclude_categories       = apply_filters( 'kshippingargentina_exclude_categories', is_array( $this->exclude_categories ) ? $this->exclude_categories : array(), $this );
			$this->exclude_categories = array();
			foreach ( $exclude_categories as $ids ) {
				$id = explode( '-', $ids );
				if ( ! isset( $id[1] ) || ! (int) $id[1] ) {
					$this->exclude_categories[] = (int) $id[0];
				} else {
					$this->exclude_categories[] = (int) $id[1];
				}
			}

			$this->exclude_products = apply_filters( 'kshippingargentina_exclude_products', is_array( $this->exclude_products ) ? $this->exclude_products : array(), $this );
			foreach ( $this->exclude_products as &$id ) {
				$id = (int) $id;
			}
		}

		/**
		 * Determina si esta disponible
		 *
		 * @param mixed $package Package.
		 *
		 * @return bool
		 */
		public function is_available( $package ) {
			// Test if is valid for use.
			$currency = get_woocommerce_currency();
			return in_array( $currency, array( 'ARS', 'USD' ), true );
		}

		/**
		 * Write in log.
		 *
		 * @param string $message Message.
		 * @param string $data Data.
		 *
		 * @return void
		 */
		public static function debug( $message, $data = null ) {
			KShippingArgentina_API::debug( $message, $data );
		}


		/**
		 * Build query function.
		 *
		 * @param array $params Params.
		 */
		private function build_query( $params ) {
			if ( function_exists( 'http_build_query' ) ) {
				return http_build_query( $params );
			} else {
				foreach ( $params as $name => $value ) {
					$elements[] = "{$name}=" . rawurlencode( $value );
				}

				return implode( '&', $elements );
			}
		}

		/**
		 * Environment_check function.
		 */
		private function environment_check() {
			if ( ! in_array( WC_KShippingArgentina::woocommerce_instance()->countries->get_base_country(), array( 'AR' ), true ) ) {
				echo '<div class="error">
					<p>' . esc_html( __( 'Argentina have to be the country of origin.', 'carriers-of-argentina-for-woocommerce' ) ) . '</p>
				</div>';
			}
		}

		/**
		 * Admin options function.
		 */
		public function admin_options() {
			// Check users environment supports this method.
			$this->environment_check();

			// Show settings.
			parent::admin_options();
		}

		/**
		 * Init categories fields function.
		 */
		public function get_categories() {
			$taxonomy       = 'product_cat';
			$orderby        = 'name';
			$show_count     = 0;      // 1 for yes, 0 for no
			$pad_counts     = 0;      // 1 for yes, 0 for no
			$hierarchical   = 1;      // 1 for yes, 0 for no
			$title          = '';
			$empty          = 0;
			$result         = array();
			$args           = array(
				'taxonomy'     => $taxonomy,
				'orderby'      => $orderby,
				'show_count'   => $show_count,
				'pad_counts'   => $pad_counts,
				'hierarchical' => $hierarchical,
				'title_li'     => $title,
				'hide_empty'   => $empty,
			);
			$all_categories = get_categories( $args );
			foreach ( $all_categories as $cat ) {
				if ( ! $cat->category_parent ) {
					$category_id                   = $cat->term_id;
					$result[ $category_id . '-0' ] = $cat->name;
					$args2                         = array(
						'taxonomy'     => $taxonomy,
						'child_of'     => 0,
						'parent'       => $category_id,
						'orderby'      => $orderby,
						'show_count'   => $show_count,
						'pad_counts'   => $pad_counts,
						'hierarchical' => $hierarchical,
						'title_li'     => $title,
						'hide_empty'   => $empty,
					);
					$sub_cats                      = get_categories( $args2 );
					if ( $sub_cats ) {
						foreach ( $sub_cats as $sub_category ) {
							$subcategory_id                                 = $sub_category->term_id;
							$result[ $category_id . '-' . $subcategory_id ] = ' -> ' . $sub_category->name;
						}
					}
				}
			}
			return $result;
		}

		/**
		 * Init form fields function.
		 */
		public function init_form_fields() {

			$this->instance_form_fields = include 'data-settings-shipping.php';

			$countries_obj = new WC_Countries();
			$this->instance_form_fields['exclude_categories']['options']  = $this->get_categories();
			$this->instance_form_fields['exclude_state']['options']       = $countries_obj->get_states( 'AR' );
			$this->instance_form_fields['free_shipping_state']['options'] = $countries_obj->get_states( 'AR' );
		}

		/**
		 * Get dimension function.
		 *
		 * @param array  $dim Dimension.
		 * @param string $to_unit Unit.
		 */
		public static function get_dimension( $dim, $to_unit ) {
			if ( function_exists( 'wc_get_dimension' ) ) {
				return wc_get_dimension( $dim, $to_unit );
			}
			return woocommerce_get_dimension( $dim, $to_unit );
		}

		/**
		 * Get weight function.
		 *
		 * @param array  $dim weight.
		 * @param string $to_unit Unit.
		 */
		public static function get_weight( $dim, $to_unit ) {
			if ( function_exists( 'wc_get_weight' ) ) {
				return wc_get_weight( $dim, $to_unit );
			}
			return woocommerce_get_weight( $dim, $to_unit );
		}
		/**
		 * Box shipping calculation function.
		 *
		 * @param array $packages Packages.
		 *
		 * @return array
		 */
		public static function box_shipping( $packages ) {
			$setting            = get_option( 'woocommerce_kshippingargentina-manager_settings' );
			$default_dimensions = array( self::get_dimension( $setting['width'], 'cm' ), self::get_dimension( $setting['height'], 'cm' ), self::get_dimension( $setting['depth'], 'cm' ) );
			$result_box         = array(
				'width'    => array(),
				'height'   => array(),
				'depth'    => array(),
				'weight'   => array(),
				'items'    => array(),
				'total'    => array(),
				'total_wt' => array(),
				'content'  => array(),
			);
			$products           = array();
			foreach ( $packages as $item_id => $values ) {
				$sku = $item_id;
				if ( $values['weight'] ) {
					$weight_p = self::get_weight( $values['weight'], 'kg' );
				} else {
					$weight_p = self::get_weight( $setting['weight'], 'kg' );
				}
				$product = array(
					'sku'    => $sku,
					'weight' => round( $weight_p, 2 ),
					'width'  => round( $default_dimensions[0], 2 ),
					'height' => round( $default_dimensions[1], 2 ),
					'depth'  => round( $default_dimensions[2], 2 ),
					'qty'    => (int) $values['quantity'],
				);
				if ( $values['length'] > 0 && $values['width'] > 0 && $values['height'] > 0 ) {
					$product['width']  = round( self::get_dimension( $values['width'], 'cm' ), 2 );
					$product['height'] = round( self::get_dimension( $values['height'], 'cm' ), 2 );
					$product['depth']  = round( self::get_dimension( $values['length'], 'cm' ), 2 );
				}
				$products[] = $product;
			}
			$all_boxes_for_bin = kshipping_argentina_boxes();
			if ( ! count( $all_boxes_for_bin ) ) {
				$all_boxes_for_bin[] = array(
					'width'     => 1,
					'height'    => 1,
					'depth'     => 1,
					'maxWeight' => 0.01,
				);
			}
			$boxes = KShippingArgentina_API::call(
				'/bins/calculate',
				array(
					'boxes'    => $all_boxes_for_bin,
					'products' => $products,
				),
				3600 * 24 * 120
			);
			if ( ! $boxes || ! is_array( $boxes ) ) {
				return false;
			}
			foreach ( $boxes as $box ) {
				if ( ! isset( $box['products'] ) || ! is_array( $box['products'] ) || ! count( $box['products'] ) ) {
					KShippingArgentina_API::debug( 'Invalid Box: ', $box );
					continue;
				}
				$result_box['width'][]  = $box['box']['width'];
				$result_box['height'][] = $box['box']['height'];
				$result_box['depth'][]  = $box['box']['depth'];
				$result_box['weight'][] = round( $box['weight'], 2 );
				$result_box['items'][]  = count( $box['products'] );
				$contents               = array();
				$total                  = 0;
				$total_wt               = 0;
				foreach ( $box['products'] as $item_id ) {
					$total     += ( $packages[ $item_id ]['line_subtotal'] ) / $packages[ $item_id ]['quantity'];
					$total_wt  += ( $packages[ $item_id ]['line_subtotal'] + $packages[ $item_id ]['line_subtotal_tax'] ) / $packages[ $item_id ]['quantity'];
					$contents[] = $packages[ $item_id ]['name'];
				}
				$result_box['total'][]    = round( $total, 2 );
				$result_box['total_wt'][] = round( $total_wt, 2 );
				$result_box['content'][]  = implode( ', ', array_unique( $contents ) );
			}
			if ( ! count( $result_box['width'] ) ) {
				return false;
			}
			KShippingArgentina_API::debug( 'Result Boxes: ', $result_box );
			return apply_filters( 'kshippingargentina_box_shipping', $result_box, $packages );
		}

		/**
		 * Calculate shipping function.
		 *
		 * @param array $package Package.
		 */
		public function calculate_shipping( $package = array() ) {
			if ( ! WC_KShippingArgentina_Manager::get_instance()->is_available() ) {
				KShippingArgentina_API::debug( 'not is_available' );
				return;
			}
			$setting            = get_option( 'woocommerce_kshippingargentina-manager_settings' );
			$total_weight       = 0;

			$packages = array();
			foreach ( $package['contents'] as $item_id => &$p ) {
				if ( ! $p['data']->needs_shipping() ) {
					// translators: %s Product ID.
					KShippingArgentina_API::debug( sprintf( __( 'Product #%s is virtual. Skipping.', 'carriers-of-argentina-for-woocommerce' ), $item_id ), 'error' );
					continue;
				}
				$r               = array();
				$r['product_id'] = $p['product_id'];
				if ( in_array( (int) $r['product_id'], $this->exclude_products, true ) ) {
					return; // Este producto esta excluido para ser usado por Shipping Argentina.
				}
				$cats = get_the_terms( $r['product_id'], 'product_cat' );
				if ( $cats && is_array( $cats ) && count( $cats ) > 0 ) {
					foreach ( $cats as $term ) {
						if ( in_array( (int) $term->term_id, $this->exclude_categories, true ) ) {
							return; // Esta categoria esta excluida para ser usado por Shipping Argentina.
						}
					}
				}
				$r['variation_id']      = isset( $p['variation_id'] ) ? $p['variation_id'] : false;
				$r['quantity']          = $p['quantity'];
				$r['name']              = $p['data']->get_name();
				$r['line_total']        = $p['line_total'];
				$r['line_tax']          = $p['line_tax'];
				$r['line_subtotal']     = $p['line_subtotal'] / $p['quantity'];
				$r['line_subtotal_tax'] = $p['line_subtotal_tax'] / $p['quantity'];
				if ( isset( $r['variation_id'] ) && $r['variation_id'] ) {
					$r['weight'] = (float) get_metadata( 'post', $p['variation_id'], '_weight', true );
					$r['length'] = (float) get_metadata( 'post', $p['variation_id'], '_length', true );
					$r['width']  = (float) get_metadata( 'post', $p['variation_id'], '_width', true );
					$r['height'] = (float) get_metadata( 'post', $p['variation_id'], '_height', true );
					if ( $r['weight'] < 0.001 ) {
						$r['weight'] = (float) get_metadata( 'post', $p['product_id'], '_weight', true );
					}
					if ( $r['length'] < 0.001 ) {
						$r['length'] = (float) get_metadata( 'post', $p['product_id'], '_length', true );
					}
					if ( $r['width'] < 0.001 ) {
						$r['width'] = (float) get_metadata( 'post', $p['product_id'], '_width', true );
					}
					if ( $r['height'] < 0.001 ) {
						$r['height'] = (float) get_metadata( 'post', $p['product_id'], '_height', true );
					}
				} else {
					$r['weight'] = (float) get_metadata( 'post', $p['product_id'], '_weight', true );
					$r['length'] = (float) get_metadata( 'post', $p['product_id'], '_length', true );
					$r['width']  = (float) get_metadata( 'post', $p['product_id'], '_width', true );
					$r['height'] = (float) get_metadata( 'post', $p['product_id'], '_height', true );
				}
				$packages[ $item_id ] = $r;
			}
			if ( ! WC()->session ) {
				WC()->session = new WC_Session_Handler();
				WC()->session->init();
			}
			KShippingArgentina_API::debug( 'Destination: ', $package['destination'] );
			$dim = self::box_shipping( $packages );
			if ( ! $dim ) {
				return;
			}
			$delivery_postcode = $package['destination']['postcode'];
			$delivery_state    = $package['destination']['state'];
			$delivery_city     = $package['destination']['city'];
			$delivery_ofi      = WC_KShippingArgentina::woocommerce_instance()->checkout->get_value( 'office_kshippingargentina' );
			$order_price       = $package['contents_cost'];
			$free_shipping     = false;
			if ( $this->free_shipping ) {
				$coupon_free_shipping = false;
				$woocommerce          = WC_KShippingArgentina::woocommerce_instance();
				if ( isset( $woocommerce->cart ) && isset( $woocommerce->cart->applied_coupons ) && is_array( $woocommerce->cart->applied_coupons ) && ! empty( $woocommerce->cart->applied_coupons ) ) {
					foreach ( $woocommerce->cart->applied_coupons as $id_coupon ) {
						$coupon = new WC_Coupon( $id_coupon );
						if ( $coupon->enable_free_shipping() ) {
							$coupon_free_shipping = true;
							break;
						}
					}
				}
				switch ( $this->free_shipping_mode ) {
					case 'coupon':
						if ( $coupon_free_shipping ) {
							$free_shipping = true;
							KShippingArgentina_API::debug(
								'Testing excludes coupon_free_shipping true'
							);
						}
						KShippingArgentina_API::debug(
							'Testing excludes coupon_free_shipping false1'
						);
						break;
					case 'automatic_coupon':
						if ( ! $coupon_free_shipping ) {
							KShippingArgentina_API::debug(
								'Testing excludes coupon_free_shipping false2'
							);
							break;
						}
						// Intensional no-break.
					case 'semiautomatic_coupon':
					case 'automatic':
					default:
						$free_shipping = true;
						if ( isset( $package['destination']['state'] ) &&
							is_array( $this->free_shipping_state ) && count( $this->free_shipping_state ) > 0 &&
							! in_array( $package['destination']['state'], $this->free_shipping_state, true ) ) {
							$free_shipping = false;
							KShippingArgentina_API::debug(
								'Testing excludes free_shipping state: ' . $package['destination']['state'] . ' not in ' . wp_json_encode( $this->free_shipping_state )
							);
						}
						if ( $order_price < $this->free_shipping_amount ) {
							$free_shipping = false;
							KShippingArgentina_API::debug(
								"Testing excludes free_shipping free_shipping_amount: $order_price < {$this->free_shipping_amount}"
							);
						}
						if ( $total_weight < $this->free_shipping_weight ) {
							$free_shipping = false;
							KShippingArgentina_API::debug(
								"Testing excludes free_shipping free_shipping_amount: $total_weight < {$this->free_shipping_weight}"
							);
						}
						break;
				}
				if ( ! $free_shipping && $coupon_free_shipping && 'semiautomatic_coupon' === $this->free_shipping_mode ) {
					$free_shipping = true;
				}
				KShippingArgentina_API::debug(
					"Testing excludes free_shipping[{$this->title}]: ! $free_shipping && $coupon_free_shipping && 'semiautomatic_coupon' === {$this->free_shipping_mode} => $free_shipping"
				);
			} else {
				KShippingArgentina_API::debug(
					"Testing excludes free_shipping[{$this->title}]: disabled"
				);
			}
			KShippingArgentina_API::debug(
				"Testing excludes:
				total_weight: $total_weight (ref: {$this->activated_min_weight} - {$this->activated_max_weight})
				order_price: $order_price (ref: {$this->activated_min_amount} - {$this->activated_max_amount})
				exclude_state: {$package['destination']['state']} (ref: " . wp_json_encode( $this->exclude_state, true ) . ')
			'
			);
			if ( $this->activated_min_weight > 0 && $total_weight < $this->activated_min_weight ) {
				return; // Excluido por peso muy bajo.
			}
			KShippingArgentina_API::debug( 'activated_min_weight-> false' );
			if ( $this->activated_max_weight > 0 && $total_weight > $this->activated_max_weight ) {
				return; // Excluido por peso muy alto.
			}
			KShippingArgentina_API::debug( 'activated_max_weight-> false' );
			if ( $this->activated_min_amount > 0 && $order_price < $this->activated_min_amount ) {
				return; // Excluido por precio muy bajo.
			}
			KShippingArgentina_API::debug( 'activated_min_amount-> false' );
			if ( $this->activated_max_amount > 0 && $order_price > $this->activated_max_amount ) {
				return; // Excluido por precio muy alto.
			}
			KShippingArgentina_API::debug( 'activated_max_amount-> false' );
			if ( isset( $package['destination']['state'] ) &&
				is_array( $this->exclude_state ) && count( $this->exclude_state ) > 0 &&
				in_array( $package['destination']['state'], $this->exclude_state, true ) ) {
				return; // Este estado esta excluido para ser usado por Shipping Argentina.
			}
			$to_ars   = WC_KShippingArgentina_Manager::get_instance()->get_conversion_rate( get_woocommerce_currency(), 'ARS' );
			$from_ars = WC_KShippingArgentina_Manager::get_instance()->get_conversion_rate( 'ARS', get_woocommerce_currency() );

			$cp = preg_replace( '/[^0-9]/', '', $delivery_postcode );
			KShippingArgentina_API::debug( 'bultos to delivery_state-> ', $delivery_state );
			KShippingArgentina_API::debug( 'bultos to delivery_ofi-> ', $delivery_ofi );
			KShippingArgentina_API::debug( 'bultos to delivery_city-> ', $delivery_city );
			KShippingArgentina_API::debug( 'bultos to delivery_postcode-> ', $delivery_postcode );
			KShippingArgentina_API::debug( 'bultos to cp-> ', $cp );
			if ( ! empty( $cp ) ) {
				$insurance     = 0;
				$cost          = 0;
				$list_packages = array();
				foreach ( $dim['items'] as $idp => $name ) {
					$line_price        = (int) round( $to_ars * $dim['total'][ $idp ], 0 );
					$b                 = array(
						'width'  => max( 5.0, $dim['width'][ $idp ] ),
						'height' => max( 5.0, $dim['height'][ $idp ] ),
						'depth'  => max( 5.0, $dim['depth'][ $idp ] ),
					);
					$weight            = max( 0.1, $dim['weight'][ $idp ] );
					$weight_v          = $b['width'] * $b['height'] * $b['depth'];
					$b['volume']       = $weight_v;
					$b['weight']       = $weight;
					$b['product_cost'] = $this->insurance_active ? $line_price : 100;
					$list_packages[]   = $b;
					if ( $this->insurance_active && 'correo_argentino' === $this->service_type ) {
						$insurance += $line_price * ( $this->insurance / 100.0 );
					}
				}
				$quote = KShippingArgentina_API::get_quote( $this->service_type, $list_packages, $this->office_src, $setting['postcode'], $cp, $this->product_cuit, $this->product_type, $this->product_client, $this->fiscal_type, $this->type, $this->velocity );
				if ( ! $quote ) {
					return;
				}
				$cost = $quote['total'] + $insurance;
				if ( $quote['delay'] > 0 ) {
					// translators: Min and max days of delay.
					$delay = sprintf( __( '%1$d to %2$d days', 'carriers-of-argentina-for-woocommerce' ), $quote['delay'], $quote['delay'] + 2 );
				} else {
					$delay = $this->delay;
				}
				if ( $cost > 0 ) {
					$cost  = round( $from_ars * $cost, 2 );
					$price = 0;
					if ( ! $free_shipping ) {
						$price = (float) $cost;
						if ( $this->shipping_fee > 0 ) {
							$price += $this->shipping_fee;
						}
						if ( $this->shipping_fee_percent > 0 ) {
							$price += $price * ( $this->shipping_fee_percent / 100.0 );
						}
						if ( $this->min_shipping_amount > 0 ) {
							$price = max( $this->min_shipping_amount, $price );
						}
						if ( $this->max_shipping_amount > 0 ) {
							$price = min( $this->max_shipping_amount, $price );
						}
						if ( $price >= $this->discount_shipping_min_amount && $total_weight >= $this->discount_shipping_min_weight ) {
							if ( $this->discount_shipping_percent > 0 ) {
								$price -= $price * ( $this->discount_shipping_percent / 100.0 );
							}
							if ( $this->discount_shipping_amount > 0 ) {
								$price -= $this->discount_shipping_amount;
							}
						}
					}
					if ( (float) $price < 0.01 ) {
						$free_shipping = true;
					}
					$rate = apply_filters(
						'kshippingargentina_price',
						array(
							'id'    => 'kshippingargentina-' . $this->instance_id,
							'label' => $this->title . ( $this->hide_delay ? '' : ' (' . $delay . ')' ) . ( $free_shipping ? ' ' . __( '- Free Shipping', 'carriers-of-argentina-for-woocommerce' ) : '' ),
							'cost'  => $free_shipping ? '0' : (float) $price,
							'taxes' => false, // Evita cobrar impuesto, el precio ya lo incluye.
						),
						'kshippingargentina-' . $this->instance_id,
						$this
					);
					$this->add_rate( $rate );
				}
			}
		}

		/**
		 * Currency not supported message.
		 */
		public function currency_not_supported_message() {
			// translators: ISO Currency.
			echo '<div class="error"><p><strong>' . esc_html( __( 'Argentina Shipping', 'carriers-of-argentina-for-woocommerce' ) ) . '</strong>: ' . esc_html( sprintf( __( 'You currency <code>%s</code> can not be supported. Please use ARS or USD.', 'carriers-of-argentina-for-woocommerce' ), get_woocommerce_currency() ) ) . '</p></div>';
		}

		/**
		 * Sort rates function.
		 *
		 * @param mixed $a A compare.
		 * @param mixed $b B compare.
		 * @return int
		 */
		public function sort_rates( $a, $b ) {
			if ( $a['sort'] === $b['sort'] ) {
				return 0;
			}
			return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
		}

		/**
		 * Generate Multiselect HTML.
		 *
		 * @param  mixed $key key.
		 * @param  mixed $data data.
		 * @since  1.1.2
		 * @return string
		 */
		public function generate_multiselectmp_html( $key, $data ) {
			$field_key = $this->get_field_key( $key );
			$defaults  = array(
				'title'             => '',
				'disabled'          => false,
				'class'             => '',
				'css'               => '',
				'placeholder'       => '',
				'type'              => 'text',
				'desc_tip'          => false,
				'description'       => '',
				'custom_attributes' => array(),
				'select_buttons'    => false,
				'options'           => array(),
			);

			$data  = wp_parse_args( $data, $defaults );
			$value = (array) $this->get_option( $key, array() );

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php echo wp_kses_post( $this->get_tooltip_html( $data ) ); ?>
					<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
						<input type="hidden" name="<?php echo esc_attr( $field_key ); ?>[]" value="dummy_delete" />
						<!-- <select multiple="multiple" class="multiselect <?php echo esc_attr( $data['class'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>[]" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo wp_kses_post( $this->get_custom_attribute_html( $data ) ); ?>> -->
						<div style="overflow: auto;height: 150px;min-width:250px;max-width:500px">
							<?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
								<input type="checkbox" name="<?php echo esc_attr( $field_key ); ?>[]" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( in_array( $option_key, $value ), true ); ?> /><?php echo esc_attr( $option_value ); ?><br />
							<?php endforeach; ?>
						</div>
						<!-- </select> -->
						<?php echo wp_kses_post( $this->get_description_html( $data ) ); ?>
						<?php if ( $data['select_buttons'] ) : ?>
							<br/><a class="select_all button" href="#"><?php esc_html_e( 'Select all', 'woocommerce' ); ?></a> <a class="select_none button" href="#"><?php esc_html_e( 'Select none', 'woocommerce' ); ?></a>
						<?php endif; ?>
					</fieldset>
				</td>
			</tr>
			<?php

			return ob_get_clean();
		}

		/**
		 * Validate Multiselect.
		 *
		 * @param  mixed $key key.
		 * @param  mixed $value value.
		 * @since  1.1.2
		 * @return bool
		 */
		public function validate_multiselectmp_field( $key, $value ) {
			return is_array( $value ) ? array_map( 'wc_clean', array_map( 'stripslashes', $value ) ) : '';
		}
	}
endif;
