<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/* @var $order OsOrderModel */
/* @var $selected_payment_method string */
/* @var $selected_payment_processor string */
/* @var $current_step string */
/* @var $transaction_intent OsTransactionIntentModel */
/* @var $errors array */
/* @var $form_heading string */
/* @var $form_prev_button string */
/* @var $form_prev_button string */
/* @var $invoice_link string */
?>
<?php if(!empty($form_heading)) { ?>
<div class="latepoint-lightbox-heading">
    <h2><?php echo esc_html($form_heading); ?></h2>
    <a href="#" class="latepoint-lightbox-close" tabindex="0"><i class="latepoint-icon latepoint-icon-x"></i></a>
</div>
<?php } ?>
<div class="latepoint-lightbox-content">
    <?php

    // Output errors if any
    if (!empty($errors)){
        echo '<div class="latepoint-message latepoint-message-error">';
        foreach($errors as $error){
            echo '<div>'.$error.'</div>';
        }
        echo '</div>';
    }
    ?>
    <?php
    include('payment_form/_'.$current_step.'.php');

    echo OsFormHelper::hidden_field('order_id', $transaction_intent->order_id);
    echo OsFormHelper::hidden_field('payment_method', $transaction_intent->get_payment_data_value('method'));
    echo OsFormHelper::hidden_field('payment_processor', $transaction_intent->get_payment_data_value('processor'));
    echo OsFormHelper::hidden_field('payment_portion', $transaction_intent->get_payment_data_value('portion'));
    echo OsFormHelper::hidden_field('payment_token', $transaction_intent->get_payment_data_value('token'));
    echo OsFormHelper::hidden_field('current_step', $current_step);
    ?>
</div>
<?php if(!empty($form_prev_button) || !empty($form_next_button) || $invoice_link){ ?>
<div class="latepoint-lightbox-footer">

	<?php
    $block_class = (empty($form_prev_button) || empty($form_next_button)) ? 'latepoint-btn-block' : '';
    if(!empty($form_prev_button)) echo '<button type="button" class="latepoint-btn latepoint-btn-secondary '.$block_class.'">'.$form_prev_button.'</a>';
	if(!empty($form_next_button)) echo '<button type="submit" class="latepoint-btn latepoint-btn-primary '.$block_class.'">'.$form_next_button.'</a>';
	if(!empty($invoice_link)) echo '<a href="#" class="latepoint-btn latepoint-btn-primary '.$block_class.'"><span>'.__('View Invoice', 'latepoint').'</span><i class="latepoint-icon latepoint-icon-external-link"></i></a>';
    ?>
</div>
<?php } ?>