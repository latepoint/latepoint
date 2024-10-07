<?php
/**
 * @var $current_step_code string
 * @var $booking OsBookingModel
 * @var $restrictions array
 * @var $presets array
 * @var $bundles OsBundleModel[]
 * @var $services OsServiceModel[]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$preselected_service = (!empty($presets['selected_service'])) ? new OsServiceModel($presets['selected_service']) : false;
$preselected_bundle = (!empty($presets['selected_bundle'])) ? new OsBundleModel($presets['selected_bundle']) : false;
?>
<div class="step-services-w latepoint-step-content"
     data-step-code="<?php echo esc_attr($current_step_code); ?>"
     data-next-btn-label="<?php echo esc_attr(OsStepsHelper::get_next_btn_label_for_step($current_step_code)); ?>"
     data-clear-action="clear_step_services">
	<?php
	do_action('latepoint_before_step_content', $current_step_code);
	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'before');

    if(OsSettingsHelper::steps_show_service_categories()) {
	    // Generate categorized services list
	    OsBookingHelper::generate_services_bundles_and_categories_list( false, [
		    'show_service_categories_arr' => $show_service_categories_arr,
		    'show_services_arr'           => $show_services_arr,
		    'preselected_service'         => $preselected_service,
		    'preselected_category'        => $preselected_category,
		    'preselected_duration'        => $preselected_duration,
		    'preselected_total_attendees' => $preselected_total_attendees,
	    ] );
    }else{
        echo '<div class="os-item-categories-holder os-item-categories-main-parent os-animated-parent">';
        OsBookingHelper::generate_services_list($services);
        OsBookingHelper::generate_bundles_folder();
        echo '</div>';
    }

	echo OsStepsHelper::get_formatted_extra_step_content($current_step_code, 'after');
	do_action('latepoint_after_step_content', $current_step_code);

	echo OsFormHelper::hidden_field('booking[service_id]', $booking->service_id, ['class' => 'latepoint_service_id', 'skip_id' => true]);
	?>
</div>