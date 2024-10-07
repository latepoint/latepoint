<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-wizard-setup-w step-<?php echo esc_attr($current_step_code); ?>">
	<a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('dashboard', 'index'))); ?>" class="os-wizard-close-trigger"><span><?php esc_html_e('Skip setup', 'latepoint'); ?></span><i class="latepoint-icon latepoint-icon-x"></i></a>
	<div class="os-wizard-setup-i">
		<div class="os-wizard-step-content-w">
			<div class="os-wizard-step-content">
				<?php include($step_file_to_include); ?>
			</div>
	    <div class="os-wizard-footer">
	    	<?php echo OsFormHelper::hidden_field('current_step_code', $current_step_code, ['id' => 'wizard_current_step_code']); ?>
	      <a href="#" data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('wizard', 'prev_step')); ?>" class="latepoint-btn latepoint-btn-lg latepoint-btn-white os-wizard-prev-btn os-wizard-trigger-prev-btn" style="display: none;"><i class="latepoint-icon latepoint-icon-arrow-left"></i> <span><?php esc_html_e('Back', 'latepoint'); ?></span></a>
	      <a href="#" data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('wizard', 'next_step')); ?>" class="latepoint-btn latepoint-btn-lg os-wizard-next-btn  os-wizard-trigger-next-btn"><span><?php esc_html_e('Next', 'latepoint'); ?></span> <i class="latepoint-icon latepoint-icon-arrow-right"></i></a>
	    </div>
		</div>
	</div>
</div>