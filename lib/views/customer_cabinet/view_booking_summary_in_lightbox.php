<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

/* @var $booking OsBookingModel */
/* @var $order OsOrderModel */
/* @var $order_item OsOrderItemModel */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-lightbox-heading">
	<h2><?php esc_html_e('Booking Summary', 'latepoint'); ?></h2>
</div>
<div class="latepoint-lightbox-content">
<?php include(LATEPOINT_VIEWS_ABSPATH.'bookings/_full_summary.php'); ?>
</div>