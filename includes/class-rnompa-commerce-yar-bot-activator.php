<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    RNOMPA_CommerceYarBot
 * @subpackage RNOMPA_CommerceYarBot/includes
 * @author     Akbar Doosti <dousti1371@gmail.com>
 */
class RNOMPA_CommerceYarBot_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::import_files();
		$api = new RNOMPA_CommerceYarBotAPI();
		$api->activate();
	}

	public static function import_files() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-rnompa-commerce-yar-bot-api.php';
	}
}
