<?php defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_pagarme_admin_links' ) ) {
	/**
	 * Adds shortcut links to the plugin's settings and support page in the WordPress admin area.
	 *
	 * This function is responsible for appending quick access links to the plugin's settings page
	 * in the WordPress admin panel, as well as a link to the Pagar.me support page. These links
	 * appear under the plugin's name on the installed plugins page, providing a convenient
	 * navigation option for administrators.
	 *
	 * @since    1.0.0
	 *
	 * @param    array $links  Array of existing plugin action links.
	 * @return   array         Modified array of action links, including the new shortcuts.
	 */
	function wc_pagarme_admin_links( $links ) {
		// Adds a link to the WooCommerce Pagar.me settings page.
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '">' . __( 'Configurações', 'wc-pagarme' ) . '</a>';

		// Adds a link to the Pagar.me support page.
		$links[] = '<a href="https://aquapress.com.br">' . __( 'Suporte', 'wc-pagarme' ) . '</a>';

		return $links;
	}
}
