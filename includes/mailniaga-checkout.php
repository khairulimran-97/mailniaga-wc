<?php

if (!defined('ABSPATH')) {
	exit;
}

use Automattic\WooCommerce\Utilities\OrderUtil;

add_action('woocommerce_review_order_before_submit', 'check_checkout_mailniaga', 10);
add_action('woocommerce_checkout_update_order_meta', 'mailniaga_checkout_field_order_meta_db');

function check_checkout_mailniaga() {
	$sendy_enabled = get_option('woocommerce_enable_sendy');
	$acelle_enabled = get_option('woocommerce_enable_acelle');

	if ($sendy_enabled === 'yes' || $acelle_enabled === 'yes')  {
		mailniaga_checkout_checkbox();
	} else {
		error_log('MailNiaga disabled');
	}
}

function mailniaga_checkout_checkbox() {
	$api_token = get_option('wc_settings_mailniaga_api');
	$api_key = get_option('wc_settings_mailniaga_api_sendy');

	if (!empty($api_token) || !empty($api_key)) {
		$opt_in_value = get_option('wc_settings_mailniaga_optin');
		$opt_in_label = get_option('wc_settings_mailniaga_label');

		woocommerce_form_field('checkbox_mailniaga', array(
			'type'        => 'checkbox',
			'class'       => array('form-row mycheckbox'),
			'label_class' => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
			'input_class' => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
			'label'       => empty($opt_in_label) ? "Subscribe to our newsletter" : $opt_in_label,
			'default'     => ($opt_in_value === 'yes') ? 1 : 0,
		));
	}
}

function mailniaga_checkout_field_order_meta_db($order_id) {
	$checkbox_mailniaga = !empty($_POST['checkbox_mailniaga']) ? sanitize_text_field($_POST['checkbox_mailniaga']) : '';

	if (!empty($checkbox_mailniaga)) {
		$order = wc_get_order($order_id);

		if (OrderUtil::custom_orders_table_usage_is_enabled()) {
			$order->update_meta_data('checkbox_mailniaga', $checkbox_mailniaga);
		} else {
			update_post_meta($order_id, 'checkbox_mailniaga', $checkbox_mailniaga);
		}

		$order->save();
	}
}
