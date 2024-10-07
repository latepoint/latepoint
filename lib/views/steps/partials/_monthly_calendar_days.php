<?php
/* @var $booking_request \LatePoint\Misc\BookingRequest */
/* @var $target_date DateTime */
/* @var $calendar_settings array */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

OsCalendarHelper::generate_single_month($booking_request, $target_date, $calendar_settings);