<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

class OsResourceHelper {

	/**
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @param DateTime $date_from
	 * @param DateTime|null $date_to
	 * @param array $settings
	 *
	 * @return array
	 *
	 *
	 * Returns an array of work periods, grouped by days that were requested in the filter.
	 * example: ['2022-02-24' => [], '2022-02-25' => [], ...]
	 *
	 *  | Agent   | Service | Location | Date          |       Hours       | Weight
	 *  | -----------------------------------------------------------------------------
	 *  | 1       | 1       | 1        | 2022-01-15    |   7:00  - 18:00   |  7
	 *  | 1       | 0       | 1        | 2022-01-15    |   8:00  - 14:00   |  6
	 *  | 1       | 0       | 0        | 2022-01-15    |   8:00  - 14:00   |  5
	 *  | 0       | 0       | 1        | 2022-01-15    |   11:00 - 12:00   |  5
	 *  | 0       | 0       | 0        | 2022-01-15    |   11:00 - 12:00   |  4
	 *  | 1       | 0       | 1        | NULL          |   0:00  - 0:00    |  2
	 *  | 1       | 0       | 0        | NULL          |   9:00  - 12:00   |  1
	 *  | 0       | 0       | 0        | NULL          |   11:00 - 17:00   |  0
	 *
	 */
	public static function get_resources_grouped_by_day( \LatePoint\Misc\BookingRequest $booking_request, DateTime $date_from, DateTime $date_to = null, array $settings = [] ): array {
		$defaults = [
			'now'                   => OsTimeHelper::now_datetime_object(),
			'exclude_booking_ids'   => [],
			'timeshift_minutes'     => 0,
			'accessed_from_backend' => false,
			'consider_cart_items' => false
		];
		$settings = array_merge( $defaults, $settings );

		$connections = OsConnectorHelper::get_connections_that_satisfy_booking_request( $booking_request, $settings['accessed_from_backend'] );
		$resources   = [];
		foreach ( $connections as $connection ) {
			$resources[] = \LatePoint\Misc\BookingResource::create_from_connection( $connection );
		}
		if ( empty( $date_to ) ) {
			$date_to = clone $date_from;
		}
		if ( $settings['timeshift_minutes'] < 0 ) {
			$date_to = clone $date_to;
			$date_to->modify( '+1 day' );
		} elseif ( $settings['timeshift_minutes'] > 0 ) {
			$date_from = clone $date_from;
			$date_from->modify( '-1 day' );
		}
		$filter          = new \LatePoint\Misc\Filter( [
			'connections' => $connections,
			'date_from'   => $date_from->format( 'Y-m-d' ),
			'date_to'     => $date_to->format( 'Y-m-d' )
		] );
		$weekday_periods = OsWorkPeriodsHelper::get_work_periods_grouped_by_weekday( $filter );
		$daily_resources = [];
		// loop through the requested days and fill in array with work periods that are applicable to that day

		// Booked periods
		$booked_periods_filter            = new \LatePoint\Misc\Filter();
		$booked_periods_filter->date_from = $date_from->format( 'Y-m-d' );
		$booked_periods_filter->date_to   = $date_to->format( 'Y-m-d' );
		$booked_periods_filter->statuses  = OsBookingHelper::get_timeslot_blocking_statuses();
		if ( $settings['exclude_booking_ids'] ) {
			$booked_periods_filter->exclude_booking_ids = $settings['exclude_booking_ids'];
		}
		if ( $settings['consider_cart_items'] ) {
			$booked_periods_filter->consider_cart_items = true;
		}

		$booked_periods  = OsBookingHelper::get_booked_periods_grouped_by_day( $booked_periods_filter );
		$blocked_periods = OsBookingHelper::get_blocked_periods_grouped_by_day( $filter, $settings['accessed_from_backend'] );


		for ( $day_date = clone $date_from; $day_date->format( 'Y-m-d' ) <= $date_to->format( 'Y-m-d' ); $day_date->modify( '+1 day' ) ) {
			$daily_resources[ $day_date->format( 'Y-m-d' ) ] = [];
			// fill every day with available resources
			foreach ( $resources as $resource ) {
				$last_added_period             = false;
				$available_work_periods_groups = [];
				$group_index                   = 0;
				// loop through available work periods for this week day
				foreach ( $weekday_periods[ $day_date->format( 'N' ) ] as $period ) {
					if ( $period->custom_date && ( $period->custom_date != $day_date->format( 'Y-m-d' ) ) ) {
						continue;
					} // if this period has a custom date set and if it doesn't match the one we search for - skip it
					// only add this work period if agent/location/service match or not set
					if ( ( ! $period->agent_id || $period->agent_id == $resource->agent_id ) && ( ! $period->service_id || $period->service_id == $resource->service_id ) && ( ! $period->location_id || $period->location_id == $resource->location_id ) ) {
						if ( $last_added_period ) {
							// if weight of previously added period is different - break, no need to add any other periods
							if ( $last_added_period->weight != $period->weight ) {
								break;
							}
							if ( ( $last_added_period->service_id != $period->service_id ) || ( $last_added_period->agent_id != $period->agent_id ) || ( $last_added_period->location_id != $period->location_id ) ) {
								// same weight NOT same exact properties, create a new group of work periods, which will later be used to find intersections
								$group_index ++;
							}
							if ( ( $period->start_time == 0 ) && ( $period->end_time == 0 ) ) {
								$available_work_periods_groups = [];
								break;
							}
							$available_work_periods_groups[ $group_index ][] = \LatePoint\Misc\TimePeriod::create_from_work_period( $period );
							$last_added_period                               = $period;
						} else {
							if ( ( $period->start_time == 0 ) && ( $period->end_time == 0 ) ) {
								$available_work_periods_groups = [];
								break;
							}
							$available_work_periods_groups[ $group_index ][] = \LatePoint\Misc\TimePeriod::create_from_work_period( $period );
							$last_added_period                               = $period;
						}
					}
				}

				$day_resource       = clone $resource;
				$day_resource->date = $day_date->format( 'Y-m-d' );
				$day_resource->add_available_periods( $available_work_periods_groups );

				/// -----------------------------
				/// LOGIC FOR CALCULATING "BOOKED" PERIODS
				/// -----------------------------
				foreach ( $booked_periods[ $day_date->format( 'Y-m-d' ) ] as $booked_period ) {

					if ( ( $day_resource->service_id == $booked_period->service_id ) && ( $day_resource->location_id == $booked_period->location_id ) && ( $day_resource->agent_id == $booked_period->agent_id ) ) {
						// same service, agent and location is already booked, block no matter what
						$day_resource->add_booked_period( $booked_period );
						continue;
					}

					if ( $day_resource->agent_id == $booked_period->agent_id ) {
						// Same agent
						if ( ( $day_resource->location_id != $booked_period->location_id ) && OsSettingsHelper::is_on( 'one_location_at_time' ) ) {
							// different location is booked, but the same agent, block if "Agents can only be present in one location at a time" is ON
							$day_resource->add_booked_period( $booked_period, true );
							continue;
						}
						if ( ( $day_resource->service_id != $booked_period->service_id ) && ( $day_resource->location_id == $booked_period->location_id ) && ! OsSettingsHelper::is_on( 'multiple_services_at_time' ) ) {
							// Different service, but same location, block, if "One agent can perform different services simultaneously" is OFF
							$day_resource->add_booked_period( $booked_period, true );
						}
					} else {
						// Different agent
						if ( ( $day_resource->location_id == $booked_period->location_id ) && OsSettingsHelper::is_on( 'one_agent_at_location' ) ) {
							// same location, so it doesn't matter who's the agent, block, because location can only be used by a single agent at a time
							// set to max capacity, to block slot even if it still has room
							$day_resource->add_booked_period( $booked_period, true );
						}
					}
				}

				/// -----------------------------
				/// LOGIC FOR CALCULATING "BLOCKED" PERIODS
				/// -----------------------------
				foreach ( $blocked_periods[ $day_date->format( 'Y-m-d' ) ] as $blocked_period ) {
					if ( ! $blocked_period->agent_id || $day_resource->agent_id == $blocked_period->agent_id ) {
						$day_resource->add_blocked_period( $blocked_period );
					}
				}

				$day_resource->build_bookable_slots( $booking_request, $day_resource->get_timeblock_interval() );
				$daily_resources[ $day_date->format( 'Y-m-d' ) ][] = $day_resource;
			}
		}
		$daily_resources = apply_filters( 'latepoint_get_resources_grouped_by_day', $daily_resources, $booking_request, $date_from, $date_to, $settings );

		return $daily_resources;
	}


	/**
	 * @param \LatePoint\Misc\BookingResource[] $resources
	 *
	 * @return array
	 */
	public static function get_ordered_booking_slots_from_resources( array $resources ): array {
		$booking_slots = [];
		foreach ( $resources as $resource ) {
			$booking_slots = array_merge( $booking_slots, $resource->slots );
		}

		usort( $booking_slots, function ( $first, $second ) {
			return $first->start_time <=> $second->start_time;
		} );

		if ( count( $resources ) > 1 ) {
			$squashed_booking_slots = [];
			$last_added_slot        = false;
			foreach ( $booking_slots as $booking_slot ) {
				if ( $last_added_slot && ( $last_added_slot->start_time == $booking_slot->start_time ) ) {
					if ( $last_added_slot->available_capacity() < $booking_slot->available_capacity() ) {
						$squashed_booking_slots[ count( $squashed_booking_slots ) - 1 ] = $booking_slot;
						$last_added_slot                                                = $booking_slot;
					}
				} else {
					$squashed_booking_slots[] = $booking_slot;
					$last_added_slot          = $booking_slot;
				}
			}
			$booking_slots = $squashed_booking_slots;
		}

		return $booking_slots;
	}

	/**
	 * @param \LatePoint\Misc\BookingResource[]
	 *
	 * @return \LatePoint\Misc\TimePeriod
	 */
	public static function get_work_boundaries_for_resources( $resources ): \LatePoint\Misc\TimePeriod {
		$times = [];
		foreach ( $resources as $resource ) {
			foreach ( $resource->work_time_periods as $work_time_period ) {
				$times[] = $work_time_period->start_time;
				$times[] = $work_time_period->end_time;
			}
			foreach ( $resource->booked_time_periods as $booked_time_period ) {
				if ( $booked_time_period->start_date == $booked_time_period->end_date ) {
					// same day event
					$times[] = $booked_time_period->start_time;
					$times[] = $booked_time_period->end_time;
				} else {
					// event spans mutiple days, expand boundaries to a full day
					$times[] = 0;
					$times[] = 24 * 60;
				}
			}
		}
		if ( $times ) {
			$boundary_time_period = new \LatePoint\Misc\TimePeriod( [
				'start_time' => min( $times ),
				'end_time'   => max( $times )
			] );
		} else {
			$boundary_time_period = new \LatePoint\Misc\TimePeriod( [ 'start_time' => 0, 'end_time' => 0 ] );
		}

		return $boundary_time_period;
	}

	/**
	 * @param array $groups_of_resources
	 *
	 * @return \LatePoint\Misc\TimePeriod
	 */
	public static function get_work_boundaries_for_groups_of_resources( array $groups_of_resources ): \LatePoint\Misc\TimePeriod {
		$times = [];
		foreach ( $groups_of_resources as $resources ) {
			$time_period = self::get_work_boundaries_for_resources( $resources );
			if ( $time_period->start_time || $time_period->end_time ) {
				$times[] = $time_period->start_time;
				$times[] = $time_period->end_time;
			}
		}
		if ( $times ) {
			$boundary_time_period = new \LatePoint\Misc\TimePeriod( [
				'start_time' => min( $times ),
				'end_time'   => max( $times )
			] );
		} else {
			$boundary_time_period = new \LatePoint\Misc\TimePeriod( [ 'start_time' => 0, 'end_time' => 0 ] );
		}

		return $boundary_time_period;
	}


}