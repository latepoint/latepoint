<?php

class OsBookingHelper {


	/**
	 * @param OsBookingModel $booking
	 *
	 * @return mixed|void
	 *
	 * Returns full amount to charge in database format 1999.0000
	 *
	 */
	public static function calculate_full_amount_for_booking( OsBookingModel $booking ) {
		if ( ! $booking->service_id ) {
			return 0;
		}
		$amount = self::calculate_full_amount_for_service( $booking );
		$amount = apply_filters( 'latepoint_calculate_full_amount_for_booking', $amount, $booking );
		$amount = OsMoneyHelper::pad_to_db_format( $amount );

		return $amount;
	}


	/**
	 * @param OsBookingModel $booking
	 *
	 * @return mixed|void
	 *
	 */
	public static function calculate_full_amount_for_service( OsBookingModel $booking ) {
		if ( ! $booking->service_id ) {
			return 0;
		}
		$service            = new OsServiceModel( $booking->service_id );
		$amount_for_service = $service->get_full_amount_for_duration( $booking->duration );
		$amount_for_service = apply_filters( 'latepoint_full_amount_for_service', $amount_for_service, $booking );

		return $amount_for_service;
	}


	/**
	 * @param OsBookingModel $booking
	 *
	 * @return mixed|void
	 *
	 * Returns deposit amount to charge in database format 1999.0000
	 *
	 */
	public static function calculate_deposit_amount_to_charge( OsBookingModel $booking ) {
		if ( ! $booking->service_id ) {
			return 0;
		}
		$service            = new OsServiceModel( $booking->service_id );
		$amount = $service->get_deposit_amount_for_duration( $booking->duration );
		$amount = apply_filters( 'latepoint_deposit_amount_for_service', $amount, $booking );
		$amount             = OsMoneyHelper::pad_to_db_format( $amount );

		return $amount;
	}


	/**
	 * @param array $item_data
	 *
	 * @return OsBookingModel
	 */
	public static function build_booking_model_from_item_data( array $item_data ): OsBookingModel {
		$booking = new OsBookingModel();
		if ( $item_data['id'] ) {
			$booking = $booking->load_by_id( $item_data['id'] );
			if ( ! $booking ) {
				$booking = new OsBookingModel();
			}
		}
		$booking->set_data( $item_data );
		// get buffers from service and set to booking object
		if ( ! isset( $item_data['buffer_before'] ) && ! isset( $item_data['buffer_after'] ) ) {
			$booking->set_buffers();
		}
		if ( empty( $booking->end_time ) ) {
			$booking->calculate_end_date_and_time();
		}
		if ( empty( $booking->end_date ) ) {
			$booking->calculate_end_date();
		}
		$booking->set_utc_datetimes();

		return $booking;
	}

	public static function get_booking_id_and_manage_ability_by_key( string $key ) {
		$booking_id = OsMetaHelper::get_booking_id_by_meta_value( "key_to_manage_for_agent", $key );
		if ( $booking_id ) {
			return [ 'booking_id' => $booking_id, 'for' => 'agent' ];
		}

		$booking_id = OsMetaHelper::get_booking_id_by_meta_value( "key_to_manage_for_customer", $key );
		if ( $booking_id ) {
			return [ 'booking_id' => $booking_id, 'for' => 'customer' ];
		}

		return false;
	}

	public static function is_action_allowed( string $action, OsBookingModel $booking, string $key = '' ) {
		$is_allowed = false;
		if ( empty( $booking->id ) ) {
			return false;
		}
		if ( ! in_array( $action, [ 'cancel', 'reschedule' ] ) ) {
			return false;
		}
		$action_result = false;
		switch ( $action ) {
			case 'cancel':
				$action_result = OsCustomerHelper::can_cancel_booking( $booking );
				break;
			case 'reschedule':
				$action_result = OsCustomerHelper::can_reschedule_booking( $booking );
				break;
		}
		if ( ! empty( $key ) ) {
			// key is passed, check if allowed through key
			$agent_key_meta    = OsMetaHelper::get_booking_meta_by_key( 'key_to_manage_for_agent', $booking->id );
			$customer_key_meta = OsMetaHelper::get_booking_meta_by_key( 'key_to_manage_for_customer', $booking->id );
			if ( $key == $agent_key_meta ) {
				// agent can do everything, no need to check for action
				$is_allowed = true;
			} elseif ( $key == $customer_key_meta ) {
				// customer
				$is_allowed = $action_result;
			}
		} elseif ( OsAuthHelper::get_logged_in_customer_id() == $booking->customer_id ) {
			$is_allowed = $action_result;
		}

		return $is_allowed;
	}

	public static function generate_add_to_calendar_links( OsBookingModel $booking, $key = false ): string {
		$html = '<div class="add-to-calendar-types">
							<div class="atc-heading-wrapper">
								<div class="atc-heading">' . esc_html__( 'Calendar Type', 'latepoint' ) . '</div>
								<div class="close-calendar-types"></div>
							</div>
							<a href="' . esc_url($booking->get_ical_download_link( $key )) . '" target="_blank" class="atc-type atc-type-apple">
								<div class="atc-type-image"></div>
								<div class="atc-type-name">' . esc_html__( 'Apple Calendar', 'latepoint' ) . '</div>
							</a>
							<a href="' . esc_url($booking->get_url_for_add_to_calendar_button( 'google' )) . '" target="_blank" class="atc-type atc-type-google">
								<div class="atc-type-image"></div>
								<div class="atc-type-name">' . esc_html__( 'Google Calendar', 'latepoint' ) . '</div>
							</a>
							<a href="' . esc_url($booking->get_url_for_add_to_calendar_button( 'outlook' )) . '" target="_blank" class="atc-type atc-type-outlook">
								<div class="atc-type-image"></div>
								<div class="atc-type-name">' . esc_html__( 'Outlook.com', 'latepoint' ) . '</div>
							</a>
							<a href="' . esc_url($booking->get_url_for_add_to_calendar_button( 'outlook' )) . '" target="_blank" class="atc-type atc-type-office-365">
								<div class="atc-type-image"></div>
								<div class="atc-type-name">' . esc_html__( 'Microsoft 365', 'latepoint' ) . '</div>
							</a>
						</div>';

		return $html;
	}


	public static function get_bookings_for_select( $should_be_in_future = false ) {
		$bookings = new OsBookingModel();
		if ( $should_be_in_future ) {
			$bookings = $bookings->should_be_in_future();
		}
		$bookings         = $bookings->order_by( 'id desc' )->set_limit( 100 )->get_results_as_models();
		$bookings_options = [];
		foreach ( $bookings as $booking ) {
			$name               = $booking->service->name . ', ' . $booking->agent->full_name . ', ' . $booking->customer->full_name . ' [' . $booking->booking_code . ' : ' . $booking->id . ']';
			$bookings_options[] = [ 'value' => $booking->id, 'label' => esc_html( $name ) ];
		}

		return $bookings_options;
	}

	/**
	 *
	 * Determine whether to show
	 *
	 * @param $rows
	 *
	 * @return bool
	 */
	public static function is_breakdown_free( $rows ) {
		return ( ( empty( $rows['subtotal']['raw_value'] ) || ( (float) $rows['subtotal']['raw_value'] <= 0 ) ) && ( empty( $rows['total']['raw_value'] ) || ( (float) $rows['total']['raw_value'] <= 0 ) ) );
	}

	public static function output_price_breakdown( $rows ) {
		foreach ( $rows['before_subtotal'] as $row ) {
			self::output_price_breakdown_row( $row );
		}
		// if there is nothing between subtotal and total - don't show subtotal as it will be identical to total
		if ( ! empty( $rows['after_subtotal'] ) ) {
			if ( ! empty( $rows['subtotal'] ) ) {
				echo '<div class="subtotal-separator"></div>';
				self::output_price_breakdown_row( $rows['subtotal'] );
			}
			foreach ( $rows['after_subtotal'] as $row ) {
				self::output_price_breakdown_row( $row );
			}
		}
		if ( ! empty( $rows['total'] ) ) {
			self::output_price_breakdown_row( $rows['total'] );
		}
		if ( ! empty( $rows['payments'] ) ) {
			foreach ( $rows['payments'] as $row ) {
				self::output_price_breakdown_row( $row );
			}
		}
		if ( ! empty( $rows['balance'] ) ) {
			self::output_price_breakdown_row( $rows['balance'] );
		}
	}

	public static function output_price_breakdown_row( $row ) {
		if ( ! empty( $row['items'] ) ) {
			if ( ! empty( $row['heading'] ) ) {
				echo '<div class="summary-box-heading"><div class="sbh-item">' . esc_html($row['heading']) . '</div><div class="sbh-line"></div></div>';
			}
			foreach ( $row['items'] as $row_item ) {
				self::output_price_breakdown_row( $row_item );
			}
		} else {
			$extra_class = '';
			if ( isset( $row['style'] ) && $row['style'] == 'strong' ) {
				$extra_class .= ' spi-strong';
			}
			if ( isset( $row['style'] ) && $row['style'] == 'total' ) {
				$extra_class .= ' spi-total';
			}
			if ( isset( $row['type'] ) && $row['type'] == 'credit' ) {
				$extra_class .= ' spi-positive';
			}
			?>
            <div class="summary-price-item-w <?php echo esc_attr($extra_class); ?>">
                <div class="spi-name">
					<?php echo $row['label']; ?>
					<?php if ( ! empty( $row['note'] ) ) {
						echo '<span class="pi-note">' . esc_html($row['note']) . '</span>';
					} ?>
					<?php if ( ! empty( $row['badge'] ) ) {
						echo '<span class="pi-badge">' . esc_html($row['badge']) . '</span>';
					} ?>
                </div>
                <div class="spi-price"><?php echo esc_html($row['value']); ?></div>
            </div>
			<?php
		}
	}

	public static function output_price_breakdown_row_as_input_field( $row, $base_name ) {
		$field_name = $base_name . '[' . OsUtilHelper::random_text( 'alnum', 8 ) . ']';
		if ( ! empty( $row['items'] ) ) {
			echo OsFormHelper::hidden_field( $field_name . '[heading]', $row['heading'] ?? '' );
			foreach ( $row['items'] as $row_item ) {
				self::output_price_breakdown_row_as_input_field( $row_item, $field_name . '[items]' );
			}
		} else {
			$wrapper_class = ( $row['raw_value'] < 0 ) ? [ 'class' => 'green-value-input' ] : [];
			$label         = $row['label'] ?? '';
			if ( ! empty( $row['note'] ) ) {
				$label .= ' ' . $row['note'];
			}
			echo OsFormHelper::money_field( $field_name . '[value]', $label, $row['raw_value'], [ 'theme' => 'right-aligned' ], [], $wrapper_class );
			echo OsFormHelper::hidden_field( $field_name . '[label]', $row['label'] ?? '' );
			echo OsFormHelper::hidden_field( $field_name . '[style]', $row['style'] ?? '' );
			echo OsFormHelper::hidden_field( $field_name . '[type]', $row['type'] ?? '' );
			echo OsFormHelper::hidden_field( $field_name . '[note]', $row['note'] ?? '' );
			echo OsFormHelper::hidden_field( $field_name . '[badge]', $row['badge'] ?? '' );
		}
		if ( ! empty( $row['sub_items'] ) ) {
			foreach ( $row['sub_items'] as $row_item ) {
				self::output_price_breakdown_row_as_input_field( $row_item, $field_name . '[sub_items]' );
			}
		}
	}

	/**
	 * @param \LatePoint\Misc\Filter $filter
	 * @param bool $accessed_from_backend
	 *
	 * @return array
	 */
	public static function get_blocked_periods_grouped_by_day( \LatePoint\Misc\Filter $filter, bool $accessed_from_backend = false ): array {
		$grouped_blocked_periods = [];

		if ( $filter->date_from ) {
			$date_from = OsWpDateTime::os_createFromFormat( 'Y-m-d', $filter->date_from );
			$date_to   = ( $filter->date_to ) ? OsWpDateTime::os_createFromFormat( 'Y-m-d', $filter->date_to ) : OsWpDateTime::os_createFromFormat( 'Y-m-d', $filter->date_from );

			# Loop through days to fill in days that might have no bookings
			for ( $day = clone $date_from; $day->format( 'Y-m-d' ) <= $date_to->format( 'Y-m-d' ); $day->modify( '+1 day' ) ) {
				$grouped_blocked_periods[ $day->format( 'Y-m-d' ) ] = [];
			}
		}
		if ( ! $accessed_from_backend ) {
			$today                     = new OsWpDateTime( 'today' );
			$earliest_possible_booking = OsSettingsHelper::get_settings_value( 'earliest_possible_booking', false );
			$block_end_datetime        = OsTimeHelper::now_datetime_object();
			if ( $earliest_possible_booking ) {
				try {
					$block_end_datetime->modify( $earliest_possible_booking );
				} catch ( Exception $e ) {
					$block_end_datetime = OsTimeHelper::now_datetime_object();
				}
			}
			for ( $day = clone $today; $day->format( 'Y-m-d' ) <= $block_end_datetime->format( 'Y-m-d' ); $day->modify( '+1 day' ) ) {
				// loop days from now to earliest possible booking and block timeslots if these days were actually requested
				if ( isset( $grouped_blocked_periods[ $day->format( 'Y-m-d' ) ] ) ) {
					$grouped_blocked_periods[ $day->format( 'Y-m-d' ) ][] = new \LatePoint\Misc\BlockedPeriod( [
						'start_time' => 0,
						'end_time'   => ( $day->format( 'Y-m-d' ) < $block_end_datetime->format( 'Y-m-d' ) ) ? 24 * 60 : OsTimeHelper::convert_datetime_to_minutes( $block_end_datetime ),
						'start_date' => $day->format( 'Y-m-d' ),
						'end_date'   => $day->format( 'Y-m-d' )
					] );
				}
			}
		}

		$grouped_blocked_periods = apply_filters( 'latepoint_blocked_periods_for_range', $grouped_blocked_periods, $filter );

		return $grouped_blocked_periods;
	}

	/**
	 * @param \LatePoint\Misc\Filter $filter
	 *
	 * @return array
	 */
	public static function get_booked_periods_grouped_by_day( \LatePoint\Misc\Filter $filter ): array {
		$booked_periods = self::get_booked_periods( $filter );

		$grouped_booked_periods = [];
		if ( $filter->date_from ) {
			$date_from = OsWpDateTime::os_createFromFormat( 'Y-m-d', $filter->date_from );
			$date_to   = ( $filter->date_to ) ? OsWpDateTime::os_createFromFormat( 'Y-m-d', $filter->date_to ) : OsWpDateTime::os_createFromFormat( 'Y-m-d', $filter->date_from );

			# Loop through days to fill in days that might have no bookings
			for ( $day = clone $date_from; $day->format( 'Y-m-d' ) <= $date_to->format( 'Y-m-d' ); $day->modify( '+1 day' ) ) {
				$grouped_booked_periods[ $day->format( 'Y-m-d' ) ] = [];
			}
			foreach ( $booked_periods as $booked_period ) {
				$grouped_booked_periods[ $booked_period->start_date ][] = $booked_period;
				// if event spans multiple days - add to other days as well
				if ( $booked_period->end_date && ( $booked_period->start_date != $booked_period->end_date ) ) {
					$grouped_booked_periods[ $booked_period->end_date ][] = $booked_period;
				}
			}
		}

		return $grouped_booked_periods;
	}

	/**
	 * @param \LatePoint\Misc\Filter $filter
	 *
	 * @return \LatePoint\Misc\BookedPeriod[]
	 */
	public static function get_booked_periods( \LatePoint\Misc\Filter $filter ): array {


		$bookings       = self::get_bookings( $filter, true );
		$booked_periods = [];

		foreach ( $bookings as $booking ) {
			$booked_periods[] = \LatePoint\Misc\BookedPeriod::create_from_booking_model( $booking );
		}


        if($filter->consider_cart_items){
            $cart = OsCartsHelper::get_or_create_cart();
            $bookings_in_cart = $cart->get_bookings_from_cart_items();

            foreach ( $bookings_in_cart as $cart_booking ) {
                $booked_periods[] = \LatePoint\Misc\BookedPeriod::create_from_booking_model( $cart_booking );
            }
        }

		// TODO Update all filters to accept new "filter" variable (In Google Calendar addon)
		$booked_periods = apply_filters( 'latepoint_get_booked_periods', $booked_periods, $filter );

		return $booked_periods;
	}


	/**
	 * @param \LatePoint\Misc\Filter $filter
	 * @param bool $as_models
	 *
	 * @return array
	 */
	public static function get_bookings( \LatePoint\Misc\Filter $filter, bool $as_models = false ): array {
		$bookings = new OsBookingModel();
		if ( $filter->date_from ) {
			if ( $filter->date_from && $filter->date_to ) {
				# both start and end date provided - means it's a range
				$bookings->where( [ 'start_date >=' => $filter->date_from, 'start_date <=' => $filter->date_to ] );
			} else {
				# only start_date provided - means it's a specific date requested
				$bookings->where( [ 'start_date' => $filter->date_from ] );
			}
		}


		if ( $filter->connections ) {
			$connection_conditions = [];
			foreach ( $filter->connections as $connection ) {
				$connection_conditions[] = [
					'AND' =>
						[
							'agent_id'    => $connection->agent_id,
							'service_id'  => $connection->service_id,
							'location_id' => $connection->location_id
						]
				];
			}
			$bookings->where( [ 'OR' => $connection_conditions ] );
		} else {
			if ( $filter->agent_id ) {
				$bookings->where( [ 'agent_id' => $filter->agent_id ] );
			}
			if ( $filter->location_id ) {
				$bookings->where( [ 'location_id' => $filter->location_id ] );
			}
			if ( $filter->service_id ) {
				$bookings->where( [ 'service_id' => $filter->service_id ] );
			}
		}
		if ( $filter->statuses ) {
			$bookings->where( [ 'status' => $filter->statuses ] );
		}
		if ( $filter->exclude_booking_ids ) {
			$bookings->where( [ 'id NOT IN' => $filter->exclude_booking_ids ] );
		}
		$bookings->order_by( 'start_time asc, end_time asc, service_id asc' );
		$bookings = ( $as_models ) ? $bookings->get_results_as_models() : $bookings->get_results();

		// make sure to return empty array if nothing is found
		if ( empty( $bookings ) ) {
			$bookings = [];
		}

		return $bookings;
	}

	public static function generate_ical_event_string( $booking ) {
        // translators: %1$s is agent name, %2$s is service name
		$booking_description = sprintf( __( 'Appointment with %1$s for %2$s', 'latepoint' ), $booking->agent->full_name, $booking->service->name );

		$ics = new ICS( array(
			'location'    => $booking->location->full_address,
			'description' => '',
			'dtstart'     => $booking->format_start_date_and_time_for_google(),
			'dtend'       => $booking->format_end_date_and_time_for_google(),
			'summary'     => $booking_description,
			'url'         => get_site_url()
		) );

		return $ics->to_string();
	}

	/**
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 *
	 * @return bool
	 *
	 * Checks if requested booking slot is available, loads work periods and booked periods from database and checks availability against them
	 */
	public static function is_booking_request_available( \LatePoint\Misc\BookingRequest $booking_request, $settings = [] ): bool {
		$requested_date = new OsWpDateTime( $booking_request->start_date );
		$resources      = OsResourceHelper::get_resources_grouped_by_day( $booking_request, $requested_date, $requested_date, $settings );
		if ( empty( $resources[ $requested_date->format( 'Y-m-d' ) ] ) ) {
			return false;
		}
		$is_available = false;
		foreach ( $resources[ $requested_date->format( 'Y-m-d' ) ] as $resource ) {
			foreach ( $resource->slots as $slot ) {
				if ( $slot->start_time == $booking_request->start_time && $slot->can_accomodate( $booking_request->total_attendees ) ) {
					$is_available = true;
				}
				if ( $is_available ) {
					break;
				}
			}
			if ( $is_available ) {
				break;
			}
		}

		return $is_available;
	}

	/**
	 *
	 * Checks if two bookings are part of the same group appointment
	 *
	 * @param bool|OsBookingModel $booking
	 * @param bool|OsBookingModel $compare_booking
	 *
	 * @return bool
	 */
	public static function check_if_group_bookings( $booking, $compare_booking ): bool {
		if ( $booking && $compare_booking && ( $compare_booking->start_time == $booking->start_time ) && ( $compare_booking->end_time == $booking->end_time ) && ( $compare_booking->service_id == $booking->service_id ) && ( $compare_booking->location_id == $booking->location_id ) ) {
			return true;
		} else {
			return false;
		}
	}


	public static function process_actions_after_save( $booking_id ) {
	}

	/**
	 * @param DateTime $start_date
	 * @param DateTime $end_date
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @param \LatePoint\Misc\BookingResource[] $resources
	 * @param array $settings
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function get_quick_availability_days( DateTime $start_date, DateTime $end_date, \LatePoint\Misc\BookingRequest $booking_request, array $resources = [], array $settings = [] ) {
		$default_settings = [
			'work_boundaries'     => false,
			'exclude_booking_ids' => []
		];
		$settings         = array_merge( $default_settings, $settings );

		$html = '';

		if ( ! $resources ) {
			$resources = OsResourceHelper::get_resources_grouped_by_day( $booking_request, $start_date, $end_date, $settings );
		}
		if ( ! $settings['work_boundaries'] ) {
			$settings['work_boundaries'] = OsResourceHelper::get_work_boundaries_for_groups_of_resources( $resources );
		}

		for ( $day_date = clone $start_date; $day_date <= $end_date; $day_date->modify( '+1 day' ) ) {
			// first day of month, output month name
			if ( $day_date->format( 'j' ) == '1' ) {
				$html .= '<div class="ma-month-label">' . OsUtilHelper::get_month_name_by_number( $day_date->format( 'n' ) ) . '</div>';
			}
			$html .= '<div class="ma-day ma-day-number-' . $day_date->format( 'N' ) . '">';
			$html .= '<div class="ma-day-info">';
			$html .= '<span class="ma-day-number">' . $day_date->format( 'j' ) . '</span>';
			$html .= '<span class="ma-day-weekday">' . OsUtilHelper::get_weekday_name_by_number( $day_date->format( 'N' ), true ) . '</span>';
			$html .= '</div>';
			$html .= OsTimelineHelper::availability_timeline( $booking_request, $settings['work_boundaries'], $resources[ $day_date->format( 'Y-m-d' ) ], [ 'book_on_click' => false ] );
			$html .= '</div>';
		}

		return $html;
	}

	public static function count_pending_bookings() {
		$bookings = new OsBookingModel();

		return $bookings->filter_allowed_records()->where( [ 'status IN' => OsBookingHelper::get_booking_statuses_for_pending_page() ] )->count();
	}


	public static function generate_bundles_folder(): void {
		$bundles_model = new OsBundleModel();
		$bundles       = $bundles_model->should_be_active()->should_not_be_hidden()->get_results_as_models();

		if ( $bundles ) {
			?>
            <div class="os-item-category-w os-items os-as-rows os-animated-child">
                <div class="os-item-category-info-w os-item os-animated-self with-plus">
                    <div class="os-item-category-info os-item-i" tabindex="0">
                        <div class="os-item-img-w"><i class="latepoint-icon latepoint-icon-shopping-bag"></i></div>
                        <div class="os-item-name-w">
                            <div class="os-item-name"><?php echo esc_html__( 'Bundle & Save', 'latepoint' ); ?></div>
                        </div>
						<?php if ( count( $bundles ) ) { ?>
                            <div class="os-item-child-count">
                                <span><?php echo count( $bundles ); ?></span> <?php esc_html_e( 'Bundles', 'latepoint' ); ?>
                            </div>
						<?php } ?>
                    </div>
                </div>
                <div class="os-bundles os-animated-parent os-items os-as-rows os-selectable-items">
					<?php
					foreach ( $bundles as $bundle ) { ?>
                        <div class="os-animated-child os-item os-selectable-item <?php echo ( $bundle->charge_amount ) ? 'os-priced-item' : ''; ?> <?php if ( $bundle->short_description ) { echo 'with-description'; } ?>"
                                tabindex="0"
                                data-item-price="<?php echo esc_attr($bundle->charge_amount); ?>"
                                data-priced-item-type="bundle"
                                data-summary-field-name="bundle"
                                data-summary-value="<?php echo esc_attr( $bundle->name ); ?>"
                                data-item-id="<?php echo esc_attr($bundle->id); ?>"
                                data-cart-item-item-data-key="bundle_id"
                                data-os-call-func="latepoint_bundle_selected">
                            <div class="os-service-selector os-item-i os-animated-self"
                                 data-bundle-id="<?php echo esc_attr($bundle->id); ?>">
                                <span class="os-item-img-w"><i class="latepoint-icon latepoint-icon-shopping-bag"></i></span>
                                <span class="os-item-name-w">
		                <span class="os-item-name"><?php echo esc_html($bundle->name); ?></span>
		                <?php if ( $bundle->short_description ) { ?>
                            <span class="os-item-desc"><?php echo esc_html($bundle->short_description); ?></span>
		                <?php } ?>
		              </span>

							<?php if ( $bundle->charge_amount > 0 ) { ?>
                                <span class="os-item-price-w">
                  <span class="os-item-price">
                    <?php echo esc_html(OsMoneyHelper::format_price($bundle->charge_amount)); ?>
                  </span>
                </span>
							<?php } ?>
                            </div>
                        </div>
						<?php
					}
					?>
                </div>
            </div>
			<?php
		}
	}

	public static function generate_services_list( $services = false, $preselected_service = false ) {
		if ( $services && is_array( $services ) && ! empty( $services ) ) { ?>
            <div class="os-services os-animated-parent os-items os-as-rows os-selectable-items">
				<?php foreach ( $services as $service ) {
					// if service is preselected - only output that service, skip the rest
					if ( $preselected_service && $service->id != $preselected_service->id ) {
						continue;
					}
					$service_durations = $service->get_all_durations_arr();
					$is_priced         = ( ! ( count( $service_durations ) > 1 ) && $service->charge_amount ) ? true : false;
					?>
                    <div class="os-animated-child os-item os-selectable-item <?php echo ( $preselected_service && $service->id == $preselected_service->id ) ? 'selected is-preselected' : ''; ?> <?php echo ( $is_priced ) ? 'os-priced-item' : ''; ?> <?php if ( $service->short_description ) { echo 'with-description'; } ?>"
                            tabindex="0"
                            data-item-price="<?php echo esc_attr($service->charge_amount); ?>"
                            data-priced-item-type="service"
                            data-summary-field-name="service"
                            data-summary-value="<?php echo esc_attr( $service->name ); ?>"
                            data-item-id="<?php echo esc_attr($service->id); ?>"
                            data-cart-item-item-data-key="service_id"
                            data-os-call-func="latepoint_service_selected"
                            data-id-holder=".latepoint_service_id">
                        <div class="os-service-selector os-item-i os-animated-self" data-service-id="<?php echo esc_attr($service->id); ?>">
							<?php if ( $service->selection_image_id ) { ?>
                                <span class="os-item-img-w" style="background-image: url(<?php echo esc_url($service->selection_image_url); ?>);"></span>
							<?php } ?>
                            <span class="os-item-name-w">
                <span class="os-item-name"><?php echo esc_html($service->name); ?></span>
                <?php if ( $service->short_description ) { ?>
                    <span class="os-item-desc"><?php echo esc_html($service->short_description); ?></span>
                <?php } ?>
              </span>
							<?php if ( $service->price_min > 0 ) { ?>
                                <span class="os-item-price-w">
                  <span class="os-item-price">
                    <?php echo esc_html($service->price_min_formatted); ?>
                  </span>
                  <?php if ( $service->price_min != $service->price_max ) { ?>
                      <span class="os-item-price-label"><?php esc_html_e( 'Starts From', 'latepoint' ); ?></span>
                  <?php } ?>
                </span>
							<?php } ?>
                        </div>
                    </div>
				<?php } ?>
            </div>
		<?php }
	}

	public static function generate_services_bundles_and_categories_list( $parent_id = false, array $settings = [] ) {
		$default_settings = [
			'show_service_categories_arr' => false,
			'show_services_arr'           => false,
			'preselected_service'         => false,
			'preselected_category'        => false,
		];
		$settings         = array_merge( $default_settings, $settings );

		if ( $settings['preselected_service'] ) {
			OsBookingHelper::generate_services_list( [ $settings['preselected_service'] ], $settings['preselected_service'] );

			return;
		}

		$service_categories = new OsServiceCategoryModel();
		$args               = array();
		if ( $settings['show_service_categories_arr'] && is_array( $settings['show_service_categories_arr'] ) ) {
			if ( $parent_id ) {
				$service_categories->where( [ 'parent_id' => $parent_id ] );
			} else {
				if ( $settings['preselected_category'] ) {
					$service_categories->where( [ 'id' => $settings['preselected_category'] ] );
				} else {
					$service_categories->where_in( 'id', $settings['show_service_categories_arr'] );
					$service_categories->where( [
						'parent_id' => [
							'OR' => [
								'IS NULL',
								' NOT IN' => $settings['show_service_categories_arr']
							]
						]
					] );
				}
			}
		} else {
			if ( $settings['preselected_category'] ) {
				$service_categories->where( [ 'id' => $settings['preselected_category'] ] );
			} else {
				$args['parent_id'] = $parent_id ? $parent_id : 'IS NULL';
			}
		}
		$service_categories = $service_categories->where( $args )->order_by( 'order_number asc' )->get_results_as_models();

		$main_parent_class = ( $parent_id ) ? 'os-animated-parent' : 'os-item-categories-main-parent os-animated-parent';
		if ( ! $settings['preselected_category'] ) {
			echo '<div class="os-item-categories-holder ' . esc_attr($main_parent_class) . '">';
		}

		// generate services that have no category
		if ( $parent_id == false && $settings['preselected_category'] == false ) { ?>
			<?php
			$services_without_category = new OsServiceModel();
			if ( $settings['show_services_arr'] ) {
				$services_without_category->where_in( 'id', $settings['show_services_arr'] );
			}
			$services_without_category = $services_without_category->where( [ 'category_id' => 0 ] )->should_be_active()->get_results_as_models();
			if ( $services_without_category ) {
				OsBookingHelper::generate_services_list( $services_without_category, false );
			}
		}

		if ( is_array( $service_categories ) ) {
			foreach ( $service_categories as $service_category ) { ?>
				<?php
				$services          = [];
				$category_services = $service_category->get_active_services();
				if ( is_array( $category_services ) ) {
					// if show selected services restriction is set - filter
					if ( $settings['show_services_arr'] ) {
						foreach ( $category_services as $category_service ) {
							if ( in_array( $category_service->id, $settings['show_services_arr'] ) ) {
								$services[] = $category_service;
							}
						}
					} else {
						$services = $category_services;
					}
				}
				$child_categories       = new OsServiceCategoryModel();
				$count_child_categories = $child_categories->where( [ 'parent_id' => $service_category->id ] )->count();
				// show only if it has either at least one child category or service
				if ( $count_child_categories || count( $services ) ) {
					// preselected category, just show contents, not the wrapper
					if ( $service_category->id == $settings['preselected_category'] ) {
						OsBookingHelper::generate_services_list( $services, false );
						OsBookingHelper::generate_services_bundles_and_categories_list( $service_category->id, array_merge( $settings, [ 'preselected_category' => false ] ) );
					} else { ?>
                    <div class="os-item-category-w os-items os-as-rows os-animated-child"
                         data-id="<?php echo esc_attr($service_category->id); ?>">
                        <div class="os-item-category-info-w os-item os-animated-self with-plus">
                            <div class="os-item-category-info os-item-i">
                                <div class="os-item-img-w"
                                     style="background-image: url(<?php echo esc_url($service_category->selection_image_url); ?>);"></div>
                                <div class="os-item-name-w">
                                    <div class="os-item-name"><?php echo esc_html($service_category->name); ?></div>
                                </div>
								<?php if ( count( $services ) ) { ?>
                                    <div class="os-item-child-count">
                                        <span><?php echo count( $services ); ?></span> <?php esc_html_e( 'Services', 'latepoint' ); ?>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
						<?php OsBookingHelper::generate_services_list( $services, false ); ?>
						<?php OsBookingHelper::generate_services_bundles_and_categories_list( $service_category->id, array_merge( $settings, [ 'preselected_category' => false ] ) ); ?>
                        </div><?php
					}
				}
			}
		}
		if ( ! $settings['preselected_category'] && ! $parent_id ) {
			OsBookingHelper::generate_bundles_folder();
		}
		if ( ! $settings['preselected_category'] ) {
			echo '</div>';
		}
	}

	public static function group_booking_btn_html( $booking_id = false ) {
		$html = 'data-os-params="' . esc_attr(http_build_query( [ 'booking_id' => $booking_id ] )) . '" 
                  data-os-action="' . esc_attr(OsRouterHelper::build_route_name( 'bookings', 'grouped_bookings_quick_view' )) . '" 
                  data-os-output-target="lightbox"
                  data-os-lightbox-classes="width-500"
                  data-os-after-call="latepoint_init_grouped_bookings_form"';

		return $html;
	}

	public static function quick_booking_btn_html( $booking_id = false, $params = array() ) {
		$html = '';
		if ( $booking_id ) {
			$params['booking_id'] = $booking_id;
		}
		$route = OsRouterHelper::build_route_name( 'orders', 'quick_edit' );

		$params_str = http_build_query( $params );
		$html       = 'data-os-params="' . esc_attr($params_str) . '" 
    data-os-action="' . esc_attr($route) . '" 
    data-os-output-target="side-panel"
    data-os-after-call="latepoint_init_quick_order_form"';

		return $html;
	}


	/**
	 * @param OsBookingModel $booking
	 *
	 * @return false|mixed
	 *
	 * Search for available location based on booking requirements. Will return false if no available location found.
	 */
	public static function get_any_location_for_booking_by_rule( OsBookingModel $booking ) {
		// ANY LOCATION SELECTED
		// get available locations
		$connected_ids = OsLocationHelper::get_location_ids_for_service_and_agent( $booking->service_id, $booking->agent_id );

		// If date/time is selected - filter locations who are available at that time
		if ( $booking->start_date && $booking->start_time ) {
			$available_location_ids = [];
			$booking_request        = \LatePoint\Misc\BookingRequest::create_from_booking_model( $booking );
			foreach ( $connected_ids as $location_id ) {
				$booking_request->location_id = $location_id;
				if ( OsBookingHelper::is_booking_request_available( $booking_request ) ) {
					$available_location_ids[] = $location_id;
				}
			}
			$connected_ids = array_intersect( $available_location_ids, $connected_ids );
		}


		$locations_model = new OsLocationModel();
		if ( ! empty( $connected_ids ) ) {
			$locations_model->where_in( 'id', $connected_ids );
			$locations = $locations_model->should_be_active()->get_results_as_models();
		} else {
			$locations = [];
		}

		if ( empty( $locations ) ) {
			return false;
		}

		$selected_location_id = $connected_ids[ wp_rand( 0, count( $connected_ids ) - 1 ) ];
		$booking->location_id = $selected_location_id;

		return $selected_location_id;
	}

	/**
	 * @param OsBookingModel $booking
	 *
	 * @return false|mixed
	 *
	 * Search for available agent based on booking requirements and agent picking preferences. Will return false if no available agent found.
	 */
	public static function get_any_agent_for_booking_by_rule( OsBookingModel $booking ) {
		// ANY AGENT SELECTED
		// get available agents
		$connected_ids = OsAgentHelper::get_agent_ids_for_service_and_location( $booking->service_id, $booking->location_id );

		// If date/time is selected - filter agents who are available at that time
		if ( $booking->start_date && $booking->start_time ) {
			$available_agent_ids = [];
			$booking_request     = \LatePoint\Misc\BookingRequest::create_from_booking_model( $booking );
			foreach ( $connected_ids as $agent_id ) {
				$booking_request->agent_id = $agent_id;
				if ( OsBookingHelper::is_booking_request_available( $booking_request ) ) {
					$available_agent_ids[] = $agent_id;
				}
			}
			$connected_ids = array_intersect( $available_agent_ids, $connected_ids );
		}


		/**
		 * Get IDs of agents that are eligible to be assigned a booking that has "ANY" agent pre-selected
		 *
		 * @param {array} $connected_ids Array of eligible Agent IDs
		 * @param {OsBookingModel} $booking Booking that needs agent ID
		 *
		 * @returns {array} Filtered array of IDs of eligible agents
		 * @since 4.7.6
		 * @hook latepoint_agent_ids_assignable_to_any_agent_booking
		 *
		 */
		$connected_ids = apply_filters( 'latepoint_agent_ids_assignable_to_any_agent_booking', $connected_ids, $booking );

		if ( ! empty( $connected_ids ) ) {
			$agents_model = new OsAgentModel();
			$agents_model->where_in( 'id', $connected_ids );
			$agents = $agents_model->should_be_active()->get_results_as_models();
		} else {
			$agents = [];
		}

		if ( empty( $agents ) ) {
			return false;
		}


		$selected_agent_id = false;
		$agent_order_rule  = OsSettingsHelper::get_any_agent_order();
		switch ( $agent_order_rule ) {
			case LATEPOINT_ANY_AGENT_ORDER_RANDOM:
				$selected_agent_id = $connected_ids[ wp_rand( 0, count( $connected_ids ) - 1 ) ];
				break;
			case LATEPOINT_ANY_AGENT_ORDER_PRICE_HIGH:
				$highest_price = false;
				foreach ( $agents as $agent ) {
					$booking->agent_id = $agent->id;
					$price             = OsBookingHelper::calculate_full_amount_for_booking( $booking );
					if ( $highest_price === false && $selected_agent_id === false ) {
						$highest_price     = $price;
						$selected_agent_id = $agent->id;
					} else {
						if ( $highest_price < $price ) {
							$highest_price     = $price;
							$selected_agent_id = $agent->id;
						}
					}
				}
				break;
			case LATEPOINT_ANY_AGENT_ORDER_PRICE_LOW:
				$lowest_price = false;
				foreach ( $agents as $agent ) {
					$booking->agent_id = $agent->id;
					$price             = OsBookingHelper::calculate_full_amount_for_booking( $booking );
					if ( $lowest_price === false && $selected_agent_id === false ) {
						$lowest_price      = $price;
						$selected_agent_id = $agent->id;
					} else {
						if ( $lowest_price > $price ) {
							$lowest_price      = $price;
							$selected_agent_id = $agent->id;
						}
					}
				}
				break;
			case LATEPOINT_ANY_AGENT_ORDER_BUSY_HIGH:
				$max_bookings = false;
				foreach ( $agents as $agent ) {
					$agent_total_bookings = OsBookingHelper::get_total_bookings_for_date( $booking->start_date, [ 'agent_id' => $agent->id ] );
					if ( $max_bookings === false && $selected_agent_id === false ) {
						$max_bookings      = $agent_total_bookings;
						$selected_agent_id = $agent->id;
					} else {
						if ( $max_bookings < $agent_total_bookings ) {
							$max_bookings      = $agent_total_bookings;
							$selected_agent_id = $agent->id;
						}
					}
				}
				break;
			case LATEPOINT_ANY_AGENT_ORDER_BUSY_LOW:
				$min_bookings = false;
				foreach ( $agents as $agent ) {
					$agent_total_bookings = OsBookingHelper::get_total_bookings_for_date( $booking->start_date, [ 'agent_id' => $agent->id ] );
					if ( $min_bookings === false && $selected_agent_id === false ) {
						$min_bookings      = $agent_total_bookings;
						$selected_agent_id = $agent->id;
					} else {
						if ( $min_bookings > $agent_total_bookings ) {
							$min_bookings      = $agent_total_bookings;
							$selected_agent_id = $agent->id;
						}
					}
				}
				break;
		}
		/**
		 * Get ID of agent that will be assigned to a booking, depending on order rules, where agent is set to ANY
		 *
		 * @param {integer} $selected_agent_id Currently selected agent ID
		 * @param {OsAgentModel[]} $agents Array of eligible agent models to pick from
		 * @param {OsBookingModel} $booking Booking that needs agent ID
		 * @param {string} $agent_order_rule Rule of agent ordering
		 *
		 * @returns {integer} ID of the agent that will be assigned to this booking
		 * @since 4.7.6
		 * @hook latepoint_get_any_agent_id_for_booking_by_rule
		 *
		 */
		$selected_agent_id = apply_filters( 'latepoint_get_any_agent_id_for_booking_by_rule', $selected_agent_id, $agents, $booking, $agent_order_rule );
		$booking->agent_id = $selected_agent_id;

		return $selected_agent_id;
	}


	public static function get_total_bookings_for_date( $date, $conditions = [], $grouped = false ) {
		$args = [ 'start_date' => $date ];
		if ( isset( $conditions['agent_id'] ) && $conditions['agent_id'] ) {
			$args['agent_id'] = $conditions['agent_id'];
		}
		if ( isset( $conditions['service_id'] ) && $conditions['service_id'] ) {
			$args['service_id'] = $conditions['service_id'];
		}
		if ( isset( $conditions['location_id'] ) && $conditions['location_id'] ) {
			$args['location_id'] = $conditions['location_id'];
		}


		$bookings = new OsBookingModel();
		if ( $grouped ) {
			$bookings->group_by( 'start_date, start_time, end_time, service_id, location_id' );
		}
		$bookings = $bookings->where( $args );

		return $bookings->count();
	}


	/**
	 *
	 * Get list of statuses that block timeslot availability
	 *
	 * @return array
	 */
	public static function get_timeslot_blocking_statuses(): array {
		$statuses = explode( ',', OsSettingsHelper::get_settings_value( 'timeslot_blocking_statuses', '' ) );

		/**
		 * Get list of statuses that block timeslot availability
		 *
		 * @param {array} $statuses array of status codes that block timeslot availability
		 * @returns {array} The filtered array of status codes
		 *
		 * @since 4.7.0
		 * @hook latepoint_get_timeslot_blocking_statuses
		 *
		 */
		return apply_filters( 'latepoint_get_timeslot_blocking_statuses', $statuses );
	}


	/**
	 *
	 * Get list of statuses that appear on pending page
	 *
	 * @return array
	 */
	public static function get_booking_statuses_for_pending_page(): array {
		$statuses = explode( ',', OsSettingsHelper::get_settings_value( 'need_action_statuses', '' ) );

		/**
		 * Get list of statuses that appear on pending page
		 *
		 * @param {array} $statuses array of status codes that appear on pending page
		 * @returns {array} The filtered array of status codes
		 *
		 * @since 4.7.0
		 * @hook latepoint_get_booking_statuses_for_pending_page
		 *
		 */
		return apply_filters( 'latepoint_get_booking_statuses_for_pending_page', $statuses );
	}

	/**
	 *
	 * Get list of statuses that are not cancelled
	 *
	 * @return array
	 */
	public static function get_non_cancelled_booking_statuses(): array {
		$statuses = self::get_statuses_list();
        if(isset($statuses[LATEPOINT_BOOKING_STATUS_CANCELLED])) unset($statuses[LATEPOINT_BOOKING_STATUS_CANCELLED]);
        $statuses = array_keys($statuses);

		/**
		 * Get list of statuses that are not cancelled
		 *
		 * @param {array} $statuses array of status codes that are not cancelled
		 * @returns {array} The filtered array of status codes
		 *
		 * @since 5.0.5
		 * @hook get_non_cancelled_booking_statuses
		 *
		 */
		return apply_filters( 'get_non_cancelled_booking_statuses', $statuses );
	}


	public static function get_default_booking_status( $service_id = false ) {
		if ( $service_id ) {
			$service = new OsServiceModel( $service_id );
			if ( $service && ! empty( $service->id ) ) {
				return $service->get_default_booking_status();
			}
		}
		$default_status = OsSettingsHelper::get_settings_value( 'default_booking_status' );
		if ( $default_status ) {
			return $default_status;
		} else {
			return LATEPOINT_BOOKING_STATUS_APPROVED;
		}
	}


	public static function change_booking_status( $booking_id, $new_status ) {
		$booking = new OsBookingModel( $booking_id );
		if ( ! $booking_id || ! $booking ) {
			return false;
		}

		if ( $new_status == $booking->status ) {
			return true;
		} else {
			$old_booking = clone $booking;
			if ( $booking->update_status( $new_status ) ) {
				do_action( 'latepoint_booking_updated', $booking, $old_booking );

				return true;
			} else {
				return false;
			}
		}
	}


	/**
	 * @param \LatePoint\Misc\BookingRequest $booking_request
	 * @param \LatePoint\Misc\BookedPeriod[]
	 * @param int $capacity
	 *
	 * @return bool
	 */
	public static function is_timeframe_in_booked_periods( \LatePoint\Misc\BookingRequest $booking_request, array $booked_periods, OsServiceModel $service ): bool {
		if ( empty( $booked_periods ) ) {
			return false;
		}
		$count_existing_attendees = 0;
		foreach ( $booked_periods as $period ) {
			if ( self::is_period_overlapping( $booking_request->get_start_time_with_buffer(), $booking_request->get_end_time_with_buffer(), $period->start_time_with_buffer(), $period->end_time_with_buffer() ) ) {
				// if it's the same service overlapping - count how many times
				// TODO maybe add an option to toggle on/off ability to share a timeslot capacity between two different services
				if ( $booking_request->service_id == $period->service_id ) {
					$count_existing_attendees += $period->total_attendees;
				} else {
					return true;
				}
			}
		}
		if ( $count_existing_attendees > 0 ) {
			// if there are attendees, check if they are below minimum need for timeslot to be blocked, if they are - then the slot is considered booked
			if ( ( $count_existing_attendees + $booking_request->total_attendees ) <= $service->get_capacity_needed_before_slot_is_blocked() ) {
				return false;
			} else {
				return true;
			}
		} else {
			// no attendees in the overlapping booked periods yet, just check if the requested number of attendees is within the service capacity
			if ( $booking_request->total_attendees <= $service->capacity_max ) {
				return false;
			} else {
				return true;
			}
		}
	}


	public static function is_period_overlapping( $period_one_start, $period_one_end, $period_two_start, $period_two_end ) {
		// https://stackoverflow.com/questions/325933/determine-whether-two-date-ranges-overlap/
		return ( ( $period_one_start < $period_two_end ) && ( $period_two_start < $period_one_end ) );
	}

	public static function is_period_inside_another( $period_one_start, $period_one_end, $period_two_start, $period_two_end ) {
		return ( ( $period_one_start >= $period_two_start ) && ( $period_one_end <= $period_two_end ) );
	}

	// args = [agent_id, 'service_id', 'location_id']
	public static function get_bookings_for_date( $date, $args = [] ) {
		$bookings           = new OsBookingModel();
		$args['start_date'] = $date;
		// if any of these are false or 0 - remove it from arguments list
		if ( isset( $args['location_id'] ) && empty( $args['location_id'] ) ) {
			unset( $args['location_id'] );
		}
		if ( isset( $args['agent_id'] ) && empty( $args['agent_id'] ) ) {
			unset( $args['agent_id'] );
		}
		if ( isset( $args['service_id'] ) && empty( $args['service_id'] ) ) {
			unset( $args['service_id'] );
		}

		$bookings->where( $args )->order_by( 'start_time asc, end_time asc, service_id asc' );

		return $bookings->get_results_as_models();
	}

	/**
	 * @param \LatePoint\Misc\Filter $filter
	 *
	 * @return int
	 */
	public static function count_bookings( \LatePoint\Misc\Filter $filter ) {
		$bookings   = new OsBookingModel();
		$query_args = [];
		if ( $filter->date_from ) {
			$query_args['start_date'] = $filter->date_from;
		}
		if ( $filter->location_id ) {
			$query_args['location_id'] = $filter->location_id;
		}
		if ( $filter->agent_id ) {
			$query_args['agent_id'] = $filter->agent_id;
		}
		if ( $filter->service_id ) {
			$query_args['service_id'] = $filter->service_id;
		}

		return $bookings->should_not_be_cancelled()->where( $query_args )->count();
	}


	public static function get_nice_status_name( $status ) {
		$statuses_list = OsBookingHelper::get_statuses_list();
		if ( $status && isset( $statuses_list[ $status ] ) ) {
			return $statuses_list[ $status ];
		} else {
			return __( 'Undefined Status', 'latepoint' );
		}
	}


	public static function get_statuses_list() {
		$statuses            = [
			LATEPOINT_BOOKING_STATUS_APPROVED  => __( 'Approved', 'latepoint' ),
			LATEPOINT_BOOKING_STATUS_PENDING   => __( 'Pending Approval', 'latepoint' ),
			LATEPOINT_BOOKING_STATUS_CANCELLED => __( 'Cancelled', 'latepoint' ),
			LATEPOINT_BOOKING_STATUS_NO_SHOW   => __( 'No Show', 'latepoint' ),
			LATEPOINT_BOOKING_STATUS_COMPLETED => __( 'Completed', 'latepoint' ),
		];
		$additional_statuses = array_map( 'trim', explode( ',', OsSettingsHelper::get_settings_value( 'additional_booking_statuses', '' ) ) );
		if ( ! empty( $additional_statuses ) ) {
			foreach ( $additional_statuses as $status ) {
				if ( ! empty( $status ) ) {
					$statuses[ str_replace( ' ', '_', strtolower( $status ) ) ] = $status;
				}
			}
		}
		$statuses = apply_filters( 'latepoint_booking_statuses', $statuses );

		return $statuses;
	}


	public static function get_weekdays_arr( $full_name = false ) {
		if ( $full_name ) {
			$weekdays = array(
				__( 'Monday', 'latepoint' ),
				__( 'Tuesday', 'latepoint' ),
				__( 'Wednesday', 'latepoint' ),
				__( 'Thursday', 'latepoint' ),
				__( 'Friday', 'latepoint' ),
				__( 'Saturday', 'latepoint' ),
				__( 'Sunday', 'latepoint' )
			);
		} else {
			$weekdays = array(
				__( 'Mon', 'latepoint' ),
				__( 'Tue', 'latepoint' ),
				__( 'Wed', 'latepoint' ),
				__( 'Thu', 'latepoint' ),
				__( 'Fri', 'latepoint' ),
				__( 'Sat', 'latepoint' ),
				__( 'Sun', 'latepoint' )
			);
		}

		return $weekdays;
	}

	public static function get_weekday_name_by_number( $weekday_number, $full_name = false ) {
		$weekdays = OsBookingHelper::get_weekdays_arr( $full_name );
		if ( ! isset( $weekday_number ) || $weekday_number < 1 || $weekday_number > 7 ) {
			return '';
		} else {
			return $weekdays[ $weekday_number - 1 ];
		}
	}

	public static function get_stat( $stat, $args = [] ) {
		if ( ! in_array( $stat, [ 'duration', 'price', 'bookings' ] ) ) {
			return false;
		}
		$defaults   = [
			'customer_id'    => false,
			'agent_id'       => false,
			'service_id'     => false,
			'location_id'    => false,
			'date_from'      => false,
			'date_to'        => false,
			'group_by'       => false,
			'exclude_status' => false
		];
		$args       = array_merge( $defaults, $args );
		$bookings   = new OsBookingModel();
		$query_args = array( $args['date_from'], $args['date_to'] );
		switch ( $stat ) {
			case 'duration':
				$stat_query = 'SUM(end_time - start_time)';
				break;
			case 'price':
				$stat_query = 'sum(total)';
				break;
			case 'bookings':
				$stat_query = 'count(id)';
				break;
		}
		$select_query = $stat_query . ' as stat';
		if ( $args['group_by'] ) {
			$select_query .= ',' . $args['group_by'];
		}
		$bookings->select( $select_query );


		if ( $args['date_from'] ) {
			$bookings->where( [ 'start_date >=' => $args['date_from'] ] );
		}
		if ( $args['date_to'] ) {
			$bookings->where( [ 'start_date <=' => $args['date_to'] ] );
		}
		if ( $args['service_id'] ) {
			$bookings->where( [ 'service_id' => $args['service_id'] ] );
		}
		if ( $args['agent_id'] ) {
			$bookings->where( [ 'agent_id' => $args['agent_id'] ] );
		}
		if ( $args['location_id'] ) {
			$bookings->where( [ 'location_id' => $args['location_id'] ] );
		}
		if ( $args['customer_id'] ) {
			$bookings->where( [ 'customer_id' => $args['customer_id'] ] );
		}
		if ( $args['group_by'] ) {
			$bookings->group_by( $args['group_by'] );
		}
		// TODO, need to support custom status exclusions
		if ( $args['exclude_status'] == LATEPOINT_BOOKING_STATUS_CANCELLED ) {
			$bookings->should_not_be_cancelled();
		}

		$stat_total = $bookings->get_results( ARRAY_A );
		if ( $args['group_by'] ) {
			return $stat_total;
		} else {
			return isset( $stat_total[0]['stat'] ) ? $stat_total[0]['stat'] : 0;
		}
	}

	public static function get_new_customer_stat_for_period( DateTime $date_from, DateTime $date_to, \LatePoint\Misc\Filter $filter ) {
		// TODO make sure filter is respected
		$customers = new OsCustomerModel();

		return $customers->filter_allowed_records()->where( [
			'created_at >=' => $date_from->format( 'Y-m-d' ),
			'created_at <=' => $date_to->format( 'Y-m-d' )
		] )->count();
	}

	public static function get_stat_for_period( $stat, $date_from, $date_to, \LatePoint\Misc\Filter $filter, $group_by = false ) {
		if ( ! in_array( $stat, [ 'duration', 'price', 'bookings' ] ) ) {
			return false;
		}
		if ( ! in_array( $group_by, [ false, 'agent_id', 'service_id', 'location_id' ] ) ) {
			return false;
		}
		$bookings = new OsBookingModel();
		switch ( $stat ) {
			case 'duration':
				$stat_query = 'SUM(end_time - start_time)';
				break;
			case 'price':
				$stat_query = 'sum(' . LATEPOINT_TABLE_ORDER_ITEMS . '.subtotal)';
				$bookings->join( LATEPOINT_TABLE_ORDER_ITEMS, [ 'id' => $bookings->table_name . '.order_item_id' ] );
				$bookings->join( LATEPOINT_TABLE_ORDERS, [ 'id' => LATEPOINT_TABLE_ORDER_ITEMS . '.order_id' ] );
				break;
			case 'bookings':
				$stat_query = 'count(id)';
				break;
		}
		$select_query = $stat_query . ' as stat';
		if ( $group_by ) {
			$select_query .= ',' . $group_by;
		}
		$bookings->select( $select_query )->where( [ 'start_date >=' => $date_from, 'start_date <= ' => $date_to ] );

		if ( $filter->service_id ) {
			$bookings->where( [ 'service_id' => $filter->service_id ] );
		}
		if ( $filter->agent_id ) {
			$bookings->where( [ 'agent_id' => $filter->agent_id ] );
		}
		if ( $filter->location_id ) {
			$bookings->where( [ 'location_id' => $filter->location_id ] );
		}

		$bookings->should_not_be_cancelled();

		if ( $group_by ) {
			$bookings->group_by( $group_by );
		}

		$stat_total = $bookings->get_results( ARRAY_A );
		if ( $group_by ) {
			return $stat_total;
		} else {
			return isset( $stat_total[0]['stat'] ) ? $stat_total[0]['stat'] : 0;
		}
	}

	public static function get_total_bookings_per_day_for_period( $date_from, $date_to, \LatePoint\Misc\Filter $filter ) {
		$bookings = new OsBookingModel();
		$bookings->select( 'count(id) as bookings_per_day, start_date' )
		         ->where( [ 'start_date >=' => $date_from, 'start_date <=' => $date_to ] )
		         ->where( [ 'status NOT IN' => OsCalendarHelper::get_booking_statuses_hidden_from_calendar() ] );
		if ( $filter->service_id ) {
			$bookings->where( [ 'service_id' => $filter->service_id ] );
		}
		if ( $filter->agent_id ) {
			$bookings->where( [ 'agent_id' => $filter->agent_id ] );
		}
		if ( $filter->location_id ) {
			$bookings->where( [ 'location_id' => $filter->location_id ] );
		}
		$bookings->group_by( 'start_date' );

		return $bookings->get_results();
	}


	/**
	 * @param \LatePoint\Misc\BookedPeriod[] $daily_periods
	 * @param int $timeshift_minutes
	 *
	 * @return \LatePoint\Misc\BookedPeriod[]
	 */
	public static function apply_timeshift( array $daily_periods, int $timeshift_minutes ): array {
		if ( empty( $timeshift_minutes ) ) {
			return $daily_periods;
		}
		$shifted_periods = [];
		// apply timeshift
		foreach ( $daily_periods as $day => $periods ) {
			$shifted_periods[ $day ] = [];
			// search for the periods that has to be moved to the next day after a timeshift
			if ( $timeshift_minutes > 0 ) {
				$day_obj = OsWpDateTime::os_createFromFormat( 'Y-m-d', $day );
				$day_obj->modify( '-1 day' );
				if ( isset( $daily_periods[ $day_obj->format( 'Y-m-d' ) ] ) ) {
					foreach ( $daily_periods[ $day_obj->format( 'Y-m-d' ) ] as $prev_day_period ) {
						list( $period_start, $period_end ) = explode( ':', $prev_day_period );
						// if period is a day off - don't shift it
						if ( $period_start == 0 && $period_end == 0 ) {
							continue;
						}
						$period_start = $period_start + $timeshift_minutes;
						$period_end   = $period_end + $timeshift_minutes;
						// if shifted period still ends on the same day - skip to next
						if ( $period_end <= ( 24 * 60 ) ) {
							continue;
						}
						// we need to capture the remaining minutes of this period in the day after shift
						$period_start = max( 24 * 60, $period_start );
						$period_start = $period_start - 24 * 60;
						$period_end   = $period_end - 24 * 60;

						$period_info_arr           = explode( ':', $prev_day_period );
						$period_info_arr[0]        = $period_start;
						$period_info_arr[1]        = $period_end;
						$shifted_periods[ $day ][] = implode( ':', $period_info_arr );
					}
				}
			}
			// work on the periods that stay in the same day
			if ( ! empty( $periods ) ) {
				foreach ( $periods as $period ) {
					list( $period_start, $period_end ) = explode( ':', $period );
					// if period is a day off - don't shift it
					if ( $period_start == 0 && $period_end == 0 ) {
						$shifted_periods[ $day ][] = $period;
					} else {
						$period_start = $period_start + $timeshift_minutes;
						$period_end   = $period_end + $timeshift_minutes;
						// if starts next day or ended previous day - skip
						if ( $period_start >= ( 24 * 60 ) || $period_end <= 0 ) {
							continue;
						}
						if ( $period_end > ( 24 * 60 ) ) {
							$period_end = 24 * 60;
						}
						if ( $period_start < 0 ) {
							$period_start = 0;
						}

						$period_info_arr           = explode( ':', $period );
						$period_info_arr[0]        = $period_start;
						$period_info_arr[1]        = $period_end;
						$shifted_periods[ $day ][] = implode( ':', $period_info_arr );
					}
				}
			}
			// search for the periods that has to be moved to the previous day after a timeshift
			if ( $timeshift_minutes < 0 ) {
				$day_obj = OsWpDateTime::os_createFromFormat( 'Y-m-d', $day );
				$day_obj->modify( '+1 day' );
				if ( isset( $daily_periods[ $day_obj->format( 'Y-m-d' ) ] ) ) {
					foreach ( $daily_periods[ $day_obj->format( 'Y-m-d' ) ] as $next_day_period ) {
						list( $period_start, $period_end ) = explode( ':', $next_day_period );
						// if period is a day off - don't shift it
						if ( $period_start == 0 && $period_end == 0 ) {
							continue;
						}
						$period_start = $period_start + $timeshift_minutes;
						$period_end   = $period_end + $timeshift_minutes;
						// if shifted period still starts on the same day - skip to next
						if ( $period_start >= 0 ) {
							continue;
						}
						$period_end   = min( 0, $period_end );
						$period_start = $period_start + 24 * 60;
						$period_end   = $period_end + 24 * 60;

						$period_info_arr           = explode( ':', $next_day_period );
						$period_info_arr[0]        = $period_start;
						$period_info_arr[1]        = $period_end;
						$shifted_periods[ $day ][] = implode( ':', $period_info_arr );
					}
				}
			}
		}

		return $shifted_periods;
	}


	public static function get_min_max_work_periods( $specific_weekdays = false, $service_id = false, $agent_id = false ) {
		$select_string = 'MIN(start_time) as start_time, MAX(end_time) as end_time';
		$work_periods  = new OsWorkPeriodModel();
		$work_periods  = $work_periods->select( $select_string );
		$query_args    = array( 'service_id' => 0, 'agent_id' => 0 );
		if ( $service_id ) {
			$query_args['service_id'] = $service_id;
		}
		if ( $agent_id ) {
			$query_args['agent_id'] = $agent_id;
		}
		if ( $specific_weekdays && ! empty( $specific_weekdays ) ) {
			$query_args['week_day'] = $specific_weekdays;
		}
		$results = $work_periods->set_limit( 1 )->where( $query_args )->get_results( ARRAY_A );
		if ( ( $service_id || $agent_id ) && empty( $results['min_start_time'] ) ) {
			if ( $service_id && empty( $results['min_start_time'] ) ) {
				$query_args['service_id'] = 0;
				$work_periods             = new OsWorkPeriodModel();
				$work_periods             = $work_periods->select( $select_string );
				$results                  = $work_periods->set_limit( 1 )->where( $query_args )->get_results( ARRAY_A );
			}
			if ( $agent_id && empty( $results['min_start_time'] ) ) {
				$query_args['agent_id'] = 0;
				$work_periods           = new OsWorkPeriodModel();
				$work_periods           = $work_periods->select( $select_string );
				$results                = $work_periods->set_limit( 1 )->where( $query_args )->get_results( ARRAY_A );
			}
		}
		if ( $results ) {
			return array( $results['start_time'], $results['end_time'] );
		} else {
			return false;
		}
	}


	public static function get_work_start_end_time_for_multiple_dates( $dates = false, $service_id = false, $agent_id = false ) {
		$specific_weekdays = array();
		if ( $dates ) {
			foreach ( $dates as $date ) {
				$target_date = new OsWpDateTime( $date );
				$weekday     = $target_date->format( 'N' );
				if ( ! in_array( $weekday, $specific_weekdays ) ) {
					$specific_weekdays[] = $weekday;
				}
			}
		}
		$work_minmax_start_end = self::get_min_max_work_periods( $specific_weekdays, $service_id, $agent_id );

		return $work_minmax_start_end;
	}

	/**
	 * @param int $minute
	 * @param \LatePoint\Misc\WorkPeriod[] $work_periods_arr
	 *
	 * @return bool
	 */
	public static function is_minute_in_work_periods( int $minute, array $work_periods_arr ): bool {
		// print_r($work_periods_arr);
		if ( empty( $work_periods_arr ) ) {
			return false;
		}
		foreach ( $work_periods_arr as $work_period ) {
			// end of period does not count because we cant make appointment with 0 duration
			if ( $work_period->start_time <= $minute && $work_period->end_time > $minute ) {
				return true;
			}
		}

		return false;
	}

	public static function get_calendar_start_end_time( $bookings, $work_start_minutes, $work_end_minutes ) {
		$calendar_start_minutes = $work_start_minutes;
		$calendar_end_minutes   = $work_end_minutes;
		if ( $bookings ) {
			foreach ( $bookings as $bookings_for_agent ) {
				if ( $bookings_for_agent ) {
					foreach ( $bookings_for_agent as $booking ) {
						if ( $booking->start_time < $calendar_start_minutes ) {
							$calendar_start_minutes = $booking->start_time;
						}
						if ( $booking->end_time > $calendar_end_minutes ) {
							$calendar_end_minutes = $booking->end_time;
						}
					}
				}
			}
		}

		return [ $calendar_start_minutes, $calendar_end_minutes ];
	}

	public static function generate_direct_manage_booking_url( OsBookingModel $booking, string $for ): string {
		if ( ! in_array( $for, [ 'agent', 'customer' ] ) ) {
			return '';
		}
		$key = $booking->get_key_to_manage_for($for);
		$url = OsRouterHelper::build_admin_post_link( [ 'manage_booking_by_key', 'show' ], [ 'key' => $key ] );

		return $url;
	}


	public static function generate_summary_for_booking( OsBookingModel $booking, $cart_item_id = false ): string {
		$summary_html     = '<div class="summary-box main-box" ' . ( ( $cart_item_id ) ? 'data-cart-item-id="' . $cart_item_id . '"' : '' ) . '>';
        $summary_html.= '<div class="summary-box-booking-date-box">';
        $summary_html.= '<div class="summary-box-booking-date-day">'.$booking->start_datetime_in_format('j', OsTimeHelper::get_timezone_name_from_session()).'</div>';
        $summary_html.= '<div class="summary-box-booking-date-month">'.OsUtilHelper::get_month_name_by_number($booking->start_datetime_in_format('n', OsTimeHelper::get_timezone_name_from_session()), true).'</div>';
        $summary_html .= '</div>';
        $summary_html.= '<div class="summary-box-inner">';
		$service_headings = [];
		$service_headings = apply_filters( 'latepoint_booking_summary_service_headings', $service_headings, $booking );
		if ( $service_headings ) {
			$summary_html .= '<div class="summary-box-heading">';
			foreach ( $service_headings as $heading ) {
				$summary_html .= '<div class="sbh-item">' . $heading . '</div>';
			}
			$summary_html .= '<div class="sbh-line"></div>';
			$summary_html .= '</div>';
		}
		$summary_html .= '<div class="summary-box-content os-cart-item">';
		if ( $cart_item_id && OsCartsHelper::can_checkout_multiple_items() ) {
			$summary_html .= '<div class="os-remove-item-from-cart" role="button" tabindex="0" data-confirm-text="' . __( 'Are you sure you want to remove this item from your cart?', 'latepoint' ) . '" data-cart-item-id="' . $cart_item_id . '" data-route="' . OsRouterHelper::build_route_name( 'carts', 'remove_item_from_cart' ) . '">
															<i class="latepoint-icon latepoint-icon-minus"></i>
														</div>';
		}
		$summary_html .= '<div class="sbc-big-item">' . $booking->get_service_name_for_summary() . '</div>';
		if ( $booking->start_date ) {
			$booking_start_datetime = $booking->get_nice_start_datetime();
			$booking_start_datetime = apply_filters( 'latepoint_booking_summary_formatted_booking_start_datetime', $booking_start_datetime, $booking, $timezone_name ?? '' );
			if ( $booking_start_datetime ) {
				$summary_html .= '<div class="sbc-highlighted-item">' . $booking_start_datetime . '</div>';
			}
		}
		$summary_html .= '</div>';

		$service_attributes = [];
		$service_attributes = apply_filters( 'latepoint_booking_summary_service_attributes', $service_attributes, $booking );
		if ( $service_attributes ) {
			$summary_html .= '<div class="summary-attributes sa-clean">';
			foreach ( $service_attributes as $attribute ) {
				$summary_html .= '<span>' . $attribute['label'] . ': <strong>' . $attribute['value'] . '</strong></span>';
			}
			$summary_html .= '</div>';
		}
		$summary_html .= '</div>';
		$summary_html .= apply_filters( 'latepoint_booking_summary_after_summary_box_inner', '', $booking );
		$summary_html .= '</div>';

		return $summary_html;
	}


	/**
	 * @param OsBookingModel[] $bookings
	 *
	 * @return bool
	 */
	public static function bookings_have_same_agent( array $bookings ): bool {
		return ( count( array_unique( array_column( $bookings, 'agent_id' ) ) ) == 1 );
	}

	/**
	 * @param OsBookingModel[] $bookings
	 *
	 * @return bool
	 */
	public static function bookings_have_same_location( array $bookings ): bool {
		return ( count( array_unique( array_column( $bookings, 'location_id' ) ) ) == 1 );
	}

	/**
	 * @param OsBookingModel[] $bookings
	 *
	 * @return bool
	 */
	public static function bookings_have_same_service( array $bookings ): bool {
		return ( count( array_unique( array_column( $bookings, 'service_id' ) ) ) == 1 );
	}

	public static function prepare_new_from_params( array $params ): OsBookingModel {
		$booking = new OsBookingModel();

		$services = OsServiceHelper::get_allowed_active_services();
		$agents   = OsAgentHelper::get_allowed_active_agents();

		// LOAD FROM PASSED PARAMS
		$booking->order_item_id = $params['order_item_id'] ?? '';
		$booking->service_id    = ! empty( $params['service_id'] ) ? OsUtilHelper::first_value_if_array( $params['service_id'] ) : '';
		if ( empty( $booking->service_id ) && ! empty( $services ) ) {
			$booking->service_id = $services[0]->id;
		}

		$booking->agent_id = ! empty( $params['agent_id'] ) ? OsUtilHelper::first_value_if_array( $params['agent_id'] ) : '';
		if ( empty( $booking->agent_id ) && ! empty( $agents ) ) {
			$booking->agent_id = $agents[0]->id;
		}

		if ( ! empty( $params['order_id'] ) ) {
			$order                = new OsOrderModel( $params['order_id'] );
			$booking->customer_id = $order->customer_id;
		} else {
			$booking->customer_id = ! empty( $params['customer_id'] ) ? OsUtilHelper::first_value_if_array( $params['customer_id'] ) : '';
		}

		$booking->location_id = ! empty( $params['location_id'] ) ? OsUtilHelper::first_value_if_array( $params['location_id'] ) : OsLocationHelper::get_default_location_id( true );
		$booking->start_date  = $params['start_date'] ?? OsTimeHelper::today_date( 'Y-m-d' );
		$booking->start_time  = $params['start_time'] ?? 600;

		$booking->end_time      = ( $booking->service_id ) ? $booking->calculate_end_time() : $booking->start_time + 60;
		$booking->end_date      = $booking->calculate_end_date();
		$booking->buffer_before = ( $booking->service_id ) ? $booking->service->buffer_before : 0;
		$booking->buffer_after  = ( $booking->service_id ) ? $booking->service->buffer_after : 0;
		$booking->status        = LATEPOINT_BOOKING_STATUS_APPROVED;

		return $booking;
	}


}
