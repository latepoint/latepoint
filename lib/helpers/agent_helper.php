<?php

class OsAgentHelper {

	static $agents;
	static $selected_agent = false;
	static $total_agents;
	static $filtered_total_agents;


	/**
	 * @return OsAgentModel[]
	 */
	public static function get_allowed_active_agents(): array {
		$agents = new OsAgentModel();

		return $agents->should_be_active()->filter_allowed_records()->get_results_as_models();
	}

	/**
	 * @param bool $filter_allowed_records
	 *
	 * @return int
	 */
	public static function count_agents( bool $filter_allowed_records = false ): int {
		if ( $filter_allowed_records ) {
			if ( self::$filtered_total_agents ) {
				return self::$filtered_total_agents;
			}
		} else {
			if ( self::$total_agents ) {
				return self::$total_agents;
			}
		}
		$agents = new OsAgentModel();
		if ( $filter_allowed_records ) {
			$agents->filter_allowed_records();
		}
		$agents = $agents->should_be_active()->get_results_as_models();
		if ( $filter_allowed_records ) {
			self::$filtered_total_agents = $agents ? count( $agents ) : 0;

			return self::$filtered_total_agents;
		} else {
			self::$total_agents = $agents ? count( $agents ) : 0;

			return self::$total_agents;
		}
	}


	public static function create_default_agent() {
		$agent_model             = new OsAgentModel();

        $current_user = wp_get_current_user();

		$agent_model->first_name = $current_user->user_firstname ?? '';
		$agent_model->last_name  = $current_user->user_lastname ?? '';
		$agent_model->email      = get_bloginfo( 'admin_email' );
		if ( $agent_model->save() ) {
			$connector              = new OsConnectorModel();
			$incomplete_connections = $connector->where( [ 'agent_id' => 'IS NULL' ] )->get_results_as_models();
			if ( $incomplete_connections ) {
				foreach ( $incomplete_connections as $incomplete_connection ) {
					$incomplete_connection->update_attributes( [ 'agent_id' => $agent_model->id ] );
				}
			}
			$bookings            = new OsBookingModel();
			$incomplete_bookings = $bookings->where( [ 'agent_id' => 'IS NULL' ] )->get_results_as_models();
			if ( $incomplete_bookings ) {
				foreach ( $incomplete_bookings as $incomplete_booking ) {
					$incomplete_booking->update_attributes( [ 'agent_id' => $agent_model->id ] );
				}
			}
		}

		return $agent_model;
	}


	public static function get_default_agent(): OsAgentModel {
		$agent_model = new OsAgentModel();
		$agent = $agent_model->should_be_active()->set_limit( 1 )->get_results_as_models();
		if ( $agent && $agent->id ) {
			return $agent;
		} else {
            // no active agents found, try searching disabled agent
            $disabled_agent = $agent_model->set_limit( 1 )->get_results_as_models();
			// create agent only if we truly haven't found anything unfiltered
			if ( $disabled_agent && $disabled_agent->id ) {
                return $disabled_agent;
			} else {
                return self::create_default_agent();
			}
		}
	}


	public static function get_default_agent_id() {
		$agent = self::get_default_agent();

		return $agent->is_new_record() ? 0 : $agent->id;
	}

	public static function generate_summary_for_agent( OsBookingModel $booking ): void {
		if ( OsAgentHelper::count_agents() > 1 && OsSettingsHelper::is_off( 'steps_hide_agent_info' ) && $booking->agent_id && $booking->agent_id != LATEPOINT_ANY_AGENT ) { ?>
            <div class="summary-box summary-box-agent-info">
                <div class="summary-box-heading">
                    <div class="sbh-item"><?php esc_html_e( 'Agent', 'latepoint' ) ?></div>
                    <div class="sbh-line"></div>
                </div>
                <div class="summary-box-content with-media">
                    <div class="os-avatar-w"
                         style="background-image: url(<?php echo ( $booking->agent->avatar_image_id ) ? esc_url($booking->agent->get_avatar_url()) : ''; ?>)">
						<?php if ( ! $booking->agent->avatar_image_id ) {
							echo '<div class="os-avatar"><span>' . esc_html($booking->agent->get_initials()) . '</span></div>';
						} ?>
                    </div>
                    <div class="sbc-content-i">
                        <div class="sbc-main-item"><?php echo esc_html($booking->agent->full_name); ?></div>
						<?php
						if ( OsSettingsHelper::steps_show_agent_bio() ) {
							echo '<div class="os-trigger-item-details-popup sbc-link-item" data-item-details-popup-id="osItemDetailsPopupAgent_' . esc_attr($booking->agent_id) . '">' . esc_html__( 'Learn More', 'latepoint' ) . '</div>';
							echo OsAgentHelper::generate_bio( $booking->agent );
						}
						?>
                    </div>
                </div>
            </div>
			<?php
		}
	}

	public static function generate_agents_list( array $agents ): void {
		if ( ! empty( $agents ) ) { ?>
            <div class="os-agents os-animated-parent os-items os-selectable-items os-as-grid os-three-columns">
				<?php $show_agent_bio = OsSettingsHelper::steps_show_agent_bio(); ?>
				<?php if ( OsSettingsHelper::is_on( 'allow_any_agent' ) ) { ?>
                    <div class="os-animated-child os-item os-selectable-item"
                         data-summary-field-name="agent"
                         data-summary-value="<?php esc_attr_e( 'Any Agent', 'latepoint' ); ?>"
                         data-id-holder=".latepoint_agent_id"
                         data-cart-item-item-data-key="agent_id"
                         data-item-id="<?php echo esc_attr(LATEPOINT_ANY_AGENT); ?>">
                        <div class="os-animated-self os-item-i">
                            <div class="os-item-img-w os-with-avatar">
                                <div class="os-avatar"
                                     style="background-image: url(<?php echo esc_url(LATEPOINT_IMAGES_URL . 'default-avatar.jpg'); ?>);"></div>
                            </div>
                            <div class="os-item-name-w">
                                <div class="os-item-name"><?php esc_html_e( 'Any Agent', 'latepoint' ); ?></div>
                            </div>
                        </div>
                    </div>
				<?php } ?>
				<?php foreach ( $agents as $agent ) { ?>
                    <div class="os-animated-child os-item os-selectable-item <?php echo $show_agent_bio ? 'with-details' : ''; ?>"
                         tabindex="0"
                         data-summary-field-name="agent"
                         data-summary-value="<?php echo esc_attr( $agent->name_for_front ); ?>"
                         data-id-holder=".latepoint_agent_id"
                         data-cart-item-item-data-key="agent_id"
                         data-item-id="<?php echo esc_attr($agent->id); ?>">
                        <div class="os-animated-self os-item-i">
                            <div class="os-item-img-w os-with-avatar">
                                <div class="os-avatar"
                                     style="background-image: url(<?php echo esc_url($agent->avatar_url); ?>);"></div>
                            </div>
                            <div class="os-item-name-w">
                                <div class="os-item-name"><?php echo esc_html($agent->name_for_front); ?></div>
                            </div>
							<?php if ( $show_agent_bio ) { ?>
                                <div class="os-item-details-popup-btn os-trigger-item-details-popup"
                                     data-item-details-popup-id="osItemDetailsPopupAgent_<?php echo esc_attr($agent->id); ?>">
                                    <span><?php esc_html_e( 'Learn More', 'latepoint' ); ?></span></div>
							<?php } ?>
                        </div>
                    </div>
				<?php } ?>
            </div>
			<?php
			if ( $show_agent_bio ) {
				foreach ( $agents as $agent ) {
					echo OsAgentHelper::generate_bio( $agent );
				}
			}
		}
	}

	public static function generate_bio( OsAgentModel $agent ) {
		$html                = '';
		$agent_features_html = '';
		foreach ( $agent->features_arr as $feature ) {
			$agent_features_html .= '<div class="item-details-popup-feature">
        <div class="item-details-popup-feature-value">' . esc_html( $feature['value'] ) . '</div>
        <div class="item-details-popup-feature-label">' . esc_html( $feature['label'] ) . '</div>
      </div>';
		}
		$html .= '<div class="os-item-details-popup" id="osItemDetailsPopupAgent_' . $agent->id . '">
        <a href="#" class="os-item-details-popup-close"><span>' . __( 'Close Details', 'latepoint' ) . '</span><i class="latepoint-icon latepoint-icon-common-01"></i></a>
	      <div class="os-item-details-popup-inner">
        <div class="item-details-popup-head" style="background-image: url(' . esc_url( $agent->bio_image_url ) . ')">
          <h3>' . esc_html( $agent->name_for_front ) . '</h3>
          <div class="item-details-popup-title">' . esc_html( $agent->title ) . '</div>
        </div>
        <div class="item-details-popup-content">
          <img class="bio-curve" src="' . LATEPOINT_IMAGES_URL . 'white-curve.png" alt="">
          <div class="item-details-popup-features">' . $agent_features_html . '</div>
          <div class="item-details-popup-content-i">
            ' . esc_html( $agent->bio ) . '
          </div>
        </div>
        </div>
      </div>';

		return $html;
	}

	public static function generate_day_schedule_info( $filter ) {
		$today_date  = new OsWpDateTime( 'today' );
		$target_date = new OsWpDateTime( $filter->date_from ); ?>
        <div class="agent-schedule-info">
            <div class="agent-today-info">
				<?php echo ( $target_date->format( 'Y-m-d' ) == $today_date->format( 'Y-m-d' ) ) ? esc_html__( 'Today', 'latepoint' ) : esc_html($target_date->format( OsSettingsHelper::get_readable_date_format() )); ?>
				<?php

				$booking_request             = new \LatePoint\Misc\BookingRequest();
				$booking_request->agent_id   = $filter->agent_id;
				$booking_request->start_date = $target_date->format( 'Y-m-d' );
				$resources                   = OsResourceHelper::get_resources_grouped_by_day( $booking_request, $target_date, $target_date );

				$day_work_periods = [];

				$periods = [];
				foreach ( $resources[ $target_date->format( 'Y-m-d' ) ] as $resource ) {
					if ( ! empty( $resource->work_time_periods ) ) {
						foreach ( $resource->work_time_periods as $work_time_period ) {
							if ( $work_time_period->start_time == $work_time_period->end_time ) {
								continue;
							}
							$periods[] = $work_time_period->start_time . ':' . $work_time_period->end_time;
						}
					}
				}
				$periods = array_unique( $periods );
				foreach ( $periods as $work_time_period ) {
					$period                       = explode( ':', $work_time_period );
					$work_time_period             = new \LatePoint\Misc\WorkPeriod();
					$work_time_period->start_time = $period[0];
					$work_time_period->end_time   = $period[1];
					$day_work_periods[]           = $work_time_period;
				}

				$is_working_today = ! empty( $day_work_periods );
				?>
                <span class="today-status <?php echo ( $is_working_today ) ? 'is-on-duty' : 'is-off-duty'; ?>"><?php echo ( $is_working_today ) ? esc_html__( 'On Duty', 'latepoint' ) : esc_html__( 'Off Duty', 'latepoint' ); ?></span>
                <div class="today-schedule">
					<?php if ( $is_working_today ) { ?>
						<?php foreach ( $day_work_periods as $period ) {
							echo '<span>' . esc_html(OsTimeHelper::minutes_to_hours_and_minutes( $period->start_time ) . ' - ' . OsTimeHelper::minutes_to_hours_and_minutes( $period->end_time )) . '</span>';
						} ?>
					<?php } else {
						esc_html_e( 'Not Available', 'latepoint' );
					} ?>
                </div>
            </div>
            <div class="today-bookings">
				<?php esc_html_e( 'Bookings', 'latepoint' ); ?>
                <div class="today-bookings-count"><?php echo esc_html(OsBookingHelper::count_bookings( $filter )); ?></div>
            </div>
        </div>
		<?php
	}

	public static function get_full_name( $agent ) {
		return join( ' ', array( $agent->first_name, $agent->last_name ) );
	}


	public static function get_agent_ids_for_service_and_location( $service_id = false, $location_id = false ): array {
		$all_agent_ids    = OsConnectorHelper::get_connected_object_ids( 'agent_id', [
			'service_id'  => $service_id,
			'location_id' => $location_id
		] );
		$agents           = new OsAgentModel();
		$active_agent_ids = $agents->select( 'id' )->should_be_active()->get_results( ARRAY_A );
		if ( $active_agent_ids ) {
			$active_agent_ids = array_column( $active_agent_ids, 'id' );
			$all_agent_ids    = array_intersect( $active_agent_ids, $all_agent_ids );
		} else {
			$all_agent_ids = [];
		}

		return $all_agent_ids;
	}


	/**
	 * @param bool $filter_allowed_records
	 * @param array $agent_ids
	 *
	 * @return array
	 */
	public static function get_agents_list( bool $filter_allowed_records = false, array $agent_ids = [] ): array {
		$agents = new OsAgentModel();
		if ( $filter_allowed_records ) {
			$agents->filter_allowed_records();
		}

        if (!empty($agent_ids)) {
        	$agents->where_in( 'id', $agent_ids );
        }

		$agents      = $agents->get_results_as_models();
		$agents_list = [];
		if ( $agents ) {
			foreach ( $agents as $agent ) {
				$agents_list[] = [ 'value' => $agent->id, 'label' => $agent->full_name ];
			}
		}

		return $agents_list;
	}

	public static function get_avatar_url( $agent ) {
		$default_avatar = LATEPOINT_DEFAULT_AVATAR_URL;

		return OsImageHelper::get_image_url_by_id( $agent->avatar_image_id, 'thumbnail', $default_avatar );
	}

	public static function get_bio_image_url( $agent ) {
		$default_bio_image = LATEPOINT_DEFAULT_AVATAR_URL;

		return OsImageHelper::get_image_url_by_id( $agent->bio_image_id, 'large', $default_bio_image );
	}
}