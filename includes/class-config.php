<?php

namespace Aquapress\Pagarme;

/**
 * Configuration class for API integration.
 *
 * Handles the storage and retrieval of API keys and debug settings.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aquapress\Pagarme\Config class.
 *
 * @since 1.0.0
 */
class Config {

	/**
	 * Secret API key.
	 *
	 * Used for authenticating requests to the payment gateway's API.
	 *
	 * @var string
	 */
	public $secret_key = '';

	/**
	 * Public API key.
	 *
	 * Used for client-side authentication to the payment gateway's API.
	 *
	 * @var string
	 */
	public $public_key = '';

	/**
	 * Debug mode.
	 *
	 * If true, enables detailed logging for debugging purposes.
	 *
	 * @var mixed
	 */
	public $debug = false;

	/**
	 * Test mode.
	 *
	 * If true, enables testmode authentication.
	 *
	 * @var mixed
	 */
	public $testmode = false;

	/**
	 * Constructor.
	 *
	 * Initializes the configuration with the provided values or defaults.
	 *
	 * @param string $settings The settings array.
	 */
	public function __construct( $settings = array() ) {
		$this->debug      = ( 'yes' == $settings['debug'] || 'on' == $settings['debug'] ) ? true : false;
		$this->testmode   = ( 'yes' == $settings['testmode'] || 'on' == $settings['testmode'] ) ? true : false;
		
		if ( $this->testmode ) {
			$this->public_key = $settings['public_key_sanbox'];
			$this->secret_key = $settings['secret_key_sanbox'];
		} else {
			$this->public_key = $settings['public_key'];
			$this->secret_key = $settings['secret_key'];
		}
	}

	/**
	 * Set the secret API key.
	 *
	 * @param string $secret_key The secret API key.
	 * @return void
	 */
	public function set_secret_key( $secret_key ) {
		$this->secret_key = $secret_key;
	}

	/**
	 * Get the secret API key.
	 *
	 * @return string The secret API key.
	 */
	public function get_secret_key() {
		return $this->secret_key;
	}

	/**
	 * Set the public API key.
	 *
	 * @param string $public_key The public API key.
	 * @return void
	 */
	public function set_public_key( $public_key ) {
		$this->public_key = $public_key;
	}

	/**
	 * Get the public API key.
	 *
	 * @return string The public API key.
	 */
	public function get_public_key() {
		return $this->public_key;
	}

	/**
	 * Set the debug mode.
	 *
	 * @param bool $debug Whether to enable debug mode.
	 * @return void
	 */
	public function set_debug( $debug ) {
		$this->debug = $debug;
	}

	/**
	 * Get the debug mode status.
	 *
	 * @return bool True if debug mode is enabled, false otherwise.
	 */
	public function is_debug() {
		return $this->debug;
	}

	/**
	 * Get the test mode status.
	 *
	 * @return bool True if test mode is enabled, false otherwise.
	 */
	public function is_testmode() {
		return $this->testmode;
	}
}
