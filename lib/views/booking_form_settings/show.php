<?php
/**
 * @var $steps array
 * @var $selected_step_code string
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="booking-form-preview-wrapper">
    <div class="booking-form-preview-inner">
		<?php include '_booking_form_preview.php'; ?>
    </div>
    <form class="booking-form-preview-settings"
          data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name( 'booking_form_settings', 'reload_preview' )); ?>">
        <div class="bf-heading">
            <div class="latepoint-icon latepoint-icon-browser"></div>
            <div><?php esc_html_e( 'Appearance', 'latepoint' ); ?></div>
        </div>
        <div class="bf-content">
			<?php
			$colors       = [ 'blue', 'black', 'teal', 'green', 'purple', 'red', 'orange' ];
			$color_labels = [
				'blue'   => __( 'Blue', 'latepoint' ),
				'red'    => __( 'Red', 'latepoint' ),
				'black'  => __( 'Black', 'latepoint' ),
				'teal'   => __( 'Teal', 'latepoint' ),
				'green'  => __( 'Green', 'latepoint' ),
				'purple' => __( 'Purple', 'latepoint' ),
				'orange' => __( 'Orange', 'latepoint' ),
				'custom' => __( 'Custom', 'latepoint' )
			];
			$color_hexes  = [
				'blue'   => '#1d7bff',
				'red'    => '#F34747',
				'black'  => '#222222',
				'teal'   => '#0f8c77',
				'green'  => '#1ca00f',
				'purple' => '#a32f96',
				'orange' => '#cc7424'
			];
			echo '<div class="os-form-group os-form-select-group os-form-group-transparent">';
			echo '<div class="bf-color-scheme-colors">';
			$active_color_scheme = OsSettingsHelper::get_booking_form_color_scheme();
			foreach ( $color_hexes as $color_code => $hex ) {
				echo '<div data-color-code="' . esc_attr($color_code) . '" class="bf-color-scheme-color-trigger ' . ( ( $active_color_scheme == $color_code ) ? 'is-selected' : '' ) . '" style="background-color: ' . esc_attr($hex) . '"></div>';
			}
			echo '<div data-color-code="custom" class="bf-color-scheme-color-trigger ' . ( ( $active_color_scheme == 'custom' ) ? 'is-selected' : '' ) . '" style="background: conic-gradient(red, yellow, lime, aqua, blue, magenta, red);"><i class="latepoint-icon latepoint-icon"></i></div>';
			echo '</div>';
			echo '</div>';
			?>
            <div class="os-color-scheme-selector-wrapper">
				<?php echo OsFormHelper::select_field( 'settings[color_scheme_for_booking_form]', __( 'Color Scheme', 'latepoint' ), $color_labels, $active_color_scheme ); ?>
            </div>
            <div class="os-custom-color-selector-wrapper <?php if ( $active_color_scheme != 'custom' ) {
				echo 'is-hidden';
			} ?>">
				<?php echo OsFormHelper::color_picker( 'settings[custom_brand_primary_color]', __( 'Pick Custom Color', 'latepoint' ), OsSettingsHelper::get_settings_value( 'custom_brand_primary_color', '#000000' ) ); ?>
                <a href="#" class="trigger-custom-color-save"><?php esc_html_e( 'Apply', 'latepoint' ); ?></a>
            </div>
			<?php echo OsFormHelper::select_field( 'settings[border_radius]', __( 'Border Style', 'latepoint' ), [
				'rounded' => 'Rounded Corners',
				'flat'    => 'Flat'
			], OsSettingsHelper::get_booking_form_border_radius() ); ?>
			<?php
			/**
			 * Content after booking form general settings section
			 *
			 * @since 5.0.0
			 * @hook latepoint_booking_form_settings_general_after
			 *
			 */
			do_action( 'latepoint_booking_form_settings_general_after' ); ?>
        </div>
        <div class="bf-heading">
            <div class="latepoint-icon latepoint-icon-menu"></div>
            <div><?php esc_html_e( 'Steps', 'latepoint' ); ?></div>
            <a href="#" class="bf-link" data-os-after-call="latepoint_init_step_reordering"
               data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name( 'settings', 'steps_order_modal' )); ?>"
               data-os-output-target="lightbox"
               data-os-lightbox-classes="width-500">
                <i class="latepoint-icon latepoint-icon-refresh-cw"></i>
                <span><?php esc_html_e( 'Change Order', 'latepoint' ); ?></span>
            </a>
        </div>
        <div class="bf-content">
			<?php echo OsFormHelper::select_field( 'selected_step_code', false, OsStepsHelper::get_steps_for_select(), $selected_step_code ); ?>
            <div class="bf-preview-step-settings">
				<?php echo OsStepsHelper::get_step_settings_edit_form_html( $selected_step_code ); ?>
            </div>
        </div>
    </form>
</div>