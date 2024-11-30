<?php
/* @var $booking OsBookingModel */
/* @var $services OsServiceModel[] */
/* @var $agents OsAgentModel[] */
/* @var $order_item_id string */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php if ( ! $booking->is_new_record() ) { ?>
    <div class="quick-booking-info">
		<?php echo '<span>' . esc_html__( 'Booking ID:', 'latepoint' ) . '</span><strong>' . esc_html($booking->id) . '</strong>'; ?>
		<?php echo '<span>' . esc_html__( 'Code:', 'latepoint' ) . '</span><strong>' . esc_html($booking->booking_code) . '</strong>'; ?>
        <?php echo '<a target="_blank" href="'.$booking->manage_by_key_url('customer').'"><i class="latepoint-icon latepoint-icon-link-2"></i>'.esc_html__('Share', 'latepoint').'</a>'; ?>
		<?php if ( OsAuthHelper::get_current_user()->has_capability( 'activity__view' ) ) {
			echo '<a href="#" data-booking-id="' . esc_attr($booking->id) . '" data-route="' . esc_attr(OsRouterHelper::build_route_name( 'bookings', 'view_booking_log' )) . '" class="quick-booking-form-view-log-btn"><i class="latepoint-icon latepoint-icon-clock"></i>' . esc_html__( 'History', 'latepoint' ) . '</a>';
		} ?>
    </div>
<?php } ?>
<?php

?>
<?php if ( $booking->is_part_of_bundle() && ! empty( $booking->service_id ) ) {
	echo '<div class="quick-booking-preselected-service-info">' . esc_html__( 'Bundled Service: ', 'latepoint' ) . '<span>' . esc_html($booking->service->name) . '</span></div>';
} elseif ( $services ) { ?>
    <div class="os-form-group os-form-group-transparent os-form-select-group os-booking-data-service-selector-wrapper">
        <label for=""><?php esc_html_e( 'Service', 'latepoint' ); ?></label>
        <div class="os-services-select-field-w">
            <div class="services-options-list">
				<?php if ( count( $services ) > 7 ) { ?>
                    <div class="service-options-filter-input-w"><input class="service-options-filter-input" type="text"
                                                                       placeholder="<?php esc_attr_e( 'Start typing to filter...', 'latepoint' ); ?>">
                    </div>
				<?php } ?>
				<?php
				$service_categories = [];
				foreach ( $services as $service ) {
					$service_categories[ 'cat_' . $service->category_id ][] = $service;
				}
				if ( $service_categories ) {
					foreach ( $service_categories as $key => $service_category_services ) {
						$category_id = str_replace( 'cat_', '', $key );
						if ( $category_id == '0' || ! $category_id ) {
							$category_name = __( 'Uncategorized', 'latepoint' );
						} else {
							$category      = new OsServiceCategoryModel( $category_id );
							$category_name = ( $category ) ? $category->name : __( 'Uncategorized', 'latepoint' );
						}
						echo '<div class="os-option-group">' . esc_html($category_name) . '</div>';
						foreach ( $service_category_services as $service ) {
							$selected = ( $booking->service_id == $service->id ) ? true : false;
							OsServiceHelper::service_option_html_for_select( $service, $selected );
						}
					}
				}
				?>
            </div>
			<?php if ( $booking->service_id ) { ?>
                <div class="service-option-selected"
                     data-id="<?php echo esc_attr($booking->service->id); ?>"
                     data-buffer-before="<?php echo esc_attr($booking->service->buffer_before); ?>"
                     data-buffer-after="<?php echo esc_attr($booking->service->buffer_after); ?>"
                     data-capacity-min="<?php echo esc_attr($booking->service->capacity_min); ?>"
                     data-capacity-max="<?php echo esc_attr($booking->service->capacity_max); ?>"
                     data-duration-name="<?php echo esc_attr($booking->service->duration_name); ?>"
                     data-duration="<?php echo esc_attr($booking->service->duration); ?>">
                    <div class="service-color"
                         style="background-color: <?php echo esc_attr($booking->service->bg_color); ?>"></div>
                    <span><?php echo esc_html($booking->service->name) ?></span>
                </div>
			<?php } else { ?>
                <div class="service-option-selected">
                    <div class="service-color"></div>
                    <span><?php esc_html_e( 'Select Service', 'latepoint' ); ?></span>
                </div>
			<?php } ?>
        </div>
    </div> <?php
} else {
	echo '<div class="latepoint-message latepoint-message-error">' . esc_html__( 'No Active Services Found.', 'latepoint' ) . '</div>';
} ?>
    <div class="os-service-durations"
         style="<?php echo ( $booking->service_id && count( $booking->service->get_all_durations_arr() ) > 1 ) ? '' : 'display: none;'; ?>">
        <div class="os-form-group os-form-select-group os-form-group-transparent">
            <label for=""><?php esc_html_e( 'Duration', 'latepoint' ); ?></label>
            <select class="os-form-control os-affects-duration os-affects-price"
                    name="order_items[<?php echo esc_attr($order_item_id); ?>][bookings][<?php echo esc_attr($booking->get_form_id()); ?>][duration]"
                    id="">
				<?php if ( $booking->service_id ) {
					foreach ( $booking->service->get_all_durations_arr() as $extra_duration ) {
						$selected    = ( $extra_duration['duration'] == $booking->duration ) ? 'selected' : '';
						// translators: %d is number of minutes
						$custom_name = empty( $extra_duration['name'] ) ? sprintf( __( '%d minutes', 'latepoint' ), $extra_duration['duration'] ) : $extra_duration['name'];
						echo '<option value="' . esc_attr($extra_duration['duration']) . '" ' . $selected . '>' . esc_html($custom_name) . '</option>';
					}
				} ?>
            </select>
        </div>
    </div>
<?php
/**
 * Output right after a service selector on a booking data form on the order edit form
 *
 * @param {OsBookingModel} $booking instance of a booking model
 * @param {string} $order_item_id ID of an order item for this booking
 *
 * @since 5.0.0
 * @hook latepoint_booking_data_form_after_service
 *
 */
do_action( 'latepoint_booking_data_form_after_service', $booking, $order_item_id ); ?>
<?php if ( OsLocationHelper::count_locations( true ) > 1 ) { ?>
    <div class="os-row">
        <div class="os-col-12">
			<?php echo OsFormHelper::select_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][location_id]', __( 'Location', 'latepoint' ), OsLocationHelper::get_locations_list( true ), $booking->location_id, [ 'class' => 'location_id_holder location-selector' ] ); ?>
        </div>
    </div>
	<?php
} else {
	// single location exist in database - assign it automatically
	echo OsFormHelper::hidden_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][location_id]', OsLocationHelper::get_default_location_id( true ), [ 'class' => 'location_id_holder location-selector' ] );
} ?>

    <div class="os-row">
		<?php
		if ( OsAgentHelper::count_agents( true ) == 1 ) {
			echo OsFormHelper::hidden_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][agent_id]', $booking->agent_id, [ 'class' => 'agent-selector' ] );
			$status_field_size = 'os-col-12';
		} else {
			$status_field_size = 'os-col-6';
			?>
            <div class="os-col-6">
                <div class="agent-info-w <?php echo ( $booking->agent_id ) ? 'selected' : 'selecting'; ?>">
                    <div class="agents-selector-w">
                        <div class="os-form-group os-form-select-group os-form-group-transparent">
                            <label for=""><?php esc_html_e( 'Agent', 'latepoint' ); ?></label>
                            <select name="order_items[<?php echo esc_attr($order_item_id); ?>][bookings][<?php echo esc_attr($booking->get_form_id()); ?>][agent_id]"
                                    class="os-form-control agent-selector">
								<?php foreach ( $agents as $agent ) { ?>
                                    <option
                                            value="<?php echo esc_attr($agent->id); ?>" <?php if ( $agent->id == $booking->agent_id ) {
										echo 'selected';
									} ?>><?php echo esc_html($agent->get_full_name()); ?></option>
								<?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
		?>
        <div class="<?php echo esc_attr($status_field_size); ?>">
			<?php echo OsFormHelper::select_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][status]', __( 'Status', 'latepoint' ), OsBookingHelper::get_statuses_list(), $booking->status, array( 'placeholder' => __( 'Set Status', 'latepoint' ) ) ); ?>
        </div>
    </div>
    <div class="os-row">
        <div class="os-col-6">
			<?php echo OsFormHelper::text_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][start_date_formatted]', __( 'Start Date', 'latepoint' ), $booking->format_start_date(), array(
				'class'      => 'os-mask-date',
				'theme'      => 'simple',
				'data-route' => OsRouterHelper::build_route_name( 'bookings', 'quick_availability' )
			) ); ?>
        </div>
        <div class="os-col-6">
            <a href="#" data-route="<?php echo esc_attr(OsRouterHelper::build_route_name( 'bookings', 'quick_availability' )); ?>"
               class="latepoint-btn latepoint-btn-white open-quick-availability-btn trigger-quick-availability">
                <span><?php esc_html_e( 'Availability', 'latepoint' ); ?></span>
                <i class="latepoint-icon latepoint-icon-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="os-row">
        <div class="os-col-6">
            <div class="quick-start-time-w">
				<?php echo OsFormHelper::time_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][start_time]', __( 'Start Time', 'latepoint' ), $booking->start_time, true ); ?>
            </div>
        </div>
        <div class="os-col-6">
            <div
                    class="quick-end-time-w <?php if ( $booking->end_time && ( $booking->end_time <= $booking->start_time ) )
						echo 'ending-next-day' ?>">
				<?php echo OsFormHelper::time_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][end_time]', __( 'End Time', 'latepoint' ), $booking->end_time, true ); ?>
                <div class="plus-day-label"><?php esc_html_e( '+1 day', 'latepoint' ); ?></div>
            </div>
        </div>
    </div>
    <div class="os-row">
        <div class="os-col-6">
			<?php echo OsFormHelper::text_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][buffer_before]', __( 'Buffer Before', 'latepoint' ), $booking->buffer_before, [
				'theme' => 'simple',
				'class' => 'os-mask-minutes'
			] ); ?>
        </div>
        <div class="os-col-6">
			<?php echo OsFormHelper::text_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][buffer_after]', __( 'Buffer After', 'latepoint' ), $booking->buffer_after, [
				'theme' => 'simple',
				'class' => 'os-mask-minutes'
			] ); ?>
        </div>
    </div>

<?php
/**
 * Output right after a booking data form on the order edit form
 *
 * @param {OsBookingModel} $booking instance of a booking model
 *
 * @since 5.0.0
 * @hook latepoint_booking_data_form_after
 *
 */
do_action( 'latepoint_booking_data_form_after', $booking, $order_item_id ); ?>
<?php
echo OsFormHelper::hidden_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][order_item_id]', $booking->order_item_id );
echo OsFormHelper::hidden_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][id]', $booking->id );
echo OsFormHelper::hidden_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][service_id]', $booking->service_id, [ 'class' => 'os-affects-service-extras os-affects-price service-selector os-affects-custom-fields' ] );
echo OsFormHelper::hidden_field( 'order_items[' . $order_item_id . '][bookings][' . $booking->get_form_id() . '][form_id]', $booking->get_form_id() );
?>