function latepoint_generate_form_message_html(messages, status){
  var message_html = '<div class="os-form-message-w status-' + status + '"><ul>';
  if(Array.isArray(messages)){
    messages.forEach(function(message){
      message_html+= '<li>' + message + '</li>';
    });
  }else{
    message_html+= '<li>' + messages + '</li>';
  }
  message_html+= '</ul></div>';
  return message_html;
}

function latepoint_display_in_side_sub_panel(html){
  if(!jQuery('.latepoint-side-panel-w').length) latepoint_show_data_in_side_panel('');
  jQuery('.latepoint-side-panel-w .latepoint-side-panels .side-sub-panel-wrapper').remove();
  jQuery('.latepoint-side-panel-w .latepoint-side-panels').append(html);
}

function latepoint_clear_form_messages($form){
  $form.find('.os-form-message-w').remove();
}

function latepoint_show_data_in_side_panel(message, extra_classes = '', close_btn = true){
  jQuery('.latepoint-side-panel-w').remove();
  jQuery('body').append('<div class="latepoint-side-panel-w ' + extra_classes + ' os-loading"><div class="latepoint-side-panel-shadow"></div><div class="latepoint-side-panels"><div class="latepoint-side-panel-i"></div></div></div>');
  jQuery('.latepoint-side-panel-i').html(message);
  if(close_btn){
    jQuery('.latepoint-side-panel-i').find('.os-form-header .latepoint-side-panel-close').remove();
    jQuery('.latepoint-side-panel-i').find('.os-form-header').append('<a href="#" class="latepoint-side-panel-close latepoint-side-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>');
  }
  setTimeout(function(){
    jQuery('.latepoint-side-panel-w').removeClass('os-loading');
  }, 100);
}

function latepoint_show_data_in_lightbox(message, extra_classes = '', close_btn = true, tag = 'div', inner_extra_classes = '', inner_tag = 'div'){
  jQuery('.latepoint-lightbox-w').remove();
  let lightbox_css_classes = 'latepoint-lightbox-w latepoint-w latepoint-border-radius-' + latepoint_helper.style_border_radius+ ' ';
  if(extra_classes) lightbox_css_classes+= extra_classes;
  let lightbox_css_inner_classes = 'latepoint-lightbox-i ';
  if(inner_extra_classes) lightbox_css_inner_classes += inner_extra_classes;

  let close_btn_html = close_btn ? '<a href="#" class="latepoint-lightbox-close" tabindex="0"><i class="latepoint-icon latepoint-icon-x"></i></a>' : '';
  jQuery('body').append('<'+tag+' class="'+ lightbox_css_classes +'"><'+inner_tag+' class="'+ lightbox_css_inner_classes +'">' + message + close_btn_html + '</'+inner_tag+'><div class="latepoint-lightbox-shadow"></div></'+tag+'>');

  jQuery('body').addClass('latepoint-lightbox-active');
}



// DOCUMENT READY
jQuery(function( $ ) {

  if($('.latepoint').find('[data-os-action-onload]').length){
    $('.latepoint').find('[data-os-action-onload]').each(function(){
      var $this = jQuery(this);
      $this.addClass('os-loading');
      var params = $this.data('os-params');
      var return_format = $this.data('os-return-format') ? $this.data('os-return-format') : 'json'
      var data = { action: 'latepoint_route_call', route_name: $this.data('os-action-onload'), params: params, return_format: return_format }
      jQuery.ajax({
        type : "post",
        dataType : "json",
        url : latepoint_timestamped_ajaxurl(),
        data : data,
        success: function(response) {
          $this.removeClass('os-loading');
          if (response.status === "success") {
            if($this.data('os-output-target') == 'self'){
              $this.html(response.message);
            }
          }
        }
      });
    });
  }

  /*
    Ajax buttons action
  */
  $('.latepoint').on('click', 'button[data-os-action], a[data-os-action], div[data-os-action], span[data-os-action], tr[data-os-action]', function(e){
    var $this = jQuery(this);
    if($this.data('os-prompt') && !confirm($this.data('os-prompt'))) return false;
    var params = $this.data('os-params');
    if($this.data('os-source-of-params')){
      var form_data = latepoint_create_form_data_from_non_form_element($($this.data('os-source-of-params')));
      params = latepoint_formdata_to_url_encoded_string(form_data);
    }
    var return_format = $this.data('os-return-format') ? $this.data('os-return-format') : 'json'
    var data = { action: 'latepoint_route_call', route_name: $this.data('os-action'), params: params, return_format: return_format }
    $this.addClass('os-loading');
    if($this.data('os-output-target') == 'side-panel'){
      $('.latepoint-side-panel-w').remove();
      let css_classes = $this.data('os-lightbox-classes') ? $this.data('os-lightbox-classes') : '';
      $('body').append('<div class="latepoint-side-panel-w ' + css_classes + ' os-loading"><div class="latepoint-side-panel-shadow"></div><div class="latepoint-side-panels"><div class="latepoint-side-panel-i"></div></div></div>');
    }
    $.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(response){
        if(response.status === "success"){
          if($this.data('os-output-target') == 'lightbox'){
            latepoint_show_data_in_lightbox(response.message, $this.data('os-lightbox-classes'), ($this.data('os-lightbox-no-close-button') !== 'yes'), $this.data('os-lightbox-tag'), $this.data('os-lightbox-inner-classes'), $this.data('os-lightbox-inner-tag'));
          }else if($this.data('os-output-target') == 'side-panel'){
            $('.latepoint-side-panel-i').html(response.message);
            jQuery('.latepoint-side-panel-i').find('.os-form-header .latepoint-side-panel-close').remove();
            jQuery('.latepoint-side-panel-i').find('.os-form-header').append('<a href="#" class="latepoint-side-panel-close latepoint-side-panel-close-trigger"><i class="latepoint-icon latepoint-icon-x"></i></a>');
            setTimeout(function(){
              $('.latepoint-side-panel-w').removeClass('os-loading');
            }, 100);
          }else if($this.data('os-success-action') == 'reload'){
            latepoint_add_notification(response.message);
            location.reload();
            return;
          }else if($this.data('os-success-action') == 'redirect'){
            if($this.data('os-redirect-to')){
              latepoint_add_notification(response.message);
              window.location.replace($this.data('os-redirect-to'));
            }else{
              window.location.replace(response.message); 
            }
            return;
          }else if($this.data('os-output-target') && $($this.data('os-output-target')).length){
            if($this.data('os-output-target-do') == 'append') {
              $($this.data('os-output-target')).append(response.message);
            }else if($this.data('os-output-target-do') == 'prepend'){
              $($this.data('os-output-target')).prepend(response.message);
            }else{
              $($this.data('os-output-target')).html(response.message);
            }
          }else{
            switch($this.data('os-before-after')){
              case 'before':
                $this.before(response.message);
                break;
              case 'after':
                $this.after(response.message);
                break;
              case 'replace':
                $this.replaceWith(response.message);
                break;
              case 'none':
                break;
              default:
                latepoint_add_notification(response.message);
            }
          }
          if($this.data('os-after-call')){
            var func_name = $this.data('os-after-call');
            var callback = false;
            if(func_name.includes('.')){
              var func_arr = func_name.split('.');
              if(typeof window[func_arr[0]][func_arr[1]] !== 'function'){
                console.log(func_name + ' is undefined');
              }
              if($this.data('os-pass-this') && $this.data('os-pass-response')){
                window[func_arr[0]][func_arr[1]]($this, response);
              }else if($this.data('os-pass-this')){
                window[func_arr[0]][func_arr[1]]($this);
              }else if($this.data('os-pass-response')){
                window[func_arr[0]][func_arr[1]](response);
              }else{
                window[func_arr[0]][func_arr[1]]();
              }
            }else{
              if(typeof window[func_name] !== 'function'){
                console.log(func_name + ' is undefined');
              }
              if($this.data('os-pass-this') && $this.data('os-pass-response')){
                window[func_name]($this, response);
              }else if($this.data('os-pass-this')){
                window[func_name]($this);
              }else if($this.data('os-pass-response')){
                window[func_name](response);
              }else{
                window[func_name]();
              }
            }
          }
          $this.removeClass('os-loading');
        }else{
          $this.removeClass('os-loading');
          if($this.data('os-output-target') && $($this.data('os-output-target')).length){
            $($this.data('os-output-target')).prepend(latepoint_generate_form_message_html(response.message, 'error'));
          }else{
            alert(response.message);
          }
          if($this.data('os-after-call-error')){
            var func_name = $this.data('os-after-call-error');
            var callback = false;
            if(func_name.includes('.')){
              var func_arr = func_name.split('.');
              if(typeof window[func_arr[0]][func_arr[1]] !== 'function'){
                console.log(func_name + ' is undefined');
              }
              if($this.data('os-pass-this') && $this.data('os-pass-response')){
                window[func_arr[0]][func_arr[1]]($this, response);
              }else if($this.data('os-pass-this')){
                window[func_arr[0]][func_arr[1]]($this);
              }else if($this.data('os-pass-response')){
                window[func_arr[0]][func_arr[1]](response);
              }else{
                window[func_arr[0]][func_arr[1]]();
              }
            }else{
              if(typeof window[func_name] !== 'function'){
                console.log(func_name + ' is undefined');
              }
              if($this.data('os-pass-this') && $this.data('os-pass-response')){
                window[func_name]($this, response);
              }else if($this.data('os-pass-this')){
                window[func_name]($this);
              }else if($this.data('os-pass-response')){
                window[func_name](response);
              }else{
                window[func_name]();
              }
            }
          }
        }
      }
    });
    return false;
  });


  $('.latepoint').on('click', 'form[data-os-action] button[type="submit"]', function(e){
    $(this).addClass('os-loading');
  });

















  /* 
    Form ajax submit action
  */
  $('.latepoint').on('submit', 'form[data-os-action]', function(e){
    e.preventDefault(); // prevent native submit
      var $form = $(this);
      var form_data = new FormData($form[0]);

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

    var data = { action: 'latepoint_route_call', route_name: $(this).data('os-action'), params: latepoint_formdata_to_url_encoded_string(form_data), return_format: 'json' }
    $form.find('button[type="submit"]').addClass('os-loading');
    $.ajax({
      type : "post",
      dataType : "json",
      url : latepoint_timestamped_ajaxurl(),
      data : data,
      success: function(response){
        $form.find('button[type="submit"].os-loading').removeClass('os-loading');
        latepoint_clear_form_messages($form);
        if(response.status === "success"){
          if($form.data('os-success-action') == 'reload'){
            latepoint_add_notification(response.message);
            location.reload();
            return;
          }else if($form.data('os-success-action') == 'redirect'){
            if($form.data('os-redirect-to')){
              latepoint_add_notification(response.message);
              window.location.replace($form.data('os-redirect-to'));
            }else{
              window.location.replace(response.message);
            }
            return;
          }else if($form.data('os-output-target') && $($form.data('os-output-target')).length){
            $($form.data('os-output-target')).html(response.message);
          }else{
            if(response.message == 'redirect'){
              window.location.replace(response.url);
            }else{
              latepoint_add_notification(response.message);
              $form.prepend(latepoint_generate_form_message_html(response.message, 'success'));
            }
          }
          if($form.data('os-record-id-holder') && response.record_id){
            $form.find('[name="' + $form.data('os-record-id-holder') + '"]').val(response.record_id)
          }
          if($form.data('os-after-call')){
            var func_name = $form.data('os-after-call');
            if(typeof window[func_name] !== 'function'){
              console.log(func_name + ' is undefined');
            }
            if($form.data('os-pass-response')){
              window[func_name](response);
            }else{
              window[func_name]();
            }
          }
          $('button.os-loading').removeClass('os-loading');
        }else{
          $('button.os-loading').removeClass('os-loading');
          if($form.data('os-show-errors-as-notification')){
            latepoint_add_notification(response.message, 'error');
          }else{
            $form.prepend(latepoint_generate_form_message_html(response.message, 'error'));
            $([document.documentElement, document.body]).animate({
                scrollTop: ($form.find(".os-form-message-w").offset().top - 30)
            }, 200);
          }
        }
        if(response.form_values_to_update){
          $.each(response.form_values_to_update, function(name, value){
            $form.find('[name="'+ name +'"]').val(value);
          });
        }
      }
    });
    return false;
  });
});