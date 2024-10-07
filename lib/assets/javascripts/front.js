/*
 * Copyright (c) 2022 LatePoint LLC. All rights reserved.
 */
// @codekit-prepend "bin/time.js"
// @codekit-prepend "bin/shared.js"
// @codekit-prepend "bin/notifications.js";
// @codekit-prepend "bin/actions.js"
// @codekit-prepend "bin/front/main.js"
// @codekit-prepend "bin/front/_customer.js"
// @codekit-prepend "bin/front/_events.js"
// @codekit-prepend "bin/front/_stripe_connect.js"


// DOCUMENT READY
jQuery(document).ready(function ($) {

    latepoint_init_customer_dashboard();
    latepoint_init_manage_booking_by_key();


    jQuery('body').on('click', '.le-filter-trigger', function () {
        let $events_calendar = jQuery(this).closest('.latepoint-calendar-wrapper');
        if ($events_calendar.hasClass('show-filters')) {
            $events_calendar.removeClass('show-filters');
            $events_calendar.find('.latepoint-calendar-filters select').val('');
            latepoint_reload_events_calendar($events_calendar);
        } else {
            $events_calendar.addClass('show-filters');
        }
        return false;
    });

    jQuery('body').on('click', '.le-navigation-trigger', function () {
        let $trigger = jQuery(this);
        let $events_calendar = $trigger.closest('.latepoint-calendar-wrapper');
        $events_calendar.find('input[name="target_date_string"]').val($trigger.data('target-date'));
        $trigger.addClass('os-loading');
        latepoint_reload_events_calendar($events_calendar);
        return false;
    });

    jQuery('body').on('change', '.le-day-filters select', function () {
        let $trigger = jQuery(this);
        let $day_view = $trigger.closest('.le-day-view-wrapper');
        latepoint_reload_day_schedule($day_view);
        return false;
    });

    jQuery('body').on('change', '.latepoint-calendar-filters select, .le-range-selector select', function () {
        let $trigger = jQuery(this);
        let $events_calendar = $trigger.closest('.latepoint-calendar-wrapper');
        $events_calendar.find('.le-filter').addClass('os-loading');
        latepoint_reload_events_calendar($events_calendar);
        return false;
    });

    jQuery('body').on('click', '.close-calendar-types', function () {
        jQuery(this).closest('.add-to-calendar-wrapper').removeClass('show-types');
        return false;
    });
    jQuery('body').on('click', '.open-calendar-types', function () {
        jQuery(this).closest('.add-to-calendar-wrapper').addClass('show-types');
        return false;
    });

    jQuery('body').on('latepoint:nextStepClicked', '.latepoint-booking-form-element', (e, data) => {

        latepoint_add_action(data.callbacks_list, async () => {
            let $booking_form = jQuery(e.currentTarget).find('.latepoint-form');
            let errors = latepoint_validate_form($booking_form);
            if (errors.length) {
                let error_messages = errors.map(error => error.message).join(', ');
                throw new Error(error_messages);
            } else {
                return true;
            }
        }, 1);

    });

    if (latepoint_helper.start_from_order_intent_key) {
        $('body').append('<div class="latepoint-continue-intent-loading"></div>');

        var data = {
            action: latepoint_helper.route_action,
            route_name: latepoint_helper.start_from_order_intent_route,
            params: {order_intent_key: latepoint_helper.start_from_order_intent_key},
            layout: 'none',
            return_format: 'json'
        }

        $.ajax({
            type: "post",
            dataType: "json",
            url: latepoint_timestamped_ajaxurl(),
            data: data,
            success: function (data) {
                $('.latepoint-continue-intent-loading').remove();
                if (data.status === "success") {
                    var lightbox_class = '';
                    latepoint_show_data_in_lightbox(data.message, data.lightbox_class);
                    var $booking_form_element = jQuery('.latepoint-lightbox-w .latepoint-booking-form-element');
                    latepoint_init_booking_form($booking_form_element);
                    $booking_form_element.find('.latepoint-step-content').addClass('is-hidden').last().removeClass('is-hidden');
                    if ($booking_form_element.find('.latepoint-step-content').length > 1) latepoint_show_prev_btn($booking_form_element);
                    var $booking_form_element = jQuery('.latepoint-lightbox-w .latepoint-booking-form-element');
                    $booking_form_element.find('.latepoint-step-content').each(function () {
                        latepoint_init_step($(this).data('step-code'), $booking_form_element);
                    });
                    $('body').addClass('latepoint-lightbox-active');
                } else {
                    // console.log(data.message);
                }
            }
        });
    }

    if (jQuery('.latepoint-booking-form-element').length) {
        jQuery('.latepoint-booking-form-element').each(function () {
            latepoint_init_booking_form(jQuery(this));
            latepoint_init_step(jQuery(this).find('.latepoint_current_step_code').val(), jQuery(this));
        });
    }


    jQuery('body').on('click', '.latepoint-lightbox-close', function () {
        latepoint_lightbox_close();
        return false;
    });


    jQuery('body').on('click', '.os-step-tabs .os-step-tab', function () {
        jQuery(this).closest('.os-step-tabs').find('.os-step-tab').removeClass('active');
        jQuery(this).addClass('active');
        var target = jQuery(this).data('target');
        jQuery(this).closest('.os-step-tabs-w').find('.os-step-tab-content').hide();
        jQuery(target).show();
    });

    jQuery('body').on('keyup', '.os-form-group .os-form-control', function () {
        if (jQuery(this).val()) {
            jQuery(this).closest('.os-form-group').addClass('has-value');
        } else {
            jQuery(this).closest('.os-form-group').removeClass('has-value');
        }
    });

    jQuery('.latepoint-tab-triggers').on('click', '.latepoint-tab-trigger', function () {
        var $tabs_wrapper = jQuery(this).closest('.latepoint-tabs-w')
        $tabs_wrapper.find('.latepoint-tab-trigger.active').removeClass('active');
        $tabs_wrapper.find('.latepoint-tab-content').removeClass('active');
        jQuery(this).addClass('active');
        $tabs_wrapper.find('.latepoint-tab-content' + jQuery(this).data('tab-target')).addClass('active');
        return false;
    });


    // Main Button to trigger lightbox opening
    if(jQuery('.latepoint-book-form-wrapper').length){
        jQuery('.latepoint-book-form-wrapper').each(function(){
            latepoint_init_booking_form_by_trigger(jQuery(this));
        });
    }

    jQuery('body').on('click', '.latepoint-book-button, .os_trigger_booking', function () {
        latepoint_init_booking_form_by_trigger(jQuery(this));
        return false;
    });

});
