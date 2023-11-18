<?php

namespace UpcomingEventsLists;

use JsonSerializable;
use WP_Post;
use WP_User;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

class Event implements JsonSerializable {

	/**
	 * Post type name
	 */
	const POST_TYPE = 'event';

	/**
	 * @var int
	 */
	private $id = 0;

	/**
	 * @var array
	 */
	private $image_src = array();

	/**
	 * @var WP_Post
	 */
	private $_post;

	/**
	 * @var array
	 */
	protected $internal_meta_keys = [
		'event-start-date'   => '',
		'event-end-date'     => '',
		'event-venue'        => '',
		'event-start-time'   => '',
		'event-end-time'     => '',
		'event-participants' => [],
	];

	/**
	 * @var array
	 */
	protected $meta_values = [];

	/**
	 * @var array
	 */
	protected $participants = [];

	/**
	 * Class constructor.
	 *
	 * @param  null|int|WP_Post  $post
	 */
	public function __construct( $post = null ) {
		$this->_post = get_post( $post );
		$this->id    = $this->_post->ID;

		foreach ( $this->internal_meta_keys as $key => $default ) {
			$this->meta_values[ $key ] = get_post_meta( $this->id, $key, true );
		}
	}

	/**
	 * @return array
	 */
	public function to_array() {
		$data = [
			'id'                 => $this->get_id(),
			'title'              => $this->get_title(),
			'excerpt'            => $this->get_excerpt(),
			'content'            => $this->get_content(),
			'permalink'          => $this->get_permalink(),
			'start_date'         => date( 'Y-m-d', $this->get_start_date() ),
			'end_date'           => date( 'Y-m-d', $this->get_end_date() ),
			'start_time'         => $this->get_meta_value( 'event-start-time' ),
			'end_time'           => $this->get_meta_value( 'event-end-time' ),
			'location'           => $this->get_location(),
			'event_image'        => $this->get_event_image_src(),
			'total_participants' => array_count_values( $this->get_event_participants() ),
		];

		$participants = $this->get_event_participants_users();
		if ( count( $participants ) ) {
			foreach ( $participants as $participant ) {
				$data['participants'][] = [
					'display_name' => $participant->display_name,
					'avatar_url'   => get_avatar_url( $participant->user_email ),
				];
			}
		}

		return $data;
	}

	/**
	 * Get meta value
	 *
	 * @param  string  $key
	 * @param  mixed  $default
	 *
	 * @return mixed|null
	 */
	public function get_meta_value( $key, $default = '' ) {
		return isset( $this->meta_values[ $key ] ) ? $this->meta_values[ $key ] : $default;
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
	 * @return WP_Post
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

		return apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $_post->post_excerpt, $_post ) );
	}

	/**
	 * Get event content
	 *
	 * @return string
	 */
	public function get_content() {
		return apply_filters( 'the_content', $this->get_post()->post_content );
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
	 * @return int event start timestamp
	 */
	public function get_start_date() {
		return $this->get_meta_value( 'event-start-date' );
	}

	/**
	 * Get event end date
	 *
	 * @return int event end timestamp
	 */
	public function get_end_date() {
		return $this->get_meta_value( 'event-end-date' );
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
	 * Get event location
	 *
	 * @return mixed
	 */
	public function get_location() {
		return $this->get_meta_value( 'event-venue' );
	}

	/**
	 * Get event participants
	 *
	 * @return array
	 */
	public function get_event_participants() {
		$participants = $this->get_meta_value( 'event-participants' );

		return is_array( $participants ) ? $participants : [];
	}

	/**
	 * Get event participants users
	 *
	 * @return array|WP_User[]
	 */
	public function get_event_participants_users() {
		$participants = $this->get_event_participants();
		if ( count( $participants ) < 1 ) {
			return [];
		}
		$ids = [];
		foreach ( $participants as $user_id => $status ) {
			if ( in_array( $status, [ 'yes', 'maybe' ] ) ) {
				$ids[] = intval( $user_id );
			}
		}
		if ( count( $ids ) < 1 ) {
			return [];
		}

		return get_users( [ 'include' => $ids ] );
	}

	/**
	 * Add participant to event
	 *
	 * @param  int  $user_id
	 * @param  string  $status
	 */
	public function add_participant( $user_id, $status ) {
		$valid_status = [ 'yes', 'no', 'maybe' ];
		if ( in_array( $status, $valid_status ) ) {
			$participants                       = $this->get_event_participants();
			$participants[ intval( $user_id ) ] = $status;

			$this->meta_values['event-participants'] = $participants;

			update_post_meta( $this->get_id(), 'event-participants', $participants );
		}
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
	 * @param  string  $size
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
	 * Get event image url
	 *
	 * @param  string  $size
	 *
	 * @return mixed|string
	 */
	public function get_event_image_url( $size = 'full' ) {
		if ( ! $this->has_event_image() ) {
			return '';
		}

		$image = $this->get_event_image_src( $size );

		return $image['src'];
	}

	/**
	 * Get event image
	 *
	 * @param  string  $size
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
	 * @param  array  $args
	 *
	 * @return self[]
	 */
	public static function get_events( $args = array() ) {
		$default = array(
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

		$args['post_type'] = static::POST_TYPE;

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

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 */
	public function jsonSerialize(): array {
		return $this->to_array();
	}
}
