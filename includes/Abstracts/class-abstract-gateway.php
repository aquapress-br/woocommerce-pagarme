<?php

namespace Aquapress\Pagarme\Abstracts;

/**
 * Abstract class that will be inherited by all payments methods.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * \Aquapress\Pagarme\Abstracts\Gateway class.
 *
 * @since 1.0.0
 */
abstract class Gateway extends \WC_Payment_Gateway {

	use \Aquapress\Pagarme\Traits\Base_Gateway_General;

	/**
	 * API handler instance.
	 *
	 * This attribute stores an instance of the `Aquapress\Pagarme\API` class, which is responsible
	 * for managing communication between the Pagar.me plugin and the Pagar.me API.
	 * It handles requests such as processing payments, refunds, and retrieving transaction details
	 * from the Pagar.me platform, ensuring seamless integration with the WooCommerce store.
	 *
	 * @var Aquapress\Pagarme\API
	 */
	public \Aquapress\Pagarme\API $api;

	/**
	 * Run child class hooks.
	 *
	 * @return void
	 */
	abstract public function init_hooks();

	/**
	 * Merge payload method data with transaction data.
	 *
	 * @param mixed  $the_order  Woocommerce Order ID or Object WC_Order.
	 *
	 * @return array
	 */
	abstract public function build_payload_data( $the_order );

	/**
	 * Initializes the Pagar.me payment gateway.
	 *
	 * This method sets up the payment method by initializing various components such as form fields,
	 * settings, and API connections. It also prepares the gateway's title, description, and configuration
	 * options based on the stored settings. Additionally, it enables logging, sets up hooks,
	 * and loads required files for the payment method to function properly.
	 *
	 * @return void
	 */
	public function init_gateway() {
		// Enable custom form fields for this gateway.
		$this->has_fields = true;

		// Initialize the form fields and gateway settings.
		$this->init_form_fields();
		$this->init_settings();

		// Load settings for the gateway title, description, and enabled state.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );
		$this->debug       = $this->get_option( 'debug' );

		// Initialize the API and set up logging and other hooks.
		$this->init_api();
		$this->init_actions();
	}

	/**
	 * Initializes the Pagar.me API instance.
	 *
	 * This method checks if the `$api` attribute is already set. If not, it creates a new instance
	 * of the `Aquapress\Pagarme\API` class using the provided API key and encryption key. This ensures
	 * that the plugin can communicate with the Pagar.me platform for handling transactions,
	 * refunds, and other API interactions.
	 *
	 * @return void
	 */
	public function init_api() {
		if ( empty( $this->api ) ) {
			$this->api = \Aquapress\Pagarme\Helpers\Factory::Load_API(
				$this->id
			);
		}
	}

	/**
	 * Initialize and register connector action hooks.
	 *
	 * This method registers the necessary action hooks for the Pagar.me connector
	 * within the WordPress environment. It ensures that critical tasks, such as
	 * scheduling recipient updates and triggering actions for recipient management,
	 * are properly executed at the right points during the request lifecycle.
	 *
	 * Additionally, this method calls `init_hooks()` to initialize any other custom hooks
	 * for the connector in the child class.
	 *
	 * @return void
	 */
	public function init_actions() {
		// Initialize any additional hooks needed by the connector in the child class.
		$this->init_hooks();
	}

	/**
	 * Processes the payment for the specified order.
	 *
	 * This method handles the payment process after a payment request has been sent
	 * during checkout. It uses the provided `$order_id` to retrieve order details and
	 * complete the payment transaction. The method typically involves interacting with
	 * the payment gateway to process the payment and may return an array containing the
	 * result of the payment operation, such as the payment status and any relevant messages.
	 *
	 * @param string $order_id The ID of the order being processed.
	 *
	 * @return array An array containing the result of the payment processing, which may include
	 *               payment status, redirect URLs, or error messages.
	 */
	public function process_payment( $order_id ) {
		// Get order data.
		$order = wc_get_order( $order_id );
		// Process payment API.
		try {
			// Process transaction request.
			$transaction = $this->api->do_transaction(
				apply_filters(
					'wc_pagarme_transaction_data',
					array_merge(
						\Aquapress\Pagarme\Helpers\Payload::Build_Transaction_Payload( $order ),
						$this->build_payload_data( $order )
					),
					$order,
					$this
				)
			);
			// Process order status and save response info.
			$this->save_order_meta_fields( $order_id, $transaction );
			$this->process_order_status( $order_id, $transaction['status'] );
			// Go to order received page.
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		} catch ( \Exception $e ) {
			// Output checkout error message.
			wc_pagarme_add_checkout_notice(
				__( 'Não foi possível processar o pagamento. Verifique as informações fornecidas e tente novamente. Se o problema persistir, entre em contato para obter mais informações.', 'wc-pagarme' ),
				'error'
			);
		}
		// Go to checkout.
		return array( 'result' => 'failure' );
	}

	/**
	 * Checks if the Pagar.me payment method is available.
	 *
	 * This method determines whether the payment method can be used during checkout.
	 * It typically checks various conditions such as whether the API is properly initialized,
	 * the store's configuration, and whether the payment gateway is enabled for the current order.
	 * If the conditions are met, it returns `true`, making the payment method available for use.
	 *
	 * @return bool  True if the payment method is available, false otherwise.
	 */
	public function is_available() {
		$is_enabled              = $this->enabled == 'yes';
		$is_available_brcheckout = class_exists( 'Extra_Checkout_Fields_For_Brazil' );
		$is_available_currency   = get_woocommerce_currency() == 'BRL';

		if ( $is_enabled
			&& $is_available_brcheckout
			&& $is_available_currency ) {
			return true;
		}

		return false;
	}
}
