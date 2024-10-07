/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

function latepoint_init_version5_intro(){
  if(jQuery('.improvement-install-pro').length){
    let $install_btn = jQuery('.improvement-install-pro');
    var data = {
      action: latepoint_helper.route_action,
      route_name: $install_btn.data('route-name'),
      params: {},
      return_format: 'json'
    }
    jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      $install_btn.removeClass('os-loading');
      if(response.status == 'success'){
        $install_btn.addClass('is-installed').find('span').html(response.message);
      }else{
        $install_btn.addClass('is-not-installed').find('span').html(response.message);
      }
    }
  });
  }
}

function latepoint_build_and_save_step_order(){
  var $steps_wrapper = jQuery('.os-ordered-steps');
  let steps_in_order = [];
  $steps_wrapper.find('.os-ordered-step').each(function(index){
    if(jQuery(this).find('.os-ordered-step-children').length){
      jQuery(this).find('.os-ordered-step-child').each(function(){
        steps_in_order.push(jQuery(this).data('step-code'));
      });
    }else{
      steps_in_order.push(jQuery(this).data('step-code'));
    }
  });
  var data = { action: latepoint_helper.route_action, route_name: $steps_wrapper.data('route-name'), params: {steps_order: steps_in_order.join(',')}, return_format: 'json' }
  jQuery('.latepoint-lightbox-heading').addClass('os-loading');
  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      jQuery('.latepoint-lightbox-heading').removeClass('os-loading');
      latepoint_add_lightbox_notification(response.message, response.status);
    }
  });
}

function latepoint_init_step_reordering(){
  jQuery('.os-ordered-step-expand').on('click', function(){
    jQuery(this).closest('.os-ordered-step').toggleClass('is-expanded');
    return false;
  });


  // Steps Order Dragging
  dragula([jQuery('.os-ordered-steps')[0]], {
    moves: function (el, container, handle) {
      return handle.classList.contains('os-ordered-step-drag-handle');
    },
  }).on('drop', function(el){
    latepoint_build_and_save_step_order();
  });

  jQuery('.os-ordered-step-children').each(function(){
    let step_holder = jQuery(this)
    // Child steps Order Dragging
    dragula([step_holder[0]], {
      moves: function (el, container, handle) {
        return handle.classList.contains('os-ordered-step-child-drag-handle');
      },
    }).on('drop', function(el){
      latepoint_build_and_save_step_order();
    });
  });
}


function latepoint_init_json_view($pre_element = false){
  if(!$pre_element){
    // if pre is not provided -search for all unitialised ones
    $pre_element = jQuery('pre.format-json:not(.json-document)');
  }
  if($pre_element.length){
    $pre_element.each(function(){
      let json_data = JSON.parse(jQuery(this).html());
      jQuery(this).jsonViewer(json_data);
    });
  }
}

function latepoint_init_accordions(){
  jQuery('.latepoint-admin').on('click', '.os-accordion-title', function(){
    jQuery(this).closest('.os-accordion-wrapper').toggleClass('is-open');
    return false;
  });
}


function latepoint_init_sticky_side_menu(){
  jQuery('.os-sticky-side-menu a').on('click', function(){
    jQuery('.os-sticky-side-menu li.os-active').removeClass('os-active');
    jQuery(this).closest('li').addClass('os-active');
    let section_anchor = jQuery(this).data('section-anchor');
    let position = jQuery('.section-anchor#'+section_anchor).offset();
    jQuery('html').animate({ scrollTop: position.top }, 300);
    return false;
  });
}

function latepoint_init_template_library(){
  jQuery('.os-templates-wrapper .template-type-selector').on('click', function(){
    jQuery(this).toggleClass('is-selected');
    let user_type = jQuery(this).data('user-type');
    jQuery('.os-template-items[data-user-type="'+user_type+'"]').toggleClass('hidden');
    return false;
  });

  jQuery('.os-templates-wrapper .os-template-item').on('click', function(){
    let $this = jQuery(this);
    $this.closest('.os-templates-list').find('.os-template-item.selected').removeClass('selected');
    $this.addClass('selected');
    let templateId = $this.data('id');
    jQuery('.os-template-preview').hide();
    jQuery('.os-template-preview[data-id="'+ templateId+'"]').show();
    jQuery('.os-no-template-selected-message').hide();
    jQuery('.os-template-use-button-wrapper').removeClass('hidden');
    return false;
  });

  jQuery('.latepoint-select-template-btn').on('click', function(){
    let $btn = jQuery(this);
    let route_name = $btn.data('route');
    let action_id = $btn.data('action-id');
    let action_type = $btn.data('action-type');
    $btn.addClass('os-loading');

    let data = {  action: latepoint_helper.route_action,
                  route_name: route_name,
                  params: {
                    template_id: jQuery('.os-template-item.selected').data('id'),
                    action_id: action_id,
                    action_type: action_type
                  },
                  return_format: 'json' }
    jQuery.ajax({
      type: 'post',
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: (response) => {
        $btn.removeClass('os-loading');
        if(response.status === latepoint_helper.response_status.success){
          let $action_form = jQuery('.process-action-form[data-id="'+action_id+'"]');
          $action_form.find('.process-action-settings').html(response.message);
          latepoint_init_process_action_form($action_form);
          latepoint_close_side_panel();
        }else{
          alert("Error!");
        }
      }
    });

    return false;
  });
}

function latepoint_init_default_form_fields_settings(){

  if(jQuery('.os-default-fields').length){
    jQuery('.os-default-field input[type="checkbox"], .os-default-field select').on('change', (event) => {
      latepoint_update_default_form_fields_settings();
    });

    jQuery('.os-default-field .os-toggler').on('ostoggler:toggle', (event) => {
      if(jQuery(event.currentTarget).hasClass('off')){
        jQuery(event.currentTarget).closest('.os-default-field').addClass('is-disabled');
      }else{
        jQuery(event.currentTarget).closest('.os-default-field').removeClass('is-disabled');
      }
      latepoint_update_default_form_fields_settings();
    });
  }
}

function latepoint_update_default_form_fields_settings(){
  var $wrapper = jQuery('.os-default-fields');

  var form_data = new FormData($wrapper.find('form')[0]);
  var data = {  action: latepoint_helper.route_action,
    route_name: $wrapper.data('route'),
    params: latepoint_formdata_to_url_encoded_string(form_data),
    return_format: 'json' }

  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: (response) => {
      latepoint_add_notification(response.message);
    }
  });
}

function latepoint_init_side_menu(){
  jQuery('.menu-toggler').on('click', function(){
    var layout_style = 'full';
    if(jQuery('.latepoint-side-menu-w').hasClass('side-menu-full')){
      layout_style = 'compact';
      jQuery('.latepoint-side-menu-w').addClass('side-menu-compact').removeClass('side-menu-full');
    }else{
      jQuery('.latepoint-side-menu-w').addClass('side-menu-full').removeClass('side-menu-compact');
    }
    var route_name = jQuery(this).data('route');
    var data = { action: latepoint_helper.route_action, route_name: route_name, params: { menu_layout_style: layout_style }, layout: 'none', return_format: 'json' }
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(data){
      }
    });
    return false;
  });
}

function latepoint_init_grouped_bookings_form(){

}

function latepoint_quick_order_customer_cleared(){
  latepoint_init_input_masks(jQuery('.quick-order-form-w .customer-quick-edit-form-w'));
}

function latepoint_quick_order_customer_selected(){
  latepoint_init_input_masks(jQuery('.quick-order-form-w .customer-quick-edit-form-w'));
  jQuery('.customer-info-w').removeClass('selecting').addClass('selected');
}

function latepoint_custom_day_removed($elem){
  $elem.closest('.custom-day-work-period').fadeOut(300, function(){ jQuery(this).remove()});
}


function latepoint_count_active_connections($connection_wrapper){
  var connected_services_count = $connection_wrapper.find('.connection-children-list li.active').length;
  var all_services_count = $connection_wrapper.find('.connection-children-list li').length;
  if(connected_services_count == all_services_count){
    connected_services_count = jQuery('.selected-connections').data('all-text');
    jQuery('.selected-connections').removeClass('not-all-selected');
  }else{
    connected_services_count = connected_services_count + '/' + all_services_count;
    jQuery('.selected-connections').addClass('not-all-selected');
    $connection_wrapper.closest('.white-box').find('.os-select-all-toggler').prop('checked', false);
  }
  $connection_wrapper.find('.selected-connections strong').text(connected_services_count);
}

function latepoint_custom_field_removed($elem){
  $elem.closest('.os-form-block').remove();
}

function latepoint_coupon_removed($elem){
  $elem.closest('.os-coupon-form').remove();
}

function latepoint_reminder_removed($elem){
  $elem.closest('.os-reminder-form').remove();
}

function latepoint_init_form_blocks(){
  jQuery('.latepoint-content-w').on('click', '.os-form-block-header', function(){
    jQuery(this).closest('.os-form-block').toggleClass('os-is-editing');
    return false;
  });
  jQuery('.latepoint-content-w').on('keyup', '.os-form-block-name-input', function(){
    jQuery(this).closest('.os-form-block').find('.os-form-block-name').text(jQuery(this).val());
  });
}


function latepoint_init_coupons_form(){
  jQuery('.latepoint-content-w').on('click', '.os-coupon-form-info', function(){
    jQuery(this).closest('.os-coupon-form').toggleClass('os-is-editing');
    return false;
  });
  jQuery('.latepoint-content-w').on('change', 'select.os-coupon-medium-select', function(){
    if(jQuery(this).val() == 'email'){
      jQuery(this).closest('.os-coupon-form').find('.os-coupon-email-subject').show();
    }else{
      jQuery(this).closest('.os-coupon-form').find('.os-coupon-email-subject').hide();
    }
  });
  jQuery('.latepoint-content-w').on('keyup', '.os-coupon-name-input', function(){
    jQuery(this).closest('.os-coupon-form').find('.os-coupon-name').text(jQuery(this).val());
  });
  jQuery('.latepoint-content-w').on('keyup', '.os-coupon-code-input', function(){
    jQuery(this).closest('.os-coupon-form').find('.os-coupon-code').text(jQuery(this).val());
  });
}

function latepoint_init_reminders_form(){
  jQuery('.latepoint-content-w').on('click', '.os-reminder-form-info', function(){
    jQuery(this).closest('.os-reminder-form').toggleClass('os-is-editing');
    return false;
  });
  jQuery('.latepoint-content-w').on('change', 'select.os-reminder-medium-select', function(){
    if(jQuery(this).val() == 'email'){
      jQuery(this).closest('.os-reminder-form').find('.os-reminder-email-subject').show();
    }else{
      jQuery(this).closest('.os-reminder-form').find('.os-reminder-email-subject').hide();
    }
  });
  jQuery('.latepoint-content-w').on('keyup', '.os-reminder-name-input', function(){
    jQuery(this).closest('.os-reminder-form').find('.os-reminder-name').text(jQuery(this).val());
  });
}

function latepoint_custom_field_saved($elem){
}

function latepoint_init_custom_day_schedule(){
  latepoint_init_input_masks(jQuery('.latepoint-lightbox-w .custom-day-schedule-w'));

  jQuery('.period-type-selector').on('change', function(){
    jQuery(this).closest('.custom-day-calendar').attr('data-period-type', jQuery(this).val());
    jQuery('.custom-day-calendar').attr('data-picking', 'start').data('picking', 'start');
    if(jQuery(this).val() == 'range'){
      jQuery('.custom-day-calendar-head .calendar-heading').text(jQuery('.custom-day-calendar-head .calendar-heading').data('label-start'));
      jQuery('.custom-day-calendar #start_custom_date').trigger('focus');
    }else{
      jQuery('.custom-day-calendar .os-day.selected').removeClass('selected');
      jQuery('.latepoint-lightbox-footer').hide();
      jQuery('.custom-day-calendar-head .calendar-heading').text(jQuery('.custom-day-calendar-head .calendar-heading').data('label-single'));
    }
  });


  jQuery('#custom_day_calendar_month, #custom_day_calendar_year').on('change', function(){
    var $calendar = jQuery('.custom-day-calendar-month');
    var route_name = $calendar.data('route');
    $calendar.addClass('os-loading');
    var target_date_string = jQuery('#custom_day_calendar_year').val() + '-' + jQuery('#custom_day_calendar_month').val() + '-01';
    var data = { action: latepoint_helper.route_action, route_name: route_name, params: { target_date_string: target_date_string }, layout: 'none', return_format: 'json' }
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(data){
        $calendar.removeClass('os-loading');
        if(data.status === "success"){
          $calendar.html(data.message);
        }else{
          // console.log(data.message);
        }
      }
    });
  });



  jQuery('.custom-day-calendar').on('focus', '#start_custom_date', function(){
    jQuery('.custom-day-calendar-head .calendar-heading').text(jQuery('.custom-day-calendar-head .calendar-heading').data('label-start'));
    jQuery('.custom-day-calendar').attr('data-picking', 'start').data('picking', 'start');
  });

  jQuery('.custom-day-calendar').on('focus', '#end_custom_date', function(){
    jQuery('.custom-day-calendar-head .calendar-heading').text(jQuery('.custom-day-calendar-head .calendar-heading').data('label-end'));
    jQuery('.custom-day-calendar').attr('data-picking', 'end').data('picking', 'end');
  });

  jQuery('.custom-day-calendar').on('click', '.os-day', function(){
    var $this = jQuery(this);
    $this.closest('.custom-day-calendar').find('.os-day.selected').removeClass('selected');
    $this.addClass('selected');

    if(jQuery('.custom-day-calendar').data('picking') == 'start'){
      jQuery('.custom-day-settings-w #start_custom_date').val($this.data('date')).trigger('keyup');
      if(jQuery('.period-type-selector').val() == 'range'){
        jQuery('.custom-day-calendar #end_custom_date').trigger('focus');
        if(!jQuery('.custom-day-calendar #end_custom_date').val()) return false;
      }
    }else{
      jQuery('.custom-day-settings-w #end_custom_date').val($this.data('date')).trigger('keyup');
    }
    jQuery('.latepoint-lightbox-footer').slideDown(200);
    if(jQuery('.custom-day-calendar').data('show-schedule') == 'yes') jQuery('.latepoint-lightbox-w').removeClass('hide-schedule');
    return false;
  });
}

function latepoint_init_updates_page(){

}

function latepoint_calendar_set_month_label(){
  jQuery('.os-current-month-label .current-month').text(jQuery('.os-monthly-calendar-days-w.active').data('calendar-month-label'));
  jQuery('.os-current-month-label .current-year').text(jQuery('.os-monthly-calendar-days-w.active').data('calendar-year'));
}


function latepoint_init_element_togglers(){
  jQuery('[data-toggle-element]').on('click', function(){
    var $this = jQuery(this);
    $this.closest('.os-form-checkbox-group').toggleClass('is-checked');
    jQuery($this.data('toggle-element')).toggle();
  });
}


function latepoint_init_color_picker(){
  if(jQuery('.latepoint-color-picker').length){
    jQuery('.latepoint-color-picker').each(function(){
      var color = jQuery(this).data('color');
      var picker = jQuery(this)[0];
      var $picker_wrapper = jQuery(this).closest('.latepoint-color-picker-w');
      Pickr.create({
        el: picker,
        default: color,
        comparison: false,
        useAsButton: true,
        components: {

            // Main components
            preview: false,
            opacity: false,
            hue: true,

            // Input / output Options
            interaction: {
                input: false,
                clear: false,
                save: true
            }
        },
        onChange(hsva, instance) {
          $picker_wrapper.find('.os-form-control').val(hsva.toHEX().toString());
        },
      });
    });
  }
}


function latepoint_lightbox_close(){
  jQuery('body').removeClass('latepoint-lightbox-active');
  jQuery('.latepoint-lightbox-w').remove();
}

function latepoint_reload_select_service_categories(){
  jQuery('.service-selector-adder-field-w').each(function(){
    var $trigger_elem = jQuery(this);
    var route = jQuery('.service-selector-adder-field-w').find('select').data('select-source');
    var data = { action: latepoint_helper.route_action, route_name: route, params: '', return_format: 'json' }
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(response){
        $trigger_elem.removeClass('os-loading');
        if(response.status === "success"){
          latepoint_lightbox_close();
          $trigger_elem.find('select').html(response.message);
          $trigger_elem.find('select option:last').attr('selected', 'selected');
        }else{
          alert(response.message, 'error');
        }
      }
    });
  });
}

function latepoint_wizard_item_editing_cancelled(response){
  jQuery('.os-wizard-setup-w').removeClass('is-sub-editing');
  jQuery('.os-wizard-footer').show();
  jQuery('.os-wizard-footer .os-wizard-next-btn').show();
  if(response.show_prev_btn){
    jQuery('.os-wizard-footer .os-wizard-prev-btn').show();
  }
}


function latepoint_reload_week_view_calendar(start_date = false){
  var service_id = (jQuery('.cc-availability-toggler #overlay_service_availability').val() == 'on') ? jQuery('.calendar-service-selector').val() : false;
  var agent_id = jQuery('.calendar-agent-selector').val();
  var location_id = jQuery('.calendar-location-selector').val();
  var calendar_start_date = (start_date) ? start_date : jQuery('.calendar-start-date').val();
  latepoint_load_calendar(calendar_start_date, agent_id, location_id, service_id);
}

function latepoint_init_work_period_form(){
  latepoint_mask_timefield(jQuery('.os-time-input-w .os-mask-time'));
}

function latepoint_close_side_panel(){
  latepoint_close_quick_availability_form();
  jQuery('.latepoint-side-panel-w').remove();
}

function reload_process_jobs_table(){
  if(jQuery('table.os-reload-on-booking-update').length) latepoint_filter_table(jQuery('table.os-reload-on-booking-update'), jQuery('table.os-reload-on-booking-update'));
}


function latepoint_transaction_removed($trigger){
  $trigger.closest('.quick-add-transaction-box-w').remove();
  latepoint_reload_balance_and_payments();
}

function latepoint_reload_widget($widget_elem){
  var form_data = $widget_elem.find('select, input').serialize();
  var data = { action: latepoint_helper.route_action, route_name: $widget_elem.data('os-reload-action'), params: form_data, return_format: 'json' }
  $widget_elem.addClass('os-loading');
  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      $widget_elem.removeClass('os-loading');
      if(response.status === "success"){
        var $updated_widget_elem = jQuery(response.message);
        $updated_widget_elem.removeClass('os-widget-animated');
        $widget_elem = $widget_elem.replaceWith($updated_widget_elem);
        latepoint_init_daterangepicker($updated_widget_elem.find('.os-date-range-picker'));
        if($widget_elem.hasClass('os-widget-top-agents')) latepoint_init_circles_charts();
        if($widget_elem.hasClass('os-widget-daily-bookings')){
          latepoint_init_daily_bookings_chart();
          latepoint_init_donut_charts();
        }
      }else{
        alert(response.message);
      }
    }
  });
}

function latepoint_load_calendar(target_date, agent_id, location_id = false, service_id = false){
  var route_name = jQuery('.calendar-week-agent-w').data('calendar-action');
  jQuery('.calendar-week-agent-w').addClass('os-loading');
  var params_arr = {target_date: target_date, agent_id: agent_id};
  if(location_id) params_arr.location_id = location_id;
  if(service_id) params_arr.service_id = service_id;
  var data = { action: latepoint_helper.route_action, route_name: route_name, params: jQuery.param(params_arr), return_format: 'json' }
  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      if(response.status === "success"){
        jQuery('.calendar-week-agent-w').html(response.message).removeClass('os-loading');
        jQuery('.calendar-load-target-date.os-loading').removeClass('os-loading');
      }else{
        alert(response.message);
      }
    }
  });
}

function latepoint_init_quick_transaction_form(){
  latepoint_mask_money(jQuery('.quick-add-transaction-box-w .os-mask-money'));
}

function latepoint_reload_price_breakdown(){
  var $trigger =  jQuery('.reload-price-breakdown');
  $trigger.addClass('os-loading');
  var $quick_edit_form = $trigger.closest('form.order-quick-edit-form');
  var form_data = new FormData($quick_edit_form[0]);
  var route = $trigger.data('route');

  var data = { action: latepoint_helper.route_action, route_name: route, params: latepoint_formdata_to_url_encoded_string(form_data), return_format: 'json' }
  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      $trigger.removeClass('os-loading');
      if(response.status === "success"){
        jQuery('.price-breakdown-wrapper').html(response.message);
        latepoint_mask_money(jQuery('.price-breakdown-wrapper .os-mask-money'));
        latepoint_reload_balance_and_payments();
      }else{
        alert(response.message);
      }
    }
  });
}

function latepoint_complex_selector_select($connection_wrappers, qty = 1){
  $connection_wrappers.each(function(){
    jQuery(this).addClass('active');
    jQuery(this).find('.connection-children-list li').addClass('active');
    jQuery(this).find('.connection-child-is-connected').val('yes');
    jQuery(this).find('.item-quantity-selector-input').val(qty);
    latepoint_count_active_connections(jQuery(this));
  });
}

function latepoint_complex_selector_deselect($connection_wrappers){
  $connection_wrappers.each(function(){
    jQuery(this).removeClass('active');
    jQuery(this).removeClass('show-customize-box');
    jQuery(this).find('.connection-children-list li.active').removeClass('active');
    jQuery(this).find('.connection-child-is-connected').val('no');
    jQuery(this).find('.item-quantity-selector-input').val(0);
    latepoint_count_active_connections(jQuery(this));
  });
}



function latepoint_is_next_day($form){
  let field_base_name = 'order_items[' + $form.data('order-item-id') +'][bookings][' + $form.data('booking-id') +']';

  var start_time = $form.find('input[name="' + field_base_name + '[start_time][formatted_value]"]').val();
  var start_time_ampm = $form.find('input[name="' + field_base_name + '[start_time][ampm]"]').val();
  var start_time_minutes = latepoint_hours_and_minutes_to_minutes(start_time, start_time_ampm);
  var end_time = $form.find('input[name="' + field_base_name + '[end_time][formatted_value]"]').val();
  var end_time_ampm = $form.find('input[name="' + field_base_name + '[end_time][ampm]"]').val();
  var end_time_minutes = latepoint_hours_and_minutes_to_minutes(end_time, end_time_ampm);

  if(end_time_minutes && (end_time_minutes <= start_time_minutes)){
    $form.find('.quick-end-time-w').addClass('ending-next-day');
  }else{
    $form.find('.quick-end-time-w').removeClass('ending-next-day');
  }
}

function latepoint_set_booking_end_time($booking_data_form){
  var booking_duration = 0;
  var service_duration = Number($booking_data_form.find('.os-service-durations select').val());

  let field_base_name = 'order_items[' + $booking_data_form.data('order-item-id') +'][bookings][' + $booking_data_form.data('booking-id') +']';

  booking_duration = booking_duration + service_duration;
  if($booking_data_form.find('select[name="temp_service_extras_ids"] option:selected').length){
    $booking_data_form.find('select[name="temp_service_extras_ids"] option:selected').each(function(){
      var extra_duration = Number(jQuery(this).data('duration'));
      var $extra_quantity_input = jQuery(this).closest('.lateselect-w').find('.ls-item[data-value="' + jQuery(this).val() + '"]').find('.os-late-quantity-selector-input');
      if($extra_quantity_input.length) extra_duration = Number(extra_duration) * Number($extra_quantity_input.val());
      booking_duration = Number(booking_duration) + Number(extra_duration);
    });
  }

  var start_time = $booking_data_form.find('input[name="'+field_base_name+'[start_time][formatted_value]"]').val();

  if(start_time){
    var start_time_ampm = $booking_data_form.find('input[name="'+field_base_name+'[start_time][ampm]"]').val();
    var start_time_minutes = latepoint_hours_and_minutes_to_minutes(start_time, start_time_ampm);
    var end_time_minutes = parseInt(start_time_minutes) + parseInt(booking_duration);
    if(end_time_minutes >= (24 * 60)) end_time_minutes = (end_time_minutes - 24 * 60);
    var end_time_ampm = (end_time_minutes >= 720 && end_time_minutes < (24 * 60)) ? 'pm' : 'am';
    var end_hours_and_minutes = latepoint_minutes_to_hours_and_minutes(end_time_minutes);

    $booking_data_form.find('input[name="'+field_base_name+'[end_time][formatted_value]"]').val(end_hours_and_minutes);
    $booking_data_form.find('.quick-end-time-w .time-ampm-select.time-' + end_time_ampm).trigger('click');
    $booking_data_form.find('input[name="'+field_base_name+'[end_time][formatted_value]"]').closest('.os-form-group').addClass('has-value');
  }
  latepoint_is_next_day($booking_data_form);
}



function latepoint_init_sortable_columns(){
  jQuery('.os-sortable-column').on('click', function(){
    let current_direction = jQuery(this).hasClass('ordered-desc') ? 'desc' : 'asc';
    let new_direction = (current_direction == 'desc') ? 'asc' : 'desc';
    jQuery(this).closest('table').find('.os-sortable-column').removeClass('ordered-desc').removeClass('ordered-asc');
    jQuery(this).addClass('ordered-' + new_direction);

    jQuery(this).closest('table').find('.records-ordered-by-key').val(jQuery(this).data('order-key'));
    jQuery(this).closest('table').find('.records-ordered-by-direction').val(new_direction);
    latepoint_filter_table(jQuery(this).closest('table'), jQuery(this).closest('.os-form-group'));
    return false;
  });
}
function latepoint_random_text(length){
   var result           = '';
   var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
   var charactersLength = characters.length;
   for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
   }
   return result;
}

function latepoint_get_order_for_service_categories(){

}


function latepoint_init_daterangepicker($elem){
  $elem.each(function(){
    // DATERANGEPICKER
    var picker_start_time = jQuery(this).find('input[name="date_from"], .os-datepicker-date-from').val();
    var picker_end_time = jQuery(this).find('input[name="date_to"], .os-datepicker-date-to').val();
    var locale = {};
    if(jQuery(this).data('can-be-cleared')) locale = { cancelLabel: jQuery(this).data('clear-btn-label')};


    moment.locale(latepoint_helper.wp_locale);

    jQuery(this).daterangepicker({
      opens: 'center',
      singleDatePicker: (jQuery(this).data('single-date') == 'yes'),
      startDate: (picker_start_time) ? moment(picker_start_time) : moment(),
      endDate: (picker_end_time) ? moment(picker_end_time) : moment(),
      locale: locale
    });
  });

  $elem.on('cancel.daterangepicker', function(ev, picker) {
    if(picker.element.data('can-be-cleared')){
      picker.element.find('input[name="date_from"], .os-datepicker-date-from').val('');
      picker.element.find('input[name="date_to"], .os-datepicker-date-to').val('');
      picker.element.find('span.range-picker-value').text(picker.element.data('no-value-label'));
      if(picker.element.hasClass('os-table-filter-datepicker')){
        latepoint_filter_table(picker.element.closest('table'), picker.element.closest('.os-form-group'));
      }
    }
  });

  $elem.on('apply.daterangepicker', function(ev, picker) {
    if(picker.element.data('single-date') == 'yes'){
      picker.element.find('.range-picker-value').text(picker.startDate.format('ll'));
    }else{
      picker.element.find('.range-picker-value').text(picker.startDate.format('ll') + ' - ' + picker.endDate.format('ll'));
    }
    picker.element.find('input[name="date_from"], .os-datepicker-date-from').attr('value', picker.startDate.format('YYYY-MM-DD'));
    picker.element.find('input[name="date_to"], .os-datepicker-date-to').attr('value', picker.endDate.format('YYYY-MM-DD'));
    if(picker.element.closest('.os-widget').length){
      latepoint_reload_widget(picker.element.closest('.os-widget'));
    }
    if(picker.element.hasClass('os-table-filter-datepicker')){
      latepoint_filter_table(picker.element.closest('table'), picker.element.closest('.os-form-group'));
    }
  });
}

function latepoint_recalculate_items_count_in_category(){
  jQuery('.os-category-items-count').each(function(){
    var number_of_items = jQuery(this).closest('.os-category-parent-w').find('.item-in-category-w').length;
    jQuery(this).find('span').text(number_of_items);
  });
}

function latepoint_remove_agent_box($remove_btn){
  var $agent_box = $remove_btn.closest('.agent-box-w');
  $agent_box.fadeOut(300, function(){ jQuery(this).remove(); });
}

function latepoint_remove_service_box($remove_btn){
  var $service_box = $remove_btn.closest('.service-box-w');
  $service_box.fadeOut(300, function(){ jQuery(this).remove(); });
}

function latepoint_init_monthly_view(){
  if(!jQuery('.calendar-month-agents-w').length) return;

  jQuery('.monthly-calendar-headers select').on('change', function(){
    var $calendar = jQuery('.calendar-month-agents-w');
    var route_name = $calendar.data('route');
    $calendar.addClass('os-loading');
    var params = { month: jQuery('#monthly_calendar_month_select').val(), year: jQuery('#monthly_calendar_year_select').val() };
    if(jQuery('#monthly_calendar_location_select').length && jQuery('#monthly_calendar_location_select').val()) params.location_id = jQuery('#monthly_calendar_location_select').val();
    if(jQuery('#monthly_calendar_service_select').length && jQuery('#monthly_calendar_service_select').val()) params.service_id = jQuery('#monthly_calendar_service_select').val();
    var data = { action: latepoint_helper.route_action, route_name: route_name, params: params, layout: 'none', return_format: 'json' }
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(data){
        $calendar.removeClass('os-loading');
        if(data.status === "success"){
          $calendar.html(data.message);
        }else{
          // console.log(data.message);
        }
      }
    });
  });
}


function latepoint_init_copy_on_click_elements(){

  jQuery('.os-click-to-copy').on('mouseenter', function() {
    var $this = jQuery(this);
    var position_info = $this.offset();
    var width = jQuery(this).outerWidth();
    var position_left = position_info.left;
    var position_top = position_info.top - 20 - jQuery(window).scrollTop();

    let color = ($this.data('copy-tooltip-color') == 'dark') ? 'dark' : 'light';
    if($this.data('copy-tooltip-position') == 'left'){
      position_left = position_left - width - 5;
      position_top = position_top + $this.outerHeight() - jQuery(window).scrollTop();
    }
    jQuery('body').append('<div class="os-click-to-copy-prompt color-'+color+'" style="top: '+position_top+'px; left: '+position_left+'px;">' + latepoint_helper.click_to_copy_prompt + '</div>');
  }).on('mouseleave', function() {
    jQuery('body').find('.os-click-to-copy-prompt').remove();
  });
  jQuery('.os-click-to-copy').on('click', function(){
    var $this = jQuery(this);
    let color = ($this.data('copy-tooltip-color') == 'dark') ? 'dark' : 'light';
    jQuery('body').find('.os-click-to-copy-prompt').hide();
    var text_to_copy = $this.is('input') ? $this.val() : $this.text();
    navigator.clipboard.writeText(text_to_copy);

    var position_info = $this.offset();
    var width = $this.outerWidth();
    var position_left = position_info.left;
    var position_top = position_info.top - 20 - jQuery(window).scrollTop();

    if($this.data('copy-tooltip-position') == 'left'){
      position_left = position_left - width - 5;
      position_top = position_top + $this.outerHeight() - jQuery(window).scrollTop();
    }
    var $done_prompt = jQuery('<div class="os-click-to-copy-done color-'+color+'" style="top: '+position_top+'px; left: '+position_left+'px;">' + latepoint_helper.click_to_copy_done + '</div>');
    $done_prompt.appendTo(jQuery('body')).animate({
      opacity: 0,
      left: (position_left + 20),
    }, 600);
    setTimeout(function(){
      jQuery('body').find('.os-click-to-copy-done').remove();
      jQuery('body').find('.os-click-to-copy-prompt').show();
    }, 800);
  });
}

function latepoint_remove_floating_popup(){
  jQuery('.os-showing-popup').removeClass('os-showing-popup');
  jQuery('.os-floating-popup').remove();
}

function latepoint_init_clickable_cells(){
  jQuery('.os-clickable-popup-trigger').on('click', function(){
    var $this = jQuery(this);
    var position = $this.offset();
    var width = $this.outerWidth();
    var $popup = jQuery('<div class="os-floating-popup os-loading"></div>');
    if($this.hasClass('os-showing-popup')){
      latepoint_remove_floating_popup();
    }else{
      latepoint_remove_floating_popup();
      $popup.offset({top: position.top, left: (position.left + width/2)});
      jQuery('body').append($popup);
      $this.addClass('os-showing-popup');

      var route = $this.data('route');
      var params = $this.data('os-params');
      var data = { action: latepoint_helper.route_action, route_name: route, params: params, layout: 'none', return_format: 'json' };
      jQuery.ajax({
        type : "post",
        dataType : "json",
        url : latepoint_timestamped_ajaxurl(),
        data : data,
        success: function(response){
          if(response.status === latepoint_helper.response_status.success){
            jQuery('body').find('.os-floating-popup').html(response.message).removeClass('os-loading');
            latepoint_init_customer_donut_chart();
            jQuery('.os-floating-popup .os-floating-popup-close').on('click', function(){
              latepoint_remove_floating_popup();
              return false;
            });
          }else{

          }
        }
      });
    }
    return false;
  });
}

function latepoint_init_tiny_mce(element_id){
  // TODO CHECK IF wp.editor is defined
  if(typeof wp !== 'undefined' && typeof wp.editor !== 'undefined' && jQuery('#'+ element_id).length){
    wp.editor.remove(element_id);
    wp.editor.initialize(element_id,
      {
        tinymce: {
          wpautop: false,
          toolbar1: 'formatselect alignjustify forecolor | bold italic underline strikethrough | bullist numlist | blockquote hr | alignleft aligncenter alignright | link unlink | pastetext removeformat | outdent indent | undo redo',
          height : "480",
        },
        quicktags: true,
        mediaButtons: true,
      }
    );
  }
}

function latepoint_init_reminder_form(){
  latepoint_init_tiny_mce(jQuery('.os-reminder-form:last-child textarea').attr('id'));
}

function latepoint_filter_table($table, $filter_elem, reset_page = true){
  $filter_elem.addClass('os-loading');
  var filter_params = $table.find('.os-table-filter').serialize();
  var $table_w = $table.closest('.table-with-pagination-w');
  if(reset_page){
    $table_w.find('select.pagination-page-select').val(1);
  }else{
    filter_params+= '&page_number='+$table_w.find('select.pagination-page-select').val();
  }
  var route = $table.data('route');
  var data = { action: latepoint_helper.route_action, route_name: route, params: filter_params, layout: 'none', return_format: 'json' };
  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(data){
      $filter_elem.removeClass('os-loading');
      if(data.status === "success"){
        $table.find('tbody').html(data.message);
        if(data.total_pages && reset_page){
          var options = '';
          for(var i = 1; i <= data.total_pages; i++){
            options+= '<option>'+ i +'</option>';
          }
          $table_w.find('select.pagination-page-select').html(options);
        }
        $table_w.find('.os-pagination-from').text(data.showing_from);
        $table_w.find('.os-pagination-to').text(data.showing_to);
        $table_w.find('.os-pagination-total').text(data.total_records);
        latepoint_init_clickable_cells();
      }else{
        // console.log(data.message);
      }
    }
  });
}

function latepoint_init_wizard_content(){
  latepoint_init_input_masks(jQuery('.os-wizard-step-content'));
}

function latepoint_init_input_masks($scoped_element = false){
  let $wrapper = $scoped_element ? $scoped_element : jQuery('body');
  latepoint_mask_timefield($wrapper.find('.os-mask-time'));

  $wrapper.find('.os-mask-phone').each(function(){
    latepoint_mask_phone(jQuery(this));
  });

  latepoint_mask_money($wrapper.find('.os-mask-money'));
  latepoint_mask_date($wrapper.find('.os-mask-date'));
  latepoint_mask_minutes($wrapper.find('.os-mask-minutes'));

  $wrapper.trigger('latepoint:initInputMasks');
}

