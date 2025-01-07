<?php

namespace Aquapress\Pagarme\Helpers;

/**
 * Helper class factory, providing them with functions to ensure functionality.
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * \Aquapress\Pagarme\Helpers\Factory class.
 *
 * @since 1.0.0
 */
class Factory {

	/**
	 * Load an API instance.
	 *
	 * Creates and returns a new instance of the `Aquapress\Pagarme\API` class.
	 * The instance is initialized with a configuration object and a logger instance.
	 *
	 * @param string|false $key. The option meta key to retrieve API settings.
	 *                          Defaults to false.
	 * @param Aquapress\Pagarme\Logger $logger Optional. The instance for logger.
	 * @return Aquapress\Pagarme\API A new API instance with its configuration and logger set up.
	 */
	public static function Load_API( $key = false, $logger = false ) {
		$default_settings = array(
			// Live mode.
			'secret_key'         => '', // The default API secret key.
			'public_key'         => '', // The default API public key.
			// Sandbox mode.
			'secret_key_sandbox' => '', // The default API secret key for sandbox.
			'public_key_sandbox' => '', // The default API public key for sandbox.
			// Enable or disable API request debugging.
			'testmode'           => 'no',
			'debug'              => 'no',
		);
		// Check if a specific key is provided.
		if ( $key ) {
			if ( in_array( $key, array( 'wc_pagarme_creditcard', 'wc_pagarme_pix', 'wc_pagarme_boleto' ) ) ) {
				$key_prefix = 'woocommerce_';
			} elseif ( $key == 'wc_pagarme_marketplace' ) {
				$key_prefix = '';
			}
			// Retrieve the settings from the WordPress database using get_option.
			$stored_settings = get_option( $key_prefix . $key . '_settings', array() );
			// Filter data and parse defaults args.
			$settings = apply_filters( 'wc_pagarme_load_api_settings', wp_parse_args( $stored_settings, $default_settings ), $key );
			// Build instances for Config.
			if ( 'yes' == $settings['testmode'] ) {
				$config = new \Aquapress\Pagarme\Config( $settings['secret_key_sandbox'], $settings['public_key_sandbox'], $settings['debug'] );
			} else {
				$config = new \Aquapress\Pagarme\Config( $settings['secret_key'], $settings['public_key'], $settings['debug'] );
			}
			// Build instances for Logger.
			if ( ! $logger ) {
				$logger = new \Aquapress\Pagarme\Logger( $key );
			}

			return new \Aquapress\Pagarme\API( $config, $logger );
		}

		return false; // Return false if no key is provided.
	}
}
