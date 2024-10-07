<?php
/**
 * @var $current_step_code string
 * @var $booking OsBookingModel
 * @var $restrictions array
 * @var $presets array
 * @var $agents OsAgentModel[]
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="step-agents-w latepoint-step-content <?php echo ($booking->agent_id) ? 'is-hidden' : ''; ?>"
     data-step-code="<?php echo esc_attr($current_step_code); ?>"
     data-next-btn-label="<?php echo esc_attr(OsStepsHelper::get_next_btn_label_for_step($current_step_code)); ?>"
     data-clear-action="clear_step_agents">
	<?php
	do_action('latepoint_before_step_content', $current_step_code);
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'before');

	OsAgentHelper::generate_agents_list($agents);

	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'after');
	do_action('latepoint_after_step_content', $current_step_code);

	echo OsFormHelper::hidden_field('booking[agent_id]', $booking->agent_id, ['class' => 'latepoint_agent_id', 'skip_id' => true]);
	?>
</div>