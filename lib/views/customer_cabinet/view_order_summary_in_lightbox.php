<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-lightbox-heading">
	<h2><?php esc_html_e('Order Summary', 'latepoint'); ?></h2>
</div>
<div class="latepoint-lightbox-content">
	<?php include(LATEPOINT_VIEWS_ABSPATH.'orders/_full_summary.php'); ?>
</div>