<?php
/* @var $action_settings_html string */
/* @var $preview_html string */
/* @var $action \LatePoint\Misc\ProcessAction */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="os-form-header">
	<h2><?php echo esc_html($action->get_nice_type_name().' '.__('Test', 'latepoint')); ?></h2>
</div>
<div class="action-settings-wrapper">
    <?php echo $action_settings_html ?>
</div>
<div class="os-form-content">
	<div class="action-preview-wrapper type-<?php echo esc_attr($action->type); ?>">
		<?php echo $preview_html; ?>
	</div>
</div>
<div class="os-form-buttons right-aligned">
	<button type="button" class="latepoint-btn latepoint-btn-primary latepoint-run-action-btn" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name('processes', 'action_test_run'));?>">
		<i class="latepoint-icon latepoint-icon-play-circle"></i>
		<span><?php esc_html_e('Run Now', 'latepoint'); ?></span>
	</button>
</div>