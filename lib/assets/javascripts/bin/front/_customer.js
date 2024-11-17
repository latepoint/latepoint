/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

async function latepoint_init_transaction_payment_form() {
    let callbacks_list = [];
    let $transaction_payment_form = jQuery('.latepoint-transaction-payment-form');
    let current_step = $transaction_payment_form.find('input[name="current_step"]').val();
    switch (current_step) {
        case 'methods':
            break;
        case 'processors':
            break;
        case 'pay':
            $transaction_payment_form.trigger('latepoint:initOrderPaymentMethod', [{
                callbacks_list: callbacks_list,
                payment_method: $transaction_payment_form.find('input[name="payment_method"]').val(),
                payment_processor: $transaction_payment_form.find('input[name="payment_processor"]').val(),
            }]);
            $transaction_payment_form.addClass('os-loading');

            try {
                for (const callback of callbacks_list) {
                    await callback.action();
                }
                $transaction_payment_form.removeClass('os-loading');
            } catch (error) {
                latepoint_show_error_and_stop_loading_booking_form(error, $transaction_payment_form);
            }
            break;
        case 'confirmation':
            break;
    }

    $transaction_payment_form.on('submit', async function (e) {
        e.preventDefault();
        await latepoint_submit_transaction_payment_form(jQuery(e.target));
    });
}

async function latepoint_submit_transaction_payment_form($transaction_payment_form) {

    if($transaction_payment_form.hasClass('os-loading')) return false;
    let callbacks_list = [];

    $transaction_payment_form.find('.latepoint-message').remove();
    $transaction_payment_form.addClass('os-loading');
    $transaction_payment_form.find('.latepoint-btn').addClass('os-loading');

    $transaction_payment_form.trigger('latepoint:submitTransactionPaymentForm', [{
        callbacks_list: callbacks_list,
        payment_method: $transaction_payment_form.find('input[name="payment_method"]').val(),
        payment_processor: $transaction_payment_form.find('input[name="payment_processor"]').val(),
        current_step: $transaction_payment_form.find('input[name="current_step"]').val(),
    }]);

    try {
        for (const callback of callbacks_list) {
            await callback.action();
        }
    } catch (error) {
        $transaction_payment_form.removeClass('os-loading').find('.os-loading').removeClass('os-loading');
        latepoint_show_message_inside_element(error.message, $transaction_payment_form.find('.lp-payment-method-content'), 'error');
        return false;
    }


    try {
        let response = await jQuery.ajax({
            type: "post",
            dataType: "json",
            processData: false,
            contentType: false,
            url: latepoint_timestamped_ajaxurl(),
            data: latepoint_create_form_data($transaction_payment_form, latepoint_helper.order_payment_form_route)
        });

        $transaction_payment_form.removeClass('os-loading').find('.os-loading').removeClass('os-loading');

        if (response.status === 'success') {
            $transaction_payment_form.html(response.message);
            return await latepoint_init_transaction_payment_form();

        } else {
            latepoint_show_message_inside_element(response.message, $transaction_payment_form.find('.lp-payment-method-content'), 'error');
            return false;
        }
    } catch (e) {

        $transaction_payment_form.removeClass('os-loading').find('.os-loading').removeClass('os-loading');
        console.log(e);
        alert('Error:' + e);
    }

}

function latepoint_hide_reschedule_button() {
    jQuery('.reschedule-confirmation-button-wrapper').hide();
}

function latepoint_show_reschedule_button() {
    jQuery('.reschedule-confirmation-button-wrapper').show();
}

function latepoint_customer_cabinet_reload_booking_tile($booking_tile) {
    $booking_tile.addClass('os-loading');
    let params = {
        booking_id: $booking_tile.data('id'),
    }
    let data = {
        action: latepoint_helper.route_action,
        route_name: $booking_tile.data('route-name'),
        params: params,
        layout: 'none',
        return_format: 'json'
    };
    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: latepoint_timestamped_ajaxurl(),
        data: data,
        success: function (data) {
            $booking_tile.removeClass('os-loading')
            if (data.status === "success") {
                $booking_tile.replaceWith(data.message);
            } else {
                alert(data.message);
            }
        }
    });
}


function latepoint_init_reschedule() {

    jQuery('.reschedule-calendar-wrapper').on('click', '.latepoint-request-reschedule-trigger', function () {
        let $trigger = jQuery(this)
        let $wrapper = $trigger.closest('.reschedule-calendar-wrapper')
        let booking_id = $wrapper.find('input[type="hidden"].latepoint_booking_id').val()

        $trigger.addClass('os-loading')
        let params = {
            booking_id: booking_id,
            key: $wrapper.find('input[type="hidden"].latepoint_manage_booking_key').val(),
            start_date: $wrapper.find('input[type="hidden"].latepoint_start_date').val(),
            start_time: $wrapper.find('input[type="hidden"].latepoint_start_time').val(),
        }
        let data = {
            action: latepoint_helper.route_action,
            route_name: $trigger.data('route-name'),
            params: params,
            layout: 'none',
            return_format: 'json'
        };
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: latepoint_timestamped_ajaxurl(),
            data: data,
            success: function (data) {
                $trigger.removeClass('os-loading')
                if (data.status === "success") {
                    jQuery('.latepoint-lightbox-content').html(data.message);
                    jQuery('.latepoint-lightbox-footer, .latepoint-lightbox-heading').remove();
                    if (jQuery('.customer-bookings-tiles').length) {
                        // called from customer cabinet
                        latepoint_customer_cabinet_reload_booking_tile(jQuery('.customer-bookings-tiles .customer-booking[data-id="' + booking_id + '"]'));
                    } else {
                        // called from manage by key
                        latepoint_manage_by_key_reload_booking();
                    }
                } else {
                    latepoint_show_message_inside_element(data.message, jQuery('.latepoint-lightbox-content'), 'error');
                    jQuery('.latepoint-lightbox-content').animate({scrollTop: 0}, 300);
                }
            }
        });
        return false;
    });

    jQuery('.reschedule-calendar-wrapper').on('click keydown', '.dp-timepicker-trigger', function (event) {
        if (event.type === 'keydown' && event.key !== ' ' && event.key !== 'Enter') return;
        var $reschedule_form_element = jQuery(this).closest('.reschedule-calendar-wrapper');
        if (jQuery(this).hasClass('is-booked') || jQuery(this).hasClass('is-off')) {
            // Show error message that you cant select a booked period
        } else {
            if (jQuery(this).hasClass('selected')) {
                jQuery(this).removeClass('selected');
                jQuery(this).find('.dp-success-label').remove();
                $reschedule_form_element.find('.latepoint_start_time').val('');
                latepoint_hide_reschedule_button();
            } else {
                $reschedule_form_element.find('.dp-timepicker-trigger.selected').removeClass('selected').find('.dp-success-label').remove();
                var selected_timeslot_time = jQuery(this).find('.dp-label-time').html();
                jQuery(this).addClass('selected').find('.dp-label').prepend('<span class="dp-success-label">' + latepoint_helper.datepicker_timeslot_selected_label + '</span>');

                var minutes = parseInt(jQuery(this).data('minutes'));
                var timeshift_minutes = parseInt($reschedule_form_element.find('.latepoint_timeshift_minutes').val());
                // we substract timeshift minutes because its timeshift minutes that the business is running in, in opposite of what we do when we generate a calendar for a client
                if (timeshift_minutes) minutes = minutes - timeshift_minutes;
                var start_date = new Date($reschedule_form_element.find('.os-day.selected').data('date'));
                if (minutes < 0) {
                    // business minutes are in previous day
                    minutes = 24 * 60 + minutes;
                    // move start date back 1 day
                    start_date.setDate(start_date.getDate() - 1);
                } else if (minutes >= 24 * 60) {
                    // business minutes are in next day
                    minutes = minutes - 24 * 60;
                    start_date.setDate(start_date.getDate() + 1);
                }
                $reschedule_form_element.find('.latepoint_start_date').val(start_date.toISOString().split('T')[0])
                $reschedule_form_element.find('.latepoint_start_time').val(minutes);
                latepoint_show_reschedule_button();
            }
        }
        return false;
    });


    jQuery('.reschedule-calendar-wrapper').on('click', '.os-month-next-btn', function () {
        var $reschedule_form_element = jQuery(this).closest('.reschedule-calendar-wrapper');
        var next_month_route_name = jQuery(this).data('route');
        if ($reschedule_form_element.find('.os-monthly-calendar-days-w.active + .os-monthly-calendar-days-w').length) {
            $reschedule_form_element.find('.os-monthly-calendar-days-w.active').removeClass('active').next('.os-monthly-calendar-days-w').addClass('active');
            latepoint_calendar_set_month_label($reschedule_form_element);
        } else {
            // TODO add condition to check maximum number months to call into the future
            if (true) {
                var $btn = jQuery(this);
                $btn.addClass('os-loading');
                var $calendar_element = $reschedule_form_element.find('.os-monthly-calendar-days-w').last();
                var calendar_year = $calendar_element.data('calendar-year');
                var calendar_month = $calendar_element.data('calendar-month');
                if (calendar_month == 12) {
                    calendar_year = calendar_year + 1;
                    calendar_month = 1;
                } else {
                    calendar_month = calendar_month + 1;
                }
                var data = {
                    action: latepoint_helper.route_action,
                    route_name: next_month_route_name,
                    params: {
                        timezone_name: $reschedule_form_element.find('input[type="hidden"].latepoint_timezone_name').val(),
                        key: $reschedule_form_element.find('input[type="hidden"].latepoint_manage_booking_key').val(),
                        target_date_string: `${calendar_year}-${calendar_month}-1`,
                        booking: {
                            id: $reschedule_form_element.find('input[type="hidden"].latepoint_booking_id').val()
                        }
                    },
                    layout: 'none',
                    return_format: 'json'
                }
                jQuery.ajax({
                    type: "post",
                    dataType: "json",
                    url: latepoint_timestamped_ajaxurl(),
                    data: data,
                    success: function (data) {
                        $btn.removeClass('os-loading');
                        if (data.status === "success") {
                            $reschedule_form_element.find('.os-months').append(data.message);
                            $reschedule_form_element.find('.os-monthly-calendar-days-w.active').removeClass('active').next('.os-monthly-calendar-days-w').addClass('active');
                            latepoint_calendar_set_month_label($reschedule_form_element);
                        } else {
                            // console.log(data.message);
                        }
                    }
                });
            }
        }
        latepoint_calendar_show_or_hide_prev_next_buttons($reschedule_form_element);
        return false;
    });

    jQuery('.reschedule-calendar-wrapper').on('click', '.os-month-prev-btn', function () {
        var $reschedule_form_element = jQuery(this).closest('.reschedule-calendar-wrapper');
        if ($reschedule_form_element.find('.os-monthly-calendar-days-w.active').prev('.os-monthly-calendar-days-w').length) {
            $reschedule_form_element.find('.os-monthly-calendar-days-w.active').removeClass('active').prev('.os-monthly-calendar-days-w').addClass('active');
            latepoint_calendar_set_month_label($reschedule_form_element);
        }
        latepoint_calendar_show_or_hide_prev_next_buttons($reschedule_form_element);
        return false;
    });

    jQuery('.reschedule-calendar-wrapper .os-months').on('click', '.os-day', function () {
        if (jQuery(this).hasClass('os-day-passed')) return false;
        if (jQuery(this).hasClass('os-not-in-allowed-period')) return false;
        var $reschedule_form_element = jQuery(this).closest('.reschedule-calendar-wrapper');
        if (jQuery(this).closest('.os-monthly-calendar-days-w').hasClass('hide-if-single-slot')) {

            // HIDE TIMESLOT IF ONLY ONE TIMEPOINT
            if (jQuery(this).hasClass('os-not-available')) {
                // clicked on a day that has no available timeslots
                // do nothing
            } else {
                $reschedule_form_element.find('.os-day.selected').removeClass('selected');
                jQuery(this).addClass('selected');
                // set date
                $reschedule_form_element.find('.latepoint_start_date').val(jQuery(this).data('date'));
                if (jQuery(this).hasClass('os-one-slot-only')) {
                    // clicked on a day that has only one slot available
                    var bookable_minutes = jQuery(this).data('bookable-minutes').toString().split(':')[0];
                    var selected_timeslot_time = latepoint_format_minutes_to_time(Number(bookable_minutes), Number(jQuery(this).data('service-duration')));
                    $reschedule_form_element.find('.latepoint_start_time').val(jQuery(this).data('bookable-minutes'));
                    $reschedule_form_element.find('.time-selector-w').slideUp(200);
                    latepoint_show_reschedule_button()
                } else {
                    // regular day with more than 1 timeslots available
                    // build timeslots
                    day_timeslots(jQuery(this), $reschedule_form_element, $reschedule_form_element.find('.latepoint-lightbox-content'));
                    // initialize timeslots events
                    // clear time and hide next btn
                    $reschedule_form_element.find('.latepoint_start_time').val('');
                }
            }
        } else {

            // SHOW TIMESLOTS EVEN IF ONLY ONE TIMEPOINT
            $reschedule_form_element.find('.latepoint_start_date').val(jQuery(this).data('date'));
            $reschedule_form_element.find('.os-day.selected').removeClass('selected');
            jQuery(this).addClass('selected');

            // build timeslots
            day_timeslots(jQuery(this), $reschedule_form_element, $reschedule_form_element.find('.latepoint-lightbox-content'));
            // initialize timeslots events
            // clear time and hide next btn
            let $booking_form_element = jQuery(this).closest('.latepoint-booking-form-element');
            if ($booking_form_element.length) latepoint_reload_summary($booking_form_element);
            $reschedule_form_element.find('.latepoint_start_time').val('');
            latepoint_hide_next_btn($reschedule_form_element);
        }

        return false;
    });
}
