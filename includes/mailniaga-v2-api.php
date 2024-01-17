<?php

if (!defined('ABSPATH')) {
	exit;
}

add_action('woocommerce_checkout_order_created', 'send_mailniaga_api_request', 10, 1);

function send_mailniaga_api_request($order_id) {
	if (!$order_id) {
		return;
	}

	$order = wc_get_order($order_id);

	if (!$order) {
		return;
	}

	$checkbox_mailniaga = $order->get_meta('checkbox_mailniaga');

	if ($checkbox_mailniaga != 1) {
		return;
	}

	$api_token = get_option('wc_settings_mailniaga_api');

	if (empty($api_token)) {
		return;
	}

	$api_url = 'https://manage.mailniaga.com/api/v1/subscribers';

	$api_response = wp_safe_remote_post($api_url, array(
		'body' => array(
			'api_token' => $api_token,
			'list_uid' => get_option('wc_settings_mailniaga_list'),
			'EMAIL' => $order->get_billing_email(),
			'FIRST_NAME' => $order->get_billing_first_name(),
			'LAST_NAME' => $order->get_billing_last_name(),
			'tag' => 'woocommerce',
		),
	));

	if (is_wp_error($api_response)) {
		error_log('Mailniaga API Request Error: ' . $api_response->get_error_message());
		return;
	}

	$response_code = wp_remote_retrieve_response_code($api_response);
	$response_body = wp_remote_retrieve_body($api_response);

	error_log('Mailniaga API Response Code: ' . $response_code);
	error_log('Mailniaga API Response Body: ' . $response_body);

	if ($response_code == 200) {
		$order->add_order_note('Email successfully added to the MailNiaga mailing list.');
	}
}
