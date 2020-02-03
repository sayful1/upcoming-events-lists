<?php

namespace UpcomingEventsLists\REST;

use UpcomingEventsLists\Event;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

class EventController extends ApiController {

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

			add_action( 'rest_api_init', array( self::$instance, 'register_routes' ) );
		}

		return self::$instance;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/events', [
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_items' ],
				'args'     => $this->get_collection_params(),
			],
		] );
		register_rest_route( $this->namespace, '/events/(?P<id>\d+)/participants', [
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'get_participants' ],
				'args'     => $this->get_participants_params(),
			],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$page     = $request->get_param( 'page' );
		$per_page = $request->get_param( 'per_page' );

		$args = [ 'posts_per_page' => $per_page ];

		$items = Event::get_events( $args );

		return $this->respondOK( [ 'items' => $items, ] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_participants( $request ) {
		$user = wp_get_current_user();

		if ( ! $user->exists() ) {
			return $this->respondUnauthorized();
		}

		$event_id = (int) $request->get_param( 'id' );

		$event = new Event( $event_id );
		if ( ! $event->get_post() ) {
			return $this->respondNotFound();
		}

		$attend       = $request->get_param( 'will_participate' );
		$valid_status = [ 'yes', 'no', 'maybe' ];
		if ( ! in_array( $attend, $valid_status ) ) {
			return $this->respondUnprocessableEntity();
		}

		$event->add_participant( $user->ID, $attend );

		return $this->respondCreated( [ 'participant' => array_count_values( $event->get_event_participants() ) ] );
	}

	/**
	 * Retrieves the query params for the collections.
	 *
	 * @return array Query parameters for the collection.
	 */
	public function get_participants_params() {
		return array(
			'will_participate' => array(
				'description'       => __( 'Limit results to those matching a string.' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
				'enum'              => [ 'yes', 'no', 'maybe' ],
			),
		);
	}
}
