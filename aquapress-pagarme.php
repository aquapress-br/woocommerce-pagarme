<?php

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Name: Pagar.me v5 para WooCommerce
 * Description: Gateway de pagamento Pagar.me para WooCommerce.
 * Version: 1.0.0-alpha35
 * Text Domain: wc-pagarme
 * Domain Path: /i18n/
 * Author: Aquapress
 * Author URI: https://aquapress.com.br/
 */

define( 'WC_PAGARME_NAME', 'Pagar.me v5 para WooCommerce' );
define( 'WC_PAGARME_VERSION', '1.0.0-alpha35' );
define( 'WC_PAGARME_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WC_PAGARME_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WC_PAGARME_BASENAME', plugin_basename( __FILE__ ) );

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/setup/utils.php';
require_once __DIR__ . '/setup/plugin.php';
require_once __DIR__ . '/setup/admin.php';
require_once __DIR__ . '/setup/template.php';
require_once __DIR__ . '/setup/gateway.php';
require_once __DIR__ . '/setup/hooks.php';
