<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
OsCalendarHelper::generate_monthly_calendar_days_only($target_date->format('Y-m-d'));