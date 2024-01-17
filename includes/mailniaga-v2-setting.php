<?php

class WC_Settings_MailNiaga {

	public static function init() {
		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_settings_mailniaga', __CLASS__ . '::settings_tab' );
		add_action( 'woocommerce_update_options_settings_mailniaga', __CLASS__ . '::update_settings' );
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

		$api_token = get_option('wc_settings_mailniaga_api');
		$lists = self::get_mailing_lists($api_token);

		$options = array();
		if (empty($lists)) {
			$options['empty'] = __('Please enter a valid API key above to retrieve your mailing lists.', 'wc-mailniaga');
		} else {
			foreach ($lists as $list) {
				$options[$list['uid']] = $list['name'];
			}
		}

		$settings = array(
			'section_title' => array(
				'name'     => __( 'Mailniaga Settings', 'wc-mailniaga' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_mailniaga_section_title'
			),
			'api_token' => array(
				'name' => __( 'API Token', 'wc-mailniaga' ),
				'type' => 'text',
				'desc' => __( 'Enter your Mailniaga API Key.', 'wc-mailniaga' ),
				'id'   => 'wc_settings_mailniaga_api'
			),
			'opt_in' => array(
				'name' => __( 'Opt-in Default', 'wc-mailniaga' ),
				'type' => 'checkbox',
				'desc' => __( 'Would you like the newsletter opt-in checkbox checked by default?' ),
				'id'   => 'wc_settings_mailniaga_optin',

			),
			'list_id' => array(
				'name' => __( 'Mailing List', 'wc-mailniaga' ),
				'type' => 'select',
				'desc' => __( 'Select mailing list to add customer email' ),
				'options' => $options,
				'id'   => 'wc_settings_mailniaga_list',

			),
			'optin_label' => array(
				'name' => __( 'Opt in Label', 'wc-mailniaga' ),
				'type' => 'text',
				'desc' => __( 'This is the text shown by default next to the Mailniaga sign up checkbox.', 'wc-mailniaga' ),
				'placeholder' => 'Subscribe to our newsletter',
				'id'   => 'wc_settings_mailniaga_label'

			),
			'section_end' => array(
				'type' => 'sectionend',
				'id' => 'wc_settings_mailniaga_section_end'
			)
		);

		return apply_filters( 'wc_settings_mailniaga_settings', $settings );
	}

	public static function get_mailing_lists($api_token) {
		$url = 'https://manage.mailniaga.com/api/v1/lists?api_token=' . $api_token;
		$response = wp_remote_get($url);

		if (is_wp_error($response)) {
			return array();
		}

		$body = wp_remote_retrieve_body($response);
		$lists = json_decode($body, true);

		return is_array($lists) ? $lists : array();
	}

}
