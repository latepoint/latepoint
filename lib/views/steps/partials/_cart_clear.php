<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-cart-clear-question">
	<div class="os-cart-clear-question-i">
		<h3><?php esc_html_e('You have items in your cart', 'latepoint'); ?></h3>
		<?php esc_html_e('Do you want to continue adding items to existing cart or start a new booking and clear your cart.', 'latepoint'); ?>
		<div class="os-cart-decision">
			<a href="#"><?php esc_html_e('Continue', 'latepoint'); ?></a>
			<a href="#"><?php esc_html_e('Clear', 'latepoint'); ?></a>
		</div>
	</div>
</div>