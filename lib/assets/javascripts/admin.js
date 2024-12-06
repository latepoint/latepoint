/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */

// @codekit-prepend "bin/time.js";
// @codekit-prepend "bin/lateselect.js";
// @codekit-prepend "bin/latecheckbox.js";
// @codekit-prepend "bin/actions.js";
// @codekit-prepend "bin/notifications.js";
// @codekit-prepend "bin/shared.js";
// @codekit-prepend "bin/admin/updates.js";
// @codekit-prepend "bin/admin/main.js";
// @codekit-prepend "bin/admin/_customers.js";
// @codekit-prepend "bin/admin/_chart.js";
// @codekit-prepend "bin/admin/_calendar.js";
// @codekit-prepend "bin/admin/_processes.js";
// @codekit-prepend "bin/admin/_steps.js";
// @codekit-prepend "bin/admin/_orders.js";
// @codekit-prepend "bin/admin/_stripe_connect.js";



// DOCUMENT READY
jQuery(document).ready(function( $ ) {


  // DASHBOARD
  latepoint_init_calendars();
  latepoint_init_circles_charts();
  latepoint_init_donut_charts();
  latepoint_init_daily_bookings_chart();
  latepoint_init_element_togglers();
  latepoint_init_daterangepicker(jQuery('.os-date-range-picker'));
  latepoint_init_monthly_view();
  latepoint_init_form_blocks();
  latepoint_init_reminders_form();
  latepoint_init_coupons_form();
  latepoint_init_copy_on_click_elements();
  latepoint_init_side_menu();
  latepoint_init_color_picker();
  latepoint_init_clickable_cells();
  latepoint_init_input_masks();
  latepoint_init_process_forms();
  latepoint_init_sticky_side_menu();
  latepoint_init_sortable_columns();
  latepoint_init_accordions();
  latepoint_init_default_form_fields_settings();
  latepoint_init_steps_settings();
  latepoint_init_booking_form_preview();

  latepoint_init_version5_intro();

  jQuery(document).on({
    mouseenter: function () {
      let $elem = jQuery(this);
      let offset = $elem.offset();
      jQuery('body > .late-tooltip').remove();
      let $popup = jQuery('<div/>').addClass('late-tooltip').text($elem.data('late-tooltip')).appendTo(jQuery('body'));
      $popup.css('top', offset.top - 2);
      $popup.css('left', offset.left + $elem.outerWidth() / 2);
      return false;
    },
    mouseleave: function () {
      jQuery('body > .late-tooltip').remove();
    }
  }, "[data-late-tooltip]");

  jQuery('body').on('click', '.disabled-items-open-trigger', function(){
    jQuery('.disabled-items-wrapper').toggleClass('is-open');
    return false;
  });

  jQuery('body').on('click', '.latepoint-side-panel-close', function(){
    jQuery('.side-sub-panel-wrapper').remove();
    return false;
  });

  jQuery('#settings_list_of_phone_countries').on('change', function(){
    if(jQuery(this).val() == latepoint_helper.value_all){
      jQuery('.select-phone-countries-wrapper').hide();
    }else{
      jQuery('.select-phone-countries-wrapper').show();
    }
  });

  jQuery('.os-select-all-toggler').on('change', function(){
    var $connection_wrappers = jQuery(this).closest('.white-box').find('.os-complex-connections-selector .connection');
    if(jQuery(this).is(':checked')){
      latepoint_complex_selector_select($connection_wrappers);
    }else{
      latepoint_complex_selector_deselect($connection_wrappers);
    }
    return false;
  });


  jQuery('.os-main-location-selector').on('change', function(){
    var route = jQuery(this).data('route');
    var params = 'id=' + jQuery(this).val();
    var data = { action: latepoint_helper.route_action, route_name: route, params: params, layout: 'none', return_format: 'json' };
    jQuery('.latepoint-content-w').addClass('os-loading');
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(data){
        location.reload();
      }
    });
  });

  jQuery('.os-service-durations-w').on('click', '.os-remove-duration', function(){
    jQuery(this).closest('.duration-box').slideUp(300, function(){
      jQuery(this).remove();
    });
    return false;
  });


  jQuery('.menu-color-toggler').on('click', function(){
    jQuery('.latepoint-side-menu-w').toggleClass('dark');
    return false;
  });


  jQuery('.latepoint-mobile-top-menu-trigger').on('click', function(){
    jQuery(this).closest('.latepoint-all-wrapper').toggleClass('os-show-mobile-menu');
    if(jQuery(this).closest('.latepoint-all-wrapper').hasClass('os-show-mobile-menu')){
      jQuery('.latepoint-side-menu-w ul.side-menu > li.has-children > a').on('click', function(){
        jQuery(this).closest('li').toggleClass('menu-item-sub-open-mobile');
        return false;
      });
    }else{
      jQuery('.latepoint-side-menu-w ul.side-menu > li.has-children > a').off('click');
    }
    return false;
  });

  jQuery('.latepoint-mobile-top-search-trigger-cancel').on('click', function(){
    jQuery(this).closest('.latepoint-all-wrapper').removeClass('os-show-mobile-search');
    return false;
  });

  jQuery('.latepoint-mobile-top-search-trigger').on('click', function(){
    jQuery(this).closest('.latepoint-all-wrapper').toggleClass('os-show-mobile-search');
    if(jQuery(this).closest('.latepoint-all-wrapper').hasClass('os-show-mobile-search')){
      jQuery('.latepoint-top-search').trigger('focus');
    }
    return false;
  });


  jQuery('.latepoint-side-menu-w').on('click', '.top-user-info-toggler', function(){
    jQuery('.latepoint-user-info-dropdown').toggleClass('os-visible');
    return false;
  });

  jQuery('.latepoint-content').on('click', '.mobile-calendar-actions-trigger', function(){
    jQuery(this).closest('.calendar-mobile-controls').toggleClass('os-show-actions');
    return false;
  });

  jQuery('.latepoint-content').on('click', '.os-widget-header-actions-trigger', function(){
    jQuery(this).closest('.os-widget-header').toggleClass('os-show-actions');
    return false;
  });

  jQuery('.latepoint-content').on('click', '.mobile-table-actions-trigger', function(){
    jQuery(this).closest('.os-pagination-w').toggleClass('os-show-actions');
    return false;
  });



  


  jQuery('.download-csv-with-filters').on('click', function(){
    var filter_params = jQuery(this).closest('.table-with-pagination-w').find('.os-table-filter').serialize();
    filter_params+= '&download=csv';
    jQuery(this).attr('href', this.href + '&' + filter_params);
  });

  jQuery('select.pagination-page-select').on('change', function(){
    latepoint_filter_table(jQuery(this).closest('.table-with-pagination-w').find('table'), jQuery(this).closest('.pagination-page-select-w'), false);
  });

  jQuery('select.os-table-filter').on('change', function(){
    latepoint_filter_table(jQuery(this).closest('table'), jQuery(this).closest('.os-form-group'));
  });

  jQuery('input.os-table-filter').on('keyup', function(){
    latepoint_filter_table(jQuery(this).closest('table'), jQuery(this).closest('.os-form-group'));
  });


  jQuery('.customize-connection-btn').on('click', function(){
    jQuery(this).closest('.connection').toggleClass('show-customize-box');
    return false;
  });

  jQuery('.connection-children-list').on('click', 'li', function(){
    if(jQuery(this).hasClass('active')){
      jQuery(this).removeClass('active');
      jQuery(this).find('input.connection-child-is-connected').val('no');
    }else{
      jQuery(this).addClass('active');
      jQuery(this).find('input.connection-child-is-connected').val('yes');
    }
    latepoint_count_active_connections(jQuery(this).closest('.connection'));
    return false;
  });

  jQuery('.display-toggler-control').on('change', function(){
    let group = jQuery(this).data('toggler-group');
    let key = jQuery(this).val();
    jQuery('.display-toggler-target[data-toggler-group="' + group + '"]').hide();
    jQuery('.display-toggler-target[data-toggler-group="' + group + '"][data-toggler-key="'+ key +'"]').show();
    return false;
  });

  jQuery('.add-item-category-trigger').on('click', function(){
    jQuery('.add-item-category-box').toggle();
    jQuery('.os-new-item-category-form-w').toggle();
    return false;
  });

  jQuery('.latepoint-top-search').on('keyup', function(event){
    var $wrapper = jQuery(this).closest('.latepoint-top-search-w');
    $wrapper.addClass('os-loading');
    var query = jQuery(this).val();
    if(event.keyCode == 27){
      $wrapper.removeClass('typing');
      jQuery('.latepoint-top-search-results-w').html('');
      jQuery(this).val('');
      $wrapper.removeClass('os-loading');
      return;
    }
    if(query == ''){
      $wrapper.removeClass('typing');
      jQuery('.latepoint-top-search-results-w').html('');
      $wrapper.removeClass('os-loading');
      return;
    }
    var route = jQuery(this).data('route');
    var params = 'query=' + query;
    var data = { action: latepoint_helper.route_action, route_name: route, params: params, layout: 'none', return_format: 'json' };
    $wrapper.addClass('typing');
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(data){
        if(!$wrapper.hasClass('typing')) return;
        $wrapper.removeClass('os-loading');
        if(data.status === "success"){
          jQuery('.latepoint-top-search-results-w').html(data.message);
        }else{
          // console.log(data.message);
        }
      }
    });
  });


  jQuery('.appointment-status-selector').on('click', function(e){
    e.stopPropagation();
  });

  jQuery('.latepoint-show-license-details').on('click', function(e){
    jQuery(this).closest('.active-license-info').find('.license-info-w').slideToggle(200);
    return false;
  });

  jQuery('.aba-button-w').on('click', function(e){
    e.stopPropagation();
    var confirm_message = (jQuery(this).hasClass('aba-approve')) ? latepoint_helper.approve_confirm : latepoint_helper.reject_confirm;
    if(confirm(confirm_message)){
      var $box = jQuery(this).closest('.appointment-box-large');
      $box.find('.appointment-status-selector select').val(jQuery(this).data('status')).trigger('change');
    }
    return false;
  });



  jQuery('.appointment-status-selector select').on('change', function(e){
    var $wrapper = jQuery(this).closest('.appointment-status-selector');
    var route = $wrapper.data('route');
    var nonce = $wrapper.data('wp-nonce');
    var booking_id = $wrapper.data('booking-id');
    var status = jQuery(this).val();
    jQuery(this).closest('.appointment-box-large').attr('class', 'appointment-box-large status-' + status);
    var params = 'id=' + booking_id + '&status=' + status + '&_wpnonce=' + nonce;
    var data = { action: latepoint_helper.route_action, route_name: route, params: params, layout: 'none', return_format: 'json' };
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(data){
        if(data.status === "success"){
          latepoint_add_notification(data.message);
        }else{
          latepoint_add_notification(data.message, 'error');
          // console.log(data.message);
        }
      }
    });
  });

  jQuery('body').on('click', '.open-template-variables-panel', function(){
    jQuery('.latepoint-template-variables').toggleClass('is-visible');
    return false;
  });

  jQuery('body').on('click', '.close-template-variables-panel', function(){
    jQuery('.latepoint-template-variables').removeClass('is-visible');
    return false;
  });

  jQuery('body').on('click', '.open-layout-template-variables-panel', function(){
    jQuery('.latepoint-layout-template-variables').toggleClass('is-visible');
    return false;
  });

  jQuery('body').on('click', '.close-layout-template-variables-panel', function(){
    jQuery('.latepoint-layout-template-variables').removeClass('is-visible');
    return false;
  });

  jQuery('body').on('click', '.os-notifications .os-notification-close', function(){
    jQuery(this).closest('.item').remove();
    return false;
  });


  jQuery('body').on('keyup', '.os-form-group .os-form-control', function(){
    if(jQuery(this).val()){
      jQuery(this).closest('.os-form-group').addClass('has-value');
    }else{
      jQuery(this).closest('.os-form-group').removeClass('has-value');
    }
  });



  jQuery('.os-wizard-setup-w, .latepoint-settings-w, .custom-schedule-wrapper').on('click', '.ws-head', function(){
    var $schedule_wrapper = jQuery(this).closest('.weekday-schedule-w');
    $schedule_wrapper.toggleClass('is-editing').removeClass('day-off');
    $schedule_wrapper.find('.os-toggler').removeClass('off');
    $schedule_wrapper.find('input.is-active').val(1);
  });


  jQuery('.latepoint').on('click', '.wizard-add-edit-item-trigger', function(e){
    jQuery(this).addClass('os-loading');
    var add_item_route_name = jQuery(this).data('route');
    var item_info = {  };
    if(jQuery(this).data('id')){
      item_info.id = jQuery(this).data('id');
    }
    var data = { action: latepoint_helper.route_action, route_name: add_item_route_name, params: item_info, layout: 'none', return_format: 'json' };
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(data){
        jQuery('.wizard-add-edit-item-trigger.os-loading').removeClass('os-loading');
        if(data.status === "success"){
          jQuery('.os-wizard-step-content-i').html(data.message);
          jQuery('.os-wizard-setup-w').addClass('is-sub-editing');
          jQuery('.os-wizard-footer').hide();
          latepoint_init_wizard_content();
        }else{
          // console.log(data.message);
        }
      }
    });
  });




  // WIZARD NEXT BUTTON CLICK LOGIC
  jQuery('.latepoint').on('click', '.latepoint-pro-link-subtle', function(){
    jQuery('.pro-premium-features-list-wrapper').slideDown(200);
    jQuery('.pro-feature-banner').addClass('is-open');
    jQuery(this).hide();
    return false;
  });

  jQuery('.latepoint').on('click', '.os-wizard-trigger-next-btn', function(){
    var $next_btn = jQuery(this);
    $next_btn.addClass('os-loading');
    var current_step_code = jQuery('#wizard_current_step_code').val();
    var params = 'current_step_code='+current_step_code;

    // work periods step
    if(jQuery('.os-wizard-setup-w form.weekday-schedules-w').length){
      params+= '&'+ jQuery('.os-wizard-setup-w form.weekday-schedules-w .weekday-schedule-w:not(.day-off) input').serialize();
    }
    // agent/notifications step
    if(jQuery('.os-wizard-default-agent-form').length){
      params+= '&'+ jQuery('.os-wizard-default-agent-form input').serialize();

      var $form = $('.os-wizard-default-agent-form');
      var form_data = new FormData($form[0]);
      form_data.set('current_step_code', current_step_code);

      if (('intlTelInputGlobals' in window) && ('intlTelInputUtils' in window)) {
        // Get e164 formatted number from phone fields when form is submitted
        $form.find('input.os-mask-phone').each(function () {
          let telInstance = window.intlTelInputGlobals.getInstance(this);
          if(telInstance){
            const phoneInputName = this.getAttribute('name');
            const phoneInputValue = window.intlTelInputGlobals.getInstance(this).getNumber(window.intlTelInputUtils.numberFormat.E164);
            form_data.set(phoneInputName, phoneInputValue);
          }
        });
      }
      params = latepoint_formdata_to_url_encoded_string(form_data);
    }

    var data = {
      action: latepoint_helper.route_action,
      route_name: jQuery(this).data('route-name'),
      params: params,
      layout: 'none',
      return_format: 'json'};
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(data){
        $next_btn.removeClass('os-loading');
        if(data.status === "success"){
          jQuery('#wizard_current_step_code').val(data.step_code);
          jQuery('.os-wizard-setup-w').attr('class', 'os-wizard-setup-w step-' + data.step_code);
          jQuery('.os-wizard-step-content').html(data.message);
          latepoint_init_wizard_content();
          if(data.show_prev_btn){
            jQuery('.os-wizard-prev-btn').show();
          }else{
            jQuery('.os-wizard-prev-btn').hide();
          }
          if(data.show_next_btn){
            jQuery('.os-wizard-next-btn').show();
          }else{
            jQuery('.os-wizard-next-btn').hide();
          }
          if(!data.show_next_btn && !data.show_prev_btn){
            jQuery('.os-wizard-footer').hide();
          }else{
            jQuery('.os-wizard-footer').show();
          }
        }
      }
    });
    return false;
  });

  // WIZARD PREV BUTTON CLICK LOGIC
  jQuery('.latepoint').on('click', '.os-wizard-trigger-prev-btn', function(){
    var $prev_btn = jQuery(this);
    $prev_btn.addClass('os-loading');
    var current_step_code = jQuery('#wizard_current_step_code').val();
    var params = 'current_step_code='+current_step_code;
    var data = { action: latepoint_helper.route_action, route_name: jQuery(this).data('route-name'), params: params, layout: 'none', return_format: 'json'};
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(data){
        $prev_btn.removeClass('os-loading');
        if(data.status === "success"){
          jQuery('#wizard_current_step_code').val(data.step_code);
          jQuery('.os-wizard-setup-w').attr('class', 'os-wizard-setup-w step-' + data.step_code);
          jQuery('.os-wizard-step-content').html(data.message);
          latepoint_init_wizard_content();
          if(data.show_prev_btn){
            jQuery('.os-wizard-prev-btn').show();
          }else{
            jQuery('.os-wizard-prev-btn').hide();
          }
          if(data.show_next_btn){
            jQuery('.os-wizard-next-btn').show();
          }else{
            jQuery('.os-wizard-next-btn').hide();
          }
          if(!data.show_next_btn && !data.show_prev_btn){
            jQuery('.os-wizard-footer').hide();
          }else{
            jQuery('.os-wizard-footer').show();
          }
        }
      }
    });
    return false;
  });

  jQuery('.latepoint-content-w').on('change', '.os-widget .os-trigger-reload-widget', function(){
    latepoint_reload_widget(jQuery(this).closest('.os-widget'));
  });

  jQuery('.latepoint-content-w').on('click', '.os-widget .timeline-type-toggle .timeline-type-option', function(){
    jQuery(this).closest('.timeline-type-toggle').find('.timeline-type-option.active').removeClass('active');
    jQuery(this).addClass('active');
    jQuery('.timeline-and-availability-contents').removeClass('shows-appointments shows-availability').addClass('shows-' + jQuery(this).data('value'));
    jQuery('#' + jQuery(this).closest('.timeline-type-toggle').data('value-holder-id')).val(jQuery(this).data('value'));
  });


  dragula([].slice.apply(document.querySelectorAll('.os-categories-ordering-w .os-category-children')), {
    moves: function (el, container, handle) {
      return (handle.classList.contains('os-category-drag') || handle.classList.contains('os-category-item-drag'));
    },
  }).on('drop', function(el){
    var $categories_wrapper = jQuery('.os-categories-ordering-w');
    var category_datas = [];
    var item_datas = [];

    $categories_wrapper.find('.os-category-parent-w').each(function(index){
      var order_number = jQuery(this).index() + 1;
      var parent_id = jQuery(this).parent().closest('.os-category-parent-w').data('id') || 0;
      category_datas.push({id: jQuery(this).data('id'), order_number: order_number, parent_id: parent_id});
    });
    $categories_wrapper.find('.item-in-category-w').each(function(index){
      var item_order_number = jQuery(this).index() + 1;
      var category_id = jQuery(this).closest('.os-category-parent-w').data('id') || 0;
      item_datas.push({id: jQuery(this).data('id'), order_number: item_order_number, category_id: category_id});
    });
    latepoint_recalculate_items_count_in_category();
    var data = { action: latepoint_helper.route_action, route_name: $categories_wrapper.data('category-order-update-route'), params: {category_datas: category_datas, item_datas: item_datas}, return_format: 'json' }
    $categories_wrapper.addClass('os-loading');
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(response){
        $categories_wrapper.removeClass('os-loading');
        if(response.status === "success"){
          // latepoint_add_notification(response.message);
        }else{
          alert(response.message);
        }
      }
    });
  });


  // Universal re-ordering dragging for form blocks
  dragula([jQuery('.os-draggable-form-blocks')[0]], {
    moves: function (el, container, handle) {
      return handle.classList.contains('os-form-block-drag');
    },
  }).on('drop', function(el){
    var blocks_order_data = {};
    var $draggable_form_blocks_wrapper = jQuery('.os-draggable-form-blocks');
    $draggable_form_blocks_wrapper.find('.os-form-block').each(function(index){
      var new_order_number = jQuery(this).index() + 1;
      var $block_model_id = jQuery(this).find('.os-form-block-id');
      if($block_model_id.length && $block_model_id.val()) blocks_order_data[$block_model_id.val()] = new_order_number;
    });
    var data = { action: latepoint_helper.route_action,
                  route_name: $draggable_form_blocks_wrapper.data('order-update-route'),
                  params: {ordered_fields: blocks_order_data,
                  fields_for: $draggable_form_blocks_wrapper.data('fields-for')},
                  return_format: 'json' } 
    $draggable_form_blocks_wrapper.addClass('os-loading');
    jQuery.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(response){
        $draggable_form_blocks_wrapper.removeClass('os-loading');
      }
    });
  });


  jQuery('body.latepoint-admin').on('click', '.os-category-edit-btn, .os-category-edit-cancel-btn, .os-category-w .os-category-name', function(){
    jQuery(this).closest('.os-category-w').toggleClass('editing');
    return false;
  });

  jQuery('body.latepoint-admin').on('click', '.step-edit-btn, .step-edit-cancel-btn, .step-w .step-head', function(){
    jQuery(this).closest('.step-w').toggleClass('editing');
    return false;
  });
    
  jQuery('body.latepoint-admin').on('click', '.agent-info-change-agent-btn', function(){
    jQuery(this).closest('.agent-info-w').removeClass('selected').addClass('selecting');
    return false;
  });
  
  jQuery('body.latepoint-admin').on('click', '.agent-info-change-agent-btn', function(){
    jQuery(this).closest('.agent-info-w').removeClass('selected').addClass('selecting');
    return false;
  });
  

  jQuery('body.latepoint-admin').on('click', '.customer-info-create-btn', function(){
    jQuery(this).closest('.customer-info-w').removeClass('selecting').addClass('selected');
    return false;
  });

  jQuery('body.latepoint-admin').on('click', '.customer-info-load-btn', function(){
    jQuery(this).closest('.customer-info-w').removeClass('selected').addClass('selecting').find('.customers-selector-search-input').trigger('focus');
    return false;
  });

  jQuery('body.latepoint-admin').on('click', '.customers-selector-cancel', function(){
    jQuery(this).closest('.customer-info-w').removeClass('selecting').addClass('selected ');
    jQuery('.customers-options-list .customer-option').show();
    jQuery('.customers-selector-search-input').val('');
    return false;
  });

  // CUSTOMER SELECTOR

  // SERVICES SELECTOR
  jQuery('body.latepoint-admin').on('click', '.service-option-selected', function(){
    var $select = jQuery(this).closest('.os-services-select-field-w');
    if($select.hasClass('active')){
      $select.removeClass('active');
    }else{
      $select.addClass('active').find('input').trigger('focus');
    }
    return false;
  });


  jQuery('body.latepoint-admin').on('keyup', '.service-options-filter-input', function(){
    var $list = jQuery(this).closest('.services-options-list');
    var text = jQuery(this).val().toLowerCase();
    $list.find('.service-option').hide();

    // Search 
    $list.find('.service-option').each(function(){

      if(jQuery(this).text().toLowerCase().indexOf(""+text+"") != -1 ){
       jQuery(this).show();
      }
    });
    return false;
  });


  jQuery('.calendar-week-agent-w').on('click', '.calendar-load-target-date', function(event){
    jQuery(this).addClass('os-loading');
    latepoint_reload_week_view_calendar(jQuery(this).data('target-date'));
    return false;
  });

  jQuery('.calendar-week-agent-w').on('change', '.cc-availability-toggler #overlay_service_availability', function(event){
    if(jQuery(this).val() == 'on'){
      jQuery('.calendar-week-agent-w .cc-service-selector').show();
    }else{
      jQuery('.calendar-week-agent-w .cc-service-selector').hide();
    }
    latepoint_reload_week_view_calendar();
  });


  jQuery('.calendar-week-agent-w').on('change', '.trigger-weekly-calendar-reload', function(event){
    latepoint_reload_week_view_calendar();
    return false;
  });

  jQuery('.latepoint-admin').on('click', '.os-complex-connections-selector .selector-trigger', function(e){
    var $connection_wrapper = jQuery(this).closest('.connection');
    if($connection_wrapper.hasClass('active')){
      latepoint_complex_selector_deselect($connection_wrapper);
      jQuery(this).closest('.white-box').find('.os-select-all-toggler').prop('checked', false);
    }else{
      latepoint_complex_selector_select($connection_wrapper);
    }
    return false;
  });

  jQuery('.latepoint-admin').on('click', '.os-complex-connections-selector .item-quantity-selector', function(e){
    let val = parseInt(jQuery(this).closest('.item-quantity-selector-w').find('.item-quantity-selector-input').val());
    if(jQuery(this).data('sign') == 'plus'){
      val = val + 1;
    }else{
      val = val - 1;
    }
    val = (val > 0) ? val : 0;
    jQuery(this).closest('.item-quantity-selector-w').find('.item-quantity-selector-input').val(val).trigger('change');
    return false;
  });

  jQuery('.latepoint-admin').on('change', '.os-complex-connections-selector .item-quantity-selector-input', function(e){
    let $this = jQuery(this);
    let $connection_wrapper = jQuery(this).closest('.connection');
    if($this.val() > 0){
      latepoint_complex_selector_select($connection_wrapper, $this.val());
    }else{
      latepoint_complex_selector_deselect($connection_wrapper);
    }
    return false;
  });

  jQuery('.latepoint-admin').on('click', '.os-agents-selector .agent', function(){
    if(jQuery(this).hasClass('active')){
      jQuery(this).removeClass('active');
      jQuery(this).find('.connection-child-is-connected').val('no');
    }else{
      jQuery(this).addClass('active');
      jQuery(this).find('.connection-child-is-connected').val('yes');
    }
    return false;
  });

  jQuery('.latepoint-admin').on('click', '.os-services-selector .service', function(){
    if(jQuery(this).hasClass('active')){
      jQuery(this).removeClass('active');
      jQuery(this).find('.connection-child-is-connected').val('no');
    }else{
      jQuery(this).addClass('active');
      jQuery(this).find('.connection-child-is-connected').val('yes');
    }
    return false;
  });

  jQuery('.latepoint-admin').on( 'click', '.os-form-toggler-group', function( event ){
    jQuery(this).find('.os-toggler').trigger('click');
    return false;
  });

  jQuery('.latepoint-admin').on( 'click', '.os-toggler', function( event ){
    let $toggler = jQuery(this);
    if($toggler.data('confirm')){
      if(!confirm($toggler.data('confirm'))) return false;
    }
    if($toggler.hasClass('on')){
      $toggler.removeClass('on').addClass('off');
    }else{
      $toggler.removeClass('off').addClass('on');
    }
    if($toggler.data('for')){
      if($toggler.hasClass('os-toggler-radio')){
        // radio
        // uncheck all radio buttons with the same name
        let $radio = jQuery('#' + $toggler.data('for'));
        jQuery('input[type="radio"][name="'+ $radio.prop('name') + '"]:checked').each(function(index){
          let toggle_content_id = jQuery(this).prop('checked', false).closest('.os-toggler-w').find('.os-toggler.on').removeClass('on').addClass('off').data('controlled-toggle-id');
          jQuery('#'+ toggle_content_id).hide();
        });
        $radio.prop('checked', !$toggler.hasClass('off'));
      }else{
        var $hiddenInput = jQuery('input[type="hidden"]#' + $toggler.data('for'));
        if($hiddenInput.length){
          // hidden input
          if($toggler.data('is-string-value')){
            $hiddenInput.val($toggler.hasClass('off') ? 'off' : 'on').trigger('change');
          }else{
            $hiddenInput.val($toggler.hasClass('off') ? 0 : 1).trigger('change');
          }

          if($toggler.data('os-instant-update')){
            let data = new FormData();

            let params = $hiddenInput.serialize();
            if($toggler.data('nonce')) params+= '&_wpnonce='+$toggler.data('nonce');
            data.append('params', params);
            data.append('action', latepoint_helper.route_action);
            data.append('route_name', $toggler.data('os-instant-update'));
            data.append('return_format', 'json');

            jQuery.ajax({
              type: "post",
              dataType: "json",
              processData: false,
              contentType: false,
              url: latepoint_timestamped_ajaxurl(),
              data: data,
              success: function (response) {

              }
            });
          }
        }else{
          // checkbox
          jQuery('#' + $toggler.data('for')).prop('checked', !$toggler.hasClass('off'));
        }
      }
    }
    if($toggler.data('controlled-toggle-id')){
      if($toggler.hasClass('off')){
        jQuery('#' + $toggler.data('controlled-toggle-id')).hide();
      }else{
        jQuery('#' + $toggler.data('controlled-toggle-id')).show();
      }
    }
    $toggler.trigger('ostoggler:toggle');
    return false;
  });



  // UPLOAD/REMOVE IMAGE LINK LOGIC
  jQuery('.latepoint-admin').on( 'click', '.os-image-selector-trigger', function( event ){
    var frame;

    event.preventDefault();

    var $image_uploader_trigger = jQuery(this);
    var $image_selector_w = jQuery(this).closest('.os-image-selector-w');
    var $image_container = $image_selector_w.find('.os-image-container');
    var $image_id_holder = $image_selector_w.find('.os-image-id-holder');

    let is_avatar = $image_selector_w.hasClass('is-avatar');

    var image_exists = is_avatar ? $image_container.find('.image-self').length : $image_container.find('img').length;

    if(image_exists){
      $image_id_holder.val('');
      $image_selector_w.removeClass('has-image');
      $image_container.html('');
      $image_uploader_trigger.find('.os-text-holder').text($image_uploader_trigger.data('label-set-str'));
    }else{
      // If the media frame already exists, reopen it.
      if ( frame ) {
        frame.open();
        return false;
      }
      
      // Create a new media frame
      frame = wp.media({
        title: 'Select or Upload Media',
        button: { text: 'Use this media' },
        multiple: false
      });

      frame.on( 'select', function() {
        var attachment = frame.state().get('selection').first().toJSON();
        if(is_avatar){
          $image_container.html( '<div class="image-self" style="background-image: url('+attachment.url+')"></div>' );
        }else{
          $image_container.html( '<img src="'+attachment.url+'" alt=""/>' );
        }
        $image_id_holder.val( attachment.id );
        $image_selector_w.addClass('has-image');
        $image_uploader_trigger.find('.os-text-holder').text($image_uploader_trigger.data('label-remove-str'));
      });

      frame.open();
    }
    
    return false;
  });



  jQuery('body').on('click', '.latepoint-lightbox-close', function(){
    latepoint_lightbox_close();
    return false;
  });


  jQuery('body').on('click', '.latepoint-side-panel-close-trigger', function(){
    latepoint_close_side_panel();
    return false;
  });
  jQuery('body').on('click', '.latepoint-side-sub-panel-close-trigger', function(){
    jQuery(this).closest('.side-sub-panel-wrapper').remove();
    return false;
  });
  


  jQuery('body.latepoint-admin').on('click', '.time-ampm-select', function(){
    let $form = jQuery(this).closest('.order-item-booking-data-form-wrapper');
    jQuery(this).closest('.time-ampm-w').find('.active').removeClass('active');
    jQuery(this).addClass('active');
    var ampm_value = jQuery(this).data('ampm-value');
    jQuery(this).closest('.os-time-group').find('.ampm-value-hidden-holder').val(ampm_value);
    if(jQuery(this).closest('.quick-start-time-w').length){
      // if called from quick edit form - we need to make sure it accurately changes time to next day if end time is earlier than start time
      latepoint_set_booking_end_time($form);
      latepoint_is_next_day($form);
    }
    if(jQuery(this).closest('.quick-end-time-w').length){
      latepoint_is_next_day($form);
    }
    return false;
  });


  jQuery('body.latepoint-admin').on('click', '.latepoint-lightbox-shadow', function(){
    latepoint_lightbox_close();
    return false;
  });

  jQuery('body.latepoint-admin').on('click', '.latepoint-side-panel-shadow', function(){
    jQuery('.latepoint-side-panel-w').remove();
    return false;
  });

  // SCHEDULE

  jQuery('body.latepoint-admin').on('click', '.ws-period-remove', function(e){
    jQuery(this).closest('.ws-period').remove();
    return false;
  });


  jQuery('.latepoint-admin').on( 'click', '.weekday-schedule-w .os-toggler', function( event ){
    if(jQuery(this).hasClass('off')){
      jQuery(this).closest('.weekday-schedule-w').addClass('day-off').removeClass('is-editing').find('input.is-active').val(0);
    }else{
      jQuery(this).closest('.weekday-schedule-w').removeClass('day-off').addClass('is-editing').find('input.is-active').val(1);
    }
    return false;
  });

  

});