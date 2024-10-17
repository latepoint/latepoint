<?php
/**
 * @var $bookings OsBookingModel[]
 * @var $calendar_start OsWpDateTime
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php  if(!empty($bookings)){
	$loop_year = $calendar_start->format('Y');
	$loop_month = $calendar_start->format('n');
	$loop_day = false;
	$total_locations = OsLocationHelper::count_locations(true);
	?>
	<div class="list-upcoming-bookings-w">
		<?php
		$first_booking_start_datetime = $bookings[0]->get_start_datetime_object();
		if($first_booking_start_datetime->format('Y-n') != $loop_year.'-'.$loop_month) echo '<div class="no-upcoming-bookings">'.esc_html__('No bookings', 'latepoint').'</div>';
		?>
		<?php foreach($bookings as $booking){ ?>
			<?php
			$booking_start_datetime = $booking->get_start_datetime_object();
			if($loop_day != $booking_start_datetime->format('d')){
				$loop_day = $booking_start_datetime->format('d');
				$is_new_day = true;
			}else{
				$is_new_day = false;
			}
			if($booking_start_datetime->format('Y') > $loop_year){
				for($m = $loop_month+1; $m < 12; $m++){
					echo '<div class="upcoming-bookings-month">'.esc_html(OsUtilHelper::get_month_name_by_number($m)).'</div>';
					echo '<div class="no-upcoming-bookings">'.esc_html__('No bookings', 'latepoint').'</div>';
				}
				$loop_month = 1;
				for($y = $loop_year + 1; $y < $booking_start_datetime->format('Y'); $y++){
					echo '<div class="upcoming-bookings-year">'.esc_html($y).'</div>';
					// loop months
					for($m=$loop_month+1;$m<=12;$m++){
						echo '<div class="upcoming-bookings-month">'.esc_html(OsUtilHelper::get_month_name_by_number($m)).'</div>';
						echo '<div class="no-upcoming-bookings">'.esc_html__('No bookings', 'latepoint').'</div>';
					}
					$loop_month = 1;
				}
				$loop_year = $booking_start_datetime->format('Y');
				echo '<div class="upcoming-bookings-year">'.esc_html($loop_year).'</div>';
			}
			if($booking_start_datetime->format('n') > $loop_month){
				for($m = $loop_month + 1; $m < $booking_start_datetime->format('n'); $m++){
					echo '<div class="upcoming-bookings-month">'.esc_html(OsUtilHelper::get_month_name_by_number($m)).'</div>';
					echo '<div class="no-upcoming-bookings">'.esc_html__('No bookings', 'latepoint').'</div>';
				}
				$loop_month = $booking_start_datetime->format('n');
				echo '<div class="upcoming-bookings-month">'.esc_html(OsUtilHelper::get_month_name_by_number($loop_month)).'</div>';
			}
			?>
			<?php $max_capacity = OsServiceHelper::get_max_capacity($booking->service); ?>
			<div class="upcoming-booking <?php echo ($is_new_day) ? 'is-new-day' : ''; ?>" <?php echo ($max_capacity > 1) ? OsBookingHelper::group_booking_btn_html($booking->id) : OsBookingHelper::quick_booking_btn_html($booking->id); ?>>
				<div class="booking-main-info">
					<div class="booking-color-elem" style="background-color: <?php echo esc_attr($booking->service->bg_color); ?>"></div>
					<div class="booking-fancy-date">
						<div class="fancy-day"><?php echo esc_html($booking_start_datetime->format('d')); ?></div>
						<div class="fancy-month"><?php echo esc_html(OsUtilHelper::get_month_name_by_number($booking_start_datetime->format('n'),true)); ?></div>
					</div>
					<div class="booking-main-info-i">
			      <div class="avatar-w" style="background-image: url(<?php echo esc_url($booking->agent->get_avatar_url()); ?>);">
			        <div class="agent-info-tooltip"><?php echo esc_html($booking->agent->full_name); ?></div>
			      </div>
						<div class="booking-date-w">
							<div class="booking-service-name"><?php echo esc_html($booking->service->name); ?></div>
							<div class="booking-date-i">
								<div class="booking-date"><?php echo esc_html($booking->get_nice_start_date(true)).', '; ?></div>
								<div class="booking-time"><?php echo esc_html($booking->get_nice_start_time()); ?>,</div>
								<div class="booking-time-left">
                                    <?php
                                    // translators: %s is time left
                                    echo sprintf(esc_html__('in %s', 'latepoint'), $booking->time_left); ?>
                                </div>
								<?php if($total_locations > 1) echo '<div class="booking-location">'.esc_html($booking->location->name).'</div>'; ?>
								<div class="booking-attendees">
									<?php
									if($max_capacity > 1) {
										$total_attendees = $booking->total_attendees;
                                        // translators: %1$d is number of booked appointments
                                        // translators: %2$d is total available
										echo '<div class="booked-count-label">'.esc_html(sprintf(__('Booked %1$d of %2$d', 'latepoint'), $total_attendees, $max_capacity)).'</div>';
										echo '<div class="booked-percentage">
											<div class="booked-bar" style="width: '.esc_attr(min(100, round($total_attendees / $max_capacity * 100))).'%;"></div>
										</div>';
									}else{
										echo '<div class="booking-attendee">';
										echo '<div class="avatar-w" style="background-image: url('.esc_url($booking->customer->get_avatar_url()).');"></div>';
										echo '<div class="customer-name">'.esc_html($booking->customer->full_name).'</div>';
										echo '</div>';
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<div class="booking-link">
						<i class="latepoint-icon latepoint-icon-arrow-right"></i>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
<?php }else{ ?>
	<div class="no-results-w">
	  <div class="icon-w"><i class="latepoint-icon latepoint-icon-book"></i></div>
	  <h2><?php esc_html_e('No Upcoming Appointments', 'latepoint'); ?></h2>
	  <a href="#" <?php echo OsOrdersHelper::quick_order_btn_html(); ?> class="latepoint-btn">
	    <i class="latepoint-icon latepoint-icon-plus-square"></i>
	    <span><?php esc_html_e('Create Appointment', 'latepoint'); ?></span>
	  </a>
	</div>
<?php } ?>