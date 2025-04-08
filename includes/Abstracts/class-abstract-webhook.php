<?php

namespace Aquapress\Pagarme\Abstracts;

/**
 * Abstract class that will be inherited by all Webhooks.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abstract class that will be inherited by all Webhooks.
 */
abstract class Webhook {

	/**
	 * Webhook identifier.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Webhook restricted events.
	 *
	 * @var string
	 */
	public $events = array();

	/**
	 * Logger instance.
	 *
	 * This attribute is used to record events and log messages.
	 * The instance may be an object of a specific logging class or
	 * a similar resource.
	 *
	 * @var Aquapress\Pagarme\Logger
	 */
	public \Aquapress\Pagarme\Logger $logger;

	/**
	 * Execute the webhook actions.
	 *
	 * Run child class additional actions hooks.
	 *
	 * @param string $event Webhook event.
	 * @param array $data Webhook data.
	 *
	 * @return void
	 */
	abstract public function process( $event = '', $data = array() );

	/**
	 * Initializes the Pagar.me marketplace Webhook.
	 *
	 * Sets up the required components for the connector, including:
	 * - Logger initialization for debugging and event tracking.
	 * - Hook registration for WordPress integration.
	 *
	 * @return void
	 */
	public function init_webhook() {
		// Initialize the logging and other hooks.
		$this->init_logger();
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
	public function init_logger() {
		$this->logger = new \Aquapress\Pagarme\Logger( 'wc_pagarme_' . $this->id . '_webhook' );
	}

	/**
	 * Initialize and register connector action hooks.
	 *
	 * @return void
	 */
	public function init_actions() {
		add_action( 'woocommerce_api_wc_pagarme_webhook', array( $this, 'webhook_handler' ) );
	}

	/**
	 * IPN handler.
	 */
	public function webhook_handler() {
		@ob_clean();

		$webhook_response = file_get_contents( 'php://input' );

		if ( $webhook_response ) {
			$webhook_body = @json_decode( $webhook_response, true );
			// Check is valid webhook body response.
			if ( is_array( $webhook_body ) && isset( $webhook_body['type'], $webhook_body['data'] ) ) {
				if ( in_array( $webhook_body['type'], $this->events ) ) {
					// Register webhook body response.
					$this->debug( 'Gateway received a body content: ' . var_export( $webhook_response, true ) );
					// Initialize any additional hooks needed by the webhook in the child class.
					$this->process( $webhook_body['type'], $webhook_body['data'] );
				}
			}
			exit;
		}
	}

	/**
	 * Debug logger.
	 *
	 * @param string $message      Log message.
	 * @param int    $start_time   Start time (optional).
	 * @param int    $end_time     End time (optional).
	 *
	 * @return void
	 */
	public function debug( $message, $start_time = null, $end_time = null ) {
		if ( ! $this->logger ) {
			$this->logger = new \Aquapress\Pagarme\Logger();
		}
		$this->logger->add( $message, $start_time, $end_time );
	}
}
