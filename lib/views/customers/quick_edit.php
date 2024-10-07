<?php
/**
 * @var $customer OsCustomerModel
 **/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
	<div class="os-form-w quick-customer-form-w <?php echo ($customer->is_new_record()) ? 'is-new-customer' : 'is-existing-customer' ;?>" data-refresh-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('customers', 'quick_edit')); ?>">
		<form action=""
			    data-route-name="<?php echo ($customer->is_new_record()) ? esc_attr(OsRouterHelper::build_route_name('customers', 'create')) : esc_attr(OsRouterHelper::build_route_name('customers', 'update')); ?>"
			    class="customer-quick-edit-form">
			<div class="os-form-header">
	      <?php if($customer->is_new_record()){ ?>
	        <h2><?php esc_html_e('New Customer', 'latepoint'); ?></h2>
	      <?php }else{ ?>
	        <h2><?php esc_html_e('Edit Customer', 'latepoint'); ?></h2>
	      <?php } ?>
	      <a href="#" class="latepoint-side-panel-close latepoint-side-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>
	    </div>
	    <div class="os-form-content">
		    <?php if(!$customer->is_new_record()){ ?>
		    <div class="quick-booking-info">
			    <?php echo '<span>'.esc_html__('Customer ID:', 'latepoint').'</span><strong>'. esc_html($customer->id).'</strong>'; ?>
			    <?php if (OsAuthHelper::get_current_user()->has_capability('activity__view')) echo '<a href="#" data-customer-id="' . esc_attr($customer->id) . '" data-route="' . esc_attr(OsRouterHelper::build_route_name('customers', 'view_customer_log')) . '" class="quick-customer-form-view-log-btn"><i class="latepoint-icon latepoint-icon-clock"></i>' . esc_html__('History', 'latepoint') . '</a>'; ?>
		    </div>
		    <?php } ?>
					<div class="fields-with-avatar">
						<div class="avatar-column">
							<div class="avatar-uploader-w">
								<?php echo OsFormHelper::media_uploader_field('customer[avatar_image_id]', 0, __('Set Avatar', 'latepoint'), __('Remove Avatar', 'latepoint'), $customer->avatar_image_id, [], [], true, true); ?>
							</div>
						</div>
						<div class="field-column">
					    <?php echo OsFormHelper::text_field('customer[first_name]', __('First Name', 'latepoint'), $customer->first_name, ['theme' => 'simple', 'validate' => $customer->get_validations_for_property('first_name')]); ?>
						</div>
						<div class="field-column">
					    <?php echo OsFormHelper::text_field('customer[last_name]', __('Last Name', 'latepoint'), $customer->last_name, ['theme' => 'simple', 'validate' => $customer->get_validations_for_property('last_name')]); ?>
						</div>
					</div>

					<div class="os-form-sub-header">
						<h3><?php esc_html_e('Contact Info', 'latepoint'); ?></h3>
					</div>
					<div class="os-row">
						<div class="os-col-lg-12">
					    <?php echo OsFormHelper::text_field('customer[email]', __('Email Address', 'latepoint'), $customer->email, ['theme' => 'simple', 'validate' => $customer->get_validations_for_property('email')]); ?>
						</div>
						<div class="os-col-lg-12">
					    <?php echo OsFormHelper::phone_number_field('customer[phone]', __('Phone Number', 'latepoint'), $customer->phone, ['theme' => 'simple', 'validate' => $customer->get_validations_for_property('phone')]); ?>
						</div>
					</div>
					<div class="os-form-sub-header">
						<h3><?php esc_html_e('Notes', 'latepoint'); ?></h3>
					</div>
					<div class="os-row">
					  <div class="os-col-lg-12">
					    <?php echo OsFormHelper::textarea_field('customer[notes]', __('Notes left by the customer', 'latepoint'), $customer->notes, ['theme' => 'simple', 'rows' => 3]); ?>
					  </div>
					</div>
					<div class="os-row">
					  <div class="os-col-lg-12">
					    <?php echo OsFormHelper::textarea_field('customer[admin_notes]', __('Admin notes (visible only to admins)', 'latepoint'), $customer->admin_notes, ['theme' => 'simple', 'rows' => 3]); ?>
					  </div>
					</div>
		      <?php if(!$customer->is_new_record()){ ?>
				    <div class="customer-password-info <?php echo ($customer->is_guest) ? 'password-not-set' : 'password-set'; ?>">
					    <?php if($customer->is_guest) {
						    esc_html_e('Guest Account', 'latepoint');
					    }else{
								esc_html_e('Password Protected', 'latepoint');
								echo '<a href="#" data-os-success-action="reload" 
															    data-os-params="'.esc_attr(OsUtilHelper::build_os_params(['id' => $customer->id])).'" 
															    data-os-prompt="'.esc_attr__('Are you sure you want to allow this customer to book without logging in?', 'latepoint').'"
															    data-os-action="'.esc_attr(OsRouterHelper::build_route_name('customers', 'set_as_guest')).'">'.esc_html__('Convert to Guest', 'latepoint').'</a>';
							} ?>
				    </div>
					<?php } ?>
					<?php if(!$customer->is_new_record() && OsAuthHelper::wp_users_as_customers()){ ?>
						<div class="connected-wp-user-status">
					    <?php
					    if($customer->wordpress_user_id){
					      echo '<div class="connected-note">'.esc_html__('Connected', 'latepoint').'</div>';
								echo '<div class="connected-buttons"> <a target="_blank" href="'.esc_attr(get_edit_user_link($customer->wordpress_user_id)).'"><i class="latepoint-icon latepoint-icon-external-link"></i><span>'.esc_html__('View Profile', 'latepoint').'</span></a> <span>or</span> <a href="#" data-os-success-action="reload" data-os-params="'.esc_attr(OsUtilHelper::build_os_params(['customer_id' => $customer->id])).'" data-os-action="'.esc_attr(OsRouterHelper::build_route_name('customers', 'disconnect_from_wp_user')).'"><i class="latepoint-icon latepoint-icon-slash"></i><span>'.esc_html__('Disconnect', 'latepoint').'</a></div>';
					    }else{
					      echo '<div class="connected-note">'.esc_html__('Not Connected', 'latepoint').'</div>';
								echo '<div class="connected-buttons"> <a href="#" data-os-success-action="reload" data-os-params="'.esc_attr(OsUtilHelper::build_os_params(['customer_id' => $customer->id])).'" data-os-action="'.esc_attr(OsRouterHelper::build_route_name('customers', 'connect_to_wp_user')).'"><i class="latepoint-icon latepoint-icon-link-2"></i><span>'.esc_html__('Connect', 'latepoint').'</span></a></div>';
					    }
					    ?>
					  </div>
					<?php } ?>

		    <?php
		    /**
		     * Content that goes after customer edit form
		     *
		     * @since 5.0.0
		     * @hook latepoint_customer_edit_form_after
		     *
		     * @param {OsCustomerModel} $customer Customer object that is being edited
		     */
		    do_action('latepoint_customer_edit_form_after', $customer); ?>

			<?php if(!$customer->is_new_record()){ ?>
				<div class="customer-appointments">
					<div class="os-form-sub-header">
						<h3><?php esc_html_e('Appointments', 'latepoint'); ?></h3>
						<div class="os-form-sub-header-actions">
							<a href="#" class="latepoint-btn latepoint-btn-sm latepoint-btn-link" <?php echo OsOrdersHelper::quick_order_btn_html(false, ['customer_id' => $customer->id]); ?>>
              <i class="latepoint-icon latepoint-icon-plus"></i><span><?php esc_html_e('Add', 'latepoint'); ?></span>
	            </a>
						</div>
					</div>
					<?php
					$customer_bookings = $customer->get_bookings(false, true);
					if($customer_bookings){
						echo '<div class="customer-appointments-list">';
						foreach($customer_bookings as $booking){
							?>
							<div class="order-item" <?php echo OsBookingHelper::quick_booking_btn_html($booking->id); ?>>
					      <div class="avatar-w" style="background-image: url(<?php echo esc_url($booking->agent->get_avatar_url()); ?>);">
					        <div class="agent-info-tooltip"><?php echo esc_html($booking->agent->full_name); ?></div>
					      </div>
								<div class="oi-info">
									<div class="oi-service-name"><?php echo esc_html($booking->service->name); ?></div>
									<div class="oi-date-w">
										<div class="oi-date-i">
											<span class="oi-date"><?php echo esc_html($booking->nice_start_date); ?></span>, <span class="appointment-time"><?php echo esc_html(implode('-', array($booking->nice_start_time, $booking->nice_end_time))); ?></span>
										</div>
									</div>
									</div>
							</div>
							<?php
						}
						echo '</div>';
					}else{ ?>
					  <div class="no-results-w">
						  <?php esc_html_e('Customer does not have any bookings', 'latepoint'); ?>
					  </div>
						<?php
					} ?>
				</div>
			<?php } ?>

	    </div>
	    <div class="os-form-buttons os-quick-form-buttons">
	    <?php
	      if($customer->is_new_record()){
	        if(OsRolesHelper::can_user('customer__create')) echo '<button name="submit" type="submit" class="latepoint-btn latepoint-btn-block latepoint-btn-lg">'.esc_html__('Create Customer', 'latepoint').'</button>';
	      }else{
	        if(OsRolesHelper::can_user('customer__edit')){
						echo '<div class="os-full">';
							echo '<button name="submit" type="submit" class="latepoint-btn latepoint-btn-block latepoint-btn-lg">'.esc_html__('Save Changes', 'latepoint').'</button>';
						echo '</div>';
	        }
	        if(OsRolesHelper::can_user('customer__delete')) {
						echo '<div class="os-compact">';
		        echo '<a href="#" class=" remove-customer-btn latepoint-btn latepoint-btn-secondary latepoint-btn-lg latepoint-btn-just-icon" 
	                data-os-prompt="' . esc_attr__('Are you sure you want to delete this customer? It will remove all appointments and transactions associated with this customer.', 'latepoint') . '" 
	                data-os-redirect-to="' . esc_attr(OsRouterHelper::build_link(OsRouterHelper::build_route_name('customers', 'index'))) . '" 
	                data-os-params="' . esc_attr(OsUtilHelper::build_os_params(['id' => $customer->id], 'destroy_customer_'.$customer->id)) . '" 
	                data-os-success-action="redirect" 
	                data-os-action="' . esc_attr(OsRouterHelper::build_route_name('customers', 'destroy') ). '"
	                title="' . esc_attr__('Delete Customer', 'latepoint') . '">
		                <i class="latepoint-icon latepoint-icon-trash1"></i>
	                </a>';
						echo '</div>';
	        }
	      }
			?>
			</div>
      <?php
      echo OsFormHelper::hidden_field('customer[id]', $customer->id);
			wp_nonce_field($customer->is_new_record() ? 'new_customer' : 'edit_customer_'.$customer->id);
			?>
	  </form>
	</div>
