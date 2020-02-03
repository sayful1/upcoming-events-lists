<?php

namespace UpcomingEventsLists;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

class Assets {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Plugin name slug
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin assets url
	 *
	 * @var string
	 */
	private $assets_url = '';

	/**
	 * plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'wp_loaded', [ self::$instance, 'register' ] );
		}

		return self::$instance;
	}

	/**
	 * Check if script debugging is enabled
	 *
	 * @return bool
	 */
	private function is_script_debug_enabled() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}

	/**
	 * Register our app scripts and styles
	 *
	 * @return void
	 */
	public function register() {
		$this->plugin_name = UPCOMING_EVENTS_LISTS;
		$this->version     = UPCOMING_EVENTS_LISTS_VERSION;
		$this->assets_url  = UPCOMING_EVENTS_LISTS_ASSETS;

		if ( $this->is_script_debug_enabled() ) {
			$this->version = $this->version . '-' . time();
		}

		$this->register_scripts( $this->get_scripts() );
		$this->register_styles( $this->get_styles() );
	}

	/**
	 * Register scripts
	 *
	 * @param array $scripts
	 *
	 * @return void
	 */
	private function register_scripts( $scripts ) {
		foreach ( $scripts as $handle => $script ) {
			$deps      = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : true;
			$version   = isset( $script['version'] ) ? $script['version'] : $this->version;
			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register styles
	 *
	 * @param array $styles
	 *
	 * @return void
	 */
	public function register_styles( $styles ) {
		foreach ( $styles as $handle => $style ) {
			$deps = isset( $style['deps'] ) ? $style['deps'] : false;
			wp_register_style( $handle, $style['src'], $deps, $this->version );
		}
	}

	/**
	 * Get all registered scripts
	 *
	 * @return array
	 */
	public function get_scripts() {
		$scripts = [];

		return $scripts;
	}

	/**
	 * Get registered styles
	 *
	 * @return array
	 */
	public function get_styles() {
		$styles = [
			$this->plugin_name . '-frontend' => [
				'src' => $this->assets_url . '/css/frontend.css'
			]
		];

		return $styles;
	}
}
