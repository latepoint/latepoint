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

if($invoice){ ?>
<div class="os-invoice-wrapper">
	<div class="os-invoice-inner">
        <?php OsInvoicesHelper::invoice_document_html($invoice); ?>
	</div>
</div>
<?php
}else{
	echo 'Invoice not found';
}