<?php
/**
 * @var $current_step_code string
 * @var $booking OsBookingModel
 * @var $restrictions array
 * @var $presets array
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<?php include('_'.$current_step_code.'.php'); ?>