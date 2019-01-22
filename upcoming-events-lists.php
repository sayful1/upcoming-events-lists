<?php
/**
 * Plugin Name: Upcoming Events Lists
 * Plugin URI: http://wordpress.org/plugins/upcoming-events-lists
 * Description: Upcoming Events Lists let you to show a list of upcoming events on the front-end.
 * Version: 1.3.3
 * Author: Sayful Islam
 * Author URI: https://sayfulislam.com
 * Text Domain: upcoming-events-lists
 * Domain Path: languages/
 * License: GPL2
 */

if ( ! class_exists( 'Upcoming_Events_Lists' ) ) {

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
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @return self - Main instance
		 */
		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();

				self::$instance->define_constants();
				self::$instance->initialize_hooks();
			}

			return self::$instance;
		}

		/**
		 * Define plugin constants
		 */
		public function define_constants() {
			$this->define( 'UPCOMING_EVENTS_LISTS_VERSION', $this->version );
			$this->define( 'UPCOMING_EVENTS_LISTS_FILE', __FILE__ );
			$this->define( 'UPCOMING_EVENTS_LISTS_PATH', dirname( UPCOMING_EVENTS_LISTS_FILE ) );
			$this->define( 'UPCOMING_EVENTS_LISTS_INCLUDES', UPCOMING_EVENTS_LISTS_PATH . '/includes' );
			$this->define( 'UPCOMING_EVENTS_LISTS_URL', plugins_url( '', UPCOMING_EVENTS_LISTS_FILE ) );
			$this->define( 'UPCOMING_EVENTS_LISTS_ASSETS', UPCOMING_EVENTS_LISTS_URL . '/assets' );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Initialize plugin hooks
		 */
		public function initialize_hooks() {
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

			// Include required files
			$this->includes();

			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		}

		/**
		 * Load plugin textdomain
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'upcoming-events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Including the widget
		 */
		public function includes() {
			include_once dirname( __FILE__ ) . '/includes/class-upcoming-events-lists-admin.php';
			include_once dirname( __FILE__ ) . '/includes/class-upcoming-events-lists-frontend.php';
			include_once dirname( __FILE__ ) . '/includes/class-widget-upcoming-events-lists.php';
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
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
Upcoming_Events_Lists::init();
