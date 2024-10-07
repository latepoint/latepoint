<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="available-vars-w">
  <div class="latepoint-message latepoint-message-subtle">
    <div><?php esc_html_e('You can use these variables in your email and sms notifications. Just click on the variable with {} brackets and it will automatically copy to your buffer and you can simply paste it where you want to use it. It will be converted into a value for the agent/service or appointment.', 'latepoint'); ?></div>
  </div>
  <div class="available-vars-i">
    <?php include(LATEPOINT_ABSPATH.'lib/views/shared/_template_variables.php'); ?>
  </div>
</div>