<?php
/**
 * New tracking email.
 *
 * @package Kijam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, false );
?>

<p>{kshipping_message}</p>

<?php

do_action( 'woocommerce_email_order_details', $order, false, false, false );

do_action( 'woocommerce_email_order_meta', $order, false, false, false );

do_action( 'woocommerce_email_customer_details', $order, false, false, false );

do_action( 'woocommerce_email_footer', false );
