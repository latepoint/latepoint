jQuery(window).on('elementor/frontend/init', function() {
    elementorFrontend.hooks.addAction('frontend/element_ready/latepoint_book_button.default', function($scope) {
        if(jQuery('.latepoint-book-button').length){
            window.latepoint_init_booking_form_by_trigger = function(e) {
                e.preventDefault();
            };
        }
    });
});