<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="latepoint-settings-w os-form-w">
    <form action="" data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name( 'settings', 'update' )); ?>">
		<?php wp_nonce_field( 'update_settings' ); ?>
        <div class="white-box section-anchor" id="stickySectionAppointment">
            <div class="white-box-header">
                <div class="os-form-sub-header"><h3><?php esc_html_e( 'Appointment Settings', 'latepoint' ); ?></h3></div>
            </div>
            <div class="white-box-content no-padding">
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Statuses', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="os-row os-mb-3">
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::select_field( 'settings[default_booking_status]', __( 'Default status', 'latepoint' ), OsBookingHelper::get_statuses_list(), OsBookingHelper::get_default_booking_status() ); ?>
                            </div>
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::multi_select_field( 'settings[timeslot_blocking_statuses]', __( 'Statuses that block timeslot', 'latepoint' ), OsBookingHelper::get_statuses_list(), OsBookingHelper::get_timeslot_blocking_statuses() ); ?>
                            </div>
                        </div>
                        <div class="os-row os-mb-3">
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::multi_select_field( 'settings[need_action_statuses]', __( 'Statuses that appear on pending page', 'latepoint' ), OsBookingHelper::get_statuses_list(), OsBookingHelper::get_booking_statuses_for_pending_page() ); ?>
                            </div>
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::multi_select_field( 'settings[calendar_hidden_statuses]', __( 'Statuses hidden on calendar', 'latepoint' ), OsBookingHelper::get_statuses_list(), OsCalendarHelper::get_booking_statuses_hidden_from_calendar() ); ?>
                            </div>
                        </div>
                        <div class="os-row">
                            <div class="os-col-12">
								<?php echo OsFormHelper::text_field( 'settings[additional_booking_statuses]', __( 'Additional Statuses (comma separated)', 'latepoint' ), OsSettingsHelper::get_settings_value( 'additional_booking_statuses' ), [ 'theme' => 'simple' ] ); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Date and time', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="os-row os-mb-3">
                            <div class="os-col-6">
								<?php echo OsFormHelper::select_field( 'settings[time_system]', __( 'Time system', 'latepoint' ), OsTimeHelper::get_time_systems_list_for_select(), OsTimeHelper::get_time_system() ); ?>
                            </div>
                            <div class="os-col-6">
								<?php echo OsFormHelper::select_field( 'settings[date_format]', __( 'Date format', 'latepoint' ), OsTimeHelper::get_date_formats_list_for_select(), OsSettingsHelper::get_date_format() ); ?>
                            </div>
                        </div>
						<?php echo OsFormHelper::text_field( 'settings[timeblock_interval]', __( 'Selectable intervals', 'latepoint' ), OsSettingsHelper::get_default_timeblock_interval(), [
							'class' => 'os-mask-minutes',
							'theme' => 'simple'
						] ); ?>
                        <div class="os-row os-mb-3">
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::toggler_field( 'settings[show_booking_end_time]', __( 'Show appointment end time', 'latepoint' ), OsSettingsHelper::is_on( 'show_booking_end_time' ), false, false, [ 'sub_label' => __( 'Show booking end time during booking process and on summary', 'latepoint' ) ] ); ?>
                            </div>
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::toggler_field( 'settings[disable_verbose_date_output]', __( 'Disable verbose date output', 'latepoint' ), OsSettingsHelper::is_on( 'disable_verbose_date_output' ), false, false, [ 'sub_label' => __( 'Use number instead of name of the month when outputting dates', 'latepoint' ) ] ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="white-box section-anchor" id="stickySectionRestrictions">
            <div class="white-box-header">
                <div class="os-form-sub-header"><h3><?php esc_html_e( 'Restrictions', 'latepoint' ); ?></h3></div>
            </div>
            <div class="white-box-content no-padding">

                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Time Restrictions', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="latepoint-message latepoint-message-subtle"><?php esc_html_e( 'You can set restrictions on earliest/latest dates in the future when your customer can place an appointment. You can either use a relative values like for example "+1 month", "+2 weeks", "+5 days", "+3 hours", "+30 minutes" (entered without quotes), or you can use a fixed date in format YYYY-MM-DD. Leave blank to remove any limitations.', 'latepoint' ); ?></div>
                        <div class="os-row">
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::text_field( 'settings[earliest_possible_booking]', __( 'Earliest Possible Booking', 'latepoint' ), OsSettingsHelper::get_settings_value( 'earliest_possible_booking' ), [ 'theme' => 'simple' ] ); ?>
                            </div>
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::text_field( 'settings[latest_possible_booking]', __( 'Latest Possible Booking', 'latepoint' ), OsSettingsHelper::get_settings_value( 'latest_possible_booking' ), [ 'theme' => 'simple' ] ); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Quantity Restrictions', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
						<?php echo OsFormHelper::text_field( 'settings[max_future_bookings_per_customer]', __( 'Maximum Number of Future Bookings per Customer', 'latepoint' ), OsSettingsHelper::get_settings_value( 'max_future_bookings_per_customer' ), [ 'theme' => 'simple' ] ); ?>
                    </div>
                </div>
				<?php
				/**
				 * Plug after general settings section called restrictions
				 *
				 * @since 5.0.0
				 * @hook latepoint_general_settings_section_restrictions_after
				 *
				 */
				do_action( 'latepoint_general_settings_section_restrictions_after' ); ?>
            </div>
        </div>
        <div class="white-box section-anchor" id="stickySectionCurrency">
            <div class="white-box-header">
                <div class="os-form-sub-header"><h3><?php esc_html_e( 'Currency & Price', 'latepoint' ); ?></h3></div>
            </div>
            <div class="white-box-content no-padding">
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Symbol', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="os-row">
                            <div class="os-col-lg-4">
								<?php echo OsFormHelper::text_field( 'settings[currency_symbol_before]', __( 'Symbol before the price', 'latepoint' ), OsSettingsHelper::get_settings_value( 'currency_symbol_before', '$' ), [ 'theme' => 'simple' ] ); ?>
                            </div>
                            <div class="os-col-lg-4">
								<?php echo OsFormHelper::text_field( 'settings[currency_symbol_after]', __( 'Symbol after the price', 'latepoint' ), OsSettingsHelper::get_settings_value( 'currency_symbol_after' ), [ 'theme' => 'simple' ] ); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Formatting', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="os-row">
                            <div class="os-col-lg-4">
								<?php echo OsFormHelper::select_field( 'settings[thousand_separator]', __( 'Thousand Separator', 'latepoint' ), [
									',' => __( 'Comma', 'latepoint' ) . ' (1,000)',
									'.' => __( 'Dot', 'latepoint' ) . ' (1.000)',
									' ' => __( 'Space', 'latepoint' ) . ' (1 000)',
									''  => __( 'None', 'latepoint' ) . ' (1000)'
								], OsSettingsHelper::get_settings_value( 'thousand_separator', ',' ) ); ?>
                            </div>
                            <div class="os-col-lg-4">
								<?php echo OsFormHelper::select_field( 'settings[decimal_separator]', __( 'Decimal Separator', 'latepoint' ), [
									'.' => __( 'Dot', 'latepoint' ) . ' (0.99)',
									',' => __( 'Comma', 'latepoint' ) . ' (0,99)'
								], OsSettingsHelper::get_settings_value( 'decimal_separator', '.' ) ); ?>
                            </div>
                            <div class="os-col-lg-4">
								<?php echo OsFormHelper::select_field( 'settings[number_of_decimals]', __( 'Number of Decimals', 'latepoint' ), [ 0, 1, 2, 3, 4 ], OsSettingsHelper::get_settings_value( 'number_of_decimals', '2' ) ); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Prices', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <?php echo OsFormHelper::toggler_field( 'settings[hide_breakdown_if_subtotal_zero]', __( 'Do not show price breakdown, if service price is zero', 'latepoint' ), OsSettingsHelper::is_on( 'hide_breakdown_if_subtotal_zero' ) ); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="white-box section-anchor" id="stickySectionPhone">
            <div class="white-box-header">
                <div class="os-form-sub-header"><h3><?php esc_html_e( 'Phone Settings', 'latepoint' ); ?></h3></div>
            </div>
            <div class="white-box-content no-padding">
                <div class="sub-section-row phone-country-picker-settings">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Countries', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="phone-country-picker-settings">
                            <div class="os-row os-mb-2">
                                <div class="os-col-lg-4">
									<?php echo OsFormHelper::select_field( 'settings[list_of_phone_countries]', __( 'Countries shown in phone field', 'latepoint' ), [
										LATEPOINT_ALL => __( 'Show all countries', 'latepoint' ),
										'select'      => __( 'Show selected countries', 'latepoint' )
									], OsSettingsHelper::get_settings_value( 'list_of_phone_countries', LATEPOINT_ALL ) ); ?>
                                </div>
                                <div class="os-col-lg-8">
									<?php echo OsFormHelper::select_field( 'settings[default_phone_country]', __( 'Default Country (if not auto-detected)', 'latepoint' ), OsUtilHelper::get_countries_list(), OsSettingsHelper::get_default_phone_country() ); ?>
                                </div>
                            </div>
                            <div class="os-row">
                                <div class="os-col-12 select-phone-countries-wrapper"
                                     style="<?php echo ( OsSettingsHelper::get_settings_value( 'list_of_phone_countries', LATEPOINT_ALL ) == LATEPOINT_ALL ) ? 'display: none;' : ''; ?>">
									<?php echo OsFormHelper::multi_select_field( 'settings[included_phone_countries]', __( 'Select countries available for phone number field', 'latepoint' ), OsUtilHelper::get_countries_list(), OsSettingsHelper::get_included_phone_countries() ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sub-section-row phone-country-picker-settings">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Validation', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
						<?php echo OsFormHelper::toggler_field( 'settings[validate_phone_number]', __( 'Validate phone typed fields if they are set as required', 'latepoint' ), OsSettingsHelper::is_on( 'validate_phone_number' ), false, false, [ 'sub_label' => __( 'Reject invalid phone for customers and agents if the phone field is set as required', 'latepoint' ) ] ); ?>
						<?php echo OsFormHelper::toggler_field( 'settings[mask_phone_number_fields]', __( 'Format phone number on input', 'latepoint' ), OsSettingsHelper::is_on( 'mask_phone_number_fields', LATEPOINT_VALUE_ON ), false, false, [ 'sub_label' => __( 'Applies formatting on phone fields based on the country selected (not recommended for countries that have multiple NSN lengths)', 'latepoint' ) ] ); ?>
						<?php echo OsFormHelper::toggler_field( 'settings[show_dial_code_with_flag]', __( 'Show country dial code next to flag', 'latepoint' ), OsSettingsHelper::is_enabled_show_dial_code_with_flag(), false, false, [ 'sub_label' => __( 'If enabled, will show a country code next to a flag, for example +1 for United States', 'latepoint' ) ] ); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="white-box section-anchor" id="stickySectionAgent">
            <div class="white-box-header">
                <div class="os-form-sub-header"><h3><?php esc_html_e( 'Timeslot Availability Logic', 'latepoint' ); ?></h3>
                </div>
            </div>
            <div class="white-box-content no-padding">
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Restrictions', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
						<?php echo OsFormHelper::toggler_field( 'settings[one_agent_at_location]', __( 'Location can only be used by one agent at a time', 'latepoint' ), OsSettingsHelper::is_on( 'one_agent_at_location' ), '', 'large', [ 'sub_label' => __( 'At any given location, only one agent can be booked at a time', 'latepoint' ) ] ); ?>
						<?php echo OsFormHelper::toggler_field( 'settings[one_location_at_time]', __( 'Agents can only be present in one location at a time', 'latepoint' ), OsSettingsHelper::is_on( 'one_location_at_time' ), '', 'large', [ 'sub_label' => __( 'If an agent is booked at one location, he will not be able to accept any bookings for the same timeslot at other locations', 'latepoint' ) ] ); ?>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Permissions', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
						<?php echo OsFormHelper::toggler_field( 'settings[multiple_services_at_time]', __( 'One agent can perform different services simultaneously', 'latepoint' ), OsSettingsHelper::is_on( 'multiple_services_at_time' ), '', 'large', [ 'sub_label' => __( 'Allows an agent to be booked for different services within the same timeslot', 'latepoint' ) ] ); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="white-box section-anchor" id="stickySectionCustomer">
            <div class="white-box-header">
                <div class="os-form-sub-header"><h3><?php esc_html_e( 'Customer Settings', 'latepoint' ); ?></h3></div>
            </div>
            <div class="white-box-content no-padding">
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Rescheduling', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
						<?php echo OsFormHelper::toggler_field( 'settings[allow_customer_booking_reschedule]', __( 'Allow customers reschedule their bookings', 'latepoint' ), OsSettingsHelper::is_on( 'allow_customer_booking_reschedule' ), 'reschedule_settings', 'normal', [ 'sub_label' => __( 'If enable, shows a button on customer cabinet to reschedule an appointment', 'latepoint' ) ] ); ?>
                        <div class="os-mb-2"
                             id="reschedule_settings" <?php echo OsSettingsHelper::is_on( 'allow_customer_booking_reschedule' ) ? '' : 'style="display:none"' ?>>
							<?php echo OsFormHelper::toggler_field( 'settings[limit_when_customer_can_reschedule]', __( 'Set restriction on when customer can reschedule', 'latepoint' ), OsSettingsHelper::is_on( 'limit_when_customer_can_reschedule' ), 'reschedule_limit_settings' ); ?>
                            <div class="os-mb-2"
                                 id="reschedule_limit_settings" <?php echo OsSettingsHelper::is_on( 'limit_when_customer_can_reschedule' ) ? '' : 'style="display:none"' ?>>
                                <div class="merged-fields os-mt-1">
                                    <div class="merged-label"><?php esc_html_e( 'Can reschedule when it is at least', 'latepoint' ); ?></div>
									<?php echo OsFormHelper::text_field( 'settings[reschedule_limit_value]', false, OsSettingsHelper::get_settings_value( 'reschedule_limit_value', 5 ), [ 'placeholder' => __( 'Value', 'latepoint' ) ] ); ?>
									<?php echo OsFormHelper::select_field( 'settings[reschedule_limit_unit]', false,
										array(
											'minute' => __( 'minutes', 'latepoint' ),
											'hour'   => __( 'hours', 'latepoint' ),
											'day'    => __( 'days', 'latepoint' )
										),
										OsSettingsHelper::get_settings_value( 'reschedule_limit_unit', 'hour' ) ); ?>
                                    <div class="merged-label"><?php esc_html_e( 'before appointment start time', 'latepoint' ); ?></div>
                                </div>
                            </div>
							<?php echo OsFormHelper::toggler_field( 'settings[change_status_on_customer_reschedule]', __( 'Change booking status when customer reschedules', 'latepoint' ), OsSettingsHelper::is_on( 'change_status_on_customer_reschedule' ), 'reschedule_change_status_settings' ); ?>
                            <div class="os-mb-2"
                                 id="reschedule_change_status_settings" <?php echo OsSettingsHelper::is_on( 'change_status_on_customer_reschedule' ) ? '' : 'style="display:none"' ?>>
                                <div class="merged-fields os-mt-1">
                                    <div class="merged-label"><?php esc_html_e( 'Change status to:', 'latepoint' ); ?></div>
									<?php echo OsFormHelper::select_field( 'settings[status_to_set_after_customer_reschedule]', false,
										OsBookingHelper::get_statuses_list(),
										OsSettingsHelper::get_settings_value( 'status_to_set_after_customer_reschedule', LATEPOINT_BOOKING_STATUS_PENDING ) ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Cancellation', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
						<?php echo OsFormHelper::toggler_field( 'settings[allow_customer_booking_cancellation]', __( 'Allow customers cancel their bookings', 'latepoint' ), OsSettingsHelper::is_on( 'allow_customer_booking_cancellation' ), 'cancellation_settings', 'normal', [ 'sub_label' => __( 'If enable, shows a button on customer cabinet to cancel an appointment', 'latepoint' ) ] ); ?>
                        <div class="os-mb-2"
                             id="cancellation_settings" <?php echo OsSettingsHelper::is_on( 'allow_customer_booking_cancellation' ) ? '' : 'style="display:none"' ?>>
							<?php echo OsFormHelper::toggler_field( 'settings[limit_when_customer_can_cancel]', __( 'Set restriction on when customer can cancel', 'latepoint' ), OsSettingsHelper::is_on( 'limit_when_customer_can_cancel' ), 'cancellation_limit_settings' ); ?>
                            <div class="os-mb-4"
                                 id="cancellation_limit_settings" <?php echo OsSettingsHelper::is_on( 'limit_when_customer_can_cancel' ) ? '' : 'style="display:none"' ?>>
                                <div class="merged-fields os-mt-1">
                                    <div class="merged-label"><?php esc_html_e( 'Can cancel when it is at least', 'latepoint' ); ?></div>
									<?php echo OsFormHelper::text_field( 'settings[cancellation_limit_value]', false, OsSettingsHelper::get_settings_value( 'cancellation_limit_value', 5 ), [ 'placeholder' => __( 'Value', 'latepoint' ) ] ); ?>
									<?php echo OsFormHelper::select_field( 'settings[cancellation_limit_unit]', false,
										array(
											'minute' => __( 'minutes', 'latepoint' ),
											'hour'   => __( 'hours', 'latepoint' ),
											'day'    => __( 'days', 'latepoint' )
										),
										OsSettingsHelper::get_settings_value( 'cancellation_limit_unit', 'hour' ) ); ?>
                                    <div class="merged-label"><?php esc_html_e( 'before appointment start time', 'latepoint' ); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Customer Cabinet', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="os-mt-2">
							<?php echo OsFormHelper::text_field( 'settings[customer_dashboard_book_shortcode]', __( 'Shortcode for contents of New Appointment tab', 'latepoint' ), OsSettingsHelper::get_settings_value( 'customer_dashboard_book_shortcode', '[latepoint_book_form]' ), [ 'theme' => 'simple' ] ); ?>
                        </div>
                        <div class="os-mt-2">
                            <div class="latepoint-message latepoint-message-subtle"><?php esc_html_e( 'You can set attributes for a new appointment button tile in a format', 'latepoint' ); ?>
                                <strong>data-selected-agent="ID" data-selected-location="ID" etc...</strong></div>
							<?php echo OsFormHelper::text_field( 'settings[customer_dashboard_book_button_attributes]', __( 'Attributes for New Appointment button', 'latepoint' ), OsSettingsHelper::get_settings_value( 'customer_dashboard_book_button_attributes', '' ), [ 'theme' => 'simple' ] ); ?>
                        </div>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Authentication', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
						<?php echo OsFormHelper::toggler_field( 'settings[wp_users_as_customers]', __( 'Use WordPress users as customers', 'latepoint' ), OsSettingsHelper::is_on( 'wp_users_as_customers' ), false, false, [ 'sub_label' => __( 'Customers can login using their WordPress credentials' ) ] ); ?>
						<?php echo OsFormHelper::toggler_field( 'settings[steps_require_setting_password]', __( 'Require customers to set password', 'latepoint' ), OsSettingsHelper::is_on( 'steps_require_setting_password' ), false, false, [ 'sub_label' => __( 'Shows password field on registration step', 'latepoint' ) ] ); ?>
						<?php echo OsFormHelper::toggler_field( 'settings[steps_hide_login_register_tabs]', __( 'Remove login and register tabs', 'latepoint' ), OsSettingsHelper::is_on( 'steps_hide_login_register_tabs' ), false, false, [ 'sub_label' => __( 'This will disable ability for customers to login or register on booking form', 'latepoint' ) ] ); ?>
						<?php echo OsFormHelper::toggler_field( 'settings[steps_hide_registration_prompt]', __( 'Hide "Create Account" prompt on confirmation step', 'latepoint' ), OsSettingsHelper::is_on( 'steps_hide_registration_prompt' ) ); ?>
                    </div>
                </div>

				<?php
				/**
				 * Plug after customer general settings output
				 *
				 * @since 5.0.15
				 * @hook latepoint_settings_general_customer_after
				 *
				 */
				do_action( 'latepoint_settings_general_customer_after' ); ?>
            </div>
        </div>
        <div class="white-box section-anchor" id="stickySectionSetup">
            <div class="white-box-header">
                <div class="os-form-sub-header"><h3><?php esc_html_e( 'Setup Pages', 'latepoint' ); ?></h3></div>
            </div>
            <div class="white-box-content no-padding">
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Set Page URLs', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
						<?php echo OsFormHelper::text_field( 'settings[page_url_customer_dashboard]', __( 'Customer Dashboard Page URL', 'latepoint' ), OsSettingsHelper::get_customer_dashboard_url( false ), [ 'theme' => 'simple' ] ); ?>
						<?php echo OsFormHelper::text_field( 'settings[page_url_customer_login]', __( 'Customer Login Page URL', 'latepoint' ), OsSettingsHelper::get_customer_login_url( false ), [ 'theme' => 'simple' ] ); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="white-box section-anchor" id="stickySectionOther">
            <div class="white-box-header">
                <div class="os-form-sub-header"><h3><?php esc_html_e( 'Other Settings', 'latepoint' ); ?></h3></div>
            </div>
            <div class="white-box-content no-padding">
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Business Information', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="os-row os-mb-2">
                            <div class="os-col-lg-12">
								<?php echo OsFormHelper::media_uploader_field( 'settings[business_logo]', 0, __( 'Company Logo', 'latepoint' ), __( 'Remove Image', 'latepoint' ), OsSettingsHelper::get_settings_value( 'business_logo' ) ); ?>
                            </div>
                        </div>
                        <div class="os-row">
                            <div class="os-col-lg-3">
								<?php echo OsFormHelper::text_field( 'settings[business_name]', __( 'Company Name', 'latepoint' ), OsSettingsHelper::get_settings_value( 'business_name' ), [ 'theme' => 'simple' ] ); ?>
                            </div>
                            <div class="os-col-lg-3">
								<?php echo OsFormHelper::text_field( 'settings[business_phone]', __( 'Business Phone', 'latepoint' ), OsSettingsHelper::get_settings_value( 'business_phone' ), [ 'theme' => 'simple' ] ); ?>
                            </div>
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::text_field( 'settings[business_address]', __( 'Business Address', 'latepoint' ), OsSettingsHelper::get_settings_value( 'business_address' ), [ 'theme' => 'simple' ] ); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Calendar Settings', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
						<?php echo OsFormHelper::text_field( 'settings[day_calendar_min_height]', __( 'Minimum height of a daily calendar (in pixels)', 'latepoint' ), OsSettingsHelper::get_day_calendar_min_height(), [ 'theme' => 'simple' ] ); ?>


                        <div class="latepoint-message latepoint-message-subtle"><?php esc_html_e( 'You can use variables in your booking template, they will be replaced with a value for the booking. ', 'latepoint' ) ?><?php echo OsUtilHelper::template_variables_link_html(); ?></div>
						<?php echo OsFormHelper::text_field( 'settings[booking_template_for_calendar]', __( 'Booking tile information to display on calendar', 'latepoint' ), OsSettingsHelper::get_booking_template_for_calendar(), [ 'theme' => 'simple' ] ); ?>
                    </div>
                </div>
                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Conversion Tracking', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="latepoint-message latepoint-message-subtle">
                            <div><?php esc_html_e( 'You can include some javascript or html that will be appended to the confirmation step. For example you can track ad conversions by triggering a tracking code or a facebook pixel. You can use these variables within your code. Click on the variable to copy.', 'latepoint' ); ?></div>
                        </div>
                        <div class="tracking-info-w">
                            <div class="available-vars-w">
                                <div class="available-vars-i">
                                    <div class="available-vars-block">
                                        <ul>
                                            <li>
                                                <span class="var-label"><?php esc_html_e( 'Order ID#:', 'latepoint' ); ?></span>
                                                <span class="var-code os-click-to-copy">{{order_id}}</span>
                                            </li>
                                            <li>
                                                <span class="var-label"><?php esc_html_e( 'Customer ID#:', 'latepoint' ); ?></span>
                                                <span class="var-code os-click-to-copy">{{customer_id}}</span>
                                            </li>
                                            <li>
                                                <span class="var-label"><?php esc_html_e( 'Order Total:', 'latepoint' ); ?></span>
                                                <span class="var-code os-click-to-copy">{{order_total}}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
							<?php echo OsFormHelper::textarea_field( 'settings[confirmation_step_tracking_code]', false, OsSettingsHelper::get_settings_value( 'confirmation_step_tracking_code', '' ), array(
								'theme' => 'bordered',
								'rows' => 9,
								'placeholder' => __( 'Enter Tracking code here', 'latepoint' )
							), [ 'class' => 'tracking-code-input-w' ] ); ?>
                        </div>
                    </div>
                </div>

                <div class="sub-section-row">
                    <div class="sub-section-label">
                        <h3><?php esc_html_e( 'Data Tables', 'latepoint' ) ?></h3>
                    </div>
                    <div class="sub-section-content">
                        <div class="os-row">
                            <div class="os-col-lg-6">
								<?php echo OsFormHelper::toggler_field( 'settings[allow_non_admins_download_csv]', __( 'Allow non admins to download table data as csv', 'latepoint' ), OsSettingsHelper::is_on( 'allow_non_admins_download_csv' ), false, false, [ 'sub_label' => __( 'Only admins will be able to download table data as csv', 'latepoint' ) ] ); ?>
                            </div>

                            <div class="os-col-lg-3">
								<?php echo OsFormHelper::select_field( 'settings[number_of_records_per_page]', __( 'Number of records per page', 'latepoint' ), [ 20, 50, 100, 200 ], OsSettingsHelper::get_settings_value( 'number_of_records_per_page', 20 ) ); ?>
                            </div>
                        </div>
                    </div>
                </div>

				<?php
				/**
				 * Plug after other general settings output
				 *
				 * @since 4.7.0
				 * @hook latepoint_settings_general_other_after
				 *
				 */
				do_action( 'latepoint_settings_general_other_after' ); ?>
            </div>
        </div>
		<?php
		/**
		 * Plug after general settings output, before buttons
		 *
		 * @since 4.7.8
		 * @hook latepoint_settings_general_after
		 *
		 */
		do_action( 'latepoint_settings_general_after' ); ?>
        <div class="os-form-buttons">
			<?php echo OsFormHelper::button( 'submit', __( 'Save Settings', 'latepoint' ), 'submit', [ 'class' => 'latepoint-btn' ] ); ?>
        </div>
    </form>
</div>