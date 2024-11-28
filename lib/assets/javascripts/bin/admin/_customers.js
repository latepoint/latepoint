/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

function latepoint_init_quick_customer_form(){
  let $customer_form_wrapper = jQuery('.quick-customer-form-w');
  latepoint_init_input_masks($customer_form_wrapper);


  $customer_form_wrapper.find('.customer-quick-edit-form').on('submit', function(e){
    if(jQuery(this).find('button[type="submit"]').hasClass('os-loading')) return false;
    e.preventDefault();
    latepoint_submit_quick_customer_form();
  });


  $customer_form_wrapper.find('.quick-customer-form-view-log-btn').on('click', function(){
    var $trigger_elem = jQuery(this);
    $trigger_elem.addClass('os-loading');
    var route = $trigger_elem.data('route');
    var data = { action: 'latepoint_route_call', route_name: route, params: {customer_id: $trigger_elem.data('customer-id')}, return_format: 'json' }
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
}


function latepoint_submit_quick_customer_form(){
  let $quick_edit_form = jQuery('form.customer-quick-edit-form');

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
      $quick_edit_form.find('button[type="submit"]').removeClass('os-loading');
      if(response.form_values_to_update){
        jQuery.each(response.form_values_to_update, function(name, value){
          $quick_edit_form.find('[name="'+ name +'"]').val(value);
        });
      }
      if (response.status === "success") {
        latepoint_add_notification(response.message);
        latepoint_reload_after_customer_save();
      }else{
        latepoint_add_notification(response.message, 'error');
      }
    }
  });

}



function latepoint_reload_after_customer_save(){
  latepoint_reload_calendar_view();

  jQuery('.os-widget').each(function(){
    latepoint_reload_widget(jQuery(this));
  });
  if(jQuery('table.os-reload-on-booking-update').length) latepoint_filter_table(jQuery('table.os-reload-on-booking-update'), jQuery('table.os-reload-on-booking-update'));
  latepoint_close_side_panel();
}