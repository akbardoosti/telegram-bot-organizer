<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Telegram_Bot_Assistant
 * @subpackage Telegram_Bot_Assistant/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Telegram_Bot_Assistant
 * @subpackage Telegram_Bot_Assistant/includes
 * @author     Your Name <email@example.com>
 */
class Telegram_Bot_Assistant_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::import_files();
		$api = new TelegramAssistanAPI();
		$api->activate();
	}

	public static function import_files() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-telegram-bot-assistant-api.php';
	}
}
