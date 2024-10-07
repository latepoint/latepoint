function init_booking_form()
{
    if(jQuery('.latepoint-book-form-wrapper').length){
        jQuery('.latepoint-book-form-wrapper').each(function(){
            latepoint_init_booking_form_by_trigger(jQuery(this));
        });
    }
}
