<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class BookingSlot{
	public string $start_date;
	public int $start_time;
	public int $min_capacity = 1;
	public int $max_capacity = 1;
	public int $min_capacity_to_be_blocked = 1;
	public int $booked_capacity = 0;
	public int $price;

	function __construct($args = []){
		$allowed_props = self::allowed_props();
		foreach($args as $key => $arg){
			if(in_array($key, $allowed_props)) $this->$key = $arg;
		}
	}

	/**
	 * @param \LatePoint\Misc\BookingSlot[] $booking_slots
	 * @return int
	 */
	public static function find_minimum_gap_between_slots(array $booking_slots): int{
		$minimum_slot_gap = 0;
		// calculate minimum gap between slots
		if(count($booking_slots) > 1){
			$prev_start_time = $booking_slots[0]->start_time;
			$intervals = [];
			foreach($booking_slots as $booking_slot){
				if($prev_start_time){
					$gap = $booking_slot->start_time - $prev_start_time;
					if($gap) $intervals[] = $gap;
				}
				$prev_start_time = $booking_slot->start_time;
			}
			if($intervals) $minimum_slot_gap = min($intervals);
		}
		return $minimum_slot_gap;
	}

	public function can_accomodate($total_attendees){
		if($this->booked_capacity >= $this->min_capacity_to_be_blocked){
			return false;
		}else{
			return $this->available_capacity() >= $total_attendees;
		}
	}

	public function available_capacity(){
		return $this->max_capacity - $this->booked_capacity;
	}

	public static function allowed_props(): array{
		return ['start_time',
						'start_date',
						'min_capacity',
						'max_capacity',
						'min_capacity_to_be_blocked',
						'booked_capacity',
						'available_capacity',
						'price'];
	}
}