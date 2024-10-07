<?php
/*
 * Copyright (c) 2021 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class WorkPeriod{
	public ?string $custom_date = null;
	public int $week_day;
	public int $start_time = 0;
	public int $end_time = 0;
	public int $service_id = 0;
	public int $agent_id = 0;
	public int $location_id = 0;
	public int $weight = 0;

	function calculate_weight(): int{
		if($this->service_id) $this->weight++;
		if($this->agent_id) $this->weight++;
		if($this->location_id) $this->weight++;
		if($this->custom_date) $this->weight = $this->weight + 3;
		return $this->weight;
	}

	function __construct($args = []){
		$allowed_props = self::allowed_props();
		foreach($args as $key => $arg){
			if(in_array($key, $allowed_props)) $this->$key = $arg;
		}
		$this->calculate_weight();
	}

	public static function create_from_work_period_model(\OsWorkPeriodModel $work_period): WorkPeriod{
		return new WorkPeriod([ 'custom_date'     => $work_period->custom_date,
														'week_day'        => $work_period->week_day,
														'start_time'      => $work_period->start_time,
                            'end_time'        => $work_period->end_time,
                            'agent_id'        => $work_period->agent_id,
                            'location_id'     => $work_period->location_id,
                            'service_id'      => $work_period->service_id]);
	}

	public static function allowed_props(): array{
		return ['custom_date',
						'week_day',
						'start_time',
						'end_time',
						'agent_id',
						'location_id',
						'service_id'];
	}
}