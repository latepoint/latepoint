<?php
/* @var $jobs OsProcessJobModel[] */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if($jobs){
  foreach ($jobs as $job): ?>
    <tr>
      <td><strong><?php echo esc_html(\LatePoint\Misc\ProcessEvent::get_event_name_for_type($job->get_original_process_attribute('event_type'))); ?></strong></td>
      <td>
	      <?php
	      $process_name = $job->get_original_process_attribute('name');
				$is_deleted = ($job->process->id != $job->process_id);
	      $process_name.= (!$is_deleted && ($job->process->name != $job->get_original_process_attribute('name'))) ? ' ['.__('Modified', 'latepoint').']' : '';
				$id_html = $is_deleted ? ' ['.__('Deleted', 'latepoint').']' : ' (ID:'.$job->process_id.')'; // deleted
	      echo '<a href="'.esc_url(OsRouterHelper::build_link(['processes', 'index'])).'" target="_blank">'.esc_html($process_name.$id_html).'</a>'; ?></td>
      <td><?php echo $job->get_link_to_object(); ?></td>
      <td><?php echo $job->get_actions_summary(); ?></td>
      <td>
	      <?php
	      if($job->status == LATEPOINT_JOB_STATUS_SCHEDULED){
					$atts = ' data-os-prompt="'.esc_attr__('Are you sure you want to cancel this scheduled job?', 'latepoint').'"
										data-os-params="'. esc_attr(OsUtilHelper::build_os_params(['id' => $job->id])). '"
										data-os-after-call="reload_process_jobs_table"
										data-os-action="'.esc_attr(OsRouterHelper::build_route_name('process_jobs', 'cancel')).'" ';
	      }else{
					$atts = '';
	      }
	      echo '<span class="os-column-status os-column-status-'.esc_attr($job->status).'" '.$atts.'">'.esc_html(OsProcessJobsHelper::get_nice_job_status_name($job->status)).'</span>';
				?>
      </td>
      <td>
        <?php echo esc_html($job->to_run_after_utc); ?>
      </td>
	    <td>
	      <?php
	      echo '<span class="in-table-time-left">'.OsTimeHelper::time_left_to_datetime($job->to_run_after_utc, new DateTimeZone('UTC')).'</span>';
        if($job->run_result){
					echo ' <a href="#" 
					data-os-params="' . esc_attr(http_build_query(['id' => $job->id])) . '" 
			    data-os-action="' . esc_attr(OsRouterHelper::build_route_name( 'process_jobs', 'view_job_run_result' )) . '" 
			    data-os-lightbox-classes="width-800"
			    data-os-after-call="latepoint_init_json_view"
			    data-os-output-target="side-panel"><i class="latepoint-icon latepoint-icon-file-text"></i></a>';
        }
				?>
      </td>
      <td>
        <a class="latepoint-link" data-os-after-call="reload_process_jobs_table" href="#" data-os-prompt="<?php esc_attr_e('Are you sure you want to run this job?', 'latepoint'); ?>" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('process_jobs', 'run_job')); ?>" data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['job_id' => $job->id], 'run_job_'.$job->id)); ?>">
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
      </td>
    </tr>

    <?php
  endforeach;
}
?>