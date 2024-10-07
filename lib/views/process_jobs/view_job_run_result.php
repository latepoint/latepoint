<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

/* @var $process_name string */
/* @var $content_html string */
/* @var $meta_html string */
/* @var $status_html string */
/* @var $status string */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="os-form-header">
	<h2><?php echo esc_html($process_name) ?></h2>
    <a href="#" class="latepoint-side-panel-close latepoint-side-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>
</div>
<div class="os-form-content">
	<div class="activity-preview-wrapper">
        <?php echo $meta_html ?>
        <div class="activity-status-wrapper status-<?php echo esc_attr($status); ?>">
            <div class="activity-status-content">
                <?php echo $status_html ?>
            </div>
        </div>
		<div class="activity-preview-content-wrapper">
			<?php echo $content_html; ?>
		</div>
	</div>
</div>
<div class="os-form-buttons os-quick-form-buttons">
    <a class="latepoint-btn latepoint-btn-block latepoint-btn-lg" data-os-after-call="reload_process_jobs_table" href="#" data-os-prompt="<?php esc_attr_e('Are you sure you want to run this job?', 'latepoint'); ?>" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('process_jobs', 'run_job')); ?>" data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['job_id' => $job->id], 'run_job_'.$job->id)); ?>">
      <?php if(in_array($job->status, [LATEPOINT_JOB_STATUS_COMPLETED, LATEPOINT_JOB_STATUS_ERROR])){ ?>
        <i class="latepoint-icon latepoint-icon-refresh-cw"></i>
        <span><?php esc_html_e('Run Again','latepoint'); ?></span>
      <?php }elseif($job->status == LATEPOINT_JOB_STATUS_SCHEDULED){ ?>
        <i class="latepoint-icon latepoint-icon-play-circle"></i>
        <span><?php esc_html_e('Run Now','latepoint'); ?></span>
      <?php }else{ ?>
        <i class="latepoint-icon latepoint-icon-play-circle"></i>
        <span><?php esc_html_e('Run','latepoint'); ?></span>
      <?php } ?>
    </a>
</div>
