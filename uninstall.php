<?php
// If this file is called directly, abort.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

global $wpdb;

// Table names with prefix
$website_inputs_table = $wpdb->prefix . 'website_inputs';
$status_table = $wpdb->prefix . 'telba_status';

// IMPORTANT: Table names cannot be parameterized with $wpdb->prepare()
// but we can still escape them for safety using esc_sql().
$website_inputs_table_esc = esc_sql($website_inputs_table);
$status_table_esc = esc_sql($status_table);

$wpdb->query("DROP TABLE IF EXISTS `{$website_inputs_table_esc}`;");
$wpdb->query("DROP TABLE IF EXISTS `{$status_table_esc}`;");