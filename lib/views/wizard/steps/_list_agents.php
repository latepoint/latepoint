<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="agent-boxes">
	<?php
	foreach($agents as $agent){ ?>
		<div class="agent-box-w wizard-add-edit-item-trigger" data-id="<?php echo esc_attr($agent->id); ?>" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('wizard', 'add_or_edit_agent')); ?>">
			<div class="agent-edit-icon"><i class="latepoint-icon latepoint-icon-edit-3"></i></div>
			<div class="agent-avatar" style="background-image: url(<?php echo esc_url($agent->avatar_url); ?>)"></div>
			<div class="agent-name"><?php echo esc_html($agent->full_name); ?></div>
			<div class="agent-remove-trigger" 
								data-os-pass-this="yes" 
								data-os-prompt="<?php echo esc_attr__('Are you sure you want to remove this agent?', 'latepoint'); ?>"
				        data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['id' => $agent->id], 'destroy_agent_'.$agent->id)); ?>"
				        data-os-after-call="latepoint_remove_agent_box" 
				        data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('agents', 'destroy')); ?>">
				<i class="latepoint-icon latepoint-icon-trash-2"></i>
			</div>
		</div>
		<?php
	} ?>
	<div class="add-agent-box wizard-add-edit-item-trigger" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('wizard', 'add_or_edit_agent')); ?>">
		<div class="add-agent-graphic-w">
			<div class="add-agent-graphic">
				<div class="add-agent-head"></div>
				<div class="add-agent-body"></div>
			</div>
			<div class="add-agent-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div>
		</div>
		<div class="add-agent-label"><?php esc_html_e('Add Agent', 'latepoint'); ?></div>
	</div>
</div>