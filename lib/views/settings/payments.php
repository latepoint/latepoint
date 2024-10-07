<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-settings-w os-form-w">
  <form action="" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name('settings', 'update')); ?>">
	  <?php wp_nonce_field('update_settings'); ?>
      <div class="os-section-header"><h3><?php esc_html_e('Payment Processors', 'latepoint'); ?></h3></div>
        <div class="os-togglable-items-w">
        <?php foreach($payment_processors as $payment_processor_code => $payment_processor){ ?>
          <div class="os-togglable-item-w" id="paymentProcessorToggler_<?php echo esc_attr($payment_processor['code']); ?>">
            <div class="os-togglable-item-head">
              <div class="os-toggler-w">
                <?php echo OsFormHelper::toggler_field('settings[enable_payment_processor_'.$payment_processor_code.']', false, OsPaymentsHelper::is_payment_processor_enabled($payment_processor_code), 'togglePaymentSettings_'.$payment_processor_code, 'large', ['nonce' => wp_create_nonce('update_settings'), 'instant_update_route' => OsRouterHelper::build_route_name('settings', 'update')]); ?>
              </div>
	            <?php if(!empty($payment_processor['image_url'])) echo '<img class="os-togglable-item-logo-img" src="'.esc_url($payment_processor['image_url']).'"/>'; ?>
              <div class="os-togglable-item-name"><?php echo esc_html($payment_processor['name']); ?></div>
            </div>
            <div class="os-togglable-item-body" style="<?php echo OsPaymentsHelper::is_payment_processor_enabled($payment_processor_code) ? '' : 'display: none'; ?>" id="togglePaymentSettings_<?php echo esc_attr($payment_processor_code); ?>">
              <?php do_action('latepoint_payment_processor_settings', $payment_processor_code); ?>
            </div>
          </div>
        <?php } ?>
        </div>

      <div class="os-mb-4">
      <?php echo OsUtilHelper::pro_feature_block(__('To add other payment processors, upgrade to a paid version', 'latepoint')); ?>
          </div>
        <div class="os-section-header"><h3><?php esc_html_e('Other Settings', 'latepoint'); ?></h3></div>
		    <div class="white-box">
		      <div class="white-box-header">
		        <div class="os-form-sub-header"><h3><?php esc_html_e('Payment Settings', 'latepoint'); ?></h3></div>
		      </div>
		      <div class="white-box-content no-padding">
		        <div class="sub-section-row">
		          <div class="sub-section-label">
		            <h3><?php esc_html_e('Environment', 'latepoint') ?></h3>
		          </div>
		          <div class="sub-section-content">
				        <?php echo OsFormHelper::select_field('settings[payments_environment]', false, array(LATEPOINT_PAYMENTS_ENV_LIVE => __('Live', 'latepoint'), LATEPOINT_PAYMENTS_ENV_DEV => __('Development', 'latepoint')), OsSettingsHelper::get_payments_environment()); ?>
		          </div>
		        </div>

		        <div class="sub-section-row">
		          <div class="sub-section-label">
		            <h3><?php esc_html_e('Local Payments', 'latepoint') ?></h3>
		          </div>
		          <div class="sub-section-content">
				        <?php echo OsFormHelper::toggler_field('settings[enable_payments_local]', __('Allow Paying Locally', 'latepoint'), OsPaymentsHelper::is_local_payments_enabled(), false, false, ['sub_label' => __('Show "Pay Later" payment option', 'latepoint')]); ?>
		          </div>
		        </div>
		      </div>
		    </div>
    <div class="os-form-buttons">
      <?php echo OsFormHelper::button('submit', __('Save Settings', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); ?>
    </div>
  </form>
</div>