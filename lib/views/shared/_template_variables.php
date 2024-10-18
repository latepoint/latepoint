<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="available-vars-block">
  <h4><?php esc_html_e('Direct URLs to Manage Appointment', 'latepoint'); ?></h4>
  <ul>
    <li><span class="var-label"><?php esc_html_e('For Agent:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{manage_booking_url_agent}}</span></li>
    <li><span class="var-label"><?php esc_html_e('For Customer:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{manage_booking_url_customer}}</span></li>
  </ul>
  <h4><?php esc_html_e('Order', 'latepoint'); ?></h4>
  <ul>
    <li><span class="var-label"><?php esc_html_e('Order ID#:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_id}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Order Confirmation Code:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_confirmation_code}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Subtotal:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_subtotal}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Total:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_total}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Payments & Credits:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_payments_total}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Balance Due:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_balance_due_total}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Order Status:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_status}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Order Fulfillment Status:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_fulfillment_status}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Transactions Breakdown', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_transactions_breakdown}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Order Summary Breakdown:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{order_summary_breakdown}}</span></li>
    <?php do_action('latepoint_available_vars_order'); ?>
  </ul>
  <h4><?php esc_html_e('Appointment', 'latepoint'); ?></h4>
  <ul>
    <li><span class="var-label"><?php esc_html_e('Appointment ID#:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{booking_id}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Confirmation Code:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{booking_code}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Service Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{service_name}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Service Category:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{service_category}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Start Date:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{start_date}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Start Time:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{start_time}}</span></li>
    <li><span class="var-label"><?php esc_html_e('End Time:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{end_time}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Service Duration:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{booking_duration}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Status:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{booking_status}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Booking Price:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{booking_price}}</span></li>
    <?php do_action('latepoint_available_vars_booking'); ?>
  </ul>
</div>
<div class="available-vars-block">
  <h4><?php esc_html_e('Customer', 'latepoint'); ?></h4>
  <ul>
    <li><span class="var-label"><?php esc_html_e('Full Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{customer_full_name}}</span></li>
    <li><span class="var-label"><?php esc_html_e('First Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{customer_first_name}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Last Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{customer_last_name}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Email Address:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{customer_email}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Phone:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{customer_phone}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Comments:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{customer_notes}}</span></li>
    <?php do_action('latepoint_available_vars_customer'); ?>
  </ul>
</div>
<div class="available-vars-block">
  <h4><?php esc_html_e('Agent', 'latepoint'); ?></h4>
  <ul>
    <li><span class="var-label"><?php esc_html_e('First Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{agent_first_name}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Last Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{agent_last_name}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Full Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{agent_full_name}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Display Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{agent_display_name}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Email:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{agent_email}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Phone:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{agent_phone}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Additional Emails:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{agent_additional_emails}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Additional Phone Numbers:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{agent_additional_phones}}</span></li>
  </ul>
</div>
<div class="available-vars-block">
  <h4><?php esc_html_e('Location', 'latepoint'); ?></h4>
  <ul>
    <li><span class="var-label"><?php esc_html_e('Name:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{location_name}}</span></li>
    <li><span class="var-label"><?php esc_html_e('Full Address:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{location_full_address}}</span></li>
  </ul>
</div>
<div class="available-vars-block">
  <h4><?php esc_html_e('Transaction', 'latepoint'); ?></h4>
  <ul>
		<li><span class="var-label"><?php esc_html_e('Token:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy"> {{transaction_token}}</span></li>
		<li><span class="var-label"><?php esc_html_e('Amount:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy"> {{transaction_amount}}</span></li>
		<li><span class="var-label"><?php esc_html_e('Processor:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy"> {{transaction_processor}}</span></li>
		<li><span class="var-label"><?php esc_html_e('Payment Method:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy"> {{transaction_payment_method}}</span></li>
		<li><span class="var-label"><?php esc_html_e('Type:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy"> {{transaction_kind}}</span></li>
		<li><span class="var-label"><?php esc_html_e('Status:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy"> {{transaction_status}}</span></li>
		<li><span class="var-label"><?php esc_html_e('Notes:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy"> {{transaction_notes}}</span></li>
		<li><span class="var-label"><?php esc_html_e('Payment Portion:', 'latepoint'); ?></span> <span class="var-code os-click-to-copy">{{transaction_payment_portion}}</span></li>
    <?php do_action('latepoint_available_vars_transaction'); ?>
  </ul>
</div>
<?php include('_business_variables.php'); ?>
<?php do_action('latepoint_available_vars_after'); ?>