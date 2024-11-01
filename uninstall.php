<?php

if (!defined('ABSPATH')) {
    exit;
}

// If the uninstallation has not been called from WordPress, it exits
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// All the Pagadito plugin configuration is removed.
global $wpdb;
$sqlQuery = "DELETE FROM $wpdb->options WHERE option_name = 'woocommerce_pagadito_settings'";
$wpdb->query($sqlQuery);
