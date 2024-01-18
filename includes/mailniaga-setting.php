<?php
// Add this line at the beginning of the file for security.
defined( 'ABSPATH' ) || exit;

class WC_Settings_MailNiaga {

	public static function init() {
		add_filter( 'woocommerce_settings_tabs_array', array( __CLASS__, 'add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_tabs_settings_mailniaga', array( __CLASS__, 'settings_tab' ) );
		add_action( 'woocommerce_update_options_settings_mailniaga', array( __CLASS__, 'update_settings' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	public static function enqueue_scripts() {
		// Enqueue the script from the plugin folder
		wp_enqueue_script( 'mailniaga-script', MAILNIAGA_PLUGIN_URL . 'assets/mailniaga.js', array( 'jquery' ), null, true );
	}

	public static function add_settings_tab( $settings_tabs ) {
		$settings_tabs['settings_mailniaga'] = __( 'MailNiaga', 'wc-mailniaga' );
		return $settings_tabs;
	}

	public static function settings_tab() {
		woocommerce_admin_fields( self::get_settings() );
	}

	public static function update_settings() {
		woocommerce_update_options( self::get_settings() );
	}

	public static function get_settings() {
		// Version 1 (v1) settings
		$api_key = get_option( 'wc_settings_mailniaga_api_sendy' );
		$brand_id = get_option( 'wc_settings_mailniaga_brand_sendy' );
		$lists = self::get_mailing_lists_v1( $api_key, $brand_id );

		$options_sendy = array();

		if ( empty( $lists ) ) {
			$options_sendy['empty'] = __( 'Please enter a valid API key and Brand ID above to retrieve your mailing lists.', 'wc-mailniaga' );
		} else {
			foreach ( $lists as $list ) {
				$options_sendy[ $list['id'] ] = $list['name'];
			}
		}


		// Version 2 settings
		$api_token = get_option( 'wc_settings_mailniaga_api' );
		$lists = self::get_mailing_lists( $api_token );

		$options = array();

		if ( empty( $lists ) ) {
			$options['empty'] = __( 'Please enter a valid API key above to retrieve your mailing lists.', 'wc-mailniaga' );
		} else {
			foreach ( $lists as $list ) {
				$options[ $list['uid'] ] = $list['name'];
			}
		}

		$settings = array(
			'section_title_sendy' => array(
				'name' => __( 'Mailniaga Settings for v1', 'wc-mailniaga' ),
				'type' => 'title',
				'desc' => 'Fill this form if you are using MailNiaga v1 . Login from <a href="https://newsletter.aplikasiniaga.com/" target="_blank">https://newsletter.aplikasiniaga.com/</a>',
				'id'   => 'wc_settings_mailniaga_section_title_sendy',
			),

			'enabled'            => array(
				'title'   => __( 'Enable/Disable', 'wc-mailniaga' ),
				'type'    => 'checkbox',
				'desc'   => __( 'Enable MailNiaga v1 integration', 'wc-mailniaga' ),
				'default' => 'no',
				'id'      => 'woocommerce_enable_sendy',
			),

			'api_key_sendy'     => array(
				'name' => __( 'API Key', 'wc-mailniaga' ),
				'type' => 'text',
				'desc' => __( 'Enter your Mailniaga API Key. Provide by admin MailNiaga', 'wc-mailniaga' ),
				'id'   => 'wc_settings_mailniaga_api_sendy',
				'class' => 'disable_mailniaga_sendy',
			),
			'brand_sendy'     => array(
				'name' => __( 'Brand ID', 'wc-mailniaga' ),
				'type' => 'number',
				'desc' => __( 'Enter your Mailniaga Brand ID. Brand ID can be found on your url app?i=*. If not sure can ask support team', 'wc-mailniaga' ),
				'id'   => 'wc_settings_mailniaga_brand_sendy',
				'class' => 'disable_mailniaga_sendy',
			),
			'list_sendy'       => array(
				'name'    => __( 'Subscriber lists', 'wc-mailniaga' ),
				'type'    => 'select',
				'desc'    => __( 'Select mailing list to add customer email' ),
				'options' => $options_sendy,
				'class'    => 'wc-enhanced-select, disable_mailniaga_sendy',
				'css'      => 'min-width:300px;',
				'id'      => 'wc_settings_mailniaga_list_sendy',

			),

			'section_end_sendy'   => array(
				'type' => 'sectionend',
				'id'   => 'wc_settings_mailniaga_section_end_sendy',
			),


			'section_title' => array(
				'name' => __( 'Mailniaga Settings for v2', 'wc-mailniaga' ),
				'type' => 'title',
				'desc' => 'Fill this form if you are using MailNiaga v2 . Login from <a href="https://manage.mailniaga.com/" target="_blank">https://manage.mailniaga.com/</a>',
				'id'   => 'wc_settings_mailniaga_section_title',
			),
			'enabled_acelle'            => array(
				'title'   => __( 'Enable/Disable', 'wc-mailniaga' ),
				'type'    => 'checkbox',
				'desc'   => __( 'Enable MailNiaga v2 integration', 'wc-mailniaga' ),
				'default' => 'no',
				'id'      => 'woocommerce_enable_acelle',
			),
			'api_token'     => array(
				'name' => __( 'API Token', 'wc-mailniaga' ),
				'type' => 'text',
				'desc' => __( 'Enter your Mailniaga API Key.', 'wc-mailniaga' ),
				'id'   => 'wc_settings_mailniaga_api',
				'class' => 'disable_mailniaga_acelle',
			),
			'list_id'       => array(
				'name'    => __( 'Mailing List', 'wc-mailniaga' ),
				'type'    => 'select',
				'desc'    => __( 'Select mailing list to add customer email' ),
				'options' => $options,
				'class'    => 'wc-enhanced-select, disable_mailniaga_acelle',
				'css'      => 'min-width:300px;',
				'id'      => 'wc_settings_mailniaga_list',
			),
			'section_end'   => array(
				'type' => 'sectionend',
				'id'   => 'wc_settings_mailniaga_section_end',
			),

			'section_checkout' => array(
				'name' => __( 'Checkbox Checkout Setting', 'wc-mailniaga' ),
				'type' => 'title',
				'desc' => 'Setting checkbox behaviour in checkout',
				'id'   => 'wc_settings_mailniaga_section_title',
			),
			'opt_in'        => array(
				'name' => __( 'Opt-in Default', 'wc-mailniaga' ),
				'type' => 'checkbox',
				'desc' => __( 'Would you like the newsletter opt-in checkbox checked by default?' ),
				'id'   => 'wc_settings_mailniaga_optin',
			),
			'optin_label'   => array(
				'name'        => __( 'Opt in Label', 'wc-mailniaga' ),
				'type'        => 'text',
				'desc'        => __( 'This is the text shown by default next to the Mailniaga sign up checkbox.', 'wc-mailniaga' ),
				'placeholder' => 'Subscribe to our newsletter',
				'id'          => 'wc_settings_mailniaga_label',
			),
			'section_end_checkbox'   => array(
				'type' => 'sectionend',
				'id'   => 'wc_settings_mailniaga_section_end_checkbox',
			),

		);

		return apply_filters( 'wc_settings_mailniaga_settings', $settings );
	}

	public static function get_mailing_lists( $api_token ) {
		$url = add_query_arg( 'api_token', $api_token, 'https://manage.mailniaga.com/api/v1/lists' );
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			// Log or handle the error appropriately.
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$lists = json_decode( $body, true );

		return is_array( $lists ) ? $lists : array();
	}

	public static function get_mailing_lists_v1( $api_key, $brand_id ) {
		// Define the API endpoint URL
		$url = 'https://newsletter.aplikasiniaga.com/api/lists/get-lists.php';

		// Prepare the request parameters
		$params = array(
			'api_key'  => $api_key,
			'brand_id' => $brand_id,
		);

		// Make the request
		$response = wp_remote_post( $url, array(
			'body' => $params,
		));

		// Check if the request was successful
		if ( is_wp_error( $response ) ) {
			// Log or handle the error appropriately.
			return array();
		}

		// Retrieve and decode the response body
		$body = wp_remote_retrieve_body( $response );
		$lists = json_decode( $body, true );

		return is_array( $lists ) ? $lists : array();
	}


}

WC_Settings_MailNiaga::init();