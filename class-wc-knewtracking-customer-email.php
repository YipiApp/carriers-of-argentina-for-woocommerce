<?php
/**
 * New tracking email.
 *
 * @package Kijam
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * A custom New tracking Email class.
 *
 * @since 0.1
 * @extends \WC_Email
 */
class WC_KNewTracking_Customer_Email extends WC_Email {
	/**
	 * Set email defaults
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// set ID, this simply needs to be a unique name.
		$this->id = 'wc-knewtracking-customer';

		// this is the title in WooCommerce Email settings.
		$this->title = __( 'New tracking code assigned', 'wc-kshippingargentina' );

		// this is the description in WooCommerce email settings.
		$this->description = __( 'Send an email to the customer with the tracking number assigned to an order', 'wc-kshippingargentina' );

		// these are the default heading and subject lines that can be overridden using the settings.
		$this->heading = $this->get_option( 'heading', __( 'Tracking code generated', 'wc-kshippingargentina' ) );
		$this->subject = $this->get_option( 'subject', __( 'Tracking code generated', 'wc-kshippingargentina' ) );

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
		$this->template_html  = 'emails/new-tracking-html.php';
		$this->template_plain = 'emails/new-tracking-plain.php';

		add_action( 'woocommerce_order_new_ktracking_code', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor to load any other defaults not explicity defined here.
		parent::__construct();

		// this sets the recipient to the settings defined below in init_form_fields().
		$this->recipient = $this->get_option( 'recipient' ); // email.

		// if none was entered, just use the WP admin email as a fallback.
		if ( ! $this->recipient ) {
			$this->recipient = get_option( 'admin_email' );
		}
	}

	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @since 1.0
	 * @param int                            $order_id order_id.
	 * @param WC_KShippingArgentina_Shipping $shipping shipping.
	 */
	public function trigger( $order_id, $shipping ) {

		// bail if no order ID is present.
		if ( ! $order_id || ! $shipping ) {
			return;
		}

		// setup order object.
		$this->object = new WC_Order( $order_id );

		// replace variables in the subject/headings.
		$this->find[]    = '{order_date}';
		$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );

		$this->find[]    = '{order_number}';
		$this->replace[] = $this->object->get_order_number();

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		// woohoo, send the email!
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get content html function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
			)
		);
		return ob_get_clean();
	}


	/**
	 * Get content plain function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
			)
		);
		return ob_get_clean();
	}

	/**
	 * Initialize Settings Form Fields
	 *
	 * @since 0.1
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'wc-kshippingargentina' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wc-kshippingargentina' ),
				'default' => 'yes',
			),
			'recipient'  => array(
				'title'       => 'Recipient(s)',
				'type'        => 'text',
				// translators: %s email example.
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'wc-kshippingargentina' ), esc_attr( get_option( 'admin_email' ) ) ),
				'placeholder' => '',
				'default'     => '',
			),
			'subject'    => array(
				'title'       => 'Subject',
				'type'        => 'text',
				// translators: %s email example.
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'wc-kshippingargentina' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			),
			'heading'    => array(
				'title'       => 'Email Heading',
				'type'        => 'text',
				'description' => sprintf(
					// translators: %s email example.
					__( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wc-kshippingargentina' ),
					$this->heading
				),
				'placeholder' => '',
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'wc-kshippingargentina' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wc-kshippingargentina' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => 'Plain text',
					'html'      => 'HTML',
					'multipart' => 'Multipart',
				),
			),
		);
	}

}
