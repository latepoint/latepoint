<?php
/* @var $job OsProcessJobModel */
/* @var $action_settings_html string */
/* @var $preview_html string */
/* @var $action_status_html string */
/* @var $action \LatePoint\Misc\ProcessAction */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="os-form-header">
	<h2><?php echo esc_html($action->get_nice_type_name()); ?></h2>
    <a href="#" class="latepoint-side-panel-close latepoint-side-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>
</div>
<div class="os-form-content">
	<?php echo $action_status_html; ?>
	<div class="action-preview-wrapper type-<?php echo esc_attr($action->type); ?>">
		<?php echo $preview_html; ?>
	</div>
</div>
<div class="os-form-buttons os-quick-form-buttons">
	<button type="button" data-os-after-call="reload_process_jobs_table" class="latepoint-btn latepoint-btn-primary latepoint-btn-lg latepoint-btn-block" data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['action_ids' => [$action->id], 'job_id' => $job->id], 'run_job_'.$job->id)); ?>" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('process_jobs', 'run_job'));?>">
		<?php if($action_status_html){ ?>
			<i class="latepoint-icon latepoint-icon-refresh-cw"></i>
			<span><?php esc_html_e('Run Again', 'latepoint'); ?></span>
		<?php }else{ ?>
			<i class="latepoint-icon latepoint-icon-play-circle"></i>
			<span><?php esc_html_e('Run Now', 'latepoint'); ?></span>
		<?php } ?>
	</button>
</div>