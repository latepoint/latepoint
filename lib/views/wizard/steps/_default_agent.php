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
<h2 class="os-wizard-header"><?php esc_html_e( 'Setup Notifications', 'latepoint' ); ?></h2>
<div class="os-wizard-desc"><?php esc_html_e( 'Who would you like to send appointment notifications to?', 'latepoint' ); ?></div>
<div class="os-wizard-step-content-i">
    <div class="os-form-w">
        <form action="" class="os-wizard-default-agent-form" data-os-output-target=".os-wizard-step-content-i" data-os-pass-response="yes"
              data-os-after-call="latepoint_wizard_item_editing_cancelled"
              data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name( 'wizard', 'save_agent' )); ?>">
            <div class="os-row">
                <div class="os-col-6">
					<?php echo OsFormHelper::text_field( 'agent[first_name]', __( 'First Name', 'latepoint' ), $agent->first_name ); ?>
                </div>
                <div class="os-col-6">
					<?php echo OsFormHelper::text_field( 'agent[last_name]', __( 'Last Name', 'latepoint' ), $agent->last_name ); ?>
                </div>
            </div>
            <div class="os-row">
                <div class="os-col-lg-6">
					<?php echo OsFormHelper::text_field( 'agent[email]', __( 'Email Address', 'latepoint' ), $agent->email ); ?>
                </div>
                <div class="os-col-lg-6">
					<?php echo OsFormHelper::phone_number_field( 'agent[phone]', __( 'Phone Number', 'latepoint' ), $agent->phone ); ?>
                </div>
            </div>
        </form>
    </div>
</div>