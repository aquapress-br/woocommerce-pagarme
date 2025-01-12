<?php

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
 *
 * @see wc_pagarme_marketplaces_register() For marketplace-specific registration logic.
 * @see wc_pagarme_gateways_register() For payment gateway registration.
 */
add_action( 'init', 'wc_pagarme_gateways_register' );
add_action( 'init', 'wc_pagarme_marketplaces_register' );
add_action( 'init', 'wc_pagarme_resources_register' );
