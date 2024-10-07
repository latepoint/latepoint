<?php 

class OsWorkPeriodsHelper {


  public static $existing_work_periods;


	/**
	 * @param \LatePoint\Misc\Filter $filter
	 * @return array
	 *
	 * Returns an array of WorkPeriod objects, grouped by a weekday 1 (for Monday) through 7 (for Sunday).
	 * example: ['1' => [], '2' => [], ...]
	 *
	 */
	public static function get_work_periods_grouped_by_weekday(\LatePoint\Misc\Filter $filter): array{

		$work_periods = OsWorkPeriodsHelper::get_work_periods($filter);

    $weekday_periods = ['1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => [], '7' => []];

    if($work_periods){
			// Loop through the found work periods and group by a week day
      foreach($work_periods as $work_period){
        $weekday_periods[$work_period->week_day][] = $work_period;
      }
    }
		return $weekday_periods;
	}


	/**
	 *
	 * Finds work periods that match a filter.
	 *
	 * @param \LatePoint\Misc\Filter $filter
	 * @return \LatePoint\Misc\WorkPeriod[]
	 */
  public static function get_work_periods(\LatePoint\Misc\Filter $filter, bool $as_models = false): array{

    self::set_default_working_hours();

    $work_periods_model = new OsWorkPeriodModel();
    $query_args = array();

		// if connections are passed - query by connection
		if($filter->connections){
			$connection_conditions = [];
			foreach($filter->connections as $connection){
				$connection_conditions[] = ['AND' => ['agent_id' => [0, $connection->agent_id], 'service_id' => [0, $connection->service_id], 'location_id' => [0, $connection->location_id]]];
			}
			$query_args['AND'][] = ['OR' => $connection_conditions];
		}else{
	    // Service query
	    if($filter->exact_match){
	      // search only for schedules that belong to passed service_id
	      $query_args['service_id'] = $filter->service_id;
	    }else{
			  $query_args['service_id'] = array_unique(is_array($filter->service_id) ? array_merge($filter->service_id, [0]) : [$filter->service_id, 0]);
	    }

	    // Location query
		  if ($filter->exact_match) {
			  // search only for schedules that belong to passed location_id
			  $query_args['location_id'] = $filter->location_id;
		  } else {
			  $query_args['location_id'] = array_unique(is_array($filter->location_id) ? array_merge($filter->location_id, [0]) : [$filter->location_id, 0]);
		  }

	    // Agent query
	    if($filter->exact_match){
	      // search only for schedules that belong to passed agent_id
	      $query_args['agent_id'] = $filter->agent_id;
	    }else{
			  $query_args['agent_id'] = array_unique(is_array($filter->agent_id) ? array_merge($filter->agent_id, [0]) : [$filter->agent_id, 0]);
	    }
		}

     if($filter->week_day){
			 $query_args['week_day'] = $filter->week_day;
     }else if($filter->date_from){
			 $date_from_obj = new OsWpDateTime($filter->date_from);
			 // date is provided, try to get week day from it
	     if(!$filter->date_to || ($filter->date_from == $filter->date_to)){
				 // single date
				 $query_args['week_day'] = $date_from_obj->format('N');
	     }else{
				 // date range
				 $date_to_obj = new OsWpDateTime(($filter->date_to));
				 // if difference between dates is less than a week - it means the days include every weekday possible,
		     // otherwise loop through them and find which weekdays we need to query for
				 if($date_to_obj->diff($date_from_obj)->format("%a") < 6){
					 for($day_date=clone $date_from_obj; $day_date<=$date_to_obj; $day_date->modify('+1 day')){
						 $query_args['week_day'][] = $day_date->format('N');
					 }
				 }
	     }
     }

		 if($filter->date_from){
			if($filter->date_to && ($filter->date_from != $filter->date_to)){
				# both from and to date provided and are different - means it's a range
				if($filter->exact_match){
					# custom date should be exactly in range, can not be NULL
					$query_args['custom_date >='] = $filter->date_from;
					$query_args['custom_date <='] = $filter->date_to;
				}else{
					# custom date should be in range, or NULL
					$query_args['AND'][] = ['OR' => ['custom_date' => 'IS NULL', 'AND' => ['custom_date >=' => $filter->date_from, 'custom_date <=' => $filter->date_to]]];
				}
			}else{
				# only date_from provided - means it's a specific date requested
				if($filter->exact_match) {
					# custom date should match requested date, can not be NULL
					$query_args['custom_date'] = $filter->date_from;
				}else{
					# custom date should match requested date or NULL
					$query_args['custom_date']['OR'] = ['IS NULL', $filter->date_from];
				}
			}
		}else{
			$query_args['custom_date'] = 'IS NULL';
		 }

    $work_periods_model->where($query_args)->order_by('custom_date DESC, agent_id DESC, service_id DESC, location_id DESC, start_time asc');
		if($as_models){
			$work_periods = $work_periods_model->get_results_as_models();
		}else{
			$work_periods_arr = $work_periods_model->get_results();
			$work_periods = [];
			if($work_periods_arr){
				foreach($work_periods_arr as $work_period){
					// Convert return row into work period object
					$work_periods[] = new \LatePoint\Misc\WorkPeriod($work_period);
				}
			}
		}
    return $work_periods;
  }




	/**
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @param array $work_periods_arr
	 * @return bool
	 */
  public static function is_timeframe_in_work_periods(\LatePoint\Misc\BookingRequest $booking_request, array $work_periods_arr): bool{
    if(empty($work_periods_arr)) return false;
    foreach($work_periods_arr as $work_period){
			// loop throught periods and check if it's inside of at least one work period (we ignore buffer here, because you generally don't care about buffer when you start or end work)
      if(OsBookingHelper::is_period_inside_another($booking_request->start_time, $booking_request->end_time, $work_period->start_time, $work_period->end_time)){
        return true;
      }
    }
    return false;
  }


	/**
	 * @param $dated_work_periods_arr
	 * @return array
	 *
	 * Returns array in format [start_minutes, end_minutes], example 8:00-18:00 would be returned as [480, 1080]
	 */
  public static function get_work_start_end_time_for_date_range(array $dated_work_periods): array{
    $work_periods_arr = [];
    foreach($dated_work_periods as $date => $work_periods_for_date){
      $work_periods_arr = array_merge($work_periods_arr, $work_periods_for_date);
    }
    $work_periods_arr = array_unique($work_periods_arr);
    return OsWorkPeriodsHelper::get_work_start_end_time($work_periods_arr);
  }


	/**
	 * @param array $agent_ids
	 * @param \LatePoint\Misc\Filter $filter
	 * @return array
	 *
   * Returns array in format [start_minutes, end_minutes], for example 08:00-18:00 will be returned as [480, 1080]
	 */
  public static function get_work_start_end_time_for_date_multi_agent(array $agent_ids, \LatePoint\Misc\Filter $filter): array{
    $work_start_times = [];
    $work_end_times = [];
		$cloned_filter = clone $filter;
    foreach($agent_ids as $agent_id){
      $cloned_filter->agent_id = $agent_id;
      $work_times = OsWorkPeriodsHelper::get_work_start_end_time_for_date($cloned_filter);
      if($work_times[0] == 0 && $work_times[1] == 0){
        // day off, do not count
      }else{
        $work_start_times[] = $work_times[0];
        $work_end_times[] = $work_times[1];
      }
    }
    if(empty($work_start_times)) $work_start_times = [0];
    if(empty($work_end_times)) $work_end_times = [0];
    return array(min($work_start_times), max($work_end_times));
  }


	/**
	 * @param \LatePoint\Misc\Filter $filter
	 * @return array
	 *
	 * Returns array in format [start_minutes, end_minutes], example 8:00-18:00 would be returned as [480, 1080]
	 */
  public static function get_work_start_end_time_for_date(\LatePoint\Misc\Filter $filter): array{
    $work_periods_arr = OsWorkPeriodsHelper::get_work_periods($filter);
    return OsWorkPeriodsHelper::get_work_start_end_time($work_periods_arr);
  }


	/**
	 * @param \LatePoint\Misc\WorkPeriod[]
	 * @return array
	 *
	 * Returns array in format [start_minutes, end_minutes], example 8:00-18:00 would be returned as [480, 1080]
	 */
  public static function get_work_start_end_time(array $work_periods_arr): array{
    $work_start_minutes = 0;
    $work_end_minutes = 0;
    foreach($work_periods_arr as $work_period){
      if($work_period->start_time == $work_period->end_time) continue;
      $work_start_minutes = ($work_start_minutes > 0) ? min($work_period->start_time, $work_start_minutes) : $work_period->start_time;
      $work_end_minutes = ($work_end_minutes > 0) ? max($work_period->end_time, $work_end_minutes) : $work_period->end_time;
    }
    return array($work_start_minutes, $work_end_minutes);
  }


  // args: period_id, week_day, is_active, start_time, end_time, custom_date, agent_id, service_id
  public static function generate_work_period_form($args = array(), $allow_remove = true){
    $default_args = array(
      'period_id' => false,
      'week_day' => 1,
      'allow_remove' => true,
      'start_time' => 480,
      'end_time' => 1080,
      'agent_id' => 0,
      'location_id' => 0,
      'service_id' => 0
    );
    $args = array_merge($default_args, $args);

    $period_id = (!$args['period_id']) ? 'new_'.$args['week_day'].'_'.OsUtilHelper::random_text() : $args['period_id'];
    $period_html = '<div class="ws-period">';
      $period_html.= OsFormHelper::time_field('work_periods['.$period_id.'][start_time]', __('Start', 'latepoint'), $args['start_time'], true);
      $period_html.= OsFormHelper::time_field('work_periods['.$period_id.'][end_time]', __('Finish', 'latepoint'), $args['end_time'], true);
      $period_html.= OsFormHelper::hidden_field('work_periods['.$period_id.'][week_day]', $args['week_day']);
      $period_html.= OsFormHelper::hidden_field('work_periods['.$period_id.'][is_active]', self::is_period_active($args['start_time'], $args['end_time']), array('class' => 'is-active'));
      $period_html.= OsFormHelper::hidden_field('work_periods['.$period_id.'][agent_id]', $args['agent_id']);
      $period_html.= OsFormHelper::hidden_field('work_periods['.$period_id.'][location_id]', $args['location_id']);
      $period_html.= OsFormHelper::hidden_field('work_periods['.$period_id.'][service_id]', $args['service_id']);
      if(isset($args['custom_date'])) $period_html.= OsFormHelper::hidden_field('work_periods['.$period_id.'][custom_date]', $args['custom_date']);
      if($allow_remove) $period_html.= '<button class="ws-period-remove"><i class="latepoint-icon latepoint-icon-x"></i></button>';
    $period_html.= '</div>';
    return $period_html;
  }

  public static function is_period_active($start_time, $end_time){
    return (($start_time == 0) && ($end_time == 0)) ? false : true;
  }

  public static function save_work_periods($work_periods_to_save, $force_new = false){
    $ids_to_save = array();
    $inactive_weekdays = array();
    // save passed periods
    if($work_periods_to_save){
      foreach($work_periods_to_save as $id => $work_period){
        if(in_array($work_period['week_day'], $inactive_weekdays)) continue;
        if($work_period['is_active'] == 0){
          $work_period['start_time'] = 0;  
          $work_period['end_time'] = 0;  
          $inactive_weekdays[] = $work_period['week_day'];
        }else{
          $start_ampm = isset($work_period['start_time']['ampm']) ? $work_period['start_time']['ampm'] : false;
          $end_ampm = isset($work_period['end_time']['ampm']) ? $work_period['end_time']['ampm'] : false;

          $work_period['start_time'] = OsTimeHelper::convert_time_to_minutes($work_period['start_time']['formatted_value'], $start_ampm);
          $work_period['end_time'] = OsTimeHelper::convert_time_to_minutes($work_period['end_time']['formatted_value'], $end_ampm);
        }
        if($force_new || substr( $id, 0, 4 ) === "new_"){
          // new record
          $work_period_obj = new OsWorkPeriodModel();
          $work_period_obj->set_data($work_period);
          $work_period_obj->save();
          $ids_to_save[] = $work_period_obj->id;
        }else{
          // existing work period
          $work_period_obj = new OsWorkPeriodModel($id);
          if(!$work_period_obj){
            $work_period_obj = new OsWorkPeriodModel();
            unset($work_period['id']);
          }
          $work_period_obj->set_data($work_period);
          if($work_period_obj->save()){
            $ids_to_save[] = $work_period_obj->id;
          }
        }
      }
    }
    if(!$force_new){
      // if any periods were saved, get their agent and service info to delete obsolete records
      $search_args = (isset($work_period_obj)) ? array('agent_id' => $work_period_obj->agent_id, 'service_id' => $work_period_obj->service_id, 'location_id' => $work_period_obj->location_id) : array();
      if(isset($work_period_obj) && $work_period_obj->custom_date){
        $search_args['custom_date'] = $work_period_obj->custom_date;
      }else{
        $search_args['custom_date'] = 'IS NULL';
      }
      $ids_in_db = OsWorkPeriodsHelper::get_periods_ids_by_args($search_args);

      $period_ids_to_remove = array_diff($ids_in_db, $ids_to_save);
      if(!empty($period_ids_to_remove)){
        $work_period_obj = new OsWorkPeriodModel();
        foreach($period_ids_to_remove as $period_id){
          $work_period_obj->delete($period_id);
        }
      }
    }
  }



  public static function get_periods_ids_by_args($args = array()){
    $default_args = array(
      'custom_date' => false, 
      'week_day' => false, 
      'service_id' => 0, 
      'location_id' => 0, 
      'agent_id' => 0);
    $args = array_merge($default_args, $args);
    if($args['custom_date']) $query_args['custom_date'] = $args['custom_date'];
    if($args['week_day']) $query_args['week_day'] = $args['week_day'];
    $query_args['agent_id'] = $args['agent_id'];
    $query_args['location_id'] = $args['location_id'];
    $query_args['service_id'] = $args['service_id'];

    $work_periods_model = new OsWorkPeriodModel();
    $work_periods_rows = $work_periods_model->select('id')->where($query_args)->get_results();
    if(is_array($work_periods_rows)){
      $ids = array_map(function($row){return $row->id; }, $work_periods_rows);
    }else{
      $ids = array();
    }
    return $ids;
  }

	/**
	 * @param OsWorkPeriodModel[] $work_periods
	 * @return OsWorkPeriodModel[]
	 */
  public static function filter_periods(array $work_periods): array{
    // remove overriden periods
    $filtered_periods = [];
    if(count($work_periods) > 1){
      $reference = $work_periods[0];
      $filtered_periods[] = $reference;
      for($i = 1; $i < count($work_periods); $i++){
        if($work_periods[$i]->week_day == $reference->week_day){
					# periods are ordered by these attributes, loop through them and if
          if( $work_periods[$i]->agent_id != $reference->agent_id ||
	            $work_periods[$i]->location_id != $reference->location_id ||
	            $work_periods[$i]->service_id != $reference->service_id ||
	            $work_periods[$i]->custom_date != $reference->custom_date)
					{
            // conflicting period, skip it
          }else{
            $filtered_periods[] = $work_periods[$i];
          }
        }else{
          $reference = $work_periods[$i];
          $filtered_periods[] = $reference;
        }
      }
      return $filtered_periods;
    }else{
      return $work_periods;
    }
  }

	public static function set_default_working_hours(){
    $work_start_minutes = 8 * 60;
    $work_end_minutes = 17 * 60;
    $week_days = OsUtilHelper::get_weekday_numbers();

    // Try to find existing work periods in the database
    $work_periods_model = new OsWorkPeriodModel();
    if(!self::$existing_work_periods){
      self::$existing_work_periods = $work_periods_model->select('week_day')->where(array('agent_id' => 0, 'service_id' => 0, 'location_id' => 0))->where(array('custom_date' => 'IS NULL'))->group_by('week_day')->get_results(ARRAY_A);
      if(self::$existing_work_periods){
        self::$existing_work_periods = array_map(function($work_period){ return $work_period['week_day']; }, self::$existing_work_periods);
        $week_days = array_diff($week_days, self::$existing_work_periods);
        // if already had some work periods - set others to 0/0 because before we used to NOT store non working days in the database, now we set hours to 0/0 instead for day offs
        $work_start_minutes = 0;
        $work_end_minutes = 0;
      }
      if(!empty($week_days)){
        foreach($week_days as $week_day){
    			$work_period = new OsWorkPeriodModel();
    			$work_period->service_id = 0;
          $work_period->agent_id = 0;
    			$work_period->location_id = 0;
    			$work_period->week_day = $week_day;
    			$work_period->start_time = $work_start_minutes;
    			$work_period->end_time = $work_end_minutes;
    			$work_period->save();
    		}
      }
    }

	}

  public static function remove_periods_for_chain_id($chain_id){
    $work_periods_model = new OsWorkPeriodModel();
    $work_periods = $work_periods_model->delete_where(['chain_id' => $chain_id]);
    return true;
  }

  public static function remove_periods_for_date(string $date, $args = array()): bool{
    $default_args = [ 'agent_id' => 0, 'service_id' => 0, 'location_id' => 0];
    $args = array_merge($default_args, $args);
    $args['custom_date'] = $date;
    $work_periods_model = new OsWorkPeriodModel();
    $work_periods = $work_periods_model->where($args)->get_results_as_models();
    if($work_periods){
      foreach($work_periods as $work_period){
        $work_period->delete();
      }
    }
    return true;
  }


  public static function generate_days_with_custom_schedule($args = array()){
    $default_args = [ 'agent_id' => 0, 'service_id' => 0, 'location_id' => 0];
    $args = array_merge($default_args, $args);

    $work_periods = new OsWorkPeriodModel();
    $work_periods = $work_periods->where($args)->where([
                          'custom_date' => 'IS NOT NULL',
													'custom_date >=' => OsTimeHelper::today_date(),
                          'OR' => ['start_time !=' => 0, 'end_time !=' => 0]])->group_by('custom_date, chain_id')->order_by('custom_date asc')->get_results_as_models();
    $html = '';
    if($work_periods && isset($work_periods[0])){
      $date = new OsWpDateTime($work_periods[0]->custom_date);
      $processing_year = $date->format('Y');
      if($date->format('Y') != gmdate('Y')) $html.=  '<div class="os-form-sub-header sub-level"><h3>'.esc_html($date->format('Y')).'</h3></div>';
    }
    $chained_periods = [];
    $html.= '<div class="custom-day-work-periods">';  
      if($work_periods){
        $total_periods = count($work_periods);
        $i = 0;
        foreach($work_periods as $work_period){
          $i = $i + 1;
          if(empty($work_period->custom_date)) continue;
          if($work_period->chain_id){
            $chained_periods[$work_period->chain_id][] = $work_period->custom_date;
            if($i < $total_periods) continue;
          }
          if($chained_periods){

            foreach($chained_periods as $chain_id => $chained_period){
              $range_start_date = new OsWpDateTime(min($chained_period));
              $range_end_date = new OsWpDateTime(max($chained_period));
              if($processing_year != $range_start_date->format('Y')){
                $html.= '</div><div class="os-form-sub-header sub-level"><h3>'.esc_html($range_start_date->format('Y')).'</h3></div><div class="custom-day-work-periods">';
                $processing_year = $range_start_date->format('Y');
              }
              $html.= '<div class="custom-day-work-period is-range">';
              $html.= '<a href="#" title="'.esc_attr__('Edit Date Range Schedule', 'latepoint').'" class="edit-custom-day" '.self::generate_custom_day_period_action($range_start_date->format('Y-m-d'), false, array_merge($args, ['chain_id' => $chain_id])).'><i class="latepoint-icon latepoint-icon-edit-3"></i></a>';
              $html.= '<a href="#" data-os-pass-this="yes" data-os-after-call="latepoint_custom_day_removed" data-os-action="'.esc_attr(OsRouterHelper::build_route_name('settings', 'remove_chain_schedule')).'" data-os-params="'.esc_attr(OsUtilHelper::build_os_params(['chain_id' => $chain_id])).'" data-os-prompt="'.esc_attr__('Are you sure you want to remove custom schedule for this date range?', 'latepoint').'" title="'.esc_attr__('Remove Date Range Schedule', 'latepoint').'" class="remove-custom-day"><i class="latepoint-icon latepoint-icon-trash-2"></i></a>';
              $html.= '<div class="custom-day-work-period-i">';
              $html.= '<div class="custom-day-number">'.esc_html($range_start_date->format('d').' - '.$range_end_date->format('d')) .'</div>';
              if($range_start_date->format('n') != $range_end_date->format('n')){
                $html.= '<div class="custom-day-month">'.esc_html(OsUtilHelper::get_month_name_by_number($range_start_date->format('n')).'-'.OsUtilHelper::get_month_name_by_number($range_end_date->format('n'))).'</div>';
              }else{
                $html.= '<div class="custom-day-month">'.esc_html(OsUtilHelper::get_month_name_by_number($range_start_date->format('n'))).'</div>';
              }
              $html.= '</div>';
              $work_periods_for_date_model = new OsWorkPeriodModel();
              $work_periods_for_date = $work_periods_for_date_model->where($args)->where(['custom_date' => $range_start_date->format('Y-m-d'), 'chain_id' => $chain_id])->order_by('start_time asc')->get_results_as_models();
              if($work_periods_for_date){
                $html.= '<div class="custom-day-periods">';
                foreach($work_periods_for_date as $work_period_for_date){
                  $html.= '<div class="custom-day-period">'. esc_html($work_period_for_date->nice_start_time.' - '.$work_period_for_date->nice_end_time). '</div>';
                }
                $html.= '</div>';
              }
              $html.= '</div>';
            }
            $chained_periods = [];
          }
          if(empty($work_period->chain_id)){
            $date = new OsWpDateTime($work_period->custom_date);
            if($processing_year != $date->format('Y')) $html.= '</div><div class="os-form-sub-header sub-level"><h3>'.esc_html($date->format('Y')).'</h3></div><div class="custom-day-work-periods">';
            $html.= '<div class="custom-day-work-period">';
            $html.= '<a href="#" title="'.esc_attr__('Edit Day Schedule', 'latepoint').'" class="edit-custom-day" '.self::generate_custom_day_period_action($work_period->custom_date, false, $args).'><i class="latepoint-icon latepoint-icon-edit-3"></i></a>';
            $html.= '<a href="#" data-os-pass-this="yes" data-os-after-call="latepoint_custom_day_removed" data-os-action="'.esc_attr(OsRouterHelper::build_route_name('settings', 'remove_custom_day_schedule')).'" data-os-params="'.esc_attr(OsUtilHelper::build_os_params(array_merge($args, ['date' => $work_period->custom_date]))).'" data-os-prompt="'.esc_attr__('Are you sure you want to remove custom schedule for this day?', 'latepoint').'" title="'.esc_attr__('Remove Day Schedule', 'latepoint').'" class="remove-custom-day"><i class="latepoint-icon latepoint-icon-trash-2"></i></a>';
            $html.= '<div class="custom-day-work-period-i">';
            $html.= '<div class="custom-day-number">'.esc_html($date->format('d')).'</div>';
            $html.= '<div class="custom-day-month">'.esc_html(OsUtilHelper::get_month_name_by_number($date->format('n'))).'</div>';
            $html.= '</div>';
            $work_periods_for_date_model = new OsWorkPeriodModel();
            $work_periods_for_date = $work_periods_for_date_model->where($args)->where(['custom_date' => $work_period->custom_date,
                                                                                        'chain_id' => 'IS NULL', 
                                                                                        'OR' => ['start_time !=' => 0, 'end_time !=' => 0]])->order_by('start_time asc')->get_results_as_models();
            if($work_periods_for_date){
              $html.= '<div class="custom-day-periods">';
              foreach($work_periods_for_date as $work_period_for_date){
                $html.= '<div class="custom-day-period">'. esc_html($work_period_for_date->nice_start_time.' - '.$work_period_for_date->nice_end_time). '</div>';
              }
              $html.= '</div>';
            }
            $html.= '</div>';
            $processing_year = $date->format('Y');
          }
        }
      }
      $html.= '<a class="add-custom-day-w" '.self::generate_custom_day_period_action(false, false, $args).'>
                <div class="add-custom-day-i">
                  <div class="add-day-graphic-w"><div class="add-day-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div></div><div class="add-day-label">'.esc_html__('Add Day', 'latepoint').'</div>
                </div>
              </a>';

    $html.= '</div>';
    echo $html;
  }


  public static function generate_off_days($args = array()){
    $default_args = [ 'agent_id' => 0, 'service_id' => 0, 'location_id' => 0];
    $args = array_merge($default_args, $args);

    $work_periods = new OsWorkPeriodModel();
    $work_periods = $work_periods->where($args)->where(['custom_date' => 'IS NOT NULL',
													'custom_date >=' => OsTimeHelper::today_date(),
                                                        'start_time' => 0, 
                                                        'end_time' => 0])->group_by('custom_date, chain_id')->order_by('custom_date asc')->get_results_as_models();
    $html = '';
    if($work_periods && isset($work_periods[0])){
      $date = new OsWpDateTime($work_periods[0]->custom_date);
      $processing_year = $date->format('Y');
      if($date->format('Y') != gmdate('Y')) $html.= '<div class="os-form-sub-header sub-level"><h3>'.esc_html($date->format('Y')).'</h3></div>';
    }
    $chained_periods = [];
    $html.= '<div class="custom-day-work-periods">';
      if($work_periods){
        $total_periods = count($work_periods);
        $i = 0;
        foreach($work_periods as $work_period){
          $i = $i + 1;
          if(empty($work_period->custom_date)) continue;
          if($work_period->chain_id){
            $chained_periods[$work_period->chain_id][] = $work_period->custom_date;
            if($i < $total_periods) continue;
          }
          if($chained_periods){

            foreach($chained_periods as $chain_id => $chained_period){
              $range_start_date = new OsWpDateTime(min($chained_period));
              $range_end_date = new OsWpDateTime(max($chained_period));
              if($processing_year != $range_start_date->format('Y')){
                $html.= '</div><div class="os-form-sub-header sub-level"><h3>'.esc_html($range_start_date->format('Y')).'</h3></div><div class="custom-day-work-periods">';
                $processing_year = $range_start_date->format('Y');
              }
              $html.= '<div class="custom-day-work-period is-range custom-day-off">';
              $html.= '<a href="#" data-os-pass-this="yes" data-os-after-call="latepoint_custom_day_removed" data-os-action="'.esc_attr(OsRouterHelper::build_route_name('settings', 'remove_chain_schedule')).'" data-os-params="'.esc_attr(OsUtilHelper::build_os_params(['chain_id' => $chain_id])).'" data-os-prompt="'.esc_attr__('Are you sure you want to remove day off range?', 'latepoint').'" title="'.esc_attr__('Remove Day Off Range', 'latepoint').'" class="remove-custom-day"><i class="latepoint-icon latepoint-icon-trash-2"></i></a>';
              $html.= '<div class="custom-day-work-period-i">';
                $html.= '<div class="custom-day-number">'.esc_html($range_start_date->format('d').' - '.$range_end_date->format('d')) .'</div>';
                if($range_start_date->format('n') != $range_end_date->format('n')){
                  $html.= '<div class="custom-day-month">'.esc_html(OsUtilHelper::get_month_name_by_number($range_start_date->format('n')).'-'.OsUtilHelper::get_month_name_by_number($range_end_date->format('n'))).'</div>';
                }else{
                  $html.= '<div class="custom-day-month">'.esc_html(OsUtilHelper::get_month_name_by_number($range_start_date->format('n'))).'</div>';
                }
                $html.= '</div>';
              $html.= '</div>';
            }
            $chained_periods = [];
          }
          if(empty($work_period->chain_id)){
            $date = new OsWpDateTime($work_period->custom_date);
            if($processing_year != $date->format('Y')) $html.= '</div><div class="os-form-sub-header sub-level"><h3>'.esc_html($date->format('Y')).'</h3></div><div class="custom-day-work-periods">';
            $html.= '<div class="custom-day-work-period custom-day-off">';
              $html.= '<a href="#" title="'.esc_attr__('Edit Day Schedule', 'latepoint').'" class="edit-custom-day" '.self::generate_custom_day_period_action($work_period->custom_date, false, $args).'><i class="latepoint-icon latepoint-icon-edit-3"></i></a>';
              $html.= '<a href="#" data-os-pass-this="yes" data-os-after-call="latepoint_custom_day_removed" data-os-action="'.esc_attr(OsRouterHelper::build_route_name('settings', 'remove_custom_day_schedule')).'" data-os-params="'.esc_attr(OsUtilHelper::build_os_params(array_merge($args, ['date' => $work_period->custom_date]))).'" data-os-prompt="'.esc_attr__('Are you sure you want to remove this day off?', 'latepoint').'" title="'.esc_attr__('Remove Day Off', 'latepoint').'" class="remove-custom-day"><i class="latepoint-icon latepoint-icon-trash-2"></i></a>';
              $html.= '<div class="custom-day-work-period-i">';
                $html.= '<div class="custom-day-number">'.esc_html($date->format('d')).'</div>';
                $html.= '<div class="custom-day-month">'.esc_html(OsUtilHelper::get_month_name_by_number($date->format('n'))).'</div>';
              $html.= '</div>';
            $html.= '</div>';
            $processing_year = $date->format('Y');
          }
        }
      }
    $html.= '<a class="add-custom-day-w" '.self::generate_custom_day_period_action(false, true, $args).'>
              <div class="add-custom-day-i">
                <div class="add-day-graphic-w"><div class="add-day-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div></div><div class="add-day-label">'.esc_html__('Add Day', 'latepoint').'</div>
              </div>
            </a>';
    $html.= '</div>';
    echo $html;
  }


  public static function generate_custom_day_period_action($target_date = false, $day_off = false, $args = array()){
    $os_params = [];
    if($day_off) $os_params['day_off'] = true;
    if($target_date){
      $os_params['target_date'] = $target_date;
      $hide_schedule_class = '';
    }else{
      $hide_schedule_class = ' hide-schedule';
    }
    $os_params = array_merge($os_params, $args);
    $html = 'data-os-after-call="latepoint_init_custom_day_schedule" data-os-lightbox-classes="width-700 '.esc_attr($hide_schedule_class).'" data-os-output-target="lightbox" data-os-action="'.esc_attr(OsRouterHelper::build_route_name('settings', 'custom_day_schedule_form')).'"';
    if(!empty($os_params)) $html.= ' data-os-params="'.esc_attr(OsUtilHelper::build_os_params($os_params)).'"';
    return $html;
  }


	/**
	 * @param array $work_periods
	 * @param \LatePoint\Misc\Filter $filter
	 * @param bool $is_new_record
	 * @return void
	 */
  public static function generate_work_periods(array $work_periods, \LatePoint\Misc\Filter $filter, bool $is_new_record = false){
    if(!$work_periods) $work_periods = OsWorkPeriodsHelper::get_work_periods($filter, true);
    $working_periods_with_weekdays = array();
    if($work_periods){
      foreach($work_periods as $work_period){
        $working_periods_with_weekdays['day_'.$work_period->week_day][] = $work_period;
      }
    }
    for($i=1; $i<=7; $i++){
      $is_day_off = true;
      $period_forms_html = '';
      if(isset($working_periods_with_weekdays['day_'.$i])){
        $is_day_off = false;
        // EXISTING WORK PERIOD
        $allow_remove = false;
        foreach($working_periods_with_weekdays['day_'.$i] as $work_period){
          if($work_period->start_time === $work_period->end_time){
            $is_day_off = true;
          }
          if($filter->agent_id && ($work_period->agent_id !== $filter->agent_id)){
            $work_period->agent_id = $filter->agent_id;
            $work_period->id = false;
          }
          if($filter->service_id && ($work_period->service_id !== $filter->service_id)){
            $work_period->service_id = $filter->service_id;
            $work_period->id = false;
          }
          if($filter->location_id && ($work_period->location_id !== $filter->location_id)){
            $work_period->location_id = $filter->location_id;
            $work_period->id = false;
          }
          if($is_new_record){
            $work_period->id = false;
          }
          $period_forms_html.= OsWorkPeriodsHelper::generate_work_period_form(array('period_id' => $work_period->id,
                                                                                'week_day' => $i, 
                                                                                'is_active' => $work_period->is_active, 
                                                                                'agent_id' => $work_period->agent_id, 
                                                                                'service_id' => $work_period->service_id, 
                                                                                'location_id' => $work_period->location_id, 
                                                                                'start_time' => $work_period->start_time, 
                                                                                'end_time' => $work_period->end_time), $allow_remove);
          $allow_remove = true;
        }
      }else{
        // NEW WORK PERIOD
        $period_forms_html.= OsWorkPeriodsHelper::generate_work_period_form(array(  'period_id' => false, 
                                                                                    'week_day' => $i,
                                                                                    'start_time' => 0,
                                                                                    'end_time' => 0), false);
      } ?>
      <div class="weekday-schedule-w <?php echo $is_day_off ? 'day-off' : ''; ?>">
        <div class="ws-head-w">
          <div class="os-toggler <?php echo $is_day_off ? 'off' : 'on'; ?>">
            <div class="toggler-rail"><div class="toggler-pill"></div></div>
          </div>
          <div class="ws-head">
            <div class="ws-day-name"><?php echo esc_html(OsBookingHelper::get_weekday_name_by_number($i, true)); ?></div>
            <div class="ws-day-hours">
              <?php
              if(isset($working_periods_with_weekdays['day_'.$i])){
                foreach($working_periods_with_weekdays['day_'.$i] as $index => $work_period){
                  if($work_period->start_time === $work_period->end_time) continue;
                  if($index >= 2) {
	                // translators: %d number of work periods
                    echo esc_html('<span>'.sprintf(__('+%d More', 'latepoint'), count($working_periods_with_weekdays['day_'.$i]) - 2)).'</span>';
                    break;
                  }
                  echo '<span>'.esc_html($work_period->nice_start_time.'-'.$work_period->nice_end_time).'</span>';
                }
              }
              ?>
            </div>
            <div class="wp-edit-icon">
              <i class="latepoint-icon latepoint-icon-edit-3"></i>
            </div>
          </div>
        </div>
        <div class="weekday-schedule-form">
          <?php 
          echo $period_forms_html; 
          $params = ['week_day' => $i];
          if($filter->agent_id) $params['agent_id'] = $filter->agent_id;
          if($filter->service_id) $params['service_id'] = $filter->service_id;
          if($filter->location_id) $params['location_id'] = $filter->location_id;
          ?>
          <div class="ws-period-add"
               data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params($params)); ?>"
              data-os-before-after="before"
              data-os-after-call="latepoint_init_work_period_form"
              data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('settings', 'load_work_period_form')); ?>">
            <div class="add-period-graphic-w">
              <div class="add-period-plus"><i class="latepoint-icon latepoint-icon-plus-square"></i></div>
            </div>
            <div class="add-period-label">
                <?php
                // translators: %s name of a weekday
                echo esc_html(sprintf(__('Add another work period for %s', 'latepoint'), OsBookingHelper::get_weekday_name_by_number($i, true))); ?>
            </div>
          </div>
        </div>
      </div>
      <?php
    }
  }
}