<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    RNOMPA_CommerceYarBot
 * @subpackage RNOMPA_CommerceYarBot/includes
 * @author     Akbar Doosti <dousti1371@gmail.com>
 */
class RNOMPA_CommerceYarBot_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'rnompa-commerce-yar',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
