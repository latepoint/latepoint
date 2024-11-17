<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/* @var $enabled_payment_methods array */
/* @var $form_prev_button string */
/* @var $form_next_button string */
/* @var $transaction_intent OsTransactionIntentModel */
/* @var $errors array */

/**
 * Content for order payment - pay step
 *
 * @param {OsTransactionIntentModel} transaction intent for a payment
 *
 * @since 5.0.15
 * @hook latepoint_order_payment__pay_content
 *
 */
do_action( 'latepoint_order_payment__pay_content', $transaction_intent );
?>