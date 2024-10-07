<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<h3 class="os-wizard-sub-header">
    <?php
    // translators: %1$d is current step, %2$d is total steps
    echo esc_html(sprintf(__('Step %1$d of %2$d', 'latepoint'), $current_step_number, 3)); ?>
</h3>
<h2 class="os-wizard-header"><?php esc_html_e('Add Services', 'latepoint'); ?></h2>
<div class="os-wizard-desc"><?php esc_html_e('When creating a service, make sure to select agents offering it. You can set custom schedules and prices for each service in LatePoint admin panel later.', 'latepoint'); ?></div>
<div class="os-wizard-step-content-i">
	<?php 
	if($services){
		include('_list_services.php');
	}else{
		include('_form_service.php');
	} ?>
</div>