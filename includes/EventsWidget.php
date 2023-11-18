<?php

namespace UpcomingEventsLists;

use WP_Widget;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

/**
 * Class Upcoming_Events
 */
class EventsWidget extends WP_Widget {

	/**
	 * Initializing the widget
	 */
	public function __construct() {
		$widget_ops = array(
			'class'       => 'upcoming-events-lists',
			'description' => __( 'A widget to display a list of upcoming events', 'upcoming-events' )
		);

		parent::__construct(
			'sis_upcoming_events',            //base id
			__( 'Upcoming Events', 'upcoming-events' ),    //title
			$widget_ops
		);
	}


	/**
	 * Displaying the widget on the back-end
	 *
	 * @param array $instance An instance of the widget
	 */
	public function form( $instance ) {
		$widget_defaults = array(
			'title'         => 'Upcoming Events',
			'number_events' => 5
		);

		$instance = wp_parse_args( (array) $instance, $widget_defaults );
		?>

        <!-- Rendering the widget form in the admin -->
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">
                <?php _e( 'Title', 'upcoming-events' ); ?>
            </label>
            <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" class="widefat"
                   value="<?php echo esc_attr( $instance['title'] ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'number_events' ); ?>">
                <?php _e( 'Number of events to show', 'upcoming-events' ); ?>
            </label>
            <select id="<?php echo $this->get_field_id( 'number_events' ); ?>"
                    name="<?php echo $this->get_field_name( 'number_events' ); ?>" class="widefat">
				<?php for ( $i = 1; $i <= 10; $i ++ ): ?>
                    <option value="<?php echo $i; ?>" <?php selected( $i, $instance['number_events'], true ); ?>><?php echo $i; ?></option>
				<?php endfor; ?>
            </select>
        </p>

		<?php
	}


	/**
	 * Making the widget updateable
	 *
	 * @param array $new_instance New instance of the widget
	 * @param array $old_instance Old instance of the widget
	 *
	 * @return array An updated instance of the widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']         = $new_instance['title'];
		$instance['number_events'] = $new_instance['number_events'];

		return $instance;
	}


	/**
	 * Displaying the widget on the front-end
	 *
	 * @param array $args Widget options
	 * @param array $instance An instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		if ( isset( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
		}

		//Preparing to show the events
		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$events = Event::get_events();
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
		echo $args['after_widget'];
	}

	/**
	 * Register current class as widget
	 */
	public static function register() {
		register_widget( __CLASS__ );
	}
}
