<?php
/**
 * Pagar.me PIX gateway
 *
 * @package WooCommerce_Pagarme/Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Pagarme_PIX_Gateway class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Pagarme_PIX_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                   = 'pagarme-pix';
		$this->icon                 = apply_filters( 'wc_pagarme_pix_icon', false );
		$this->has_fields           = true;
		$this->method_title         = __( 'Pagar.me - PIX', 'wc-pagarme' );
		$this->method_description   = __( 'Accept PIX using Pagar.me.', 'wc-pagarme' );
		$this->view_transaction_url = 'https://dashboard.pagar.me/#/transactions/%s';

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title          = $this->get_option( 'title' );
		$this->description    = $this->get_option( 'description' );
		$this->api_key        = $this->get_api_key();
		$this->encryption_key = $this->get_encryption_key();
		$this->debug          = $this->get_option( 'debug' );
		$this->expiration     = $this->get_option( 'expiration' );
		$this->supports       = array( 'Products', 'subscriptions', 'subscription_suspension', 'subscription_reactivation', 'subscription_amount_changes', 'subscription_date_changes', 'subscription_payment_method_change', 'subscription_payment_method_change_customer', 'subscription_payment_method_change_admin' );

		// Active logs.
		if ( 'yes' === $this->debug ) {
			$this->log = new WC_Logger();
		}

		// Set the API.
		$this->api = new WC_Pagarme_API( $this );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
		add_action( 'woocommerce_api_wc_pagarme_pix_gateway', array( $this, 'ipn_handler' ) );
		//add_action( 'woocommerce_scheduled_subscription_payment_pagarme-pix', array( $this, 'scheduled_subscription' ), 10, 2 );
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		include __DIR__ . '/admin/views/html-admin-page.php';
	}

	/**
	 * Check if the gateway is available to take payments.
	 *
	 * @return bool
	 */
	public function is_available() {
		return parent::is_available() && ! empty( $this->api_key ) && ! empty( $this->encryption_key ) && $this->api->using_supported_currency();
	}

	/**
	 * Settings fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'        => array(
				'title'   => __( 'Enable/Disable', 'wc-pagarme' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Pagar.me PIX', 'wc-pagarme' ),
				'default' => 'no',
			),
			'title'          => array(
				'title'       => __( 'Title', 'wc-pagarme' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wc-pagarme' ),
				'desc_tip'    => true,
				'default'     => __( 'PIX', 'wc-pagarme' ),
			),
			'description'    => array(
				'title'       => __( 'Description', 'wc-pagarme' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'wc-pagarme' ),
				'desc_tip'    => true,
				'default'     => __( 'Pay with PIX', 'wc-pagarme' ),
			),
			'integration'    => array(
				'title'       => __( 'Integration Settings', 'wc-pagarme' ),
				'type'        => 'title',
				'description' => '',
			),
			'api_key'        => array(
				'title'             => __( 'Pagar.me API Key', 'wc-pagarme' ),
				'type'              => 'text',
				'description'       => sprintf( __( 'Please enter your Pagar.me API Key. This is needed to process the payment and notifications. Is possible get your API Key in %s.', 'wc-pagarme' ), '<a href="https://dashboard.pagar.me/">' . __( 'Pagar.me Dashboard > My Account page', 'wc-pagarme' ) . '</a>' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'encryption_key' => array(
				'title'             => __( 'Pagar.me Encryption Key', 'wc-pagarme' ),
				'type'              => 'text',
				'description'       => sprintf( __( 'Please enter your Pagar.me Encryption key. This is needed to process the payment. Is possible get your Encryption Key in %s.', 'wc-pagarme' ), '<a href="https://dashboard.pagar.me/">' . __( 'Pagar.me Dashboard > My Account page', 'wc-pagarme' ) . '</a>' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'expiration'     => array(
				'title'       => __( 'Minutes for Expiration', 'wc-pagarme' ),
				'description' => sprintf( __( 'It is the number of minutes for Pix to expire. By default one day, that is, 1440 minutes', 'wc-pagarme' ) ),
				'default'     => '1440',
			),
			'testing'        => array(
				'title'       => __( 'Gateway Testing', 'wc-pagarme' ),
				'type'        => 'title',
				'description' => '',
			),
			'debug'          => array(
				'title'       => __( 'Debug Log', 'wc-pagarme' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'wc-pagarme' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Pagar.me events, such as API requests. You can check the log in %s', 'wc-pagarme' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'wc-pagarme' ) . '</a>' ),
			),
		);
	}

	/**
	 * Payment fields.
	 */
	public function payment_fields() {
		if ( $description = $this->get_description() ) {
			echo wp_kses_post( wpautop( wptexturize( $description ) ) );
		}

		wc_get_template(
			'pix/checkout-instructions.php',
			array(),
			'woocommerce/pagarme/',
			WC_Pagarme::get_templates_path()
		);
	}

	/**
	 * Process subscription renewal.
	 *
	 * @param float $amount
	 * @param WC_Order $renewal_order
	 */
	public function scheduled_subscription( $amount, $order ) {

		$this->log->add( $this->id, 'Init subscription renewal for order ID: ' . $order->get_id() );
		$this->api->process_regular_payment( $order->get_id() );
	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array Redirect data.
	 */
	public function process_payment( $order_id ) {
		return $this->api->process_regular_payment( $order_id );
	}

	/**
	 * Thank You page message.
	 *
	 * @param int $order_id Order ID.
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		$data  = get_post_meta( $order_id, '_wc_pagarme_transaction_data', true );

		if ( isset( $data['pix_qr_code'] ) && in_array( $order->get_status(), array( 'processing', 'on-hold' ), true ) ) {

			wc_get_template(
				'pix/payment-instructions.php',
				array(
					'url'  => WC_Pagarme_Helper::qrcode_generator( $data['pix_qr_code'] ),
					'code' => $data['pix_qr_code'],
				),
				'woocommerce/pagarme/',
				WC_Pagarme::get_templates_path()
			);
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param  object $order         Order object.
	 * @param  bool   $sent_to_admin Send to admin.
	 * @param  bool   $plain_text    Plain text or HTML.
	 *
	 * @return string                Payment instructions.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $sent_to_admin || ! in_array( $order->get_status(), array( 'on-hold' ), true ) || $this->id !== $order->payment_method ) {
			return;
		}

		$data = get_post_meta( $order->id, '_wc_pagarme_transaction_data', true );

		if ( isset( $data['pix_qr_code'] ) ) {
			$email_type = $plain_text ? 'plain' : 'html';
			wc_get_template(
				'pix/emails/' . $email_type . '-instructions.php',
				array(
					'url'  => WC_Pagarme_Helper::qrcode_generator( $data['pix_qr_code'] ),
					'code' => $data['pix_qr_code'],
				),
				'woocommerce/pagarme/',
				WC_Pagarme::get_templates_path()
			);
		}
	}

	public function get_api_key() {

		if ( class_exists( 'WeDevs_Dokan' ) ) {
			$dokan_options = get_option( 'pagarme_gateway_admin_settings' );

			return $dokan_options['api_key'];
		}

		return $this->get_option( 'api_key' );
	}

	/**
	 * Get encryption key.
	 */
	public function get_encryption_key() {

		if ( class_exists( 'WeDevs_Dokan' ) ) {
			$dokan_options = get_option( 'pagarme_gateway_admin_settings' );

			return $dokan_options['encryption_key'];
		}

		return $this->get_option( 'encryption_key' );
	}

	/**
	 * IPN handler.
	 */
	public function ipn_handler() {
		$this->api->ipn_handler();
	}
}
