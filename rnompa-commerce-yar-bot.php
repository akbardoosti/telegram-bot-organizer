<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           RNOMPA_CommerceYarBot
 *
 * @wordpress-plugin
 * Plugin Name:       Commerce Yar Bot
 * Plugin URI:        http://www.commerceyar.ir/commerce-yar-bot
 * Description:       به کمک این افزونه میتوانید فروشگاه خود را از طریق ربات تلگرام مدیریت کنید.
 * Version:           1.0.0
 * Author:            CommerceYar
 * Author URI:        http://www.commerceyar.ir/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       commerce-yar-bot
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RNOMPA_COMMERCE_YAR_VERSION', '1.0.0' );


define( 'COMMERCE_YAR_PREFIX', 'RNOMPA' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rnompa-commerce-yar-bot-activator.php
 */
function activate_rnompa_commerce_yar_bot() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-rnompa-commerce-yar-bot-activator.php';
    RNOMPA_CommerceYarBot_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rnompa-commerce-yar-bot-deactivator.php
 */
function deactivate_rnompa_commerce_yar_bot() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-rnompa-commerce-yar-bot-deactivator.php';
    RNOMPA_CommerceYarBot_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rnompa_commerce_yar_bot' );
register_deactivation_hook( __FILE__, 'deactivate_rnompa_commerce_yar_bot' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rnompa-commerce-yar-bot.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rnompa_commerce_yar_bot() {

    $plugin = new RNOMPA_CommerceYarBot();
    $plugin->run();

}
run_rnompa_commerce_yar_bot();


// Ensure the plugin update is recognized
add_filter('plugins_api', 'rnompa_plugin_update_info', 10, 3);
function rnompa_plugin_update_info($result, $action, $args) {
    if ($action !== 'plugin_information') {
        return $result;
    }

    if ($args->slug !== 'commerce-yar-bot') {
        return $result;
    }

    $remote_url = 'https://wp93.ir/info.json';
    $response = wp_remote_get($remote_url);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return $result;
    }

    $update_data = json_decode(wp_remote_retrieve_body($response));
    $obj = new stdClass();
    $obj->slug = $update_data->slug;
    $obj->name = $update_data->name;
    $obj->version = $update_data->version;
    $obj->author = $update_data->author;
    $obj->requires = $update_data->requires;
    $obj->tested = $update_data->tested;
    $obj->last_updated = $update_data->last_updated;
    $obj->download_link = $update_data->download_link;
    $obj->sections = [
        'description' => 'Your plugin description here.',
        'changelog' => 'Changelog details here.'
    ];

    return $obj;
}