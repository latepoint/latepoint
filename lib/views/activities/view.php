<?php
/* @var $activity_id string */
/* @var $activity_name string */
/* @var $activity_type string */
/* @var $content_html string */
/* @var $meta_html string */
/* @var $status_html string */
/* @var $status string */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-form-header">
	<h2><?php echo esc_html($activity_name) ?></h2>
</div>
<div class="os-form-content">
	<?php if($status && $status_html){ ?>
	<div class="activity-status-wrapper status-<?php echo esc_attr($status); ?>">
		<div class="activity-status-content">
			<?php echo $status_html ?>
		</div>
	</div>
	<?php } ?>
	<div class="activity-preview-wrapper type-<?php echo esc_attr($activity_type); ?>">
		<div class="activity-preview-content-wrapper">
			<?php echo $meta_html ?>
			<?php echo $content_html; ?>
		</div>
	</div>
</div>
<div class="os-form-buttons">
	<button type="button" class="latepoint-btn latepoint-btn-danger"
          data-os-success-action="reload"
          data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['id' => $activity_id], 'destroy_activity_'.$activity_id)); ?>"
          data-os-prompt="<?php esc_attr_e('Are you sure you want to delete this activity record?', 'latepoint'); ?>"
	        data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('activities', 'destroy'));?>">
		<i class="latepoint-icon latepoint-icon-trash"></i>
		<span><?php esc_html_e('Delete', 'latepoint'); ?></span>
	</button>
</div>