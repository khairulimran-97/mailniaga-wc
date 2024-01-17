<?php
/**
 * Plugin Name: MailNiaga for WooCommerce
 * Plugin URI: https://mailniaga.com
 * Description: Integrates WooCommerce with MailNiaga allowing customers to be automatically sent to your Mailing List in MailNiaga Account.
 * Author: Web Impian Sdn Bhd
 * Author URI: https://mailniaga.com
 * Version: 1.0
 * WC requires at least: 3.0
 * WC tested up to: 8.5.1
 */

// Define MailNiaga Plugin paths and version number.
define( 'MAILNIAGA_PLUGIN_NAME', 'MailNiagaWooCommerce' );
define( 'MAILNIAGA_PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( 'MAILNIAGA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MAILNIAGA_PLUGIN_PATH', __DIR__ );
define( 'MAILNIAGA_PLUGIN_VERSION', '1.0.0' );


require_once MAILNIAGA_PLUGIN_PATH . '/includes/functions.php';
require_once MAILNIAGA_PLUGIN_PATH . '/includes/mailniaga-v2-setting.php';
require_once MAILNIAGA_PLUGIN_PATH . '/includes/mailniaga-checkout.php';
require_once MAILNIAGA_PLUGIN_PATH . '/includes/mailniaga-v2-api.php';


function WP_MAILNIAGA() {
	return WP_MAILNIAGA::get_instance();
}


function WP_MAILNIAGA_Integration() {

	// Bail if WooCommerce isn't active.
	if ( ! function_exists( 'WC' ) ) {
		return false;
	}

	// Get registered WooCommerce integrations.
	$integrations = WC()->integrations->get_integrations();

	// Return our integration, if it's registered.
	return isset( $integrations['mailniaga'] ) ? $integrations['mailniaga'] : false;

}

// Finally, initialize the Plugin.
WP_MAILNIAGA();
WC_Settings_MailNiaga::init();



