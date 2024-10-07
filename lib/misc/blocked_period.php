<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 *
 * This class is used to create blocked periods in resources
 */

namespace LatePoint\Misc;

class BlockedPeriod{
	public string $start_date;
	public string $end_date;
	public int $start_time = 0;
	public int $end_time = 0;
	public ?int $service_id = 0;
	public ?int $agent_id = 0;
	public ?int $location_id = 0;

	function __construct($args = []){
		$allowed_props = static::allowed_props();
		foreach($args as $key => $arg){
			if(in_array($key, $allowed_props)) $this->$key = $arg;
		}
	}

	public static function allowed_props(): array{
		return ['start_date',
						'end_date',
						'start_time',
						'end_time',
						'service_id',
						'agent_id',
						'location_id',
						'reason'
						];
	}
}