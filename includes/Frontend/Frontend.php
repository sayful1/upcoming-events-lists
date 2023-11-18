<?php

namespace UpcomingEventsLists\Frontend;

use UpcomingEventsLists\Event;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

class Frontend {

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

			add_action( 'wp_enqueue_scripts', array( self::$instance, 'frontend_scripts' ) );
			add_filter( 'the_content', array( self::$instance, 'single_event_content' ) );
			add_filter( 'the_content', array( self::$instance, 'archive_event_content' ) );
			add_shortcode( 'upcoming_events_list', array( self::$instance, 'upcoming_events_list' ) );
		}

		return self::$instance;
	}

	/**
	 * Render upcoming events list html
	 *
	 * @return string
	 */
	public function upcoming_events_list() {
		$events = Event::get_events();
		ob_start();
		?>
        <div class="upcoming-events-list">
			<?php
			foreach ( $events as $event ) {
				$event->get_event_card();
			}
			?>
        </div>
        <a class="upcoming-events-list-button" href="<?php echo get_post_type_archive_link( Event::POST_TYPE ); ?>">
			<?php esc_html_e( 'View All Events', 'upcoming-events' ); ?>
        </a>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueueing styles for the front-end widget
	 */
	public function frontend_scripts() {
		if ( ! $this->should_load_frontend_script() ) {
//			return;
		}
		wp_enqueue_style( UPCOMING_EVENTS_LISTS . '-frontend' );
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

		if ( is_singular( Event::POST_TYPE ) || is_post_type_archive( Event::POST_TYPE ) ) {
			return true;
		}

		global $post;
		if ( $post instanceof \WP_Post && has_shortcode( $post->post_content, 'upcoming_events_list' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if archive-event.php file loaded in theme directory
	 *
	 * @return bool
	 */
	private function has_event_archive_template() {
		if ( locate_template( "archive-event.php" ) != '' ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if single-event.php file loaded in theme directory
	 *
	 * @return bool
	 */
	private function has_event_single_template() {
		if ( locate_template( "single-event.php" ) != '' ) {
			return true;
		}

		return false;
	}

	public function archive_event_content( $content ) {
		if ( is_post_type_archive( Event::POST_TYPE ) ) {
			if ( $this->has_event_archive_template() ) {
				return $content;
			}
		}

		return $content;
	}

	/**
	 * @param  string  $content
	 *
	 * @return string
	 */
	public function single_event_content( $content ) {
		if ( is_singular( Event::POST_TYPE ) || is_post_type_archive( Event::POST_TYPE ) ) {

			$event = new Event();

			$event_start_date = $event->get_start_date();
			$event_end_date   = $event->get_end_date();
			$event_venue      = $event->get_location();

			$event = '<table>';
			$event .= '<tr>';
			$event .= '<td><strong>' . __( 'Event Start Date:',
					'upcoming-events' ) . '</strong><br>' . date_i18n( get_option( 'date_format' ),
					$event_start_date ) . '</td>';
			$event .= '<td><strong>' . __( 'Event End Date:',
					'upcoming-events' ) . '</strong><br>' . date_i18n( get_option( 'date_format' ),
					$event_end_date ) . '</td>';
			$event .= '<td><strong>' . __( 'Event Venue:',
					'upcoming-events' ) . '</strong><br>' . $event_venue . '</td>';
			$event .= '</tr>';
			$event .= '</table>';

			$content = $event . $content;
		}

		return $content;
	}
}
