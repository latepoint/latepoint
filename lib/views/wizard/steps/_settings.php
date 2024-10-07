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
<h2 class="os-wizard-header"><?php esc_html_e('Add services you offer', 'latepoint'); ?></h2>
<div class="os-wizard-desc"><?php esc_html_e('Day and, through to this separated is rhetoric regretting the magnitude, perception is keep in', 'latepoint'); ?></div>
<div class="os-wizard-step-content-i">
	<div class="os-form-w">
		<form action="" data-os-output-target=".os-wizard-step-content-i" data-os-pass-response="yes" data-os-after-call="latepoint_wizard_item_editing_cancelled" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('wizard', 'save_agent')); ?>">
			<?php echo OsFormHelper::text_field('settings[purchase_code]', __('Plugin Purchase Code', 'latepoint'), OsSettingsHelper::get_settings_value('purchase_code')); ?>
	  </form>
	</div>
</div>