<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
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
		<div class="os-row">
			<?php if ($default_fields_for_customer['first_name']['active']) echo OsFormHelper::text_field('customer[first_name]', __('First Name', 'latepoint'), $customer->first_name, array('validate' => $customer->get_validations_for_property('first_name'), 'class' => $default_fields_for_customer['first_name']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['first_name']['width'])); ?>
			<?php if ($default_fields_for_customer['last_name']['active']) echo OsFormHelper::text_field('customer[last_name]', __('Last Name', 'latepoint'), $customer->last_name, array('validate' => $customer->get_validations_for_property('last_name'), 'class' => $default_fields_for_customer['last_name']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['last_name']['width'])); ?>
			<?php if ($default_fields_for_customer['phone']['active']) echo OsFormHelper::phone_number_field('customer[phone]', __('Phone Number', 'latepoint'), $customer->phone, array('validate' => $customer->get_validations_for_property('phone'), 'class' => $default_fields_for_customer['phone']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['phone']['width'] . ' os-col-sm-12')); ?>
			<?php echo OsFormHelper::text_field('customer[email]', __('Email Address', 'latepoint'), $customer->email, array('validate' => $customer->get_validations_for_property('email'), 'class' => 'required'), array('class' => $default_fields_for_customer['email']['width'] . ' os-col-sm-12')); ?>
			<?php if (OsSettingsHelper::is_on('steps_require_setting_password') && !OsAuthHelper::is_customer_logged_in() && ($customer->is_new_record() || $customer->is_guest)) {
				echo OsFormHelper::password_field('customer[password]', __('Password', 'latepoint'), '', array('class' => 'required'), array('class' => 'os-col-6'));
				echo OsFormHelper::password_field('customer[password_confirmation]', __('Confirm Password', 'latepoint'), '', array('class' => 'required'), array('class' => 'os-col-6'));
			} ?>
			<?php if ($default_fields_for_customer['notes']['active']) echo OsFormHelper::textarea_field('customer[notes]', __('Add Comments', 'latepoint'), $customer->notes, array('validate' => $customer->get_validations_for_property('notes'), 'class' => $default_fields_for_customer['notes']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['notes']['width'])); ?>
			<?php do_action('latepoint_booking_steps_contact_after', $customer, $booking); ?>
		</div>
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