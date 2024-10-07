<?php

/**
 * @property string $full_name
 */
class OsCustomerModel extends OsModel {
	var $id,
		$first_name,
		$last_name,
		$password,
		$email,
		$phone,
		$account_nonse,
		$status,
		$activation_key,
		$google_user_id,
		$facebook_user_id,
		$avatar_image_id,
		$is_guest,
		$notes,
		$admin_notes,
		$wordpress_user_id,
		$meta_class = 'OsCustomerMetaModel',
		$updated_at,
		$created_at;

	function __construct( $id = false ) {
		$this->table_name = LATEPOINT_TABLE_CUSTOMERS;
		$this->nice_names = array(
			'first_name' => __( 'Customer First Name', 'latepoint' ),
			'email'      => __( 'Email Address', 'latepoint' ),
			'phone'      => __( 'Phone Number', 'latepoint' ),
			'last_name'  => __( 'Customer Last Name', 'latepoint' )
		);

		parent::__construct( $id );
	}

	public function generate_data_vars(): array {
		return [
			'id'         => $this->id,
			'first_name' => $this->first_name,
			'last_name'  => $this->last_name,
			'full_name'  => $this->full_name,
			'email'      => $this->email,
			'phone'      => $this->phone
		];
	}

	public function get_meta_by_key( $meta_key, $default = false ) {
		if ( $this->is_new_record() ) {
			return $default;
		}

		$meta = new OsCustomerMetaModel();

		return $meta->get_by_key( $meta_key, $this->id, $default );
	}

	/**
	 * @param int $limit
	 * @param bool $filter_allowed_records
	 *
	 * @return OsOrderModel[]
	 */
	public function get_orders( int $limit = 0, bool $filter_allowed_records = false ): array {
		$orders = new OsOrderModel();
		if ( $limit ) {
			$orders = $orders->set_limit( $limit );
		}
		if ( $filter_allowed_records ) {
			$orders->filter_allowed_records();
		}

		return $orders->where( [ 'customer_id' => $this->id ] )->order_by( 'created_at desc' )->get_results_as_models();
	}

	public function get_initials() {
		return substr( $this->first_name, 0, 1 ) . substr( $this->last_name, 0, 1 );
	}

	public function delete_meta_by_key( $meta_key ) {
		if ( $this->is_new_record() ) {
			return true;
		}

		$meta = new OsCustomerMetaModel();

		return $meta->delete_by_key( $meta_key, $this->id );
	}

	public function save_meta_by_key( $meta_key, $meta_value ) {
		if ( $this->is_new_record() ) {
			return false;
		}

		$meta = new OsCustomerMetaModel();

		return $meta->save_by_key( $meta_key, $meta_value, $this->id );
	}

	public function set_timezone_name( $timezone_name = false ) {
		if ( ! $timezone_name ) {
			$timezone_name = OsTimeHelper::get_timezone_name_from_session();
		}
		$this->save_meta_by_key( 'timezone_name', $timezone_name );
	}

	public function get_selected_timezone_name() {
		return $this->get_meta_by_key( 'timezone_name', OsTimeHelper::get_timezone_name_from_session() );
	}

	public function get_selected_timezone_obj() {
		$timezone_obj = new DateTimeZone( $this->get_selected_timezone_name() );

		return $timezone_obj;
	}

	public function get_timeshift_in_minutes() {
		return OsTimeHelper::get_timezone_shift_in_minutes( $this->get_selected_timezone_name() );
	}


	public function delete( $id = false ) {
		if ( ! $id && isset( $this->id ) ) {
			$id = $this->id;
		}
		$bookings           = new OsBookingModel();
		$bookings_to_delete = $bookings->where( [ 'customer_id' => $id ] )->get_results_as_models();
		if ( $bookings_to_delete ) {
			foreach ( $bookings_to_delete as $booking ) {
				$booking->delete();
			}
		}
		$orders           = new OsOrderModel();
		$orders_to_delete = $orders->where( [ 'customer_id' => $id ] )->get_results_as_models();
		if ( $orders_to_delete ) {
			foreach ( $orders_to_delete as $order ) {
				$order->delete();
			}
		}
		$transactions           = new OsTransactionModel();
		$transactions_to_delete = $transactions->where( [ 'customer_id' => $id ] )->get_results_as_models();
		if ( $transactions_to_delete ) {
			foreach ( $transactions_to_delete as $transaction ) {
				$transaction->delete();
			}
		}
		$customer_metas           = new OsCustomerMetaModel();
		$customer_metas_to_delete = $customer_metas->where( [ 'object_id' => $id ] )->get_results_as_models();
		if ( $customer_metas_to_delete ) {
			foreach ( $customer_metas_to_delete as $customer_meta ) {
				$customer_meta->delete();
			}
		}

		return parent::delete( $id );
	}


	public function get_bookings( $limit = false, $filter_allowed_records = false ) {
		$bookings = new OsBookingModel();
		if ( $limit ) {
			$bookings = $bookings->set_limit( $limit );
		}
		if ( $filter_allowed_records ) {
			$bookings->filter_allowed_records();
		}

		return $bookings->where( [ 'customer_id' => $this->id ] )->get_results_as_models();
	}

	public function get_past_bookings( $limit = false, $filter_allowed_records = false ) {
		$bookings = new OsBookingModel();
		if ( $limit ) {
			$bookings = $bookings->set_limit( $limit );
		}
		if ( $filter_allowed_records ) {
			$bookings->filter_allowed_records();
		}

		return $bookings->should_not_be_cancelled()->where( array(
			'customer_id' => $this->id,
			'OR'          => array(
				'start_date <' => OsTimeHelper::today_date( 'Y-m-d' ),
				'AND'          => array(
					'start_date'   => OsTimeHelper::today_date( 'Y-m-d' ),
					'start_time <' => OsTimeHelper::get_current_minutes()
				)
			)
		) )->get_results_as_models();
	}

	/**
	 * @return OsBundleModel[]
	 */
	public function get_not_scheduled_bundles(): array {
		$non_scheduled_bundles = [];
		$orders                = new OsOrderModel();
		$orders                = $orders->where( [ 'customer_id' => $this->id ] )->get_results_as_models();
		if ( $orders ) {
			foreach ( $orders as $order ) {
				$bundles = $order->get_bundles_from_order_items();
				if ( $bundles ) {
					foreach ( $bundles as $order_item_id => $bundle ) {
						$bundle_services = $bundle->get_services();
						foreach ( $bundle_services as $bundle_service ) {
							$bookings                 = new OsBookingModel();
							$total_scheduled_bookings = $bookings->where( [ 'order_item_id' => $order_item_id, 'service_id' => $bundle_service->id ] )->should_not_be_cancelled()->count();
							if ( $total_scheduled_bookings < $bundle_service->join_attributes['quantity'] ) {
								$non_scheduled_bundles[ $order_item_id ] = $bundle;
								break;
							}
						}
					}
				}
			}
		}

		return $non_scheduled_bundles;
	}

	public function get_cancelled_bookings( $limit = false, $filter_allowed_records = false ) {
		$bookings = new OsBookingModel();
		if ( $limit ) {
			$bookings = $bookings->set_limit( $limit );
		}
		if ( $filter_allowed_records ) {
			$bookings->filter_allowed_records();
		}

		return $bookings->should_be_cancelled()->order_by( 'start_date, start_time asc' )->where( [ 'customer_id' => $this->id ] )->get_results_as_models();
	}

	public function get_future_bookings( $limit = false, $filter_allowed_records = false ) {
		$bookings = new OsBookingModel();
		if ( $limit ) {
			$bookings = $bookings->set_limit( $limit );
		}
		if ( $filter_allowed_records ) {
			$bookings->filter_allowed_records();
		}

		return $bookings->should_not_be_cancelled()->order_by( 'start_date, start_time asc' )->where( [ 'customer_id' => $this->id ] )->should_be_in_future()->get_results_as_models();
	}


	public function get_future_bookings_count( $filter_allowed_records = false ) {
		$bookings = new OsBookingModel();
		if ( $filter_allowed_records ) {
			$bookings->filter_allowed_records();
		}

		return $bookings->should_not_be_cancelled()->where( array(
			'customer_id' => $this->id,
			'OR'          => array(
				'start_date >' => OsTimeHelper::today_date( 'Y-m-d' ),
				'AND'          => array(
					'start_date'   => OsTimeHelper::today_date( 'Y-m-d' ),
					'start_time >' => OsTimeHelper::get_current_minutes()
				)
			)
		) )->count();
	}

	public function get_total_bookings_count( $filter_allowed_records = false ) {
		$bookings = new OsBookingModel();
		if ( $filter_allowed_records ) {
			$bookings->filter_allowed_records();
		}

		return $bookings->select( 'count(id) as total_bookings' )->where( array( 'customer_id' => $this->id ) )->count();
	}


	public function filter_allowed_records(): OsModel {
		if ( ! OsRolesHelper::are_all_records_allowed() ) {
			$this->select( LATEPOINT_TABLE_CUSTOMERS . '.*' )->join( LATEPOINT_TABLE_BOOKINGS, [ 'customer_id' => LATEPOINT_TABLE_CUSTOMERS . '.id' ] )->group_by( LATEPOINT_TABLE_CUSTOMERS . '.id' );
			if ( ! OsRolesHelper::are_all_records_allowed( 'agent' ) ) {
				$this->filter_where_conditions( [ LATEPOINT_TABLE_BOOKINGS . '.agent_id' => OsRolesHelper::get_allowed_records( 'agent' ) ] );
			}
			if ( ! OsRolesHelper::are_all_records_allowed( 'location' ) ) {
				$this->filter_where_conditions( [ LATEPOINT_TABLE_BOOKINGS . '.location_id' => OsRolesHelper::get_allowed_records( 'location' ) ] );
			}
			if ( ! OsRolesHelper::are_all_records_allowed( 'service' ) ) {
				$this->filter_where_conditions( [ LATEPOINT_TABLE_BOOKINGS . '.service_id' => OsRolesHelper::get_allowed_records( 'service' ) ] );
			}
		}

		return $this;
	}

	public function update_password( $password, $is_hashed = false ) {
		if ( ! $is_hashed ) {
			$password = OsAuthHelper::hash_password( $password );
		}

		return $this->update_attributes( [ 'password' => $password, 'is_guest' => false ] );
	}

	protected function get_full_name() {
		return trim( join( ' ', array( $this->first_name, $this->last_name ) ) );
	}

	protected function get_default_status() {
		return 'pending_verification';
	}

	protected function before_create() {
		if ( ! isset( $this->is_guest ) ) {
			$this->is_guest = true;
		}
		if ( empty( $this->status ) ) {
			$this->status = $this->get_default_status();
		}
		if ( empty( $this->password ) ) {
			$this->password = wp_hash_password( bin2hex( openssl_random_pseudo_bytes( 8 ) ) );
		}
		if ( empty( $this->activation_key ) ) {
			$this->activation_key = sha1( wp_rand( 10000, 99999 ) . time() . $this->email );
		}
		if ( empty( $this->account_nonse ) ) {
			$this->account_nonse = sha1( wp_rand( 10000, 99999 ) . time() . $this->activation_key );
		}
	}


	public function get_avatar_url() {
		return OsCustomerHelper::get_avatar_url( $this );
	}

	public function get_avatar_image() {
		return OsCustomerHelper::get_avatar_image( $this );
	}

	// if this was a guest account without a set password and social login was not used, you can login just by email
	public function can_login_without_password() {
		return ( $this->is_guest && empty( $this->google_user_id ) && empty( $this->facebook_user_id ) );
	}

	public function prepare_data_before_it_is_set( $data ) {
		if ( isset( $data['phone'] ) ) {
			$data['phone'] = OsUtilHelper::sanitize_phone_number( $data['phone'] );
		}

		return $data;
	}


	protected function allowed_params( $role = 'admin' ) {
		switch ( $role ) {
			case 'admin':
				$allowed_params = [
					'id',
					'first_name',
					'last_name',
					'email',
					'phone',
					'avatar_image_id',
					'is_guest',
					'notes',
					'admin_notes',
					'wordpress_user_id',
					'password'
				];
				break;
			case 'customer':
			case 'public':
				$allowed_params = [
					'first_name',
					'last_name',
					'email',
					'phone',
					'avatar_image_id',
					'notes',
					'password'
				];
				break;
		}

		return $allowed_params;
	}

	protected function params_to_save( $role = 'admin' ) {
		$params_to_save = array(
			'id',
			'first_name',
			'last_name',
			'email',
			'phone',
			'password',
			'activation_key',
			'account_nonse',
			'avatar_image_id',
			'status',
			'is_guest',
			'notes',
			'admin_notes',
			'wordpress_user_id',
			'google_user_id',
			'facebook_user_id'
		);

		return $params_to_save;
	}

	protected function properties_to_validate( $alternative_validation = false ) {
		// if alternative validation is enabled - use a different scope of rules (useful when you don't need to run all validations for example on social login)
		if ( $alternative_validation ) {
			$validations = array(
				'email' => array( 'presence', 'email', 'uniqueness' ),
			);
		} else {
			$validations = array(
				'first_name' => array( 'presence' ),
				'last_name'  => array( 'presence' ),
				'email'      => array( 'presence', 'email', 'uniqueness' ),
			);

			$default_fields = OsSettingsHelper::get_default_fields_for_customer();
			foreach ( $default_fields as $name => $field ) {
				if ( $field['required'] && $field['active'] ) {
					$validations[ $name ][] = 'presence';
					$validations[ $name ]   = array_unique( $validations[ $name ] );
				} else {
					if ( isset( $validations[ $name ] ) ) {
						$validations[ $name ] = array_diff( $validations[ $name ], [ 'presence' ] );
					}
				}
			}
			$validations = apply_filters( 'latepoint_customer_model_validations', $validations );
		}

		return $validations;
	}
}