<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

namespace LatePoint\Misc;

class StripeConnectCustomer{
	public string $id;

	function __construct($args = []){
		$allowed_props = self::allowed_props();
		foreach($args as $key => $arg){
			if(in_array($key, $allowed_props)) $this->$key = $arg;
		}
	}


	public static function allowed_props(): array{
		return ['id', 'type', 'settings', 'status'];
	}
}