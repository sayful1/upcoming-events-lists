<?php

namespace UpcomingEventsLists;

defined( 'ABSPATH' ) || exit;

class GutenbergBlock {
	/**
	 * Instance of current class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Only one instance of the class can be loaded
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'init', [ self::$instance, 'register_block_type' ] );
		}

		return self::$instance;
	}

	/**
	 * Register block type
	 *
	 * @return void
	 */
	public function register_block_type() {
		wp_register_script(
			'upcoming-events-lists-block',
			UPCOMING_EVENTS_LISTS_ASSETS . '/js/block.js',
			[ 'wp-blocks', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-element', 'wp-server-side-render' ],
			UPCOMING_EVENTS_LISTS_VERSION
		);

		register_block_type( 'upcoming-events-lists/events', array(
			'api_version'     => 2,
			'editor_script'   => 'upcoming-events-lists-block',
			'style'           => UPCOMING_EVENTS_LISTS . '-frontend',
			'render_callback' => [ $this, 'render_callback' ],
			'attributes'      => [
				'show_all_event_link'   => [ 'type' => 'bool', 'default' => true ],
				'view_type'             => [ 'type' => 'string', 'default' => 'grid' ],
				'columns_on_phone'      => [ 'type' => 'number', 'default' => 1 ],
				'columns_on_tablet'     => [ 'type' => 'number', 'default' => 2 ],
				'columns_on_desktop'    => [ 'type' => 'number', 'default' => 3 ],
				'columns_on_widescreen' => [ 'type' => 'number', 'default' => 4 ],
			],
		) );
	}

	/**
	 * Render portfolio content
	 *
	 * @param  array  $attributes  The block attributes.
	 * @param  string  $content  The block content.
	 *
	 * @return string Returns the block content.
	 */
	public function render_callback( $attributes, $content ) {
		$show_button = in_array( $attributes['show_all_event_link'], [ 'true', true, 1, '1', 'yes', 'on' ], true );

		$args = [
			'show_all_event_link'   => $show_button ? 'yes' : 'no',
			'view_type'             => 'list' === $attributes['view_type'] ? 'list' : 'grid',
			'columns_on_phone'      => $attributes['columns_on_phone'] ?? '',
			'columns_on_tablet'     => $attributes['columns_on_tablet'] ?? '',
			'columns_on_desktop'    => $attributes['columns_on_desktop'] ?? '',
			'columns_on_widescreen' => $attributes['columns_on_widescreen'] ?? '',
		];

		return \UpcomingEventsLists\Frontend\Frontend::init()->upcoming_events_list( $args );
	}
}
