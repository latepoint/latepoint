jQuery(window).on('elementor/frontend/init', function() {
    elementorFrontend.hooks.addAction('frontend/element_ready/latepoint_book_form.default', function($scope) {
        if(jQuery('.latepoint-book-form-wrapper').length){
            jQuery('.latepoint-book-form-wrapper').each(function(){
                latepoint_init_booking_form_by_trigger(jQuery(this));
            });
        }
    });
});