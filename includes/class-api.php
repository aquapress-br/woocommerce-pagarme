<?php

namespace Aquapress\Pagarme;

/**
 * Integration with pagar.me r api.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\API class.
 *
 * @since 1.0.0
 */
class API {

	/**
	 * API URL.
	 *
	 * The base URL for the payment gateway's API endpoints.
	 *
	 * @var string
	 */
	const API_URL = 'https://api.pagar.me/core/v5';

	/**
	 * Configuration class for API integration.
	 *
	 * Handles the storage and retrieval of API keys and debug settings.
	 *
	 * @var Aquapress\Pagarme\Config
	 */
	public \Aquapress\Pagarme\Config $config;

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
	 * Constructor.
	 *
	 * Initializes the secret API key, encryption key, and logger.
	 * If any of these values are not provided, it attempts to use
	 * default values from filters or initializes a logger automatically.
	 *
	 * @param string|false $config     The configuration class for API integration. Defaults to a filtered value.
	 * @param object|false $logger         The logger instance for debugging. If not provided, a new logger is initialized.
	 */
	public function __construct( $config, $logger = false ) {
		// Initialize the API settings, using a filter if not provided.
		$this->config = $config;
		// Initialize the logger. If none is provided, initialize it automatically.
		if ( $logger ) {
			$this->logger = $logger;
		}
	}

	/**
	 * Retrieve the stored API Access Key
	 *
	 * @since 1.0.0
	 *
	 * @param endpoint         $endpoint API endpoint to merge.
	 *
	 * @return string
	 */
	private function get_api_url( $endpoint = '' ) {
		if ( $endpoint ) {
			$url = sprintf( '%s/%s', static::API_URL, $endpoint );
			// Remove double slashes, but keep those after "http://", "https://", etc.
			return preg_replace( '#(?<!:)//+#', '/', $url );
		}
		return static::API_URL;
	}

	/**
	 * Do save customer in Pagar.me API.
	 *
	 * @param  array    $payload     Save customer payload.
	 * @return string|false          Saved customer id.
	 */
	public function do_save_customer( $payload = array() ) {

		$this->debug( 'Save customer data: ' . var_export( $payload, true ) );

		// Perform the request.
		$response = $this->do_request( '/customers', 'POST', $payload );

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'Save customer data successfully! The response is: ' . var_export( $data, true ) );

			do_action( 'wc_pagarme_saved_customer_data', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}

	/**
	 * Do the transaction.
	 *
	 * @param  array    $args  Transaction args.
	 * @param  string   $token Checkout token.
	 *
	 * @return array           Response data.
	 */
	public function do_transaction( $payload = array(), $token = '' ) {

		$this->debug( 'Doing a transaction for order ' . var_export( $payload, true ) );

		// Check the capture token to endpoint.
		$endpoint = ! empty( $token ) ? '/charges/' . $token . '/capture' : '/orders';

		// Perform the request.
		$response = $this->do_request( $endpoint, 'POST', $payload );
		

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'Transaction completed successfully! The transaction response is: ' . var_export( $data, true ) );

			do_action( 'wc_pagarme_processed_transaction_data', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}

	/**
	 * Get recipient list.
	 *
	 * @param  array    $payload     Save customer payload.
	 * @return array|false           Recipient list.
	 */
	public function get_recipients( $payload = array() ) {

		$this->debug( 'Get recipients ' . var_export( $payload, true ) );

		$response = $this->do_request( '/recipients', 'GET', $payload );

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'Failed in doing save data: ' . var_export( $response, true ) );

			do_action( 'wc_pagarme_processed_recipients_data', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}

	/**
	 * Do save recipient in Pagar.me API.
	 *
	 * @param  array    $payload     Save customer payload.
	 * @return string|false          Saved customer id.
	 */
	public function do_save_recipient( $payload = array() ) {

		$this->debug( 'Request save recipient data: ' . var_export( $payload, true ) );

		$response = $this->do_request( '/recipients', 'POST', $payload );

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'Saved recipient data successfully! The endpoint response is: ' . var_export( $data, true ) );

			do_action( 'wc_pagarme_saved_recipient_data', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}

	/**
	 * Do update recipient in Pagar.me API.
	 *
	 * @param  array    $recipient_id     The recipient id in pagarme.
	 * @param  array    $payload     Save customer payload.
	 * @return string|false          Saved customer id.
	 */
	public function do_update_recipient( $recipient_id, $payload = array() ) {

		$this->debug( 'Request update recipient data: ' . var_export( $payload, true ) );

		$response = $this->do_request( '/recipients/' . $recipient_id, 'PUT', $payload );

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'Updated recipient data successfully! The endpoint response is: ' . var_export( $data, true ) );

			do_action( 'wc_pagarme_updated_recipient_data', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}

	/**
	 * Get KYC link.
	 *
	 * @param  string    $recipient_id
	 * @return array|false
	 */
	public function get_kyc_link( $recipient_id ) {

		$this->debug( 'Get KYC link: ' . var_export( $payload, true ) );

		$response = $this->do_request( '/recipients/' . $recipient_id . '/kyc_link', 'POST' );

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'KYC Link completed successfully! The endpoint response is: ' . var_export( $data, true ) );

			do_action( 'wc_pagarme_processed_kyc_link', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}

	/**
	 * Get recipient balance.
	 *
	 * @param  string    $recipient_id
	 * @return array|false
	 */
	public function get_recipient_balance( $recipient_id ) {

		$this->debug( 'Get recipient balance: ' . var_export( $recipient_id, true ) );

		$response = $this->do_request( '/recipients/' . $recipient_id . '/balance', 'GET' );

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'Get recipient balance successfully! The endpoint response is: ' . var_export( $data, true ) );

			do_action( 'wc_pagarme_processed_recipient_balance', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}

	/**
	 * Get recipient operations.
	 *
	 * @return array|false
	 */
	public function get_recipient_operations( $payload = array() ) {

		$this->debug( 'Get recipient operations: ' . var_export( $payload, true ) );

		$response = $this->do_request( '/balance/operations', 'GET', $payload );

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'Get recipient operations successfully! The endpoint response is: ' . var_export( $data, true ) );

			do_action( 'wc_pagarme_processed_recipient_operations', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}

	/**
	 * Get recipient payables.
	 *
	 * @return array|false
	 */
	public function get_recipient_payables( $payload = array() ) {

		$this->debug( 'Get recipient payables: ' . var_export( $payload, true ) );

		$response = $this->do_request( '/payables', 'GET', $payload );

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'Get recipient payables successfully! The endpoint response is: ' . var_export( $data, true ) );

			do_action( 'wc_pagarme_processed_recipient_payables', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}


	/**
	 * Get webhooks.
	 *
	 * @return array|false
	 */
	public function get_webhooks( $payload = array() ) {

		$this->debug( 'Get webhooks: ' . var_export( $payload, true ) );

		$response = $this->do_request( '/hooks', 'GET', $payload );

		// Process response data.
		if ( ! is_wp_error( $response ) ) {

			$data = $this->api_remote_retrieve_body( $response );

			$this->debug( 'Get webhooks successfully! The endpoint response is: ' . var_export( $data, true ) );

			do_action( 'wc_pagarme_processed_webhooks', $data );

			return $data;

		} else {
			throw new \Exception( $response->get_error_message() );
		}
	}

	/**
	 * Perform a request to the server API.
	 *
	 * @param array $body Request arguments (for the request body).
	 * @return WP_Error|string
	 */
	public function do_request( $endpoint, $method, $body = array(), $headers = array(), $timeout = 90 ) {

		/**
		 * Filter the API request URL
		 *
		 * The API Access key is intentionally excluded from the URL and appended as a query string
		 * variable after this filter has run.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url Default API Request URL.
		 * @param array $body API POST Request arguments.
		 */
		$url = apply_filters( 'wc_pagarme_api_url', $this->get_api_url( $endpoint ), $body );

		/**
		 * Filter the API request arguments
		 *
		 * @since 1.0.0
		 *
		 * @param array $args API request arguments.
		 * @param string $url API Request URL.
		 */
		$args = apply_filters( 'wc_pagarme_api_request_args', $this->api_get_request_args( $method, $body, $headers, $timeout ), $url );

		// Perform the request.
		$response = wp_remote_post( $url, $args );

		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			// Log errors.
			$this->debug( 'API request fail: ' . var_export( $args, true ) );
			$this->debug( 'API response fail: ' . var_export( $response, true ) );
			// Check response erros.
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			//  Return error message if existis.
			return new \WP_Error( '', $body['message'] ?? '' );
		}

		return $response;
	}

	/**
	 * Setup full API request arguments.
	 *
	 * Adds some default arguments and request headers to supplied arguments.
	 *
	 * @param string $method The request method.
	 * @param array $body Request arguments (for the request body).
	 * @return array
	 */
	protected function api_get_request_args( $method = 'POST', $body = array(), $headers = array(), $timeout = 90 ) {
		return array(
			'method'  => $method,
			'body'    => 'GET' !== $method ? wp_json_encode( $body ) : $body,
			'headers' => array_merge(
				$headers,
				array(
					'Content-Type'  => 'application/json',
					'User-Agent'    => sprintf( 'AquapressGateway/%1$s/wordpress', WC_PAGARME_VERSION ),
					'Authorization' => 'Basic ' . base64_encode( sprintf( '%s:', $this->config->get_secret_key() ) ),
				)
			),
			'timeout' => $timeout,
		);
	}

	/**
	 * Decodes a JSON response from an API into an associative array.
	 *
	 * @param array $response The WP response from the API.
	 * @return array Returns the decoded associative array.
	 *               If decoding fails, a JsonException is thrown.
	 * @throws JsonException If the JSON decoding fails.
	 */
	public function api_remote_retrieve_body( $response ) {
		if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
			return [];
		}
		// Decode the JSON response into an associative array.
		$data = json_decode( $response['body'], true, 512, JSON_THROW_ON_ERROR ); // Calls an Exception on failure.

		// Return the decoded array.
		return $data;
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
		if ( $this->config->debug === 'yes' || $this->config->debug === 'on' || $this->config->debug === true ) {
			if ( ! $this->logger ) {
				$this->logger = new \Aquapress\Pagarme\Logger();
			}
			$this->logger->add( $message, $start_time, $end_time );
		}
	}
}
