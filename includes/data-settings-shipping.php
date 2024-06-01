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
	'' => __( 'Not founded offices in your postcode.', 'carriers-of-argentina-for-woocommerce' ),
);
$setting     = get_option( 'woocommerce_kshippingargentina-manager_settings' );
if ( is_admin() && isset( $setting['postcode'] ) && ! empty( $this->service_type ) && ! empty( $setting['postcode'] ) ) {
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
		'title'       => __( 'Input name for this Courier', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Enter the name for this courier', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => __( 'Correo Argentino', 'carriers-of-argentina-for-woocommerce' ),
		'desc_tip'    => false,
	),
	'service_type'                 => array(
		'title'       => __( 'Service Type', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'select',
		'description' => __( 'Select you shipping company.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'correo_argentino',
		'options'     => array(
			'correo_argentino' => __( 'Correo Argentino', 'carriers-of-argentina-for-woocommerce' ),
			'oca'              => __( 'OCA e-Pak', 'carriers-of-argentina-for-woocommerce' ),
			'andreani'         => __( 'Andreani', 'carriers-of-argentina-for-woocommerce' ),
		),
		'desc_tip'    => false,
	),
	'type'                         => array(
		'title'       => __( 'Type', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'select',
		'description' => __( 'Type of shipping.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'door',
		'options'     => array(
			'door'   => __( 'Delivery in Door', 'carriers-of-argentina-for-woocommerce' ),
			'office' => __( 'Delivery in Office', 'carriers-of-argentina-for-woocommerce' ),
		),
		'desc_tip'    => false,
	),
	'office_src'                   => array(
		'title'       => __( 'Office of Origin', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'select',
		'description' => __( 'Select your office of origin.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '',
		'options'     => $offices,
		'desc_tip'    => false,
	),
	'product_type'                 => array(
		'title'       => __( 'Type of product, operation or contract number', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Input "CP" in Correo Argentino case', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'CP',
		'desc_tip'    => false,
	),
	'product_client'               => array(
		'title'       => __( 'Client Number', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Input "CP" in Correo Argentino case', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'CP',
		'desc_tip'    => false,
	),
	'product_cuit'                 => array(
		'title'       => __( 'CUIT (Only for OCA e-Pak)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Only for OCA e-Pak', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '',
		'desc_tip'    => false,
	),
	'insurance_active'             => array(
		'title' => __( 'Enable Insurance', 'carriers-of-argentina-for-woocommerce' ),
		'type'  => 'checkbox',
	),
	'find_in_store'                => array(
		'title' => __( 'Does this carrier pick up the package at your store? (Only for Andreani/OCA e-Pak)', 'carriers-of-argentina-for-woocommerce' ),
		'type'  => 'checkbox',
	),
	'insurance'                    => array(
		'title'       => __( 'Insurance Percentage (Only for Correo Argentino)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If the shipments are Insured, enter the Insurance Percentage here (Only for Correo Argentino)', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'delay'                        => array(
		'title'       => __( 'Delivery time (If the service does not provide it)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Enter the approximate delivery time of this transport', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => __( '4 - 7 days', 'carriers-of-argentina-for-woocommerce' ),
		'desc_tip'    => false,
	),
	'box_calculation'                     => array(
		'title'       => __( 'Box Calculation', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'select',
		'description' => __( 'Select the Algorithm to calculate the final Boxes', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'smart',
		'options'     => array(
			'smart' => __( 'Select best boxes for all order', 'carriers-of-argentina-for-woocommerce' ),
			'fit_one' => __( 'Force to fit in one box', 'carriers-of-argentina-for-woocommerce' ),
			'by_product' => __( 'One box by unit product', 'carriers-of-argentina-for-woocommerce' ),
		),
		'desc_tip'    => false,
	),
	'velocity'                     => array(
		'title'       => __( 'Velocity', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'select',
		'description' => __( 'Select the velocity (Only for Correo Argentino)', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'classic',
		'options'     => array(
			'classic' => __( 'Classic', 'carriers-of-argentina-for-woocommerce' ),
			'express' => __( 'Express', 'carriers-of-argentina-for-woocommerce' ),
		),
		'desc_tip'    => false,
	),
	'fiscal_type'                  => array(
		'title'       => __( 'Fiscal Type', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'select',
		'description' => __( 'Select the type of fiscal price that will be charged (Only for Correo Argentino)', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'CF',
		'options'     => array(
			'PY' => __( 'Responsable Inscrito (Only available with Correo Argentino)', 'carriers-of-argentina-for-woocommerce' ),
			'CF' => __( 'Monotributista y Consumidor Final', 'carriers-of-argentina-for-woocommerce' ),
		),
		'desc_tip'    => false,
	),
	'shipping_fee'                 => array(
		'title'       => __( 'Add a fee value of shipping cost for all orders', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Add this fee cost to courier amount.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'shipping_fee_percent'         => array(
		'title'       => __( 'Add a fee percent of shipping cost for all orders', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Add this fee percent cost to courier amount.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'min_shipping_amount'          => array(
		'title'       => __( 'Minimum value of shipping cost for all orders', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If the cost of courier is less than the amount established here, the customer will only be charged this amount.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'max_shipping_amount'          => array(
		'title'       => __( 'Maximum value of shipping cost for all orders', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'If the cost of courier is greater than the amount established here, the customer will only be charged this amount.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'exclude_categories'           => array(
		'title'       => __( 'Exclude if exists any product from the following categories', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'multiselectmp',
		'description' => __( 'This option allows you to disable if the order contains any products from these selected categories.', 'carriers-of-argentina-for-woocommerce' ),
		'desc_tip'    => false,
	),
	'exclude_state'                => array(
		'title'       => __( 'Exclude for following States', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'multiselectmp',
		'description' => __( 'This option allows you to deactivate for this states.', 'carriers-of-argentina-for-woocommerce' ),
		'desc_tip'    => false,
	),
	'activated_min_amount'         => array(
		'title'       => __( 'Minimum amount of Cart to activate courier', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Deactivate if order amount is less to this value. (leave in 0 for ignore)', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'activated_max_amount'         => array(
		'title'       => __( 'Maximum amount of Cart to activate courier', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Deactivate if order amount is greater to this value. (leave in 0 for ignore)', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'activated_min_weight'         => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Minimum weight of Cart to activate courier (In %s)', 'carriers-of-argentina-for-woocommerce' ), $from_unit_w ),
		'type'        => 'text',
		'description' => __( 'Deactivate if weight of order is less to this value. (leave in 0 for ignore)', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'activated_max_weight'         => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Maximum weight of Cart to activate courier (In %s)', 'carriers-of-argentina-for-woocommerce' ), $from_unit_w ),
		'type'        => 'text',
		'description' => __( 'Deactivate if weight of order is greater to this value. (leave in 0 for ignore)', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'discount_shipping_min_amount' => array(
		'title'       => __( 'Minimum amount of Cart to activate the Discount Shipping', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This option allows to apply a discount in the cost of shipping.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'discount_shipping_min_weight' => array(
		'title'       => __( 'Minimum weight of order to activate the Discount Shipping', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This option allows to apply a discount in the cost of shipping.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'discount_shipping_percent'    => array(
		'title'       => __( 'Percent of discount in the shipping cost', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This option works only if the shipping cost (and weight) of courier is greater of "Minimum amount (and weight) of Cart to activate the Discount Shipping". Leave in 0 to disable this option.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'discount_shipping_amount'     => array(
		'title'       => __( 'Amount of discount in the shipping cost', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This option works only if the shipping cost (and weight) of courier is greater of "Minimum amount (and weight) of Cart to activate the Discount Shipping". Leave in 0 to disable this option.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'hide_delay'                   => array(
		'title'       => __( 'Hide estimated delivery time label', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'checkbox',
		'description' => __( 'Hide delay label', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'no',
	),
	'free_shipping'                => array(
		'title'       => __( 'Enable Shipping Argentina for Free Shipping', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'checkbox',
		'description' => __( 'Activate this method for Free Shipping', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'no',
	),
	'free_shipping_mode'           => array(
		'title'       => __( 'Free Shipping Mode', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'select',
		'description' => __( 'This option works only if "Free Shipping" is activated.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => 'automatic_coupon',
		'class'       => 'free_shipping_mode',
		'options'     => array(
			'coupon'               => __( 'Use "Free Shipping" if a free shipping coupon is applied (The following criteria are ignored)', 'carriers-of-argentina-for-woocommerce' ),
			'automatic_coupon'     => __( 'Use "Free Shipping" if there is a free shipping coupon applied and if the following criteria are met', 'carriers-of-argentina-for-woocommerce' ),
			'semiautomatic_coupon' => __( 'Use "Free Shipping" if there is a free shipping coupon applied or if the following criteria are met', 'carriers-of-argentina-for-woocommerce' ),
			'automatic'            => __( 'Use "Free Shipping" automatically only if you meet any of the following criteria (applied coupons are ignored)', 'carriers-of-argentina-for-woocommerce' ),
		),
		'desc_tip'    => false,
	),
	'free_shipping_amount'         => array(
		'title'       => __( 'Cart minimum amount to activate the Free Shipping', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This option works only if "Free Shipping" is activated.', 'carriers-of-argentina-for-woocommerce' ),
		'description' => __( 'This option works only if you have enabled courier for Free Shipping. Leave to 0 to apply free shipping all orders.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'free_shipping_weight'         => array(
		// translators: %s Unit dimension.
		'title'       => sprintf( __( 'Cart minimum weight to activate the Free Shipping (In %s)', 'carriers-of-argentina-for-woocommerce' ), $from_unit_w ),
		'type'        => 'text',
		'description' => __( 'This option works only if "Free Shipping" is activated.', 'carriers-of-argentina-for-woocommerce' ),
		'description' => __( 'This option works only if you have enabled courier for Free Shipping. Leave to 0 to apply free shipping all orders.', 'carriers-of-argentina-for-woocommerce' ),
		'default'     => '0',
		'desc_tip'    => false,
	),
	'free_shipping_state'          => array(
		'title'       => __( 'Free shipping for the following States (Leave empty for all)', 'carriers-of-argentina-for-woocommerce' ),
		'type'        => 'multiselectmp',
		'description' => __( 'This option allows free shipping by courier in some states.', 'carriers-of-argentina-for-woocommerce' ),
		'desc_tip'    => false,
	),
);
