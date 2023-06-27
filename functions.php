<?php
/**
 * Functions.
 *
 * @package Kijam
 */

add_filter(
	'wc_order_statuses',
	function ( $order_statuses ) {
		$new_order_statuses = array();
		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;
			if ( 'wc-processing' === $key ) {
				$new_order_statuses['wc-arrival']   = __( 'Shipment Arrival', 'carriers-of-argentina-for-woocommerce' );
				$new_order_statuses['wc-intransit'] = __( 'Shipment in Transit', 'carriers-of-argentina-for-woocommerce' );
			}
		}
		return $new_order_statuses;
	}
);

add_filter(
	'woocommerce_register_shop_order_post_statuses',
	function ( $order_statuses ) {
		$order_statuses['wc-arrival']   = array(
			'label'                     => __( 'Shipment Arrival', 'carriers-of-argentina-for-woocommerce' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			// translators: count.
			'label_count'               => _n_noop( 'Shipment Arrival <span class="count">(%s)</span>', 'Shipment Arrival <span class="count">(%s)</span>' ),
		);
		$order_statuses['wc-intransit'] = array(
			'label'                     => __( 'Shipment in Transit', 'carriers-of-argentina-for-woocommerce' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			// translators: count.
			'label_count'               => _n_noop( 'Shipment in Transit <span class="count">(%s)</span>', 'Shipment in Transit <span class="count">(%s)</span>' ),
		);
		return $order_statuses;
	}
);

add_filter(
	'woocommerce_states',
	function ( $states ) {
		$states['AR'] = array(
			'C' => 'Capital Federal',
			'B' => 'Buenos Aires',
			'K' => 'Catamarca',
			'H' => 'Chaco',
			'U' => 'Chubut',
			'X' => 'Córdoba',
			'W' => 'Corrientes',
			'E' => 'Entre Ríos',
			'P' => 'Formosa',
			'Y' => 'Jujuy',
			'L' => 'La Pampa',
			'F' => 'La Rioja',
			'M' => 'Mendoza',
			'N' => 'Misiones',
			'Q' => 'Neuquén',
			'R' => 'Río Negro',
			'A' => 'Salta',
			'J' => 'San Juan',
			'D' => 'San Luis',
			'Z' => 'Santa Cruz',
			'S' => 'Santa Fe',
			'G' => 'Santiago del Estero',
			'V' => 'Tierra del Fuego',
			'T' => 'Tucumán',
		);
		return $states;
	}
);

add_action(
	'woocommerce_checkout_update_order_review',
	function ( $posted_data ) {
		if ( ! WC()->session ) {
			WC()->session = new WC_Session_Handler();
			WC()->session->init();
		}
		$data = WC()->session->get_session_data();
		foreach ( $data as $key => $v ) {
			if ( strstr( $key, 'shipping_for_package' ) !== false ) {
				unset( WC()->session->$key );
			}
		}
		WC()->cart->calculate_totals();
		WC()->cart->calculate_shipping();
	}
);


add_action(
	'woocommerce_after_shipping_rate',
	function ( $method, $index ) {
		if ( ! is_checkout() ) {
			return; // Only on checkout page.
		}
		if ( false === strstr( $method->id, 'kshippingargentina' ) ) {
			return;
		}
		$t_setting = get_option( 'woocommerce_kshippingargentina-shipping_' . $method->instance_id . '_settings' );
		if ( 'office' !== $t_setting['type'] ) {
			return;
		}
		echo '<div data-instance_id="' . esc_html( $method->instance_id ) . '" class="custom-office_kshippingargentina method_instance_id-' . esc_html( $method->instance_id ) . '" style="display: flex;padding-left: 20px;">
		<input class="method_nonce" name="kshippingargentina_method_nonce[' . esc_html( $method->instance_id ) . ']" value="' . esc_html( wp_create_nonce( 'office_kshippingargentina_' . $method->instance_id ) ) . '" type="hidden" />
		<input class="method_instance_id" value="' . esc_html( $method->instance_id ) . '" type="hidden" />
		';
		$values = WC_KShippingArgentina::woocommerce_instance()->checkout->get_value( 'kshippingargentina_method_office' );
		woocommerce_form_field(
			'kshippingargentina_method_office[' . $method->instance_id . ']',
			array(
				'label'    => __( 'Nearest office', 'carriers-of-argentina-for-woocommerce' ),
				'type'     => 'select',
				'class'    => array( 'form-row-wide office_kshippingargentina' ),
				'required' => false,
				'options'  => array(
					'' => __( 'Choose one...', 'carriers-of-argentina-for-woocommerce' ),
				),
			),
			isset( $values[ $method->instance_id ] ) ? $values[ $method->instance_id ] : ''
		);
		echo '</div>';
	},
	20,
	2
);

add_action(
	'woocommerce_checkout_update_order_meta',
	function ( $order_id ) {
		$zones   = WC_Shipping_Zones::get_zones();
		$methods = array_map(
			function( $zone ) {
				return $zone['shipping_methods'];
			},
			$zones
		);
		$offices = get_post_meta( $order_id, '_office_kshippingargentina', true );
		if ( ! $offices ) {
			$offices = array();
		}
		foreach ( $methods as $list ) {
			foreach ( $list as $m ) {
				if ( strstr( $m->id, 'kshippingargentina' ) !== false ) {
					$shipping = WC_KShippingArgentina_Shipping::get_instance( $m->instance_id );
					if ( $shipping->office ) {
						if ( isset( $_POST['kshippingargentina_method_nonce'][ $m->instance_id ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kshippingargentina_method_nonce'][ $m->instance_id ] ) ), 'office_kshippingargentina_' . $m->instance_id ) ) {
							if ( isset( $_POST['kshippingargentina_method_office'][ $m->instance_id ] ) && ! empty( $_POST['kshippingargentina_method_office'][ $m->instance_id ] ) ) {
								$offices[ $m->instance_id ] = array(
									'method_id'    => $m->id,
									'instance_id'  => $m->instance_id,
									'service_type' => $shipping->service_type,
									'office'       => sanitize_text_field( wp_unslash( $_POST['kshippingargentina_method_office'][ $m->instance_id ] ) ),
								);
							}
						}
					}
				}
			}
		}
		update_post_meta( $order_id, '_office_kshippingargentina', $offices );
	},
	30,
	1
);

// Our hooked in function - $fields is passed via the filter!

add_filter(
	'woocommerce_checkout_fields',
	function ( $fields ) {
		if ( ! WC()->session ) {
			WC()->session = new WC_Session_Handler();
			WC()->session->init();
		}
		$ship_to_different_address = (bool) WC()->session->get( 'ship_to_different_address' );
		$country                   = $ship_to_different_address ? WC()->customer->get_shipping_country() : WC()->customer->get_billing_country();
		$required                  = true;
		$bp_state                  = $fields['billing']['billing_state']['priority'];
		$fields['billing']['billing_country']['priority'] = $bp_state - 5;
		$sp_state = $fields['shipping']['shipping_state']['priority'];
		$fields['shipping']['shipping_country']['priority'] = $sp_state - 5;
		$bp_country  = $fields['billing']['billing_country']['priority'];
		$bp_address  = $fields['billing']['billing_address_1']['priority'];
		$bp_postcode = $fields['billing']['billing_postcode']['priority'];
		$sp_address  = $fields['shipping']['shipping_address_1']['priority'];
		$bp_address2 = $fields['billing']['billing_address_2']['priority'];
		$sp_address2 = $fields['shipping']['shipping_address_2']['priority'];
		$bp_city     = $fields['billing']['billing_city']['priority'];
		$sp_city     = $fields['shipping']['shipping_city']['priority'];
		$sp_postcode = $fields['shipping']['shipping_postcode']['priority'];
		$bp_phone    = $fields['billing']['billing_phone']['priority'];
		$setting     = get_option( 'woocommerce_kshippingargentina-manager_settings' );
		if ( ! isset( $setting['meta_dni'] ) || empty( $setting['meta_dni'] ) ) {
			$fields['billing']['billing_vat_type'] = array(
				'label'       => __( 'Identification number type', 'carriers-of-argentina-for-woocommerce' ),
				'type'        => 'select',
				'required'    => $required,
				'priority'    => $bp_country - 2,
				'options'     => WC_KShippingArgentina::$vat_types,
				'class'       => apply_filters( 'kshippingargentina_form_row_first_field', array( 'form-row-first', 'form-group', 'col-sm-12', 'col-md-6' ) ),
				'input_class' => apply_filters( 'kshippingargentina_form_row_first_input', array( 'form-control' ) ),
				'clear'       => true,
			);
			$fields['billing']['billing_vat']      = array(
				'label'       => __( 'Identification number', 'carriers-of-argentina-for-woocommerce' ),
				'type'        => 'text',
				'priority'    => $bp_country - 1,
				'required'    => $required,
				'class'       => apply_filters( 'kshippingargentina_form_row_last_field', array( 'form-row-last', 'form-group', 'col-sm-12', 'col-md-6' ) ),
				'input_class' => apply_filters( 'kshippingargentina_form_row_last_input', array( 'form-control' ) ),
				'clear'       => true,
			);
		}
		if ( ! isset( $setting['meta_phone'] ) || empty( $setting['meta_phone'] ) ) {
			$fields['billing']['billing_kphone_prefix'] = array(
				'label'       => __( 'Mobile Phone Area Code', 'carriers-of-argentina-for-woocommerce' ),
				'type'        => 'text',
				'priority'    => $bp_phone + 1,
				'required'    => $required,
				'class'       => apply_filters( 'kshippingargentina_form_row_first_field', array( 'form-row-first', 'form-group', 'col-sm-12', 'col-md-6' ) ),
				'input_class' => apply_filters( 'kshippingargentina_form_row_first_input', array( 'form-control' ) ),
				'clear'       => true,
			);
			$fields['billing']['billing_kphone']        = array(
				'label'       => __( 'Mobile Phone (No Area Code)', 'carriers-of-argentina-for-woocommerce' ),
				'type'        => 'text',
				'priority'    => $bp_phone + 2,
				'required'    => $required,
				'class'       => apply_filters( 'kshippingargentina_form_row_last_field', array( 'form-row-last', 'form-group', 'col-sm-12', 'col-md-6' ) ),
				'input_class' => apply_filters( 'kshippingargentina_form_row_last_input', array( 'form-control' ) ),
				'clear'       => true,
			);
		}
		if ( ! isset( $setting['meta_number'] ) || empty( $setting['meta_number'] ) ) {
			$fields['billing']['billing_number']   = array(
				'label'             => __( 'Height (Enter numbers only)', 'carriers-of-argentina-for-woocommerce' ),
				'type'              => 'text',
				'priority'          => $bp_address2 + 1,
				'custom_attributes' => array( 'pattern' => '[0-9]{1,8}' ),
				'maxlength'         => 8,
				'required'          => $required,
				'class'             => apply_filters( 'kshippingargentina_form_row_field', array( 'form-group', 'col-sm-12', 'col-md-12' ) ),
				'input_class'       => apply_filters( 'kshippingargentina_form_row_input', array( 'form-control' ) ),
				'clear'             => true,
			);
			$fields['shipping']['shipping_number'] = array(
				'label'             => __( 'Height (Enter numbers only)', 'carriers-of-argentina-for-woocommerce' ),
				'type'              => 'text',
				'priority'          => $sp_address2 + 1,
				'custom_attributes' => array( 'pattern' => '[0-9]{1,8}' ),
				'maxlength'         => 8,
				'required'          => $required,
				'class'             => apply_filters( 'kshippingargentina_form_row_field', array( 'form-group', 'col-sm-12', 'col-md-12' ) ),
				'input_class'       => apply_filters( 'kshippingargentina_form_row_input', array( 'form-control' ) ),
				'clear'             => true,
			);
		}
		$fields['billing']['billing_floor']       = array(
			'label'             => __( 'Floor', 'carriers-of-argentina-for-woocommerce' ),
			'type'              => 'text',
			'priority'          => $bp_address2 + 3,
			'custom_attributes' => array( 'pattern' => '[a-zA-Z0-9 ]{0,3}' ),
			'maxlength'         => 3,
			'required'          => false,
			'class'             => apply_filters( 'kshippingargentina_form_row_field', array( 'form-group', 'col-sm-12', 'col-md-12' ) ),
			'input_class'       => apply_filters( 'kshippingargentina_form_row_input', array( 'form-control' ) ),
			'clear'             => true,
		);
		$fields['shipping']['shipping_floor']     = array(
			'label'             => __( 'Floor', 'carriers-of-argentina-for-woocommerce' ),
			'type'              => 'text',
			'priority'          => $sp_address2 + 3,
			'required'          => false,
			'custom_attributes' => array( 'pattern' => '[a-zA-Z0-9 ]{0,3}' ),
			'maxlength'         => 3,
			'class'             => apply_filters( 'kshippingargentina_form_row_field', array( 'form-group', 'col-sm-12', 'col-md-12' ) ),
			'input_class'       => apply_filters( 'kshippingargentina_form_row_input', array( 'form-control' ) ),
			'clear'             => true,
		);
		$fields['billing']['billing_apartment']   = array(
			'label'             => __( 'Apartment', 'carriers-of-argentina-for-woocommerce' ),
			'type'              => 'text',
			'priority'          => $bp_address2 + 4,
			'custom_attributes' => array( 'pattern' => '[a-zA-Z0-9 ]{0,3}' ),
			'maxlength'         => 3,
			'required'          => false,
			'class'             => apply_filters( 'kshippingargentina_form_row_field', array( 'form-group', 'col-sm-12', 'col-md-12' ) ),
			'input_class'       => apply_filters( 'kshippingargentina_form_row_input', array( 'form-control' ) ),
			'clear'             => true,
		);
		$fields['shipping']['shipping_apartment'] = array(
			'label'             => __( 'Apartment', 'carriers-of-argentina-for-woocommerce' ),
			'type'              => 'text',
			'priority'          => $sp_address2 + 4,
			'custom_attributes' => array( 'pattern' => '[a-zA-Z0-9 ]{0,3}' ),
			'maxlength'         => 3,
			'required'          => false,
			'class'             => apply_filters( 'kshippingargentina_form_row_field', array( 'form-group', 'col-sm-12', 'col-md-12' ) ),
			'input_class'       => apply_filters( 'kshippingargentina_form_row_input', array( 'form-control' ) ),
			'clear'             => true,
		);
		return $fields;
	},
	5
);

add_filter(
	'woocommerce_default_address_fields',
	function ( $address_fields ) {
		$address_fields['address_1']['label']       = __( 'Street', 'carriers-of-argentina-for-woocommerce' );
		$address_fields['address_1']['placeholder'] = __( 'Street name', 'carriers-of-argentina-for-woocommerce' );
		$address_fields['address_2']['label']       = __( 'Detail (Between-streets, etc)', 'carriers-of-argentina-for-woocommerce' );
		$address_fields['address_2']['placeholder'] = __( 'Detail (Between-streets, etc)', 'carriers-of-argentina-for-woocommerce' );
		return $address_fields;
	}
);

/**
 * Custom function that get the chosen shipping method details for a cart item.
 *
 * @param string $cart_item_key Item key.
 */
function kshippingargentina_cart_item_shipping_method( $cart_item_key = false ) {
	if ( ! WC()->session ) {
		WC()->session = new WC_Session_Handler();
		WC()->session->init();
	}
	$chosen_shippings = WC()->session->get( 'chosen_shipping_methods' ); // The chosen shipping methods.

	KShippingArgentina_API::debug( 'Chosen Shippings: ', array( $chosen_shippings ) );
	foreach ( WC()->cart->get_shipping_packages() as $id => $package ) {
		$chosen = $chosen_shippings[ $id ]; // The chosen shipping method.
		KShippingArgentina_API::debug( 'Chosen package: ', array( $id, $chosen ) );
		if ( WC()->session->__isset( 'shipping_for_package_' . $id ) ) {
			$prices = WC()->session->get( 'shipping_for_package_' . $id );
			KShippingArgentina_API::debug( 'Chosen price: ', array( $id, $chosen, $prices ) );
			return $prices['rates'][ $chosen ];
		}
	}
}

// Save shipping method details in order line items (product) as custom order item meta data.
add_action(
	'woocommerce_checkout_create_order_line_item',
	function ( $item, $cart_item_key, $values, $order ) {
		// Load shipping rate for this item.
		$rate = kshippingargentina_cart_item_shipping_method( $cart_item_key );
		if ( strstr( $rate->id, 'kshippingargentina' ) === false ) {
			return;
		}
		$order->update_meta_data( 'kshippingargentina_type', $rate );
		$order->update_meta_data( 'kshippingargentina_cost', $rate->cost );
		$order->update_meta_data( 'kshippingargentina_instance_id', $rate->instance_id );
		$order->update_meta_data( 'kshippingargentina_label', $rate->label );
		KShippingArgentina_API::debug( 'Chosen instance: ', array( $rate->id, $rate->cost, $rate->instance_id, $rate->label ) );
	},
	10,
	4
);

if ( ! function_exists( 'array_key_first' ) ) {
	/**
	 * Array key first.
	 *
	 * @param array $arr Items.
	 */
	function array_key_first( $arr ) {
		foreach ( $arr as $key => $unused ) {
			return $key;
		}
		return null;
	}
}

/**
 * Hook js function.
 */
add_action( 'wp_enqueue_scripts', 'kshippingargentina_hook_js' );
add_action( 'admin_enqueue_scripts', 'kshippingargentina_hook_js' );
function kshippingargentina_hook_js() {
	if ( ! WC()->session ) {
		WC()->session = new WC_Session_Handler();
		WC()->session->init();
	}
	if ( is_admin() ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}
	$values = WC_KShippingArgentina::woocommerce_instance()->checkout->get_value( 'kshippingargentina_method_office' );
	wp_enqueue_script( 'wc-kshippingargentina-js', plugins_url( 'kshippingargentina_script.js', __FILE__ ), array( 'jquery' ), WC_KShippingArgentina::VERSION, true );
	wp_localize_script(
		'wc-kshippingargentina-js',
		'wc_kshippingargentina_context',
		array(
			'token'                     => wp_create_nonce( 'kshippingargentina_token' ),
			'ajax_url'                  => WC_AJAX::get_endpoint( 'wc_kshippingargentina_ajax' ),
			'home_url'                  => home_url(),
			'office_kshippingargentina' => $values,
			'messages'                  => array(
				'days'              => __( 'days', 'carriers-of-argentina-for-woocommerce' ),
				'empty_cart'        => __( 'Your shopping cart is empty', 'carriers-of-argentina-for-woocommerce' ),
				'carrier_empty'     => __( 'No carriers found', 'carriers-of-argentina-for-woocommerce' ),
				'product_not_found' => __( 'Product not found', 'carriers-of-argentina-for-woocommerce' ),
				'server_error'      => __( 'Server error', 'carriers-of-argentina-for-woocommerce' ),
				'server_loading'    => __( 'Loading...', 'carriers-of-argentina-for-woocommerce' ),
				'not_installed'     => __( 'The plugin is not configured correctly...', 'carriers-of-argentina-for-woocommerce' ),
			),
		)
	);
	wp_enqueue_style( 'wc-kshippingargentina-css', plugins_url( 'kshippingargentina_style.css', __FILE__ ), array(), WC_KShippingArgentina::VERSION );
}

add_action( 'wc_ajax_wc_kshippingargentina_ajax', 'wc_kshippingargentina_ajax' );
/**
 * Ajax function.
 */
function wc_kshippingargentina_ajax() {
	if ( ! class_exists( 'KShippingArgentina_API' ) ) {
		include_once 'includes/class-kshippingargentina-api.php';
	}
	KShippingArgentina_API::init();
	WC_KShippingArgentina::get_instance();
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'kshippingargentina_token' ) ) {
		KShippingArgentina_API::debug( 'invalid nonce wc_kshippingargentina_ajax', $_POST );
		die( wp_json_encode( array() ) );
	}

	KShippingArgentina_API::debug( 'wc_kshippingargentina_ajax', $_POST );

	$setting = get_option( 'woocommerce_kshippingargentina-manager_settings' );

	if ( isset( $_POST['cmd'] ) ) {
		switch ( $_POST['cmd'] ) {
			case 'offices_sender':
				$postcode = $setting['postcode'];
				if ( isset( $_POST['postcode'] ) ) {
					$postcode = sanitize_text_field( wp_unslash( $_POST['postcode'] ) );
				}
				$service_type = false;
				if ( ! isset( $_POST['instance_id'] ) && ! isset( $_POST['service_type'] ) ) {
					die( wp_json_encode( array() ) );
				}
				if ( isset( $_POST['service_type'] ) ) {
					$service_type = sanitize_text_field( wp_unslash( $_POST['service_type'] ) );
				} else {
					$shipping     = WC_KShippingArgentina_Shipping::get_instance( (int) $_POST['instance_id'] );
					$service_type = $shipping->service_type;
				}

				$offices = KShippingArgentina_API::get_office( $service_type, $postcode, true );
				die( wp_json_encode( $offices ) );
			case 'offices_rcv':
				if ( ! isset( $_POST['postcode'] ) ) {
					die( wp_json_encode( array() ) );
				}
				if ( ! isset( $_POST['instance_id'] ) ) {
					die( wp_json_encode( array() ) );
				}
				$shipping = WC_KShippingArgentina_Shipping::get_instance( (int) $_POST['instance_id'] );
				$offices  = KShippingArgentina_API::get_office( $shipping->service_type, sanitize_text_field( wp_unslash( $_POST['postcode'] ) ), null, true );
				die( wp_json_encode( $offices ) );
			case 'cities':
				if ( ! isset( $_POST['state'] ) ) {
					die( wp_json_encode( array() ) );
				}
				$cities = KShippingArgentina_API::get_cities( sanitize_text_field( wp_unslash( $_POST['state'] ) ) );
				die( wp_json_encode( $cities ) );
		}
	}
	die( '"invalid_cmd"' );
}

add_filter(
	'woocommerce_default_address_fields',
	function ( $address_fields ) {
		// as you can see, no needs to specify a field group anymore.
		$country_p                           = $address_fields['country']['priority'];
		$address_fields['state']['priority'] = $country_p + 1;
		$address_fields['city']['priority']  = $country_p + 2;
		return $address_fields;
	}
);

add_filter(
	'woocommerce_localisation_address_formats',
	function ( $formats ) {
		$formats['AR'] = "{name}\n{company}\n{address_1} {shipping_number}\n{shipping_floor} {shipping_apartment}\n{address_2}\n{city}\n{state}\n{postcode}\n{country}\n{shipping_office}";
		return $formats;
	}
);

add_filter(
	'woocommerce_order_formatted_billing_address',
	function ( $fields, $order ) {
		$fields['shipping_number']    = $order->get_meta( '_billing_number' );
		$fields['shipping_floor']     = $order->get_meta( '_billing_floor' );
		$fields['shipping_apartment'] = $order->get_meta( '_billing_apartment' );
		$shipping                     = false;
		$offices                      = $order->get_meta( '_office_kshippingargentina' );

		$instance_id = get_post_meta( $order->get_id(), 'kshippingargentina_instance_id', true );

		if ( $instance_id ) {
			$shipping = WC_KShippingArgentina_Shipping::get_instance( $instance_id );
		}

		if ( $shipping && $shipping->office && isset( $offices [ $instance_id ] ) ) {
			$address = $order->get_address( 'billing' );
			if ( ! isset( $address['state'] ) || empty( $address['state'] ) ) {
				$address = $order->get_address( 'shipping' );
			}
			$countries_obj = new WC_Countries();
			$states        = $countries_obj->get_states( 'AR' );
			$state         = $address['state'];
			$state_name    = $state;
			$office        = $offices [ $instance_id ]['office'];
			if ( isset( $states[ $state ] ) ) {
				$state_name = $states[ $state ];
			}
			$fields['shipping_office'] = sprintf(
				// translators: Office.
				__( 'Office: %1$s', 'carriers-of-argentina-for-woocommerce' ),
				$office . ' - ' . $state_name
			);
			$postcode = preg_replace(
				'/[^0-9]/',
				'',
				$address['postcode']
			);
			$offices  = KShippingArgentina_API::get_office( $shipping->service_type, $postcode, null, true );
			if ( isset( $offices[ $office ] ) ) {
				$o                         = $offices[ $office ];
				$fields['shipping_office'] = sprintf(
					// translators: name - (iso / code).
					__( 'Office %1$s: %2$s - %3$s (%4$s / %5$s)', 'carriers-of-argentina-for-woocommerce' ),
					$state_name,
					$o['description'],
					$o['address'],
					$o['iso'],
					$o['id']
				);
			}
		} else {
			$fields['shipping_office'] = '';
		}
		return $fields;
	},
	10,
	2
);

add_filter(
	'woocommerce_order_formatted_shipping_address',
	function ( $fields, $order ) {
		$fields['shipping_number']    = $order->get_meta( '_shipping_number' );
		$fields['shipping_floor']     = $order->get_meta( '_shipping_floor' );
		$fields['shipping_apartment'] = $order->get_meta( '_shipping_apartment' );
		$shipping                     = false;
		$offices                      = $order->get_meta( '_office_kshippingargentina' );

		$instance_id = get_post_meta( $order->get_id(), 'kshippingargentina_instance_id', true );

		if ( $instance_id ) {
			$shipping = WC_KShippingArgentina_Shipping::get_instance( $instance_id );
		}

		if ( $shipping && $shipping->office && isset( $offices [ $instance_id ] ) ) {
			$address = $order->get_address( 'shipping' );
			if ( ! isset( $address['state'] ) || empty( $address['state'] ) ) {
				$address = $order->get_address( 'billing' );
			}
			$countries_obj = new WC_Countries();
			$states        = $countries_obj->get_states( 'AR' );
			$state         = $address['state'];
			$state_name    = $state;
			$office        = $offices [ $instance_id ]['office'];
			if ( isset( $states[ $state ] ) ) {
				$state_name = $states[ $state ];
			}
			$fields['shipping_office'] = sprintf(
				// translators: Office.
				__( 'Office: %1$s', 'carriers-of-argentina-for-woocommerce' ),
				$office . ' - ' . $state_name
			);
			$postcode = preg_replace(
				'/[^0-9]/',
				'',
				$address['postcode']
			);
			$offices  = KShippingArgentina_API::get_office( $shipping->service_type, $postcode, null, true );
			if ( isset( $offices[ $office ] ) ) {
				$o                         = $offices[ $office ];
				$fields['shipping_office'] = sprintf(
					// translators: name - (iso / code).
					__( 'Office %1$s: %2$s - %3$s (%4$s / %5$s)', 'carriers-of-argentina-for-woocommerce' ),
					$state_name,
					$o['description'],
					$o['address'],
					$o['iso'],
					$o['id']
				);
			}
		} else {
			$fields['shipping_office'] = '';
		}
		return $fields;
	},
	10,
	2
);

add_filter(
	'woocommerce_formatted_address_replacements',
	function ( $replacements, $address ) {
		$replacements['{shipping_number}']    = isset( $address['shipping_number'] ) ? $address['shipping_number'] : '';
		$replacements['{shipping_floor}']     = isset( $address['shipping_floor'] ) ? $address['shipping_floor'] : '';
		$replacements['{shipping_apartment}'] = isset( $address['shipping_apartment'] ) ? $address['shipping_apartment'] : '';
		$replacements['{shipping_office}']    = isset( $address['shipping_office'] ) ? $address['shipping_office'] : '';
		return $replacements;
	},
	10,
	2
);


add_filter(
	'manage_edit-shop_order_columns',
	function ( $columns ) {
		$return = array();
		foreach ( $columns as $key => $data ) {
			$return[ $key ] = $data;
			if ( 'order_number' === $key ) {
				$return['shipping_name'] = __( 'Shipping', 'carriers-of-argentina-for-woocommerce' );
			}
		}
		return $return;
	},
	20
);

add_action(
	'manage_shop_order_posts_custom_column',
	function ( $column, $post_id ) {
		if ( 'shipping_name' === $column ) {
			$instance_id = get_post_meta( $post_id, 'kshippingargentina_instance_id', true );
			if ( $instance_id ) {
				$shipping = WC_KShippingArgentina_Shipping::get_instance( $instance_id );
				if ( $shipping && isset( $shipping->service_type ) && ! empty( $shipping->service_type ) ) {
					echo '<img src="' . esc_url( plugin_dir_url( __FILE__ ) . 'img/' . $shipping->service_type . '.png' ) . '" height="12" class="wc-kshippingargentina-logo" /> ';
				}
			}
			$order = wc_get_order( $post_id );
			echo esc_html( $order->get_shipping_method() );
		}
	},
	10,
	2
);

add_action(
	'wp_enqueue_scripts',
	function() {
		if ( is_checkout() && ! is_wc_endpoint_url() ) {
			wp_enqueue_style( 'kshippingargentina_style', plugin_dir_url( __FILE__ ) . 'kshippingargentina_style.css', array(), WC_KShippingArgentina::VERSION );
		}
	}
);
