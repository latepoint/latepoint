<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/** @var $target_date OsWpDateTime */
/** @var $filter array */
/** @var $range_type string */
/** @var $restrictions array */


echo OsEventsHelper::events_grid($target_date, $filter, $range_type, $restrictions);