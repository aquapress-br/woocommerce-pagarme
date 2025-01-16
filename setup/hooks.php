<?php defined( 'ABSPATH' ) || exit;

/**
 * Registers the activation hook for the plugin.
 * This hook is triggered when the plugin is activated.
 *
 * @param string WC_PAGARME_BASENAME The plugin's main file path.
 * @param callable wc_pagarme_plugin_activate The function to run during activation.
 */
register_activation_hook( WC_PAGARME_BASENAME, 'wc_pagarme_plugin_activate' );

/**
 * Registers the deactivation hook for the plugin.
 * This hook is triggered when the plugin is deactivated.
 *
 * @param string WC_PAGARME_BASENAME The plugin's main file path.
 * @param callable wc_pagarme_plugin_deactivate The function to run during deactivation.
 */
register_deactivation_hook( WC_PAGARME_BASENAME, 'wc_pagarme_plugin_deactivate' );

/**
 * Loads the plugin's text domain for translations after all plugins are loaded.
 *
 * This hook ensures that the Pagar.me plugin is ready for internationalization
 * by loading its text domain for translation when all plugins are initialized.
 */
add_action( 'plugins_loaded', 'wc_pagarme_plugin_i18n' );

/**
 * Adds custom action links for the Pagar.me plugin on the WordPress admin plugins page.
 *
 * This hook adds links (e.g., settings and support) to the Pagar.me plugin on the
 * plugins management screen, allowing quick access to important plugin-related pages.
 */
add_filter( 'plugin_action_links_' . WC_PAGARME_BASENAME, 'wc_pagarme_admin_links' );

/**
 * Registers essential components of the Pagar.me plugin at WordPress initialization.
 *
 * These hooks are responsible for initializing key features of the plugin. They register
 * the custom Pagar.me payment gateways and marketplaces during WordPress's 'init' action.
 */
add_action( 'init', 'wc_pagarme_plugin_run' );
