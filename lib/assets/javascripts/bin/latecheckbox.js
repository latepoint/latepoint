/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

(function($) {

    jQuery.fn.lateCheckbox = function() {

      function applyChanges(id){
        let $wrapper = jQuery('.latecheckbox-w[data-latecheckbox-id="' + id + '"]');
        $wrapper.find('.latecheckbox-options-w').html(jQuery('.latecheckbox-options-w[data-latecheckbox-id="' + id + '"]').html());

        let $options = $wrapper.find('.latecheckbox-options');
        let total_checked = $options.find('.latecheckbox-option input[type="checkbox"]:checked').length;
        let total_available = $options.find('.latecheckbox-option input[type="checkbox"]').length;
        if(total_checked < total_available){
          $wrapper.find('.latecheckbox .filter-value').text(total_checked);
        }else{
          $wrapper.find('.latecheckbox .filter-value').text('All');
        }
        // set indeterminate, since it can only be set via JS
        $wrapper.find('input[type="checkbox"][indeterminate="indeterminate"]').prop('indeterminate', true).removeAttr('indeterminate');

        $wrapper.find('.latecheckbox').trigger('change');
      }

      this.each( function() {
        var $latecheckbox_wrapper = jQuery(this).closest('.latecheckbox-w');
        $latecheckbox_wrapper.attr('data-latecheckbox-id',  'latecheckbox-' + latepoint_random_generator());

        $latecheckbox_wrapper.on('click', '.latecheckbox', function(){
          let $latecheckbox = jQuery(this);
          jQuery('body > .latecheckbox-options-w').remove();
          if(jQuery(this).hasClass('is-active')){
            jQuery(this).removeClass('is-active');
          }else{
            jQuery('.latecheckbox.is-active').removeClass('is-active');
            jQuery(this).addClass('is-active');
            let position = jQuery(this).position();
            let left = position.left;
            let $options_wrapper = $latecheckbox_wrapper.find('.latecheckbox-options-w');
            let $options_wrapper_clone = $options_wrapper.clone();
            $options_wrapper_clone.attr('data-latecheckbox-id', jQuery(this).closest('.latecheckbox-w').attr('data-latecheckbox-id')).appendTo('body');
            if(true){
              // todo add ability to change position
              left = left + jQuery(this).outerWidth() - $options_wrapper_clone.outerWidth();
            }
            $options_wrapper_clone.css({"top": position.top + jQuery(this).outerHeight() +5 , "left": left});
            if($options_wrapper_clone.find('.latecheckbox-filter-input').length) $options_wrapper_clone.find('.latecheckbox-filter-input').trigger('focus');

            $options_wrapper_clone.on('change', '.latecheckbox-all-check', function(){
              if(jQuery(this).is(':checked')){
                jQuery(this).attr('checked', 'checked').removeAttr('indeterminate');
                jQuery(this).closest('.latecheckbox-options-w').find('.latecheckbox-options input[type="checkbox"]').prop('checked', true).prop('indeterminate', false).attr('checked', 'checked');
              }else{
                jQuery(this).removeAttr('checked').removeAttr('indeterminate');
                jQuery(this).closest('.latecheckbox-options-w').find('.latecheckbox-options input[type="checkbox"]').prop('checked', false).prop('indeterminate', false).removeAttr('checked');
              }
              applyChanges(jQuery(this).closest('.latecheckbox-options-w').attr('data-latecheckbox-id'));
            });
            $options_wrapper_clone.on('change', '.latecheckbox-group-check', function(){
              if(jQuery(this).is(':checked')){
                jQuery(this).attr('checked', 'checked').removeAttr('indeterminate');
                jQuery(this).closest('.latecheckbox-group').find('.latecheckbox-group-options input[type="checkbox"]').prop('checked', true).attr('checked', 'checked');
              }else{
                jQuery(this).removeAttr('checked').removeAttr('indeterminate');
                jQuery(this).closest('.latecheckbox-group').find('.latecheckbox-group-options input[type="checkbox"]').prop('checked', false).removeAttr('checked');
              }
              applyChanges(jQuery(this).closest('.latecheckbox-options-w').attr('data-latecheckbox-id'));
            });

            $options_wrapper_clone.on('keyup', '.latecheckbox-filter-input', function(){
              let q = jQuery(this).val().toLowerCase();
              if(q == ''){
                jQuery(this).closest('.latecheckbox-options-w').find('.latecheckbox-option.hidden').removeClass('hidden');
              }else{
                jQuery(this).closest('.latecheckbox-options-w').find('.latecheckbox-option').each(function(){
                  let text = jQuery(this).text().toLowerCase();
                  (text.indexOf(q) >= 0) ? jQuery(this).removeClass('hidden') : jQuery(this).addClass('hidden');
                });
              }
            });

            $options_wrapper_clone.on('change', '.latecheckbox-option input[type="checkbox"]', function(){
              if(jQuery(this).is(':checked')){
                jQuery(this).attr('checked', 'checked');
              }else{
                jQuery(this).removeAttr('checked');
              }

              // group checkbox
              if(jQuery(this).closest('.latecheckbox-group-options').length){
                let $group = jQuery(this).closest('.latecheckbox-group');
                let checked_count = $group.find('.latecheckbox-option input:checked').length;
                let unchecked_count = $group.find('.latecheckbox-option input:not(:checked)').length;

                if(checked_count && unchecked_count){
                  $group.find('.latecheckbox-group-check').prop('indeterminate', true).attr('indeterminate', 'indeterminate');
                  $group.find('.latecheckbox-group-check').prop('checked', false).removeAttr('checked');
                }else{
                  $group.find('.latecheckbox-group-check').prop('indeterminate', false).removeAttr('indeterminate');
                  if(!checked_count){
                    $group.find('.latecheckbox-group-check').prop('checked', false).removeAttr('checked');
                  }
                  if(!unchecked_count){
                    $group.find('.latecheckbox-group-check').prop('checked', true).attr('checked', 'checked');
                  }
                }
              }
              let checked_count = $options_wrapper_clone.find('.latecheckbox-option input:checked').length;
              let unchecked_count = $options_wrapper_clone.find('.latecheckbox-option input:not(:checked)').length;

              if(checked_count && unchecked_count){
                $options_wrapper_clone.find('.latecheckbox-all-check').prop('indeterminate', true).attr('indeterminate', 'indeterminate');
                $options_wrapper_clone.find('.latecheckbox-all-check').prop('checked', false).removeAttr('checked');
              }else{
                $options_wrapper_clone.find('.latecheckbox-all-check').prop('indeterminate', false).removeAttr('indeterminate');
                if(!checked_count){
                  $options_wrapper_clone.find('.latecheckbox-all-check').prop('checked', false).removeAttr('checked');
                }
                if(!unchecked_count){
                  $options_wrapper_clone.find('.latecheckbox-all-check').prop('checked', true).attr('checked', 'checked');
                }
              }
              applyChanges(jQuery(this).closest('.latecheckbox-options-w').attr('data-latecheckbox-id'));
            });
          }
          return false;
        });

      });
    }
}(jQuery));