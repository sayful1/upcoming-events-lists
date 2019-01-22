<?php

if ( ! defined( 'ABSPATH' ) ) {
	die; // If this file is called directly, abort.
}

if ( ! class_exists( 'Upcoming_Events_Lists_Frontend' ) ) {

	class Upcoming_Events_Lists_Frontend {

		/**
		 * The instance of the class
		 *
		 * @var self
		 */
		private static $instance;

		/**
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @return self
		 */
		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();

				self::$instance->initialize_hooks();
			}

			return self::$instance;
		}

		/**
		 * Initialize frontend hooks
		 */
		public function initialize_hooks() {
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
			add_filter( 'the_content', array( $this, 'single_event_content' ) );
		}

		/**
		 * Enqueueing styles for the front-end widget
		 */
		public function frontend_scripts() {
			if ( ! $this->should_load_frontend_script() ) {
				return;
			}
			wp_enqueue_style( 'upcoming-events-lists', UPCOMING_EVENTS_LISTS_ASSETS . '/css/style.css',
				array(), UPCOMING_EVENTS_LISTS_VERSION, 'all' );
		}

		/**
		 * Check if frontend script should load
		 *
		 * @return bool
		 */
		public function should_load_frontend_script() {
			if ( is_active_widget( '', '', 'sis_upcoming_events', true ) ) {
				return true;
			}

			return false;
		}

		/**
		 * @param string $content
		 *
		 * @return string
		 */
		public function single_event_content( $content ) {
			if ( is_singular( 'event' ) || is_post_type_archive( 'event' ) ) {

				$event_start_date = get_post_meta( get_the_ID(), 'event-start-date', true );
				$event_end_date   = get_post_meta( get_the_ID(), 'event-end-date', true );
				$event_venue      = get_post_meta( get_the_ID(), 'event-venue', true );

				$event = '<table>';
				$event .= '<tr>';
				$event .= '<td><strong>' . __( 'Event Start Date:', 'upcoming-events' ) . '</strong><br>' . date_i18n( get_option( 'date_format' ), $event_start_date ) . '</td>';
				$event .= '<td><strong>' . __( 'Event End Date:', 'upcoming-events' ) . '</strong><br>' . date_i18n( get_option( 'date_format' ), $event_end_date ) . '</td>';
				$event .= '<td><strong>' . __( 'Event Venue:', 'upcoming-events' ) . '</strong><br>' . $event_venue . '</td>';
				$event .= '</tr>';
				$event .= '</table>';

				$content = $event . $content;
			}

			return $content;
		}
	}
}

Upcoming_Events_Lists_Frontend::init();
