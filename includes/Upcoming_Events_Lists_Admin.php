<?php

if( ! class_exists('Upcoming_Events_Lists_Admin') ):

class Upcoming_Events_Lists_Admin
{
	public function __construct()
	{
		add_action( 'init', array( $this, 'post_type'), 0 );
		add_action('do_meta_boxes', array( $this, 'events_img_box') );

		add_filter( 'manage_edit-event_columns', array( $this, 'custom_columns_head'), 10 );
		add_action( 'manage_event_posts_custom_column', array( $this, 'custom_columns_content'), 10, 2 );
	}

	// Register Custom Post Type
	public function post_type() {

		$labels = array(
			'name'                => _x( 'Events', 'Post Type General Name', 'upcoming-events' ),
			'singular_name'       => _x( 'Event', 'Post Type Singular Name', 'upcoming-events' ),
			'menu_name'           => __( 'Event', 'upcoming-events' ),
			'parent_item_colon'   => __( 'Parent Event:', 'upcoming-events' ),
			'all_items'           => __( 'All Events', 'upcoming-events' ),
			'view_item'           => __( 'View Event', 'upcoming-events' ),
			'add_new_item'        => __( 'Add New Event', 'upcoming-events' ),
			'add_new'             => __( 'Add New', 'upcoming-events' ),
			'edit_item'           => __( 'Edit Event', 'upcoming-events' ),
			'update_item'         => __( 'Update Event', 'upcoming-events' ),
			'search_items'        => __( 'Search Event', 'upcoming-events' ),
			'not_found'           => __( 'Not found', 'upcoming-events' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'upcoming-events' ),
		);
		$args = array(
			'label'               => __( 'event', 'upcoming-events' ),
			'description'         => __( 'A list of upcoming events', 'upcoming-events' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-calendar-alt',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'event', $args );
	}

	// Move featured image box under title
	public function events_img_box(){
	    remove_meta_box( 'postimagediv', 'event', 'side' );
	    add_meta_box('postimagediv', __('Event Image', 'upcoming-events'), 'post_thumbnail_meta_box', 'event', 'side', 'low');
	}

	/**
	 * Custom columns head
	 * @param  array $defaults The default columns in the post admin
	 */
	function custom_columns_head( $defaults ) {
		unset( $defaults['date'] );

		$defaults['event_start_date'] = __( 'Start Date', 'upcoming-events' );
		$defaults['event_end_date'] = __( 'End Date', 'upcoming-events' );
		$defaults['event_venue'] = __( 'Venue', 'upcoming-events' );

		return $defaults;
	}

	/**
	 * Custom columns content
	 * @param  string 	$column_name The name of the current column
	 * @param  int 		$post_id     The id of the current post
	 */
	function custom_columns_content( $column_name, $post_id ) {
		if ( 'event_start_date' == $column_name ) {
			$start_date = get_post_meta( $post_id, 'event-start-date', true );
			echo date_i18n( get_option( 'date_format' ), $start_date );
		}

		if ( 'event_end_date' == $column_name ) {
			$end_date = get_post_meta( $post_id, 'event-end-date', true );
			echo date_i18n( get_option( 'date_format' ), $end_date );
		}

		if ( 'event_venue' == $column_name ) {
			$venue = get_post_meta( $post_id, 'event-venue', true );
			echo $venue;
		}
	}
}

endif;