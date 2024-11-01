<?php

/**
 * WC_Gateway_Pagadito class
 * 
 * @author   Pagadito Developers
 * @package  WooCommerce Pagadito Payment Gateway
 * @since    6.0.0
 */

require_once 'pagadito-1.5.2.php';

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pagadito Gateway.
 *
 * @class    WC_Gateway_Pagadito
 * @version  1.0.0
 */
class WC_Gateway_Pagadito extends WC_Payment_Gateway
{

    public $id;
    public $has_fields;
    public $method_title;
    public $method_description;
    public $title;
    public $theme;
    public $description;
    public $uid;
    public $wsk;
    public $sandbox_mode;
    public $local_currency;
    public $debug;
    public $custom_param;
    public $icon;
    public $log;

    public function __construct()
    {
        $icon_path = '/assets/images/';

        $this->id                 = 'pagadito';
        $this->has_fields         = false;
        $this->method_title       = 'Pagadito';
        $this->method_description = __('Pagadito redirects customers to the Pagadito website, where they must enter their payment information, after which the client is returned to their website to complete the order.', 'woo-pagadito-payment-gateway');
        $this->supports           = array(
            'products',
        );

        $this->init_form_fields();
        $this->init_settings();

        /* Loading configurations */
        $this->title              = $this->get_option('title');
        $this->theme              = $this->get_option('theme');
        $this->description        = $this->get_option('description');
        $this->uid                = $this->get_option('uid');
        $this->wsk                = $this->get_option('wsk');
        $this->sandbox_mode       = $this->get_option('sandbox_mode');
        $this->local_currency     = $this->get_option('local_currency_select');
        $this->debug              = $this->get_option('debug');
        $this->custom_param       = $this->get_option('custom_param');
        $this->icon               = apply_filters('woocommerce_pagadito_icon', WC_Pagadito_Payments::plugin_url() . $icon_path . $this->theme . '.png');

        // hook for administrative options
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        // hook for pagadito's response
        add_action('woocommerce_api_wc_gateway_pagadito', array($this, 'check_pagadito_response'));
        // web hook for pagadito response
        add_action('woocommerce_api_wc_webhook_pagadito', array($this, 'pagadito_webhook'));

        // Deactivate if any of the official pagadito currencies is not available
        if (!$this->is_valid_for_use()) $this->enabled = false;
    }

    /**
     * Register in Log
     * Check first if debug is active to allow saving to file
     * @param string $message Information to be logged
     */
    public function save_to_log($order, $message)
    {
        if ($this->debug) {
            if (empty($this->log)) {
                $this->log = new WC_Logger();
            }
            $this->log->add('pagadito', 'Order: ' . $order . ' - ' . $message);
        }
    }

    public function init_form_fields()
    {
        $log_handler = new WC_Log_Handler_File();
        $log_file_path = $log_handler->get_log_file_path('pagadito');
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __('Enable/Disable', 'woo-pagadito-payment-gateway'),
                'type'        => 'checkbox',
                'label'       => __('Enable payments with Pagadito', 'woo-pagadito-payment-gateway'),
                'default'     => 'yes'
            ),
            'title' => array(
                'title'       => __('Title', 'woo-pagadito-payment-gateway'),
                'type'        => 'text',
                'description' => __('Control the title that the user sees in the checkout, if it is empty, only the Pagadito logo will appear.', 'woo-pagadito-payment-gateway'),
                'default'     => 'Pagadito',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'woo-pagadito-payment-gateway'),
                'type'        => 'text',
                'description' => __('Control the description which the user sees in the checkout.', 'woo-pagadito-payment-gateway'),
                'default'     => __('Pagadito allows you to pay online in a safe, easy and reliable way.', 'woo-pagadito-payment-gateway'),
                'desc_tip'    => true,
            ),
            'theme' => array(
                'title'       => __('Pagadito Theme', 'woo-pagadito-payment-gateway'),
                'type'        => 'select',
                'options'     => array(
                    'default' => __('Default', 'woo-pagadito-payment-gateway'),
                    'light'   => __('Light', 'woo-pagadito-payment-gateway'),
                    'dark'    => __('Dark', 'woo-pagadito-payment-gateway'),
                ),
                'description' => __('Control the color of the logos that will appear on the payment screen.', 'woo-pagadito-payment-gateway'),
                'default'     => 'default',
            ),
            'options_details' => array(
                'title'       => __('Advanced Options', 'woo-pagadito-payment-gateway'),
                'type'        => 'title',
            ),
            'sandbox_mode' => array(
                'title'       => 'SandBox Pagadito',
                'type'        => 'checkbox',
                'label'       => __('Enable Pagadito SandBox', 'woo-pagadito-payment-gateway'),
                'description' => sprintf(__('Pagadito SandBox can be used to test payments. Sign up for a %sPagadito account commerce in SandBox%s. Once the tests are completed, be sure to disable this option, to process payments in Production environment.', 'woo-pagadito-payment-gateway'), '<a href="https://sandbox.pagadito.com/index.php?mod=user&hac=vregfC" target="_blank">', '</a>'),
                'default'     => 'yes',
            ),
            'local_currency_select' => array(
                'title'       => __('Select Local Currency', 'woo-pagadito-payment-gateway'),
                'type'        => 'select',
                'options'     => array(
                    'USD' => __('USD', 'woo-pagadito-payment-gateway'),
                    'GTQ' => __('GTQ', 'woo-pagadito-payment-gateway'),
                ),
                'description' => __('Select the local currency to use. <strong>This configuration is only for production.</strong>', 'woo-pagadito-payment-gateway'),
                'desc_tip'    => __('This configuration is only for production.', 'woo-pagadito-payment-gateway'),
                'default'     => 'USD',
            ),
            'debug' => array(
                'title'       => __('Debug log', 'woo-pagadito-payment-gateway'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'woo-pagadito-payment-gateway'),
                'description' => sprintf(__('Log API events, inside <code>%s</code>', 'woo-pagadito-payment-gateway'), $log_file_path),
                'default'     => 'yes',
            ),
            'api_details' => array(
                'title'       => __('Integration parameters', 'woo-pagadito-payment-gateway'),
                'type'        => 'title',
                'description' => sprintf(__('Learn how to access your %sConnection credentials% s.', 'woo-pagadito-payment-gateway'), '<a href="https://dev.pagadito.com/index.php?mod=docs&hac=conf" target="_blank">', '</a>'),
            ),
            'uid' => array(
                'title'       => 'UID',
                'type'        => 'text',
                'description' => __('Represents the identifier of your Pagadito commerce account.', 'woo-pagadito-payment-gateway'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'wsk' => array(
                'title'       => 'WSK',
                'type'        => 'text',
                'description' => __('Represents the connection key to connect to Pagadito.', 'woo-pagadito-payment-gateway'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'url_return' => array(
                'title'       => __('Return URL', 'woo-pagadito-payment-gateway'),
                'type'        => 'title',
                'description' => sprintf(__('Enter this address <code>%s</code> in the Return URL field in your Pagadito account -> Technical configuration -> Integration parameters.', 'woo-pagadito-payment-gateway'), home_url() . '/?wc-api=WC_Gateway_Pagadito&token={value}&order_id={ern_value}'),
            ),
            'url_webhook' => array(
                'title'       => __('Webhooks', 'woo-pagadito-payment-gateway'),
                'type'       => 'title',
                'description' => sprintf(__('For a full integration with Pagadito, be sure to enable the notifications send to Webhooks and enter this address <code>%s</code> in the Webhook URL field in your Pagadito account -> Technical configuration -> Webhooks.', 'woo-pagadito-payment-gateway'), home_url() . '/?wc-api=WC_Webhook_Pagadito'),
            ),
            'custom_param' => array(
                'title'       => __('Custom parameters', 'woo-pagadito-payment-gateway'),
                'type'        => 'checkbox',
                'label'       => __('Enable custom parameters', 'woo-pagadito-payment-gateway'),
                'description' => sprintf(__('Register the customer data in Pagadito, which includes: Full name, Telephone and Email. For more information %svisit this link.%s', 'woo-pagadito-payment-gateway'), '<a href="https://dev.pagadito.com/index.php?mod=docs&hac=conf#parametros_personalizados" target="_blank">', '</a>'),
                'default'     => 'no',
            ),
        );
    }

    private function is_valid_for_use()
    {
        if (!in_array(get_woocommerce_currency(), apply_filters('woocommerce_pagadito_supported_currencies', array('USD', 'GTQ', 'HNL', 'NIO', 'CRC', 'PAB', 'DOP')))) return false;

        return true;
    }

    /**
     * Process Payment
     * @param integer $order_id The order that will be processed by Pagadito
     * @return array Returns the url to which it should direct, in case of error it shows it on the screen
     */
    public function process_payment($order_id)
    {
        global $woocommerce;
        // $order = new WC_Order($order_id);
        $order = wc_get_order($order_id);

        $tax_included = get_option('woocommerce_prices_include_tax' == 'yes') ? true : false;

        try {
            $this->save_to_log($order->get_order_number(), '');
            $this->save_to_log($order->get_order_number(), 'Processing Payment');

            //Initializing pagadito api
            $pg = new Pagadito($this->uid, $this->wsk, $this->local_currency);

            if ($this->sandbox_mode == 'yes') {
                $pg->mode_sandbox_on();
                $pg->change_currency_usd();
                $this->save_to_log($order->get_order_number(), 'Transaction in SandBox mode activated');
            }

            if (!$pg->connect())
                throw new Exception('Connect (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());

            $pg->enable_pending_payments();
            $token_pagadito = $pg->get_rs_value();
            $this->save_to_log($order->get_order_number(), 'Connection Token = ' . $pg->get_rs_value());

            $currency = strtoupper(get_woocommerce_currency());
            $this->save_to_log($order->get_order_number(), 'The order is processed in currency = ' . $currency);

            //Changing currency type
            switch ($currency) {
                case 'USD':
                    $pg->change_currency_usd();
                    break;
                case 'GTQ':
                    $pg->change_currency_gtq();
                    break;
                case 'HNL':
                    $pg->change_currency_hnl();
                    break;
                case 'NIO':
                    $pg->change_currency_nio();
                    break;
                case 'CRC':
                    $pg->change_currency_crc();
                    break;
                case 'PAB':
                    $pg->change_currency_pab();
                    break;
                case 'DOP':
                    $pg->change_currency_dop();
                    break;
            }

            if ($order->get_item_count() <= 0)
                throw new Exception(__('There are no items in the order', 'woo-pagadito-payment-gateway'));

            // Adding items
            foreach ($order->get_items() as $item) {
                if ($tax_included)
                    $pg->add_detail($item['qty'], $item['name'], $order->get_item_subtotal($item, true, false));
                else
                    $pg->add_detail($item['qty'], $item['name'], $order->get_item_subtotal($item, false, true));
            }

            // Applying fees
            $fees = $order->get_fees();
            if (is_array($fees) && count($fees) > 0) {
                foreach ($fees as $fee) {
                    $pg->add_detail(1, $fee['name'], $fee['line_total']);
                }
            }

            // Applying shipping costs
            $shipping = $order->get_shipping_total();
            $shipping_tax = $order->get_shipping_tax();

            if ((abs($shipping) - 0) >= 0.01) {
                $pg->add_detail(1, __('Shipping: ', 'woo-pagadito-payment-gateway') . ucwords($order->get_shipping_method()), $shipping);
            }

            // Applying taxes
            $taxes = $order->get_total_tax();
            if ((abs($taxes) - 0) >= 0.01) {
                $pg->add_detail(1, __('Total Tax: ', 'woo-pagadito-payment-gateway'), $taxes);
            }

            // Applying discount
            if (!empty($woocommerce->cart->applied_coupons)) {
                $discount = $order->get_discount_total();

                if ((abs($discount) - 0) >= 0.01) {
                    $discount = abs($discount) * (-1);
                    $pg->add_detail(1, __('Discount coupon (', 'woo-pagadito-payment-gateway') . implode('+', $order->get_coupon_codes()) . ')', $discount);
                }
            }

            // Adding custom parameters
            if ($this->custom_param == 'yes') {
                $pg->set_custom_param('param1', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
                $pg->set_custom_param('param2', $order->get_billing_phone());
                $pg->set_custom_param('param3', $order->get_billing_email());

                $this->save_to_log($order->get_order_number(), 'Sending custom parameters');
            }

            // Validating that the transaction is greater than a dollar including its different conversions
            switch ($currency) {
                case "USD":
                    if ($order->get_total() >= 1.00) {

                        // Continue with Pagadito
                        $this->save_to_log($order->get_order_number(), 'Continuing with Pagadito...');
                        $redirect_url = $pg->exec_trans($order->get_id());

                        if ($redirect_url === false) {
                            throw new Exception('Exec trans (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());
                        }

                        // Successful process
                        $result = array(
                            'result' => 'success',
                            'redirect' => $redirect_url
                        );

                        // Associating paid transaction with the order
                        add_post_meta($order->get_id(), '_token_pagadito', $token_pagadito);

                        return $result;
                    } else {
                        $this->save_to_log($order->get_order_number(), 'The transaction is less than 1 dollar =' . $order->get_total() . $currency);
                        wc_add_notice(__('<strong>Note:</strong> Transactions under 1 dollar can´t be made', 'woo-pagadito-payment-gateway'), 'notice');
                    }
                    break;
                case "GTQ":
                    if ($order->get_total() >= $pg->get_exchange_rate_gtq()) {

                        // Continue with Pagadito
                        $this->save_to_log($order->get_order_number(), 'Continuing with Pagadito...');
                        $redirect_url = $pg->exec_trans($order->get_id());

                        if ($redirect_url === false) {
                            throw new Exception('Exec trans (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());
                        }

                        // Successful process
                        $result = array(
                            'result' => 'success',
                            'redirect' => $redirect_url
                        );

                        // Associating paid transaction with the order
                        add_post_meta($order->get_id(), '_token_pagadito', $token_pagadito);

                        return $result;
                    } else {
                        $this->save_to_log($order->get_order_number(), 'The transaction is less than 1 dollar =' . $order->get_total() . $currency);
                        wc_add_notice(__('<strong>Note:</strong> Transactions less than the equivalent of 1 dollar can´t be processed', 'woo-pagadito-payment-gateway'), 'notice');
                    }
                    break;
                case "HNL":
                    if ($order->get_total() >= $pg->get_exchange_rate_hnl()) {

                        // Continue with Pagadito
                        $this->save_to_log($order->get_order_number(), 'Continuing with Pagadito...');
                        $redirect_url = $pg->exec_trans($order->get_id());

                        if ($redirect_url === false) {
                            throw new Exception('Exec trans (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());
                        }

                        // Successful process
                        $result = array(
                            'result' => 'success',
                            'redirect' => $redirect_url
                        );

                        // Associating paid transaction with the order
                        add_post_meta($order->get_id(), '_token_pagadito', $token_pagadito);

                        return $result;
                    } else {
                        $this->save_to_log($order->get_order_number(), 'The transaction is less than 1 dollar =' . $order->get_total() . $currency);
                        wc_add_notice(__('<strong>Note:</strong> Transactions less than the equivalent of 1 dollar can´t be processed', 'woo-pagadito-payment-gateway'), 'notice');
                    }
                    break;
                case "NIO":
                    if ($order->get_total() >= $pg->get_exchange_rate_nio()) {

                        // Continue with Pagadito
                        $this->save_to_log($order->get_order_number(), 'Continuing with Pagadito...');
                        $redirect_url = $pg->exec_trans($order->get_id());

                        if ($redirect_url === false) {
                            throw new Exception('Exec trans (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());
                        }

                        // Successful process
                        $result = array(
                            'result' => 'success',
                            'redirect' => $redirect_url
                        );

                        // Associating paid transaction with the order
                        add_post_meta($order->get_id(), '_token_pagadito', $token_pagadito);

                        return $result;
                    } else {
                        $this->save_to_log($order->get_order_number(), 'The transaction is less than 1 dollar =' . $order->get_total() . $currency);
                        wc_add_notice(__('<strong>Note:</strong> Transactions less than the equivalent of 1 dollar can´t be processed', 'woo-pagadito-payment-gateway'), 'notice');
                    }
                    break;
                case "CRC":
                    if ($order->get_total() >= $pg->get_exchange_rate_crc()) {

                        // Continue with Pagadito
                        $this->save_to_log($order->get_order_number(), 'Continuing with Pagadito...');
                        $redirect_url = $pg->exec_trans($order->get_id());

                        if ($redirect_url === false) {
                            throw new Exception('Exec trans (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());
                        }

                        // Successful process
                        $result = array(
                            'result' => 'success',
                            'redirect' => $redirect_url
                        );

                        // Associating paid transaction with the order
                        add_post_meta($order->get_id(), '_token_pagadito', $token_pagadito);

                        return $result;
                    } else {
                        $this->save_to_log($order->get_order_number(), 'The transaction is less than 1 dollar =' . $order->get_total() . $currency);
                        wc_add_notice(__('<strong>Note:</strong> Transactions less than the equivalent of 1 dollar can´t be processed', 'woo-pagadito-payment-gateway'), 'notice');
                    }
                    break;
                case "PAB":
                    if ($order->get_total() >= $pg->get_exchange_rate_pab()) {

                        // Continue with Pagadito
                        $this->save_to_log($order->get_order_number(), 'Continuing with Pagadito...');
                        $redirect_url = $pg->exec_trans($order->get_id());

                        if ($redirect_url === false) {
                            throw new Exception('Exec trans (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());
                        }

                        // Successful process
                        $result = array(
                            'result' => 'success',
                            'redirect' => $redirect_url
                        );

                        // Associating paid transaction with the order
                        add_post_meta($order->get_id(), '_token_pagadito', $token_pagadito);

                        return $result;
                    } else {
                        $this->save_to_log($order->get_order_number(), 'The transaction is less than 1 dollar =' . $order->get_total() . $currency);
                        wc_add_notice(__('<strong>Note:</strong> Transactions less than the equivalent of 1 dollar can´t be processed', 'woo-pagadito-payment-gateway'), 'notice');
                    }
                    break;
                case "DOP":
                    if ($order->get_total() >= $pg->get_exchange_rate_dop()) {

                        // Continue with Pagadito
                        $this->save_to_log($order->get_order_number(), 'Continuing with Pagadito...');
                        $redirect_url = $pg->exec_trans($order->get_id());

                        if ($redirect_url === false) {
                            throw new Exception('Exec trans (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());
                        }

                        // Successful process
                        $result = array(
                            'result' => 'success',
                            'redirect' => $redirect_url
                        );

                        // Associating paid transaction with the order
                        add_post_meta($order->get_id(), '_token_pagadito', $token_pagadito);

                        return $result;
                    } else {
                        $this->save_to_log($order->get_order_number(), 'The transaction is less than 1 dollar =' . $order->get_total() . $currency);
                        wc_add_notice(__('<strong>Note:</strong> Transactions less than the equivalent of 1 dollar can´t be processed', 'woo-pagadito-payment-gateway'), 'notice');
                    }
                    break;
            }
        } catch (Exception $e) {
            $this->save_to_log($order->get_order_number(), 'Exception = ' . $e->getMessage() . ' | CURL error = ' . $pg->curl_errorno);
            wc_add_notice(__('<strong>A problem has occurred:</strong> ', 'woo-pagadito-payment-gateway') . $e->getMessage(), 'error');
        }
    }

    public function pagadito_webhook()
    {
        // get headers
        $headers = getallheaders();
        $successful = false;

        if (!isset(
            $headers['Pagadito-Notification-Id'],
            $headers['Pagadito-Notification-Timestamp'],
            $headers['Pagadito-Auth-Algo'],
            $headers['Pagadito-Cert-Url'],
            $headers['Pagadito-Signature']
        )) {
            header('HTTP/1.1 428 Nothing to see here');
            exit;
        }

        $notification_id = $headers['Pagadito-Notification-Id'];
        $notification_timestamp = $headers['Pagadito-Notification-Timestamp'];
        $auth_algo = $headers['Pagadito-Auth-Algo'];
        $cert_url = $headers['Pagadito-Cert-Url'];
        $notification_signature = base64_decode($headers['Pagadito-Signature']);

        // verifying that the url of the certificate is under pagadito
        $cent_url_parsed = parse_url($cert_url);
        $cert_url_exploded = explode('.', $cent_url_parsed['host']);
        if ($cert_url_exploded[count($cert_url_exploded) - 2] != 'pagadito') {
            header('HTTP/1.1 429 Naughty boy');
            exit();
        }

        // get data
        $data = @file_get_contents('php://input');

        // get event id
        $obj_data = @json_decode($data);
        $event_id = $obj_data->id;

        // generate string to confirm signature
        $data_signed = $notification_id . '|' . $notification_timestamp . '|' . $event_id . '|' . crc32($data) . '|' . $this->wsk;

        // get certificate content
        // http request options to generate the stream context to obtain the certificate
        $http_options = array(
            'http' => array(
                'protocol_version' => '1.1',
                'method' => 'GET',
                'header' => array(
                    'Connection: close'
                ),
            )
        );
        $cert_stream_context = stream_context_create($http_options);
        $cert_content = file_get_contents($cert_url, FALSE, $cert_stream_context);

        // get public key
        $pubkeyid = openssl_pkey_get_public($cert_content);

        // verify signature
        $resultado = openssl_verify($data_signed, $notification_signature, $pubkeyid, $auth_algo);

        // release public key
        openssl_free_key($pubkeyid);

        // verification
        if ($resultado == 1) {
            $order = new WC_Order($obj_data->resource->ern);

            // processing
            if ($order->get_status() == 'processing') {
                $successful = true;
            }

            if (in_array($order->get_status(), array('on-hold', 'pending'))) {
                $this->save_to_log($obj_data->resource->ern, 'Processing WebHook - ' . $obj_data->resource->status);
                switch ($obj_data->resource->status) {
                    case 'REGISTERED':
                        // Registered do nothing
                        $successful = true;
                        break;
                    case 'COMPLETED':
                        // Complete the transaction
                        $order->add_order_note(__('Payment Completed, payment reference: ', 'woo-pagadito-payment-gateway') . $obj_data->resource->reference, 1);
                        $this->save_to_log($order->get_order_number(), 'Payment reference = ' . $obj_data->resource->reference);
                        $order->payment_complete($obj_data->resource->reference);
                        $successful = true;
                        break;
                    case 'VERIFYING':
                        // Verification put the transaction on hold
                        if ($order->get_status() == 'on-hold') {
                            // Verify that the transaction has not been previously marked on hold
                            break;
                        }
                        $order->add_order_note(__('Payment on Verification, payment reference: ', 'woo-pagadito-payment-gateway') . $obj_data->resource->reference, 1);
                        $order->update_status('wc-on-hold', 'The payment was in verification.');
                        $this->save_to_log($order->get_order_number(), 'Payment reference = ' . $obj_data->resource->reference);
                        $successful = true;
                        break;
                    default:
                        // In any of these states, set the transaction as failed
                        // REVOKED, FAILED, CANCELED, EXPIRED
                        $order->update_status('wc-failed', 'Payment canceled by the user.');
                        $this->save_to_log($order->get_order_number(), 'Payment reference = N/A');
                        $successful = true;
                        break;
                }
            } elseif ($order->get_status() == 'processing') {
                // At this point the payment had already been registered and the transaction completed,
                // returns code 200 so that pagadito does not send the webhook again
                $successful = true;
            } else {
                $this->save_to_log($obj_data->resource->ern, 'Order in status other than on-hold or pending');
            }
        } elseif ($resultado == 0) {
            $this->save_to_log('NA', 'WH invalid signature verification');
        } else {
            $this->save_to_log('NA', 'WH error performing signature verification');
        }

        if ($successful) {
            header('HTTP/1.1 200 OK');
        } else {
            header('HTTP/1.1 427 Nope, something is not right');
        }
        exit;
    }

    /**
     * Verify Payment
     * It redirects to the Order completed screen and in case of error it directs to the canceled order.
     */
    public function check_pagadito_response()
    {
        global $woocommerce;

        // Transaction statuses in which payment verification is allowed
        $allowed_transaction_states = array(
            'pending',
            'processing',
        );

        $values = stripslashes_deep($_GET);
        $token_pagadito = '';
        try {
            if (!isset($values['token'], $values['order_id']))
                throw new Exception(__('It tried to verify a transaction with token or order_id null', 'woo-pagadito-payment-gateway'));

            // Loading the order
            $order = new WC_Order($values['order_id']);
            $this->save_to_log($order->get_order_number(), '...Return of Pagadito');
            if (!in_array($order->get_status(), $allowed_transaction_states))
                throw new Exception(__('The transaction does not have a valid status to be processed: ', 'woo-pagadito-payment-gateway') . $order->get_status());

            // Redirect to thank you page in case the order has already been processed
            if ($order->get_status() == 'processing') {
                wp_redirect(html_entity_decode($order->get_checkout_order_received_url()));
                return;
            }

            // Get the paid token, by the URL or by the custom field
            if (!empty($values['token'])) {
                $token_pagadito = $values['token'];
            } else {
                $token_pagadito = end(get_post_meta($order->get_id(), '_token_pagadito'));
            }

            if ($token_pagadito === false)
                throw new Exception(__('The Token Pagadito was not found', 'woo-pagadito-payment-gateway'));

            if ($token_pagadito != $values['token'])
                throw new Exception(__('Token Pagadito associated with the order not correspond with the get parameter: ', 'woo-pagadito-payment-gateway') . $token_pagadito . ' != ' . $values['token']);

            $pg = new Pagadito($this->uid, $this->wsk);

            if ($this->sandbox_mode == 'yes') {
                $pg->mode_sandbox_on();
                $this->save_to_log($order->get_order_number(), 'SandBox mode activated');
            }

            if (!$pg->connect())
                throw new Exception('Connect (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());

            if (!$pg->get_status($token_pagadito))
                throw new Exception('Get Status (' . $pg->get_rs_code() . '): ' . $pg->get_rs_message());
            $respuesta_pg = $pg->get_rs_status();
            $num_aprob_pg = $pg->get_rs_reference();
            $this->save_to_log($order->get_order_number(), 'Checking payment, status = ' . $respuesta_pg);

            switch ($respuesta_pg) {
                case 'COMPLETED':
                    $order->add_order_note(__('Payment Completed, payment reference: ', 'woo-pagadito-payment-gateway') . $pg->get_rs_reference(), 1);
                    $this->save_to_log($order->get_order_number(), 'Payment reference = ' . $pg->get_rs_reference());
                    $order->payment_complete($pg->get_rs_reference());
                    wp_redirect(html_entity_decode($order->get_checkout_order_received_url()));
                    return;
                    break;
                case 'VERIFYING':
                    $order->add_order_note(__('Payment on Verification, payment reference: ', 'woo-pagadito-payment-gateway') . $pg->get_rs_reference(), 1);
                    $order->update_status('wc-on-hold', __('The payment was left in verification.', 'woo-pagadito-payment-gateway'));
                    $this->save_to_log($order->get_order_number(), 'Payment reference = ' . $pg->get_rs_reference());
                    wp_redirect(html_entity_decode($order->get_checkout_order_received_url()));
                    return;
                    break;
                default:
                    $order->add_order_note(__('Transaction not completed, status: ', 'woo-pagadito-payment-gateway') . $respuesta_pg);
                    wp_redirect(html_entity_decode($order->get_cancel_order_url()));
                    return;
                    break;
            }
            exit;
        } catch (Exception $e) {
            $this->save_to_log($order->get_order_number(), 'Verifying payment - ' . $e->getMessage());
            $order->add_order_note(__('Transaction not completed, status: ', 'woo-pagadito-payment-gateway') . $e->getMessage());

            //Redireccionando a pantalla de error
            wp_redirect(html_entity_decode($order->get_cancel_order_url()));
            return;
        }

        return false;
    }

    public function get_return_url($order = '', $page = 'thanks')
    {
        $thanks_page_id = wc_get_page_id($page);
        if ($thanks_page_id) :
            $return_url = get_permalink($thanks_page_id);
        else :
            $return_url = home_url();
        endif;

        if ($order) :
            $return_url = add_query_arg('key', $order->order_key, add_query_arg('order', $order->get_id(), $return_url));
        endif;

        if (is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes')
            $return_url = str_replace('http:', 'https:', $return_url);

        return apply_filters('woocommerce_get_return_url', $return_url);
    }
}
