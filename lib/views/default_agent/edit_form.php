<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-form-w">
	<div class="white-box">
		<div class="white-box-header">
			<div class="os-form-sub-header">
				<h3><?php esc_html_e('Recipient of Notifications', 'latepoint') ?></h3>
			</div>
		</div>
		<div class="white-box-content">
				<form  data-os-success-action="reload" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('default_agent', 'update')); ?>">
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
			    <?php if(!$agent->is_new_record()) echo OsFormHelper::hidden_field('agent[id]', $agent->id); ?>

			    <div class="os-form-buttons os-flex">
			    <?php
			        echo OsFormHelper::hidden_field('agent[id]', $agent->id);
			        if(OsRolesHelper::can_user('agent__edit')) {
				        echo OsFormHelper::button('submit', __('Save Changes', 'latepoint'), 'submit', ['class' => 'latepoint-btn']);
			        }
			      ?>
			    </div>
				  <?php wp_nonce_field('edit_agent_'.$agent->id); ?>
			  </form>
			</div>
		</div>
</div>

<?php echo OsUtilHelper::pro_feature_block(__('To add more agents upgrade to a paid version', 'latepoint')); ?>