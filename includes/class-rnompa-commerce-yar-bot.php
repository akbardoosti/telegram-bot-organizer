<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    RNOMPA_CommerceYarBot
 * @subpackage RNOMPA_CommerceYarBot/includes
 * @author     Akbar Doosti <dousti1371@gmail.com>
 */
class RNOMPA_CommerceYarBot {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      RNOMPA_CommerceYarBot_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RNOMPA_COMMERCE_YAR_BOT_VERSION' ) ) {
			$this->version = RNOMPA_COMMERCE_YAR_BOT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'commerce-yar-bot';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		// die('fdjfkj');
		add_action('plugins_loaded', function() {
			RNOMPA_Table_Manager::init();
			RNOMPA_Statistics::init();
			RNOMPA_Coupon::init();
			RNOMPA_Site_Info::init();
			RNOMPA_Notification::init();
			RNOMPA_Ajax::init();
			RNOMPA_REST_Routes::init();
		});
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - RNOMPA_CommerceYarBot_Loader. Orchestrates the hooks of the plugin.
	 * - RNOMPA_CommerceYarBot_i18n. Defines internationalization functionality.
	 * - RNOMPA_CommerceYarBot_Admin. Defines all hooks for the admin area.
	 * - RNOMPA_CommerceYarBot_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rnompa-commerce-yar-bot-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rnompa-commerce-yar-bot-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rnompa-commerce-yar-bot-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rnompa-commerce-yar-bot-public.php';

		$this->loader = new RNOMPA_CommerceYarBot_Loader();

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-rnompa-commerce-yar-bot-api.php';
		new RNOMPA_CommerceYarBotAPI();

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-table-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-statistics.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-coupon.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-site-info.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-notification.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/classes/class-ajax.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-rest-routes.php';


	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the RNOMPA_CommerceYarBot_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new RNOMPA_CommerceYarBot_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new RNOMPA_CommerceYarBotAdmin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new RNOMPA_CommerceYarBotPublic( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    RNOMPA_CommerceYarBot_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
