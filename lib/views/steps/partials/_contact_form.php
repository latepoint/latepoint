<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-row">
  <?php if($default_fields_for_customer['first_name']['active']) echo OsFormHelper::text_field('customer[first_name]', __('First Name', 'latepoint'), $customer->first_name, array('validate' => $customer->get_validations_for_property('first_name'), 'class' => $default_fields_for_customer['first_name']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['first_name']['width'])); ?>
  <?php if($default_fields_for_customer['last_name']['active'])echo OsFormHelper::text_field('customer[last_name]', __('Last Name', 'latepoint'), $customer->last_name, array('validate' => $customer->get_validations_for_property('last_name'), 'class' => $default_fields_for_customer['last_name']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['last_name']['width'])); ?>
  <?php if($default_fields_for_customer['phone']['active'])echo OsFormHelper::phone_number_field('customer[phone]', __('Phone Number', 'latepoint'), $customer->phone, array('validate' => $customer->get_validations_for_property('phone'), 'class' => $default_fields_for_customer['phone']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['phone']['width'].' os-col-sm-12')); ?>
  <?php echo OsFormHelper::text_field('customer[email]', __('Email Address', 'latepoint'), $customer->email, array('validate' => $customer->get_validations_for_property('email'), 'class' => 'required'), array('class' => $default_fields_for_customer['email']['width'].' os-col-sm-12')); ?>
  <?php if(OsSettingsHelper::is_on('steps_require_setting_password') && !OsAuthHelper::is_customer_logged_in() && ($customer->is_new_record() || $customer->is_guest)){
		echo OsFormHelper::password_field('customer[password]', __('Password', 'latepoint'), '', array('class' => 'required'), array('class' => 'os-col-6'));
		echo OsFormHelper::password_field('customer[password_confirmation]', __('Confirm Password', 'latepoint'), '', array('class' => 'required'), array('class' => 'os-col-6'));
  } ?>
  <?php if($default_fields_for_customer['notes']['active']) echo OsFormHelper::textarea_field('customer[notes]', __('Add Comments', 'latepoint'), $customer->notes, array('validate' => $customer->get_validations_for_property('notes'), 'class' => $default_fields_for_customer['notes']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['notes']['width'])); ?>
  <?php do_action('latepoint_booking_steps_contact_after', $customer, $booking); ?>
</div>