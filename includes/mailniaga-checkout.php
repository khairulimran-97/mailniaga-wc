<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Hook to add checkbox to the checkout page
add_action('woocommerce_review_order_before_submit', 'mailniaga_checkout_checkbox', 10);

// Hook to save checkbox value to order meta
add_action('woocommerce_checkout_update_order_meta', 'mailniaga_checkout_field_order_meta_db');

/**
 * Display Mailniaga checkbox on the checkout page.
 */
function mailniaga_checkout_checkbox() {
	// Get the API token
	$api_token = get_option('wc_settings_mailniaga_api');

	// Check if API token is not empty before displaying the checkbox
	if (!empty($api_token)) {
		$opt_in_value = get_option('wc_settings_mailniaga_optin');
		$opt_in_label = get_option('wc_settings_mailniaga_label');

		// Set default label text if it's empty
		$opt_in_label = empty($opt_in_label) ? "Subscribe to our newsletter" : $opt_in_label;

		woocommerce_form_field('checkbox_mailniaga', array(
			'type'        => 'checkbox',
			'class'       => array('form-row mycheckbox'),
			'label_class' => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
			'input_class' => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
			'label'       => $opt_in_label,
			'default'     => ($opt_in_value === 'yes') ? 1 : 0,
		));
	}
}

/**
 * Save Mailniaga checkbox value to order meta.
 *
 * @param int $order_id The order ID.
 */
function mailniaga_checkout_field_order_meta_db($order_id) {
	$checkbox_mailniaga = isset($_POST['checkbox_mailniaga']) ? sanitize_text_field($_POST['checkbox_mailniaga']) : '';

	if (!empty($checkbox_mailniaga)) {
		$order = wc_get_order($order_id);
		$order->update_meta_data('checkbox_mailniaga', $checkbox_mailniaga);
		$order->save();
	}
}
