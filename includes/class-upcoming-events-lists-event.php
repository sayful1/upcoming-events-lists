<?php

if ( ! defined( 'ABSPATH' ) ) {
	die; // If this file is called directly, abort.
}

if ( ! class_exists( 'Upcoming_Events_Lists_Event' ) ) {

	class Upcoming_Events_Lists_Event {

		/**
		 * @var int
		 */
		private $id = 0;

		/**
		 * @var string
		 */
		private $start_date;

		/**
		 * @var string
		 */
		private $end_date;

		/**
		 * @var string
		 */
		private $location;

		/**
		 * @var array
		 */
		private $image_src = array();

		/**
		 * @var \WP_Post
		 */
		private $_post;

		/**
		 * Upcoming_Events_Lists_Event constructor.
		 *
		 * @param null|int|\WP_Post $post
		 */
		public function __construct( $post = null ) {
			$this->_post = get_post( $post );
			$this->id    = $this->_post->ID;

			$this->start_date = get_post_meta( $this->id, 'event-start-date', true );
			$this->end_date   = get_post_meta( $this->id, 'event-end-date', true );
			$this->location   = get_post_meta( $this->id, 'event-venue', true );
		}

		/**
		 * Get event id
		 *
		 * @return int
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Get post object
		 *
		 * @return \WP_Post
		 */
		public function get_post() {
			return $this->_post;
		}

		/**
		 * Get event title
		 *
		 * @return string
		 */
		public function get_title() {
			return get_the_title( $this->get_post() );
		}

		/**
		 * Get event excerpt
		 *
		 * @return string
		 */
		public function get_excerpt() {
			$_post = $this->get_post();

			return apply_filters( 'get_the_excerpt', $_post->post_excerpt, $_post );
		}

		/**
		 * Get event permalink
		 *
		 * @return false|string
		 */
		public function get_permalink() {
			return get_the_permalink( $this->get_post() );
		}

		/**
		 * Get event start date
		 *
		 * @return mixed
		 */
		public function get_start_date() {
			return $this->start_date;
		}

		/**
		 * Get start date for display
		 *
		 * @return string
		 */
		public function get_display_date() {
			$date_format = get_option( 'date_format' );

			$day  = date_i18n( 'l', $this->get_start_date() );
			$date = date_i18n( $date_format, $this->get_start_date() );

			return sprintf( "%s, %s", $day, $date );
		}

		/**
		 * Get event end date
		 *
		 * @return mixed
		 */
		public function get_end_date() {
			return $this->end_date;
		}

		/**
		 * Get event location
		 *
		 * @return mixed
		 */
		public function get_location() {
			return $this->location;
		}

		/**
		 * Check if event exists
		 *
		 * @return bool
		 */
		public function has_event_image() {
			$attachment_id = get_post_thumbnail_id( $this->get_id() );

			return (bool) $attachment_id;
		}

		/**
		 * Get event image source
		 *
		 * @param string $size
		 *
		 * @return array
		 */
		public function get_event_image_src( $size = 'full' ) {
			if ( ! isset( $this->image_src[ $size ] ) ) {
				$thumbnail_id = get_post_thumbnail_id( $this->id );
				if ( ! $thumbnail_id ) {
					return [];
				}
				$src       = wp_get_attachment_image_src( $thumbnail_id, $size );
				$image_alt = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );

				$this->image_src[ $size ]['src']    = $src[0];
				$this->image_src[ $size ]['width']  = $src[1];
				$this->image_src[ $size ]['height'] = $src[2];
				$this->image_src[ $size ]['alt']    = trim( strip_tags( $image_alt ) );
			}

			return $this->image_src[ $size ];
		}

		/**
		 * Get event image
		 *
		 * @param string $size
		 *
		 * @return string
		 */
		public function get_event_image( $size = 'full' ) {
			$attachment_id = get_post_thumbnail_id( $this->get_id() );

			return wp_get_attachment_image( $attachment_id, $size );
		}

		/**
		 * Get event card
		 */
		public function get_event_card() {
			?>
            <div id="event-<?php echo $this->get_id(); ?>" class="upcoming-events-list-item">
				<?php if ( $this->has_event_image() ) { ?>
                    <div class="upcoming-events-list-item__media">
						<?php echo $this->get_event_image(); ?>
                    </div>
				<?php } ?>
                <div class="upcoming-events-list-item__title">
                    <a class="upcoming-events-list-item__title-text" href="<?php echo $this->get_permalink(); ?>">
						<?php echo $this->get_title(); ?>
                    </a>
                </div>
                <div class="upcoming-events-list-item__location">
					<?php echo $this->get_location(); ?>
                </div>
                <div class="upcoming-events-list-item__datetime">
					<?php echo $this->get_display_date(); ?>
                </div>
            </div>
			<?php
		}

		/**
		 * Get events
		 *
		 * @param array $args
		 *
		 * @return array
		 */
		public static function get_events( $args = array() ) {
			$default = array(
				'post_type'           => 'event',
				'posts_per_page'      => 5,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
				'meta_key'            => 'event-start-date',
				'orderby'             => 'meta_value_num',
				'order'               => 'ASC',
				'meta_query'          => array(
					'relation' => 'AND',
					array(
						'key'     => 'event-end-date',
						'value'   => current_time( 'timestamp' ),
						'compare' => '>='
					)
				)
			);

			$args = wp_parse_args( $args, $default );

			$_events = get_posts( $args );
			if ( count( $_events ) < 1 ) {
				return array();
			}

			$events = array();
			foreach ( $_events as $event ) {
				$events[] = new self( $event );
			}

			return $events;
		}
	}
}
