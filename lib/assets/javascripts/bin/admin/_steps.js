/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */


function latepoint_preview_init_step_category_items(step_code){
  jQuery('.booking-form-preview .os-item-category-info').on('click', function(){
    var $booking_form_element = jQuery(this).closest('.booking-form-preview');
    jQuery(this).closest('.latepoint-step-content').addClass('selecting-item-category');
    var $category_wrapper = jQuery(this).closest('.os-item-category-w');
    var $main_parent = jQuery(this).closest('.os-item-categories-main-parent');
    if($category_wrapper.hasClass('selected')){
      $category_wrapper.removeClass('selected');
      if($category_wrapper.parent().closest('.os-item-category-w').length){
        $category_wrapper.parent().closest('.os-item-category-w').addClass('selected');
      }else{
        $main_parent.removeClass('show-selected-only');
      }
    }else{
      $main_parent.find('.os-item-category-w.selected').removeClass('selected');
      $main_parent.addClass('show-selected-only');
      $category_wrapper.addClass('selected');
    }
    return false;
  });
}

function latepoint_booking_form_discard_changes(){

  let form_data = new FormData(jQuery('.booking-form-preview-settings')[0]);

  var data = {
    action: latepoint_helper.route_action,
    route_name: jQuery('.booking-form-preview-settings').data('route-name'),
    params: latepoint_formdata_to_url_encoded_string(form_data),
    layout: 'none',
    return_format: 'json'
  }

  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(data){
      if(data.status === "success"){
        jQuery('.booking-form-preview-inner').html(data.booking_form_html);
        latepoint_init_booking_form_preview();
      }else{
        latepoint_add_notification(data.message, 'error');
      }
    }
  });
}

function latepoint_booking_form_save_changes(){

  let form_data = new FormData(jQuery('.booking-form-preview-settings')[0]);

  jQuery('.editable-setting').each(function(){
    form_data.set('steps_settings' + jQuery(this).data('setting-key'), jQuery(this).html());
  });


  form_data.set('steps_settings' + jQuery('.bf-side-media-picker-trigger').find('.os-image-id-holder').prop('name'), jQuery('.bf-side-media-picker-trigger').find('.os-image-id-holder').val());


  var data = {
    action: latepoint_helper.route_action,
    route_name: jQuery('.booking-form-preview-settings').data('route-name'),
    params: latepoint_formdata_to_url_encoded_string(form_data),
    layout: 'none',
    return_format: 'json'
  }

  jQuery.ajax({
    type : "post",
    dataType : "json",
    url : latepoint_timestamped_ajaxurl(),
    data : data,
    success: function(data){
      jQuery('.booking-form-preview-settings').removeClass('os-loading');
      if(data.status === "success"){
        jQuery('.bf-preview-step-settings').html(data.step_settings_html);
        jQuery('.booking-form-preview-inner').html(data.booking_form_html);
        jQuery('#latepoint-main-admin-inline-css').html(data.css_variables);
        latepoint_init_booking_form_preview();
      }else{
        latepoint_add_notification(data.message, 'error');
      }
    }
  });
}

function latepoint_init_booking_form_preview(){

  latepoint_preview_init_step_category_items();
  latepoint_booking_form_preview_init_datepicker();


  jQuery('.booking-form-preview-wrapper').on('click', '.os-step-tab', function(){
    jQuery(this).closest('.os-step-tabs').find('.os-step-tab').removeClass('active');
    jQuery(this).addClass('active');
    var target = jQuery(this).data('target');
    jQuery(this).closest('.os-step-tabs-w').find('.os-step-tab-content').hide();
    jQuery(target).show();
  });


  jQuery('.bf-save-btn').on('click', function(){
    jQuery(this).addClass('os-loading');
    latepoint_booking_form_save_changes();
    return false;
  });

  jQuery('.bf-cancel-save-btn').on('click', function(){
    jQuery(this).addClass('os-loading');
    latepoint_booking_form_discard_changes();
    return false;
  });


  jQuery('.booking-form-preview .bf-next-btn').on('click', function(){
    jQuery(this).addClass('os-loading');
    jQuery("#selected_step_code > option:selected")
        .prop("selected", false)
        .next()
        .prop("selected", true).trigger('change');
  });

  jQuery('.booking-form-preview .bf-prev-btn').on('click', function(){
    jQuery(this).addClass('os-loading');
    jQuery("#selected_step_code > option:selected")
        .prop("selected", false)
        .prev()
        .prop("selected", true).trigger('change');
  });


  jQuery('.booking-form-preview .os-image-selector-trigger').on('click', function(){
    jQuery('.booking-form-preview').addClass('has-changes');
  });

  jQuery('.booking-form-preview .editable-setting').on('focus', function(){
    jQuery('.booking-form-preview').addClass('has-changes');
  });


  let editor = new MediumEditor('.booking-form-preview .os-editable', {toolbar: {
        buttons: [
          {
                name: 'bold',
                classList: ['latepoint-icon', 'latepoint-icon-format_bold'],
            },
          {
                name: 'anchor',
                classList: ['latepoint-icon', 'latepoint-icon-format_link'],
            },
          {
                name: 'h3',
                classList: ['latepoint-icon', 'latepoint-icon-format_h3'],
            },
          {
                name: 'h4',
                classList: ['latepoint-icon', 'latepoint-icon-format_h4'],
            },
          {
                name: 'h5',
                classList: ['latepoint-icon', 'latepoint-icon-format_h5'],
            },

        ]
  }
  });
  let editor_basic = new MediumEditor('.booking-form-preview .os-editable-basic', {toolbar: {
        buttons: [
          {
                name: 'bold',
                classList: ['latepoint-icon', 'latepoint-icon-format_bold'],
            },
          {
                name: 'italic',
                classList: ['latepoint-icon', 'latepoint-icon-format_italic'],
            },
          {
                name: 'underline',
                classList: ['latepoint-icon', 'latepoint-icon-format_underlined'],
            },
          {
                name: 'anchor',
                classList: ['latepoint-icon', 'latepoint-icon-format_link'],
            },

        ]
  }
  });
}

function latepoint_reload_booking_form_preview(){
  latepoint_booking_form_save_changes();
}

function latepoint_init_steps_settings(){

  jQuery('.booking-form-preview-settings').on('change', ' select, input[type="hidden"]', function(){
    jQuery('.booking-form-preview-settings').addClass('os-loading');
    latepoint_reload_booking_form_preview();
  });

  jQuery('.trigger-custom-color-save').on('click', function(){
    jQuery('.booking-form-preview-settings').addClass('os-loading');
    latepoint_booking_form_save_changes();
    return false;
  });

  jQuery('.bf-color-scheme-color-trigger').on('click', function(){
    jQuery('.bf-color-scheme-color-trigger.is-selected').removeClass('is-selected');
    jQuery(this).addClass('is-selected');
    let color_scheme = jQuery(this).data('color-code');
    jQuery('.os-color-scheme-selector-wrapper select').val(color_scheme).trigger('change');
    if(color_scheme == 'custom'){
      jQuery('.os-custom-color-selector-wrapper').removeClass('is-hidden');
    }else{
      jQuery('.os-custom-color-selector-wrapper').addClass('is-hidden');
    }
    return false;
  });

  jQuery('.os-section-collapsible-trigger').on('click', function(){
    jQuery(this).closest('.os-section-collapsible-wrapper').toggleClass('is-open');
    return false;
  })
}


function latepoint_booking_form_preview_init_timeslots($booking_form_element = false){
  if(!$booking_form_element) return;
  $booking_form_element.on('click', '.dp-timepicker-trigger', function(){
    if(jQuery(this).hasClass('is-booked') || jQuery(this).hasClass('is-off')){
      // Show error message that you cant select a booked period
    }else{
      if(jQuery(this).hasClass('selected')){
        jQuery(this).removeClass('selected');
        jQuery(this).find('.dp-success-label').remove();
      }else{
        $booking_form_element.find('.dp-timepicker-trigger.selected').removeClass('selected').find('.dp-success-label').remove();
        var selected_timeslot_time = jQuery(this).find('.dp-label-time').html();
        jQuery(this).addClass('selected').find('.dp-label').prepend('<span class="dp-success-label">' + latepoint_helper.datepicker_timeslot_selected_label + '</span>');

        var minutes = parseInt(jQuery(this).data('minutes'));
        var timeshift_minutes = parseInt($booking_form_element.find('.latepoint_timeshift_minutes').val());
        // we substract timeshift minutes because its timeshift minutes that the business is running in, in opposite of what we do when we generate a calendar for a client
        if(timeshift_minutes) minutes = minutes - timeshift_minutes;
        var start_date = new Date($booking_form_element.find('.os-day.selected').data('date'));
        if(minutes < 0){
          // business minutes are in previous day
          minutes = 24*60 + minutes;
          // move start date back 1 day
          start_date.setDate(start_date.getDate() - 1);
        }else if(minutes >= 24*60){
          // business minutes are in next day
          minutes = minutes - 24*60;
          start_date.setDate(start_date.getDate() + 1);
        }
        $booking_form_element.find('.latepoint_start_date').val(start_date.toISOString().split('T')[0])
        latepoint_trigger_next_btn($booking_form_element);
      }
    }
    return false;
  });
}


function latepoint_booking_form_preview_day_timeslots($day){
  let $wrapper_element = jQuery('.booking-form-preview');
  $day.addClass('selected');

  var service_duration = $day.data('service-duration');
  var interval = $day.data('interval');
  var work_start_minutes = $day.data('work-start-time');
  var work_end_minutes = $day.data('work-end-time');
  var total_work_minutes = $day.data('total-work-minutes');
  var bookable_minutes = [];
  var available_capacities_of_bookable_minute = [];
  if($day.attr('data-bookable-minutes')){
    if($day.data('bookable-minutes').toString().indexOf(':') > -1){
      // has capacity information embedded into bookable minutes string
      let bookable_minutes_with_capacity = $day.data('bookable-minutes').toString().split(',');
      for(let i = 0; i < bookable_minutes_with_capacity.length; i++){
        bookable_minutes.push(parseInt(bookable_minutes_with_capacity[i].split(':')[0]));
        available_capacities_of_bookable_minute.push(parseInt(bookable_minutes_with_capacity[i].split(':')[1]));
      }
    }else{
      bookable_minutes = $day.data('bookable-minutes').toString().split(',').map(Number);
    }
  }
  var work_minutes = $day.data('work-minutes').toString().split(',').map(Number);

  var $timeslots = $wrapper_element.find('.timeslots');
  $timeslots.html('');

  if(total_work_minutes > 0 && bookable_minutes.length && work_minutes.length){
    var prev_minutes = false;
    work_minutes.forEach(function(current_minutes){
      var ampm = latepoint_am_or_pm(current_minutes);

      var timeslot_class = 'dp-timepicker-trigger';
      var timeslot_available_capacity = 0;
      if($wrapper_element.find('.os-dates-w').data('time-pick-style') == 'timeline'){
        timeslot_class+= ' dp-timeslot';
      }else{
        timeslot_class+= ' dp-timebox';
      }

      if(prev_minutes !== false && ((current_minutes - prev_minutes) > service_duration)){
        // show interval that is off between two work periods
        var off_label = latepoint_minutes_to_hours_and_minutes(prev_minutes + service_duration)+' '+ latepoint_am_or_pm(prev_minutes + service_duration) + ' - ' + latepoint_minutes_to_hours_and_minutes(current_minutes)+' '+latepoint_am_or_pm(current_minutes);
        var off_width = (((current_minutes - prev_minutes - service_duration) / total_work_minutes) * 100);
        $timeslots.append('<div class="'+ timeslot_class +' is-off" style="max-width:'+ off_width +'%; width:'+ off_width +'%"><span class="dp-label">' + off_label + '</span></div>');
      }

      if(!bookable_minutes.includes(current_minutes)){
        timeslot_class+= ' is-booked';
      }else{
        if(available_capacities_of_bookable_minute.length) timeslot_available_capacity = available_capacities_of_bookable_minute[bookable_minutes.indexOf(current_minutes)];
      }
      var tick_html = '';
      var capacity_label = '';
      var capacity_label_html = '';
      var capacity_internal_label_html = '';

      if(((current_minutes % 60) == 0) || (interval >= 60)){
        timeslot_class+= ' with-tick';
        tick_html = '<span class="dp-tick"><strong>'+latepoint_minutes_to_hours_preferably(current_minutes)+'</strong>'+' '+ampm+'</span>';
      }
      var timeslot_label = latepoint_minutes_to_hours_and_minutes(current_minutes)+' '+ampm;
      if(latepoint_show_booking_end_time()){
        var end_minutes = current_minutes + service_duration;
        if(end_minutes > 1440) end_minutes = end_minutes - 1440;
        var end_minutes_ampm = latepoint_am_or_pm(end_minutes);
        timeslot_label+= ' - <span class="dp-label-end-time">' + latepoint_minutes_to_hours_and_minutes(end_minutes)+' '+end_minutes_ampm + '</span>';
      }
      if(timeslot_available_capacity){
        var spaces_message = timeslot_available_capacity > 1 ? latepoint_helper.many_spaces_message : latepoint_helper.single_space_message;
        capacity_label = timeslot_available_capacity + ' ' + spaces_message;
        capacity_label_html = '<span class="dp-capacity">' + capacity_label + '</span>';
        capacity_internal_label_html = '<span class="dp-label-capacity">' + capacity_label + '</span>';
      }
      timeslot_label = timeslot_label.trim();
      $timeslots.removeClass('slots-not-available').append('<div class="'+timeslot_class+'" data-minutes="' + current_minutes + '"><span class="dp-label">' + capacity_internal_label_html + '<span class="dp-label-time">' + timeslot_label + '</span>' +'</span>'+tick_html+ capacity_label_html + '</div>');
      prev_minutes = current_minutes;
    });
  }else{
    // No working hours this day
    $timeslots.addClass('slots-not-available').append('<div class="not-working-message">' + latepoint_helper.msg_not_available + "</div>");
  }
  jQuery('.times-header-label span').text($day.data('nice-date'));
  $wrapper_element.find('.time-selector-w').slideDown(200);
}

function latepoint_booking_form_preview_init_monthly_calendar_navigation($booking_form_element){

  if(!$booking_form_element) return;
  $booking_form_element.on('click', '.os-month-next-btn', function(){
    var $booking_form_element = jQuery(this).closest('.latepoint-booking-form-element');
    var next_month_route_name = jQuery(this).data('route');
    if($booking_form_element.find('.os-monthly-calendar-days-w.active + .os-monthly-calendar-days-w').length){
      $booking_form_element.find('.os-monthly-calendar-days-w.active').removeClass('active').next('.os-monthly-calendar-days-w').addClass('active');
      latepoint_booking_form_preview_calendar_set_month_label($booking_form_element);
    }else{
      alert('Disabled in preview');
    }
    latepoint_calendar_show_or_hide_prev_next_buttons($booking_form_element);
    return false;
  });
  $booking_form_element.on('click', '.os-month-prev-btn', function(){
    var $booking_form_element = jQuery(this).closest('.latepoint-booking-form-element');
    if($booking_form_element.find('.os-monthly-calendar-days-w.active').prev('.os-monthly-calendar-days-w').length){
      $booking_form_element.find('.os-monthly-calendar-days-w.active').removeClass('active').prev('.os-monthly-calendar-days-w').addClass('active');
      latepoint_booking_form_preview_calendar_set_month_label($booking_form_element);
    }
    return false;
  });
}


function latepoint_booking_form_preview_calendar_set_month_label(){
  jQuery('.os-current-month-label .current-month').text(jQuery('.os-monthly-calendar-days-w.active').data('calendar-month-label'));
  jQuery('.os-current-month-label .current-year').text(jQuery('.os-monthly-calendar-days-w.active').data('calendar-year'));
}



function latepoint_booking_form_preview_init_datepicker(){
  let $booking_form_element = jQuery('.latepoint-booking-form-element');
  latepoint_booking_form_preview_init_timeslots($booking_form_element);
  latepoint_booking_form_preview_init_monthly_calendar_navigation($booking_form_element);
  $booking_form_element.on('click', '.os-months .os-day', function(){
    if(jQuery(this).hasClass('os-day-passed')) return false;
    if(jQuery(this).hasClass('os-not-in-allowed-period')) return false;
    if(jQuery(this).closest('.os-monthly-calendar-days-w').hasClass('hide-if-single-slot')){

      // HIDE TIMESLOT IF ONLY ONE TIMEPOINT
      if(jQuery(this).hasClass('os-not-available')){
        // clicked on a day that has no available timeslots
        // do nothing
      }else{
        $booking_form_element.find('.os-day.selected').removeClass('selected');
        jQuery(this).addClass('selected');
        // set date
        $booking_form_element.find('.latepoint_start_date').val(jQuery(this).data('date'));
        if(jQuery(this).hasClass('os-one-slot-only')){
          // clicked on a day that has only one slot available
          var bookable_minutes = jQuery(this).data('bookable-minutes').toString().split(':')[0];
          var selected_timeslot_time = latepoint_format_minutes_to_time(Number(bookable_minutes), Number(jQuery(this).data('service-duration')));
          $booking_form_element.find('.time-selector-w').slideUp(200);
        }else{
          // regular day with more than 1 timeslots available
          // build timeslots
          latepoint_booking_form_preview_day_timeslots(jQuery(this));
          // clear time and hide next btn
        }
      }
    }else{
      // SHOW TIMESLOTS EVEN IF ONLY ONE TIMEPOINT
      $booking_form_element.find('.latepoint_start_date').val(jQuery(this).data('date'));
      $booking_form_element.find('.os-day.selected').removeClass('selected');
      jQuery(this).addClass('selected');

      // build timeslots
      latepoint_booking_form_preview_day_timeslots(jQuery(this));
      // clear time and hide next btn
    }


    return false;
  });
}
