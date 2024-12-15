<?php
/**
 * @var $booking OsBookingModel
 * @var $all_steps \LatePoint\Misc\Step[]
 * @var $steps \LatePoint\Misc\Step[]
 * @var $current_step \LatePoint\Misc\Step
 * @var $current_step_code string
 * @var $show_next_btn bool
 * @var $restrictions array
 * @var $presets array
 * @var $cart OsCartModel
 * @var $booking_element_type string
 * @var $booking_element_styles array
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>

<?php
$element_classes = [];
$element_classes[] = ($booking_element_type == 'lightbox') ? 'latepoint-lightbox-form' : 'latepoint-inline-form';
$element_classes[] = empty($booking_element_styles['hide_summary']) ? 'latepoint-with-summary' : 'latepoint-without-summary';
$element_classes[] = empty($booking_element_styles['hide_side_panel']) ? 'latepoint-show-side-panel' : 'latepoint-hide-side-panel';
$element_classes[] = ((!$cart->is_empty() || $booking->is_ready_for_summary()) && $current_step_code != 'confirmation') ? 'latepoint-summary-is-open' : '';
?>
<div class="latepoint-w <?php echo esc_attr(implode(' ', $element_classes)); ?>">
<div class="latepoint-booking-form-element current-step-<?php echo esc_attr($current_step->code); ?> <?php if ( ! $show_next_btn ) {
	echo 'hidden-buttons';
} ?> latepoint-color-<?php echo esc_attr(OsSettingsHelper::get_booking_form_color_scheme()); ?> latepoint-border-radius-<?php echo esc_attr(OsSettingsHelper::get_booking_form_border_radius()); ?> <?php echo esc_attr(implode( ' ', apply_filters( 'latepoint_booking_form_classes', [] ) )); ?>">
    <div class="latepoint-side-panel">
		<?php OsStepsHelper::show_step_progress( $all_steps, $current_step ); ?>
        <div class="latepoint-step-desc-w">
            <div class="latepoint-step-desc">
				<?php if ( $current_step->is_using_custom_image_for_side_panel() ) { ?>
                    <div class="latepoint-desc-media img-w"
                         style="background-image: url(<?php echo esc_url($current_step->get_image_url_for_side_panel()); ?>)"></div>
				<?php } else {
					echo '<div class="latepoint-desc-media svg-w">' . $current_step->get_default_image_html_for_side_panel() . '</div>';
				} ?>
                <h3 class="latepoint-desc-title"><?php echo esc_html($current_step->side_panel_heading); ?></h3>
                <div class="latepoint-desc-content"><?php echo stripcslashes( $current_step->side_panel_description ); ?></div>
            </div>
			<?php
			foreach ( $all_steps as $index => $step ) { ?>
                <div data-step-code="<?php echo esc_attr($step->code); ?>"
                     class="latepoint-step-desc-library <?php if ( $current_step->code == $step->code ) {
					     echo ' active ';
				     } ?>">
					<?php if ( $step->is_using_custom_image_for_side_panel() ) { ?>
                        <div class="latepoint-desc-media img-w"
                             style="background-image: url(<?php echo esc_url($step->get_image_url_for_side_panel()); ?>)"></div>
					<?php } else {
						echo '<div class="latepoint-desc-media svg-w">' . $step->get_default_image_html_for_side_panel() . '</div>';
					} ?>
                    <h3 class="latepoint-desc-title"><?php echo esc_html($step->side_panel_heading); ?></h3>
                    <div class="latepoint-desc-content"><?php echo $step->side_panel_description; ?></div>
                </div>
			<?php } ?>
        </div>
        <div class="latepoint-questions"><?php echo OsSettingsHelper::get_steps_support_text(); ?></div>

		<?php

		/**
		 * Triggered at the bottom of side panel content
		 *
		 * @param {\LatePoint\Misc\Step} $current_step active step
		 *
		 * @since 4.7.0
		 * @hook latepoint_steps_side_panel_after
		 *
		 */
		do_action( 'latepoint_steps_side_panel_after', $current_step ); ?>
    </div>
    <div class="latepoint-form-w">
        <form class="latepoint-form"
              data-selected-label="<?php esc_attr_e( 'Selected', 'latepoint' ); ?>"
              data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name( 'steps', 'load_step' )); ?>"
              action="#">
            <div class="latepoint-heading-w">
                <h3 class="os-heading-text"><?php echo esc_html($current_step->main_panel_heading); ?></h3>
				<?php foreach ( $all_steps as $index => $step ) { ?>
                    <div data-step-code="<?php echo esc_attr($step->code); ?>"
                         class="os-heading-text-library <?php if ( $current_step->code == $step->code ) {
						     echo ' active ';
					     } ?>"><?php echo esc_html($step->main_panel_heading); ?></div>
				<?php } ?>
                <a href="#" class="latepoint-lightbox-close" tabindex="0"><i class="latepoint-icon-common-01"></i></a>
				<?php if ( $cart->is_empty() ) { ?>
                    <a href="#" class="latepoint-lightbox-summary-trigger"><i class="latepoint-icon-shopping-cart"></i></a>
				<?php } else { ?>
                    <a href="#" class="latepoint-lightbox-summary-trigger"><i
                                class="latepoint-icon-shopping-cart"></i><span><?php echo count( $cart->get_items() ); ?></span></a>
				<?php } ?>
            </div>
            <div class="latepoint-body">
				<?php if ( ! empty( $step_codes_to_preload ) ) {
					foreach ( $step_codes_to_preload as $step_code_to_preload ) {
						do_action( 'latepoint_load_step', $step_code_to_preload, 'html', [] );
					}
				}
				?>
				<?php do_action( 'latepoint_load_step', $current_step->code, 'html', [] ); ?>
            </div>
            <div class="latepoint-footer">
                <a href="#" class="latepoint-btn latepoint-btn-white latepoint-prev-btn disabled" tabindex="0" role="button"><i
                            class="latepoint-icon-arrow-2-left"></i>
                    <span><?php esc_html_e( 'Back', 'latepoint' ); ?></span></a>
				<?php OsStepsHelper::show_step_progress( $all_steps, $current_step ); ?>
                <a href="#" tabindex="0"
                   class="latepoint-btn latepoint-btn-primary latepoint-next-btn <?php echo ( $show_next_btn ) ? '' : 'disabled'; ?>" role="button"
                   data-pre-last-step-label="<?php esc_attr_e( 'Submit', 'latepoint' ); ?>"
                   data-label="<?php esc_attr_e( 'Next', 'latepoint' ); ?>"><span><?php esc_html_e( 'Next', 'latepoint' ); ?></span> <i class="latepoint-icon-arrow-2-right"></i></a>
				<?php if ( $current_step_code != 'confirmation' ) {
					include 'partials/_booking_form_params.php';
				} ?>
            </div>
        </form>
    </div>
    <div class="latepoint-summary-w">
        <div class="os-summary-contents">
			<?php if ( $current_step_code != 'confirmation' ) {
				include( 'partials/_booking_form_summary_panel.php' );
			} ?>
        </div>
    </div>
</div>
</div>