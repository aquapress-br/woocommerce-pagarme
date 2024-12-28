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
	 * These dependencies are loaded dynamically through `Load_Config` and `Load_Logger`.
	 *
	 * @param string|false $key Optional. A key to customize the API instance configuration or logger. 
	 *                          Defaults to false.
	 * @param bool                     $sandbox Optional. Enable the sandbox API involvement. 
	 * @param Aquapress\Pagarme\Logger $logger Optional. The instance for logger. 
	 * @return Aquapress\Pagarme\API A new API instance with its configuration and logger set up.
	 */
	public static function Load_API( $key = false, $sandbox = false, $logger = false ) {
		// Check if a specific key is provided.
		if ( $key ) {
			if ( strpos($key, 'wc_pagarme_') !== 0 ) {
				$key = 'wc_pagarme_' . $key;
			}
			// Retrieve the settings from the WordPress database using get_option.
			$settings = get_option(
				$key . '_settings',
				array(
					'debug' => false, // Enable or disable API request debugging.
					'secret_key' => apply_filters( 'wc_pagarme_default_secret_key', '', $key ), // The default API secret key.
					'secret_key_sandbox' => apply_filters( 'wc_pagarme_default_secret_key_sandbox', '', $key ), // The default API secret key for sandbox.
				)
			);			
			// Build instances for Config.
			$config = new \Aquapress\Pagarme\Config( $sandbox ? $settings['secret_key_sandbox'] : $settings['secret_key'], false, $settings['debug'] );
			// Build instances for Logger.
			if ( !$logger ) {
				$logger = new \Aquapress\Pagarme\Logger( $key );
			}
			
			return new \Aquapress\Pagarme\API( $config, $logger );		
		}

		return false; // Return false if no key is provided.
	}
}
