<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-form-w">
	<form action="" data-os-output-target=".os-wizard-step-content-i" data-os-pass-response="yes" data-os-after-call="latepoint_wizard_item_editing_cancelled" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('wizard', 'save_agent')); ?>">
		<div class="os-row">
			<div class="os-col-6">
		    <?php echo OsFormHelper::text_field('agent[first_name]', __('First Name', 'latepoint'), $agent->first_name); ?>
			</div>
			<div class="os-col-6">
		    <?php echo OsFormHelper::text_field('agent[last_name]', __('Last Name', 'latepoint'), $agent->last_name); ?>
			</div>
		</div>
		<div class="os-row">
			<div class="os-col-lg-6">
		    <?php echo OsFormHelper::text_field('agent[email]', __('Email Address', 'latepoint'), $agent->email); ?>
			</div>
			<div class="os-col-lg-6">
		    <?php echo OsFormHelper::phone_number_field('agent[phone]', __('Phone Number', 'latepoint'), $agent->phone); ?>
			</div>
		</div>
    <?php echo OsFormHelper::media_uploader_field('agent[avatar_image_id]', 0, __('Upload Agent\'s Photo', 'latepoint'), __('Remove Agent\'s Photo', 'latepoint'), $agent->avatar_image_id, false, false, true); ?>
    <?php if(!$agent->is_new_record()) echo OsFormHelper::hidden_field('agent[id]', $agent->id); ?>

    <div class="side-by-side-buttons">
	    <?php if($agents){ ?>
			      <button type="button" data-os-after-call="latepoint_wizard_item_editing_cancelled" data-os-pass-response="yes" data-os-output-target=".os-wizard-step-content" data-os-params="current_step_code=agents" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('wizard', 'load_step')); ?>" class="wizard-finished-editing-trigger latepoint-btn latepoint-btn-lg latepoint-btn-secondary">
	            <i class="latepoint-icon latepoint-icon-arrow-left"></i>
				      <span><?php esc_html_e('Cancel', 'latepoint'); ?></span>
			      </button>
	    <?php }else{ ?>
				<a href="#" data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('wizard', 'prev_step')); ?>" class="latepoint-btn latepoint-btn-lg latepoint-btn-secondary os-wizard-trigger-prev-btn"><i class="latepoint-icon latepoint-icon-arrow-left"></i> <span><?php esc_html_e('Back', 'latepoint'); ?></span></a>
	    <?php } ?>
	      <button type="submit" class="latepoint-btn latepoint-btn-lg latepoint-btn-primary">
	        <span><?php echo ($agent->is_new_record()) ? esc_html__('Save', 'latepoint') : esc_html__('Save', 'latepoint'); ?></span>
	        <i class="latepoint-icon latepoint-icon-check"></i>
        </button>
    </div>
  </form>
</div>