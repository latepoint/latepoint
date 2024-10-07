<?php
/* @var $process OsProcessModel */
/* @var $action_settings_html string */
/* @var $action \LatePoint\Misc\ProcessAction */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="os-form-header">
	<h2><?php echo esc_html($process->name.' '.__('Test', 'latepoint')); ?></h2>
</div>
<div class="action-settings-wrapper">
    <?php echo esc_html($action_settings_html); ?>
</div>
<div class="os-form-content">
	<div class="action-preview-wrapper">
		<h3><?php esc_html_e('Actions to trigger:', 'latepoint'); ?></h3>
		<div class="actions-to-run-wrapper">
		<?php
		if(!empty($process->actions)){
			foreach($process->actions as $action){
				if($action->status != LATEPOINT_STATUS_ACTIVE) continue;
				echo '<div class="action-to-run" data-id="'.esc_attr($action->id).'">'.OsFormHelper::toggler_field('action['.$action->id.']', $action->get_nice_type_name(), true).'</div>';
			}
		}else{
			echo '<div class="latepoint-message latepoint-message-subtle">'.esc_html__('No actions were created for this process. Create actions first in order to test them.', 'latepoint').'</div>';
		}
		?>
		</div>
	</div>
</div>
<?php if(!empty($process->actions)){ ?>
<div class="os-form-buttons">
	<button type="button" class="latepoint-btn latepoint-btn-primary latepoint-run-process-btn" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('processes', 'test_run'));?>">
		<i class="latepoint-icon latepoint-icon-play-circle"></i>
		<span><?php esc_html_e('Run Now', 'latepoint'); ?></span>
	</button>
</div>
<?php } ?>