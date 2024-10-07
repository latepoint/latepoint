<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */


class OsCalendarHelper {

	/**
	 * Get list of statuses which should not appear on calendar
	 *
	 * @return array
	 */
	public static function get_booking_statuses_hidden_from_calendar(): array {
		$statuses = explode( ',', OsSettingsHelper::get_settings_value( 'calendar_hidden_statuses', '' ) );

		/**
		 * Get list of statuses which bookings should not appear on calendar
		 *
		 * @param {array} $statuses array of status codes that will be hidden from calendar
		 * @returns {array} The filtered array of status codes
		 *
		 * @since 4.7.0
		 * @hook latepoint_get_booking_statuses_hidden_from_calendar
		 *
		 */
		return apply_filters( 'latepoint_get_booking_statuses_hidden_from_calendar', $statuses );
	}


	/**
	 * Returns an array of booking status codes to be displayed on calendar
	 *
	 * @return {array} The array of statuses
	 */
	public static function get_booking_statuses_to_display_on_calendar(): array {
		$hidden_statuses   = self::get_booking_statuses_hidden_from_calendar();
		$all_statuses      = OsBookingHelper::get_statuses_list();
		$eligible_statuses = [];
		foreach ( $all_statuses as $status_code => $status_label ) {
			if ( ! in_array( $status_code, $hidden_statuses ) ) {
				$eligible_statuses[] = $status_code;
			}
		}

		/**
		 * Returns an array of booking status codes to be displayed on calendar
		 *
		 * @param {array} array of statuses
		 *
		 * @returns {array} The array of statuses
		 *
		 * @since 4.7.0
		 * @hook latepoint_get_booking_statuses_to_display_on_calendar
		 *
		 */
		return apply_filters( 'latepoint_get_booking_statuses_to_display_on_calendar', $eligible_statuses );
	}

	public static function is_external_calendar_enabled( string $external_calendar_code ): bool {
		return OsSettingsHelper::is_on( 'enable_' . $external_calendar_code );
	}

	public static function get_list_of_external_calendars( $enabled_only = false ) {
		$external_calendars = [];

		/**
		 * Returns an array of external calendars
		 *
		 * @param {array} array of calendars
		 * @param {bool} filter to return only calendars that are enabled
		 *
		 * @returns {array} The array of external calendars
		 *
		 * @since 4.7.0
		 * @hook latepoint_list_of_external_calendars
		 *
		 */
		return apply_filters( 'latepoint_list_of_external_calendars', $external_calendars, $enabled_only );
	}


	/**
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @param DateTime $target_date
	 * @param array $settings
	 *
	 * @return void
	 */
	public static function generate_calendar_for_datepicker_step( \LatePoint\Misc\BookingRequest $booking_request, DateTime $target_date, array $settings = [] ) {
		$defaults = [
			'exclude_booking_ids'         => [],
			'number_of_months_to_preload' => 1,
			'timezone_name'               => false,
			'layout'                      => 'classic',
			'highlight_target_date'       => false,
			'consider_cart_items'         => false,
		];

		$settings = OsUtilHelper::merge_default_atts( $defaults, $settings );

		$weekdays   = OsBookingHelper::get_weekdays_arr();
		$today_date = new OsWpDateTime( 'today' );


		?>
        <div class="os-current-month-label-w calendar-mobile-controls">
            <div class="os-current-month-label">
                <div class="current-month">
					<?php if ( $settings['highlight_target_date'] ) {
						echo esc_html( OsTimeHelper::get_nice_date_with_optional_year( $target_date->format( 'Y-m-d' ), false ) );
					} else {
						echo esc_html( OsUtilHelper::get_month_name_by_number( $target_date->format( 'n' ) ) );
					} ?>
                </div>
                <div class="current-year"><?php echo esc_html( $target_date->format( 'Y' ) ); ?></div>
            </div>
            <div class="os-month-control-buttons-w">
                <button type="button" class="os-month-prev-btn disabled" data-route="<?php echo esc_attr( OsRouterHelper::build_route_name( 'steps', 'load_datepicker_month' ) ); ?>">
                    <i class="latepoint-icon latepoint-icon-arrow-left"></i></button>
				<?php if ( $settings['layout'] == 'horizontal' ) {
					echo '<button class="latepoint-btn latepoint-btn-outline os-month-today-btn" data-year="' . esc_attr( $today_date->format( 'Y' ) ) . '" data-month="' . esc_attr( $today_date->format( 'n' ) ) . '" data-date="' . esc_attr( $today_date->format( 'Y-m-d' ) ) . '">' . esc_html__( 'Today', 'latepoint' ) . '</button>';
				} ?>
                <button type="button" class="os-month-next-btn" data-route="<?php echo esc_attr( OsRouterHelper::build_route_name( 'steps', 'load_datepicker_month' ) ); ?>">
                    <i class="latepoint-icon latepoint-icon-arrow-right"></i></button>
            </div>
        </div>
		<?php if ( $settings['layout'] == 'classic' ) { ?>
            <div class="os-weekdays">
				<?php
				$start_of_week = OsSettingsHelper::get_start_of_week();

				// Output the divs for each weekday
				for ( $i = $start_of_week - 1; $i < $start_of_week - 1 + 7; $i ++ ) {
					// Calculate the index within the range of 0-6
					$index = $i % 7;

					// Output the div for the current weekday
					echo '<div class="weekday weekday-' . esc_attr( $index + 1 ) . '">' . esc_html( $weekdays[ $index ] ) . '</div>';
				}
				?>
            </div>
		<?php } ?>
        <div class="os-months">
		<?php
		$month_settings = [
			'active'                => true,
			'timezone_name'         => $settings['timezone_name'],
			'highlight_target_date' => $settings['highlight_target_date'],
			'exclude_booking_ids'   => $settings['exclude_booking_ids'],
			'consider_cart_items'   => $settings['consider_cart_items']
		];


		// if it's not from admin - blackout dates that are not available to select due to date restrictions in settings
        $month_settings['earliest_possible_booking'] = OsSettingsHelper::get_settings_value( 'earliest_possible_booking', false );
        $month_settings['latest_possible_booking']   = OsSettingsHelper::get_settings_value( 'latest_possible_booking', false );

		OsCalendarHelper::generate_single_month( $booking_request, $target_date, $month_settings );
		for ( $i = 1; $i <= $settings['number_of_months_to_preload']; $i ++ ) {
			$next_month_target_date = clone $target_date;
			$next_month_target_date->modify( 'first day of next month' );
			$month_settings['active']                = false;
			$month_settings['highlight_target_date'] = false;
			OsCalendarHelper::generate_single_month( $booking_request, $next_month_target_date, $month_settings );
		}
		?>
        </div><?php
	}

	public static function generate_single_month( \LatePoint\Misc\BookingRequest $booking_request, DateTime $target_date, array $settings = [] ) {
		$defaults = [
			'accessed_from_backend'        => false,
			'active'                       => false,
			'layout'                       => 'classic',
			'highlight_target_date'        => false,
			'timezone_name'                => false,
			'earliest_possible_booking'    => false,
			'latest_possible_booking'      => false,
			'exclude_booking_ids'          => [],
			'consider_cart_items'          => false,
			'hide_slot_availability_count' => OsStepsHelper::hide_slot_availability_count()
		];
		$settings = OsUtilHelper::merge_default_atts( $defaults, $settings );

		if ( $settings['timezone_name'] && $settings['timezone_name'] != OsTimeHelper::get_wp_timezone_name() ) {
			$timeshift_minutes = OsTimeHelper::get_timezone_shift_in_minutes( $settings['timezone_name'] );
		} else {
			$timeshift_minutes = 0;
		}

		// set service to the first available if not set
		// IMPORTANT, we have to have service in the booking request, otherwise we can't know duration and intervals
		$service = new OsServiceModel();
		$service = $service->where( [ 'id' => $booking_request->service_id ] )->set_limit( 1 )->get_results_as_models();
		if ( $service ) {
			if ( ! $booking_request->duration ) {
				$booking_request->duration = $service->duration;
			}
			$selectable_time_interval = $service->get_timeblock_interval();
		} else {
			echo '<div class="latepoint-message latepoint-message-error">' . esc_html__( 'In order to generate the calendar, a service must be selected.', 'latepoint' ) . '</div>';

			return;
		}


		# Get bounds for a month of a targetted day
		$calendar_start = clone $target_date;
		$calendar_start->modify( 'first day of this month' );
		$calendar_end = clone $target_date;
		$calendar_end->modify( 'last day of this month' );


		// if it's a classic layout - it means we need to load some days from previous and next month, to fill in blank spaces on the grid
		if ( $settings['layout'] == 'classic' ) {
			$weekday_for_first_day_of_month = intval( $calendar_start->format( 'N' ) );
			$weekday_for_last_day_of_month  = intval( $calendar_end->format( 'N' ) );

			$week_starts_on = OsSettingsHelper::get_start_of_week();
			$week_ends_on   = $week_starts_on > 1 ? $week_starts_on - 1 : 7;

			if ( $weekday_for_first_day_of_month != $week_starts_on ) {
				$days_to_subtract = ( $weekday_for_first_day_of_month - $week_starts_on + 7 ) % 7;
				$calendar_start->modify( '-' . $days_to_subtract . ' days' );
			}

			if ( $weekday_for_last_day_of_month != $week_ends_on ) {
				$days_to_add = ( $weekday_for_last_day_of_month > $week_ends_on ) ? abs( 7 - $weekday_for_last_day_of_month + $week_ends_on ) : ( $week_ends_on - $weekday_for_last_day_of_month );
				$calendar_end->modify( '+' . $days_to_add . ' days' );
			}
		}

		// apply timeshift if needed
		$now_datetime = OsTimeHelper::now_datetime_object();

		// figure out when the earliest and latest bookings can be placed
		$earliest_possible_booking = ( $settings['earliest_possible_booking'] ) ? new OsWpDateTime( $settings['earliest_possible_booking'] ) : clone $now_datetime;
		$latest_possible_booking   = ( $settings['latest_possible_booking'] ) ? new OsWpDateTime( $settings['latest_possible_booking'] ) : clone $calendar_end;
		// make sure they are set correctly
		if ( ! $earliest_possible_booking ) {
			$earliest_possible_booking = clone $now_datetime;
		}
		if ( ! $latest_possible_booking ) {
			$latest_possible_booking = clone $calendar_end;
		}

		$date_range_start = ( $calendar_start->format( 'Y-m-d' ) > $earliest_possible_booking->format( 'Y-m-d' ) ) ? $calendar_start : $earliest_possible_booking;
		$date_range_end   = ( $calendar_end->format( 'Y-m-d' ) < $latest_possible_booking->format( 'Y-m-d' ) ) ? $calendar_end : $latest_possible_booking;

		// make sure date range is within the requested calendar range
		if ( ( $date_range_start->format( 'Y-m-d' ) >= $calendar_start->format( 'Y-m-d' ) )
		     && ( $date_range_end->format( 'Y-m-d' ) <= $calendar_end->format( 'Y-m-d' ) )
		     && ( $date_range_start->format( 'Y-m-d' ) <= $date_range_end->format( 'Y-m-d' ) ) ) {
			$daily_resources = OsResourceHelper::get_resources_grouped_by_day( $booking_request, $date_range_start, $date_range_end, [
				'timeshift_minutes'     => $timeshift_minutes,
				'accessed_from_backend' => $settings['accessed_from_backend'],
				'exclude_booking_ids'   => $settings['exclude_booking_ids'],
				'consider_cart_items'   => $settings['consider_cart_items']
			] );
		} else {
			$daily_resources = [];
		}

		$active_class           = $settings['active'] ? 'active' : '';
		$hide_single_slot_class = OsStepsHelper::hide_timepicker_when_one_slot_available() ? 'hide-if-single-slot' : '';
		echo '<div class="os-monthly-calendar-days-w ' . esc_attr( $hide_single_slot_class . ' ' . $active_class ) . '" data-calendar-layout="' . esc_attr( $settings['layout'] ) . '" data-calendar-year="' . esc_attr( $target_date->format( 'Y' ) ) . '" data-calendar-month="' . esc_attr( $target_date->format( 'n' ) ) . '" data-calendar-month-label="' . esc_attr( OsUtilHelper::get_month_name_by_number( $target_date->format( 'n' ) ) ) . '"><div class="os-monthly-calendar-days">';
		// DAYS LOOP START
		for ( $day_date = clone $calendar_start; $day_date <= $calendar_end; $day_date->modify( '+1 day' ) ) {
			if ( ! isset( $daily_resources[ $day_date->format( 'Y-m-d' ) ] ) ) {
				$daily_resources[ $day_date->format( 'Y-m-d' ) ] = [];
			}

			$is_today              = ( $day_date->format( 'Y-m-d' ) == $now_datetime->format( 'Y-m-d' ) );
			$is_day_in_past        = ( $day_date->format( 'Y-m-d' ) < $now_datetime->format( 'Y-m-d' ) );
			$is_target_month       = ( $day_date->format( 'm' ) == $target_date->format( 'm' ) );
			$is_next_month         = ( $day_date->format( 'm' ) > $target_date->format( 'm' ) );
			$is_prev_month         = ( $day_date->format( 'm' ) < $target_date->format( 'm' ) );
			$not_in_allowed_period = false;

			if ( $day_date->format( 'Y-m-d' ) < $earliest_possible_booking->format( 'Y-m-d' ) ) {
				$not_in_allowed_period = true;
			}
			if ( $day_date->format( 'Y-m-d' ) > $latest_possible_booking->format( 'Y-m-d' ) ) {
				$not_in_allowed_period = true;
			}

			$work_minutes = [];

			foreach ( $daily_resources[ $day_date->format( 'Y-m-d' ) ] as $resource ) {
				if ( $is_day_in_past && $not_in_allowed_period ) {
					continue;
				}
				$work_minutes = array_merge( $work_minutes, $resource->work_minutes );
			}
			$work_minutes = array_unique( $work_minutes, SORT_NUMERIC );
			sort( $work_minutes, SORT_NUMERIC );


			$work_boundaries    = OsResourceHelper::get_work_boundaries_for_resources( $daily_resources[ $day_date->format( 'Y-m-d' ) ] );
			$total_work_minutes = $work_boundaries->end_time - $work_boundaries->start_time;

			$booking_slots = OsResourceHelper::get_ordered_booking_slots_from_resources( $daily_resources[ $day_date->format( 'Y-m-d' ) ] );

			$bookable_minutes = [];
			foreach ( $booking_slots as $booking_slot ) {
				if ( $booking_slot->can_accomodate( $booking_request->total_attendees ) ) {
					$bookable_minutes[ $booking_slot->start_time ] = isset( $bookable_minutes[ $booking_slot->start_time ] ) ? max( $booking_slot->available_capacity(), $bookable_minutes[ $booking_slot->start_time ] ) : $booking_slot->available_capacity();
				}
			}
			ksort( $bookable_minutes );
			$bookable_minutes_with_capacity_data = '';
			// this is a group service
			if ( $service->is_group_service() && ! $settings['hide_slot_availability_count'] ) {
				foreach ( $bookable_minutes as $minute => $available_capacity ) {
					$bookable_minutes_with_capacity_data .= $minute . ':' . $available_capacity . ',';
				}
			} else {
				foreach ( $bookable_minutes as $minute => $available_capacity ) {
					$bookable_minutes_with_capacity_data .= $minute . ',';
				}
			}
			$bookable_minutes_with_capacity_data = rtrim( $bookable_minutes_with_capacity_data, ',' );


			$bookable_slots_count = count( $bookable_minutes );
			// TODO use work minutes instead to calculate minimum gap
			$minimum_slot_gap = \LatePoint\Misc\BookingSlot::find_minimum_gap_between_slots( $booking_slots );
			$day_class = 'os-day os-day-current week-day-' . strtolower( $day_date->format( 'N' ) );
            $tabbable = true;
			if ( empty( $bookable_minutes ) ) {
				$day_class .= ' os-not-available';
                $tabbable = false;
			}
			if ( $is_today ) {
				$day_class .= ' os-today';
			}
			if ( $is_day_in_past ) {
				$day_class .= ' os-day-passed';
                $tabbable = false;
			}
			if ( $is_target_month ) {
				$day_class .= ' os-month-current';
			}
			if ( $is_next_month ) {
				$day_class .= ' os-month-next';
			}
			if ( $is_prev_month ) {
				$day_class .= ' os-month-prev';
			}
			if ( $not_in_allowed_period ) {
				$day_class .= ' os-not-in-allowed-period';
                $tabbable = false;
			}
			if ( count( $bookable_minutes ) == 1 && OsStepsHelper::hide_timepicker_when_one_slot_available() ) {
				$day_class .= ' os-one-slot-only';
			}
			if ( ( $day_date->format( 'Y-m-d' ) == $target_date->format( 'Y-m-d' ) ) && $settings['highlight_target_date'] ) {
				$day_class .= ' selected';
			}
			?>

            <div <?php if($tabbable) echo 'tabindex="0"'; ?> role="button" class="<?php echo esc_attr( $day_class ); ?>"
                 data-date="<?php echo esc_attr( $day_date->format( 'Y-m-d' ) ); ?>"
                 data-nice-date="<?php echo esc_attr( OsTimeHelper::get_nice_date_with_optional_year( $day_date->format( 'Y-m-d' ), false ) ); ?>"
                 data-service-duration="<?php echo esc_attr( $booking_request->duration ); ?>"
                 data-total-work-minutes="<?php echo esc_attr( $total_work_minutes ); ?>"
                 data-work-start-time="<?php echo esc_attr( $work_boundaries->start_time ); ?>"
                 data-work-end-time="<?php echo esc_attr( $work_boundaries->end_time ); ?>"
                 data-bookable-minutes="<?php echo esc_attr( $bookable_minutes_with_capacity_data ); ?>"
                 data-work-minutes="<?php echo esc_attr( implode( ',', $work_minutes ) ); ?>"
                 data-interval="<?php echo esc_attr( $selectable_time_interval ); ?>">
				<?php if ( $settings['layout'] == 'horizontal' ) { ?>
                    <div
                            class="os-day-weekday"><?php echo esc_html( OsBookingHelper::get_weekday_name_by_number( $day_date->format( 'N' ) ) ); ?></div><?php } ?>
                <div class="os-day-box">
					<?php
					if ( $bookable_slots_count && ! $settings['hide_slot_availability_count'] ) {
                        // translators: %d is the number of slots available
                        echo '<div class="os-available-slots-tooltip">' . esc_html( sprintf( __( '%d Available', 'latepoint' ), $bookable_slots_count ) ) . '</div>';
					} ?>
                    <div class="os-day-number"><?php echo esc_html( $day_date->format( 'j' ) ); ?></div>
					<?php if ( ! $is_day_in_past && ! $not_in_allowed_period ) { ?>
                        <div class="os-day-status">
							<?php
							if ( $total_work_minutes > 0 && $bookable_slots_count ) {
								$available_blocks_count      = 0;
								$not_available_started_count = 0;
								$duration                    = $booking_request->duration;
								$end_time                    = $work_boundaries->end_time - $duration;
								$processed_count             = 0;
								$last_available_slot_time    = false;
								$bookable_ranges             = [];
								$loop_availability_status    = false;
								for ( $i = 0; $i < count( $booking_slots ); $i ++ ) {
									if ( $booking_slots[ $i ]->can_accomodate( $booking_request->total_attendees ) ) {
										// AVAILABLE SLOT
										if ( $loop_availability_status && $i > 0 && ( ( $booking_slots[ $i ]->start_time - $booking_slots[ $i - 1 ]->start_time ) > $minimum_slot_gap ) ) {
											// big gap between previous slot and this slot
											$bookable_ranges[] = $booking_slots[ $i - 1 ]->start_time + $minimum_slot_gap;
											$bookable_ranges[] = $booking_slots[ $i ]->start_time;
										}
										if ( ! $loop_availability_status ) {
											$bookable_ranges[] = $booking_slots[ $i ]->start_time;
										}
										$last_available_slot_time = $booking_slots[ $i ]->start_time;
										$loop_availability_status = true;
									} else {
										// NOT AVAILABLE
										// a different resource but with the same start time, so that if its available (checked in next loop iteration) - we don't block this slot
										if ( isset( $booking_slots[ $i + 1 ] ) && $booking_slots[ $i + 1 ]->start_time == $booking_slots[ $i ]->start_time ) {
											continue;
										}
										// check if last available slot had the same start time as current one, if so - we don't block this slot and move to the next one
										if ( $last_available_slot_time == $booking_slots[ $i ]->start_time && isset( $booking_slots[ $i - 1 ] ) && $booking_slots[ $i - 1 ]->start_time == $booking_slots[ $i ]->start_time ) {
											continue;
										}
										// if last available slot exists and previous slot was also available
										if ( $last_available_slot_time && $loop_availability_status ) {
											$bookable_ranges[] = $last_available_slot_time + $minimum_slot_gap;
										}
										$loop_availability_status = false;
									}
								}
								if ( $bookable_ranges ) {
									for ( $i = 0; $i < count( $bookable_ranges ); $i += 2 ) {
										$left  = ( $bookable_ranges[ $i ] - $work_boundaries->start_time ) / $total_work_minutes * 100;
										$width = isset( $bookable_ranges[ $i + 1 ] ) ? ( ( $bookable_ranges[ $i + 1 ] - $bookable_ranges[ $i ] ) / $total_work_minutes * 100 ) : ( ( $work_boundaries->end_time - $bookable_ranges[ $i ] ) / $total_work_minutes * 100 );
										echo '<div class="day-available" style="left:' . esc_attr( $left ) . '%;width:' . esc_attr( $width ) . '%;"></div>';
									}
								}
							}
							?>
                        </div>
					<?php } ?>
                </div>
            </div>

			<?php

			// DAYS LOOP END
		}
		echo '</div></div>';
	}

	// Used on holiday/custom schedule generator lightbox
	public static function generate_monthly_calendar_days_only( $target_date_string = 'today', $highlight_target_date = false ) {
		$target_date    = new OsWpDateTime( $target_date_string );
		$calendar_start = clone $target_date;
		$calendar_start->modify( 'first day of this month' );
		$calendar_end = clone $target_date;
		$calendar_end->modify( 'last day of this month' );

		$weekday_for_first_day_of_month = $calendar_start->format( 'N' ) - 1;
		$weekday_for_last_day_of_month  = $calendar_end->format( 'N' ) - 1;


		if ( $weekday_for_first_day_of_month > 0 ) {
			$calendar_start->modify( '-' . $weekday_for_first_day_of_month . ' days' );
		}

		if ( $weekday_for_last_day_of_month < 6 ) {
			$days_to_add = 6 - $weekday_for_last_day_of_month;
			$calendar_end->modify( '+' . $days_to_add . ' days' );
		}

		echo '<div class="os-monthly-calendar-days-w" data-calendar-year="' . esc_attr( $target_date->format( 'Y' ) ) . '" data-calendar-month="' . esc_attr( $target_date->format( 'n' ) ) . '" data-calendar-month-label="' . esc_attr( OsUtilHelper::get_month_name_by_number( $target_date->format( 'n' ) ) ) . '">
            <div class="os-monthly-calendar-days">';
		for ( $day_date = clone $calendar_start; $day_date <= $calendar_end; $day_date->modify( '+1 day' ) ) {
			$is_today       = ( $day_date->format( 'Y-m-d' ) == OsTimeHelper::today_date() ) ? true : false;
			$is_day_in_past = ( $day_date->format( 'Y-m-d' ) < OsTimeHelper::today_date() ) ? true : false;
			$day_class      = 'os-day os-day-current week-day-' . strtolower( $day_date->format( 'N' ) );

			if ( $day_date->format( 'm' ) > $target_date->format( 'm' ) ) {
				$day_class .= ' os-month-next';
			}
			if ( $day_date->format( 'm' ) < $target_date->format( 'm' ) ) {
				$day_class .= ' os-month-prev';
			}

			if ( $is_today ) {
				$day_class .= ' os-today';
			}
			if ( $highlight_target_date && ( $day_date->format( 'Y-m-d' ) == $target_date->format( 'Y-m-d' ) ) ) {
				$day_class .= ' selected';
			}
			if ( $is_day_in_past ) {
				$day_class .= ' os-day-passed';
			} ?>
        <div class="<?php echo esc_attr( $day_class ); ?>" data-date="<?php echo esc_attr( $day_date->format( 'Y-m-d' ) ); ?>">
            <div class="os-day-box">
                <div class="os-day-number"><?php echo esc_html( $day_date->format( 'j' ) ); ?></div>
            </div>
            </div><?php
		}
		echo '</div></div>';
	}


}