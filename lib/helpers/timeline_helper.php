<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */


class OsTimelineHelper{

	/**
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @param \LatePoint\Misc\TimePeriod
	 * @param \LatePoint\Misc\BookingResource[]
	 * @param array $settings
	 * @return string
	 * @throws Exception
	 */
  public static function availability_timeline(\LatePoint\Misc\BookingRequest $booking_request, \LatePoint\Misc\TimePeriod $timeline_boundaries, array $resources, array $settings = []){
    $default_settings = [
			'agent_to_show' => false,
      'book_on_click' => true,
      'show_ticks' => true
    ];
    $settings = array_merge($default_settings, $settings);
		$total_timeline_minutes = $timeline_boundaries->end_time - $timeline_boundaries->start_time;
    $html = '<div class="agent-day-availability-w">';
      if($settings['agent_to_show']){
				// show agent avatar if agent was passed
	      $agent = $settings['agent_to_show'];
        $html.= '<a href="'.OsRouterHelper::build_link(['agents', 'edit_form'], ['id' => $agent->id] ).'" class="agent-avatar-w with-hover-name" style="background-image: url('.$agent->get_avatar_url().');"><span>'.$agent->full_name.'</span></a>';
      }
			$booking_slots = OsResourceHelper::get_ordered_booking_slots_from_resources($resources);

      $html.= '<div class="agent-timeslots">';

				if($booking_slots){
					$total_slots = count($booking_slots);
					$after_slot_html = false;
					$slot_width = false;
					$gap = false;
					// find minimum gap
					$minimum_slot_gap = \LatePoint\Misc\BookingSlot::find_minimum_gap_between_slots($booking_slots);

					if($booking_slots[0]->start_time != $timeline_boundaries->start_time){
						$slot_width = ($booking_slots[0]->start_time - $timeline_boundaries->start_time) / $total_timeline_minutes * 100;
						$html.= self::timeline_timeslot_off($slot_width);
					}
					for($i = 0; $i<$total_slots; $i++){
						if($i == $total_slots - 1){
							// last slot in a day
							$prev_width = $slot_width;
							$slot_width = ($timeline_boundaries->end_time - $booking_slots[$i]->start_time) / $total_timeline_minutes * 100;
							if($prev_width && $prev_width < $slot_width){
								$after_slot_html = self::timeline_timeslot_off($slot_width - $prev_width);
								$slot_width = $prev_width;
							}
						}else{
							$gap = $booking_slots[$i + 1]->start_time - $booking_slots[$i]->start_time;
							if($gap > $minimum_slot_gap){
								$slot_width = $minimum_slot_gap / $total_timeline_minutes * 100;
								$after_slot_html = self::timeline_timeslot_off(($gap - $minimum_slot_gap) / $total_timeline_minutes * 100);
							}else{
								$slot_width = ($booking_slots[$i + 1]->start_time - $booking_slots[$i]->start_time) / $total_timeline_minutes * 100;
							}
						}
						$html.= self::timeline_timeslot($booking_slots[$i], $booking_request, ['show_ticks' => $settings['show_ticks'], 'book_on_click' => $settings['book_on_click'], 'slot_width' => $slot_width]);
						if($after_slot_html){
							$html.=$after_slot_html;
							$after_slot_html = false;
						}
					}
				}else{
					$html.= self::availability_timeline_off();
				}
      $html.= '</div>';
    $html.= '</div>';
		return $html;
  }

  public static function availability_timeline_off(string $off_label = ''){
		$off_label = $off_label ? $off_label : __('Not Available', 'latepoint');
    return '<div class="agent-timeslot is-off full-day-off"><span class="agent-timeslot-label">'.$off_label.'</span></div>';
  }


	/**
	 * @param \LatePoint\Misc\BookingSlot $booking_slot
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @param array $settings
	 * @return string
	 */
	public static function timeline_timeslot(\LatePoint\Misc\BookingSlot $booking_slot, \LatePoint\Misc\BookingRequest $booking_request, array $settings = []){
		$default_settings = [
      'book_on_click' => true,
      'show_ticks' => true,
			'slot_width' => false
    ];
		$settings = array_merge($default_settings, $settings);

    $ampm = OsTimeHelper::am_or_pm($booking_slot->start_time);
    $tick_html = '';
		$timeslot_class = '';
    if($settings['show_ticks'] && ($booking_slot->start_time % 60) == 0){
      $timeslot_class.= ' with-tick';
      $tick_html = '<span class="agent-timeslot-tick"><strong>'. OsTimeHelper::minutes_to_hours($booking_slot->start_time) .'</strong>'.' '.$ampm.'</span>';
    }

    $data_attrs = '';
    if($booking_slot->can_accomodate($booking_request->total_attendees)){
      $timeslot_class.= ' is-available';
      if($settings['book_on_click']){
				// clicking a timeslot will result in opening a new booking slideout
        $data_attrs = OsOrdersHelper::quick_order_btn_html(false, ['start_time'=> $booking_slot->start_time,
	                                                                                'agent_id' => $booking_request->agent_id,
	                                                                                'service_id' => $booking_request->service_id,
	                                                                                'location_id' => $booking_request->location_id,
	                                                                                'start_date' => $booking_request->start_date]);
      }else{
				// fills in the data of a booking form slideout
        $data_attrs = 'data-date="'.$booking_slot->start_date.'" data-formatted-date="'.OsTimeHelper::reformat_date_string($booking_slot->start_date, 'Y-m-d', OsSettingsHelper::get_date_format()).'" data-minutes="'.$booking_slot->start_time.'"';
        $timeslot_class.= ' fill-booking-time';
      }
    }else{
			$timeslot_class.= ' is-booked';
    }
		$style = $settings['slot_width'] ? 'width: '.$settings['slot_width'].'%' : '';
		$timeslot_html = '<div '.$data_attrs.' style="'.$style.'" class="agent-timeslot '.$timeslot_class.'">
												<span class="agent-timeslot-label">'.OsTimeHelper::get_nice_date_with_optional_year($booking_slot->start_date).', '.OsTimeHelper::minutes_to_hours_and_minutes($booking_slot->start_time).'</span>'.
						            $tick_html.'
											</div>';
    return $timeslot_html;
	}




	public static function timeline_timeslot_off($slot_width){
		return '<div class="agent-timeslot is-off" style="width: '.esc_attr($slot_width).'%"><span class="agent-timeslot-label">'.__('Not Available', 'latepoint').'</span></div>';
	}


}