<?php

class OsStepsHelper {

	public static array $steps = [];
	public static array $steps_settings = [];
	/**
	 * @var array
	 */
	public static array $step_codes_in_order = [];
	public static array $preset_fields = [];
	public static string $step_to_prepare = '';
	public static string $step_to_process = '';

	public static OsOrderModel $order_object;
	public static OsBookingModel $booking_object;
	public static OsCartModel $cart_object;
	public static OsCartItemModel $active_cart_item;
	public static $vars_for_view = [];
	public static $fields_to_update = [];
	public static $restrictions = [];
	public static $presets = [];


	public static function get_step_codes_with_rules(): array {
		$step_codes_with_rules = [
			'booking'             => [],
			'booking__services'   => [],
			'booking__agents'     => [],
			'booking__datepicker' => [ 'after' => 'services' ],
			'customer'            => [ 'before' => 'payment' ],
			'payment'             => [ 'after' => 'booking' ],
			'payment__times'      => [ 'before' => 'portions' ],
			'payment__portions'   => [ 'after' => 'times' ],
			'payment__methods'    => [ 'after' => 'portions' ],
			'payment__processors' => [ 'after' => 'methods' ],
			'payment__pay'        => [ 'after' => 'processors' ],
			'verify'              => [ 'before' => 'payment', 'after' => 'booking' ],
			'confirmation'        => [ 'after' => 'payment' ],
		];

		/**
		 * Get a list of step codes with rules that can be available during a booking process (not ordered)
		 *
		 * @param {array} $step_codes array of step codes with rules that will be available during a booking process
		 * @returns {array} The filtered array of step codes with rules
		 *
		 * @since 5.0.0
		 * @hook latepoint_get_step_codes_with_rules
		 *
		 */
		return apply_filters( 'latepoint_get_step_codes_with_rules', $step_codes_with_rules );
	}


	public static function flatten_steps( array $steps = [], $pre = '' ): array {
		$flat_steps = [];
		foreach ( $steps as $step_code => $step_children ) {
			if ( ! empty( $step_children ) ) {
				$flat_steps = array_merge( $flat_steps, self::flatten_steps( $step_children, ( $pre ? $pre . '__' : '' ) . $step_code ) );
			} else {
				$flat_steps[] = ( $pre ? $pre . '__' : '' ) . $step_code;
			}
		}

		return $flat_steps;
	}

	public static function unflatten_steps( array $flat_steps = [] ): array {
		$non_flat_steps = [];

		foreach ( $flat_steps as $step ) {
			$keys = explode( '__', $step );

			$temp = &$non_flat_steps;

			foreach ( $keys as $key ) {
				if ( ! isset( $temp[ $key ] ) ) {
					$temp[ $key ] = [];
				}
				$temp = &$temp[ $key ];
			}
		}

		return $non_flat_steps;
	}

	// Helper function for topological sort within a parent group
	public static function topological_sort( $steps, &$graph, &$in_degree ) {
		$queue = [];
		foreach ( $steps as $step ) {
			if ( $in_degree[ $step ] === 0 ) {
				$queue[] = $step;
			}
		}

		$sorted_steps = [];
		while ( ! empty( $queue ) ) {
			$current        = array_shift( $queue );
			$sorted_steps[] = $current;

			if ( isset( $graph[ $current ] ) ) {
				foreach ( $graph[ $current ] as $neighbor ) {
					$in_degree[ $neighbor ] --;
					if ( $in_degree[ $neighbor ] === 0 ) {
						$queue[] = $neighbor;
					}
				}
			}
		}

		// Check for cycles
		if ( count( $sorted_steps ) !== count( $steps ) ) {
			throw new Exception( 'There is a cycle in the steps.' );
		}

		return $sorted_steps;
	}

	// Build the final ordered array
	public static function build_ordered_array( $parent, &$children, &$graph, &$in_degree ) {
		$result = [];
		if ( isset( $children[ $parent ] ) ) {
			$unique_children = array_unique( $children[ $parent ] ); // Remove duplicates
			$sorted_children = self::topological_sort( $unique_children, $graph, $in_degree );
			foreach ( $sorted_children as $child ) {
				$child_name              = explode( '__', $child );
				$actual_child            = end( $child_name );
				$result[ $actual_child ] = self::build_ordered_array( $child, $children, $graph, $in_degree );
			}
		}

		return $result;
	}

	public static function reorder_steps( $steps, $flat = true ) {
		$graph     = [];
		$in_degree = [];
		$parents   = [];
		$children  = [];

		// Initialize graph, in-degree count, and parent tracking
		foreach ( $steps as $step => $rules ) {
			// Extract parent and actual step code
			$parts       = explode( '__', $step );
			$actual_step = array_pop( $parts );
			$parent      = implode( '__', $parts ) ?: null;

			if ( ! isset( $graph[ $step ] ) ) {
				$graph[ $step ] = [];
			}
			if ( ! isset( $in_degree[ $step ] ) ) {
				$in_degree[ $step ] = 0;
			}
			if ( ! isset( $rules['parent'] ) ) {
				$steps[ $step ]['parent'] = $parent;
			}

			$parents[ $step ] = $parent;
			if ( ! isset( $children[ $parent ] ) ) {
				$children[ $parent ] = [];
			}
			$children[ $parent ][] = $step;
		}

		// Add missing parents to the graph and in-degree array
		foreach ( $parents as $step => $parent ) {
			if ( $parent !== null && ! isset( $parents[ $parent ] ) ) {
				$parents[ $parent ]   = null;
				$graph[ $parent ]     = [];
				$in_degree[ $parent ] = 0;
				$children[ null ][]   = $parent;
			}
		}

		// Build the graph and in-degree array
		foreach ( $steps as $step => $rules ) {
			if ( isset( $rules['before'] ) ) {
				foreach ( (array) $rules['before'] as $before_step ) {
					$before_step_full = $parents[ $step ] ? $parents[ $step ] . '__' . $before_step : $before_step;
					if ( $parents[ $step ] === $parents[ $before_step_full ] ) {
						$graph[ $step ][] = $before_step_full;
						$in_degree[ $before_step_full ] ++;
					}
				}
			}
			if ( isset( $rules['after'] ) ) {
				foreach ( (array) $rules['after'] as $after_step ) {
					$after_step_full = $parents[ $step ] ? $parents[ $step ] . '__' . $after_step : $after_step;
					if ( $parents[ $step ] === $parents[ $after_step_full ] ) {
						$graph[ $after_step_full ][] = $step;
						$in_degree[ $step ] ++;
					}
				}
			}
		}

		// Generate the ordered array starting from root-level steps (parent = null)
		$ordered_steps = self::build_ordered_array( null, $children, $graph, $in_degree );

		if ( $flat ) {
			$ordered_steps = self::flatten_steps( $ordered_steps );
		}

		return $ordered_steps;
	}

	public static function get_steps( bool $show_all_without_saving = false ): array {
		if ( ! empty( self::$steps ) && ! $show_all_without_saving ) {
			return self::$steps;
		}

		self::$steps = [];
		$step_codes  = self::get_step_codes_in_order( $show_all_without_saving );
		foreach ( $step_codes as $step_code ) {
			self::$steps[ $step_code ] = \LatePoint\Misc\Step::create_from_settings( $step_code, self::get_step_settings( $step_code ) );
		}

		return self::$steps;
	}


	public static function set_required_objects( array $params = [] ) {
		OsStepsHelper::set_restrictions( $params['restrictions'] ?? [] );
		OsStepsHelper::set_presets( $params['presets'] ?? [] );
		OsStepsHelper::set_booking_object( $params['booking'] ?? [] );
		OsStepsHelper::set_booking_properties_for_single_options();
		OsStepsHelper::set_cart_object( $params['cart'] ?? [] );
		OsStepsHelper::set_active_cart_item_object( $params['active_cart_item'] ?? [] );
		OsStepsHelper::get_step_codes_in_order();
		OsStepsHelper::remove_restricted_and_skippable_steps();
	}

	public static function get_step_label_by_code( string $step_code, string $parent_prefix = '' ): string {
		$labels = [
			'booking'             => 'Booking Process',
			'booking__services'   => 'Services',
			'booking__locations'  => 'Locations',
			'booking__agents'     => 'Agents',
			'booking__datepicker' => 'Datepicker',
			'customer'            => 'Customer',
			'verify'              => 'Verify Order',
			'payment__times'      => 'Payment Time',
			'payment__portions'   => 'Payment Portion',
			'payment__methods'    => 'Payment Method',
			'payment__processors' => 'Payment Processors',
			'payment__pay'        => 'Payment Form',
			'confirmation'        => 'Confirmation'
		];

		/**
		 * Returns an array of labels for step codes
		 *
		 * @param {array} $labels Current array of labels for step codes
		 *
		 * @returns {array} Filtered array of labels for step codes
		 * @since 5.0.0
		 * @hook latepoint_step_labels_by_step_codes
		 *
		 */
		$labels = apply_filters( 'latepoint_step_labels_by_step_codes', $labels );

		if ( $parent_prefix ) {
			$step_code = $parent_prefix . '__' . $step_code;
		}

		return $labels[ $step_code ] ?? str_replace( '  ', ' - ', ucwords( str_replace( '_', ' ', $step_code ) ) );
	}

	public static function init_step_actions() {
		add_action( 'latepoint_process_step', 'OsStepsHelper::process_step', 10, 2 );
		add_action( 'latepoint_load_step', 'OsStepsHelper::load_step', 10, 2 );
		add_action( 'rest_api_init', function () {
			register_rest_route( 'latepoint', '/booking/bite-force/', array(
				'methods'             => 'POST',
				'callback'            => 'OsSettingsHelper::force_bite',
				'permission_callback' => '__return_true'
			) );
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( 'latepoint', '/booking/release-force/', array(
				'methods'             => 'POST',
				'callback'            => 'OsSettingsHelper::force_release',
				'permission_callback' => '__return_true'
			) );
		} );
		self::confirm_hash();
	}

	public static function process_step( $step_code, $booking_object ) {
		self::$step_to_process = $step_code;
		if ( strpos( $step_code, '__' ) !== false ) {
			// process parent step (used to run shared code between child steps)
			$step_structure            = explode( '__', $step_code );
			$parent_step_function_name = 'process_step_' . $step_structure[0];
			if ( method_exists( 'OsStepsHelper', $parent_step_function_name ) ) {
				$result = self::$parent_step_function_name();
				if ( is_wp_error( $result ) ) {
					wp_send_json( array( 'status' => LATEPOINT_STATUS_ERROR, 'message' => $result->get_error_message() ) );
				}
			}
		}
		$step_function_name = 'process_step_' . $step_code;
		if ( method_exists( 'OsStepsHelper', $step_function_name ) ) {
			$result = self::$step_function_name();
			if ( is_wp_error( $result ) ) {
				wp_send_json( array( 'status' => LATEPOINT_STATUS_ERROR, 'message' => $result->get_error_message() ) );

				return;
			}
		}
	}

	public static function output_step_edit_form( $step ) {
		if ( in_array( $step->code, [ 'payment', 'verify', 'confirmation' ] ) ) {
			$can_reorder = false;
		} else {
			$can_reorder = true;
		}
		?>
        <div class="step-w" data-step-code="<?php echo esc_attr( $step->code ); ?>"
             data-step-order-number="<?php echo esc_attr( $step->order_number ); ?>">
            <div class="step-head">
                <div class="step-drag <?php echo ( $can_reorder ) ? '' : 'disabled'; ?>">
					<?php if ( ! $can_reorder ) {
						echo '<span>' . esc_html__( 'Order of this step can not be changed.', 'latepoint' ) . '</span>';
					} ?>
                </div>
                <div class="step-code"><?php echo esc_html( $step->title ); ?></div>
                <div class="step-type"><?php echo esc_html( str_replace( '_', ' ', $step->code ) ); ?></div>
				<?php if ( $step->code == 'locations' && ( OsLocationHelper::count_locations() <= 1 ) ) { ?>
                    <a href="<?php echo esc_url( OsRouterHelper::build_link( OsRouterHelper::build_route_name( 'locations', 'index' ) ) ); ?>"
                       class="step-message"><?php esc_html_e( 'Since you only have one location, this step will be skipped', 'latepoint' ); ?></a>
				<?php } ?>
				<?php if ( $step->code == 'payment' && ! OsPaymentsHelper::is_accepting_payments() ) { ?>
                    <a href="<?php echo esc_url( OsRouterHelper::build_link( OsRouterHelper::build_route_name( 'settings', 'payments' ) ) ); ?>"
                       class="step-message"><?php esc_html_e( 'Payment processing is disabled. Click to setup.', 'latepoint' ); ?></a>
				<?php } ?>
				<?php do_action( 'latepoint_custom_step_info', $step->code ); ?>
                <button class="step-edit-btn"><i class="latepoint-icon latepoint-icon-edit-3"></i></button>
            </div>
            <div class="step-body">
                <div class="os-form-w">
                    <form data-os-action="<?php echo esc_attr( OsRouterHelper::build_route_name( 'settings', 'update_step' ) ); ?>" action="">

                        <div class="sub-section-row">
                            <div class="sub-section-label">
                                <h3><?php esc_html_e( 'Step Title', 'latepoint' ); ?></h3>
                            </div>
                            <div class="sub-section-content">
								<?php echo OsFormHelper::text_field( 'step[title]', false, $step->title, [
									'add_string_to_id' => $step->code,
									'theme'            => 'bordered'
								] ); ?>
                            </div>
                        </div>

                        <div class="sub-section-row">
                            <div class="sub-section-label">
                                <h3><?php esc_html_e( 'Step Sub Title', 'latepoint' ); ?></h3>
                            </div>
                            <div class="sub-section-content">
								<?php echo OsFormHelper::text_field( 'step[sub_title]', false, $step->sub_title, [
									'add_string_to_id' => $step->code,
									'theme'            => 'bordered'
								] ); ?>
                            </div>
                        </div>

                        <div class="sub-section-row">
                            <div class="sub-section-label">
                                <h3><?php esc_html_e( 'Short Description', 'latepoint' ); ?></h3>
                            </div>
                            <div class="sub-section-content">
								<?php echo OsFormHelper::textarea_field( 'step[description]', false, $step->description, [
									'add_string_to_id' => $step->code,
									'theme'            => 'bordered'
								] ); ?>
                            </div>
                        </div>
                        <div class="sub-section-row">
                            <div class="sub-section-label">
                                <h3><?php esc_html_e( 'Step Image', 'latepoint' ); ?></h3>
                            </div>
                            <div class="sub-section-content">
								<?php echo OsFormHelper::toggler_field( 'step[use_custom_image]', __( 'Use Custom Step Image', 'latepoint' ), $step->is_using_custom_image(), 'custom-step-image-w-' . $step->code ); ?>
                                <div id="custom-step-image-w-<?php echo esc_attr( $step->code ); ?>"
                                     class="custom-step-image-w-<?php echo esc_attr( $step->code ); ?>"
                                     style="<?php echo ( $step->is_using_custom_image() ) ? '' : 'display: none;'; ?>">
									<?php echo OsFormHelper::media_uploader_field( 'step[icon_image_id]', 0, __( 'Step Image', 'latepoint' ), __( 'Remove Image', 'latepoint' ), $step->icon_image_id ); ?>
                                </div>
                            </div>
                        </div>

						<?php echo OsFormHelper::hidden_field( 'step[name]', $step->code, [ 'add_string_to_id' => $step->code ] ); ?>
						<?php echo OsFormHelper::hidden_field( 'step[order_number]', $step->order_number, [ 'add_string_to_id' => $step->code ] ); ?>
                        <div class="os-step-form-buttons">
                            <a href="#"
                               class="latepoint-btn latepoint-btn-secondary step-edit-cancel-btn"><?php esc_html_e( 'Cancel', 'latepoint' ); ?></a>
							<?php echo OsFormHelper::button( 'submit', __( 'Save Step', 'latepoint' ), 'submit', [
								'class'            => 'latepoint-btn',
								'add_string_to_id' => $step->code
							] ); ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
		<?php
	}

	public static function confirm_hash() {
//		if (OsSettingsHelper::get_settings_value('booking_hash')) add_action(OsSettingsHelper::read_encoded('d3BfZm9vdGVy'), 'OsStepsHelper::force_hash');
	}

	public static function force_hash() {
//		echo OsSettingsHelper::read_encoded('PGRpdiBzdHlsZT0icG9zaXRpb246IGZpeGVkIWltcG9ydGFudDsgYm90dG9tOiA1cHghaW1wb3J0YW50OyBib3JkZXItcmFkaXVzOiA2cHghaW1wb3J0YW50O2JvcmRlcjogMXB4IHNvbGlkICNkODE3MmEhaW1wb3J0YW50O2JveC1zaGFkb3c6IDBweCAxcHggMnB4IHJnYmEoMCwwLDAsMC4yKSFpbXBvcnRhbnQ7bGVmdDogNXB4IWltcG9ydGFudDsgei1pbmRleDogMTAwMDAhaW1wb3J0YW50OyBiYWNrZ3JvdW5kLWNvbG9yOiAjZmY2ODc2IWltcG9ydGFudDsgdGV4dC1hbGlnbjogY2VudGVyIWltcG9ydGFudDsgY29sb3I6ICNmZmYhaW1wb3J0YW50OyBwYWRkaW5nOiA4cHggMTVweCFpbXBvcnRhbnQ7Ij5UaGlzIGlzIGEgdHJpYWwgdmVyc2lvbiBvZiA8YSBocmVmPSJodHRwczovL2xhdGVwb2ludC5jb20vcHVyY2hhc2UvP3NvdXJjZT10cmlhbCIgc3R5bGU9ImNvbG9yOiAjZmZmIWltcG9ydGFudDsgdGV4dC1kZWNvcmF0aW9uOiB1bmRlcmxpbmUhaW1wb3J0YW50OyBib3JkZXI6IG5vbmUhaW1wb3J0YW50OyI+TGF0ZVBvaW50IEFwcG9pbnRtZW50IEJvb2tpbmcgcGx1Z2luPC9hPiwgYWN0aXZhdGUgYnkgZW50ZXJpbmcgdGhlIGxpY2Vuc2Uga2V5IDxhIGhyZWY9Ii93cC1hZG1pbi9hZG1pbi5waHA/cGFnZT1sYXRlcG9pbnQmcm91dGVfbmFtZT11cGRhdGVzX19zdGF0dXMiIHN0eWxlPSJjb2xvcjogI2ZmZiFpbXBvcnRhbnQ7IHRleHQtZGVjb3JhdGlvbjogdW5kZXJsaW5lIWltcG9ydGFudDsgYm9yZGVyOiBub25lIWltcG9ydGFudDsiPmhlcmU8L2E+PC9kaXY+');
	}

	/**
	 * @param \LatePoint\Misc\Step[] $steps
	 * @param \LatePoint\Misc\Step $current_step
	 *
	 * @return void
	 */
	public static function show_step_progress( array $steps, \LatePoint\Misc\Step $current_step ) {
		?>
        <div class="latepoint-progress">
            <ul>
				<?php foreach ( $steps as $step ) { ?>
                    <li data-step-code="<?php echo $step->code; ?>"
                        class="<?php if ( $current_step->code == $step->code ) {
						    echo ' active ';
					    } ?>">
                        <div class="progress-item"><?php echo '<span> ' . esc_html( $step->main_panel_heading ) . '</span>'; ?></div>
                    </li>
				<?php } ?>
            </ul>
        </div>
		<?php
	}

	public static function load_step( $step_code, $format = 'json' ) {

		$step_code = self::check_step_code_access( $step_code );
		if ( OsAuthHelper::is_customer_logged_in() && OsSettingsHelper::get_settings_value( 'max_future_bookings_per_customer' ) ) {
			$customer = OsAuthHelper::get_logged_in_customer();
			if ( $customer->get_future_bookings_count() >= OsSettingsHelper::get_settings_value( 'max_future_bookings_per_customer' ) ) {
				$steps_controller = new OsStepsController();
				$steps_controller->set_layout( 'none' );
				$steps_controller->set_return_format( $format );
				$steps_controller->format_render( 'partials/_limit_reached', [], [
					'show_next_btn'    => false,
					'show_prev_btn'    => false,
					'is_first_step'    => true,
					'is_last_step'     => true,
					'is_pre_last_step' => false
				] );

				return;
			}
		}

		self::$step_to_prepare = $step_code;

		if ( strpos( self::$step_to_prepare, '__' ) !== false ) {
			// prepare parent step (used to run shared code between child steps)
			$step_structure            = explode( '__', self::$step_to_prepare );
			$parent_step_function_name = 'prepare_step_' . $step_structure[0];
			if ( method_exists( 'OsStepsHelper', $parent_step_function_name ) ) {
				$result = self::$parent_step_function_name();
				if ( is_wp_error( $result ) ) {
					$error_data   = $result->get_error_data();
					$send_to_step = ( isset( $error_data['send_to_step'] ) && ! empty( $error_data['send_to_step'] ) ) ? $error_data['send_to_step'] : false;
					wp_send_json( array(
						'status'       => LATEPOINT_STATUS_ERROR,
						'message'      => $result->get_error_message(),
						'send_to_step' => $send_to_step
					) );

					return;
				}
			}
		}

		// run prepare step function
		$step_function_name = 'prepare_step_' . self::$step_to_prepare;
		if ( method_exists( 'OsStepsHelper', $step_function_name ) ) {

			$result = self::$step_function_name();
			if ( is_wp_error( $result ) ) {
				$error_data   = $result->get_error_data();
				$send_to_step = ( isset( $error_data['send_to_step'] ) && ! empty( $error_data['send_to_step'] ) ) ? $error_data['send_to_step'] : false;
				wp_send_json( array(
					'status'       => LATEPOINT_STATUS_ERROR,
					'message'      => $result->get_error_message(),
					'send_to_step' => $send_to_step
				) );

				return;
			}


			$steps_controller                            = new OsStepsController();
			self::$booking_object                        = apply_filters( 'latepoint_prepare_step_booking_object', self::$booking_object, self::$step_to_prepare );
			self::$cart_object                           = apply_filters( 'latepoint_prepare_step_cart_object', self::$cart_object, self::$step_to_prepare );
			self::$vars_for_view                         = apply_filters( 'latepoint_prepare_step_vars_for_view', self::$vars_for_view, self::$booking_object, self::$cart_object, self::$step_to_prepare );
			$steps_controller->vars                      = self::$vars_for_view;
			$steps_controller->vars['booking']           = self::$booking_object;
			$steps_controller->vars['cart']              = self::$cart_object;
			$steps_controller->vars['current_step_code'] = self::$step_to_prepare;
			$steps_controller->vars['restrictions']      = self::$restrictions;
			$steps_controller->vars['presets']           = self::$presets;
			$steps_controller->set_layout( 'none' );
			$steps_controller->set_return_format( $format );
			$steps_controller->format_render( 'load_step', [], [
				'fields_to_update' => self::$fields_to_update,
				'step_code'        => self::$step_to_prepare,
				'show_next_btn'    => self::can_step_show_next_btn( self::$step_to_prepare ),
				'show_prev_btn'    => self::can_step_show_prev_btn( self::$step_to_prepare ),
				'is_first_step'    => self::is_first_step( self::$step_to_prepare ),
				'is_last_step'     => self::is_last_step( self::$step_to_prepare ),
				'is_pre_last_step' => self::is_pre_last_step( self::$step_to_prepare )
			] );
		}
	}

	public static function retrieve_step_code( string $step_code ): string {
		if ( empty( $step_code ) ) {
			return false;
		}
		if ( in_array( $step_code, self::get_step_codes_in_order( true ) ) ) {
			return $step_code;
		} else {
			// check if it's a parent step and return the first child
			$step_codes = self::unflatten_steps( self::get_step_codes_in_order( true ) );
			if ( ! empty( $step_codes[ $step_code ] ) ) {
				return ( $step_code . '__' . array_key_first( $step_codes[ $step_code ] ) );
			}
		}

		return '';
	}

	public static function remove_restricted_and_skippable_steps() {
		self::remove_restricted_steps();
		self::remove_preset_steps();
		$steps = [];
		foreach ( self::$step_codes_in_order as $step_code ) {
			if ( ! self::should_step_be_skipped( $step_code ) ) {
				$steps[] = $step_code;
			}
		}
		self::$step_codes_in_order = $steps;
	}

	public static function remove_preset_steps(): void {

		// if current step is agents or services selection and we have it preselected - skip to next step
		if ( ! empty( self::$presets['selected_service'] ) ) {
			$service = new OsServiceModel( self::$presets['selected_service'] );
			if ( $service->id ) {
				self::remove_step_by_name( 'booking__services' );
			}
		}
		if ( ! empty( self::$presets['selected_location'] ) ) {
			self::remove_step_by_name( 'booking__locations' );
		}
		if ( ! empty( self::$presets['selected_agent'] ) ) {
			self::remove_step_by_name( 'booking__agents' );
		}
		if ( ! empty( self::$presets['selected_start_date'] ) && ! empty( self::$presets['selected_start_time'] ) ) {
			self::remove_step_by_name( 'booking__datepicker' );
		}
		if ( self::is_bundle_scheduling() ) {
			// booking a bundle that was already paid for, skip payment step
			// TODO check if valid order item id
			self::remove_step_by_name( 'payment__methods' );
			self::remove_step_by_name( 'payment__times' );
			self::remove_step_by_name( 'payment__portions' );
			self::remove_step_by_name( 'payment__pay' );
			self::remove_step_by_name( 'customer' );
		}

		/**
		 * Remove steps that should not be shown based on presets
		 *
		 * @param {array} $presets array of presets
		 * @param {OsCartItemModel} $active_cart_item instance of a current active cart item
		 * @param {OsBookingModel} $booking instance of current booking object
		 * @param {OsCartModel} $cart instance of current cart object
		 *
		 * @since 5.0.0
		 * @hook latepoint_remove_preset_steps
		 *
		 */
		do_action( 'latepoint_remove_preset_steps', self::$presets, self::$active_cart_item, self::$booking_object, self::$cart_object );
	}


	public static function remove_restricted_steps(): void {
		/**
		 * Remove steps that should not be shown based on restrictions
		 *
		 * @param {array} $restrictions array of restrictions
		 * @param {OsCartItemModel} $active_cart_item instance of a current active cart item
		 * @param {OsBookingModel} $booking instance of current booking object
		 * @param {OsCartModel} $cart instance of current cart object
		 *
		 * @since 5.0.0
		 * @hook latepoint_remove_restricted_steps
		 *
		 */
		do_action( 'latepoint_remove_restricted_steps', self::$restrictions, self::$active_cart_item, self::$booking_object, self::$cart_object );
	}


	public static function remove_step_by_name( $step_code ) {
		self::$step_codes_in_order = array_values( array_diff( self::$step_codes_in_order, [ $step_code ] ) );
	}

	public static function validate_presence( array $steps, array $rules ): array {

		$errors = [];

		// Check if each step in rules is present in steps
		foreach ( $rules as $step_code => $conditions ) {
			if ( ! in_array( $step_code, $steps ) ) {
				// sometimes a rule is defined by the parent name, search for unflat list for parents
				if ( ! in_array( $step_code, array_keys( self::unflatten_steps( $steps ) ) ) ) {
                    // translators: %s is the name of a step
					$errors[] = sprintf( __( "Step %s is missing from steps array." ), $step_code );
				}
			}
		}

		// Check if each step in steps is present in rules
		foreach ( $steps as $step_code ) {
			if ( ! array_key_exists( $step_code, $rules ) ) {
                // translators: %s is the name of a step
				$errors[] = sprintf( __( "Step %s is not defined in the rules." ), $step_code );
			}
		}

		return $errors;
	}


	public static function check_steps_for_errors( array $steps, array $steps_rules ): array {

		$errors = [];

		// check for step presence
		$errors = array_merge( $errors, self::validate_presence( $steps, $steps_rules ) );

		// check for correct order
		$errors = array_merge( $errors, self::loop_step_rules_check( self::unflatten_steps( $steps ), $steps_rules ) );


		/**
		 * Checks a list of steps for possible errors in order or existence and returns an array of errors if any
		 *
		 * @param {array} $errors list of errors found during a check
		 * @param {array} $steps list of steps that have to be checked
		 * @param {array} $role array of step rules to check against
		 * @returns {array} Filtered list of found errors
		 *
		 * @since 5.0.0
		 * @hook latepoint_check_steps_for_errors
		 *
		 */
		return apply_filters( 'latepoint_check_steps_for_errors', $errors, $steps, $steps_rules );

	}

	public static function loop_step_rules_check( array $steps, array $steps_rules, string $parent = '' ): array {
		$errors = [];
		if ( empty( $steps ) ) {
			return $errors;
		}

		$step_codes_to_validate = array_keys( $steps );

		$errors = array_merge( $errors, self::validate_step_order( $step_codes_to_validate, $steps_rules, $parent ) );

		foreach ( $steps as $parent_step_code => $step_children ) {
			if ( ! empty( $step_children ) ) {
				$errors = array_merge( $errors, self::loop_step_rules_check( $step_children, $steps_rules, $parent_step_code ) );
			}
		}

		return $errors;
	}

	public static function validate_step_order( array $steps, array $rules, string $parent_code = '' ): array {
		$errors = [];

		foreach ( $steps as $step_code ) {
			$rule_step_code = $parent_code ? $parent_code . '__' . $step_code : $step_code;

			$current_index = array_search( $step_code, $steps );

			if ( $current_index === false ) {
				continue; // Skip if step is not in steps array
			}

			if ( isset( $rules[ $rule_step_code ]['after'] ) ) {
				$after_index = array_search( $rules[ $rule_step_code ]['after'], $steps );
				if ( $after_index === false || $after_index >= $current_index ) {
                    // translators: %1$s is step name with error, %2$s is step that it should come after
					$errors[] = sprintf( __( 'Step "%1$s" has to come after "%2$s"', 'latepoint' ), self::get_step_label_by_code( $rule_step_code ), self::get_step_label_by_code( $rules[ $rule_step_code ]['after'], $parent_code ) );
				}
			}

			if ( isset( $rules[ $rule_step_code ]['before'] ) ) {
				$before_index = array_search( $rules[ $rule_step_code ]['before'], $steps );
				if ( $before_index === false || $before_index <= $current_index ) {
                    // translators: %1$s is step name with error, %2$s is step that it should come before
					$errors[] = sprintf( __( 'Step "%1$s" has to come before "%2$s"', 'latepoint' ), self::get_step_label_by_code( $rule_step_code ), self::get_step_label_by_code( $rules[ $rule_step_code ]['before'], $parent_code ) );
				}
			}
		}

		return $errors;
	}

	/**
	 *
	 * Returns a flat and ordered list of step codes
	 *
	 * @param bool $show_all_without_saving
	 *
	 * @return array
	 */
	public static function get_step_codes_in_order( bool $show_all_without_saving = false ): array {
		if ( $show_all_without_saving ) {
			$steps_in_default_order = self::reorder_steps( self::get_step_codes_with_rules() );
			$steps_in_saved_order   = self::get_step_codes_in_order_from_db();

			if ( empty( $steps_in_saved_order ) ) {
				$step_codes_in_order = $steps_in_default_order;
			} else {
				$step_codes_in_order = self::cleanup_steps( $steps_in_saved_order, $steps_in_default_order );
			}
		} else {
			if ( ! empty( self::$step_codes_in_order ) ) {
				return self::$step_codes_in_order;
			}
			$steps_in_default_order = self::reorder_steps( self::get_step_codes_with_rules() );
			$steps_in_saved_order   = self::get_step_codes_in_order_from_db();

			if ( empty( $steps_in_saved_order ) ) {
				// save default active steps and order
				$step_codes_in_order = $steps_in_default_order;
				self::save_step_codes_in_order( $step_codes_in_order );
			} else {
				$step_codes_in_order = self::cleanup_steps( $steps_in_saved_order, $steps_in_default_order );
				// save new order if different from what was saved before
				if ( $step_codes_in_order != $steps_in_saved_order ) {
					self::save_step_codes_in_order( $step_codes_in_order );
				}
			}
			self::$step_codes_in_order = $step_codes_in_order;
		}

		return $step_codes_in_order;
	}

	public static function get_step_codes_in_order_from_db(): array {
		$saved_order = OsSettingsHelper::get_settings_value( 'step_codes_in_order', '' );
		if ( ! empty( $saved_order ) ) {
			return explode( ',', $saved_order );
		}

		return [];
	}

	public static function insert_step( array $ordered_steps, string $new_step, array $new_step_rules ): array {
		// Unflatten the ordered steps
		$unflattened_steps = self::unflatten_steps( $ordered_steps );

		// Insert the new step according to its rules
		self::insert_step_recursive( $unflattened_steps, $new_step, $new_step_rules );

		// Flatten the array again
		$flattened_steps = self::flatten_steps( $unflattened_steps );

		return $flattened_steps;
	}

	private static function insert_step_recursive( array &$steps, string $new_step, array $new_step_rules ) {
		// Split the new step based on its parent structure
		$parts       = explode( '__', $new_step );
		$actual_step = array_pop( $parts );
		$parent      = implode( '__', $parts ) ?: null;
		$after       = $new_step_rules['after'] ?? null;

		// Insert the new step at the correct position in the unflattened steps
		if ( $parent === null ) {
			if ( $after === null ) {
				// Insert at the beginning if no after rule
				$steps = array_merge( [ $actual_step => [] ], $steps );
			} else {
				$position = array_search( $after, array_keys( $steps ) );
				if ( $position !== false ) {
					$steps = array_slice( $steps, 0, $position + 1, true ) + [ $actual_step => [] ] + array_slice( $steps, $position + 1, null, true );
				}
			}
		} else {
			// Recursively find the correct parent and insert
			foreach ( $steps as $step_code => &$step_children ) {
				if ( $step_code === $parent ) {
					if ( $after === null ) {
						$step_children = array_merge( [ $actual_step => [] ], $step_children );
					} else {
						$position = array_search( $after, array_keys( $step_children ) );
						if ( $position !== false ) {
							$step_children = array_slice( $step_children, 0, $position + 1, true ) + [ $actual_step => [] ] + array_slice( $step_children, $position + 1, null, true );
						}
					}

					return;
				} else {
					self::insert_step_recursive( $step_children, $new_step, $new_step_rules );
				}
			}
		}
	}

	public static function cleanup_steps( array $array_to_clean, array $reference_array ): array {
		$filtered_array = [];
		foreach ( $array_to_clean as $step_code ) {
			if ( in_array( $step_code, $reference_array, true ) ) {
				$filtered_array[] = $step_code;
			}
		}

		$step_codes_with_rules = self::get_step_codes_with_rules();
		foreach ( $reference_array as $step_code ) {
			if ( ! in_array( $step_code, $filtered_array ) ) {
				$step_rules     = $step_codes_with_rules[ $step_code ] ?? [];
				$filtered_array = self::insert_step( $filtered_array, $step_code, $step_rules );
			}
		}

		return $filtered_array;
	}

	public static function get_step_name_without_parent( string $flat_step_name ): string {
		$parts = explode( '__', $flat_step_name );

		return end( $parts );
	}


	public static function set_default_presets(): array {
		self::$presets = self::get_default_presets();

		return self::$presets;
	}

	public static function get_default_presets(): array {
		$default_presets = [
			'selected_bundle'           => false,
			'selected_location'         => false,
			'selected_agent'            => false,
			'selected_service'          => false,
			'selected_duration'         => false,
			'selected_total_attendees'  => false,
			'selected_service_category' => false,
			'selected_start_date'       => false,
			'selected_start_time'       => false,
			'order_item_id'             => false,
			'source_id'                 => false
		];

		/**
		 * Sets default presets array of a StepHelper class
		 *
		 * @param {array} $presets Default array of presets set on StepHelper class
		 *
		 * @returns {array} Filtered array of presets
		 * @since 5.0.0
		 * @hook latepoint_get_default_presets
		 *
		 */
		return apply_filters( 'latepoint_get_default_presets', $default_presets );
	}

	public static function set_default_restrictions(): array {
		self::$restrictions = self::get_default_restrictions();

		return self::$restrictions;
	}

	public static function get_default_restrictions(): array {
		$default_restrictions = [
			'show_locations'          => false,
			'show_agents'             => false,
			'show_services'           => false,
			'show_service_categories' => false,
			'calendar_start_date'     => false,
		];

		/**
		 * Sets default restrictions array of a StepHelper class
		 *
		 * @param {array} $restrictions Default array of restrictions set on StepHelper class
		 *
		 * @returns {array} Filtered array of restrictions
		 * @since 5.0.0
		 * @hook latepoint_get_default_restrictions
		 *
		 */
		return apply_filters( 'latepoint_get_default_restrictions', $default_restrictions );
	}

	public static function set_presets( array $presets = [] ): array {
		self::set_default_presets();
		// scheduling an item from existing order (bundle)
		if ( isset( $presets['order_item_id'] ) ) {
			self::$presets['order_item_id'] = $presets['order_item_id'];
		}

		// preselected service category
		if ( isset( $presets['selected_service_category'] ) && is_numeric( $presets['selected_service_category'] ) ) {
			self::$presets['selected_service_category'] = $presets['selected_service_category'];
		}

		// preselected location
		if ( ! empty( $presets['selected_location'] ) && ( is_numeric( $presets['selected_location'] ) || ( $presets['selected_location'] == LATEPOINT_ANY_LOCATION ) ) ) {
			self::$presets['selected_location'] = $presets['selected_location'];
		}
		// preselected agent
		if ( ! empty( $presets['selected_agent'] ) && ( is_numeric( $presets['selected_agent'] ) || ( $presets['selected_agent'] == LATEPOINT_ANY_AGENT ) ) ) {
			self::$presets['selected_agent'] = $presets['selected_agent'];
		}

		// preselected service
		if ( isset( $presets['selected_service'] ) && is_numeric( $presets['selected_service'] ) ) {
			self::$presets['selected_service'] = $presets['selected_service'];
		}

		// preselected duration
		if ( isset( $presets['selected_duration'] ) && is_numeric( $presets['selected_duration'] ) ) {
			self::$presets['selected_duration'] = $presets['selected_duration'];
		}

		// preselected total attendees
		if ( isset( $presets['selected_total_attendees'] ) && is_numeric( $presets['selected_total_attendees'] ) ) {
			self::$presets['selected_total_attendees'] = $presets['selected_total_attendees'];
		}

		// preselected date
		if ( isset( $presets['selected_start_date'] ) && OsTimeHelper::is_valid_date( $presets['selected_start_date'] ) ) {
			self::$presets['selected_start_date'] = $presets['selected_start_date'];
		}

		// preselected time
		if ( isset( $presets['selected_start_time'] ) && is_numeric( $presets['selected_start_time'] ) ) {
			self::$presets['selected_start_time'] = $presets['selected_start_time'];
		}

		// set source id
		if ( isset( $presets['source_id'] ) ) {
			self::$presets['source_id'] = $presets['source_id'];
		}

		/**
		 * Sets presets array of a StepHelper class
		 *
		 * @param {array} $presets Array of presets set on StepHelper class
		 * @param {array} $presets Array of presets to be used to set presets on StepHelper class
		 *
		 * @returns {array} Filtered array of presets
		 * @since 5.0.0
		 * @hook latepoint_set_presets
		 *
		 */
		return apply_filters( 'latepoint_set_presets', self::$presets, $presets );
	}


	public static function set_restrictions( array $restrictions = [] ): array {
		self::set_default_restrictions();
		if ( ! empty( $restrictions ) ) {
			// filter locations
			if ( isset( $restrictions['show_locations'] ) ) {
				self::$restrictions['show_locations'] = $restrictions['show_locations'];
			}

			// filter agents
			if ( isset( $restrictions['show_agents'] ) ) {
				self::$restrictions['show_agents'] = $restrictions['show_agents'];
			}

			// filter service category
			if ( isset( $restrictions['show_service_categories'] ) ) {
				self::$restrictions['show_service_categories'] = $restrictions['show_service_categories'];
			}

			// filter services
			if ( isset( $restrictions['show_services'] ) ) {
				self::$restrictions['show_services'] = $restrictions['show_services'];
			}

			// preselected calendar start date
			if ( isset( $restrictions['calendar_start_date'] ) && OsTimeHelper::is_valid_date( $restrictions['calendar_start_date'] ) ) {
				self::$restrictions['calendar_start_date'] = $restrictions['calendar_start_date'];
			}

			// restriction in settings can override it
			if ( OsTimeHelper::is_valid_date( OsSettingsHelper::get_settings_value( 'earliest_possible_booking' ) ) ) {
				self::$restrictions['calendar_start_date'] = OsSettingsHelper::get_settings_value( 'earliest_possible_booking' );
			}


		}

		/**
		 * Sets restrictions array of a StepHelper class
		 *
		 * @param {array} $restrictions Array of restrictions set on StepHelper class
		 * @param {array} $restrictions Array of restrictions to be used to set restrictions on StepHelper class
		 *
		 * @returns {array} Filtered array of restrictions
		 * @since 5.0.0
		 * @hook latepoint_set_restrictions
		 *
		 */
		return apply_filters( 'latepoint_set_restrictions', self::$restrictions, $restrictions );
	}

	/**
	 * Sets booking object properties when a single option is available
	 *
	 * If a booking object has a service selected and only one agent is offering that service -
	 * that agent will be preselected. Same for location
	 *
	 * @return OsBookingModel
	 */
	public static function set_booking_properties_for_single_options(): OsBookingModel {

		// if only 1 location exists or assigned to selected agent - set it to this booking object
		if ( OsLocationHelper::count_locations() == 1 ) {
			self::$booking_object->location_id = OsLocationHelper::get_default_location_id();
		}
		// if only 1 agent exists - set it to this booking object
		if ( OsAgentHelper::count_agents() == 1 ) {
			self::$booking_object->agent_id = OsAgentHelper::get_default_agent_id();
		}

		return self::$booking_object;
	}

	public static function set_booking_object( $booking_object_params = [] ): OsBookingModel {
		self::$booking_object = new OsBookingModel();
		self::$booking_object->set_data( $booking_object_params );
		if ( ! empty( $booking_object_params['intent_key'] ) ) {
			self::$booking_object->intent_key = $booking_object_params['intent_key'];
		}

		// set based on presets

		// preselected service
		if ( isset( self::$presets['selected_service'] ) && is_numeric( self::$presets['selected_service'] ) ) {
			self::$booking_object->service_id = self::$presets['selected_service'];
			$service                          = new OsServiceModel( self::$booking_object->service_id );
			self::$booking_object->service    = $service;
			if ( empty( $booking_object_params['duration'] ) ) {
				self::$booking_object->duration = $service->duration;
			}
			if ( empty( $booking_object_params['total_attendees'] ) ) {
				self::$booking_object->total_attendees = $service->capacity_min;
			}
		}

		// preselected agent
		if ( ! empty( self::$presets['selected_agent'] ) && ( is_numeric( self::$presets['selected_agent'] ) || ( self::$presets['selected_agent'] == LATEPOINT_ANY_AGENT ) ) ) {
			self::$booking_object->agent_id = self::$presets['selected_agent'];
		}

		// preselected location
		if ( ! empty( self::$presets['selected_location'] ) && ( is_numeric( self::$presets['selected_location'] ) || ( self::$presets['selected_location'] == LATEPOINT_ANY_LOCATION ) ) ) {
			self::$booking_object->location_id = self::$presets['selected_location'];
		}

		// preselected duration
		if ( isset( self::$presets['selected_duration'] ) && is_numeric( self::$presets['selected_duration'] ) ) {
			self::$booking_object->duration = self::$presets['selected_duration'];
		}
		// preselected attendees
		if ( isset( self::$presets['selected_total_attendees'] ) && is_numeric( self::$presets['selected_total_attendees'] ) ) {
			self::$booking_object->total_attendees = self::$presets['selected_total_attendees'];
		}
		// preselected date
		if ( isset( self::$presets['selected_start_date'] ) && OsTimeHelper::is_valid_date( self::$presets['selected_start_date'] ) ) {
			self::$booking_object->start_date = self::$presets['selected_start_date'];
		}
		// preselected time
		if ( isset( self::$presets['selected_start_time'] ) && is_numeric( self::$presets['selected_start_time'] ) ) {
			self::$booking_object->start_time = self::$presets['selected_start_time'];
		}
		// preselected time
		if ( isset( self::$presets['order_item_id'] ) && is_numeric( self::$presets['order_item_id'] ) ) {
			self::$booking_object->order_item_id = self::$presets['order_item_id'];
			// TODO - move to pro
			// it's a bundle, preset values from a bundle
			$order_item                            = new OsOrderItemModel( self::$booking_object->order_item_id );
			$bundle                                = new OsBundleModel( $order_item->get_item_data_value_by_key( 'bundle_id' ) );
			self::$booking_object->total_attendees = $bundle->total_attendees_for_service( self::$booking_object->service_id );
			self::$booking_object->duration        = $bundle->duration_for_service( self::$booking_object->service_id );
		}


		// get buffers from service and set to booking object
		self::$booking_object->set_buffers();
		self::$booking_object->calculate_end_date_and_time();
		self::$booking_object->customer_id = OsAuthHelper::get_logged_in_customer_id();

		return self::$booking_object;
	}

	public static function load_order_object( $order_id = false ) {
		if ( $order_id ) {
			self::$order_object = new OsOrderModel( $order_id );
		} else {
			self::$order_object = new OsOrderModel();
		}
	}

	public static function is_bundle_scheduling() {
		return ! empty( self::$booking_object->order_item_id );
	}

	/**
	 * Checks if there were supposed to be some fields for this step - now they have to be carried over to next step, because this step is skipped
	 *
	 * @param string $current_step_code
	 * @param string $next_step_code
	 *
	 * @return array
	 */
	public static function carry_preset_fields_to_next_step( string $current_step_code, string $next_step_code ): void {
		if ( ! empty( self::$preset_fields[ $current_step_code ] ) ) {
			self::$preset_fields[ $next_step_code ] = array_merge( self::$preset_fields[ $next_step_code ], self::$preset_fields[ $current_step_code ] );
		}
	}

	public static function should_step_be_skipped( string $step_code ): bool {
		$skip = false;

		switch ( $step_code ) {
			case 'booking__agents':
				if ( OsAgentHelper::count_agents() == 1 ) {
					$skip = true;
				}
				if ( self::$active_cart_item->is_bundle() ) {
					$skip = true;
				}
				break;
			case 'booking__locations':
				if ( OsLocationHelper::count_locations() == 1 ) {
					$skip = true;
				}
				if ( self::$active_cart_item->is_bundle() ) {
					$skip = true;
				}
				break;
			case 'booking__datepicker':
				if ( self::$active_cart_item->is_bundle() ) {
					$skip = true;
				}
				break;
			case 'booking__services':
				if ( self::is_bundle_scheduling() ) {
					$skip = true;
				}
				break;
			case 'payment__times':
			case 'payment__portions':
			case 'payment__methods':
			case 'payment__processors':
			case 'payment__pay':
				if ( self::is_bundle_scheduling() || self::$cart_object->is_empty() || empty( OsPaymentsHelper::get_enabled_payment_times() ) ) {
					// scheduling a bundle or no enabled payment times
					$skip = true;
					self::set_zero_cost_payment_fields();
				} else {
					$original_amount      = self::$cart_object->get_subtotal();
					$after_coupons_amount = self::$cart_object->get_total();
					$deposit_amount       = self::$cart_object->deposit_amount_to_charge();
					if ( $original_amount > 0 && $after_coupons_amount <= 0 ) {
						// original price was set, but coupon was applied and charge amount is now 0, we can skip step, even if deposit is not 0
						$is_zero_cost = true;
					} else {
						if ( $after_coupons_amount <= 0 && $deposit_amount <= 0 ) {
							$is_zero_cost = true;
						} else {
							$is_zero_cost = false;
						}
					}
					// if nothing to charge - don't show it, no matter what
					if ( $is_zero_cost && ! OsSettingsHelper::is_env_demo() ) {
						$skip = true;
						self::set_zero_cost_payment_fields();
					} else {
						if ( $step_code == 'payment__times' ) {
							if ( ! empty( self::$cart_object->payment_time ) ) {
								$skip = true;
							} else {
								// try to check if one only available and preset it
								$enabled_payment_times = OsPaymentsHelper::get_enabled_payment_times();
								if ( count( $enabled_payment_times ) == 1 ) {
									$skip                                                = true;
									self::$cart_object->payment_time                     = array_key_first( $enabled_payment_times );
									self::$preset_fields['verify']['cart[payment_time]'] = OsFormHelper::hidden_field( 'cart[payment_time]', self::$cart_object->payment_time, [ 'skip_id' => true ] );
									// assign preset field value for next step
									self::$preset_fields['payment__portions']['cart[payment_time]'] = OsFormHelper::hidden_field( 'cart[payment_time]', self::$cart_object->payment_time, [ 'skip_id' => true ] );
									self::carry_preset_fields_to_next_step( 'payment__times', 'payment__portions' );
								}
							}
						}
						if ( $step_code == 'payment__portions' ) {
							if ( ! empty( self::$cart_object->payment_portion ) ) {
								$skip = true;
							} else {
								if ( $is_zero_cost || ( self::$cart_object->payment_time == LATEPOINT_PAYMENT_TIME_LATER ) || ( $after_coupons_amount > 0 && $deposit_amount <= 0 ) ) {
									// zero cost, pay later or 0 deposit, means it's a full portion payment preset
									self::$cart_object->payment_portion = LATEPOINT_PAYMENT_PORTION_FULL;
								} elseif ( $deposit_amount > 0 && $after_coupons_amount <= 0 ) {
									self::$cart_object->payment_portion = LATEPOINT_PAYMENT_PORTION_DEPOSIT;
								}

								if ( ! empty( self::$cart_object->payment_portion ) ) {
									$skip                                                             = true;
									self::$preset_fields['verify']['cart[payment_portion]']           = OsFormHelper::hidden_field( 'cart[payment_portion]', self::$cart_object->payment_portion, [ 'skip_id' => true ] );
									self::$preset_fields['payment__methods']['cart[payment_portion]'] = OsFormHelper::hidden_field( 'cart[payment_portion]', self::$cart_object->payment_portion, [ 'skip_id' => true ] );

									self::carry_preset_fields_to_next_step( 'payment__portions', 'payment__methods' );
								}
							}
						}
						if ( $step_code == 'payment__methods' ) {
							if ( ! empty( self::$cart_object->payment_method ) ) {
								$skip = true;
							} else {
								if ( self::$cart_object->payment_time ) {
									$enabled_payment_methods = OsPaymentsHelper::get_enabled_payment_methods_for_payment_time( self::$cart_object->payment_time );
									if ( count( $enabled_payment_methods ) <= 1 ) {
										$skip                                                               = true;
										self::$cart_object->payment_method                                  = array_key_first( $enabled_payment_methods );
										self::$preset_fields['verify']['cart[payment_method]']              = OsFormHelper::hidden_field( 'cart[payment_method]', self::$cart_object->payment_method, [ 'skip_id' => true ] );
										self::$preset_fields['payment__processors']['cart[payment_method]'] = OsFormHelper::hidden_field( 'cart[payment_method]', self::$cart_object->payment_method, [ 'skip_id' => true ] );

										self::carry_preset_fields_to_next_step( 'payment__methods', 'payment__processors' );
									}
								}
							}
						}
						if ( $step_code == 'payment__processors' ) {
							if ( ! empty( self::$cart_object->payment_processor ) ) {
								$skip = true;
							} else {
								if ( self::$cart_object->payment_time && self::$cart_object->payment_method ) {
									$enabled_payment_processors = OsPaymentsHelper::get_enabled_payment_processors_for_payment_time_and_method( self::$cart_object->payment_time, self::$cart_object->payment_method );
									if ( count( $enabled_payment_processors ) <= 1 ) {
										$skip                                                           = true;
										self::$cart_object->payment_processor                           = array_key_first( $enabled_payment_processors );
										self::$preset_fields['verify']['cart[payment_processor]']       = OsFormHelper::hidden_field( 'cart[payment_processor]', self::$cart_object->payment_processor, [ 'skip_id' => true ] );
										self::$preset_fields['payment__pay']['cart[payment_processor]'] = OsFormHelper::hidden_field( 'cart[payment_processor]', self::$cart_object->payment_processor, [ 'skip_id' => true ] );

										self::carry_preset_fields_to_next_step( 'payment__processors', 'payment__pay' );
									}
								}
							}
						}
						if ( $step_code == 'payment__pay' ) {
							if ( self::$cart_object->payment_time == LATEPOINT_PAYMENT_TIME_LATER || empty( OsPaymentsHelper::get_enabled_payment_times() ) ) {
								$skip = true;
							}
						}
					}
				}
				break;
		}

		$skip = apply_filters( 'latepoint_should_step_be_skipped', $skip, $step_code, self::$cart_object, self::$active_cart_item, self::$booking_object );

		return $skip;
	}

	public static function set_zero_cost_payment_fields() {
		self::$preset_fields['verify']['cart[payment_time]']      = OsFormHelper::hidden_field( 'cart[payment_time]', LATEPOINT_PAYMENT_TIME_LATER, [ 'skip_id' => true ] );
		self::$preset_fields['verify']['cart[payment_method]']    = OsFormHelper::hidden_field( 'cart[payment_method]', 'other', [ 'skip_id' => true ] );
		self::$preset_fields['verify']['cart[payment_processor]'] = OsFormHelper::hidden_field( 'cart[payment_processor]', 'other', [ 'skip_id' => true ] );
		self::$preset_fields['verify']['cart[payment_portion]']   = OsFormHelper::hidden_field( 'cart[payment_portion]', LATEPOINT_PAYMENT_PORTION_FULL, [ 'skip_id' => true ] );
	}

	public static function output_preset_fields( string $step_code ) {
		if ( ! empty( self::$preset_fields[ $step_code ] ) ) {
			foreach ( self::$preset_fields[ $step_code ] as $preset_field_html ) {
				echo $preset_field_html;
			}
		}
	}

	public static function get_next_step_code( $current_step_code ) {
		$all_step_codes     = self::get_step_codes_in_order( true );
		$active_step_codes  = self::get_step_codes_in_order();
		$current_step_index = array_search( $current_step_code, $all_step_codes );
		if ( $current_step_index === false || ( ( $current_step_index + 1 ) == count( $all_step_codes ) ) ) {
			// no more steps or not found
			return false;
		}
		$next_step_code = $all_step_codes[ $current_step_index + 1 ];

		if ( ! in_array( $next_step_code, $active_step_codes ) ) {
			// if is skipped - get next step in order and try again
			$next_step_code = self::get_next_step_code( $next_step_code );
		}

		return $next_step_code;
	}

	public static function get_prev_step_code( $current_step_code ) {
		$all_step_codes     = self::get_step_codes_in_order( true );
		$current_step_index = array_search( $current_step_code, $all_step_codes );

		if ( ! $current_step_index ) {
			// first step or not found - return the same code
			return $current_step_code;
		}
		$prev_step_code = $all_step_codes[ $current_step_code - 1 ];
		if ( self::should_step_be_skipped( $prev_step_code ) ) {
			// if skipped - get previous in order and try again
			$prev_step_code = self::get_prev_step_code( $prev_step_code );
		}

		return $prev_step_code;
	}


	public static function is_first_step( $step_code ) {
		$step_index = array_search( $step_code, self::get_step_codes_in_order() );

		return $step_index == 0;
	}

	public static function is_last_step( $step_code ) {
		$step_index = array_search( $step_code, self::get_step_codes_in_order() );

		return ( ( $step_index + 1 ) == count( self::get_step_codes_in_order() ) );
	}

	public static function is_pre_last_step( $step_code ) {
		$next_step_code = self::get_next_step_code( $step_code );
		$step_index     = array_search( $next_step_code, self::get_step_codes_in_order() );

		return ( ( $step_index + 1 ) == count( self::get_step_codes_in_order() ) );
	}

	public static function can_step_show_prev_btn( $step_code ) {
		$step_index = array_search( $step_code, self::get_step_codes_in_order() );
		// if first or last step
		if ( $step_index == 0 || ( ( $step_index + 1 ) == count( self::get_step_codes_in_order() ) ) ) {
			return false;
		} else {
			return true;
		}
	}

	public static function get_next_btn_label_for_step( $step_code ) {
		$label         = __( 'Next', 'latepoint' );
		$custom_labels = [
			'payment__pay' => __( 'Submit', 'latepoint' ),
			'verify'       => OsStepsHelper::should_step_be_skipped( 'payment__pay' ) ? __( 'Submit', 'latepoint' ) : __( 'Checkout', 'latepoint' )
		];


		/**
		 * Returns an array of custom labels for "next" button with step codes as keys
		 *
		 * @param {array} $custom_labels Current array of labels for "next" button
		 *
		 * @returns {array} Filtered array of labels for "next" button
		 * @since 4.7.0
		 * @hook latepoint_next_btn_labels_for_steps
		 *
		 */
		$custom_labels = apply_filters( 'latepoint_next_btn_labels_for_steps', $custom_labels );
		if ( ! empty( $custom_labels[ $step_code ] ) ) {
			$label = $custom_labels[ $step_code ];
		}

		return $label;
	}

	public static function can_step_show_next_btn( $step_code ) {
		$step_show_btn_rules = [
			'booking__services'   => false,
			'booking__agents'     => false,
			'booking__datepicker' => false,
			'customer'            => true,
			'payment__times'      => false,
			'payment__portions'   => false,
			'payment__methods'    => false,
			'payment__pay'        => false,
			'verify'              => true,
			'confirmation'        => false
		];

		/**
		 * Returns an array of rules of whether to show a next button on not, step codes are keys in this array
		 *
		 * @param {array} $step_show_btn_rules Current array of labels for "next" button
		 * @param {string} $step_code Current array of labels for "next" button
		 *
		 * @returns {array} Filtered array of labels for "next" button
		 * @since 4.7.0
		 * @hook latepoint_step_show_next_btn_rules
		 *
		 */
		$step_show_btn_rules = apply_filters( 'latepoint_step_show_next_btn_rules', $step_show_btn_rules, $step_code );

		return $step_show_btn_rules[ $step_code ] ?? false;
	}

	/**
	 * @throws Exception
	 */
	public static function add_current_item_to_cart() {
		if ( self::$active_cart_item->is_new_record() ) {
			if ( self::$active_cart_item->is_bundle() ) {
				self::$cart_object->add_item( self::$active_cart_item );
				self::$fields_to_update['active_cart_item[id]'] = self::$active_cart_item->id;
			} elseif ( self::$active_cart_item->is_booking() ) {
				// only do this for new cart item, if modifying existing one - then the set_active_cart_item method will take care of updating it
				if ( self::$booking_object->is_bookable( true, true ) ) {
					// set it again as booking object might have changed if agent or location were set to ANY, they are assigned now
					self::set_active_cart_item_object();
					if ( self::is_bundle_scheduling() ) {
						// we don't need to use a cart for bundle scheduling
					} else {
						self::$cart_object->add_item( self::$active_cart_item );
						self::$fields_to_update['active_cart_item[id]'] = self::$active_cart_item->id;
					}
					self::reset_booking_object();

					return true;
				} else {
					throw new Exception( implode( ',', self::$booking_object->get_error_messages() ) );
				}
			}
		}
	}

	public static function process_step_booking() {

		if ( ! self::is_bundle_scheduling() ) {
			// check if we are processing the last step of a booking sequence
			$booking_steps = [];
			foreach ( self::$step_codes_in_order as $step_code ) {
				if ( strpos( $step_code, 'booking__' ) !== false ) {
					$booking_steps[] = $step_code;
				}
			}
			if ( end( $booking_steps ) == self::$step_to_process ) {
				try {
					self::add_current_item_to_cart();
				} catch ( Exception $e ) {
					return new WP_Error( 'booking_slot_not_available', $e->getMessage() );
				}
			}
		}


	}

	public static function reset_booking_object() {
		self::set_booking_object( [] );
	}

	public static function prepare_step_booking() {

	}


	// SERVICES

	public static function process_step_booking__services() {
	}

	public static function prepare_step_booking__services() {
		$bundles_model = new OsBundleModel();
		$bundles       = $bundles_model->should_be_active()->should_not_be_hidden()->get_results_as_models();

		$services_model              = new OsServiceModel();
		$show_selected_services_arr  = self::$restrictions['show_services'] ? explode( ',', self::$restrictions['show_services'] ) : false;
		$show_service_categories_arr = self::$restrictions['show_service_categories'] ? explode( ',', self::$restrictions['show_service_categories'] ) : false;
		$preselected_category        = self::$presets['selected_service_category'];
		$preselected_duration        = self::$presets['selected_duration'];
		$preselected_total_attendees = self::$presets['selected_total_attendees'];

		$connected_ids = OsConnectorHelper::get_connected_object_ids( 'service_id', [
			'agent_id'    => self::$booking_object->agent_id,
			'location_id' => self::$booking_object->location_id
		] );
		// if "show only specific services" is selected (restrictions) - remove ids that are not found in connection
		$show_services_arr = ( ! empty( $show_selected_services_arr ) && ! empty( $connected_ids ) ) ? array_intersect( $connected_ids, $show_selected_services_arr ) : $connected_ids;
		if ( ! empty( $show_services_arr ) ) {
			$services_model->where_in( 'id', $show_services_arr );
		}

		$services = $services_model->should_be_active()->should_not_be_hidden()->order_by( 'order_number asc' )->get_results_as_models();

		self::$vars_for_view['show_services_arr']           = $show_services_arr;
		self::$vars_for_view['show_service_categories_arr'] = $show_service_categories_arr;
		self::$vars_for_view['preselected_category']        = $preselected_category;
		self::$vars_for_view['preselected_duration']        = $preselected_duration;
		self::$vars_for_view['preselected_total_attendees'] = $preselected_total_attendees;
		self::$vars_for_view['services']                    = $services;
		self::$vars_for_view['bundles']                     = $bundles;
	}

	// AGENTS

	public static function process_step_booking__agents() {
	}

	public static function prepare_step_booking__agents() {
		$agents_model = new OsAgentModel();

		$show_selected_agents_arr = ( self::$restrictions['show_agents'] ) ? explode( ',', self::$restrictions['show_agents'] ) : false;
		// Find agents that actually offer selected service (if selected) at selected location (if selected)
		$connected_ids = OsConnectorHelper::get_connected_object_ids( 'agent_id', [
			'service_id'  => self::$booking_object->service_id,
			'location_id' => self::$booking_object->location_id
		] );

		// If date/time is selected - filter agents who are available at that time
		if ( self::$booking_object->start_date && self::$booking_object->start_time ) {
			$available_agent_ids = [];
			$booking_request     = \LatePoint\Misc\BookingRequest::create_from_booking_model( self::$booking_object );
			foreach ( $connected_ids as $agent_id ) {
				$booking_request->agent_id = $agent_id;
				if ( OsBookingHelper::is_booking_request_available( $booking_request ) ) {
					$available_agent_ids[] = $agent_id;
				}
			}
			$connected_ids = array_intersect( $available_agent_ids, $connected_ids );
		}


		// if show only specific agents are selected (restrictions) - remove ids that are not found in connection
		$show_agents_arr = ( $show_selected_agents_arr ) ? array_intersect( $connected_ids, $show_selected_agents_arr ) : $connected_ids;
		if ( ! empty( $show_agents_arr ) ) {
			$agents_model->where_in( 'id', $show_agents_arr );
			$agents                        = $agents_model->should_be_active()->get_results_as_models();
			self::$vars_for_view['agents'] = $agents;
		} else {
			// no available or connected agents
			self::$vars_for_view['agents'] = [];
		}
	}


	// DATEPICKER

	public static function prepare_step_booking__datepicker() {
		if ( empty( self::$booking_object->agent_id ) ) {
			self::$booking_object->agent_id = LATEPOINT_ANY_AGENT;
		}
		self::$vars_for_view['calendar_start_date'] = self::$restrictions['calendar_start_date'] ? self::$restrictions['calendar_start_date'] : 'today';
		self::$vars_for_view['timeshift_minutes']   = OsTimeHelper::get_timezone_shift_in_minutes_from_session();
		self::$vars_for_view['timezone_name']       = OsTimeHelper::get_timezone_name_from_session();
	}

	public static function process_step_booking__datepicker() {
	}


	// CONTACT


	public static function prepare_step_customer() {

		if ( OsAuthHelper::is_customer_logged_in() ) {
			self::$booking_object->customer    = OsAuthHelper::get_logged_in_customer();
			self::$booking_object->customer_id = self::$booking_object->customer->id;
		} else {
			self::$booking_object->customer = new OsCustomerModel();
		}

		self::$vars_for_view['default_fields_for_customer'] = OsSettingsHelper::get_default_fields_for_customer();
		self::$vars_for_view['customer']                    = self::$booking_object->customer;
	}

	private static function customer_params(): array {
		$params = OsParamsHelper::get_param( 'customer' );
		if ( empty( $params ) ) {
			return [];
		}

		$customer_params = OsParamsHelper::permit_params( $params, [
			'first_name',
			'last_name',
			'email',
			'phone',
			'notes',
			'password',
			'password_confirmation'
		] );

		if ( ! empty( $customer_params['first_name'] ) ) {
			$customer_params['first_name'] = sanitize_text_field( $customer_params['first_name'] );
		}
		if ( ! empty( $customer_params['last_name'] ) ) {
			$customer_params['last_name'] = sanitize_text_field( $customer_params['last_name'] );
		}
		if ( ! empty( $customer_params['email'] ) ) {
			$customer_params['email'] = sanitize_email( $customer_params['email'] );
		}
		if ( ! empty( $customer_params['phone'] ) ) {
			$customer_params['phone'] = sanitize_text_field( $customer_params['phone'] );
		}
		if ( ! empty( $customer_params['notes'] ) ) {
			$customer_params['notes'] = sanitize_textarea_field( $customer_params['notes'] );
		}

		/**
		 * Filtered customer params for steps
		 *
		 * @param {array} $customer_params a filtered array of customer params
		 * @param {array} $params unfiltered 'customer' params
		 * @returns {array} $customer_params a filtered array of customer params
		 *
		 * @since 5.0.14
		 * @hook latepoint_customer_params_on_steps
		 *
		 */
		return apply_filters( 'latepoint_customer_params_on_steps', $customer_params, $params );
	}

	public static function process_step_customer() {
		$status = LATEPOINT_STATUS_SUCCESS;

		$customer_params = self::customer_params();

		$logged_in_customer = OsAuthHelper::get_logged_in_customer();


		if ( $logged_in_customer ) {
			// LOGGED IN ALREADY
			// Check if they are changing the email on file
			if ( $logged_in_customer->email != $customer_params['email'] ) {
				// Check if other customer already has this email
				$customer                  = new OsCustomerModel();
				$customer_with_email_exist = $customer->where( array(
					'email' => $customer_params['email'],
					'id !=' => $logged_in_customer->id
				) )->set_limit( 1 )->get_results_as_models();
				// check if another customer (or if wp user login enabled - another wp user) exists with the email that this user tries to update to
				if ( $customer_with_email_exist || ( OsAuthHelper::wp_users_as_customers() && email_exists( $customer_params['email'] ) ) ) {
					$status        = LATEPOINT_STATUS_ERROR;
					$response_html = __( 'Another customer is registered with this email.', 'latepoint' );
				}
			}
		} else {
			// NEW REGISTRATION (NOT LOGGED IN)
			if ( OsAuthHelper::wp_users_as_customers() ) {
				// WP USERS AS CUSTOMERS
				if ( email_exists( $customer_params['email'] ) ) {
					// wordpress user with this email already exists, ask to login
					$status        = LATEPOINT_STATUS_ERROR;
					$response_html = __( 'An account with that email address already exists. Please try signing in.', 'latepoint' );
				} else {
					// wp user does not exist - search for latepoint customer
					$customer = new OsCustomerModel();
					$customer = $customer->where( array( 'email' => $customer_params['email'] ) )->set_limit( 1 )->get_results_as_models();
					if ( $customer ) {
						// latepoint customer with this email exits, create wp user for them
						$wp_user       = OsCustomerHelper::create_wp_user_for_customer( $customer );
						$status        = LATEPOINT_STATUS_ERROR;
						$response_html = __( 'An account with that email address already exists. Please try signing in.', 'latepoint' );
					} else {
						// no latepoint customer or wp user with this email found, can proceed
					}
				}
			} else {
				// LATEPOINT CUSTOMERS
				$customer       = new OsCustomerModel();
				$customer_exist = $customer->where( array( 'email' => $customer_params['email'] ) )->set_limit( 1 )->get_results_as_models();
				if ( $customer_exist ) {
					// customer with this email exists - check if current customer was registered as a guest
					if ( OsSettingsHelper::is_on( 'steps_hide_login_register_tabs' ) || ( $customer_exist->can_login_without_password() && ! OsSettingsHelper::is_on( 'steps_require_setting_password' ) ) ) {
						// guest account, login automatically
						$status == LATEPOINT_STATUS_SUCCESS;
						OsAuthHelper::authorize_customer( $customer_exist->id );
					} else {
						// Not a guest account, ask to login
						$status        = LATEPOINT_STATUS_ERROR;
						$response_html = __( 'An account with that email address already exists. Please try signing in.', 'latepoint' );
					}
				} else {
					// no latepoint customer with this email found, can proceed
				}
			}
			// if not logged in - check if password has to be set
			if ( ! OsAuthHelper::is_customer_logged_in() && OsSettingsHelper::is_on( 'steps_require_setting_password' ) ) {
				if ( ! empty( $customer_params['password'] ) && $customer_params['password'] == $customer_params['password_confirmation'] ) {
					$customer_params['password'] = OsAuthHelper::hash_password( $customer_params['password'] );
					$customer_params['is_guest'] = false;
				} else {
					// Password is blank or does not match the confirmation
					$status        = LATEPOINT_STATUS_ERROR;
					$response_html = __( 'Setting password is required and should match password confirmation', 'latepoint' );
				}
			}
		}
		// If no errors, proceed
		if ( $status == LATEPOINT_STATUS_SUCCESS ) {
			if ( OsAuthHelper::is_customer_logged_in() ) {
				$customer        = OsAuthHelper::get_logged_in_customer();
				$is_new_customer = $customer->is_new_record();
			} else {
				$customer        = new OsCustomerModel();
				$is_new_customer = true;
			}
			$old_customer_data = $is_new_customer ? [] : $customer->get_data_vars();
			$customer->set_data( $customer_params, LATEPOINT_PARAMS_SCOPE_PUBLIC );
			if ( $customer->save() ) {
				if ( $is_new_customer ) {
					do_action( 'latepoint_customer_created', $customer );
				} else {
					do_action( 'latepoint_customer_updated', $customer, $old_customer_data );
				}

				self::$booking_object->customer_id = $customer->id;
				if ( ! OsAuthHelper::is_customer_logged_in() ) {
					OsAuthHelper::authorize_customer( $customer->id );
				}
				$customer->set_timezone_name();
			} else {
				$status        = LATEPOINT_STATUS_ERROR;
				$response_html = $customer->get_error_messages();
				if ( is_array( $response_html ) ) {
					$response_html = implode( ', ', $response_html );
				}
			}
		}
		if ( $status == LATEPOINT_STATUS_ERROR ) {
			return new WP_Error( LATEPOINT_STATUS_ERROR, $response_html );
		}

	}


	// VERIFICATION STEP

	public static function process_step_verify() {

	}

	public static function prepare_step_verify() {
		$cart = OsCartsHelper::get_or_create_cart();

		$cart->set_singular_payment_attributes();

		self::$vars_for_view['cart']                        = $cart;
		self::$vars_for_view['customer']                    = OsAuthHelper::get_logged_in_customer();
		self::$vars_for_view['default_fields_for_customer'] = OsSettingsHelper::get_default_fields_for_customer();
	}

	// PAYMENT

	public static function process_step_payment__portions() {
	}

	public static function prepare_step_payment__portions() {
	}

	public static function process_step_payment__times() {
	}

	public static function prepare_step_payment__times() {
		$enabled_payment_times = OsPaymentsHelper::get_enabled_payment_times();

		self::$vars_for_view['enabled_payment_times'] = $enabled_payment_times;
	}

	public static function process_step_payment__methods() {
	}

	public static function prepare_step_payment__methods() {
		$enabled_payment_methods                        = OsPaymentsHelper::get_enabled_payment_methods_for_payment_time( self::$cart_object->payment_time );
		self::$vars_for_view['enabled_payment_methods'] = $enabled_payment_methods;
	}

	public static function process_step_payment__processors() {
	}

	public static function prepare_step_payment__processors() {
		$enabled_payment_processors = OsPaymentsHelper::get_enabled_payment_processors();
		$enabled_payment_methods    = OsPaymentsHelper::get_enabled_payment_methods( false );

		foreach ( $enabled_payment_methods as $payment_method_code => $payment_method ) {
			if ( $payment_method_code != self::$cart_object->payment_method ) {
				continue;
			}
		}
		self::$vars_for_view['enabled_payment_processors'] = $enabled_payment_processors;
	}

	public static function process_step_payment__pay() {
	}

	public static function prepare_step_payment__pay() {
	}


	// CONFIRMATION

	public static function process_step_confirmation() {
	}

	public static function prepare_step_confirmation() {
		self::$vars_for_view['customer']                    = OsAuthHelper::get_logged_in_customer();
		self::$vars_for_view['default_fields_for_customer'] = OsSettingsHelper::get_default_fields_for_customer();
		if ( ! self::$order_object->is_new_record() ) {
			self::$vars_for_view['order']                = self::$order_object;
			self::$vars_for_view['order_bookings']       = self::$order_object->get_bookings_from_order_items();
			self::$vars_for_view['order_bundles']        = self::$order_object->get_bundles_from_order_items();
			self::$vars_for_view['price_breakdown_rows'] = self::$order_object->generate_price_breakdown_rows();
			self::$vars_for_view['is_bundle_scheduling'] = false;
		} else {
			// TRY SAVING BOOKING
			// check if it's a scheduling request for an existing order item, it means its a bundle
			$is_bundle_scheduling                        = self::is_bundle_scheduling();
			self::$vars_for_view['is_bundle_scheduling'] = $is_bundle_scheduling;
			if ( $is_bundle_scheduling ) {
				$order_item                                  = new OsOrderItemModel( self::$booking_object->order_item_id );
				$order                                       = new OsOrderModel( $order_item->order_id );
				self::$vars_for_view['order']                = $order;
				self::$vars_for_view['order_bookings']       = $order->get_bookings_from_order_items();
				self::$vars_for_view['order_bundles']        = $order->get_bundles_from_order_items();
				self::$vars_for_view['price_breakdown_rows'] = self::$cart_object->generate_price_breakdown_rows();

				if ( self::$booking_object->is_bookable() ) {
					self::$booking_object->calculate_end_time();
					self::$booking_object->calculate_end_date();
					self::$booking_object->set_utc_datetimes();
					$service                             = new OsServiceModel( self::$booking_object->service_id );
					self::$booking_object->buffer_before = $service->buffer_before;
					self::$booking_object->buffer_after  = $service->buffer_after;

					if ( self::$booking_object->save() ) {
					} else {
						// error saving booking
						self::$booking_object->add_error( 'booking_error', self::$booking_object->get_error_messages() );
					}
				} else {
					// is not bookable
					self::$booking_object->add_error( 'booking_error', self::$booking_object->get_error_messages() );
				}

			} else {
				$order_intent = OsOrderIntentHelper::create_or_update_order_intent( self::$cart_object, self::$restrictions, self::$presets );
				if ( $order_intent->is_processing() ) {
					return new WP_Error( LATEPOINT_STATUS_ERROR, __( 'Processing...', 'latepoint' ), [ 'send_to_step' => 'resubmit' ] );
				}
				if ( $order_intent->convert_to_order() ) {
					$order = new OsOrderModel( $order_intent->order_id );
					self::$cart_object->clear();
					self::$vars_for_view['order']                = $order;
					self::$vars_for_view['order_bookings']       = $order->get_bookings_from_order_items();
					self::$vars_for_view['order_bundles']        = $order->get_bundles_from_order_items();
					self::$vars_for_view['price_breakdown_rows'] = $order->generate_price_breakdown_rows();
				} else {
					// ERROR CONVERTING TO ORDER
					OsDebugHelper::log( 'Error saving order', 'order_error', $order_intent->get_error_messages() );
					$response_html = $order_intent->get_error_messages();
					$error_data    = ( $order_intent->get_error_data( 'send_to_step' ) ) ? [ 'send_to_step' => $order_intent->get_error_data( 'send_to_step' ) ] : '';

					return new WP_Error( LATEPOINT_STATUS_ERROR, $response_html, $error_data );
				}
			}
		}
	}

	public static function output_list_option( $option ) {
		$html = '';
		$html .= '<div tabindex="0" class="lp-option ' . esc_attr( $option['css_class'] ) . '" ' . $option['attrs'] . '>';
		$html .= '<div class="lp-option-image-w"><div class="lp-option-image" style="background-image: url(' . esc_url( $option['image_url'] ) . ')"></div></div>';
		$html .= '<div class="lp-option-label">' . esc_html( $option['label'] ) . '</div>';
		$html .= '</div>';

		return $html;
	}

	public static function get_steps_for_select(): array {
		$steps             = self::get_step_codes_in_order();
		$steps_with_labels = [];
		foreach ( $steps as $step_code ) {
			$steps_with_labels[ $step_code ] = self::get_step_label_by_code( $step_code );
		}

		return $steps_with_labels;
	}


	public static function save_step_codes_in_order( array $step_codes_in_order ): bool {
		return OsSettingsHelper::save_setting_by_name( 'step_codes_in_order', implode( ',', $step_codes_in_order ) );
	}


	public static function save_steps_settings( $steps_settings ): bool {
		self::$steps_settings = $steps_settings;

		return OsSettingsHelper::save_setting_by_name( 'steps_settings', self::$steps_settings );
	}


	public static function get_step_settings( string $step_code ): array {
		$settings = self::get_steps_settings();

		return $settings[ $step_code ] ?? [];
	}

	public static function get_steps_settings(): array {
		if ( ! empty( self::$steps_settings ) ) {
			return self::$steps_settings;
		}

		$steps_settings_from_db = OsSettingsHelper::get_settings_value( 'steps_settings', [] );
		$step_codes             = self::get_step_codes_in_order();


		if ( empty( $steps_settings_from_db ) ) {
			$steps_settings = [
				'shared' => [
					'steps_support_text' => '<h5>Questions?</h5><p>Call (858) 939-3746 for help</p>'
				]
			];
			foreach ( $step_codes as $step_code ) {
				$steps_settings[ $step_code ] = self::get_default_value_for_step_settings( $step_code );
			}
			OsSettingsHelper::save_setting_by_name( 'steps_settings', $steps_settings );
			self::$steps_settings = $steps_settings;
		} else {
			// iterate step codes to see if each has a setting
			$changed = false;
			foreach ( $step_codes as $step_code ) {
				if ( ! isset( $steps_settings_from_db[ $step_code ] ) ) {
					$steps_settings_from_db[ $step_code ] = self::get_default_value_for_step_settings( $step_code );
					$changed                              = true;
				}
			}
			if ( $changed ) {
				OsSettingsHelper::save_setting_by_name( 'steps_settings', $steps_settings_from_db );
			}
			self::$steps_settings = $steps_settings_from_db;
		}

		return self::$steps_settings;
	}

	/**
	 * @param string $step_code
	 * @param string $placement before, after
	 *
	 * @return string
	 */
	public static function get_formatted_extra_step_content( string $step_code, string $placement ): string {
		$content = self::get_step_setting_value( $step_code, 'main_panel_content_' . $placement );

		return ! empty( $content ) ? '<div class="latepoint-step-content-text-left">' . $content . '</div>' : '';
	}


	public static function get_step_setting_value( string $step_code, string $setting_key, $default = '' ) {
		$steps_settings = self::get_step_settings( $step_code );

		return $steps_settings[ $setting_key ] ?? $default;
	}

	public static function get_step_settings_edit_form_html( string $selected_step_code ): string {
		$step_settings_html = '';
		switch ( $selected_step_code ) {
			case 'booking__services';
				$step_settings_html .= OsFormHelper::toggler_field( 'settings[steps_show_service_categories]', __( 'Show service categories', 'latepoint' ), OsSettingsHelper::steps_show_service_categories(), false, false, [ 'sub_label' => __( 'If turned on, services will be displayed in categories', 'latepoint' ) ] );
				break;
			case 'booking__agents':
				$step_settings_html .= OsFormHelper::toggler_field( 'settings[steps_show_agent_bio]', __( 'Show Learn More about agents', 'latepoint' ), OsSettingsHelper::is_on( 'steps_show_agent_bio' ), false, false, [ 'sub_label' => __( 'A link to open information about agent will be added to each agent tile', 'latepoint' ) ] );
				$step_settings_html .= OsFormHelper::toggler_field( 'settings[steps_hide_agent_info]', __( 'Hide agent name from summary and confirmation', 'latepoint' ), OsSettingsHelper::is_on( 'steps_hide_agent_info' ), false, false, [ 'sub_label' => __( 'Check if you want to hide agent name from showing up', 'latepoint' ) ] );
				$step_settings_html .= OsFormHelper::toggler_field( 'settings[allow_any_agent]', __( 'Add "Any Agent" option to agent selection', 'latepoint' ), OsSettingsHelper::is_on( 'allow_any_agent' ), 'lp-any-agent-settings', false, [ 'sub_label' => __( 'Customers can pick "Any agent" and system will find a matching agent', 'latepoint' ) ] );
				$step_settings_html .= '<div class="control-under-toggler" id="lp-any-agent-settings" ' . ( OsSettingsHelper::is_on( 'allow_any_agent' ) ? '' : 'style="display: none;"' ) . '>';
				$step_settings_html .= OsFormHelper::select_field( 'settings[any_agent_order]', __( 'If "Any Agent" is selected then assign booking to', 'latepoint' ), OsSettingsHelper::get_order_types_list_for_any_agent_logic(), OsSettingsHelper::get_any_agent_order() );
				$step_settings_html .= '</div>';
				break;
			case 'booking__datepicker';
				$step_settings_html .= OsFormHelper::select_field( 'steps_settings[booking__datepicker][time_pick_style]', __( 'Show Time Slots as', 'latepoint' ), [
					'timebox'  => 'Time Boxes',
					'timeline' => 'Timeline'
				], OsStepsHelper::get_time_pick_style() );
				$step_settings_html .= OsFormHelper::toggler_field( 'steps_settings[booking__datepicker][hide_timepicker_when_one_slot_available]', __( 'Hide time picker if single slot', 'latepoint' ), OsUtilHelper::is_on( self::get_step_setting_value( $selected_step_code, 'hide_timepicker_when_one_slot_available' ) ), false, false, [ 'sub_label' => __( 'If a single slot is available in a day, it will be preselected.', 'latepoint' ) ] );
				$step_settings_html .= OsFormHelper::toggler_field( 'steps_settings[booking__datepicker][hide_slot_availability_count]', __( 'Hide slot availability count', 'latepoint' ), OsStepsHelper::hide_slot_availability_count(), false, false, [ 'sub_label' => __( 'Slot counter tooltip will not appear when hovering a day.', 'latepoint' ) ] );
				$step_settings_html .= OsFormHelper::toggler_field( 'steps_settings[booking__datepicker][hide_unavailable_slots]', __( 'Hide slots that are not available (time boxes)', 'latepoint' ), OsStepsHelper::hide_unavailable_slots(), false, false, [ 'sub_label' => __( 'Hides time boxes that are not available, instead of showing them in gray.', 'latepoint' ) ] );
				break;
		}
		/**
		 * Generates HTML for step settings form in the preview
		 *
		 * @param {string} $step_settings_html html that is going to be output on the step settings form
		 * @param {string} $selected_step_code step code that settings are requested for
		 * @returns {string} $step_settings_html Filtered HTML of the settings form
		 *
		 * @since 5.0.0
		 * @hook latepoint_get_step_settings_edit_form_html
		 *
		 */
		$step_settings_html = apply_filters( 'latepoint_get_step_settings_edit_form_html', $step_settings_html, $selected_step_code );
		if ( empty( $step_settings_html ) ) {
			$step_settings_html = '<div class="bf-step-no-settings-message">' . __( 'This step does not have any specific settings. You can use the selector above to check another step.', 'latepoint' ) . '</div>';
		}

		return $step_settings_html;
	}

	public static function get_default_value_for_step_settings( string $step_code ): array {
		$settings = [
			'booking__services'   => [
				'side_panel_heading'     => 'Service Selection',
				'side_panel_description' => 'Please select a service for which you want to schedule an appointment',
				'main_panel_heading'     => 'Available Services'
			],
			'booking__locations'  => [
				'side_panel_heading'     => 'Location Selection',
				'side_panel_description' => 'Please select a location where you want to schedule an appointment',
				'main_panel_heading'     => 'Available Locations'
			],
			'booking__agents'     => [
				'side_panel_heading'     => 'Agent Selection',
				'side_panel_description' => 'Please select an agent that will be providing you a service',
				'main_panel_heading'     => 'Available Agents'
			],
			'booking__datepicker' => [
				'side_panel_heading'     => 'Select Date & Time',
				'side_panel_description' => 'Please select date and time for your appointment',
				'main_panel_heading'     => 'Date & Time Selection'
			],
			'customer'            => [
				'side_panel_heading'     => 'Enter Your Information',
				'side_panel_description' => 'Please enter your contact information',
				'main_panel_heading'     => 'Customer Information'
			],
			'verify'              => [
				'side_panel_heading'     => 'Verify Order Details',
				'side_panel_description' => 'Double check your reservation details and click submit button if everything is correct',
				'main_panel_heading'     => 'Verify Order Details',
			],
			'payment__times'      => [
				'side_panel_heading'     => 'Payment Time Selection',
				'side_panel_description' => 'Please choose when you would like to pay for your appointment',
				'main_panel_heading'     => 'When would you like to pay?'
			],
			'payment__portions'   => [
				'side_panel_heading'     => 'Payment Portion Selection',
				'side_panel_description' => 'Please select how much you would like to pay now',
				'main_panel_heading'     => 'How much would you like to pay now?'
			],
			'payment__methods'    => [
				'side_panel_heading'     => 'Payment Method Selection',
				'side_panel_description' => 'Please select a payment method you would like to make a payment with',
				'main_panel_heading'     => 'Select payment method'
			],
			'payment__processors' => [
				'side_panel_heading'     => 'Payment Processor Selection',
				'side_panel_description' => 'Please select a payment processor you want to process the payment with',
				'main_panel_heading'     => 'Select payment processor'
			],
			'payment__pay'        => [
				'side_panel_heading'     => 'Make a Payment',
				'side_panel_description' => 'Please enter your payment information so we can process the payment',
				'main_panel_heading'     => 'Enter your payment information'
			],
			'confirmation'        => [
				'side_panel_heading'     => 'Confirmation',
				'side_panel_description' => 'Your order has been placed. Please retain this confirmation for your record.',
				'main_panel_heading'     => 'Order Confirmation'
			]
		];


		$settings = apply_filters( 'latepoint_settings_for_step_codes', $settings );

		return $settings[ $step_code ] ?? [];
	}


	public static function get_default_side_panel_image_html_for_step_code( string $step_code ): string {
		$svg = '';
		switch ( $step_code ) {
			case 'booking__locations':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73 73">
					<path class="latepoint-step-svg-highlight" d="M60.3884583,4.85921c-2.8716431-0.2993164-5.8259277,0.557373-7.9927979,2.197998 c-1.0095825,0.6467285-1.8696899,1.4177246-2.4382935,2.2561035c-1.7146873,2.5291042-2.5220757,6.3280535-1.3348999,10.835206 c-5.2646828-1.1404552-4.7828903-1.0880737-4.9659424-1.052002l-2.1259766,0.4560547 c-18.4231091,3.9559402-16.4117718,3.5059223-16.6292133,3.5698242 C4.8973494,18.9566498,6.1634111,19.1396389,5.8543382,19.2293282c0.0001221-0.0048828,0.0001221-0.0097656,0.0002441-0.0146484 c-0.0184326,0.012207-0.0371094,0.0292969-0.055603,0.0419922c-0.2596664,0.100153-0.2317972,0.1285801-0.3178711,0.2409668 c-0.388855,0.3278809-0.7800293,0.7553711-1.1567383,1.2041016c-0.3962412,0.4718437-0.1706734-1.9064941,0.5690308,41.3483887 c0.0057373,0.3037109,0.1334229,0.597168,0.3482666,0.8115234c0.3456421,0.3449707,0.5272217,0.5529785,0.7957764,0.7592773 c0.0950928,0.2109375,0.2803345,0.3754883,0.5170288,0.4277306c20.0937347,4.4312515,18.6302357,4.2767105,19.0541992,3.9326172 c0.0049438-0.0039063,0.0066528-0.010498,0.0114746-0.0146484c0.10186-0.0230865,15.3084774-3.4694977,17.9484882-4.0644493 c0.0352173-0.0078125,0.0643921-0.0273438,0.0973511-0.0397949c19.0996971,4.4957237,18.2303658,4.3366661,18.4299927,4.3366661 c0.4144669,0,0.7473717-0.3352814,0.75-0.7451172c0.0791321-12.2700005,0.2286911-24.8520088,0.3359375-36.9809532 c3.2604828-5.2970676,7.2790756-13.97159,5.0361328-19.7866211C67.0105286,7.553546,63.8635559,5.2127256,60.3884583,4.85921z M24.2595501,66.4368439c-0.1054153-0.0233917-14.3338861-3.1805725-16.8095703-3.727047 C7.0617967,48.3806953,6.8420701,33.9500313,6.8132615,20.8670235c5.8759589,1.233469,11.3363876,2.3809967,17.2407227,3.6113281 C24.3160305,51.6952362,24.2979584,58.1465149,24.2595501,66.4368439z M42.6662903,62.5681953 c-2.7329216,0.6163788-16.6759109,3.7770119-16.7893696,3.8027306c-0.1231174-12.0390549-0.0782604-29.8359985-0.02948-41.9248009 c5.5739422-1.1885509,11.055666-2.3654537,17.2197285-3.6884766C43.0675392,20.8666286,42.96418,48.7001991,42.6662903,62.5681953z M61.3523254,66.5017853c-5.4633789-1.2939453-11.2871094-2.6728477-16.8710938-3.989254 c-0.1817551-17.4268951-0.0330315-7.6905823,0.1430664-41.7041016c1.5129585,0.33918,2.9774971,0.6543026,4.5148926,0.9870605 c1.2711296,3.5923672,4.1154442,8.24547,6.2368164,10.9348145c0.510498,0.6472168,1.4362793,1.4404297,2.2056885,1.7519531 c0.8912773,0.6281052,1.8476524,0.4962959,2.5943604-0.1904297c0.5303345-0.4863281,1.022644-1.03125,1.4845581-1.6137695 C61.5390205,45.8931503,61.4254494,55.6076279,61.3523254,66.5017853z M64.0022278,25.9051094 c-1.2943535,2.4604969-2.8116989,5.4206085-4.840332,7.28125c-0.1386719,0.1279297-0.296875,0.1855469-0.4130859,0.2011719 c-0.7806473-0.0199814-5.2463379-5.6790333-7.6728516-13.1708984c-0.5771484-1.7861328-1.190918-4.1210938-0.8085938-6.3457041 c0.3496094-2.03125,0.9931641-3.5849609,1.9125977-4.6152344c1.8496094-2.0751953,5.0126953-3.2119141,8.0566406-2.9042969 c2.9272461,0.2978516,5.5722656,2.2568359,6.5820313,4.8740234C68.454361,15.4667559,66.1138763,21.8956394,64.0022278,25.9051094z "/>
					<path class="latepoint-step-svg-base" d="M54.1091614,12.0506163c-2.088459,3.2326937,0.0606689,7.85254,4.3237305,7.85254 c3.6078873,0,5.8475189-3.5880222,4.8115234-6.6953135C61.9358063,9.2799187,56.3691139,8.5516081,54.1091614,12.0506163z M58.170929,18.3797188c-0.8803711-0.0610352-1.743103-0.4106445-2.3566895-1.0410156 c-1.1245117-1.1542969-1.3198242-3.1201181-0.4453125-4.4736338c0.8155251-1.2618265,2.428051-1.8824129,4.0743408-1.404541 c0.5652466,0.5754395,1.0892944,1.170166,1.3425903,1.8354492C61.5309181,15.2528019,60.553997,17.7360039,58.170929,18.3797188z" /></svg>';
				break;
			case 'booking__services':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73 73">
					<path class="latepoint-step-svg-highlight" d="M12.4475956,46.2568436c-0.1044884,1.7254677-0.2875328,2.2941246,0.1235962,3.2275391 c0.2800293,1.0578613,1.2532349,2.0065918,2.4077148,2.4970703c2.5679932,1.0912819,3.8084583,0.576416,36.5757446,0.7905273 c1.5809326,0.0102539,4.2476807-0.1374512,5.786499-0.4538574c2.1460648-0.4416046,4.1996078-1.119503,4.6765137-3.3955078 c0.1690674-0.3930664,0.2585449-0.8137207,0.2453613-1.244873c-0.0195313-0.6503906-0.0566406-1.3046875-0.1044922-1.9511719 c-0.1210938-1.6845703-1.6621094-2.9892578-3.5175781-2.9892578c-0.015625,0-0.03125,0-0.046875,0l-42.6777344,0.5214844 C14.0725956,43.2812576,12.5491581,44.5976639,12.4475956,46.2568436z M58.6409569,44.2373123 c1.0712891,0,1.9560547,0.6972656,2.0214844,1.5976563c0.0458984,0.6259766,0.0830078,1.2587891,0.1005859,1.8876953 c0.0309868,1.0110512-0.9663086,1.7237892-2.0117188,1.7304688c-14.3534698,0.0823135-28.739151,0.728199-42.9609375,0.5419922 c-1.0929708-0.0137672-2.0631294-0.8028984-1.9785156-1.8085938c0.0527344-0.6113281,0.0957031-1.2294922,0.1337891-1.8378906 c0.0537109-0.8789063,0.9267578-1.5771484,1.9882813-1.5898438C16.0340576,44.757576,58.7426338,44.2373123,58.6409569,44.2373123z "/>
					<path class="latepoint-step-svg-base" d="M58.2141991,6.9736419l-0.5214844,4.9931645c-0.0457916,0.4391737,0.2963982,0.828125,0.7470703,0.828125 c0.3789063,0,0.7050781-0.2861328,0.7451172-0.671875l0.5214844-4.9931645 c0.0429688-0.4121094-0.2558594-0.78125-0.6679688-0.8242188C58.6360741,6.256845,58.2571678,6.5605559,58.2141991,6.9736419z"/>
					<path class="latepoint-step-svg-base" d="M65.2903671,8.9316502l-3.6796837,3.6767578c-0.4748344,0.4748325-0.1306915,1.2802734,0.5302734,1.2802734 c0.1914063,0,0.3837891-0.0732422,0.5302734-0.2197266L66.350914,9.992197c0.2929688-0.2929688,0.2929688-0.7675781,0-1.0605469 C66.0589218,8.639658,65.5843124,8.6377048,65.2903671,8.9316502z"/>
					<path class="latepoint-step-svg-base" d="M68.8108749,16.1767673c-0.1835938-0.3710938-0.6347656-0.5234375-1.0048828-0.3388672 c-1.1025391,0.5478516-2.3320313,0.7939453-3.5585938,0.7119141c-0.4033165-0.0234375-0.770504,0.2851563-0.7978477,0.6982422 s0.2851563,0.7705078,0.6982384,0.7978516c1.4586029,0.0992756,2.9659576-0.1902256,4.3242188-0.8642578 C68.8431015,16.9970798,68.9944687,16.5468845,68.8108749,16.1767673z"/>
					<path class="latepoint-step-svg-highlight" d="M7.0583744,24.3901463c1.7924805,0.6647949,3.8635864,0.6894531,5.857666,0.7006836 c12.414856,0.0710449,23.6358051,0.019043,36.0507202,0.0898438c1.8114014,0.0102539,4.8669434-0.1374512,6.630127-0.4538574 c1.7630615-0.3166504,3.4486084-0.7158203,4.5030518-1.8364258c0.5599365-0.5949707,0.8862305-1.326416,0.9301758-2.0551758 c0.1284103-0.495512,0.1391678-0.7500668-0.0229492-2.7072754c-0.125988-1.5260391-1.6530342-2.9814453-3.9726563-2.9814453 L8.1350956,15.6670017c-2.0859375,0.0224609-3.7490234,1.3085938-3.8671875,2.9931641 c-0.131978,1.8722496-0.2533808,2.0809135-0.0430298,2.7998047C4.332056,22.6867771,5.5573368,23.8335056,7.0583744,24.3901463z M5.7640018,18.764658c0.0615234-0.8681641,1.1318359-1.5849609,2.3867188-1.5976563l48.8994141-0.5205078 c1.2441406-0.0126953,2.3886719,0.7070313,2.4628906,1.6044922c0.0517578,0.625,0.09375,1.2558594,0.1142578,1.8818359 c0.0375061,1.0384789-1.2411385,1.7228012-2.4140625,1.7285156c-16.2836723,0.0816097-33.0511169,0.7308216-49.2275391,0.5429688 c-1.1799021-0.0141487-2.4750004-0.7440434-2.3740234-1.8007813C5.6712284,19.9912205,5.7220097,19.3730564,5.7640018,18.764658z" />
					<path class="latepoint-step-svg-highlight" d="M25.6985722,38.054451c1.9748383,1.0864716,2.6161232,0.5729103,28.2541523,0.7905273 c1.2214355,0.0102539,3.28125-0.1374512,4.4699707-0.4538574c1.6699829-0.4448471,2.8914299-1.0308228,3.4542236-2.7290039 c0.6960297-1.1023483,0.5326729-2.1277504,0.4388428-3.850584c-0.0966797-1.7070313-1.40625-3.0332031-2.9306641-3.0009766 l-32.9677734,0.5205078c-1.5166016,0.0253906-2.765625,1.3466797-2.8447266,3.0097637 c-0.0829926,1.7514267-0.3514214,2.8246078,0.5612793,4.0524902C24.4834843,37.0983963,25.0513554,37.698494,25.6985722,38.054451z M25.0706425,32.4111404c0.0419922-0.8740215,0.6445313-1.5683575,1.3710938-1.5800762l32.9667969-0.5205078 c0.0058594,0,0.0117188,0,0.0175781,0c0.7314453,0,1.3417969,0.6923828,1.3916016,1.5839844 c0.0351563,0.6289043,0.0634766,1.2646465,0.078125,1.8945293c0.0201225,0.8820457-0.556736,1.731514-1.3867188,1.7373047 c-10.9964714,0.0815811-22.1932869,0.7267456-33.1787109,0.5419922c-0.7375622-0.013092-1.4293518-0.7859573-1.3623047-1.8242188 C25.0081425,33.6347733,25.0423222,33.0185623,25.0706425,32.4111404z"/>
					<path class="latepoint-step-svg-highlight" d="M62.451992,63.2775955c0.5789719-1.0259094,0.4419289-1.8840179,0.3344727-3.6164551 c-0.1044922-1.6894531-1.4648438-2.9960938-3.1064453-2.9960938c-0.0146484,0-0.0302734,0-0.0449219,0l-36.3544922,0.5205078 c-1.6298828,0.0234375-2.9755859,1.3427734-3.0634766,3.0048828c-0.09375,1.795887-0.3370171,2.6628914,0.4232788,3.8208008 c0.3649292,0.8071289,1.0519409,1.5019531,1.8442383,1.8972168c2.1949348,1.0950089,3.3277054,0.5763168,31.1570454,0.7905273 c1.3469238,0.0102539,3.6184082-0.1374512,4.9293213-0.4538574C60.4500313,65.7912064,61.8896866,65.1745071,62.451992,63.2775955z M59.7708397,63.3798904c-12.1266251,0.0816307-24.4732285,0.7282944-36.5908203,0.5419922 c-0.9430161-0.0149651-1.6459942-0.8662491-1.578125-1.8183594c0.0439453-0.6103516,0.0820313-1.2265625,0.1132813-1.8339844 c0.0458984-0.8769531,0.7431641-1.5722656,1.5869141-1.5839844l36.3544922-0.5205078 c0.9013672-0.0332031,1.5761719,0.6855469,1.6328125,1.5888672c0.0390625,0.6289063,0.0693359,1.2617188,0.0859375,1.8916016 C61.4014854,62.6212692,60.6525688,63.3738251,59.7708397,63.3798904z"/>
				</svg>';
				break;
			case 'booking__agents':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73 73">
					<path class="latepoint-step-svg-base" d="M53.4534083,0.0474242671 C53.0666895,-0.0961304329 52.6335841,0.0967406671 52.4866114,0.483947667 L50.3816309,6.05572497 C50.2351465,6.44342027 50.4309473,6.87603747 50.8181543,7.02252187 C51.2107248,7.16946117 51.6403055,6.96943747 51.7849512,6.58599847 L53.8899317,1.01422117 C54.0364161,0.626525867 53.8406153,0.193908667 53.4534083,0.0474242671 Z"></path>
					<path class="latepoint-step-svg-base" d="M55.1467677,9.54449457 L60.2917872,4.91949457 C60.5998927,4.64263907 60.624795,4.16851797 60.3479395,3.86041257 C60.0701075,3.55181877 59.5964747,3.52691647 59.2888575,3.80426027 L54.143838,8.42926027 C53.8357325,8.70611577 53.8108302,9.18023687 54.0876857,9.48834227 C54.3632441,9.79482267 54.8367587,9.82286737 55.1467677,9.54449457 Z"></path>
					<path class="latepoint-step-svg-base" d="M58.0530177,12.1817007 C58.1018458,12.5601187 58.4245997,12.8364859 58.7961818,12.8364859 C58.8279201,12.8364859 58.8601466,12.8345328 58.8923732,12.8306265 C60.810342,12.585021 62.7136623,11.9522085 64.3962795,11.0010376 C64.7566311,10.7974243 64.8840725,10.3399048 64.6799709,9.97906487 C64.4758693,9.61724847 64.0178615,9.49078357 63.6579982,9.69537347 C62.1428615,10.5518188 60.4289943,11.1211548 58.7019435,11.3423462 C58.2908106,11.3950796 58.0007716,11.7710562 58.0530177,12.1817007 Z"></path>
					<path class="latepoint-step-svg-base" d="M30.1647665,12.3430099 C34.8016087,11.2484035 39.4478623,14.1199381 40.5424644,18.7567618 C41.6370664,23.3935856 38.7655134,28.0398278 34.1286712,29.1344342 C29.491829,30.2290406 24.8455754,27.3575061 23.7509733,22.7206823 C22.6563712,18.0838585 25.5279243,13.4376163 30.1647665,12.3430099 Z M30.7048927,13.6876382 C26.8743165,14.5919117 24.5020759,18.4302508 25.406345,22.2608086 C26.3106141,26.0913663 30.1489646,28.4635885 33.9795408,27.5593151 C37.810117,26.6550416 40.1823577,22.8167025 39.2780886,18.9861448 C38.3738195,15.155587 34.535469,12.7833648 30.7048927,13.6876382 Z"></path>
					<path class="latepoint-step-svg-base" d="M21.9115992,61.4981718 C23.8270655,62.2352323 26.1083765,62.550601 28.0801173,62.8933134 C39.1328402,64.8145094 50.0195018,63.0462065 53.2110377,61.4772978 C54.3124781,60.935916 53.9811183,59.2539663 52.7560206,59.1805411 C50.270547,59.0314932 47.770608,59.1632071 45.3111353,59.5512114 C55.2235003,54.6875143 61.8597269,44.4488249 62.4270411,34.1118765 L62.4270411,34.1123648 C63.5544825,13.7695837 44.6203433,-0.201645833 26.3787013,3.15100097 C1.04216438,5.25931547 -5.22645982,35.1987143 4.08518218,48.907836 C7.82184888,54.4092207 14.728097,59.697505 21.9115992,61.4981718 Z M49.7043238,55.0174551 C38.1006632,64.1502943 22.8722105,61.8384047 13.4803858,53.7492056 C12.5408716,43.1234541 20.9689856,33.9107046 31.6687403,33.9107046 C42.9996081,33.9107046 51.4818011,44.1488142 49.7043238,55.0174551 Z M9.60721588,15.241271 C26.2435961,-6.79306413 62.4589091,6.43408397 60.9289942,34.029357 C60.8975687,34.1444121 60.8018961,44.9580946 51.3662501,53.6017447 C52.1936312,42.0003806 42.9873324,32.4107047 31.6687403,32.4107047 C20.7886057,32.4107047 11.8490992,41.2775069 11.9136133,52.293212 C2.00266698,42.3921652 1.59887988,25.849227 9.60721588,15.241271 Z"></path>
				</svg>';
				break;
			case 'booking__datepicker':
			case 'customer':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73 73">
					<path class="latepoint-step-svg-highlight" d="M36.270771,27.7026501h16.8071289c0.4140625,0,0.75-0.3359375,0.75-0.75s-0.3359375-0.75-0.75-0.75H36.270771 c-0.4140625,0-0.75,0.3359375-0.75,0.75S35.8567085,27.7026501,36.270771,27.7026501z"/>
					<path class="latepoint-step-svg-highlight" d="M40.5549507,42.3081207c0,0.4140625,0.3359375,0.75,0.75,0.75h12.6015625c0.4140625,0,0.75-0.3359375,0.75-0.75 s-0.3359375-0.75-0.75-0.75H41.3049507C40.8908882,41.5581207,40.5549507,41.8940582,40.5549507,42.3081207z"/>
					<path class="latepoint-step-svg-highlight" d="M45.6980171,51.249527H29.9778023c-0.4140625,0-0.75,0.3359375-0.75,0.75s0.3359375,0.75,0.75,0.75h15.7202148 c0.4140625,0,0.75-0.3359375,0.75-0.75S46.1120796,51.249527,45.6980171,51.249527z"/>
					<path class="latepoint-step-svg-highlight" d="M62.1623726,11.5883932l0.3300781-3.3564453c0.0405273-0.4121094-0.2607422-0.7792969-0.6728516-0.8193359 c-0.4091797-0.0458984-0.77882,0.2597656-0.8203125,0.6728516l-0.3300781,3.3564453 c-0.0405273,0.4121094,0.2612305,0.7792969,0.6733398,0.8193359 C61.7317963,12.3070383,62.1204109,12.0155325,62.1623726,11.5883932z"/>
					<path class="latepoint-step-svg-highlight" d="M63.9743843,13.9233541c1.1010704-0.3369141,2.0717735-1.0410156,2.7333946-1.9814453 c0.2382813-0.3388672,0.1567383-0.8066406-0.1816406-1.0449219c-0.3383789-0.2392578-0.8066406-0.1572266-1.0449219,0.1816406 c-0.4711914,0.6699219-1.1621094,1.1708984-1.9462852,1.4111328c-0.3959961,0.1210938-0.6186523,0.5400391-0.4975586,0.9365234 C63.1588402,13.8212023,63.5774651,14.0450754,63.9743843,13.9233541z"/>
					<path class="latepoint-step-svg-highlight" d="M68.8601227,17.4516735c0.0356445-0.4121094-0.2695313-0.7763672-0.6826172-0.8115234l-3.859375-0.3349609 c-0.4072227-0.0390625-0.7758751,0.2695313-0.8115196,0.6826172c-0.0356445,0.4121094,0.2695313,0.7763672,0.6826134,0.8115234 l3.859375,0.3349609C68.4594727,18.1708145,68.8244781,17.8649578,68.8601227,17.4516735z"/>
					<path class="latepoint-step-svg-highlight" d="M4.7497134,18.4358044c1.0574932,1.9900436,1.9738078,2.5032253,13.2814941,11.7038574 c0.5604858,11.4355488,0.9589844,22.8789082,1.1829224,34.3259277c0.3128052,0.1918945,0.6256714,0.3835449,0.9384766,0.5751953 c0.1058846,0.3764038,0.416275,0.5851364,0.7949219,0.5466309c12.6464844-1.4892578,25.8935547-2.0419922,40.4916992-1.6767578 c0.4600639-0.0021172,0.763813-0.3514481,0.7685547-0.7421875c0.1805725-16.3819695-0.080349-32.8599472,0.0605469-49.1875 c0.003418-0.3740234-0.2685547-0.6923828-0.6376953-0.7480469c-14.1435547-2.140625-28.5092773-2.3291016-42.6953125-0.5664063 c-0.331604,0.0407715-0.5751953,0.2971191-0.6331177,0.6113281c-0.3464966,0.277832-0.6930542,0.5556641-1.0396118,0.8334961 c0.1156616,1.137207,0.0985718,2.392333,0.1765137,3.5629873c-2.2901011-1.8925772-4.5957651-3.8081045-6.9354258-5.7802725 c-0.7441406-0.6269531-1.6889648-0.9277344-2.683105-0.8378906C4.4105406,11.3600969,3.320657,15.7476349,4.7497134,18.4358044z M60.7629585,14.6196432c-0.1265907,15.9033155,0.1148987,31.8954544-0.046875,47.7734375 c-14.0498047-0.3193359-26.8598633,0.2099609-39.1044922,1.6074219c0.0154419-10.8208008-0.2228394-21.3803711-0.6828613-31.503418 c8.6963615,7.0753174,9.1210613,7.5400124,10.6517334,8.1962891c2.7804565,1.1923828,7.8590698,1.5974121,8.4487305,0.6987305 c0.0741577-0.0522461,0.1495361-0.1047363,0.2015381-0.1826172c0.1469727-0.2207031,0.1669922-0.5029297,0.0517578-0.7412109 c-1.0354347-2.1505203-2.3683548-6.0868149-3.1914063-6.7568359c-5.5252628-4.5023842-10.581501-8.5776329-16.84375-13.7214375 c-0.1300049-1.973877-0.2654419-3.9484863-0.4165039-5.9221182C33.4343452,12.4419088,47.1985054,12.6274557,60.7629585,14.6196432 z M9.5368834,13.0405416c9.0454321,7.6246099,17.5216217,14.4366217,26.5917969,21.8203125 c0.3883591,0.3987503,1.5395088,3.3786926,2.2700195,5.078125c-1.4580688-0.1650391-2.9936523-0.479248-4.7089233-0.8842773 c0.4859009-0.9790039,1.1461182-1.8769531,1.953064-2.6108398c0.3061523-0.2783203,0.3286133-0.7529297,0.0498047-1.0595703 c-0.2783203-0.3046875-0.7519531-0.328125-1.0595703-0.0498047c-0.9295654,0.8461914-1.6932373,1.8774414-2.2598877,3.0026855 c-8.9527779-7.1637478-17.1909065-14.1875877-25.8739014-21.1394062c-0.5556641-0.4443359-0.8725586-1.09375-0.8481445-1.7363272 C5.7526169,12.8167362,8.1288319,11.8543167,9.5368834,13.0405416z"/>
				</svg>';
				break;
			case 'payment__times':
			case 'payment__portions':
			case 'payment__methods':
			case 'payment__processors':
			case 'payment__pay':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73 73">
					<path class="latepoint-step-svg-highlight" d="M58.6511116,6.1223307l-0.2675781,2.7509766c-0.0427284,0.4397869,0.3022537,0.8222656,0.7470703,0.8222656 c0.3818359,0,0.7080078-0.2900391,0.7451172-0.6777344l0.2675781-2.7509766 c0.0400391-0.4121094-0.2617188-0.7792969-0.6738281-0.8183594C59.0612679,5.3947916,58.6901741,5.7092447,58.6511116,6.1223307z" />
					<path class="latepoint-step-svg-highlight" d="M60.9724007,11.0764322c0.296711,0.2927561,0.7712784,0.2872667,1.0605469-0.0058594 c1.0693359-1.0820313,1.8466797-2.4306641,2.2470665-3.8984375c0.109375-0.3994141-0.1269531-0.8115234-0.5263634-0.9208984 c-0.4082031-0.1083984-0.8125,0.1269531-0.9208984,0.5263672c-0.3330078,1.2197266-0.9785156,2.3398438-1.8662109,3.2382813 C60.6755257,10.3108072,60.6774788,10.7854166,60.9724007,11.0764322z"/>
					<path class="latepoint-step-svg-highlight" d="M68.802475,10.2619791c-0.1806641-0.3710938-0.6279297-0.5253906-1.0029297-0.3466797l-4.2695274,2.0771484 c-0.3720703,0.1816406-0.5273438,0.6308594-0.3466797,1.0029297c0.1800232,0.3695202,0.6266098,0.5278702,1.0029259,0.3466797 l4.2695313-2.0771484C68.8278503,11.0832682,68.983139,10.6340494,68.802475,10.2619791z"/>
					<path class="latepoint-step-svg-highlight" d="M56.075428,39.6298981l-0.0135498,0.1000977c-1.02771,0.3820801-1.6018066,1.6784668-1.2001343,2.6987305 c0.4017334,1.0202637,1.6987915,1.5778809,2.7179565,1.173584c1.019165-0.404541,1.581665-1.692627,1.1917114-2.7172852 C58.3814583,39.8601227,57.1116829,39.2714996,56.075428,39.6298981z"/>
					<path class="latepoint-step-svg-highlight" d="M67.1153412,64.6347809c0.3217163-0.7180176-0.0892334-1.5942383-0.7265625-2.0559082 c-0.3763428-0.2724609-0.8133545-0.4296875-1.2661743-0.5449219c0.4932785-1.2028122,0.3154755,0.6508713,0.4796753-37.815918 c0.0175247-3.8000011-0.7661972-6.7081814-4.6874352-7.2695313c-0.3728027-0.1738281-0.7583618-0.3242188-1.1530762-0.456543 c0.0695915-1.4608269-0.0228233-2.4685307-0.0032349-3.5571299c0.0311775-1.7980299-1.4539566-3.2119141-3.1962891-3.2119141 c-0.0029297,0-0.0058594,0-0.0087891,0L17.7292366,9.8449869c-3.6554623,0.0112343-7.4443989,0.1655378-10.0129395,2.8173828 c-1.4490428,1.00739-2.4756026,2.9240465-2.9685669,4.6687021c-0.8636329,3.0560856-0.6394863,1.955822-0.4553223,44.1296387 c0.0185671,4.2640686,1.1058459,5.8280563,6.0576177,5.918457c18.1763916,0.3305664,36.4078979,0.4030762,54.4744225-1.6201172 C65.7114716,65.6596832,66.750412,65.4494781,67.1153412,64.6347809z M10.1530647,12.6457682 c2.2675781-1.2832031,5.0898438-1.2929688,7.5800781-1.3007813l38.8242188-0.1220703c0.0019531,0,0.0039063,0,0.0048828,0 c0.9442444,0,1.7127266,0.7628899,1.6962891,1.6855469c-0.0167885,0.973794,0.0510406,1.9935045,0.0214844,3.1801767 c-3.1493874-0.6768255-2.4396057-0.4888554-44.4998169-0.6098642c-0.5518799-0.0014648-5.0442505,0.4206543-6.5944219,1.3168955 C7.4678226,15.1682291,8.5861702,13.5339518,10.1530647,12.6457682z M64.0123749,45.5925446l-5.2597008,0.0493164 c-3.4698677,0.0267563-7.8461227-0.6362991-7.4550781-4.0878906c0.2425804-2.1451874,2.5993347-3.0465698,4.7382813-3.3955078 c2.6318359-0.4296875,5.3945313-0.3251953,7.9882774,0.3017578c0.0061646,0.0014648,0.012085-0.0004883,0.0182495,0.0007324 L64.0123749,45.5925446z M64.0487518,36.9409332c-2.6920738-0.6071777-5.5366783-0.7060547-8.2550621-0.2629395 c-2.8740196,0.470295-5.6615906,1.8131523-5.9863281,4.7080078c-0.5018425,4.4379425,4.47435,5.7899628,8.9589844,5.7558594 l5.2397423-0.0490723c-0.0889435,13.624691,0.1381378,14.0157204-0.5004845,14.7600098 c-0.4492188,0.5253906-2.2080078,1.0888672-3.2431641,1.1425781c-17.3261032,0.8932877-33.7187004,1.8238754-50.8261719,0.8164063 c-0.8339844-0.0488281-1.4882817-0.7509766-1.4912114-1.5986328C7.9190578,52.4376526,6.8739986,19.3938637,7.102283,19.0354176 c1.2720323,0,6.8894105-0.2661171,25.2783203-0.2939453c8.4413376-0.0108852,17.2458305-0.0266666,25.7978516-0.3779297 C65.4974823,18.0765209,64.0197983,20.7003078,64.0487518,36.9409332z"/>
					</svg>';
				break;
			case 'verify':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80">
					<path class="latepoint-step-svg-base" d="M14.1105938,17.6527386h21.4086933c0.4140625,0,0.75-0.3359375,0.75-0.75s-0.3359375-0.75-0.75-0.75H14.1105938 c-0.4140625,0-0.75,0.3359375-0.75,0.75S13.6965313,17.6527386,14.1105938,17.6527386z"/>
					<path class="latepoint-step-svg-base" d="M48.0480957,22.5179729c0.190918-4.6103516-0.2402344-8.1689453-1.3554688-11.2001953 c-1.9773369-5.3880882-10.6812592-6.6263709-16.4194965-6.88623c-2.2271042-0.3552918-3.4171219-0.4732823-23.8388062-0.9545901 C5.5955906,3.4306827,5.2978926,3.7840867,5.309813,4.2435594c0.4078836,15.8521996,0.3535037,38.6989517,0.1298828,54.6308594 c0.0489416,0.1005783,0.1066036,0.7338486,0.7416992,0.7373047c0.0014648,0,0.003418,0,0.0048828,0 c0.1726775,0,19.3874683-0.9524536,39.9575195,1.1923828c0.5861588,0.0651283,1.0673027-0.5827713,0.6965942-1.1501465 c-0.3957596-2.2545013-0.4755592-3.6757584-0.5795288-5.1481934c0.0477905-0.0227051,0.0947876-0.0480957,0.1424561-0.0710449 c2.0167389,2.6554184,8.5339165,10.8789749,11.3917847,12.6982422c0.7129517,0.4538574,1.5125732,0.8005371,2.3395996,0.9714355 c4.5379868,1.9745102,8.1917953-3.4511719,5.8001099-6.3081055c-4.0245361-4.8284912-8.767334-10.3620605-13.5692749-15.0280762 c1.0654297-2.1257324,1.6327515-4.5004883,1.6327515-6.911377c0-4.8347168-2.2924194-9.3981953-6.1298218-12.3183613 c0.0004272-0.0112305,0.0014648-0.0220947,0.0018921-0.0332031 C47.9866676,24.0398521,48.0113487,23.3549309,48.0480957,22.5179729z M45.2601929,59.2135315 c-12.4361572-1.2451172-25.3148212-1.6257324-38.3179321-1.1262207c0.02246-8.7914352,0.4327807-31.9077263-0.112915-53.0991211 c20.4045773,0.4872842,21.7616024,0.5873499,24.1508789,1.0756836c1.9755001,0.4037867,3.2904224,4.9198499,5.040041,6.5957026 c0.3312874,0.3179483,0.834362,0.2433729,1.1196289-0.0429688c1.8201218-1.8236427,4.0447845-4.2757235,6.2490234-3.3017578 c0.7670898,0.3339844,1.4047852,1.1816406,1.8959961,2.5205078c1.0449219,2.8398438,1.4467773,6.2138672,1.2641602,10.6191406 c-0.0358124,0.8280945-0.0610733,1.5315475-0.1461792,4.076416c-2.3810425-1.4171143-5.0792236-2.1643066-7.8845825-2.1643066 c-3.1671143,0-6.135437,0.9802246-8.6168232,2.6494141c-0.4119091-0.311924,0.2382946-0.0890408-15.7840576-0.3027344 c-0.0024414,0-0.0048828,0-0.0068359,0c-0.4111328,0-0.7460938,0.3310547-0.75,0.7431641 c-0.0039063,0.4140625,0.3291016,0.7529297,0.7431641,0.7568359l14.081665,0.1290283 c-2.8327827,2.5395775-5.5364246,7.2262096-5.8631592,11.064333l-10.6237793,0.2597656 c-0.4140625,0.0107422-0.7412109,0.3544922-0.7314453,0.7685547c0.0102539,0.4072266,0.34375,0.7314453,0.7495117,0.7314453 c0.0063477,0,0.0126953,0,0.019043,0l10.5239258-0.2573242c-0.0244522,3.6942863,0.6843319,7.0339737,3.2225342,10.0561523 l-11.5189209,0.1054688c-0.4140625,0.0039063-0.7470703,0.3427734-0.7431641,0.7568359 c0.0039063,0.4121094,0.3388672,0.7431641,0.75,0.7431641c0.0019531,0,0.0043945,0,0.0068359,0l12.9440308-0.1186523 c0.0007935,0.0007324,0.0015259,0.0014648,0.0023193,0.0021973c3.6866817,3.1902428,7.7025356,4.4405403,11.8752575,4.1297493 c1.9718208-0.146862,3.978672-0.6423225,6.0023689-1.4463997C44.890686,56.5292053,45.0510254,57.889801,45.2601929,59.2135315z  M64.7839355,62.7582092c1.643486,1.9650421-1.8606987,5.9641113-4.7329102,3.5546875 c-0.2494545-0.2046814-7.4860306-8.2930336-12.2422485-14.1032715c1.5042725-1.1379395,2.7863159-2.5305176,3.7785034-4.102417 C56.248291,52.6703186,60.8580322,58.0475159,64.7839355,62.7582092z M52.498291,39.856842 c0,7.7039337-6.2337532,13.9804688-13.9799805,13.9804688c-7.7138691,0-13.989748-6.2714844-13.989748-13.9804688 c0-7.7516708,6.3275547-13.9902363,13.989748-13.9902363C46.3522835,25.8666058,52.498291,32.2686691,52.498291,39.856842z"/>
					<path class="latepoint-step-svg-base" d="M61.0549316,64.0072327c0.2964249,0.2864761,0.7709198,0.2816391,1.0605469-0.0175781 c0.2875977-0.2978516,0.2792969-0.7734375-0.0185547-1.0605469l-1.0400391-1.0039063 c-0.2978516-0.2880859-0.7734375-0.2773438-1.0605469,0.0195313c-0.2875977,0.2988281-0.2788086,0.7734375,0.0195313,1.0605469 L61.0549316,64.0072327z"/>
					<path class="latepoint-step-svg-base" d="M38.798584,28.5873089c-6.2089844,0-11.2602558,5.055666-11.2602558,11.2695332 c0,6.2089844,5.0512714,11.2597656,11.2602558,11.2597656c6.2009888,0,11.2597656-5.036171,11.2597656-11.2597656 C50.0583496,33.6183395,44.9775581,28.5873089,38.798584,28.5873089z M38.798584,49.6166077 c-5.3818359,0-9.7602558-4.3779297-9.7602558-9.7597656c0-5.3867188,4.3784199-9.7695332,9.7602558-9.7695332 c5.343029,0,9.7597656,4.3516827,9.7597656,9.7695332C48.5583496,45.2636604,44.1625519,49.6166077,38.798584,49.6166077z"/>
					<path class="latepoint-step-svg-base" d="M44.651123,39.0619202c-4.2592773-0.2041016-6.421875-0.2050781-10.8295898,0.1923828 c-0.4125977,0.0371094-0.7167969,0.4023438-0.6796875,0.8144531c0.0351563,0.3896484,0.3623047,0.6826172,0.7460938,0.6826172 c0.0229492,0,0.0454102-0.0009766,0.0683594-0.0029297c4.3188477-0.3916016,6.440918-0.3886719,10.6225586-0.1884766 c0.4106445,0.0498047,0.765625-0.2998047,0.7851563-0.7128906C45.3840332,39.4330139,45.0646973,39.0814514,44.651123,39.0619202z "/>
				</svg>';
				break;
			case 'confirmation':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80">
					<path class="latepoint-step-svg-base" d="M17.6552105,33.4646034C8.2132654,33.6182289,3.8646491,39.9382057,3.773782,46.3166199 C3.6704469,53.57024,9.073472,60.8994293,18.7539654,59.3212318c0.0535278,1.8059692,0.1070557,3.6119995,0.1605835,5.4179649 c0.4868374,0.7426834,0.9158726,1.2552795,1.3218193,1.5758286c0.7646008,0.6037445,1.4473019,0.5261841,2.2800751,0.0214233 c0.9628239-0.5835876,2.1262512-1.7382126,3.8487892-3.0711861c1.3595581,1.338192,2.7954102,3.2556725,3.8725586,4.7504234 c0.6969604,0.1324463,1.3938599,0.2648926,2.0908184,0.3973389c0.354744,0.2420731,0.7306252,0.1458817,0.9553833-0.0870972 c1.1480217-1.1914139,0.2770538-0.5825653,5.0960693-4.9796104c1.381897,1.3053551,3.0732422,3.0024986,4.1270752,4.464901 c2.8935661,0.5499954,2.7743301,0.7335205,3.1699219,0.4522095c0.2846146-0.2016754,0.2662773-0.1645584,0.3554688-0.2646484 c1.3665047-1.5280838,3.0428238-3.2071915,4.854248-5.0933189c1.8391113,1.4305992,3.5415039,2.966732,5.0125732,4.6672935 c0.8833618,0.1398926,1.7667236,0.2797241,2.6500854,0.4195557c0.3787956,0.0587921,0.647274-0.1178513,0.7819214-0.3831787 c0.6037369-1.1866455,1.2043419-2.4298172,1.9224854-3.9011192c1.3636475,1.03265,2.6345825,2.1318321,3.7449989,3.3383751 c0.520752,0.0775146,0.9672852,0.0211792,1.4367676,0.0062256c0.6980667,0.5534744,1.3601151,0.1294708,1.392334-0.4434814 c1.1637878-20.9316826-0.4478302-32.0234108-1.8408203-43.4101563 c-1.0667953-8.7491531-3.4310074-16.6642761-17.6171913-18.6894531 C37.5750961,2.9660594,18.2152557,2.0518365,10.3015718,9.4919462 c-3.7495093,3.4759312-5.6556306,13.6249208-5.8579102,18.3261719c-0.0175781,0.4130859,0.3032227,0.7636719,0.7167969,0.78125 c0.0008545,0,0.0019531-0.0001831,0.0028076-0.0001831c0.0002441,0,0.0003662,0.0001831,0.0006104,0.0001831 c0.0022583,0.0003052,0.0042114-0.0008545,0.0064697-0.0005493c1.7694812,0.0453014,8.2837915-2.8392754,13.4412851-1.0584106 c0.3204956,1.9219971,0.4412842,3.8793335,0.4950562,5.8326435 C18.6154156,33.3746986,18.1323223,33.4094276,17.6552105,33.4646034z M19.1414165,57.7614784 c-7.5994434,0-11.3555832-5.7171745-11.3348923-11.4369698c0.0206909-5.7197952,3.8182158-11.4422112,11.3261032-11.4526787 c0.0092773,0,0.0180664,0,0.0273438,0c6.2543888,0,11.4311523,5.0988808,11.4311523,11.4394531 C30.5911236,52.5667496,25.5261116,57.7614784,19.1414165,57.7614784z M48.1580162,5.9938989 c13.5598068,1.9365721,15.3743439,9.4665871,16.3403358,17.3867188c0.7182922,5.8958893,3.0389252,18.635561,1.8983765,41.6446533 c-1.2305298-1.1603355-2.6870155-2.8059044-4.0233803-4.5684776c-0.3519096-0.4632568-1.1312485-0.3892365-1.3088379,0.2573853 c-0.0006714,0.0013428-0.0020142,0.0020142-0.0026855,0.0033569c-0.829628,1.6306496-1.5776443,3.2193794-2.6342773,5.3439903 c-1.9974098-2.2269859-3.4938774-3.9506302-5.3305054-5.9934654c-0.1636276-0.8107109-1.4189148-0.82724-1.5952148-0.0100098 c-1.9148636,2.1023941-4.205822,4.3376503-6.1530762,6.4651451c-1.4751854-1.9926682-3.3123169-4.1955643-4.62323-6.0411949 c-0.2008209-0.5232658-0.8574333-0.635643-1.2301025-0.258606c-2.1993942,2.222168-4.5591049,4.0396156-6.7687988,6.4904747 c-1.3328838-1.4328613-3.3396587-3.9911461-4.4924297-5.7590294c-0.2881527-0.4409218-0.9600582-0.4756927-1.2632446,0.0197754 c-1.7325058,1.1738968-2.8503933,2.218853-4.8071289,3.6727867l0.09198-5.7758751 c5.7322388-1.4144287,9.8353252-6.5934448,9.8353252-12.5602417c0-5.9226074-4.0585918-11.0758057-9.8167706-12.5380249 c-0.1152134-4.2746181-0.3553181-14.4360523-1.6055908-18.5303345c-0.6845055-2.2400188-2.8216324-5.7650404-5.5857553-7.1168213 C21.5624371,4.8990502,34.3388634,4.0191674,48.1580162,5.9938989z M6.0422945,26.9650288 c0.2917447-3.411478,1.0564828-7.6568089,2.2514648-10.9311523c0.883728-0.4779043,1.4030762-0.8288565,1.9675293-0.7024527 c0.9700317,0.2299805,1.9000244,1.0199575,2.710022,1.5799551c2.9155273,2.0056763,4.5519419,5.618042,5.333375,8.9669189 C13.8285227,24.7062149,8.9758253,26.2891541,6.0422945,26.9650288z"/>
					<path class="latepoint-step-svg-base" d="M20.168272,46.12183c-1.4780273-0.424263-3.6082001-0.2521667-4.2836924-1.4824219 c-0.4052734-0.7392578,0.0585938-1.7636719,0.7285166-2.2216797c0.9785156-0.6708984,2.2700195-0.5273438,2.9526367-0.3837891 c0.4052734,0.0830078,0.8032227-0.1748047,0.8886719-0.5800781s-0.1738281-0.8027344-0.5791016-0.8886719 c-0.3931274-0.0823975-0.7782593-0.130127-1.1518555-0.1454468c-0.1039429-0.53302-0.0985718-1.0831909,0.0239258-1.6152954 c0.0927734-0.4033203-0.1591797-0.8066406-0.5629883-0.8994141c-0.4038086-0.0898438-0.8061523,0.1611328-0.8989258,0.5634766 c-0.1596069,0.6945801-0.1751709,1.4108276-0.0565796,2.1081543c-0.53479,0.1254883-1.0369263,0.3114624-1.4629526,0.6027832 c-1.3994141,0.9570313-1.9360352,2.8320313-1.1962891,4.1816406c1.1052847,2.0129051,3.8100004,1.8074532,5.1850595,2.2021484 c2.1161976,0.6054153,1.8197498,2.4342194,0.3833008,3.0107422c-1.0332031,0.4150391-2.2402344,0.0205078-2.8691406-0.2519531 c-0.3808594-0.1640625-0.8217773,0.0107422-0.9863281,0.390625s0.0102539,0.8212891,0.390625,0.9863281 c0.4503174,0.1948242,1.0012817,0.3755493,1.5961304,0.4760132l0.1016235,1.6411743 c0.0249023,0.3974609,0.3549805,0.703125,0.7480469,0.703125c0.4355659,0,0.7758923-0.3669624,0.7490234-0.796875 l-0.0942383-1.5200806c0.3078613-0.0443115,0.6169434-0.112915,0.9238281-0.2357788 C23.4494343,50.8599739,23.6716747,47.1243896,20.168272,46.12183z"/>
					<path class="latepoint-step-svg-base" d="M27.5291119,20.7048359h28.2197247c0.4140625,0,0.75-0.3359375,0.75-0.75s-0.3359375-0.75-0.75-0.75H27.5291119 c-0.4140625,0-0.75,0.3359375-0.75,0.75S27.1150494,20.7048359,27.5291119,20.7048359z"/>
					<path class="latepoint-step-svg-base" d="M32.607235,31.4577656c0,0.4140625,0.3359375,0.7500019,0.75,0.7500019h23.1582031 c0.4140625,0,0.75-0.3359394,0.75-0.7500019s-0.3359375-0.75-0.75-0.75H33.357235 C32.9431725,30.7077656,32.607235,31.0437031,32.607235,31.4577656z"/>
					<path class="latepoint-step-svg-base" d="M55.2888756,41.443119H38.4182701c-0.4140625,0-0.75,0.3359375-0.75,0.75s0.3359375,0.75,0.75,0.75h16.8706055 c0.4140625,0,0.75-0.3359375,0.75-0.75S55.7029381,41.443119,55.2888756,41.443119z"/>
				</svg>';
				break;
		}

		/**
		 * Generates an SVG image for step code, if there was no custom image set
		 *
		 * @param {string} $svg image svg code
		 * @param {string} $step_code step name code
		 *
		 * @since 5.0.0
		 * @hook latepoint_svg_for_step_code
		 *
		 */
		return apply_filters( 'latepoint_svg_for_step_code', $svg, $step_code );
	}


	public static function get_time_pick_style() {
		return OsStepsHelper::get_step_setting_value( 'booking__datepicker', 'time_pick_style', 'timebox' );
	}

	/**
	 * Generates a preview for a selected step to show on booking form preview in settings
	 *
	 * @param string $selected_step_code
	 *
	 * @return void
	 */
	public static function get_step_content_preview( string $selected_step_code ) {
		switch ( $selected_step_code ) {
			case 'booking__services':
				OsBookingHelper::generate_services_bundles_and_categories_list();
				break;
			case 'booking__agents':
				$agents_model = new OsAgentModel();
				$agents       = $agents_model->should_be_active()->get_results_as_models();
				OsAgentHelper::generate_agents_list( $agents );
				break;
			case 'booking__datepicker':
				$booking = new OsBookingModel();
				$services = new OsServiceModel();
				$service = $services->should_be_active()->set_limit( 1 )->get_results_as_models();
				if ( $service ) {
					$booking->service_id = $service->id;
					?>
                    <div class="os-dates-w" data-time-pick-style="<?php echo esc_attr(OsStepsHelper::get_time_pick_style()); ?>">
						<?php OsCalendarHelper::generate_calendar_for_datepicker_step( \LatePoint\Misc\BookingRequest::create_from_booking_model( $booking ), new OsWpDateTime( 'now' ) ); ?>
                    </div>
                    <div class="time-selector-w <?php echo OsStepsHelper::hide_unavailable_slots() ? 'hide-not-available-slots' : ''; ?> <?php echo 'time-system-' . esc_attr(OsTimeHelper::get_time_system()); ?> <?php echo ( OsSettingsHelper::is_on( 'show_booking_end_time' ) ) ? 'with-end-time' : 'without-end-time'; ?> style-<?php echo esc_attr(OsStepsHelper::get_time_pick_style()); ?>">
                        <div class="times-header">
                            <div class="th-line"></div>
                            <div class="times-header-label">
								<?php esc_html_e( 'Pick a slot for', 'latepoint' ); ?> <span></span>
								<?php do_action( 'latepoint_step_datepicker_appointment_time_header_label', $booking ); ?>
                            </div>
                            <div class="th-line"></div>
                        </div>
                        <div class="os-times-w">
                            <div class="timeslots"></div>
                        </div>
                    </div>
					<?php
				} else {
					echo 'You need to have an active service to generate the calendar';
				}
				break;
			case 'booking__locations':
				OsLocationHelper::generate_locations_and_categories_list();
				break;
			case 'customer':
				$booking                     = new OsBookingModel();
				$services                    = new OsServiceModel();
				$service                     = $services->should_be_active()->set_limit( 1 )->get_results_as_models();
				$customer                    = new OsCustomerModel();
				$default_fields_for_customer = OsSettingsHelper::get_default_fields_for_customer();

				$current_step_code = $selected_step_code;

				include LATEPOINT_VIEWS_ABSPATH . 'booking_form_settings/previews/_customer.php';
				break;
			case 'payment__times':
				echo '<div class="booking-preview-step-skipped-message">' . esc_html__( "If you have both a payment processor and pay locally enabled, customer will make a selection here.", 'latepoint' ) . '</div>';
				break;
			case 'payment__portions':
				echo '<div class="booking-preview-step-skipped-message">' . esc_html__( "If selected service has both deposit and charge amount set, customer will have to pick how much they want to pay now.", 'latepoint' ) . '</div>';
				break;
			case 'payment__methods':
				echo '<div class="booking-preview-step-skipped-message">' . esc_html__( "If you have multiple payment processors enabled, customer will be able to select how they want to pay", 'latepoint' ) . '</div>';
				break;
			case 'payment__pay':
				echo '<div class="booking-preview-step-skipped-message">' . esc_html__( "Payment form generated by selected payment processor will appear here", 'latepoint' ) . '</div>';
				break;
		}
		do_action( 'latepoint_get_step_content_preview', $selected_step_code );
	}

	public static function hide_slot_availability_count(): bool {
		return OsUtilHelper::is_on( self::get_step_setting_value( 'booking__datepicker', 'hide_slot_availability_count' ) );
	}

	public static function hide_timepicker_when_one_slot_available(): bool {
		return OsUtilHelper::is_on( self::get_step_setting_value( 'booking__datepicker', 'hide_timepicker_when_one_slot_available' ) );
	}

	public static function build_booking_object_for_current_step_preview( string $current_step ): OsBookingModel {
		$booking        = new OsBookingModel();
		$steps_in_order = self::get_step_codes_in_order();

		$current_step_index = array_search( $current_step, $steps_in_order );
		if ( $current_step_index === false ) {
			return $booking;
		}
		$completed_steps = array_slice( $steps_in_order, 0, $current_step_index );
		foreach ( $completed_steps as $completed_step ) {
			self::set_booking_object_values_for_completed_step( $booking, $completed_step );
		}

		return $booking;
	}

	public static function set_booking_object_values_for_completed_step( OsBookingModel $booking, string $completed_step ): OsBookingModel {
		switch ( $completed_step ) {
			case 'booking__services':
				$services = new OsServiceModel();
				$service  = $services->should_be_active()->set_limit( 1 )->get_results_as_models();
				if ( $service ) {
					$booking->service_id = $service->id;
				}
				break;
			case 'booking__locations':
				$locations = new OsLocationModel();
				$location  = $locations->should_be_active()->set_limit( 1 )->get_results_as_models();
				if ( $location ) {
					$booking->location_id = $location->id;
				}
				break;
			case 'booking__agents':
				$agents = new OsAgentModel();
				$agent  = $agents->should_be_active()->set_limit( 1 )->get_results_as_models();
				if ( $agent ) {
					$booking->agent_id = $agent->id;
				}
				break;
			case 'customer':
				$customers = new OsCustomerModel();
				$customer  = $customers->set_limit( 1 )->get_results_as_models();
				if ( $customer ) {
					$booking->customer_id = $customer->id;
				}
				break;
			case 'booking__datepicker':
				$tomorrow            = new OsWpDateTime( 'tomorrow' );
				$booking->start_date = $tomorrow->format( 'Y-m-d' );
				$booking->start_time = 600;

				break;
		}

		/**
		 * Sets values for booking object depending on a completed step code
		 *
		 * @param {OsBookingModel} $booking booking object
		 * @param {string} $completed_step step code that was completed
		 *
		 * @since 5.0.0
		 * @hook latepoint_set_booking_object_values_for_completed_step
		 *
		 */
		return apply_filters( 'latepoint_set_booking_object_values_for_completed_step', $booking, $completed_step );
	}

	public static function generate_summary_key_value_pairs( OsBookingModel $booking ): string {
		$html = '';


		if ( $booking->location_id ) {
			$html .= '<div class="summary-box summary-box-location-info">
					<div class="summary-box-heading">
						<div class="sbh-item">' . __( 'Location', 'latepoint' ) . '</div>
						<div class="sbh-line"></div>
					</div>
					<div class="summary-box-content with-media">
						<div class="sbc-content-i">
							<div class="sbc-main-item">' . $booking->location->name . '</div>
						</div>
					</div>
				</div>';
		}
		if ( $booking->customer_id ) {
			$html                .= '<div class="summary-box summary-box-customer-info">
					<div class="summary-box-heading">
						<div class="sbh-item">' . __( 'Customer', 'latepoint' ) . '</div>
						<div class="sbh-line"></div>
					</div>
					<div class="summary-box-content with-media">
						<div class="os-avatar-w">
							<div class="os-avatar"><span>' . esc_html( $booking->customer->get_initials() ) . '</span></div>
						</div>
						<div class="sbc-content-i">
							<div class="sbc-main-item">' . esc_html( $booking->customer->full_name ) . '</div>
							<div class="sbc-sub-item">' . esc_html( $booking->customer->email ) . '</div>
						</div>
					</div>';
			$customer_attributes = [];
			$customer_attributes = apply_filters( 'latepoint_booking_summary_customer_attributes', $customer_attributes, $booking->customer );
			if ( $customer_attributes ) {
				$html .= '<div class="summary-attributes sa-clean sa-hidden">';
				foreach ( $customer_attributes as $attribute ) {
					$html .= '<span>' . esc_html( $attribute['label'] ) . ': <strong>' . esc_html( $attribute['value'] ) . '</strong></span>';
				}
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		if ( OsSettingsHelper::is_off( 'steps_hide_agent_info' ) && $booking->agent_id && $booking->agent_id != LATEPOINT_ANY_AGENT ) {
			$bio_html = '';
			if ( OsSettingsHelper::steps_show_agent_bio() ) {
				$bio_html .= '<div class="os-trigger-item-details-popup sbc-link-item" data-item-details-popup-id="osItemDetailsPopupAgent_' . $booking->agent_id . '">' . __( 'Learn More', 'latepoint' ) . '</div>';
				$bio_html .= OsAgentHelper::generate_bio( $booking->agent );
			}
			$html .= '<div class="summary-box summary-box-agent-info">
					<div class="summary-box-heading">
						<div class="sbh-item">' . __( 'Agent', 'latepoint' ) . '</div>
						<div class="sbh-line"></div>
					</div>
					<div class="summary-box-content with-media">
						<div class="os-avatar-w"
						     style="background-image: url(' . ( ( $booking->agent->avatar_image_id ) ? $booking->agent->get_avatar_url() : '' ) . ')">
							' . ( ( ! $booking->agent->avatar_image_id ) ? '<div class="os-avatar"><span>' . esc_html( $booking->agent->get_initials() ) . '</span></div>' : '' ) . '
						</div>
						<div class="sbc-content-i">
							<div class="sbc-main-item">' . esc_html( $booking->agent->full_name ) . '</div>
							' . $bio_html . '
						</div>
					</div>
				</div>';
		}


		/**
		 * Key value pairs of summary values for the booking summary panel
		 *
		 * @param {string} $html HTML of key value pairs
		 * @param {OsBookingModel} $booking Booking object that is used to generate the summary
		 * @returns {string} $html The filtered HTML of key value pairs
		 *
		 * @since 5.0.0
		 * @hook latepoint_summary_key_value_pairs
		 *
		 */
		$html = apply_filters( 'latepoint_summary_key_value_pairs', $html, $booking );

		if ( $html ) {
			$html = '<div class="summary-boxes-columns">' . $html . '</div>';
		}

		return $html;
	}

	public static function is_ready_for_summary() {
		if ( ! empty( self::$order_object ) && ! self::$order_object->is_new_record() ) {
			// order object is set - don't need to show summary anymore
			return false;
		}
		if ( ! self::$cart_object->is_empty() ) {
			// cart has items inside - show summary
			return true;
		}
		if ( self::$active_cart_item->is_bundle() ) {
			// bundle selected already - show summary
			return true;
		}
		if ( ! empty( self::$booking_object->service_id ) ) {
			// service is selected for a booking - show summary
			return true;
		}


		return false;
	}

	public static function set_active_cart_item_object( array $cart_item_params = [] ): OsCartItemModel {
		self::$active_cart_item = new OsCartItemModel();
		if ( ! empty( $cart_item_params['id'] ) ) {
			self::$active_cart_item->id = $cart_item_params['id'];
			// try to find it in cart
			$cart_item = new OsCartItemModel( self::$active_cart_item->id );
			if ( $cart_item->is_new_record() ) {
				// not found, reset active cart item ID
				self::$active_cart_item = new OsCartItemModel();
			}
		}
		self::$active_cart_item->variant = ! empty( $cart_item_params['variant'] ) ? $cart_item_params['variant'] : LATEPOINT_ITEM_VARIANT_BOOKING;
		if ( self::$active_cart_item->is_bundle() ) {
			// bundle gets data from params
			self::$active_cart_item->item_data = is_array( $cart_item_params['item_data'] ) ? wp_json_encode( $cart_item_params['item_data'], true ) : $cart_item_params['item_data'];
		} else {
			// booking gets data from booking object
			self::$active_cart_item->item_data = wp_json_encode( self::$booking_object->generate_params_for_booking_form(), true );
		}

		return self::$active_cart_item;
	}

	public static function get_cart_item_object() {
		return self::$active_cart_item;
	}


	/**
	 *
	 * Given a step code, returns the first sub step if found, or returns the parent step code if no children
	 *
	 * @param string $parent_code
	 *
	 * @return string
	 */
	public static function get_first_step_for_parent_code( string $parent_code ): string {
		$first_step_code = '';
		$step_codes      = self::$step_codes_in_order;
		foreach ( $step_codes as $step_code ) {
			$loop_parent_code = explode( '__', $step_code )[0];
			if ( $loop_parent_code == $parent_code ) {
				$first_step_code = $step_code;
				break;
			}
		}

		return $first_step_code;
	}

	public static function check_step_code_access( string $step_code_to_access ): string {
		if ( $step_code_to_access == 'confirmation' && ! self::$order_object->is_new_record() ) {
			return $step_code_to_access;
		}
		// loops through all steps and checks if they satisfy condition to be skipped
		for ( $i = 0; $i < count( self::$step_codes_in_order ); $i ++ ) {
			$code        = self::$step_codes_in_order[ $i ];
			$parent_code = explode( '__', $code )[0];

			$next_code        = ( ( $i + 1 ) < count( self::$step_codes_in_order ) ) ? self::$step_codes_in_order[ $i + 1 ] : false;
			$next_parent_code = $next_code ? explode( '__', $next_code )[0] : false;

			if ( $step_code_to_access == $code ) {
				break;
			}
			switch ( $parent_code ) {
				// even tho we are checking a parent code - make sure to assign to a $code, because it's a first one in order in that parent
				case 'customer':
					if ( ! OsAuthHelper::is_customer_logged_in() ) {
						$step_code_to_access = $code;
						break 2;
					}
					break;
				case 'booking':
					if ( $next_parent_code && $next_parent_code != $parent_code && self::$cart_object->is_empty() ) {
//						$step_code_to_access = self::get_first_step_for_parent_code($parent_code);
//						break 2;
					}
					break;
			}
		}

		/**
		 * Checks if a step code can be accessed, returns the step code that can be accessed
		 *
		 * @param {string} $step_code_to_access step code that needs to be checked for access
		 * @returns {string} $step_code_to_access The filtered step code that can be accessed
		 *
		 * @since 5.0.0
		 * @hook latepoint_check_step_code_access
		 *
		 */
		return apply_filters( 'latepoint_check_step_code_access', $step_code_to_access );
	}

	public static function get_first_step_code( string $step_code, $step_codes = false ): string {
		if ( ! $step_codes ) {
			$step_codes = self::get_step_codes_in_order();
		}
		if ( isset( $step_codes[ $step_code ] ) ) {
			return $step_code;
		}
		$unflat_step_codes = self::unflatten_steps( $step_codes );

		// TODO add support for more than 2 dimentional parent/child arrays
		if ( isset( $unflat_step_codes[ $step_code ] ) ) {
			return implode( '__', [ $step_code, array_key_first( $unflat_step_codes[ $step_code ] ) ] );
		}

		return '';
	}

	public static function build_cart_object(): OsCartModel {
		if ( ! isset( self::$cart_object ) ) {
			self::set_cart_object();
		}

		return self::$cart_object;
	}

	public static function set_order_object( array $params = [] ): OsOrderModel {
		self::$order_object = new OsOrderModel();

		return self::$order_object;
	}

	public static function set_cart_object( array $params = [] ): OsCartModel {
		self::$cart_object = OsCartsHelper::get_or_create_cart();
		if ( self::$cart_object->order_id ) {
			self::load_order_object( self::$cart_object->order_id );
		} else {
			self::load_order_object();
			self::$cart_object->set_data( $params );

			// set source id
			if ( isset( self::$restrictions['source_id'] ) ) {
				self::$cart_object->source_id = self::$restrictions['source_id'];
			}

			self::$cart_object->calculate_prices();
		}

		return self::$cart_object;
	}

	public static function set_cart_object_from_order_intent( OsOrderIntentModel $order_intent ): OsCartModel {
		OsCartsHelper::get_or_create_cart();
		self::$cart_object->clear();


		// add items from intent
		$intent_cart_items = json_decode( $order_intent->cart_items_data, true );
		foreach ( $intent_cart_items as $cart_item_data ) {
			OsCartsHelper::add_item_to_cart( OsCartsHelper::create_cart_item_from_item_data( $cart_item_data ) );
		}

		// restore payment info
		$payment_data                         = json_decode( $order_intent->payment_data, true );
		self::$cart_object->payment_method    = $payment_data['method'];
		self::$cart_object->payment_time      = $payment_data['time'];
		self::$cart_object->payment_portion   = $payment_data['portion'];
		self::$cart_object->payment_token     = $payment_data['token'];
		self::$cart_object->payment_processor = $payment_data['processor'];

		return self::$cart_object;
	}

	public static function hide_unavailable_slots() {
		return OsUtilHelper::is_on( self::get_step_setting_value( 'booking__datepicker', 'hide_unavailable_slots' ) );
	}
}