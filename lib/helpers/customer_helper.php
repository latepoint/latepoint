<?php

class OsCustomerHelper {


	public static function quick_customer_btn_html( $customer_id = false, $params = array() ) {
		$html = '';
		if ( $customer_id ) {
			$params['customer_id'] = $customer_id;
		}
		$route = OsRouterHelper::build_route_name( 'customers', 'quick_edit' );

		$params_str = http_build_query( $params );
		$html       = 'data-os-params="' . esc_attr($params_str) . '" 
    data-os-action="' . esc_attr($route) . '" 
    data-os-output-target="side-panel"
    data-os-after-call="latepoint_init_quick_customer_form"';

		return $html;
	}

	public static function generate_summary_for_customer( OsCustomerModel $customer ): void {
		?>
        <div class="summary-box summary-box-customer-info">
            <div class="summary-box-heading">
                <div class="sbh-item"><?php esc_html_e( 'Customer', 'latepoint' ) ?></div>
                <div class="sbh-line"></div>
            </div>
            <div class="summary-box-content with-media">
                <div class="os-avatar-w">
                    <div class="os-avatar"><span><?php echo esc_html( $customer->get_initials() ); ?></span></div>
                </div>
                <div class="sbc-content-i">
                    <div class="sbc-main-item"><?php echo esc_html( $customer->full_name ); ?></div>
                    <div class="sbc-sub-item"><?php echo esc_html( $customer->email ); ?></div>
                </div>
            </div>
			<?php
			$customer_attributes = [];
			$customer_attributes = apply_filters( 'latepoint_booking_summary_customer_attributes', $customer_attributes, $customer );
			if ( $customer_attributes ) {
				echo '<div class="summary-attributes sa-clean sa-hidden">';
				foreach ( $customer_attributes as $attribute ) {
					echo '<span>' . esc_html( $attribute['label'] ) . ': <strong>' . esc_html( $attribute['value'] ) . '</strong></span>';
				}
				echo '</div>';
			}
			?>
        </div>
		<?php
	}

	public static function get_customers_for_select() {
		$customers         = new OsCustomerModel();
		$customers         = $customers->set_limit( 100 )->get_results_as_models();
		$customers_options = [];
		foreach ( $customers as $customer ) {
			$customers_options[] = [ 'value' => $customer->id, 'label' => esc_html( $customer->full_name ) ];
		}

		return $customers_options;
	}

	public static function get_full_name( $customer ) {
		return join( ' ', array( $customer->first_name, $customer->last_name ) );
	}

	public static function get_avatar_url( $customer ) {
		$default_avatar = LATEPOINT_IMAGES_URL . 'default-avatar.jpg';
		if ( OsAuthHelper::wp_users_as_customers() && $customer->wordpress_user_id && empty( $customer->avatar_image_id ) ) {
			// try to get gravatar with WP function
			$avatar_url = get_avatar_url( $customer->wordpress_user_id );
		} else {
			$avatar_url = false;
		}
		if ( ! $avatar_url ) {
			$avatar_url = OsImageHelper::get_image_url_by_id( $customer->avatar_image_id, 'thumbnail', $default_avatar );
		}

		return $avatar_url;
	}


	public static function get_avatar_image( $customer ) {
		return '<img src="' . self::get_avatar_url( $customer ) . '"/>';
	}


	public static function total_new_customers_for_date( $date ) {
		$customers = new OsCustomerModel();
		$customers = $customers->where( array( 'DATE(created_at)' => $date ) );

		return $customers->count();
	}

	public static function can_cancel_booking( OsBookingModel $booking ): bool {
		if ( OsSettingsHelper::is_on( 'allow_customer_booking_cancellation' ) && ( $booking->status != LATEPOINT_BOOKING_STATUS_CANCELLED ) ) {
			if ( OsSettingsHelper::is_on( 'limit_when_customer_can_cancel' ) ) {
				// check if there is a limit on when they can cancel
				$limit_value = OsSettingsHelper::get_settings_value( 'cancellation_limit_value' );
				$limit_unit  = OsSettingsHelper::get_settings_value( 'cancellation_limit_unit' );
				if ( $limit_value && $limit_unit ) {
					$now = new OsWpDateTime( 'now' );
					if ( $now <= $booking->get_start_datetime_object()->modify( '-' . $limit_value . ' ' . $limit_unit ) ) {
						return true;
					}
				}
			} else {
				return true;
			}
		}

		return false;
	}

	public static function can_reschedule_booking( OsBookingModel $booking ): bool {
		if ( OsSettingsHelper::is_on( 'allow_customer_booking_reschedule' ) && ( $booking->status != LATEPOINT_BOOKING_STATUS_CANCELLED ) ) {
			if ( OsSettingsHelper::is_on( 'limit_when_customer_can_reschedule' ) ) {
				// check if there is a limit on when they can reschedule
				$limit_value = OsSettingsHelper::get_settings_value( 'reschedule_limit_value' );
				$limit_unit  = OsSettingsHelper::get_settings_value( 'reschedule_limit_unit' );
				if ( $limit_value && $limit_unit ) {
					$now = new OsWpDateTime( 'now' );
					if ( $now <= $booking->get_start_datetime_object()->modify( '-' . $limit_value . ' ' . $limit_unit ) ) {
						return true;
					}
				}
			} else {
				return true;
			}
		}

		return false;
	}


	public static function get_customer_for_wp_user( $wp_user ) {
		$customer = new OsCustomerModel();
		$customer = $customer->where( [ 'wordpress_user_id' => $wp_user->ID ] )->set_limit( 1 )->get_results_as_models();
		if ( $customer ) {
			if ( $customer->email != $wp_user->user_email ) {
				// TODO: check if other customer already has this email
				$customer->update_attributes( [ 'email' => $wp_user->user_email ] );
			}

			return $customer;
		} else {
			// check if customer with this email exists
			$customer = new OsCustomerModel();
			$customer = $customer->where( [ 'email' => $wp_user->user_email ] )->set_limit( 1 )->get_results_as_models();
			if ( $customer ) {
				$old_customer_data = $customer->get_data_vars();
				$customer->update_attributes( [ 'wordpress_user_id' => $wp_user->ID ] );
				do_action( 'latepoint_customer_updated', $customer, $old_customer_data );
			} else {
				// create new customer
				$customer             = new OsCustomerModel();
				$customer->first_name = $wp_user->first_name;
				$customer->last_name  = $wp_user->last_name;
				$customer->email      = $wp_user->user_email;
				$customer->password   = $wp_user->user_pass;
				$customer->is_guest   = false;
				$customer->save( true );
				do_action( 'latepoint_customer_created', $customer );
			}
		}

		return $customer;
	}

	public static function count_customers_not_connected_to_wp_users() {
		$customers = new OsCustomerModel();

		return $customers->where( [ 'wordpress_user_id' => [ 'OR' => [ 0, 'IS NULL' ] ] ] )->count();
	}

	public static function get_by_account_nonse( $account_nonse ) {
		if ( empty( $account_nonse ) ) {
			return false;
		}
        $account_nonse = sanitize_text_field( $account_nonse );
		$customer = new OsCustomerModel();

		return $customer->where( [ 'account_nonse' => $account_nonse ] )->set_limit( 1 )->get_results_as_models();
	}

	public static function create_wp_user_for_customer( $customer ) {
		// NO connected wp user, create one
		// check if wp user with this customer email already exists
		$wp_user_id = email_exists( $customer->email );
		if ( ! $wp_user_id ) {
			$wp_user_id = username_exists( $customer->email );
		}
		if ( $wp_user_id ) {
			// wp user with this email or username exists - check if its linked to another customer already - if not link it to current customer
			$linked_customer = new OsCustomerModel();
			$linked_customer = $linked_customer->where( [ 'wordpress_user_id' => $wp_user_id ] )->set_limit( 1 )->get_results_as_models();
			if ( $linked_customer ) {
				// wp user with this email exists and is linked already to a different latepoint customer
				$customer->add_error( 'customer_exists', __( 'Customer with this email already exists', 'latepoint' ) );
			} else {
				$customer->update_attributes( [ 'wordpress_user_id' => $wp_user_id, 'is_guest' => false ] );
			}
		} else {
			$userdata   = [
				'user_email' => $customer->email,
				'first_name' => $customer->first_name,
				'last_name'  => $customer->last_name,
				'user_login' => $customer->email,
				'user_pass'  => $customer->password
			];
			$wp_user_id = wp_insert_user( $userdata );
			if ( ! is_wp_error( $wp_user_id ) ) {
				$customer->update_attributes( [ 'wordpress_user_id' => $wp_user_id, 'is_guest' => false ] );
				// update password directly in database because we already hashed it in latepoint customer
				global $wpdb;
				$wpdb->update(
					$wpdb->users,
					array(
						'user_pass'           => $customer->password,
						'user_activation_key' => '',
					),
					array( 'ID' => $wp_user_id )
				);
			} else {
				OsDebugHelper::log( 'Error creating WP User for customer', 'registration_error', [ 'errors' => $wp_user_id->get_error_messages() ] );
			}
		}

		return ( ! is_wp_error( $wp_user_id ) ) ? $wp_user_id : false;
	}

	public static function generate_booking_summary_preview_btn( int $booking_id ): string {
		$html = 'data-os-after-call="latepoint_init_booking_summary_lightbox"
			   data-os-params="' . esc_attr(OsUtilHelper::build_os_params( [ 'booking_id' => $booking_id ] )) . '"
			   data-os-action="' . esc_attr(OsRouterHelper::build_route_name( 'customer_cabinet', 'view_booking_summary_in_lightbox' )) . '"
			   data-os-output-target="lightbox"
				data-os-lightbox-classes="width-500 customer-dashboard-booking-summary-lightbox"';

		return $html;
	}


	public static function generate_bundle_scheduling_btn( int $order_item_id ): string {
		$html = 'data-os-after-call="latepoint_init_bundle_scheduling_summary"
			   data-os-params="' . esc_attr(OsUtilHelper::build_os_params( [ 'order_item_id' => $order_item_id ] )) . '"
			   data-os-action="' . esc_attr(OsRouterHelper::build_route_name( 'customer_cabinet', 'scheduling_summary_for_bundle' )) . '"
			   data-os-output-target="lightbox"
				data-os-lightbox-classes="width-500 customer-dashboard-bundle-scheduling-summary"';

		return $html;
	}

	public static function generate_order_summary_btn( int $order_id ): string {
		$html = 'data-os-after-call="latepoint_init_order_summary_lightbox"
			   data-os-params="' . esc_attr(OsUtilHelper::build_os_params( [ 'order_id' => $order_id ] )) . '"
			   data-os-action="' . esc_attr(OsRouterHelper::build_route_name( 'customer_cabinet', 'view_order_summary_in_lightbox' )) . '"
			   data-os-output-target="lightbox"
				data-os-lightbox-classes="width-500 customer-dashboard-booking-summary-lightbox"';

		return $html;
	}

}