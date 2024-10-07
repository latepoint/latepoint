function latepoint_add_notification(message, message_type = 'success'){
	var wrapper = jQuery('body').find('.os-notifications');
	if(!wrapper.length){
		jQuery('body').append('<div class="os-notifications"></div>');
		wrapper = jQuery('body').find('.os-notifications');
	}
	if(wrapper.find('.item').length > 0) wrapper.find('.item:first-child').remove();
	wrapper.append('<div class="item item-type-'+ message_type +'">' + message + '<span class="os-notification-close"><i class="latepoint-icon latepoint-icon-x"></i></span></div>');
}

function latepoint_add_lightbox_notification(message, message_type = 'success'){
	var wrapper = jQuery('.latepoint-lightbox-content').find('.os-notifications');
	if(!wrapper.length){
		jQuery('.latepoint-lightbox-content').prepend('<div class="os-notifications"></div>');
		wrapper = jQuery('.latepoint-lightbox-content').find('.os-notifications');
	}
	if(wrapper.find('.item').length > 0) wrapper.find('.item:first-child').remove();
	wrapper.append('<div class="item item-type-'+ message_type +'">' + message + '<span class="os-notification-close"><i class="latepoint-icon latepoint-icon-x"></i></span></div>');
}