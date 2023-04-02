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
	 * Instance.
	 *
	 * @var WC_KNewTracking_Customer_Email
	 */
	private static $instance = null;

	/**
	 * Returns instance.
	 *
	 * @return WC_KNewTracking_Customer_Email
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Mail message.
	 *
	 * @var string
	 */
	private $mail_message;
	/**
	 * Set email defaults
	 *
	 * @since 1.0
	 */
	public function __construct() {

		self::$instance = $this;

		// set ID, this simply needs to be a unique name.
		$this->id = 'wc-knewtracking-customer';

		// this is the title in WooCommerce Email settings.
		$this->title = __( 'Customer: New tracking code assigned', 'wc-kshippingargentina' );

		// this is the description in WooCommerce email settings.
		$this->description = __( 'Send an email to the customer with the tracking number assigned to an order', 'wc-kshippingargentina' );

		// these are the default heading and subject lines that can be overridden using the settings.
		$this->heading      = $this->get_option( 'heading', __( '#{order_id} Tracking code generated', 'wc-kshippingargentina' ) );
		$this->subject      = $this->get_option( 'subject', __( '#{order_id} Tracking code generated', 'wc-kshippingargentina' ) );
		$this->mail_message = $this->get_option( 'message', __( 'Your tracking code for the order #{order_id} has been created. The link to track your package is as follows: {link}', 'wc-kshippingargentina' ) );

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
		$plugin_dirname = basename( dirname( __DIR__ ) );
		$this->template_html  = '../../' . $plugin_dirname . '/emails/new-tracking-html.php';
		$this->template_plain = '../../' . $plugin_dirname . '/emails/new-tracking-plain.php';

		add_action( 'woocommerce_order_new_ktracking_code', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor to load any other defaults not explicity defined here.
		parent::__construct();

		// this sets the recipient to the settings defined below in init_form_fields().
		$this->recipient  = '';
		$this->email_type = 'html';
	}

	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @since 1.0
	 * @param int                            $order_id order_id.
	 * @param WC_KShippingArgentina_Shipping $shipping shipping.
	 */
	public function trigger( $order_id, $shipping ) {
		KShippingArgentina_API::debug(
			'WC_KNewTracking_Customer_Email trigger...',
			array(
				$order_id,
			)
		);

		// bail if no order ID is present.
		if ( ! $order_id || ! $shipping ) {
			KShippingArgentina_API::debug(
				'WC_KNewTracking_Customer_Email failed...',
				array(
					$order_id,
					$order_id,
				)
			);
			return;
		}

		// setup order object.
		$this->object = new WC_Order( $order_id );

		// replace variables in the subject/headings.
		$this->find[]    = '{order_date}';
		$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );

		$this->find[]    = '{order_number}';
		$this->replace[] = $this->object->get_order_number();

		$message = str_replace( '{order_id}', $this->object->get_order_number(), $this->mail_message );
		$links   = array();
		$labels  = get_post_meta( $this->object->get_id(), 'kshippingargentina_label_file', true );
		if ( ! $labels || ! is_array( $labels ) || ! count( $labels ) ) {
			KShippingArgentina_API::debug(
				'WC_KNewTracking_Customer_Email no labels...',
				array(
					$order_id,
					$labels,
				)
			);
			return;
		}
		$url = '';
		if ( 'correo_argentino' === $shipping->service_type ) {
			$url = KShippingArgentina_API::TRACKING_URL_CORREO;
		} elseif ( 'oca' === $shipping->service_type ) {
			$url = KShippingArgentina_API::TRACKING_URL_OCA;
		} elseif ( 'andreani' === $shipping->service_type ) {
			$url = KShippingArgentina_API::TRACKING_URL_ANDREANI;
		}
		foreach ( array_keys( $labels ) as $tc ) {
			$links[] = str_replace( '@', $tc, '<a href="' . $url . '">' . $tc . '</a>' );
		}

		$this->find[]    = '{kshipping_message}';
		$this->replace[] = str_replace( '{link}', implode( ', ', $links ), $message );

		$billing_address = $this->object->get_address( 'billing' );
		$this->recipient = $billing_address['email'];

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			KShippingArgentina_API::debug(
				'WC_KNewTracking_Customer_Email disabled...',
				array(
					$order_id,
					$this->is_enabled(),
					$this->get_recipient(),
				)
			);
			return;
		}

		KShippingArgentina_API::debug(
			'WC_KNewTracking_Customer_Email trigger sending...',
			array(
				$order_id,
			)
		);
		// woohoo, send the email!
		$this->send( $this->get_recipient(), str_replace( '{order_id}', $this->object->get_order_number(), $this->get_subject() ), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
			'subject'    => array(
				'title'       => __( 'Subject', 'wc-kshippingargentina' ),
				'type'        => 'text',
				// translators: %s email example.
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'wc-kshippingargentina' ), $this->subject ),
				'placeholder' => '',
				'default'     => $this->subject,
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'wc-kshippingargentina' ),
				'type'        => 'text',
				'description' => sprintf(
					// translators: %s email example.
					__( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'wc-kshippingargentina' ),
					$this->heading
				),
				'placeholder' => '',
				'default'     => $this->heading,
			),
			'message'    => array(
				'title'       => __( 'Message', 'wc-kshippingargentina' ),
				'type'        => 'textarea',
				'placeholder' => '',
				'default'     => $this->mail_message,
			),
		);
	}

}
