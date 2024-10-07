<?php
/*
 * Copyright (c) 2021 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class Filter{
	public $service_id = 0;
	public $agent_id = 0;
	public $location_id = 0;

	public $connections = [];

	public ?int $week_day = null;
	public ?string $date_from = null;
	public ?string $date_to = null;

	public ?int $start_time = null;
	public ?int $end_time = null;

	public array $statuses = [];
	public array $exclude_booking_ids = [];
	public bool $consider_cart_items = false;
	public bool $exact_match = false;
	public int $timeshift_minutes = 0;

	function __construct(array $args = []){
		$allowed_args = [ 'service_id',
											'agent_id',
											'location_id',
											'connections',
											'date_from',
											'date_to',
											'start_time',
											'end_time',
											'week_day',
											'statuses',
											'exclude_booking_ids',
											'timeshift_minutes',
											'exact_match'];
		foreach($args as $key => $arg){
			if(in_array($key, $allowed_args)) $this->$key = $arg;
		}
	}

	public static function create_from_booking_request(BookingRequest $booking_request): Filter{
		return new self([ 'date_from'       => $booking_request->start_date,
											'start_time'      => $booking_request->start_time,
                      'end_time'        => $booking_request->end_time,
                      'agent_id'        => $booking_request->agent_id,
                      'location_id'     => $booking_request->location_id,
                      'service_id'      => $booking_request->service_id]);
	}

}
