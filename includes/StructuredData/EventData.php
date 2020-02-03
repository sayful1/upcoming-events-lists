<?php

namespace UpcomingEventsLists\StructuredData;

use UpcomingEventsLists\Event;

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

class EventData {

	/**
	 * @var Event
	 */
	protected $event;

	/**
	 * Event constructor.
	 *
	 * @param Event $event
	 */
	public function __construct( $event ) {
		$this->event = $event;
	}

	public function get_structured_data() {
		$event = $this->get_event();

		$markup = array(
			"@context"  => "https://schema.org",
			"@type"     => "Event",
			"name"      => $event->get_title(),
			"startDate" => $event->get_start_date(),
			"endDate"   => $event->get_end_date(),
		);

		if ( $event->has_event_image() ) {
			$image_url       = $event->get_event_image_url();
			$markup["image"] = [ $image_url ];
		}

		return $markup;
	}

	/**
	 * @return Event
	 */
	public function get_event() {
		return $this->event;
	}
}