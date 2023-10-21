<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cloudarcade.net
 * @since      1.0.0
 *
 * @package    Cloudarcade_Wp
 * @subpackage Cloudarcade_Wp/includes
 */

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
 * @package    Cloudarcade_Wp
 * @subpackage Cloudarcade_Wp/includes
 * @author     CloudArcade <hello@redfoc.com>
 */
class Cloudarcade_Wp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cloudarcade_Wp_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	
		if ( defined( 'CLOUDARCADE_WP_VERSION' ) ) {
			$this->version = CLOUDARCADE_WP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'cloudarcade-wp';

		$this->load_dependencies();
		$this->set_locale();
	
		//$this->define_admin_hooks();
		//$this->define_public_hooks();
		$this->setup_second_db();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cloudarcade_Wp_Loader. Orchestrates the hooks of the plugin.
	 * - Cloudarcade_Wp_i18n. Defines internationalization functionality.
	 * - Cloudarcade_Wp_Admin. Defines all hooks for the admin area.
	 * - Cloudarcade_Wp_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cloudarcade-wp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cloudarcade-wp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cloudarcade-wp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cloudarcade-wp-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cloudarcade-wp-post-types.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cloudarcade-wp-taxonomies.php';
    	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cloudarcade-wp-shortcodes.php';

		

		$this->loader = new Cloudarcade_Wp_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cloudarcade_Wp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Cloudarcade_Wp_i18n();

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

		$plugin_admin = new Cloudarcade_Wp_Admin( $this->get_plugin_name(), $this->get_version() );

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

		$plugin_public = new Cloudarcade_Wp_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader = new Cloudarcade_Wp_Loader();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		// Initialize post types
		$post_types = new Cloudarcade_Wp_Post_Types();
		$post_types->init();
		flush_rewrite_rules();


		// Initialize taxonomies
		$taxonomies = new Cloudarcade_Wp_Taxonomies();
		$taxonomies->init();

		// Initialize shortcodes
		$shortcodes = new Cloudarcade_Wp_Shortcodes();
		$shortcodes->init();

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
	 * @return    Cloudarcade_Wp_Loader    Orchestrates the hooks of the plugin.
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
	public function setup_second_db() {
        global $second_db;
		$settings = new cloudarcadeSettingsAPI();
		
		if(  
			$settings->get_option( 'db_name', 'cloudarcade_db_settings', '' ) != '' &&
			$settings->get_option( 'db_host', 'cloudarcade_db_settings', '' ) != ''
		 ){
			if (@mysqli_connect(
				$settings->get_option( 'db_host', 'cloudarcade_db_settings', '' ),
				$settings->get_option( 'db_user', 'cloudarcade_db_settings', '' ),
				$settings->get_option( 'db_pass', 'cloudarcade_db_settings', '' ),
				$settings->get_option( 'db_name', 'cloudarcade_db_settings', '' )
			)) $second_db = new wpdb(
				$settings->get_option( 'db_user', 'cloudarcade_db_settings', '' ), 
				$settings->get_option( 'db_pass', 'cloudarcade_db_settings', '' ), 
				$settings->get_option( 'db_name', 'cloudarcade_db_settings', '' ),
				$settings->get_option( 'db_host', 'cloudarcade_db_settings', '' )
			);  // use your actual database info here
			else add_action( 'admin_notices', function () {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php _e( 'Cloudarcade DB Error! Please, check credentials!', 'sample-text-domain' ); ?></p>
				</div>
				<?php
			});
		 }
        
    }

}
