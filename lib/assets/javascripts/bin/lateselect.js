(function($) {

    jQuery.fn.lateSelect = function() {

      function os_build_selected_item($option){
        var quantity_html = '';
        if($option.data('quantity')) quantity_html = '<span class="os-late-quantity-selector-w"><span class="os-late-quantity-selector minus" data-sign="minus"></span><input class="os-late-quantity-selector-input" type="text" data-max-quantity="'+ $option.data('max-quantity') +'" value="' + $option.data('quantity') + '"/><span class="os-late-quantity-selector plus" data-sign="plus"></span></span>';
        return '<div class="ls-item" data-value="' + $option.val() + '"><span class="latepoint-icon latepoint-icon-cross ls-item-remover"></span><span>' + $option.text() + '</span>' + quantity_html + '</div>'
      }

      this.each( function() {
          var lateselect_html = '';
          var all_items = '';
          var selected_items = '';
          var is_selected = '';
          if(jQuery(this).hasClass('os-late-select-active')) return;
          jQuery(this).hide().addClass('os-late-select-active');
          jQuery(this).find('option').each(function(){
              if(jQuery(this).is(':selected')) selected_items+= os_build_selected_item(jQuery(this));
              is_selected = jQuery(this).is(':selected') ? 'selected' : '';
              all_items+= '<div class="ls-item '+ is_selected +'" data-value="' + jQuery(this).val() + '">' + jQuery(this).text() + '</div>';
          });
          var placeholder = '<div class="ls-placeholder">' + jQuery(this).data('placeholder') + '</div>';
          lateselect_html = jQuery('<div class="lateselect-w"></div>');
          jQuery(this).wrap(lateselect_html);
          var $lateselect_wrapper = jQuery(this).closest('.lateselect-w');
          $lateselect_wrapper.append('<div class="ls-selected-items-w">' + placeholder + selected_items + '</div>');
          $lateselect_wrapper.append('<div class="ls-all-items-w">' + all_items + '</div>');


          // ADD ITEM
          $lateselect_wrapper.on('click', '.ls-all-items-w .ls-item:not(.selected)', function(){
              var selected_value = jQuery(this).data('value');
              $lateselect_wrapper.find('.ls-selected-items-w').append(os_build_selected_item($lateselect_wrapper.find('select option[value="'+ selected_value +'"]')));
              jQuery(this).addClass('selected');
              $lateselect_wrapper.removeClass('ls-selecting');
              $lateselect_wrapper.find('select option[value="'+ selected_value +'"]').prop('selected', true);
              $lateselect_wrapper.find('select').trigger('change');
              return false;
          });

          // REMOVE ITEM
          $lateselect_wrapper.on('click', '.ls-selected-items-w .ls-item-remover', function(){
              var selected_value = jQuery(this).closest('.ls-item').data('value');
              jQuery(this).closest('.ls-item').remove();
              $lateselect_wrapper.find('.ls-all-items-w .ls-item.selected[data-value="' + selected_value + '"]').removeClass('selected');
              $lateselect_wrapper.find('select option[value="'+ selected_value +'"]').prop('selected', false);
              $lateselect_wrapper.find('select').trigger('change');
              return false;
          });

          $lateselect_wrapper.on('click', '.ls-selected-items-w', function(){
              $lateselect_wrapper.toggleClass('ls-selecting');
              return false;
          });

          $lateselect_wrapper.on('click', '.os-late-quantity-selector', function(){
              var $input = jQuery(this).closest('.ls-item').find('input.os-late-quantity-selector-input');
              var current_value = parseInt($input.val());
              var new_quantity = (jQuery(this).data('sign') == 'minus') ? current_value - 1 : current_value + 1;
              var max_quantity = $input.data('max-quantity');
              if(new_quantity <= 0) new_quantity = 1;
              if(max_quantity && (new_quantity > max_quantity)) new_quantity = max_quantity;
              var selected_value = jQuery(this).closest('.ls-item').data('value');
              $lateselect_wrapper.find('select option[value="'+ selected_value +'"]').data('quantity', new_quantity);
              $input.val(new_quantity);
              $lateselect_wrapper.find('select').trigger('change');
              return false;
          });

          jQuery(this).on('change', function(){
              var $hidden_connection = false;
              if(jQuery(this).data('hidden-connection')){
                $hidden_connection = jQuery(jQuery(this).data('hidden-connection'));
              }else{
                $hidden_connection = jQuery(this).closest('.lateselect-w').next('input[type="hidden"]');
              }
              var formatted_ids = '';
              if(jQuery(this).find('option:selected').length){
                  jQuery(this).find('option:selected').each(function(){
                    if(jQuery(this).data('quantity')){
                      var quantity = jQuery(this).data('quantity') ? jQuery(this).data('quantity') : 1;
                      formatted_ids+= jQuery(this).val() + ':' + quantity + ',';
                    }else{
                      formatted_ids+= jQuery(this).val() + ',';
                    }
                  });
              }else{
                formatted_ids = '';
              }
              if(formatted_ids != '') formatted_ids = formatted_ids.slice(0, -1);
              $hidden_connection.val(formatted_ids);
          });
      });
    }
}(jQuery));