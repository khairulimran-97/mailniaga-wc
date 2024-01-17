<?php
/**
 * MailNiaga for WooCommerce class.
 *
 * @package MAILNIAGA
 * @author MailNiaga
 */

/**
 * Main MailNiaga for WooCommerce class, which registers the WooCommerce integration
 * and initialises required classes depending on the environment (frontend site, admin etc).
 *
 * @package MAILNIAGA
 * @author MailNiaga
 */
class WP_MAILNIAGA {

	private static $instance;

	private $classes = array();

	public function __construct() {

		// Declare HPOS compatibility.
		add_action( 'before_woocommerce_init', array( $this, 'woocommerce_hpos_compatibility' ) );

	}

	/**
	 * Tells WooCommerce that this integration is compatible with HPOS.
	 *
	 * @see https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	 *
	 * @since   1.6.6
	 */
	public function woocommerce_hpos_compatibility() {

		// Don't declare compatibility if the applicable class doesn't exist.
		if ( ! class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			return;
		}

		// Declare compatibility with HPOS.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', MAILNIAGA_PLUGIN_FILE, true ); // @phpstan-ignore-line

	}


	/**
	 * Returns the given class
	 *
	 * @since   1.4.2
	 *
	 * @param   string $name   Class Name.
	 * @return  object          Class Object
	 */
	public function get_class( $name ) {

		// If the class hasn't been loaded, throw a WordPress die screen
		// to avoid a PHP fatal error.
		if ( ! isset( $this->classes[ $name ] ) ) {
			// Define the error.
			$error = new WP_Error(
				'mailniaga_for_woocommerce_get_class',
				sprintf(
				/* translators: %1$s: PHP class name */
					__( 'MailNiaga for WooCommerce Error: Could not load Plugin class <strong>%1$s</strong>', 'woocommerce-mailniaga' ),
					$name
				)
			);

			// Depending on the request, return or display an error.
			// Admin UI.
			if ( is_admin() ) {
				wp_die(
					esc_attr( $error->get_error_message() ),
					esc_html__( 'MailNiaga for WooCommerce Error', 'woocommerce-mailniaga' ),
					array(
						'back_link' => true,
					)
				);
			}

			// Cron / CLI.
			return $error;
		}

		// Return the class object.
		return $this->classes[ $name ];

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since   1.4.2
	 *
	 * @return  object Class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

}
