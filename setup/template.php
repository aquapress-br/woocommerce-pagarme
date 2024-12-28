<?php defined( 'ABSPATH' ) || exit;

/**
 * Get template part
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name Optional. The name of the specialised template. Default is empty string.
 * @return void
 */
function wc_pagarme_get_template_part( $slug, $name = '' ) {
	$template = '';

	if ( $name ) {
		$template = wc_pagarme_locate_template( "{$slug}-{$name}.php", apply_filters( 'wc_pagarme_template_path', wc_pagarme_get_template_path() ) . "{$slug}-{$name}.php" );
	}

	// Get default slug-name.php.
	if ( ! $template && $name && file_exists( WC_PAGARME_PATH . "/templates/{$slug}-{$name}.php" ) ) {
		$template = WC_PAGARME_PATH . "/templates/{$slug}-{$name}.php";
	}

	if ( ! $template ) {
		$template = wc_pagarme_locate_template( "{$slug}.php", apply_filters( 'wc_pagarme_template_path', wc_pagarme_get_template_path() ) . "{$slug}.php" );
	}

	/**
	 * Filters the template file path
	 *
	 * Allow 3rd party plugin filter template file from their plugin.
	 *
	 * @param string $template The path to the template file.
	 * @param string $slug     The slug name for the generic template.
	 * @param stirng $name     The name of the specialised template.
	 */
	$template = apply_filters( 'wc_pagarme_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get Template Part
 *
 * @param string $template_name Name of template.
 * @param array  $args          Array of arguments accessible from the template.
 * @param string $template_path Optional. Dir path to template. Default is empty string.
 *                              If not supplied the one retrived from `humanbot()->template_path()` will be used.
 * @param string $default_path  Optional. Default path is empty string.
 *                              If not supplied the template path is `WC_PAGARME_PATH . '/templates/'`.
 * @return void
 */
function wc_pagarme_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	$located = wc_pagarme_locate_template( $template_name, $template_path, $default_path );

	/**
	 * Fired before a template part is included
	 *
	 * @param string $template_name Name of template.
	 * @param string $template_path Dir path to template as passed to the `get_template()` function.
	 * @param string $located       The full path of the template file to load.
	 * @param array  $args          Array of arguments accessible from the template.
	 */
	do_action( 'wc_pagarme_before_template_part', $template_name, $template_path, $located, $args );

	if ( file_exists( $located ) ) {
		include $located;
	}

	/**
	 * Fired after a template part is included
	 *
	 * @param string $template_name Name of template.
	 * @param string $template_path Dir path to template as passed to the `get_template()` function.
	 * @param string $located       The full path of the (maybe) loaded template file.
	 * @param array  $args          Array of arguments accessible from the template.
	 */
	do_action( 'wc_pagarme_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate Template
 *
 * @param string $template_name Name of template.
 * @param string $template_path Optional. Dir path to template. Default is empty string.
 *                              If not supplied the one retrived from `humanbot()->template_path()` will be used.
 * @param string $default_path  Optional. Default path is empty string.
 *                              If not supplied the template path is `WC_PAGARME_PATH . '/templates/'`.
 * @return string
 */
function wc_pagarme_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = apply_filters( 'wc_pagarme_template_path', wc_pagarme_get_template_path() );
	}

	if ( ! $default_path ) {
		$default_path = WC_PAGARME_PATH . '/templates/';
	}

	// Check theme and template directories for the template.
	$override_path = wc_pagarme_get_template_override( $template_name );

	// Get default template.
	$path = ( $override_path ) ? $override_path : $default_path;

	$template = $path . $template_name;

	if ( ! file_exists( $template ) ) {

		$template = '';

	}

	/**
	 * Filters the maybe located template file path
	 *
	 * Allow 3rd party plugin filter template file from their plugin.
	 *
	 * @param string $template      The path to the template file. Empty string if no template found.
	 * @param string $template_name Name of template.
	 * @param string $template_path Dir path to template.
	 */
	return apply_filters( 'wc_pagarme_locate_template', $template, $template_name, $template_path );
}

/**
 * Get template override.
 *
 * @param string $template Template file.
 * @return mixed Template file directory or false if none exists.
 */
function wc_pagarme_get_template_override( $template = '' ) {

	$dirs = wc_pagarme_get_template_override_directories();

	foreach ( $dirs as $dir ) {

		$path = $dir . '/';
		if ( file_exists( "{$path}{$template}" ) ) {
			return $path;
		}
	}

	return false;
}

/**
 * Get template override directories.
 *
 * Moved from `get_template_override()`.
 *
 * @return string[]
 */
function wc_pagarme_get_template_override_directories() {

	$dirs = wp_cache_get( 'theme-override-directories', 'wc_pagarme_template_functions' );
	if ( false === $dirs ) {
		$dirs = array_filter(
			array_unique(
				array(
					get_stylesheet_directory() . '/wc-pagarme-v5-beta',
					get_template_directory() . '/wc-pagarme-v5-beta',
				)
			),
			'is_dir'
		);
		wp_cache_set( 'theme-override-directories', $dirs, 'wc_pagarme_template_functions' );
	}

	/**
	 * Filters the theme override directories.
	 *
	 * Allow themes and plugins to determine which folders to look in for theme overrides.
	 *
	 * @param string[] $theme_override_directories List of theme override directory paths.
	 */
	return apply_filters( 'humanbot_playground_theme_override_directories', $dirs );
}

/**
 * Retrieves the template directory path for the WooCommerce Pagar.me plugin.
 *
 * This function uses the 'wc_pagarme_template_path' filter to allow the
 * template directory path to be modified by other parts of the code or by
 * additional plugins.
 *
 * @return string Path to the plugin's template directory.
 *
 * @hook wc_pagarme_template_path Allows modifying the template directory
 *                                    path returned by the function.
 */
function wc_pagarme_get_template_path() {
	/**
	 * Filter the template directory path for WooCommerce Pagar.me plugin.
	 *
	 * @param string $template_path Default template directory path.
	 */
	return apply_filters( 'wc_pagarme_template_path', 'wc-pagarme-v5-beta/' );
}
