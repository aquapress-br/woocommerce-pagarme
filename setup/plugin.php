<?php defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_pagarme_plugin_activate' ) ) {

	/**
	 * Plugin activate call function
	 *
	 * @see wc_pagarme_migrations_register() For migrations process registration logic.
	 * @see wc_pagarme_tasks_register() For schedule events registration.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	function wc_pagarme_plugin_activate() {
		wc_pagarme_migrations_register();
		wc_pagarme_tasks_register();
	}
}

if ( ! function_exists( 'wc_pagarme_plugin_deactivate' ) ) {

	/**
	 * Plugin deactivation call function
	 *
	 * @see wc_pagarme_tasks_unregister() To cancel registration for scheduled events.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	function wc_pagarme_plugin_deactivate() {
		wc_pagarme_tasks_unregister();
	}
}

if ( ! function_exists( 'wc_pagarme_plugin_run' ) ) {

	/**
	 * Begins run of the plugin.
	 *
	 * @see wc_pagarme_marketplaces_register() For marketplace-specific registration logic.
	 * @see wc_pagarme_gateways_register() For payment gateway registration.
	 * @see wc_pagarme_webhooks_register() For webhooks registration.
	 * @see wc_pagarme_resources_register() For resources registration.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	function wc_pagarme_plugin_run() {
		do_action( 'wc_pagarme_before_plugin_run' );
		wc_pagarme_gateways_register();
		wc_pagarme_marketplaces_register();
		wc_pagarme_webhooks_register();
		wc_pagarme_resources_register();
		wc_pagarme_tasks_register();
		do_action( 'wc_pagarme_plugin_run' );
	}
}

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
		load_plugin_textdomain( 'wc-pagarme', false, dirname( WC_PAGARME_BASENAME ) . '/i18n' );
	}
}
