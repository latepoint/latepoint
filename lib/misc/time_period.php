<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class TimePeriod{
	public int $start_time = 0;
	public int $end_time = 0;

	function __construct($args = []){
		$allowed_props = self::allowed_props();
		foreach($args as $key => $arg){
			if(in_array($key, $allowed_props)) $this->$key = $arg;
		}
	}


	public static function merge_periods(array $time_periods): array{
	  if(!$time_periods) return [];

	  $result = [$time_periods[0]];

	  for ($i = 0; $i < count($time_periods); $i++) {
	    $x1 = $time_periods[$i]->start_time;
	    $y1 = $time_periods[$i]->end_time;
	    $x2 = $result[count($result) - 1]->start_time;
	    $y2 = $result[count($result) - 1]->end_time;

	    if ($y2 >= $x1) {
	      $result[count($result) - 1]->end_time = max($y1, $y2);
	    } else {
	      $result[] = new TimePeriod(['start_time' => $x1, 'end_time' => $y1]);
	    }
	  }
	  return $result;
	}

	public static function create_from_work_period(WorkPeriod $work_period){
		return new TimePeriod(['start_time' => $work_period->start_time, 'end_time' => $work_period->end_time]);
	}

	/**
	 * @param array $overlapped_time_periods
	 * @return TimePeriod
	 */
	public static function get_unified_period_from_overlapped_periods(array $overlapped_time_periods): TimePeriod{
		$bounds = [];
		foreach($overlapped_time_periods as $time_period){
			$bounds[] = $time_period->start_time;
			$bounds[] = $time_period->end_time;
		}
		return new TimePeriod(['start_time' => min($bounds), 'end_time' => max($bounds)]);
	}

	public function check_if_overlaps(TimePeriod $check_time_period){
    return (($this->start_time < $check_time_period->end_time) && ($check_time_period->start_time < $this->end_time));
	}

	public static function allowed_props(): array{
		return ['start_time',
						'end_time'];
	}
}