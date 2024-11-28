
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

function latepoint_submit_quick_order_form(){
  let $quick_edit_form = jQuery('form.order-quick-edit-form');

  let errors = latepoint_validate_form($quick_edit_form);
  if(errors.length){
    let error_messages = errors.map(error =>  error.message ).join(', ');
    latepoint_add_notification(error_messages, 'error');
    return false;
  }

  $quick_edit_form.find('button[type="submit"]').addClass('os-loading');
  jQuery.ajax({
    type: "post",
    dataType: "json",
    processData: false,
    contentType: false,
    url: latepoint_timestamped_ajaxurl(),
    data: latepoint_create_form_data($quick_edit_form),
    success: function (response) {
      if(response.fields_to_update){
        for (const [key, value] of Object.entries(response.fields_to_update)) {
            $quick_edit_form.find('input[name="' + key + '"]').val(value)
        }
      }
      $quick_edit_form.find('button[type="submit"]').removeClass('os-loading');
      if(response.form_values_to_update){
        jQuery.each(response.form_values_to_update, function(name, value){
          $quick_edit_form.find('[name="'+ name +'"]').val(value);
        });
      }
      if (response.status === "success") {
        latepoint_add_notification(response.message);
        latepoint_reload_after_order_save();
      }else{
        latepoint_add_notification(response.message, 'error');
      }
    }
  });

}

function latepoint_apply_agent_selector_change(){
  if(jQuery('.quick-availability-per-day-w').length){

    let booking_form_id = jQuery('.quick-availability-per-day-w').data('trigger-form-booking-id');
    let $trigger_btn = jQuery('.order-item-booking-data-form-wrapper[data-booking-id="' + booking_form_id + '"]').find('.trigger-quick-availability');

    latepoint_load_quick_availability($trigger_btn);
  }
}

function latepoint_apply_service_selector_change($form){
  let field_base_name = 'order_items[' + $form.data('order-item-id') +'][bookings][' + $form.data('booking-id') +']';

  var $selected_service = $form.find('.os-services-select-field-w .service-option-selected');
  var service_id = $selected_service.data('id');
  var buffer_before = $selected_service.data('buffer-before');
  var buffer_after = $selected_service.data('buffer-after');
  var default_duration = $selected_service.data('duration');
  var default_duration_name = $selected_service.data('duration-name');
  var min_capacity = $selected_service.data('capacity-min');
  var max_capacity = $selected_service.data('capacity-max');

  var extra_durations = $selected_service.data('extra-durations');

  $form.find('input[name="'+field_base_name+'[buffer_before]"]').val(buffer_before).trigger('change').closest('.os-form-group').addClass('has-value');
  $form.find('input[name="'+field_base_name+'[buffer_after]"]').val(buffer_after).trigger('change').closest('.os-form-group').addClass('has-value');
  $form.find('input[name="'+field_base_name+'[service_id]"]').val(service_id).trigger('change').closest('.os-form-group').addClass('has-value');

  var duration_name = default_duration_name ? default_duration_name : (default_duration + ' ' + latepoint_helper.string_minutes);
  var options = '<option value="'+ default_duration +'">' + duration_name + '</option>';
  if(extra_durations.length){
    jQuery.each(extra_durations, function(index, value){
      var duration_name = value.name ? value.name : value.duration + ' ' + latepoint_helper.string_minutes;
      options+= '<option value="'+ value.duration +'">' + duration_name + '</option>';
    });
    $form.find('.os-service-durations').show();
  }else{
    $form.find('.os-service-durations').hide();
  }

  $form.find('.booking-total-attendees-selector-w .capacity-info strong').text(max_capacity);
  var attendees_options_html = '';
  for(var i=1;i<=max_capacity;i++){
    attendees_options_html+= '<option value="' + i + '">' + i + '</option>';
  }
  var selected_attendees = Math.min(jQuery('.booking-total-attendees-selector-w select').val(), max_capacity);
  $form.find('.booking-total-attendees-selector-w select').html(attendees_options_html).val(selected_attendees);
  if(max_capacity > 1){
    $form.find('.booking-total-attendees-selector-w').show();
  }else{
    $form.find('.booking-total-attendees-selector-w').hide();
  }

  $form.find('.os-service-durations select').html(options);

  latepoint_set_booking_end_time($form);
  if(jQuery('.quick-availability-per-day-w').length){
    latepoint_load_quick_availability($form.find('.trigger-quick-availability'));
  }

  latepoint_init_input_masks($form);
}

function latepoint_reload_balance_and_payments(){
  let $wrapper = jQuery('.balance-payment-info');
  $wrapper.closest('.balance-payment-wrapper').addClass('os-loading');
  let route_name = $wrapper.data('route');
  let $quick_edit_form = $wrapper.closest('form.order-quick-edit-form');
  let form_data = new FormData($quick_edit_form[0]);

  let data = { action: latepoint_helper.route_action, route_name: route_name, params: latepoint_formdata_to_url_encoded_string(form_data), return_format: 'json' }
  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      $wrapper.closest('.balance-payment-wrapper').removeClass('os-loading');
      if(response.status === "success"){
        jQuery('.balance-payment-wrapper').html(response.message);
      }else{
        alert(response.message);
      }
    }
  });
}



function latepoint_cancel_adding_new_order_item_to_quick_edit_form(){
  jQuery('.order-items-list').removeClass('is-blurred');
  jQuery('.new-order-item-list-bundles-wrapper').removeClass('is-open');
  jQuery('.new-order-item-variant-selector-wrapper').removeClass('is-open');
  jQuery('.order-form-add-item-btn').removeClass('is-cancelling').find('span').text(jQuery('.order-form-add-item-btn').data('add-label'));
}

function latepoint_build_new_booking_order_item(){
  jQuery('.order-form-add-item-btn').addClass('os-loading');
  latepoint_cancel_adding_new_order_item_to_quick_edit_form();
  let params = {}

  var data = {
    action: 'latepoint_route_call',
    route_name: jQuery('.order-form-add-item-btn').data('booking-form-route-name'),
    params: params,
    return_format: 'json'
  }
  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      if(response.status === "success"){
        let $form = jQuery(response.message);
        jQuery('.order-items-list').prepend($form);
        jQuery('.order-form-add-item-btn').removeClass('os-loading');
        latepoint_init_booking_data_form(jQuery('.order-item-booking-data-form-wrapper[data-order-item-id="' + $form.data('order-item-id') + '"]'));
        // new item added, trigger change event
        latepoint_quick_order_items_changed();
      }else{
        alert(response.message, 'error');
      }
    }
  });
}

function latepoint_build_booking_data_form_for_bundle($slot_for_booking){
  $slot_for_booking.addClass('os-loading');
  latepoint_cancel_adding_new_order_item_to_quick_edit_form();
  let params = {}

  let is_booked = $slot_for_booking.hasClass('is-booked');

  var data = {
    action: 'latepoint_route_call',
    route_name: jQuery('.order-form-add-item-btn').data('booking-form-route-name'),
    params: {
      order_item_id: $slot_for_booking.data('order-item-id'),
      order_item_variant: $slot_for_booking.data('order-item-variant'),
      booking_id: $slot_for_booking.data('booking-id'),
      booking_item_data: is_booked ? $slot_for_booking.find('.booking_item_data').val() : $slot_for_booking.find('.unscheduled_booking_item_data').val()
    },
    return_format: 'json'
  }
  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      if(response.status === "success"){
        let $form = jQuery(response.message);
        $slot_for_booking.removeClass('os-loading');
        if($slot_for_booking){
          $slot_for_booking.find('.scheduled-bundle-booking').html($form).closest('.order-item-variant-bundle-booking ').addClass('is-booked');
        }else{
          jQuery('.order-items-list').prepend($form);
        }
        latepoint_init_booking_data_form(jQuery('.order-item-booking-data-form-wrapper[data-order-item-id="' + $form.data('order-item-id') + '"][data-booking-id="' + $form.data('booking-id') + '"]'));
        // new item added, trigger change event
        if(!$slot_for_booking) latepoint_quick_order_items_changed();
      }else{
        alert(response.message, 'error');
      }
    }
  });
}

function latepoint_bundle_added_to_quick_order(){
  latepoint_quick_order_items_changed();
  latepoint_cancel_adding_new_order_item_to_quick_edit_form();
}

function latepoint_quick_order_items_changed(){
  latepoint_reload_price_breakdown();
}


function latepoint_fold_booking_data_form_in_order_quick_edit($booking_data_form){
  if(!$booking_data_form.length) return false;
  latepoint_close_quick_availability_form();
  latepoint_show_all_order_items();
  let order_item_id = $booking_data_form.data('order-item-id');
  let booking_id = $booking_data_form.data('booking-id');
  let order_item_variant = $booking_data_form.data('order-item-variant');


  $booking_data_form.addClass('is-loading');

  let form_data = new FormData(jQuery('.order-quick-edit-form')[0]);
  form_data.set('order_item_id', order_item_id);
  form_data.set('booking_id', booking_id);
  var data = {
    action: 'latepoint_route_call',
    route_name: jQuery('.order-form-add-item-btn').data('fold-booking-data-route-name'),
    params: latepoint_formdata_to_url_encoded_string(form_data),
    return_format: 'json'
  }
  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      if(response.status === "success"){
        $booking_data_form.removeClass('is-loading').removeClass('is-unfolded').addClass('is-folded');
        if(order_item_variant == latepoint_helper.order_item_variant_bundle){
          $booking_data_form.closest('.order-item-variant-bundle-booking').addClass('is-booked');
          $booking_data_form.find('.bundle-booking-item-pill').replaceWith(response.message);
        }else{
          $booking_data_form.find('.order-item-pill').replaceWith(response.message);
        }
      }else{
        alert(response.message, 'error');
      }
    }
  });
}


function latepoint_init_booking_data_form($booking_data_form){
  latepoint_init_input_masks($booking_data_form);

  $booking_data_form.find('.fold-order-item-booking-data-form-btn').on('click', function(){
    latepoint_fold_booking_data_form_in_order_quick_edit($booking_data_form);
    return false;
  });

  $booking_data_form.find('.quick-booking-form-view-log-btn').on('click', function(){
    var $trigger_elem = jQuery(this);
    $trigger_elem.addClass('os-loading');
    var route = $trigger_elem.data('route');
    var data = { action: 'latepoint_route_call', route_name: route, params: {booking_id: $trigger_elem.data('booking-id')}, return_format: 'json' }
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(response){
        $trigger_elem.removeClass('os-loading');
        if(response.status === "success"){
          latepoint_display_in_side_sub_panel(response.message);
          jQuery('body').addClass('has-side-sub-panel');
        }else{
          alert(response.message, 'error');
        }
      }
    });
    return false;
  });


  $booking_data_form.find('.os-late-select').lateSelect();

  $booking_data_form.find('.trigger-quick-availability').on('click', function(){
    latepoint_load_quick_availability(jQuery(this));
    return false;
  });

  let field_base_name = 'order_items[' + $booking_data_form.data('order-item-id') +'][bookings][' + $booking_data_form.data('booking-id') +']';

  $booking_data_form.find('input[name="' + field_base_name +'[start_time][formatted_value]"]').on('change', function(){
    latepoint_set_booking_end_time($booking_data_form);
  });
  $booking_data_form.find('input[name="' + field_base_name +'[end_time][formatted_value]"]').on('change', function(){
    latepoint_is_next_day($booking_data_form);
  });



  $booking_data_form.on('change', '.agent-selector', function(){
    latepoint_apply_agent_selector_change($booking_data_form);
  });
  $booking_data_form.on('change', 'select[name="booking[location_id]"]', function(){
    latepoint_apply_agent_selector_change($booking_data_form);
  });
  $booking_data_form.on('change', 'select[name="booking[total_attendees]"]', function(){
    latepoint_apply_agent_selector_change($booking_data_form);
  });

  $booking_data_form.on('change', '.os-affects-duration', function(){
    latepoint_set_booking_end_time($booking_data_form);
    if(jQuery('.quick-availability-per-day-w').length){
      latepoint_load_quick_availability($booking_data_form.find('.trigger-quick-availability'));
    }
  });

  $booking_data_form.on('change', '.os-affects-price', function(){
    latepoint_reload_price_breakdown();
  });

  $booking_data_form.on('change', '.os-affects-balance', function(){
    latepoint_reload_balance_and_payments();
  });
  $booking_data_form.on('keyup', '.os-affects-balance', function(event){
    if(event.keyCode == 13) {
      latepoint_reload_balance_and_payments();
    }
  });


  $booking_data_form.on('click', '.services-options-list .service-option', function(){
    var selected_option_html = jQuery(this).html();
    var $selected_option = jQuery(this).closest('.os-services-select-field-w').find('.service-option-selected');
    $selected_option.html(selected_option_html)
                    .data('id', jQuery(this).data('id'))
                    .data('duration', jQuery(this).data('duration'))
                    .data('duration-name', jQuery(this).data('duration-name'))
                    .data('buffer-before', jQuery(this).data('buffer-before'))
                    .data('buffer-after', jQuery(this).data('buffer-after'))
                    .data('capacity-min', jQuery(this).data('capacity-min'))
                    .data('capacity-max', jQuery(this).data('capacity-max'))
                    .data('extra-durations', jQuery(this).data('extra-durations'));
    jQuery(this).closest('.os-services-select-field-w').find('.service-option.selected').removeClass('selected');
    jQuery(this).addClass('selected').closest('.os-services-select-field-w').removeClass('active');
    latepoint_apply_service_selector_change($booking_data_form);
    return false;
  });

  $booking_data_form.trigger('latepoint:initBookingDataForm');

}

function latepoint_show_all_order_items(){
  let $quick_order_form = jQuery('.quick-order-form-w');
  $quick_order_form.find('.order-items-info-w').removeClass('show-preselected-only');
  $quick_order_form.find('.holds-preselected-booking').removeClass('holds-preselected-booking');
}

function latepoint_init_quick_order_form(){
  let $quick_order_form = jQuery('.quick-order-form-w');
  $quick_order_form.trigger('latepoint:initOrderEditForm');

  $quick_order_form.on('change', '.os-affects-balance', function(){
    latepoint_reload_balance_and_payments();
  });
  $quick_order_form.on('keyup', '.os-affects-balance', function(event){
    if(event.keyCode == 13) {
      latepoint_reload_balance_and_payments();
    }
  });

  latepoint_init_customer_inline_edit_form($quick_order_form.find('.customer-info-w'));
  $quick_order_form.find('.order-item-booking-data-form-wrapper').each(function(){
    latepoint_init_booking_data_form(jQuery(this));
  });

  latepoint_lightbox_close();
  latepoint_remove_floating_popup();
  latepoint_init_input_masks($quick_order_form);

  $quick_order_form.find('.quick-order-form-view-log-btn').on('click', function(){
    var $trigger_elem = jQuery(this);
    $trigger_elem.addClass('os-loading');
    var route = $trigger_elem.data('route');
    var data = { action: 'latepoint_route_call', route_name: route, params: {order_id: $trigger_elem.data('order-id')}, return_format: 'json' }
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(response){
        $trigger_elem.removeClass('os-loading');
        if(response.status === "success"){
          latepoint_display_in_side_sub_panel(response.message);
          jQuery('body').addClass('has-side-sub-panel');
        }else{
          alert(response.message, 'error');
        }
      }
    });
    return false;
  });


  $quick_order_form.find('.new-order-item-variant-bundle').on('click', function(){
    $quick_order_form.find('.new-order-item-list-bundles-wrapper').toggleClass('is-open');
    $quick_order_form.find('.new-order-item-variant-selector-wrapper').toggleClass('is-open');
    return false;
  });




  $quick_order_form.find('.hidden-order-items-notice-link, .hidden-bundle-items-notice-link').on('click', function(e){
    latepoint_show_all_order_items();
    return false;
  });

  $quick_order_form.find('.order-quick-edit-form').on('submit', function(e){
    if(jQuery(this).find('button[type="submit"]').hasClass('os-loading')) return false;
    e.preventDefault();
    latepoint_submit_quick_order_form();
  });

  $quick_order_form.on("keydown", ":input:not(textarea):not(:submit)", function(event) {
    if (event.key == "Enter") {
        event.preventDefault();
    }
  });

  $quick_order_form.find('.order-items-list').on('click', '.remove-order-item-btn', function(){
    latepoint_close_quick_availability_form();
    if(confirm(jQuery(this).data('os-prompt'))){
      if(jQuery(this).closest('.order-item-variant-bundle-booking-wrapper').length){
        // it's a bundle booking
        // need to figure out how to remove it when bundle
        jQuery(this).closest('.order-item-variant-bundle-booking').removeClass('is-booked').find('.scheduled-bundle-booking').html('');
      }else{
        jQuery(this).closest('.order-item').remove();
        jQuery(this).closest('.order-item-booking-data-form-wrapper').remove();

      }
      latepoint_quick_order_items_changed();
    }
    return false;
  });

  $quick_order_form.find('.new-order-item-variant-booking').on('click', function(){
    latepoint_fold_all_open_booking_data_forms();
    latepoint_build_new_booking_order_item();
  });

  $quick_order_form.on('click', '.order-item-pill.order-item-pill-variant-booking', function(){
    jQuery(this).closest('.order-item-booking-data-form-wrapper').removeClass('is-folded').addClass('is-unfolded');
    return false;
  });

  $quick_order_form.on('click', '.bundle-booking-item-pill', function(){
    jQuery(this).closest('.order-item-booking-data-form-wrapper').removeClass('is-folded').addClass('is-unfolded');
    return false;
  });

  $quick_order_form.on('click', '.unscheduled-bundle-booking', function(){
    latepoint_build_booking_data_form_for_bundle(jQuery(this).closest('.order-item-variant-bundle-booking'));
  });


  $quick_order_form.find('.order-form-add-item-btn').on('click', function(){
    let $booking_data_forms = jQuery('.order-item-booking-data-form-wrapper');
    $booking_data_forms.each(function(){
      latepoint_fold_booking_data_form_in_order_quick_edit(jQuery(this));
    });
    if(jQuery(this).hasClass('is-cancelling')){
      latepoint_cancel_adding_new_order_item_to_quick_edit_form();
    }else{
      if(jQuery('.new-order-item-variant-selector-wrapper').length){
        jQuery('.order-items-list').addClass('is-blurred');
        jQuery('.new-order-item-variant-selector-wrapper').addClass('is-open');
        jQuery(this).addClass('is-cancelling').find('span').text(jQuery(this).data('cancel-label'));
      }else{
        // no bundles exist, create booking form
        latepoint_cancel_adding_new_order_item_to_quick_edit_form();
        latepoint_build_new_booking_order_item();
      }
    }
    return false;
  });


  $quick_order_form.on('click', '.order-item-variant-bundle .bundle-icon', function(){
    jQuery(this).closest('.order-item-variant-bundle').toggleClass('is-open');
    return false;
  });

  $quick_order_form.find('.reload-price-breakdown').on('click', function(){
    latepoint_reload_price_breakdown();
    return false;
  });

  $quick_order_form.on('click', '.trigger-remove-transaction-btn', function(){
    jQuery(this).closest('.quick-add-transaction-box-w').remove();
    return false;
  });


  $quick_order_form.trigger('latepoint:initQuickOrderForm');
}

function latepoint_fold_all_open_booking_data_forms(){
  let $booking_data_forms = jQuery('.order-item-booking-data-form-wrapper');
  $booking_data_forms.each(function(){
    latepoint_fold_booking_data_form_in_order_quick_edit(jQuery(this));
  });
}

function latepoint_init_customer_inline_edit_form($customer_form){

  latepoint_init_input_masks($customer_form);

  $customer_form.find('.customers-selector-search-input').on('keyup',function(){
    var $queryInput = jQuery(this);
    var query = $queryInput.val().toLowerCase();
    if(query == $queryInput.data('current-query')) return;

    // Search
    $queryInput.closest('.customers-selector-search-w').addClass('os-loading');
    $queryInput.data('searching-query', query);
    setTimeout(function(){
      if(query != jQuery('.customers-selector-search-input').data('searching-query')) return;
      var data = { action: latepoint_helper.route_action, route_name: $queryInput.data('route'), params: {query: query}, return_format: 'json' }
      jQuery.ajax({
        type : "post",
        dataType : "json",
        url : latepoint_timestamped_ajaxurl(),
        data : data,
        success: function(response){
          if($queryInput.data('searching-query') != query) return;
          $queryInput.closest('.customers-selector-search-w').removeClass('os-loading');
          if(response.status === "success"){
            $queryInput.data('current-query', query);
            jQuery('.quick-order-form-w .customers-options-list').html(response.message);
          }else{
            // console.log(response.message);
          }
        }
      });
    }, 300, query, $queryInput);
 });

}


function latepoint_load_quick_availability($trigger_elem, custom_agent_id = false, start_date = false, load_more_days = false, load_prev_days = false){
  $trigger_elem.addClass('os-loading');

  let $booking_form = $trigger_elem.closest('.order-item-booking-data-form-wrapper');
  var route = $booking_form.find('.trigger-quick-availability').data('route');
  var $quick_order_form = jQuery('.quick-order-form-w');

  if(custom_agent_id) $quick_order_form.find('.agent-selector').val(custom_agent_id);
  if(!$quick_order_form.find('.service-selector').val() || $quick_order_form.find('.service-selector').val() == '0'){
    $quick_order_form.find('.os-services-select-field-w .service-option:first').trigger('click');
  }

  let form_data = new FormData($quick_order_form.find('form')[0]);


  form_data.set('trigger_form_booking_id', $booking_form.data('booking-id'));
  form_data.set('trigger_form_order_item_id', $booking_form.data('order-item-id'));

  if(start_date) form_data.set('start_date', start_date);
  if(load_more_days || load_prev_days) form_data.set('show_days_only', true);
  if(load_prev_days) form_data.set('previous_days', true);

  var data = {
    action: latepoint_helper.route_action,
    route_name: route,
    params: latepoint_formdata_to_url_encoded_string(form_data),
    return_format: 'json'
  }

  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(response){
      $trigger_elem.removeClass('os-loading');
      if(response.status === "success"){
        if(load_more_days){
          jQuery('.latepoint-side-panel-w .quick-availability-per-day-w').html(response.message);
        }else if(load_prev_days){
          jQuery('.latepoint-side-panel-w .quick-availability-per-day-w').html(response.message);
          jQuery('.latepoint-side-panel-w .os-availability-days').scrollTop(30);
        }else{
          latepoint_display_in_side_sub_panel(response.message);
          jQuery('.latepoint-side-panel-w .os-availability-days').scrollTop(30);
          jQuery('body').addClass('has-side-sub-panel');
          latepoint_init_quick_availability_form();
        }
      }else{
        alert(response.message, 'error');
      }
    }
  });
}

function latepoint_create_field_base_name(order_item_id, booking_id){
  return 'order_items['+order_item_id+'][bookings]['+booking_id+']';
}

function latepoint_close_quick_availability_form(){
  jQuery('.quick-availability-per-day-w').remove();
  jQuery('body').removeClass('has-side-sub-panel');
}

function latepoint_init_quick_availability_form(){
  // TODO set booking ID
  let $quick_availability_wrapper = jQuery('.quick-availability-per-day-w');

  let trigger_form_order_item_id = $quick_availability_wrapper.data('trigger-form-order-item-id');
  let trigger_form_booking_id = $quick_availability_wrapper.data('trigger-form-booking-id');

  let field_base_name = latepoint_create_field_base_name(trigger_form_order_item_id, trigger_form_booking_id);

  let $booking_data_form = jQuery('.quick-order-form-w .order-item-booking-data-form-wrapper[data-booking-id="'+trigger_form_booking_id+'"]');

  var selected_start_date = $booking_data_form.find('input[name="'+field_base_name+'[start_date_formatted]"').val();
  var selected_start_time = $booking_data_form.find('input[name="'+field_base_name+'[start_time][formatted_value]"]').val();
  var selected_start_time_ampm = $booking_data_form.find('input[name="'+field_base_name+'[start_time][ampm]"]').val();


  var selected_start_time_minutes = latepoint_hours_and_minutes_to_minutes(selected_start_time, selected_start_time_ampm);
  $quick_availability_wrapper.find('.os-availability-days').find('.agent-timeslot[data-formatted-date="'+ selected_start_date +'"][data-minutes="' + selected_start_time_minutes + '"]').addClass('selected');
  $quick_availability_wrapper.on('click', '.load-more-quick-availability', function(){
    jQuery(this).addClass('os-loading');
    let booking_form_id = jQuery(this).closest('.quick-availability-per-day-w').data('trigger-form-booking-id');
    let $trigger_btn = jQuery('.order-item-booking-data-form-wrapper[data-booking-id="' + booking_form_id + '"]').find('.trigger-quick-availability');
    latepoint_load_quick_availability($trigger_btn, false, jQuery(this).data('start-date'), true);
    return false;
  });
  $quick_availability_wrapper.on('click', '.load-prev-quick-availability', function(){
    jQuery(this).addClass('os-loading');
    let booking_form_id = jQuery(this).closest('.quick-availability-per-day-w').data('trigger-form-booking-id');
    let $trigger_btn = jQuery('.order-item-booking-data-form-wrapper[data-booking-id="' + booking_form_id + '"]').find('.trigger-quick-availability');
    latepoint_load_quick_availability($trigger_btn, false, jQuery(this).data('start-date'), false, true);
    return false;
  });
  $quick_availability_wrapper.find('select[name="booking[agent_id]"]').on('change', function(){
    latepoint_load_quick_availability(jQuery('.trigger-quick-availability'), jQuery(this).val());
  });
  jQuery('.os-time-group label').on('click', function(){
    jQuery(this).closest('.os-time-group').find('.os-form-control').trigger('focus');
  });
  $quick_availability_wrapper.on('click', '.fill-booking-time', function(){
    jQuery('.os-availability-days .agent-timeslot.selected').removeClass('selected');
    jQuery(this).addClass('selected');
    var formatted_date = jQuery(this).data('formatted-date');
    var minutes = jQuery(this).data('minutes');
    $booking_data_form.find('input[name="'+field_base_name+'[start_date_formatted]"]').val(formatted_date);
    var start_minutes = minutes;
    var start_hours_and_minutes = latepoint_minutes_to_hours_and_minutes(start_minutes);

    if(start_minutes >= 720){
      $booking_data_form.find('.quick-start-time-w .time-pm').trigger('click');
    }else{
      $booking_data_form.find('.quick-start-time-w .time-am').trigger('click');
    }

    $booking_data_form.find('input[name="'+field_base_name+'[start_time][formatted_value]"]').val(start_hours_and_minutes);
    latepoint_set_booking_end_time($booking_data_form);
    $booking_data_form.find('.ws-period, .as-period').addClass('animate-filled-in');
    setTimeout(function(){
      $booking_data_form.find('.ws-period, .as-period').removeClass('animate-filled-in');
    }, 500)
  });
}


function latepoint_reload_after_order_save(){
  latepoint_reload_calendar_view();

  jQuery('.os-widget').each(function(){
    latepoint_reload_widget(jQuery(this));
  });
  if(jQuery('table.os-reload-on-booking-update').length) latepoint_filter_table(jQuery('table.os-reload-on-booking-update'), jQuery('table.os-reload-on-booking-update'));
  latepoint_close_side_panel();
}