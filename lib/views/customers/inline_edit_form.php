<?php
/* @var $order OsOrderModel */
/* @var $selected_customer OsCustomerModel */
/* @var $default_fields_for_customer array */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// This form is used inside of a quick order form, when you edit the customer
?>
<div class="os-row">
  <div class="os-col-6">
    <?php if($default_fields_for_customer['first_name']['active']) echo OsFormHelper::text_field('customer[first_name]', __('First Name', 'latepoint'), $selected_customer->first_name, ['theme' => 'simple', 'validate' => $selected_customer->get_validations_for_property('first_name')]); ?>
  </div>
  <div class="os-col-6">
    <?php if($default_fields_for_customer['last_name']['active']) echo OsFormHelper::text_field('customer[last_name]', __('Last Name', 'latepoint'), $selected_customer->last_name, ['theme' => 'simple', 'validate' => $selected_customer->get_validations_for_property('last_name')]); ?>
  </div>
</div>
<div class="os-row">
  <div class="os-col-12">
    <?php echo OsFormHelper::text_field('customer[email]', __('Email Address', 'latepoint'), $selected_customer->email, ['theme' => 'simple', 'validate' => $selected_customer->get_validations_for_property('email')]); ?>
  </div>
</div>
<div class="os-row">
  <div class="os-col-12">
    <?php if($default_fields_for_customer['phone']['active']) echo OsFormHelper::phone_number_field('customer[phone]', __('Telephone Number', 'latepoint'), $selected_customer->phone, ['theme' => 'simple', 'validate' => $selected_customer->get_validations_for_property('phone')] ); ?>
  </div>
</div>
<div class="os-row">
  <div class="os-col-12">
    <?php if($default_fields_for_customer['notes']['active']) echo OsFormHelper::textarea_field('customer[notes]', __('Customer Notes', 'latepoint'), $selected_customer->notes, ['rows' => 1, 'theme' => 'simple', 'validate' => $selected_customer->get_validations_for_property('notes'), 'placeholder' => '']); ?>
  </div>
</div>
<div class="os-row">
  <div class="os-col-12">
    <?php echo OsFormHelper::textarea_field('customer[admin_notes]', __('Notes only visible to admins', 'latepoint'), $selected_customer->admin_notes, ['rows' => 1, 'theme' => 'simple', 'placeholder' => '']); ?>
  </div>
</div>
<?php
/**
 * Content that goes after inline customer edit form on an order form
 *
 * @since 5.0.0
 * @hook latepoint_customer_inline_edit_form_after
 *
 * @param {OsCustomerModel} $customer Customer object that is being edited
 */
do_action('latepoint_customer_inline_edit_form_after', $selected_customer); ?>

<?php echo OsFormHelper::hidden_field('order[customer_id]', $selected_customer->id); ?>