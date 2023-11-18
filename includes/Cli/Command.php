<?php

namespace UpcomingEventsLists\Cli;

use UpcomingEventsLists\Event;
use WP_CLI;
use WP_CLI_Command;
use WP_Post;

defined( 'ABSPATH' ) || exit;

class Command extends WP_CLI_Command {

	/**
	 * Display Carousel Slider Information
	 */
	public function info() {
		WP_CLI::success( 'Welcome to the Upcoming Events Lists WP-CLI Extension!' );
		WP_CLI::line( '' );
		WP_CLI::line( '- Plugin Version: ' . UPCOMING_EVENTS_LISTS_VERSION );
		WP_CLI::line( '- Plugin Directory: ' . UPCOMING_EVENTS_LISTS_PATH );
		WP_CLI::line( '- Plugin Public URL: ' . UPCOMING_EVENTS_LISTS_URL );
		WP_CLI::line( '' );
	}

	/**
	 * Generate dummy data
	 *
	 * @return void
	 */
	public function create_dummy_data() {
		$events_ids = static::create_dummy_events( 5 );
		WP_CLI::line( sprintf( 'Upcoming Events Lists: %s dummy events have been generated.', count( $events_ids ) ) );
		$page_id = static::create_test_page();
		WP_CLI::line( sprintf( 'Upcoming Events Lists: Test page (#%s) has been generated.', $page_id ) );
	}

	/**
	 * Creates Filterable Portfolio test page
	 *
	 * @return int|WP_Error
	 */
	private static function create_test_page() {
		$page_path    = 'upcoming-events-lists-test';
		$page_title   = __( 'Upcoming Events Lists Test', 'carousel-slider' );
		$page_content = '[upcoming_events_list]';

		// Check that the page doesn't exist already
		$_page     = get_page_by_path( $page_path );
		$page_data = [
			'post_content'   => $page_content,
			'post_name'      => $page_path,
			'post_title'     => $page_title,
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
		];

		if ( $_page instanceof WP_Post ) {
			$page_data['ID'] = $_page->ID;

			return wp_update_post( $page_data );
		}

		return wp_insert_post( $page_data );
	}

	/**
	 * Create dummy portfolios
	 *
	 * @param  int  $total
	 */
	private static function create_dummy_events( $total = 1 ) {
		$venues     = [
			'Bongobondhu Auditorium, Barisal University, Barisal',
			'Grace N Bakes, 1, Avinashi Rd · Coimbatore',
			'Hotel O2 Oxygen, Kolkata, India'
		];
		$images     = self::get_images( 'full', $total );
		$images_ids = wp_list_pluck( $images, 'id' );
		$events_ids = [];
		$datetime   = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
		foreach ( range( 1, $total ) as $number ) {
			$id = wp_insert_post( [
				'post_title'   => self::lorem( 1, 10, false ),
				'post_excerpt' => self::lorem( 1, 30, false ),
				'post_content' => self::lorem( 5, 600, false ),
				'post_status'  => 'publish',
				'post_type'    => Event::POST_TYPE,
			] );
			if ( ! is_wp_error( $id ) ) {
				$date = clone $datetime;
				$date->setTimestamp( $date->getTimestamp() + ( DAY_IN_SECONDS * $number ) );

				add_post_meta( $id, 'event-start-date', $date->format( 'Y-m-d' ) );
				add_post_meta( $id, 'event-end-date', $date->format( 'Y-m-d' ) );
				add_post_meta( $id, 'event-start-time', '10:00' );
				add_post_meta( $id, 'event-end-time', '16:00' );

				$vanue = $venues[ rand( 0, 2 ) ];
				add_post_meta( $id, 'event-venue', $vanue );

				$image_id = isset( $images_ids[ $number ] ) ? intval( $images_ids[ $number ] ) : $images_ids[0];
				if ( $image_id ) {
					set_post_thumbnail( $id, $image_id );
				}

				$events_ids[] = $id;
			}
		}

		return $events_ids;
	}

	/**
	 * Generate lorem text
	 *
	 * @param  int  $sentence
	 * @param  int  $max_words
	 * @param  bool  $prepend_lorem_text
	 *
	 * @return string
	 */
	private static function lorem( $sentence = 1, $max_words = 20, $prepend_lorem_text = true ) {
		$out = '';
		if ( $prepend_lorem_text ) {
			$out = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, ' .
			       'sed do eiusmod tempor incididunt ut labore et dolore magna ' .
			       'aliqua.';
		}
		$rnd       = explode( ' ',
			'a ab ad accusamus adipisci alias aliquam amet animi aperiam ' .
			'architecto asperiores aspernatur assumenda at atque aut beatae ' .
			'blanditiis cillum commodi consequatur corporis corrupti culpa ' .
			'cum cupiditate debitis delectus deleniti deserunt dicta ' .
			'dignissimos distinctio dolor ducimus duis ea eaque earum eius ' .
			'eligendi enim eos error esse est eum eveniet ex excepteur ' .
			'exercitationem expedita explicabo facere facilis fugiat harum ' .
			'hic id illum impedit in incidunt ipsa iste itaque iure iusto ' .
			'laborum laudantium libero magnam maiores maxime minim minus ' .
			'modi molestiae mollitia nam natus necessitatibus nemo neque ' .
			'nesciunt nihil nisi nobis non nostrum nulla numquam occaecati ' .
			'odio officia omnis optio pariatur perferendis perspiciatis ' .
			'placeat porro possimus praesentium proident quae quia quibus ' .
			'quo ratione recusandae reiciendis rem repellat reprehenderit ' .
			'repudiandae rerum saepe sapiente sequi similique sint soluta ' .
			'suscipit tempora tenetur totam ut ullam unde vel veniam vero ' .
			'vitae voluptas' );
		$max_words = $max_words <= 3 ? 4 : $max_words;
		for ( $i = 0, $add = $sentence - (int) $prepend_lorem_text; $i < $add; $i ++ ) {
			shuffle( $rnd );
			$words = array_slice( $rnd, 0, mt_rand( 3, $max_words ) );
			$out   .= ( ! $prepend_lorem_text && $i == 0 ? '' : ' ' ) . ucfirst( implode( ' ', $words ) ) . '.';
		}

		return $out;
	}

	/**
	 * Get list of images sorted by its width and height
	 *
	 * @param  string  $image_size
	 * @param  int  $per_page
	 *
	 * @return array
	 */
	private static function get_images( $image_size = 'full', $per_page = 100 ) {
		$args        = [
			'order'          => 'DESC',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'any',
			'posts_per_page' => $per_page,
			'orderby'        => 'rand',
		];
		$attachments = get_posts( $args );

		$images = [];

		foreach ( $attachments as $attachment ) {
			if ( ! $attachment instanceof WP_Post ) {
				continue;
			}

			if ( ! in_array( $attachment->post_mime_type, array( 'image/jpeg', 'image/png' ) ) ) {
				continue;
			}

			$src = wp_get_attachment_image_src( $attachment->ID, $image_size );

			$images[] = [
				'id'           => $attachment->ID,
				'title'        => $attachment->post_title,
				'description'  => $attachment->post_content,
				'caption'      => $attachment->post_excerpt,
				'image_src'    => $src[0],
				'image_width'  => $src[1],
				'image_height' => $src[2],
			];
		}

		$widths  = wp_list_pluck( $images, 'image_width' );
		$heights = wp_list_pluck( $images, 'image_height' );

		// Sort the $images with $widths and $heights descending
		array_multisort( $widths, SORT_DESC, $heights, SORT_DESC, $images );

		return $images;
	}
}
