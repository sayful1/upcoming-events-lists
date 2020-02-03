<?php
/**
 * Plugin Name: Upcoming Events Lists
 * Plugin URI: http://wordpress.org/plugins/upcoming-events-lists
 * Description: Upcoming Events Lists let you to show a list of upcoming events on the front-end.
 * Version: 1.4.0
 * Author: Sayful Islam
 * Author URI: https://sayfulislam.com
 * Text Domain: upcoming-events-lists
 * Domain Path: languages/
 * License: GPL2
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

class Upcoming_Events_Lists {

	/**
	 * @var string
	 */
	private $plugin_name = 'upcoming-events-lists';

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '1.3.3';

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * @var array
	 */
	private $container = [];

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self - Main instance
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			self::$instance->define_constants();
			self::$instance->initialize_hooks();

			register_activation_hook( __FILE__, array( self::$instance, 'activation' ) );
			register_deactivation_hook( __FILE__, array( self::$instance, 'deactivation' ) );
		}

		return self::$instance;
	}

	/**
	 * Define plugin constants
	 */
	public function define_constants() {
		define( 'UPCOMING_EVENTS_LISTS', $this->plugin_name );
		define( 'UPCOMING_EVENTS_LISTS_VERSION', $this->version );
		define( 'UPCOMING_EVENTS_LISTS_FILE', __FILE__ );
		define( 'UPCOMING_EVENTS_LISTS_PATH', dirname( UPCOMING_EVENTS_LISTS_FILE ) );
		define( 'UPCOMING_EVENTS_LISTS_INCLUDES', UPCOMING_EVENTS_LISTS_PATH . '/includes' );
		define( 'UPCOMING_EVENTS_LISTS_URL', plugins_url( '', UPCOMING_EVENTS_LISTS_FILE ) );
		define( 'UPCOMING_EVENTS_LISTS_ASSETS', UPCOMING_EVENTS_LISTS_URL . '/assets' );
	}

	/**
	 * Initialize plugin hooks
	 */
	public function initialize_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		$this->include_files();

		// Include required files
		$this->init_classes();
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'upcoming-events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes files
	 */
	private function include_files() {
		spl_autoload_register( function ( $class ) {

			// project-specific namespace prefix
			$prefix = 'UpcomingEventsLists\\';

			// base directory for the namespace prefix
			$base_dir = UPCOMING_EVENTS_LISTS_INCLUDES . DIRECTORY_SEPARATOR;

			// does the class use the namespace prefix?
			$len = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				// no, move to the next registered autoloader
				return;
			}

			// get the relative class name
			$relative_class = substr( $class, $len );

			// replace the namespace prefix with the base directory, replace namespace
			// separators with directory separators in the relative class name, append
			// with .php
			$class_path = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			if ( file_exists( $class_path ) ) {
				require_once $class_path;
			}
		} );
	}

	/**
	 * Including the widget
	 */
	private function init_classes() {
		$this->container['assets'] = UpcomingEventsLists\Assets::init();

		// Admin functionality
		$this->container['admin'] = UpcomingEventsLists\Admin\Admin::init();

		// Frontend functionality
		if ( $this->is_request( 'frontend' ) ) {
			$this->container['frontend']   = UpcomingEventsLists\Frontend\Frontend::init();
			$this->container['rest_event'] = UpcomingEventsLists\REST\EventController::init();
		}

		add_action( 'widgets_init', array( UpcomingEventsLists\EventsWidget::class, 'register' ) );
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		return false;
	}

	/**
	 * To be run when the plugin is activated
	 * @return void
	 */
	public function activation() {
		do_action( 'upcoming_events_lists/activation' );
		flush_rewrite_rules();
	}

	/**
	 * To be run when the plugin is deactivated
	 * @return void
	 */
	public function deactivation() {
		do_action( 'upcoming_events_lists/deactivation' );
		flush_rewrite_rules();
	}
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
Upcoming_Events_Lists::init();
