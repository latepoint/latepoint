<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-form-w">
  <form action="" data-os-success-action="redirect" data-os-redirect-to="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('services', 'index'))); ?>" data-os-action="<?php echo $service->is_new_record() ? esc_attr(OsRouterHelper::build_route_name('services', 'create')) : esc_attr(OsRouterHelper::build_route_name('services', 'update')); ?>">

    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header">
          <h3><?php esc_html_e('General Information', 'latepoint'); ?></h3>
          <?php if(!$service->is_new_record()){ ?>
            <div class="os-form-sub-header-actions"><?php echo esc_html__('Service ID:', 'latepoint').esc_html($service->id); ?></div>
          <?php } ?>  
        </div>
      </div>
      <div class="white-box-content">
        <div class="os-row">
          <div class="os-col-lg-6">
            <?php echo OsFormHelper::text_field('service[name]', __('Service Name', 'latepoint'), $service->name, ['theme' => 'simple']); ?>
            <?php echo OsFormHelper::service_selector_adder_field('service[category_id]', __('Category', 'latepoint'), __('Add Category', 'latepoint'), $service_categories_for_select, $service->category_id); ?>
            <?php echo OsFormHelper::color_picker('service[bg_color]', __('Background Color', 'latepoint'), $service->bg_color); ?>
          </div>
          <div class="os-col-lg-6">
            <?php echo OsFormHelper::textarea_field('service[short_description]', __('Short Description', 'latepoint'), $service->short_description, array('rows' => 1, 'theme' => 'simple')); ?>
            <?php echo OsFormHelper::select_field('service[status]', __('Status', 'latepoint'), array(LATEPOINT_SERVICE_STATUS_ACTIVE => __('Active', 'latepoint'), LATEPOINT_SERVICE_STATUS_DISABLED => __('Disabled', 'latepoint')), $service->status); ?>
            <?php echo OsFormHelper::select_field('service[visibility]', __('Visibility', 'latepoint'), array(LATEPOINT_SERVICE_VISIBILITY_VISIBLE => __('Visible to everyone', 'latepoint'), LATEPOINT_SERVICE_VISIBILITY_HIDDEN => __('Visible only to admins and agents', 'latepoint')), $service->visibility); ?>
          </div>
        </div>
      </div>
    </div>


    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header"><h3><?php esc_html_e('Media', 'latepoint'); ?></h3></div>
      </div>
      <div class="white-box-content">

        <div class="os-row">
          <div class="os-col-lg-6">
            <div class="label-with-description">
              <h3><?php esc_html_e('Selection Image', 'latepoint'); ?></h3>
              <div class="label-desc"><?php esc_html_e('This image is used on a service selection step in the booking form.', 'latepoint'); ?></div>
            </div>
            <?php echo OsFormHelper::media_uploader_field('service[selection_image_id]', 0, __('Step Image', 'latepoint'), __('Remove Image', 'latepoint'), $service->selection_image_id); ?>
          </div>
          <div class="os-col-lg-6">
            <div class="label-with-description">
              <h3><?php esc_html_e('Service Tile Image', 'latepoint'); ?></h3>
              <div class="label-desc"><?php esc_html_e('This image is used when service is listed in [latepoint_resources] shortcode.', 'latepoint'); ?></div>
            </div>
            <?php echo OsFormHelper::media_uploader_field('service[description_image_id]', 0, __('Step Image', 'latepoint'), __('Remove Image', 'latepoint'), $service->description_image_id); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header"><h3><?php esc_html_e('Service Duration and Price', 'latepoint'); ?></h3></div>
      </div>
      <div class="white-box-content">
	      <div class="service-duration-box">
	        <div class="os-row">
	          <div class="os-col-lg-3">
	            <?php echo OsFormHelper::text_field('service[duration_name]', __('Optional Duration Name', 'latepoint'), $service->duration_name, ['theme' => 'simple']); ?>
	          </div>
	          <div class="os-col-lg-3">
	            <?php echo OsFormHelper::text_field('service[duration]', __('Duration (minutes)', 'latepoint'), $service->duration, ['class' => 'os-mask-minutes', 'theme' => 'simple']); ?>
	          </div>
	          <div class="os-col-lg-3">
	            <?php echo OsFormHelper::money_field('service[charge_amount]', __('Charge Amount', 'latepoint'), $service->charge_amount, ['theme' => 'simple']); ?>
	          </div>
	          <div class="os-col-lg-3">
	            <?php echo OsFormHelper::money_field('service[deposit_amount]', __('Deposit Amount', 'latepoint'),  $service->deposit_amount, ['theme' => 'simple']); ?>
	          </div>
	        </div>
	      </div>
		    <?php do_action('latepoint_service_edit_durations', $service); ?>
      </div>
    </div>
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header"><h3><?php esc_html_e('Display Price', 'latepoint'); ?></h3></div>
      </div>
      <div class="white-box-content">
        <div class="latepoint-message latepoint-message-subtle"><?php esc_html_e('This price is for display purposes only, it is not the price that the customer will be charged. The Charge Amount field above controls the amount that customer will be charged for. Setting both minimum and maximum price, will show a price range on the service selection step.', 'latepoint'); ?></div>
        <div class="os-row">
          <div class="os-col-lg-3">
            <?php echo OsFormHelper::money_field('service[price_min]', __('Minimum Price', 'latepoint'), $service->price_min, ['theme' => 'simple']); ?>
          </div>  
          <div class="os-col-lg-3">
            <?php echo OsFormHelper::money_field('service[price_max]', __('Maximum Price', 'latepoint'), $service->price_max, ['theme' => 'simple']); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header"><h3><?php esc_html_e('Other Settings', 'latepoint'); ?></h3></div>
      </div>
      <div class="white-box-content">
        <div class="os-row">
          <div class="os-col-lg-3">
            <?php echo OsFormHelper::text_field('service[buffer_before]', __('Buffer Before', 'latepoint'), $service->buffer_before, ['class' => 'os-mask-minutes', 'theme' => 'simple']); ?>
          </div>
          <div class="os-col-lg-3">
            <?php echo OsFormHelper::text_field('service[buffer_after]', __('Buffer After', 'latepoint'), $service->buffer_after, ['class' => 'os-mask-minutes', 'theme' => 'simple']); ?>
          </div>
          <div class="os-col-lg-3">
            <?php echo OsFormHelper::text_field('service[timeblock_interval]', __('Override Time Intervals', 'latepoint'), $service->timeblock_interval, ['class' => 'os-mask-minutes', 'theme' => 'simple']); ?>
          </div>
          <div class="os-col-lg-3">
            <?php echo OsFormHelper::select_field('service[override_default_booking_status]', __('Override status for bookings', 'latepoint'), array_merge(['' => __('Use from general settings', 'latepoint')], OsBookingHelper::get_statuses_list()), $service->override_default_booking_status); ?>
          </div>
        </div>
      </div>
    </div>

		<?php if(OsRolesHelper::can_user('connection__edit')){ ?>
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header">
          <h3><?php esc_html_e('Agents Who Offer This Service', 'latepoint'); ?></h3>
          <div class="os-form-sub-header-actions">
            <?php echo OsFormHelper::checkbox_field('select_all_agents', __('Select All', 'latepoint'), 'off', false, ['class' => 'os-select-all-toggler']); ?>
          </div>
        </div>
      </div>
      <div class="white-box-content">

        <div class="os-complex-connections-selector">
        <?php if($agents){
          foreach($agents as $agent){
            $is_connected = $service->is_new_record() ? true : $service->has_agent($agent->id);
            $is_connected_value = $is_connected ? 'yes' : 'no';
            if($locations){
              if(count($locations) > 1){
                // multiple locations
                $locations_count = $service->count_number_of_connected_locations($agent->id);
                if($locations_count == count($locations)){
                  $locations_count_string = __('All', 'latepoint');
                }else{
                  $locations_count_string = $service->is_new_record() ? __('All', 'latepoint') : $locations_count.'/'.count($locations);
                } ?>
                <div class="connection <?php echo $is_connected ? 'active' : ''; ?>">
                  <div class="connection-i selector-trigger">
                    <div class="connection-avatar"><img src="<?php echo esc_url($agent->get_avatar_url()); ?>"/></div>
                    <h3 class="connection-name"><?php echo esc_html($agent->full_name); ?></h3>
                    <div class="selected-connections" data-all-text="<?php echo esc_attr__('All', 'latepoint'); ?>">
                      <strong><?php echo esc_html($locations_count_string); ?></strong>
                      <span><?php echo  esc_html__('Locations Selected', 'latepoint'); ?></span>
                    </div>
                    <a href="#" class="customize-connection-btn"><i class="latepoint-icon latepoint-icon-ui-46"></i><span><?php echo esc_html__('Customize', 'latepoint'); ?></span></a>
                  </div><?php
                  if($locations){ ?>
                    <div class="connection-children-list-w">
                      <h4>
                          <?php
                          // translators: %s is the name of an agent
                          echo esc_html(sprintf(__('Select locations where %s will be offering this service:', 'latepoint'), $agent->first_name)); ?></h4>
                      <ul class="connection-children-list"><?php
                        foreach($locations as $location){ 
                          $is_connected = $service->is_new_record() ? true : $location->has_agent_and_service($agent->id, $service->id);
                          $is_connected_value = $is_connected ? 'yes' : 'no'; ?>
                          <li class="<?php echo $is_connected ? 'active' : ''; ?>">
                            <?php echo OsFormHelper::hidden_field('service[agents][agent_'.$agent->id.'][location_'.$location->id.'][connected]', $is_connected_value, array('class' => 'connection-child-is-connected'));?>
                            <?php echo esc_html($location->name); ?>
                          </li>
                        <?php } ?>
                      </ul>
                    </div><?php
                  } ?>
                </div><?php
              }else{
                // one location
                $location = $locations[0];
                $is_connected = $service->is_new_record() ? true : $location->has_agent_and_service($agent->id, $service->id);
                $is_connected_value = $is_connected ? 'yes' : 'no';
                ?>
                <div class="connection <?php echo $is_connected ? 'active' : ''; ?>">
                  <div class="connection-i selector-trigger">
                    <div class="connection-avatar"><img src="<?php echo esc_url($agent->get_avatar_url()); ?>"/></div>
                    <h3 class="connection-name"><?php echo esc_html($agent->full_name); ?></h3>
                    <?php echo OsFormHelper::hidden_field('service[agents][agent_'.$agent->id.'][location_'.$location->id.'][connected]', $is_connected_value, array('class' => 'connection-child-is-connected'));?>
                  </div>
                </div>
                <?php
              }
            }
          }
        }else{ ?>
          <div class="no-results-w">
            <div class="icon-w"><i class="latepoint-icon latepoint-icon-users"></i></div>
            <h2><?php esc_html_e('No Existing Agents Found', 'latepoint'); ?></h2>
            <a href="<?php echo esc_url(OsRouterHelper::build_link(['agents', 'new_form'] )); ?>" class="latepoint-btn"><i class="latepoint-icon latepoint-icon-plus"></i><span><?php esc_html_e('Add First Agent', 'latepoint'); ?></span></a>
          </div> <?php
        }
        ?>
        </div>
      </div>
    </div>
		<?php } ?>
		<?php if(OsRolesHelper::can_user('resource_schedule__edit')){ ?>
    <div class="white-box">
      <div class="white-box-header">
        <div class="os-form-sub-header">
          <h3><?php esc_html_e('Service Schedule', 'latepoint'); ?></h3>
          <div class="os-form-sub-header-actions">
            <?php echo OsFormHelper::checkbox_field('is_custom_schedule', __('Set Custom Schedule', 'latepoint'), 'on', $is_custom_schedule, array('data-toggle-element' => '.custom-schedule-wrapper')); ?>
          </div>
        </div>
      </div>
      <div class="white-box-content">
        <div class="custom-schedule-wrapper" style="<?php if(!$is_custom_schedule) echo 'display: none;'; ?>">
          <?php
          $filter = new \LatePoint\Misc\Filter();
          if(!$service->is_new_record()) $filter->service_id = $service->id; ?>
          <?php OsWorkPeriodsHelper::generate_work_periods($custom_work_periods, $filter, $service->is_new_record()); ?>
        </div>
        <div class="custom-schedule-wrapper" style="<?php if($is_custom_schedule) echo 'display: none;'; ?>">
          <div class="latepoint-message latepoint-message-subtle"><?php esc_html_e('This service is using general schedule which is set in main settings', 'latepoint'); ?></div>
        </div>
      </div>
    </div>

    <?php if(!$service->is_new_record()){ ?>

        
        <div class="white-box">
          <div class="white-box-header">
            <div class="os-form-sub-header"><h3><?php esc_html_e('Days With Custom Schedules', 'latepoint'); ?></h3></div>
          </div>
          <div class="white-box-content">
            <div class="latepoint-message latepoint-message-subtle"><?php esc_html_e('Service shares custom daily schedules that you set in general settings for your company, however you can add additional days with custom hours which will be specific to this service only.', 'latepoint'); ?></div>
            <?php OsWorkPeriodsHelper::generate_days_with_custom_schedule(['service_id' => $service->id]); ?>
          </div>
        </div>
        <div class="white-box">
          <div class="white-box-header">
            <div class="os-form-sub-header"><h3><?php esc_html_e('Holidays & Days Off', 'latepoint'); ?></h3></div>
          </div>
          <div class="white-box-content">
            <div class="latepoint-message latepoint-message-subtle"><?php esc_html_e('Service uses the same holidays you set in general settings for your company, however you can add additional holidays for this service here.', 'latepoint'); ?></div>
            <?php OsWorkPeriodsHelper::generate_off_days(['service_id' => $service->id]); ?>
          </div>
        </div>
    <?php } ?>
    <?php } ?>
    <?php do_action('latepoint_service_form_after', $service); ?>
    <div class="os-form-buttons os-flex">
    <?php 
      if($service->is_new_record()){
        echo OsFormHelper::hidden_field('service[id]', '');
        echo OsFormHelper::button('submit', __('Add Service', 'latepoint'), 'submit', ['class' => 'latepoint-btn']); 
      }else{
        echo OsFormHelper::hidden_field('service[id]', $service->id);
        if(OsRolesHelper::can_user('service__edit')) {
	        echo OsFormHelper::button('submit', __('Save Changes', 'latepoint'), 'submit', ['class' => 'latepoint-btn']);
        }
        if(OsRolesHelper::can_user('service__delete')) {
	        echo '<a href="#" class="latepoint-btn latepoint-btn-danger remove-service-btn" style="margin-left: auto;" 
                data-os-prompt="' . esc_attr__('Are you sure you want to remove this service? It will remove all appointments associated with it. You can also change status to disabled if you want to temprorary disable it instead.', 'latepoint') . '" 
                data-os-redirect-to="' . esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('services', 'index'))) . '" 
                data-os-params="' . esc_attr(OsUtilHelper::build_os_params(['id' => $service->id], 'destroy_service_'.$service->id)) . '" 
                data-os-success-action="redirect" 
                data-os-action="' . esc_attr(OsRouterHelper::build_route_name('services', 'destroy')) . '">' . esc_html__('Delete Service', 'latepoint') . '</a>';
        }
      }

      ?>
    </div>
	  <?php wp_nonce_field($service->is_new_record() ? 'new_service' : 'edit_service_'.$service->id); ?>
  </form>
</div>