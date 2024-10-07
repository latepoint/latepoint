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
<h2 class="os-wizard-header"><?php esc_html_e('Create Agents', 'latepoint'); ?></h2>
<div class="os-wizard-desc"><?php esc_html_e('Agents act as your bookable resources, you have to have at least one created in order for you to accept bookings.', 'latepoint'); ?></div>
<div class="os-wizard-step-content-i">
	<?php 
	if($agents){
		include('_list_agents.php');
	}else{
		include('_form_agent.php');
	} ?>
</div>