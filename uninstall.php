<?php
// If this file is called directly, abort.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

global $wpdb;

// Table names with prefix, escaped for safety
$website_inputs_table = esc_sql($wpdb->prefix . 'website_inputs');
$status_table = esc_sql($wpdb->prefix . 'telba_status');

// Drop the custom tables
$wpdb->query("DROP TABLE IF EXISTS `$website_inputs_table`;");
$wpdb->query("DROP TABLE IF EXISTS `$status_table`;");