<?php
/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

/* @var $transaction OsTransactionModel */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<div class="quick-add-transaction-box-w">
  <div class="quick-add-transaction-box">
	  <div class="quick-add-transaction-box-header">
		  <div class="transaction-fold-wrapper">
		    <div class="latepoint-icon latepoint-icon-chevron-down"></div>
			  <div><?php echo ($transaction->is_new_record() ? esc_html__('New Transaction', 'latepoint') : esc_html__('Edit Transaction', 'latepoint')); ?></div>
		  </div>
		  <?php if($transaction->is_new_record()){ ?>
			  <a href="#" class="trigger-remove-transaction-btn form-close-btn"><i class="latepoint-icon latepoint-icon-trash"></i></a>
		  <?php }else{ ?>
		    <a href="#" data-os-prompt="<?php esc_attr_e('Are you sure you want to delete this transaction?', 'latepoint'); ?>"
		       data-os-after-call="latepoint_transaction_removed"
		       data-os-pass-this="yes"
		       data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('transactions', 'destroy')); ?>"
		       data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params(['id' => $real_or_rand_id], 'destroy_transaction_'.$transaction->id)); ?>"
		       class="form-close-btn"><i class="latepoint-icon latepoint-icon-trash"></i></a>
		  <?php } ?>
	  </div>
	  <div class="quick-add-transaction-box-content">
    <div class="os-row">
      <div class="os-col-lg-6">
        <?php echo OsFormHelper::money_field('transactions['.$real_or_rand_id.'][amount]', __('Amount', 'latepoint'), $transaction->amount, ['placeholder' => __('Amount', 'latepoint'), 'theme' => 'simple'] ); ?>
      </div>
      <div class="os-col-lg-6">
        <?php echo OsFormHelper::text_field('transactions['.$real_or_rand_id.'][created_at]', __('Date', 'latepoint'), $transaction->formatted_created_date('Y-m-d', OsTimeHelper::today_date()), ['placeholder' => __('Date', 'latepoint'), 'theme' => 'simple']); ?>
      </div>
    </div>
    <div class="os-row">
      <div class="os-col-12">
        <?php echo OsFormHelper::text_field('transactions['.$real_or_rand_id.'][token]', __('Confirmation Code', 'latepoint'), $transaction->token, ['placeholder' => __('Confirmation Code', 'latepoint'), 'theme' => 'simple'] ); ?>
      </div>
    </div>
    <div class="os-row">
      <div class="os-col-6">
        <?php echo OsFormHelper::select_field('transactions['.$real_or_rand_id.'][payment_portion]', __('Payment Portion', 'latepoint'), OsPaymentsHelper::get_payment_portions_list(), $transaction->payment_portion, false ); ?>
      </div>
      <div class="os-col-6">
        <?php echo OsFormHelper::select_field('transactions['.$real_or_rand_id.'][kind]', __('Type', 'latepoint'), OsPaymentsHelper::get_list_of_transaction_kinds(), $transaction->kind, false ); ?>
      </div>
    </div>
    <div class="os-row">
      <div class="os-col-lg-6">
        <?php echo OsFormHelper::select_field('transactions['.$real_or_rand_id.'][processor]',__('Payment Processor', 'latepoint'), OsPaymentsHelper::get_payment_processors_for_select(false, true), $transaction->processor , false); ?>
      </div>
      <div class="os-col-lg-6">
        <?php echo OsFormHelper::select_field('transactions['.$real_or_rand_id.'][payment_method]',__('Payment Method', 'latepoint'), OsPaymentsHelper::get_all_payment_methods_for_select(), $transaction->payment_method, false); ?>
      </div>
    </div>
    <div class="os-row">
      <div class="os-col-lg-12">
        <?php echo OsFormHelper::textarea_field('transactions['.$real_or_rand_id.'][notes]',__('Notes', 'latepoint') , ($transaction->notes ?? ''), ['theme' => 'simple']); ?>
      </div>
    </div>
	  <?php echo OsFormHelper::hidden_field('transactions['.$real_or_rand_id.'][id]', $real_or_rand_id); ?>
	  <?php do_action( 'latepoint_transaction_edit_form_after', $transaction, $real_or_rand_id ); ?>
	  </div>
  </div>
</div>