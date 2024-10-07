<?php
/**
 * @var $current_step_code string
 * @var $booking OsBookingModel
 * @var $restrictions array
 * @var $presets array
 * @var $customer OsCustomerModel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="step-customer-w latepoint-step-content" data-step-code="<?php echo esc_attr($current_step_code); ?>"
     data-next-btn-label="<?php echo esc_attr(OsStepsHelper::get_next_btn_label_for_step($current_step_code)); ?>">
	<?php
	do_action('latepoint_before_step_content', $current_step_code);
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'before');
	?>
	<?php if ($customer->id) { ?>
		<div class="step-customer-logged-in-header-w">
			<div><?php esc_html_e('Contact Information', 'latepoint'); ?></div>
			<span><?php esc_html_e('Not You?', 'latepoint'); ?></span><a
				data-btn-action="<?php echo esc_attr(OsRouterHelper::build_route_name('auth', 'logout_customer')); ?>" href="#"
				class="step-customer-logout-btn"><?php esc_html_e('Logout', 'latepoint'); ?></a>
		</div>
		<?php include('partials/_contact_form.php'); ?>
	<?php } else { ?>
		<div class="os-step-tabs-w">
			<?php if (OsSettingsHelper::get_settings_value('steps_hide_login_register_tabs') != 'on') { ?>
				<div class="os-step-tabs">
					<div class="os-step-tab active"
					     data-target=".os-step-new-customer-w"><?php esc_html_e('New Customer', 'latepoint'); ?></div>
					<div class="os-step-tab"
					     data-target=".os-step-existing-customer-login-w"><?php esc_html_e('Already have an account?', 'latepoint'); ?></div>
				</div>
			<?php } ?>
			<div class="os-step-tab-content os-step-new-customer-w">
				<?php include('partials/_contact_form.php'); ?>
			</div>
			<?php if (OsSettingsHelper::get_settings_value('steps_hide_login_register_tabs') != 'on') { ?>
				<div class="os-step-tab-content os-step-existing-customer-login-w" style="display: none;">
					<div class="os-row">
						<?php echo OsFormHelper::text_field('customer_login[email]', __('Your Email Address', 'latepoint'), '', array('class' => 'required'), array('class' => 'os-col-12')); ?>
						<?php echo OsFormHelper::password_field('customer_login[password]', __('Your Password', 'latepoint'), '', array('class' => 'required'), array('class' => 'os-col-12')); ?>
					</div>
					<div class="os-form-buttons os-flex os-space-between">
						<a data-btn-action="<?php echo esc_attr(OsRouterHelper::build_route_name('auth', 'login_customer')); ?>" href="#"
						   class="latepoint-btn latepoint-btn-primary step-login-existing-customer-btn"><?php esc_html_e('Log Me In', 'latepoint'); ?></a>
						<a href="#" class="latepoint-btn latepoint-btn-primary latepoint-btn-link step-forgot-password-btn"
						   data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('customer_cabinet', 'request_password_reset_token')); ?>"
						   data-os-output-target=".os-password-reset-form-holder"
						   data-os-after-call="latepoint_reset_password_from_booking_init"
						   data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['from_booking' => true])); ?>"><?php esc_html_e('Forgot Password?', 'latepoint'); ?></a>
					</div>
				</div>
				<div class="os-password-reset-form-holder"></div>
			<?php } ?>
		</div>
        <?php do_action('latepoint_after_customer_login_form'); ?>
	<?php } ?>
	<?php
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'after');
	do_action('latepoint_after_step_content', $current_step_code);
	?>
</div>