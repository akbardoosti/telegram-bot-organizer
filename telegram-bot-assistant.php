<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Telegram_Bot_Assistant
 *
 * @wordpress-plugin
 * Plugin Name:       Telegram bot assistant
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            CommerceYar
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TELGRAM_BOT_ASSISTANT_VERSION', '1.0.0' );


define( 'TELEGRAM_BOT_ASSISTANT', 'RNOMPA' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-telegram-bot-assistant-activator.php
 */
function activate_telegram_bot_assistant() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-telegram-bot-assistant-activator.php';
	Telegram_Bot_Assistant_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-telegram-bot-assistant-deactivator.php
 */
function deactivate_telegram_bot_assistant() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-telegram-bot-assistant-deactivator.php';
	Telegram_Bot_Assistant_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_telegram_bot_assistant' );
register_deactivation_hook( __FILE__, 'deactivate_telegram_bot_assistant' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-telegram-bot-assistant.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_telegram_bot_assistant() {

	$plugin = new Telegram_Bot_Assistant();
	$plugin->run();

}
run_telegram_bot_assistant();

// add_filter('pre_set_site_transient_update_plugins', 'check_for_plugin_update');
// // Hook into the update check

// function check_for_plugin_update($transient) {
	
//     if (empty($transient->checked)) {
//         return $transient;
//     }
	

//     // Define plugin data
//     $plugin_slug = 'telegram-bot-assistant';
//     $current_version = '1.0.0'; // Update this to match your plugin's current version
//     $remote_url = 'https://wp93.ir/info.json'; // URL to your update JSON

//     // Get remote update data
//     $response = wp_remote_get($remote_url);
//     if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
//         return $transient;
//     }

//     $update_data = json_decode(wp_remote_retrieve_body($response));
//     if (!is_object($update_data) || version_compare($current_version, $update_data->version, '>=')) {
//         return $transient;
//     }

//     // Prepare update object
//     $obj = new stdClass();
//     $obj->slug = $plugin_slug;
//     $obj->plugin = $plugin_slug . '/' . $plugin_slug . '.php';
//     $obj->new_version = $update_data->version;
//     $obj->url = $update_data->homepage;
//     $obj->package = $update_data->download_link;

//     $transient->response[$obj->plugin] = $obj;

//     return $transient;
// }

// // Ensure the plugin update is recognized
// add_filter('plugins_api', 'plugin_update_info', 10, 3);
// function plugin_update_info($result, $action, $args) {
//     if ($action !== 'plugin_information') {
//         return $result;
//     }

//     if ($args->slug !== 'telegram-bot-assistant') {
//         return $result;
//     }

//     $remote_url = 'https://wp93.ir/info.json';
//     $response = wp_remote_get($remote_url);
//     if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
//         return $result;
//     }

//     $update_data = json_decode(wp_remote_retrieve_body($response));
//     $obj = new stdClass();
//     $obj->slug = $update_data->slug;
//     $obj->name = $update_data->name;
//     $obj->version = $update_data->version;
//     $obj->author = $update_data->author;
//     $obj->requires = $update_data->requires;
//     $obj->tested = $update_data->tested;
//     $obj->last_updated = $update_data->last_updated;
//     $obj->download_link = $update_data->download_link;
//     $obj->sections = [
//         'description' => 'Your plugin description here.',
//         'changelog' => 'Changelog details here.'
//     ];

//     return $obj;
// }