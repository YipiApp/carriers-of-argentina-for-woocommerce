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
	'enabled'            => array(
		'title'   => __( 'Enable / Disable', 'wc-kshippingargentina' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Shipping Argentina', 'wc-kshippingargentina' ),
		'default' => 'yes',
	),
	'api_host'           => array(
		'title'       => __( 'X-RapidAPI-Host', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Get this in https://rapidapi.com/kijamve/api/argentina-shipping-cost', 'wc-kshippingargentina' ),
		'default'     => '',
	),
	'api_key'            => array(
		'title'       => __( 'X-RapidAPI-Key', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Get this in https://rapidapi.com/kijamve/api/argentina-shipping-cost', 'wc-kshippingargentina' ),
		'default'     => '',
	),
	'andreani_username'  => array(
		'title'       => __( 'Andreani API Username (Optional)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Optional, only for generation of label', 'wc-kshippingargentina' ),
		'default'     => '',
	),
	'andreani_password'  => array(
		'title'       => __( 'Andreani API Password (Optional)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Only for generation of label', 'wc-kshippingargentina' ),
		'default'     => '',
	),
	'oca_username'       => array(
		'title'       => __( 'OCA e-Pack API Username (Optional)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Optional, only for generation of label', 'wc-kshippingargentina' ),
		'default'     => '',
	),
	'oca_password'       => array(
		'title'       => __( 'OCA e-Pack API Password (Optional)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Optional, only for generation of label', 'wc-kshippingargentina' ),
		'default'     => '',
	),
	'time_slot'          => array(
		'title'    => __( 'OCA collections time slot', 'wc-kshippingargentina' ),
		'type'     => 'select',
		'default'  => '1',
		'options'  => array(
			'1' => __( '8:00 - 17:00', 'wc-kshippingargentina' ),
			'2' => __( '8:00 - 12:00', 'wc-kshippingargentina' ),
			'3' => __( '14:00 - 17:00', 'wc-kshippingargentina' ),
		),
		'desc_tip' => false,
	),
	'postcode'           => array(
		'title'       => __( 'Sender Post Code', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => '',
		'default'     => '',
	),
	'state'              => array(
		'title'       => __( 'State of sender', 'wc-kshippingargentina' ),
		'type'        => 'select',
		'description' => '',
		'default'     => '',
		'options'     => $states,
	),
	'city'               => array(
		'title'       => __( 'City of sender', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'street'             => array(
		'title'       => __( 'Street of sender', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'number'             => array(
		'title'       => __( 'Number of sender', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'floor'              => array(
		'title'       => __( 'Floor of sender', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'apartment'          => array(
		'title'       => __( 'Apartment of sender', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'other'              => array(
		'title'       => __( 'Detalle (Entre-calles, etc)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'fullname'           => array(
		'title'       => __( 'Full name of sender (Or company name)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'email'              => array(
		'title'       => __( 'E-Mail of sender', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => '',
		'default    ' => '',
		'desc_tip'    => false,
	),
	'weight'             => array(
		// translators: %s Unit weight.
		'title'       => sprintf( __( 'Weight default product (In %s)', 'wc-kshippingargentina' ), $from_unit_w ),
		'type'        => 'text',
		'description' => __( 'Only numbers value, for example: 0.1', 'wc-kshippingargentina' ),
		'default'     => '0.1',
		'desc_tip'    => false,
	),
	'width'              => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Width default product (In %s)', 'wc-kshippingargentina' ), $from_unit_d ),
		'type'        => 'text',
		'description' => __( 'Only numbers value, for example: 10', 'wc-kshippingargentina' ),
		'default'     => '15',
		'desc_tip'    => false,
	),
	'height'             => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Height default product (In %s)', 'wc-kshippingargentina' ), $from_unit_d ),
		'type'        => 'text',
		'description' => __( 'Only numbers value, for example: 10', 'wc-kshippingargentina' ),
		'default'     => '15',
		'desc_tip'    => false,
	),
	'depth'              => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Depth default product (In %s)', 'wc-kshippingargentina' ), $from_unit_d ),
		'type'        => 'text',
		'description' => __( 'Only numbers value, for example: 10', 'wc-kshippingargentina' ),
		'default'     => '15',
		'desc_tip'    => false,
	),
	'shipping_mode'      => array(
		'title'   => __( 'Calculation mode', 'wc-kshippingargentina' ),
		'type'    => 'select',
		'default' => '',
		'class'   => 'shipping_mode',
		'options' => array(
			'sum_side'    => __( 'Use width and height longest of all. Adding depth of each product.', 'wc-kshippingargentina' ),
			'longer_side' => __( 'Use the longer sides of each product.', 'wc-kshippingargentina' ),
		),
	),
	'shipping_mode_calc' => array(
		'title'   => __( 'Package type', 'wc-kshippingargentina' ),
		'type'    => 'select',
		'default' => '',
		'class'   => 'shipping_mode_calc',
		'options' => array(
			'one_package'            => __( 'One package', 'wc-kshippingargentina' ),
			'one_package_by_product' => __( 'One package by product (One package for all same products)', 'wc-kshippingargentina' ),
			'one_package_by_unit'    => __( 'One package by unit', 'wc-kshippingargentina' ),
		),
	),
	'conversion_option'  => array(
		// translators: %1$s currency origin -  %2$s currency destination.
		'title'   => sprintf( __( 'Enable %1$s to %2$s conversion', 'wc-kshippingargentina' ), $currency_src, $currency_dst ),
		'type'    => 'select',
		'label'   => __( 'Activate the plugin by converting the amounts to the gateway currency', 'wc-kshippingargentina' ),
		'default' => '',
		'options' => array(
			'off'        => __( 'Disable module', 'wc-kshippingargentina' ),
			'live-rates' => __( 'Use the official conversion rate', 'wc-kshippingargentina' ),
			'custom'     => __( 'Use a manual conversion rate', 'wc-kshippingargentina' ),
		),
	),
	'conversion_rate'    => array(
		// translators: %1$s currency origin -  %2$s currency destination.
		'title'   => sprintf( __( 'Convert using manual rate from %1$s to %2$s', 'wc-kshippingargentina' ), $currency_src, $currency_dst ),
		'type'    => 'text',
		'label'   => __( 'Use a manual conversion rate', 'wc-kshippingargentina' ),
		'default' => '1.0',
	),
	'meta_dni'           => array(
		'title'       => __( 'Meta-key for DNI', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'wc-kshippingargentina' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'meta_phone'         => array(
		'title'       => __( 'Meta-key for Phone', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'wc-kshippingargentina' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'meta_number'        => array(
		'title'       => __( 'Meta-key for Altura (Address)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'If you don\'t specify it, the plugin adds it to the checkout form.', 'wc-kshippingargentina' ),
		'default    ' => '',
		'desc_tip'    => false,
	),
	'debug'              => array(
		'title'       => __( 'Debug', 'wc-kshippingargentina' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable log', 'wc-kshippingargentina' ),
		'default'     => 'no',
		'description' => __( 'See in WooCommerce logs', 'wc-kshippingargentina' ),
	),
);
if ( $currency_src === $currency_dst ) {
	unset( $r['conversion_option'] );
	unset( $r['conversion_rate'] );
}
return $r;
