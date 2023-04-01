<?php
/**
 * Setting array.
 *
 * @package Kijam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$from_unit_w = strtolower( get_option( 'woocommerce_weight_unit' ) );
$from_unit_d = strtolower( get_option( 'woocommerce_dimension_unit' ) );
$offices     = array(
	'' => __( 'Not founded offices in your postcode.', 'wc-kshippingargentina' ),
);
$setting     = get_option( 'woocommerce_kshippingargentina-manager_settings' );
if ( isset( $setting['postcode'] ) && ! empty( $this->service_type ) ) {
	$list = KShippingArgentina_API::get_office( $this->service_type, $setting['postcode'], true );
	if ( $list ) {
		$offices = array();
		foreach ( $list as $office ) {
			$offices[ $office['iso'] . '#' . $office['id'] ] = $office['description'] . ' - ' . $office['address'];
		}
	}
}
/**
 * Array of settings
 */
return array(
	'title'                        => array(
		'title'       => __( 'Input name for this Courier', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Enter the name for this courier', 'wc-kshippingargentina' ),
		'default'     => __( 'Correo Argentino', 'wc-kshippingargentina' ),
		'desc_tip'    => false,
	),
	'service_type'                 => array(
		'title'       => __( 'Service Type', 'wc-kshippingargentina' ),
		'type'        => 'select',
		'description' => __( 'Select you shipping company.', 'wc-kshippingargentina' ),
		'default'     => 'correo_argentino',
		'options'     => array(
			'correo_argentino' => __( 'Correo Argentino', 'wc-kshippingargentina' ),
			'oca'              => __( 'OCA e-Pack', 'wc-kshippingargentina' ),
			'andreani'         => __( 'Andreani', 'wc-kshippingargentina' ),
		),
		'desc_tip'    => false,
	),
	'type'                         => array(
		'title'       => __( 'Type', 'wc-kshippingargentina' ),
		'type'        => 'select',
		'description' => __( 'Type of shipping.', 'wc-kshippingargentina' ),
		'default'     => 'door',
		'options'     => array(
			'door'   => __( 'Delivery in Door', 'wc-kshippingargentina' ),
			'office' => __( 'Delivery in Office', 'wc-kshippingargentina' ),
		),
		'desc_tip'    => false,
	),
	'office_src'                   => array(
		'title'       => __( 'Office of Origin', 'wc-kshippingargentina' ),
		'type'        => 'select',
		'description' => __( 'Select your office of origin.', 'wc-kshippingargentina' ),
		'default'     => '',
		'options'     => $offices,
		'desc_tip'    => false,
	),
	'product_type'                 => array(
		'title'       => __( 'Type of product, operation or contract number', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Input "CP" in Correo Argentino case', 'wc-kshippingargentina' ),
		'default'     => 'CP',
		'desc_tip'    => false,
	),
	'product_client'               => array(
		'title'       => __( 'Client Number', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Input "CP" in Correo Argentino case', 'wc-kshippingargentina' ),
		'default'     => 'CP',
		'desc_tip'    => false,
	),
	'product_cuit'                 => array(
		'title'       => __( 'CUIT (Only for OCA e-Pack)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Only for OCA e-Pack', 'wc-kshippingargentina' ),
		'default'     => '',
		'desc_tip'    => false,
	),
	'insurance_active'             => array(
		'title' => __( 'Enable Insurance', 'wc-kshippingargentina' ),
		'type'  => 'checkbox',
	),
	'find_in_store'                => array(
		'title' => __( 'Does this carrier pick up the package at your store? (Only for OCA e-Pack)', 'wc-kshippingargentina' ),
		'type'  => 'checkbox',
	),
	'insurance'                    => array(
		'title'       => __( 'Insurance Percentage (Only for Correo Argentino)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'If the shipments are Insured, enter the Insurance Percentage here (Only for Correo Argentino)', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'delay'                        => array(
		'title'       => __( 'Delivery time (If the service does not provide it)', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Enter the approximate delivery time of this transport', 'wc-kshippingargentina' ),
		'default'     => __( '4 - 7 days', 'wc-kshippingargentina' ),
		'desc_tip'    => false,
	),
	'velocity'                     => array(
		'title'       => __( 'Velocity', 'wc-kshippingargentina' ),
		'type'        => 'select',
		'description' => __( 'Select the velocity (Only for Correo Argentino)', 'wc-kshippingargentina' ),
		'default'     => 'classic',
		'options'     => array(
			'classic' => __( 'Classic', 'wc-kshippingargentina' ),
			'express' => __( 'Express', 'wc-kshippingargentina' ),
		),
		'desc_tip'    => false,
	),
	'fiscal_type'                  => array(
		'title'       => __( 'Fiscal Type', 'wc-kshippingargentina' ),
		'type'        => 'select',
		'description' => __( 'Select the type of fiscal price that will be charged (Only for Correo Argentino)', 'wc-kshippingargentina' ),
		'default'     => 'CF',
		'options'     => array(
			'PY' => __( 'Responsable Inscrito (Only available with Correo Argentino)', 'wc-kshippingargentina' ),
			'CF' => __( 'Monotributista y Consumidor Final', 'wc-kshippingargentina' ),
		),
		'desc_tip'    => false,
	),
	'shipping_fee'                 => array(
		'title'       => __( 'Add a fee value of shipping cost for all orders', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Add this fee cost to courier amount.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'shipping_fee_percent'         => array(
		'title'       => __( 'Add a fee percent of shipping cost for all orders', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Add this fee percent cost to courier amount.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'min_shipping_amount'          => array(
		'title'       => __( 'Minimum value of shipping cost for all orders', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'If the cost of courier is less than the amount established here, the customer will only be charged this amount.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'max_shipping_amount'          => array(
		'title'       => __( 'Maximum value of shipping cost for all orders', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'If the cost of courier is greater than the amount established here, the customer will only be charged this amount.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'exclude_categories'           => array(
		'title'       => __( 'Exclude if exists any product from the following categories', 'wc-kshippingargentina' ),
		'type'        => 'multiselectmp',
		'description' => __( 'This option allows you to disable if the order contains any products from these selected categories.', 'wc-kshippingargentina' ),
		'desc_tip'    => false,
	),
	'exclude_state'                => array(
		'title'       => __( 'Exclude for following States', 'wc-kshippingargentina' ),
		'type'        => 'multiselectmp',
		'description' => __( 'This option allows you to deactivate for this states.', 'wc-kshippingargentina' ),
		'desc_tip'    => false,
	),
	'activated_min_amount'         => array(
		'title'       => __( 'Minimum amount of Cart to activate courier', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Deactivate if order amount is less to this value. (leave in 0 for ignore)', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'activated_max_amount'         => array(
		'title'       => __( 'Maximum amount of Cart to activate courier', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'Deactivate if order amount is greater to this value. (leave in 0 for ignore)', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'activated_min_weight'         => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Minimum weight of Cart to activate courier (In %s)', 'wc-kshippingargentina' ), $from_unit_w ),
		'type'        => 'text',
		'description' => __( 'Deactivate if weight of order is less to this value. (leave in 0 for ignore)', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'activated_max_weight'         => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Maximum weight of Cart to activate courier (In %s)', 'wc-kshippingargentina' ), $from_unit_w ),
		'type'        => 'text',
		'description' => __( 'Deactivate if weight of order is greater to this value. (leave in 0 for ignore)', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'discount_shipping_min_amount' => array(
		'title'       => __( 'Minimum amount of Cart to activate the Discount Shipping', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'This option allows to apply a discount in the cost of shipping.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'discount_shipping_min_weight' => array(
		'title'       => __( 'Minimum weight of order to activate the Discount Shipping', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'This option allows to apply a discount in the cost of shipping.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'discount_shipping_percent'    => array(
		'title'       => __( 'Percent of discount in the shipping cost', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'This option works only if the shipping cost (and weight) of courier is greater of "Minimum amount (and weight) of Cart to activate the Discount Shipping". Leave in 0 to disable this option.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'discount_shipping_amount'     => array(
		'title'       => __( 'Amount of discount in the shipping cost', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'This option works only if the shipping cost (and weight) of courier is greater of "Minimum amount (and weight) of Cart to activate the Discount Shipping". Leave in 0 to disable this option.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'hide_delay'                   => array(
		'title'       => __( 'Hide estimated delivery time label', 'wc-kshippingargentina' ),
		'type'        => 'checkbox',
		'description' => __( 'Hide delay label', 'wc-kshippingargentina' ),
		'default'     => 'no',
	),
	'free_shipping'                => array(
		'title'       => __( 'Enable Shipping Argentina for Free Shipping', 'wc-kshippingargentina' ),
		'type'        => 'checkbox',
		'description' => __( 'Activate this method for Free Shipping', 'wc-kshippingargentina' ),
		'default'     => 'no',
	),
	'free_shipping_mode'           => array(
		'title'       => __( 'Free Shipping Mode', 'wc-kshippingargentina' ),
		'type'        => 'select',
		'description' => __( 'This option works only if "Free Shipping" is activated.', 'wc-kshippingargentina' ),
		'default'     => 'automatic_coupon',
		'class'       => 'free_shipping_mode',
		'options'     => array(
			'coupon'               => __( 'Use "Free Shipping" if a free shipping coupon is applied (The following criteria are ignored)', 'wc-kshippingargentina' ),
			'automatic_coupon'     => __( 'Use "Free Shipping" if there is a free shipping coupon applied and if the following criteria are met', 'wc-kshippingargentina' ),
			'semiautomatic_coupon' => __( 'Use "Free Shipping" if there is a free shipping coupon applied or if the following criteria are met', 'wc-kshippingargentina' ),
			'automatic'            => __( 'Use "Free Shipping" automatically only if you meet any of the following criteria (applied coupons are ignored)', 'wc-kshippingargentina' ),
		),
		'desc_tip'    => false,
	),
	'free_shipping_amount'         => array(
		'title'       => __( 'Cart minimum amount to activate the Free Shipping', 'wc-kshippingargentina' ),
		'type'        => 'text',
		'description' => __( 'This option works only if "Free Shipping" is activated.', 'wc-kshippingargentina' ),
		'description' => __( 'This option works only if you have enabled courier for Free Shipping. Leave to 0 to apply free shipping all orders.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'free_shipping_weight'         => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Cart minimum weight to activate the Free Shipping (In %s)', 'wc-kshippingargentina' ), $from_unit_w ),
		'type'        => 'text',
		'description' => __( 'This option works only if "Free Shipping" is activated.', 'wc-kshippingargentina' ),
		'description' => __( 'This option works only if you have enabled courier for Free Shipping. Leave to 0 to apply free shipping all orders.', 'wc-kshippingargentina' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'free_shipping_state'          => array(
		'title'       => __( 'Free shipping for the following States (Leave empty for all)', 'wc-kshippingargentina' ),
		'type'        => 'multiselectmp',
		'description' => __( 'This option allows free shipping by courier in some states.', 'wc-kshippingargentina' ),
		'desc_tip'    => false,
	),
);
