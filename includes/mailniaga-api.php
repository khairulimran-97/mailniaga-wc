<?php

add_action('woocommerce_checkout_order_created', 'check_and_send_mailniaga', 10, 1);

function check_and_send_mailniaga($order_id) {
	if (!$order_id) {
		return;
	}

	$sendy_enabled = get_option('woocommerce_enable_sendy');
	$acelle_enabled = get_option('woocommerce_enable_acelle');

	if ($sendy_enabled === 'yes') {
		send_email($order_id);
	} else {
		error_log('MailNiaga v1 disabled');
	}

	if ($acelle_enabled === 'yes') {
		send_mailniaga_api_request($order_id);
	} else {
		error_log('MailNiaga v2 disabled');
	}
}

function send_email($order_id) {
	$order = wc_get_order($order_id);

	if (!$order) {
		return;
	}

	$checkbox_mailniaga = $order->get_meta('checkbox_mailniaga');

	if ($checkbox_mailniaga != 1) {
		return;
	}

	$api_token = get_option('wc_settings_mailniaga_api_sendy');

	if (empty($api_token)) {
		error_log('Token MailNiaga v1 empty');
		return;
	}

	$api_key = $api_token;
	$api_url = 'https://newsletter.aplikasiniaga.com/subscribe';
	$email = $order->get_billing_email();
	$name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
	$boolean = 'true';
	$list = get_option('wc_settings_mailniaga_list_sendy');
	$referrer = get_home_url();

	$api_response = wp_safe_remote_post($api_url, array(
		'body' => compact('api_key', 'list', 'boolean', 'email', 'name', 'referrer'),
	));

	handle_api_response($api_response, $order, 'MailNiaga v1');
}

function send_mailniaga_api_request($order_id) {
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
		error_log('Token MailNiaga v2 empty');
		return;
	}

	$api_url = 'https://manage.mailniaga.com/api/v1/subscribers';
	$EMAIL = $order->get_billing_email();
	$FIRST_NAME = $order->get_billing_first_name();
	$LAST_NAME = $order->get_billing_last_name();
	$list_uid = get_option('wc_settings_mailniaga_list');
	$tag = 'woocommerce';

	$api_response = wp_safe_remote_post($api_url, array(
		'body' => compact('api_token', 'list_uid', 'EMAIL', 'FIRST_NAME', 'LAST_NAME', 'tag'),
	));

	handle_api_response($api_response, $order, 'MailNiaga v2');
}

function handle_api_response($api_response, $order, $version) {
	if (is_wp_error($api_response)) {
		error_log($version . ' API Request Error: ' . $api_response->get_error_message());
		return;
	}

	$response_code = wp_remote_retrieve_response_code($api_response);
	$response_body = wp_remote_retrieve_body($api_response);

	error_log($version . ' API Response Code: ' . $response_code);
	error_log($version . ' API Response Body: ' . $response_body);

	if ($version === 'MailNiaga v2' && $response_code == 200 ) {
		$order->add_order_note('Email successfully added to the ' . $version . ' mailing list.');
	} elseif ($version === 'MailNiaga v1') {
		$order->add_order_note($response_body == 1 ? 'Email successfully added to the ' . $version . ' mailing list.' : $response_body);
	}
}
