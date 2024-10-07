<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-all-wrapper <?php echo esc_attr(implode(' ', $extra_css_classes)); ?>">
	<div class="latepoint-content">
		<?php include($view); ?>
	</div>
</div>