<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class BookingResource{
	public int $agent_id;
	public int $service_id;
	public int $location_id;
	public int $max_capacity;
	public int $min_capacity;
	public int $min_capacity_to_be_blocked;
	public int $timeblock_interval;
	public string $date;
	public array $work_time_periods = [];
	public array $booked_time_periods = [];
	// time periods that are usually part of work periods but a blocked now for some reason(time past today),
	// it's different from booked periods, because they have no bookings
	public array $blocked_time_periods = [];
	public array $slots = [];


	public array $bookable_minutes = [];
	public array $work_minutes = [];

	function __construct($args = []){
		$allowed_props = self::allowed_props();
		foreach($args as $key => $arg){
			if(in_array($key, $allowed_props)) $this->$key = $arg;
		}
	}

	/**
	 * @return \OsAgentModel
	 */
	function get_agent(): \OsAgentModel{
		return ($this->agent_id) ? new \OsAgentModel($this->agent_id) : new \OsAgentModel();
	}

	protected function retrieve_service_data(){
		$service = new \OsServiceModel($this->service_id);
		$this->max_capacity = $service->capacity_max;
		$this->timeblock_interval = $service->timeblock_interval ? intval($service->timeblock_interval) : \OsSettingsHelper::get_default_timeblock_interval();
		$this->min_capacity_to_be_blocked = $service->get_capacity_needed_before_slot_is_blocked();
	}


	public function get_timeblock_interval(){
		if(isset($this->timeblock_interval)){
			return $this->timeblock_interval;
		}else{
			$this->retrieve_service_data();
			return $this->timeblock_interval;
		}
	}


	public function get_min_capacity_to_be_blocked(){
		if(isset($this->min_capacity_to_be_blocked)){
			return $this->min_capacity_to_be_blocked;
		}else{
			$this->retrieve_service_data();
			return $this->min_capacity_to_be_blocked;
		}
	}

	public function get_max_capacity(){
		if(isset($this->max_capacity)){
			return $this->max_capacity;
		}else{
			$this->retrieve_service_data();
			return $this->max_capacity;
		}
	}

	/**
	 * @param BookingRequest $booking_request
	 * @param int $selectable_time_interval
	 * @return void
	 */
	public function build_bookable_slots(BookingRequest $booking_request, int $selectable_time_interval){
		$this->slots = [];
		$this->work_minutes = [];
		foreach($this->work_time_periods as $time_period){
      for($minute = $time_period->start_time; $minute <= $time_period->end_time - $booking_request->duration; $minute+= $selectable_time_interval){
				$period = new TimePeriod(['start_time' => $minute - $booking_request->buffer_before, 'end_time' => $minute + $booking_request->duration + $booking_request->buffer_after]);
				$this->work_minutes[] = $minute;
				// add booking slot
				$slot = new BookingSlot();
				$slot->start_date = $this->date;
				$slot->start_time = $minute;
				$slot->max_capacity = $this->get_max_capacity();
				$slot->min_capacity_to_be_blocked = $this->get_min_capacity_to_be_blocked();

				// ---------------------
	      // LOGIC FOR "BOOKED" PERIODS
				// ---------------------
				foreach($this->booked_time_periods as $booked_period){
		      if ($booked_period->start_time_with_buffer() >= $period->end_time || $booked_period->end_time_with_buffer() <= $period->start_time) {
						// not intersected
		      }else{
						// intersects with a booked period, disqualify it
			      if($booked_period->service_id != $this->service_id){
							// if it's a different service being performed - block full capacity of a slot, because you can't share capacities between different services
							$slot->booked_capacity = $this->max_capacity;
			      }else{
				      $slot->booked_capacity+= $booked_period->total_attendees;
			      }
		      }
				}
				// ---------------------
	      // LOGIC FOR "BLOCKED" PERIODS
				// ---------------------
				foreach($this->blocked_time_periods as $blocked_period){
		      if ($blocked_period->start_time >= $period->end_time || $blocked_period->end_time <= $period->start_time) {
						// not intersected
		      }else{
						// intersects with a blocked period, disqualify it
			      $slot->booked_capacity = $slot->max_capacity;
		      }
				}
				$this->slots[] = $slot;
      }
		}
	}

	// Function to print the intersection
	private function find_intersection(array $time_periods){
    // First interval
    $start = $time_periods[0]->start_time;
    $end = $time_periods[0]->end_time;
    // Check rest of the intervals and find the intersection
    $total = count($time_periods);
    for ($i = 1; $i < $total; $i++){
       // If no intersection exists
      if ($time_periods[$i]->start_time > $end || $time_periods[$i]->end_time < $start){
        return [];
      }else{
        // Else update the intersection
        $start = max($start, $time_periods[$i]->start_time);
        $end = min($end, $time_periods[$i]->end_time);
      }
    }
    return new TimePeriod(['start_time' => $start, 'end_time' => $end]);
	}

	public function add_blocked_period(BlockedPeriod $blocked_period){
		$this->blocked_time_periods[] = $blocked_period;
	}

	public function add_booked_period(BookedPeriod $booked_period, $block_full_capacity = false){
		if($block_full_capacity){
			$max_capacity_booked_period = clone $booked_period;
			$max_capacity_booked_period->total_attendees = $this->max_capacity;
			$this->booked_time_periods[] = $max_capacity_booked_period;
		}else{
			$this->booked_time_periods[] = $booked_period;
		}
	}

	public function add_available_periods(array $work_period_groups){
		$available_periods = [];
		foreach($work_period_groups as $group_work_periods){
			// loop through groups, if multiple group - add them by comparing them against each other
			$available_periods = ($available_periods) ? $this->compare_periods($available_periods, $group_work_periods) : $group_work_periods;
		}
		$this->work_time_periods = $available_periods;
	}


	private function compare_periods(array $available_periods, array $periods_to_intersect): array{
    $intersects = [];
    foreach($available_periods as $available_period){
      foreach($periods_to_intersect as $intersect_period){
        $intersect = $this->find_intersection([$available_period, $intersect_period]);
        if($intersect) $intersects[] = $intersect;
      }
    }
    return $intersects;
	}

	public function intersect_time_period(TimePeriod $time_period){
	}


	// TODO instead of trying to find overlaps, just add a period and then overlaps can be skipped using array_unique for available minutes
	public function add_time_period(TimePeriod $time_period_to_add){
		$overlapped_time_periods = [];
		$not_overlapped_time_periods = [];
		// search for all periods that overlap, to later merge them into one
		foreach($this->work_time_periods as $time_period){
			if($time_period->check_if_overlaps($time_period_to_add)){
				$overlapped_time_periods[] = $time_period;
			}else{
				$not_overlapped_time_periods[] = $time_period;
			}
		}
		if($overlapped_time_periods){
			// if overlapping periods were found - find a unified range between them and create a new list of available time
			// periods which should include those that were not overlapped
			$this->work_time_periods = $not_overlapped_time_periods;
			$merged_time_period = TimePeriod::get_unified_period_from_overlapped_periods($merged_time_period);
			$this->work_time_periods[] = $merged_time_period;
		}else{
			// nothing was overlapped, simply add this time period
			$this->work_time_periods[] = $time_period_to_add;
		}
	}

	public function generate_resource_id(): string{
		return $this->agent_id.'_'.$this->service_id.'_'.$this->location_id;
	}

	public static function create_from_connection(\OsConnectorModel $connection): BookingResource{
		$booking_request = new BookingResource([ 'agent_id' => $connection->agent_id,
																'service_id' => $connection->service_id,
																'location_id' => $connection->location_id]);
		$booking_request->retrieve_service_data();
		return $booking_request;
	}


	public static function allowed_props(): array{
		return ['agent_id',
						'service_id',
						'location_id',
						'date',
						'start_time',
						'end_time'];
	}
}