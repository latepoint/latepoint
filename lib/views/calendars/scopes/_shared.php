<?php
/**
 * @var $prev_target_date OsWpDateTime
 * @var $next_target_date OsWpDateTime
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php echo OsFormHelper::hidden_field('prev_target_date', $prev_target_date->format('Y-m-d')); ?>
<?php echo OsFormHelper::hidden_field('next_target_date', $next_target_date->format('Y-m-d')); ?>