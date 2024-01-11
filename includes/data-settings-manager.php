<?php
/**
 * Setting array.
 *
 * @package Kijam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currency_src = get_woocommerce_currency();
$currency_dst = 'ARS';

$from_unit_w = strtolower( get_option( 'woocommerce_weight_unit' ) );
$from_unit_d = strtolower( get_option( 'woocommerce_dimension_unit' ) );

$countries_obj = new WC_Countries();
$states        = $countries_obj->get_states( 'AR' );

$r = array(
	'enabled'              => array(
		'title'   => __( 'Enable / Disable', 'carriers-of-argentina-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Shipping Argentina', 'carriers-of-argentina-for-woocommerce' ),
		'default' => 'yes',
	),
	'api_host'             => array(
		'title'       => __( 'API Host', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'default'     => 'shipping.yipi.app',
	),
	'api_key'              => array(
		'title'       => __( 'API Key or Yipi License Key', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Get this in https://yipi.app/p/membresia/', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '',
	),
	'andreani_username'    => array(
		'title'       => __( 'Andreani API Username (Optional)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Optional, only for generation of label', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '',
	),
	'andreani_password'    => array(
		'title'       => __( 'Andreani API Password (Optional)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Only for generation of label', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '',
	),
	'oca_username'         => array(
		'title'       => __( 'OCA e-Pak API Username (Optional)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Optional, only for generation of label', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '',
	),
	'oca_password'         => array(
		'title'       => __( 'OCA e-Pak API Password (Optional)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Optional, only for generation of label', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '',
	),
	'time_slot'            => array(
		'title'    => __( 'OCA collections time slot', 'carriers-of-argentina-for-woocommerce' ),
		'type'     => 'select',
		'default'  => '1',
		'options'  => array(
			'1' => __( '8:00 - 17:00', 'carriers-of-argentina-for-woocommerce' ),
			'2' => __( '8:00 - 12:00', 'carriers-of-argentina-for-woocommerce' ),
			'3' => __( '14:00 - 17:00', 'carriers-of-argentina-for-woocommerce' ),
		),
		'desc_tip' => false,
	),
	'postcode'             => array(
		'title'       => __( 'Sender Post Code', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default'     => '',
	),
	'state'                => array(
		'title'       => __( 'State of sender', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'select',
		'description' => '',
		'default'     => '',
		'options'     => $states,
	),
	'city'                 => array(
		'title'       => __( 'City of sender', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'street'               => array(
		'title'       => __( 'Street of sender', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'number'               => array(
		'title'       => __( 'Number of sender', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'floor'                => array(
		'title'       => __( 'Floor of sender', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'apartment'            => array(
		'title'       => __( 'Apartment of sender', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'other'                => array(
		'title'       => __( 'Detalle (Entre-calles, etc)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'fullname'             => array(
		'title'       => __( 'Full name of sender (Or company name)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'dni_type'             => array(
		'title'   => __( 'DNI type of sender (Only for Andreani)', 'carriers-of-argentina-for-woocommerce' ),
		'type'    => 'select',
		'default' => 'DNI',
		'options' => array(
			'DNI'  => __( 'DNI', 'carriers-of-argentina-for-woocommerce' ),
			'CUIT' => __( 'CUIT', 'carriers-of-argentina-for-woocommerce' ),
			'CUIL' => __( 'CUIL', 'carriers-of-argentina-for-woocommerce' ),
		),
	),
	'dni'                  => array(
		'title'       => __( 'DNI of sender', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'phone'                => array(
		'title'       => __( 'Phone of sender', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'email'                => array(
		'title'       => __( 'E-Mail of sender', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'weight'               => array(
		// translators: %s Unit weight.
		'title'       => sprintf( __( 'Weight default product (In %s)', 'carriers-of-argentina-for-woocommerce' ), $from_unit_w ),
		'type'        => 'text',
		'description' => __( 'Only numbers value, for example: 0.1', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0.1',
		'desc_tip'    => false,
	),
	'width'                => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Width default product (In %s)', 'carriers-of-argentina-for-woocommerce' ), $from_unit_d ),
		'type'        => 'text',
		'description' => __( 'Only numbers value, for example: 10', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '15',
		'desc_tip'    => false,
	),
	'height'               => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Height default product (In %s)', 'carriers-of-argentina-for-woocommerce' ), $from_unit_d ),
		'type'        => 'text',
		'description' => __( 'Only numbers value, for example: 10', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '15',
		'desc_tip'    => false,
	),
	'depth'                => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Depth default product (In %s)', 'carriers-of-argentina-for-woocommerce' ), $from_unit_d ),
		'type'        => 'text',
		'description' => __( 'Only numbers value, for example: 10', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '15',
		'desc_tip'    => false,
	),
	'no_change_in_transit' => array(
		'title'   => __( 'Do not change to "In Transit"', 'carriers-of-argentina-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Deactivate automatic status change to "In Transit" when creating the guide.', 'carriers-of-argentina-for-woocommerce' ),
		'default' => 'no',
	),
	'conversion_option'    => array(
		// translators: %1$s currency origin -  %2$s currency destination.
		'title'   => sprintf( __( 'Enable %1$s to %2$s conversion', 'carriers-of-argentina-for-woocommerce' ), $currency_src, $currency_dst ),
		'type'    => 'select',
		'label'   => __( 'Activate the plugin by converting the amounts to the gateway currency', 'carriers-of-argentina-for-woocommerce' ),
		'default' => '',
		'options' => array(
			'off'        => __( 'Disable module', 'carriers-of-argentina-for-woocommerce' ),
			'live-rates' => __( 'Use the official conversion rate', 'carriers-of-argentina-for-woocommerce' ),
			'custom'     => __( 'Use a manual conversion rate', 'carriers-of-argentina-for-woocommerce' ),
		),
	),
	'conversion_rate'      => array(
		// translators: %1$s currency origin -  %2$s currency destination.
		'title'   => sprintf( __( 'Convert using manual rate from %1$s to %2$s', 'carriers-of-argentina-for-woocommerce' ), $currency_src, $currency_dst ),
		'type'    => 'text',
		'label'   => __( 'Use a manual conversion rate', 'carriers-of-argentina-for-woocommerce' ),
		'default' => '1.0',
	),
	'meta_dni'             => array(
		'title'       => __( 'Meta-key for DNI', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'carriers-of-argentina-for-woocommerce' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'meta_phone'           => array(
		'title'       => __( 'Meta-key for Phone', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'carriers-of-argentina-for-woocommerce' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'meta_number'          => array(
		'title'       => __( 'Meta-key for Altura (Billing Address)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'carriers-of-argentina-for-woocommerce' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'meta_number_shipping'          => array(
		'title'       => __( 'Meta-key for Altura (Shipping Address)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'carriers-of-argentina-for-woocommerce' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'meta_apartment'          => array(
		'title'       => __( 'Meta-key for Aparment (Billing Address)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'carriers-of-argentina-for-woocommerce' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'meta_apartment_shipping'          => array(
		'title'       => __( 'Meta-key for Aparment (Shipping Address)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'carriers-of-argentina-for-woocommerce' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'meta_floor'          => array(
		'title'       => __( 'Meta-key for Floor (Billing Address)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'carriers-of-argentina-for-woocommerce' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'meta_floor_shipping'          => array(
		'title'       => __( 'Meta-key for Floor (Shipping Address)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'carriers-of-argentina-for-woocommerce' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'debug'                => array(
		'title'       => __( 'Debug', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable log', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'no',
		'description' => __( 'See in WooCommerce logs', 'carriers-of-argentina-for-woocommerce' ),
	),
);
if ( $currency_src === $currency_dst ) {
	unset( $r['conversion_option'] );
	unset( $r['conversion_rate'] );
}
return $r;
