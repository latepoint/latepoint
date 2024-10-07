<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-settings-w os-form-w">
	<form action="" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('settings', 'update_work_periods')); ?>">
		<?php wp_nonce_field('update_work_periods'); ?>
		<div class="white-box">
			<div class="white-box-header">
				<div class="os-form-sub-header"><h3><?php esc_html_e('General Weekly Schedule', 'latepoint'); ?></h3></div>
			</div>
			<div class="white-box-content">
				<div class="weekday-schedules-w">
					<?php OsWorkPeriodsHelper::generate_work_periods([], new \LatePoint\Misc\Filter()); ?>
				</div>
				<div class="os-form-buttons">
					<?php echo OsFormHelper::button('submit', __('Save Weekly Schedule', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
				</div>
			</div>
		</div>
	</form>
	<div class="white-box">
		<div class="white-box-header">
			<div class="os-form-sub-header"><h3><?php esc_html_e('Days With Custom Schedules', 'latepoint'); ?></h3></div>
		</div>
		<div class="white-box-content">
			<?php OsWorkPeriodsHelper::generate_days_with_custom_schedule(); ?>
		</div>
	</div>
	<div class="white-box">
		<div class="white-box-header">
			<div class="os-form-sub-header"><h3><?php esc_html_e('Holidays & Days Off', 'latepoint'); ?></h3></div>
		</div>
		<div class="white-box-content">
			<?php OsWorkPeriodsHelper::generate_off_days(); ?>
		</div>
	</div>
	<div class="full-screen-year-calendar-w">
		<div class="full-screen-year-calendar">
			<div class="fsy-header">
				<h2><?php esc_html_e('Select Date', 'latepoint'); ?></h2>
			</div>
			<div class="full-screen-year-calendar-months">
				<?php $year = OsTimeHelper::today_date('Y') + 1; ?>
				<?php for ($i = 1; $i <= 12; $i++) {
					echo '<div class="fsy-month">';
					echo '<div class="fsy-month-name">' . esc_html(OsUtilHelper::get_month_name_by_number($i)) . '</div>';
					$target_date = new OsWpDateTime($year . '-' . $i . '-01');
					$calendar_start = clone $target_date;
					$calendar_start->modify('first day of this month');
					$calendar_end = clone $target_date;
					$calendar_end->modify('last day of this month');

					$weekday_for_first_day_of_month = $calendar_start->format('N') - 1;
					$weekday_for_last_day_of_month = $calendar_end->format('N') - 1;

					if ($weekday_for_first_day_of_month > 0) {
						$calendar_start->modify('-' . $weekday_for_first_day_of_month . ' days');
					}

					if ($weekday_for_last_day_of_month < 7) {
						$days_to_add = 7 - $weekday_for_last_day_of_month;
						$calendar_end->modify('+' . $days_to_add . ' days');
					}

					echo '<div class="os-monthly-calendar-days-w" data-calendar-year="' . esc_attr($target_date->format('Y')) . '" data-calendar-month="' . esc_attr($target_date->format('n')) . '" data-calendar-month-label="' . esc_attr(OsUtilHelper::get_month_name_by_number($target_date->format('n'))) . '">
			    				<div class="os-monthly-calendar-days">';
					for ($day_date = clone $calendar_start; $day_date <= $calendar_end; $day_date->modify('+1 day')) {
						$is_today = ($day_date->format('Y-m-d') == OsTimeHelper::today_date('Y-m-d')) ? true : false;
						$is_day_in_past = ($day_date->format('Y-m-d') < OsTimeHelper::today_date('Y-m-d')) ? true : false;
						$day_class = 'os-day os-day-current week-day-' . strtolower($day_date->format('N'));
						if ($is_today) $day_class .= ' os-today';
						if ($is_day_in_past) $day_class .= ' os-day-passed'; ?>
					<div class="<?php echo esc_attr($day_class); ?>" data-date="<?php echo esc_attr($day_date->format('Y-m-d')); ?>">
						<div class="os-day-box">
							<div class="os-day-number"><?php echo esc_html($day_date->format('j')); ?></div>
						</div>
						</div><?php
					}
					echo '</div></div>';
					echo '</div>';
				} ?>
			</div>
		</div>
	</div>
	<?php

	?>
</div>