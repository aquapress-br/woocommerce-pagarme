<?php

if ( ! function_exists( 'wc_pagarme_plugin_i18n' ) ) {
	/**
	 * Loads the plugin text domain to enable translations.
	 *
	 * This function ensures that the plugin's text domain is loaded,
	 * allowing the plugin's strings to be translated into different languages.
	 * It uses the `load_plugin_textdomain` function to load the necessary
	 * translation files from the specified directory.
	 *
	 * @since    1.0.0
	 *
	 * @return   void
	 */
	function wc_pagarme_plugin_i18n() {
		// Load the text domain for the 'wc-pagarme' plugin, fetching translations from the 'i18n' folder.
		load_plugin_textdomain( 'wc-pagarme', false, dirname( plugin_basename( __FILE__ ), 2 ) . '/i18n' );
	}
}
