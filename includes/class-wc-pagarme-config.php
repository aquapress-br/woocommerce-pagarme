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

defined('ABSPATH') || exit;

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
    public string $secret_key = '';

    /**
     * Public API key.
     *
     * Used for client-side authentication to the payment gateway's API.
     *
     * @var string
     */
    public string $public_key = '';

    /**
     * Debug mode.
     *
     * If true, enables detailed logging for debugging purposes.
     *
     * @var bool
     */
    public bool $debug = false;

    /**
     * Constructor.
     *
     * Initializes the configuration with the provided values or defaults.
     *
     * @param string $secret_key The secret API key for authentication.
     * @param string $public_key The public API key for client-side authentication.
     * @param bool   $debug      Debug mode (default: false).
     */
    public function __construct(string $secret_key = '', string $public_key = '', bool $debug = false) {
        $this->secret_key = $secret_key;
        $this->public_key = $public_key;
        $this->debug = $debug;
    }
	
    /**
     * Set the secret API key.
     *
     * @param string $secret_key The secret API key.
     * @return void
     */
    public function set_secret_key(string $secret_key): void {
        $this->secret_key = $secret_key;
    }

    /**
     * Get the secret API key.
     *
     * @return string The secret API key.
     */
    public function get_secret_key(): string {
        return $this->secret_key;
    }

    /**
     * Set the public API key.
     *
     * @param string $public_key The public API key.
     * @return void
     */
    public function set_public_key(string $public_key): void {
        $this->public_key = $public_key;
    }

    /**
     * Get the public API key.
     *
     * @return string The public API key.
     */
    public function get_public_key(): string {
        return $this->public_key;
    }

    /**
     * Set the debug mode.
     *
     * @param bool $debug Whether to enable debug mode.
     * @return void
     */
    public function set_debug(bool $debug): void {
        $this->debug = $debug;
    }

    /**
     * Get the debug mode status.
     *
     * @return bool True if debug mode is enabled, false otherwise.
     */
    public function get_debug(): bool {
        return $this->debug;
    }
	
}
