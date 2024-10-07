<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<h3 class="os-wizard-sub-header">
    <?php
    // translators: %1$d is current step, %2$d is total steps
    echo esc_html(sprintf(__('Step %1$d of %2$d', 'latepoint'), $current_step_number, 3)); ?>
</h3>
<h2 class="os-wizard-header"><?php esc_html_e('Set Your Work Hours', 'latepoint'); ?></h2>
<div class="os-wizard-desc"><?php esc_html_e('These will be your default work hours for all your locations, agents and services. You can set custom hours for each agent, service or location in LatePoint admin panel.', 'latepoint'); ?></div>
<div class="os-wizard-step-content-i">
  <form class="weekday-schedules-w">
      <?php OsWorkPeriodsHelper::generate_work_periods([], new \LatePoint\Misc\Filter()); ?>
  </form>
</div>