<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/* @var $invoice OsInvoiceModel */
/* @var $invoice_data array */
?>
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="side-sub-panel-wrapper wide" data-invoice-id="<?php echo esc_attr( $invoice->id ); ?>">
    <div class="side-sub-panel-header os-form-header">
        <h2><?php esc_html_e( 'View Invoice', 'latepoint-pro-features' ); ?></h2>
        <a href="#" class=" latepoint-side-sub-panel-close latepoint-side-sub-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>
    </div>
    <div class="side-sub-panel-content pattern-dotted">
        <?php OsInvoicesHelper::invoice_document_html($invoice, true); ?>
    </div>
</div>