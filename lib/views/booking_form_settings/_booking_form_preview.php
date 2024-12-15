<?php
/* @var $selected_step_code string */
/* @var $steps_for_select array */
/* @var $booking OsBookingModel */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="booking-form-preview latepoint-w booking-form-preview-step-<?php echo esc_attr($selected_step_code); ?>">
	<div class="latepoint-booking-form-element <?php echo 'latepoint-color-'.esc_attr(OsSettingsHelper::get_booking_form_color_scheme()); ?> latepoint-border-radius-<?php echo esc_attr(OsSettingsHelper::get_booking_form_border_radius()); ?>">
		<div class="bf-side-panel">
			<div class="side-panel-main">
				<div class="bf-side-media">
                    <div class="bf-side-media-picker-trigger">
                        <?php
                        $custom_image_id = OsStepsHelper::get_step_setting_value($selected_step_code, 'side_panel_custom_image_id');
                        echo OsFormHelper::media_uploader_field('['.$selected_step_code.'][side_panel_custom_image_id]', 0, '', '', $custom_image_id, [], [], false, false, OsStepsHelper::get_default_side_panel_image_html_for_step_code($selected_step_code));
                        ?>
                    </div>
                </div>
				<div class="bf-side-heading editable-setting" data-setting-key="[<?php echo esc_attr($selected_step_code);?>][side_panel_heading]" contenteditable="true"><?php echo OsStepsHelper::get_step_setting_value($selected_step_code, 'side_panel_heading'); ?></div>
				<div class="bf-side-desc os-editable-basic editable-setting" data-setting-key="[<?php echo $selected_step_code;?>][side_panel_description]"><?php echo OsStepsHelper::get_step_setting_value($selected_step_code, 'side_panel_description'); ?></div>
			</div>
			<div class="side-panel-extra os-editable editable-setting" data-setting-key="[shared][steps_support_text]">
				<?php echo OsSettingsHelper::get_steps_support_text(); ?>
			</div>
		</div>
		<div class="bf-main-panel">
			<div class="bf-main-heading editable-setting" data-setting-key="[<?php echo esc_attr($selected_step_code);?>][main_panel_heading]" contenteditable="true"><?php echo OsStepsHelper::get_step_setting_value($selected_step_code, 'main_panel_heading'); ?></div>
			<div class="bf-main-panel-content-wrapper">
				<div class="bf-main-panel-content-before os-editable editable-setting" data-placeholder="+" data-setting-key="[<?php echo esc_attr($selected_step_code);?>][main_panel_content_before]"><?php echo OsStepsHelper::get_step_setting_value($selected_step_code, 'main_panel_content_before'); ?></div>
				<div class="bf-main-panel-content">
					<?php echo OsStepsHelper::get_step_content_preview($selected_step_code); ?>
				</div>
				<div class="bf-main-panel-content-after os-editable editable-setting" data-placeholder="+" data-setting-key="[<?php echo esc_attr($selected_step_code);?>][main_panel_content_after]"><?php echo OsStepsHelper::get_step_setting_value($selected_step_code, 'main_panel_content_after'); ?></div>
			</div>
			<div class="bf-main-panel-buttons">
                <div class="bf-btn bf-cancel-save-btn">
                    <i class="latepoint-icon latepoint-icon-x"></i>
                    <span><?php esc_html_e('Discard', 'latepoint'); ?></span>
                </div>
                <div class="bf-btn bf-save-btn">
                    <i class="latepoint-icon latepoint-icon-check"></i>
                    <span><?php esc_html_e('Save Changes', 'latepoint'); ?></span>
                </div>
				<div class="bf-btn bf-prev-btn">
					<i class="latepoint-icon latepoint-icon-arrow-left"></i>
					<span><?php esc_html_e('Back', 'latepoint'); ?></span>
				</div>
				<div class="bf-btn bf-next-btn">
					<span><?php esc_html_e('Next', 'latepoint'); ?></span>
					<i class="latepoint-icon latepoint-icon-arrow-right"></i>
				</div>
			</div>
		</div>
	</div>
	<?php /*
	<div class="bf-summary-panel latepoint-summary-w">
		<div class="bf-summary-heading">
			<div class="bf-summary-heading-inner">
				<span>Summary</span>
			</div>
		</div>
		<div class="bf-summary-content">
			<?php include(LATEPOINT_VIEWS_ABSPATH.'steps/partials/_booking_form_summary_panel.php'); ?>
		</div>
	</div>
 */ ?>
</div>