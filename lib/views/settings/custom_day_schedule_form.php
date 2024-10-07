<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<form class="latepoint-lightbox-wrapper-form" action="" data-os-success-action="reload" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('settings', 'save_custom_day_schedule')); ?>">
	<?php wp_nonce_field('save_custom_day_schedule'); ?>
	<div class="latepoint-lightbox-heading">
		<h2><?php esc_html_e('Custom schedule', 'latepoint'); ?></h2>
	</div>
	<div class="latepoint-lightbox-content">
  	<div class="custom-day-schedule-w">
	  	<div class="custom-day-calendar" data-show-schedule="<?php echo ($day_off) ? 'no' : 'yes'; ?>" data-period-type="<?php echo ($chain_id) ? 'range' : 'single'; ?>" data-picking="start">
				<div class="custom-day-settings-w">
					<?php echo OsFormHelper::select_field('period_type', false, ['single' => __('Single Day', 'latepoint'), 'range' => __('Date Range', 'latepoint')], ($chain_id) ? 'range' : 'single', ['class' => 'period-type-selector']); ?>
					<?php echo OsFormHelper::hidden_field('chain_id', $chain_id); ?>
					<div class="start-day-input-w">
						<?php echo OsFormHelper::text_field('start_custom_date', false, ($date_is_preselected) ? $target_date->format('Y-m-d') : '', ['placeholder' => __('Pick a Start', 'latepoint'), 'theme' => 'simple']); ?>
					</div>
					<div class="end-day-input-w">
						<?php echo OsFormHelper::text_field('end_custom_date', false, ($chain_end_date) ? $chain_end_date->format('Y-m-d') : '', ['placeholder' => __('Pick an End', 'latepoint'), 'theme' => 'simple']); ?>
					</div>
				</div>
	  		<div class="custom-day-calendar-head">
					<h3 class="calendar-heading" 
							data-label-single="<?php esc_attr_e('Pick a Date', 'latepoint'); ?>"
							data-label-start="<?php esc_attr_e('Pick a Start Date', 'latepoint'); ?>"
							data-label-end="<?php esc_attr_e('Pick an End Date', 'latepoint'); ?>"><?php esc_html_e('Pick a Date', 'latepoint'); ?></h3>
	  			<?php echo OsFormHelper::select_field('custom_day_calendar_month', false, OsUtilHelper::get_months_for_select(), $target_date->format('n')); ?>
	  			<?php echo OsFormHelper::select_field('custom_day_calendar_year', false, [OsTimeHelper::today_date('Y'), OsTimeHelper::today_date('Y') + 1], $target_date->format('Y')); ?>
	  		</div>
	  		<div class="custom-day-calendar-month" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('calendars', 'load_monthly_calendar_days_only')); ?>">
	  			<?php OsCalendarHelper::generate_monthly_calendar_days_only($target_date->format('Y-m-d'), $date_is_preselected); ?>
	  		</div>
	  	</div>
	  	<div class="custom-day-schedule">
	  		<div class="custom-day-schedule-head">
					<h3><?php esc_html_e('Set Schedule', 'latepoint'); ?></h3>
				</div>
  			<div class="weekday-schedule-form active">
		      <?php 
		      $args = ['period_id' => false, 'agent_id' => $agent_id, 'service_id' => $service_id, 'location_id' => $location_id];
		      $work_periods = false;
		      $preselected_date = '';
		      if($day_off){
		      	$args['start_time'] = 0;
		      	$args['end_time'] = 0;
		      }elseif($date_is_preselected){
		      	$preselected_date = $target_date->format('Y-m-d');
		      	$work_periods = new OsWorkPeriodModel();
		      	$work_periods = $work_periods->where(['agent_id' => $agent_id, 
		      																				'service_id' => $service_id, 
		      																				'location_id' => $location_id, 
		      																				'custom_date' => $target_date->format('Y-m-d')])->get_results_as_models();
		      }
		      if($work_periods){
	      		$allow_remove = false;
	      		$existing_work_period_ids = [];
	      		foreach($work_periods as $work_period){
	      			echo OsWorkPeriodsHelper::generate_work_period_form(array('period_id' => $work_period->id, 
                                                                        'week_day' => $target_date->format('N'), 
                                                                        'is_active' => $work_period->is_active, 
                                                                        'agent_id' => $work_period->agent_id, 
                                                                        'service_id' => $work_period->service_id, 
                                                                        'location_id' => $work_period->location_id, 
                                                                        'start_time' => $work_period->start_time, 
                                                                        'custom_date' => $work_period->custom_date, 
                                                                        'end_time' => $work_period->end_time), $allow_remove);
	      			$allow_remove = true;
	      			$existing_work_periods_ids[] = $work_period->id;
	      		}
						echo OsFormHelper::hidden_field('existing_work_periods_ids', implode(',', $existing_work_periods_ids));

	      	}else{
		      	echo OsWorkPeriodsHelper::generate_work_period_form($args, false);
		      }
		      ?>
          <div class="ws-period-add" data- 
          data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['custom_date' => $preselected_date,
          																													'service_id' => $service_id, 
          																													'agent_id' => $agent_id, 
          																													'location_id' => $location_id])); ?>"
          data-os-before-after="before" 
          data-os-after-call="latepoint_init_work_period_form"
          data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('settings', 'load_work_period_form')); ?>">
            <div class="add-period-graphic-w">
              <div class="add-period-plus"><i class="latepoint-icon latepoint-icon-plus-square"></i></div>
            </div>
            <div class="add-period-label"><?php esc_html_e('Add another work period', 'latepoint'); ?></div>
          </div>
        </div>
	  	</div>
  	</div>
	</div>
	<div class="latepoint-lightbox-footer" <?php if(!$date_is_preselected) echo 'style="display: none;"'; ?>>
  	<button type="submit" class="latepoint-btn latepoint-btn-block latepoint-btn-lg latepoint-btn-outline latepoint-save-day-schedule-btn"><?php echo ($day_off) ? esc_html__('Set as Day Off', 'latepoint') : esc_html__('Save Schedule', 'latepoint'); ?></button>
	</div>
</form>