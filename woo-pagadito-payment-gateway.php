<?php

/**
 * Plugin Name: WooCommerce Pagadito Payment Gateway
 * Plugin URI: https://wordpress.org/plugins/woo-pagadito-payment-gateway/
 * Description: Pagadito allows you to pay online in a safe, easy and reliable way.
 * Version: 6.1.1
 * 
 * Author: Pagadito Developers
 * Author URI: https://dev.pagadito.com/
 * 
 * Text Domain: woo-pagadito-payment-gateway
 * Domain Path: /languages/
 * 
 * License: LGPL3
 */

/*  Copyright (c) 2021 PAGADITO EL SALVADOR S.A. DE C.V. (developers@pagadito.com)

    This program is Free Software: You can redistribute it and/or modify it under
    the terms of the GNU Lesser General Public License (GNU Lesser General Public
    License), version 3 or higher, as published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY
    WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
    PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program; otherwise write to the Free Software Foundation, Inc.,
    51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC Pagadito Payment gateway plugin class.
 *
 * @class WC_Pagadito_Payments
 */
class WC_Pagadito_Payments
{
    /**
     * Plugin bootstrapping.
     */
    public static function init()
    {
        // Pagadito Payments gateway class.
        add_action('plugins_loaded', array(__CLASS__, 'includes'), 0);

        // Make the Pagadito Payments gateway available to WC.
        add_filter('woocommerce_payment_gateways', array(__CLASS__, 'add_gateway'));

        // Registers WooCommerce Blocks integration.
        add_action('woocommerce_blocks_loaded', array(__CLASS__, 'woocommerce_gateway_pagadito_woocommerce_block_support'));

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'plugin_action_links'));
        add_filter('woocommerce_thankyou_order_received_text',  array(__CLASS__, 'woo_change_order_received_text'), 10, 2);
    }

    /**
     * Add the Pagadito Payment gateway to the list of available gateways.
     *
     * @param array
     */
    public static function add_gateway($gateways)
    {
        $gateways[] = 'WC_Gateway_Pagadito';
        return $gateways;
    }

    /**
     * Include the Pagadito Payment gateway class.
     */
    public static function includes()
    {
        // Make the WC_Gateway_Pagadito class available.
        if (class_exists('WC_Payment_Gateway')) {
            require_once 'includes/class-wc-pagadito-payment-gateway.php';
        }
    }

    /**
     * Plugin url.
     *
     * @return string
     */
    public static function plugin_url()
    {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    /**
     * Plugin url.
     *
     * @return string
     */
    public static function plugin_abspath()
    {
        return trailingslashit(plugin_dir_path(__FILE__));
    }

    /**
     * Registers WooCommerce Blocks integration.
     *
     */
    public static function woocommerce_gateway_pagadito_woocommerce_block_support()
    {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            require_once 'includes/blocks/class-wc-pagadito-payments-blocks.php';
            add_action(
                'woocommerce_blocks_payment_method_type_registration',
                function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                    $payment_method_registry->register(new WC_Gateway_Pagadito_Blocks_Support());
                }
            );
        }
    }

    /**
     * Adding additional links to the Plugin
     */
    public static function plugin_action_links($links)
    {
        $links[] = '<a href="admin.php?page=wc-settings&tab=checkout&section=pagadito">' . esc_html__('Configuration', 'woo-pagadito-payment-gateway') . '</a>';
        $links[] = '<a href="https://dev.pagadito.com/index.php?mod=docs" target="_blank">' . esc_html__('Documentation', 'woo-pagadito-payment-gateway') . '</a>';

        return $links;
    }

    /**
     * Show Pagadito Approval Number
     */
    public static function woo_change_order_received_text($str, $order)
    {
        $str = __('APPROVAL NUMBER: <strong>', 'woo-pagadito-payment-gateway') . $order->get_transaction_id() . '</strong>';

        return $str;
    }
}

WC_Pagadito_Payments::init();
