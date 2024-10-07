<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-dashboard-row">
	<div class="os-dashboard-column">
		<?php echo $widget_daily_bookings_chart; ?>
	</div>
	<div class="os-dashboard-column os-fit">
		<?php echo $widget_upcoming_appointments; ?>
	</div>
</div>
<div class="os-dashboard-row">
	<div class="os-dashboard-column">
		<?php echo $widget_bookings_and_availability_timeline; ?>
	</div>
</div>