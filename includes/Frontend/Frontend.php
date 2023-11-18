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
	public function upcoming_events_list( $attributes ): string {
		$attributes = shortcode_atts( array(
			'show_all_event_link'   => 'yes',
			'view_type'             => 'list', // 'list' or 'grid'
			'columns_on_tablet'     => 2,
			'columns_on_desktop'    => 3,
			'columns_on_widescreen' => 4,
		), $attributes, 'upcoming_events_list' );

		$view   = 'grid' === $attributes['view_type'] ? 'grid' : 'list';
		$events = Event::get_events();
		ob_start();

		$classes                = [ 'upcoming-events-list', $view . '-view' ];
		$classes_item_container = [ 'upcoming-events-list-container' ];
		if ( 'grid' === $view ) {
			$classes[]                = 'shapla-columns is-multiline';
			$classes_item_container[] = static::column_to_class( $attributes );
		}
		?>
        <div class="<?php echo esc_attr( join( ' ', $classes ) ) ?>">
			<?php
			foreach ( $events as $event ) {
				echo '<div class="' . esc_attr( join( ' ', $classes_item_container ) ) . '">';
				$event->get_event_card();
				echo '</div>';
			}
			?>
        </div>
		<?php if ( 'yes' === $attributes['show_all_event_link'] ) { ?>
            <a class="upcoming-events-list-button" href="<?php echo get_post_type_archive_link( Event::POST_TYPE ); ?>">
				<?php esc_html_e( 'View All Events', 'upcoming-events' ); ?>
            </a>
			<?php
		}

		return ob_get_clean();
	}

	private static function column_to_class( array $attributes ) {
		$defaults = [
			'columns_on_phone'      => 1,
			'columns_on_tablet'     => 2,
			'columns_on_desktop'    => 3,
			'columns_on_widescreen' => 4,
		];
		$maps     = [ 1 => 12, 2 => 6, 3 => 4, 4 => 3, 6 => 2 ];
		$attrs    = [];
		foreach ( $defaults as $key => $default ) {
			$number        = isset( $attributes[ $key ] ) ? intval( $attributes[ $key ] ) : $default;
			$number        = min( 6, max( 1, $number ) );
			$attrs[ $key ] = $maps[ $number ] ?? - 1;
		}

		$classes = [ 'shapla-column', sprintf( 'is-%s-tablet', $attrs['columns_on_tablet'] ) ];
		if ( $attrs['columns_on_desktop'] < $attrs['columns_on_tablet'] ) {
			$classes[] = sprintf( 'is-%s-desktop', $attrs['columns_on_desktop'] );
		}
		if ( $attrs['columns_on_widescreen'] < $attrs['columns_on_desktop'] ) {
			$classes[] = sprintf( 'is-%s-widescreen', $attrs['columns_on_widescreen'] );
		}

		return join( ' ', $classes );
	}

	/**
	 * Enqueueing styles for the front-end widget
	 */
	public function frontend_scripts() {
		if ( ! $this->should_load_frontend_script() ) {
			return;
		}
		wp_enqueue_style( UPCOMING_EVENTS_LISTS . '-frontend' );
	}

	/**
	 * Check if frontend script should load
	 *
	 * @return bool
	 */
	public function should_load_frontend_script(): bool {
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
