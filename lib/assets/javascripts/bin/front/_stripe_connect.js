/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

class LatepointStripeConnectFront {

    // Init
    constructor(stripeKey) {
        this.stripeKey = stripeKey;
        this.stripeElements = null;
        this.stripeCore = null;
        this.stripePaymentMethod = null;
        this.stripeContinueOrderIntentURL = null;
        this.stripePaymentIntentSecret = null;
        this.stripePaymentElement = null;
        this.ready();
    }

    ready() {
        jQuery(document).ready(() => {
            jQuery('body').on('latepoint:submitBookingForm', '.latepoint-booking-form-element', (e, data) => {
                if (!latepoint_helper.demo_mode && data.is_final_submit && data.direction == 'next') {
                    let payment_method = jQuery(e.currentTarget).find('input[name="cart[payment_method]"]').val();
                    switch (payment_method) {
                        case 'payment_element':
                            latepoint_add_action(data.callbacks_list, async () => {
                                if (this.stripePaymentIntentSecret && this.stripeCore) {
                                    return await this.confirmPaymentElementPayment(jQuery(e.currentTarget), this.stripePaymentIntentSecret, this.stripePaymentMethod);
                                }
                            });
                            break;
                    }
                }
            });

            // INITIALIZE PAYMENT METHOD
            jQuery('body').on('latepoint:initPaymentMethod', '.latepoint-booking-form-element', (e, data) => {
                if (!latepoint_helper.demo_mode) {
                    switch (data.payment_method) {
                        case 'payment_element':
                            latepoint_add_action(data.callbacks_list, async () => {
                                return await this.createPaymentIntent(jQuery(e.currentTarget), data.payment_method);
                            });
                            break;
                    }
                } else {
                    latepoint_show_next_btn(jQuery(e.currentTarget));
                }
            });
        });
    }


    async createPaymentIntent($booking_form_element, payment_method) {
        this.stripeCore = Stripe(this.stripeKey, {stripeAccount: latepoint_helper.stripe_connected_account_id});
        this.stripeElements = this.stripeCore.elements();

        var data = latepoint_create_form_data($booking_form_element.find('.latepoint-form'), latepoint_helper.stripe_connect_route_create_payment_intent, {booking_form_page_url: window.location.href});

        let response = await jQuery.ajax({
            type: "post",
            dataType: "json",
            processData: false,
            contentType: false,
            url: latepoint_timestamped_ajaxurl(),
            data: data
        });

        if (response.status === "success") {
            $booking_form_element.find('input[name="cart[payment_token]"]').val(response.payment_intent_id);
            this.stripePaymentIntentSecret = response.payment_intent_secret;
            this.stripeContinueOrderIntentURL = response.continue_order_intent_url;
            latepoint_show_next_btn($booking_form_element);

            if ($booking_form_element.find('.stripe-payment-element').length){
                return this.initPaymentElement($booking_form_element);
            }
        } else {
            alert(response.message);
            throw new Error(response.message);
        }
    }

    async confirmPaymentElementPayment($booking_form_element, intent_secret) {
        let elements = this.stripeElements;
        let continue_order_intent_url = this.stripeContinueOrderIntentURL;
        let result = await this.stripeCore.confirmPayment({
            elements,
            confirmParams: {
                // Return URL where the customer should be redirected after the PaymentIntent is confirmed.
                return_url: continue_order_intent_url,
            },
            redirect: "if_required",
        });
        if (result.error) {
            throw new Error(result.error.message);
        } else {
            $booking_form_element.find('input[name="cart[payment_token]"]').val(result.paymentIntent.id);
            return result.paymentIntent.id;
        }
    }

    initPaymentElement($booking_form_element) {
        let appearance = {
            theme: 'stripe',
            variables: {
                fontFamily: 'Overpass',
                colorPrimary: '#1d7bff'
            },
            rules: {
                '.Tab': {
                    border: '1px solid #E0E6EB',
                    boxShadow: 'none',
                    borderRadius: '0',
                    marginBottom: '10px'
                },
                '.Input': {
                    boxShadow: 'none',
                    borderRadius: '0'
                },

                '.Tab:hover': {
                    color: 'var(--colorText)',
                },

                '.Tab--selected': {
                    borderColor: 'var(--colorPrimary)',
                    boxShadow: `0 0 0 1px var(--colorPrimary)`,
                },

                '.Input--invalid': {
                    boxShadow: '0 1px 1px 0 rgba(0, 0, 0, 0.07), 0 0 0 2px var(--colorPrimary)',
                },

                // See all supported class names and selector syntax below
            }
        };


        // Create an instance of the Payment Element
        this.stripeElements = this.stripeCore.elements({
            clientSecret: this.stripePaymentIntentSecret,
            appearance,
            fonts: [{cssSrc: 'https://fonts.googleapis.com/css2?family=Overpass&display=swap'}],
        });

        let options = {
            layout: {
                type: 'tabs',
                defaultCollapsed: false,
            },
        };
        this.stripePaymentElement = this.stripeElements.create('payment', options);

        return this.stripePaymentElement.mount($booking_form_element.find('.stripe-payment-element')[0]);
    }


}


if (latepoint_helper.is_stripe_connect_enabled) window.latepointStripeConnectFront = new LatepointStripeConnectFront(latepoint_helper.stripe_connect_key);