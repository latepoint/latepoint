<?php

class OsStripeConnectHelper {
	public static $default_currency_iso_code = 'usd';
	public static $error = false;

	public static $stripe = false;
	public static $processor_code = 'stripe_connect';


	public static function add_all_payment_methods_to_payment_times(array $payment_times): array {
		$payment_methods = self::get_supported_payment_methods();
		foreach ($payment_methods as $payment_method_code => $payment_method_info) {
			$payment_times[LATEPOINT_PAYMENT_TIME_NOW][$payment_method_code][self::$processor_code] = $payment_method_info;
		}
		return $payment_times;
	}

	public static function add_enabled_payment_methods_to_payment_times(array $payment_times): array {
		if (OsPaymentsHelper::is_payment_processor_enabled(self::$processor_code)) {
			$payment_times = self::add_all_payment_methods_to_payment_times($payment_times);
		}
		return $payment_times;
	}

	public static function output_stripe_link_on_customer_quick_form(OsCustomerModel $customer) {
		$stripe_customer_id = self::get_stripe_customer_id($customer);
		if ($stripe_customer_id) echo '<div class="payment-processor-customer-link-wrapper">' . esc_html__('Stripe Customer', 'latepoint') . '<a target="_blank" href="' . esc_url(self::build_customer_profile_link($stripe_customer_id, OsSettingsHelper::is_env_payments_dev())) . '">' . esc_html__('Open in Stripe', 'latepoint') . '</a></div>';
	}


    public static function process_payment_for_transaction_intent( $result, OsTransactionIntentModel $transaction_intent ) {
        if ( OsPaymentsHelper::should_processor_handle_payment_for_transaction_intent( self::$processor_code, $transaction_intent ) ) {
            switch ( $transaction_intent->get_payment_data_value( 'method' ) ) {
                case 'payment_element':
                    if ( $transaction_intent->get_payment_data_value( 'token' ) ) {
                        // since the payment is already processed on the frontend - we need to retrieve payment intent and verify if its paid
                        $payment_intent_data = self::retrieve_payment_intent( $transaction_intent->get_payment_data_value( 'token' ) );
                        if ( in_array( $payment_intent_data['status'], [ 'succeeded', 'requires_capture' ] ) ) {
                            // success
                            $result['status']    = LATEPOINT_STATUS_SUCCESS;
                            $result['processor'] = self::$processor_code;
                            $result['charge_id'] = $payment_intent_data['id'];
                            $result['amount']    = $payment_intent_data['total'];
                            $result['kind']      = $payment_intent_data['status'] == 'requires_capture' ? LATEPOINT_TRANSACTION_KIND_AUTHORIZATION : LATEPOINT_TRANSACTION_KIND_CAPTURE;
                        } else {
                            // payment error
                            $result['status']  = LATEPOINT_STATUS_ERROR;
                            $result['message'] = __( 'Payment Error', 'latepoint' );
                            $transaction_intent->add_error( 'send_to_step', $result['message'], 'payment' );
                        }
                    } else {
                        // payment token missing
                        $result['status']  = LATEPOINT_STATUS_ERROR;
                        $result['message'] = __( 'Payment Error 23JDF38', 'latepoint' );
                        $transaction_intent->add_error( 'payment_error', $result['message'] );
                    }
                    break;
            }
        }

        return $result;
    }


	public static function process_payment($result, OsOrderIntentModel $order_intent) {
			if (OsPaymentsHelper::should_processor_handle_payment_for_order_intent(self::$processor_code, $order_intent)) {
			switch ($order_intent->get_payment_data_value('method')) {
				case 'payment_element':
					if ($order_intent->get_payment_data_value('token')) {
						// since the payment is already processed on the frontend - we need to retrieve payment intent and verify if its paid
						$payment_intent_data = self::retrieve_payment_intent($order_intent->get_payment_data_value('token'));
						if (in_array($payment_intent_data['status'], ['succeeded', 'requires_capture'])) {
							// success
							$result['status'] = LATEPOINT_STATUS_SUCCESS;
							$result['processor'] = self::$processor_code;
							$result['charge_id'] = $payment_intent_data['id'];
							$result['amount'] = $payment_intent_data['total'];
							$result['kind'] = $payment_intent_data['status'] == 'requires_capture' ? LATEPOINT_TRANSACTION_KIND_AUTHORIZATION : LATEPOINT_TRANSACTION_KIND_CAPTURE;
						} else {
							// payment error
							$result['status'] = LATEPOINT_STATUS_ERROR;
							$result['message'] = __('Payment Error', 'latepoint');
							$order_intent->add_error('payment_error', $result['message']);
							$order_intent->add_error('send_to_step', $result['message'], 'payment');
						}
					} else {
						// payment token missing
						$result['status'] = LATEPOINT_STATUS_ERROR;
						$result['message'] = __('Payment Error 23JDF38', 'latepoint');
						$order_intent->add_error('payment_error', $result['message']);
					}
					break;
			}
		}
		return $result;
	}


	public static function convert_charge_amount_to_requirements($charge_amount, OsCartModel $cart) {
		if (OsPaymentsHelper::should_processor_handle_payment_for_cart(self::$processor_code, $cart)) {
			$charge_amount = self::convert_amount_to_specs($charge_amount);
		}
		return $charge_amount;
	}

    public static function convert_amount_to_specs($charge_amount){
        $iso_code = self::get_currency_iso_code();
        if (in_array($iso_code, self::zero_decimal_currencies_list())) {
            $charge_amount = round($charge_amount);
        } else {
            $number_of_decimals = OsSettingsHelper::get_settings_value('number_of_decimals', '2');
            $charge_amount = number_format((float)$charge_amount, $number_of_decimals, '.', '') * pow(10, $number_of_decimals);
        }
        return $charge_amount;
    }


	public static function output_order_payment_pay_contents(OsTransactionIntentModel $transaction_intent) {
		if (!OsPaymentsHelper::should_processor_handle_payment_for_transaction_intent(self::$processor_code, $transaction_intent)) return;
		echo '<div class="lp-payment-method-content" data-payment-method="payment_element">';
		echo '<div class="lp-payment-method-content-i">';
		echo '<div class="stripe-payment-element"></div>';
		echo '</div>';
		echo '</div>';
	}

	public static function output_payment_step_contents(OsCartModel $cart) {
		if (!OsPaymentsHelper::should_processor_handle_payment_for_cart(self::$processor_code, $cart)) return;
		echo '<div class="lp-payment-method-content" data-payment-method="payment_element">';
		echo '<div class="lp-payment-method-content-i">';
		echo '<div class="stripe-payment-element"></div>';
		echo '</div>';
		echo '</div>';
	}


	public static function get_supported_payment_methods() : array{
		return [
			'payment_element' => [
				'name' => __('Payment Element', 'latepoint'),
				'label' => __('Credit Card', 'latepoint'),
				'image_url' => LATEPOINT_IMAGES_URL . 'payment_cards.png',
			]
		];
	}

	public static function register_payment_processor(array $payment_processors) : array {
		$payment_processors[self::$processor_code] = [
			'code' => self::$processor_code,
			'name' => __('Stripe Connect', 'latepoint'),
			'front_name' => __('Stripe', 'latepoint'),
			'image_url' => LATEPOINT_IMAGES_URL . 'processor-stripe-connect.png'
		];
		return $payment_processors;
	}

	public static function add_settings_fields($processor_code) {
		if ($processor_code != self::$processor_code) return false; ?>
		<div class="sub-section-row">
			<div class="sub-section-label">
				<h3><?php esc_html_e('Connect (Live)', 'latepoint'); ?></h3>
			</div>
			<div class="sub-section-content">
				<div data-env="<?php echo esc_attr(LATEPOINT_PAYMENTS_ENV_LIVE); ?>"
				     class="payment-processor-connect-status-wrapper stripe-connect-status-wrapper"
				     data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('stripe_connect', 'check_connect_status')); ?>">
					<div class="os-loading-spinner"></div>
				</div>
			</div>
		</div>
		<div class="sub-section-row">
			<div class="sub-section-label">
				<h3><?php esc_html_e('Connect (Dev)', 'latepoint'); ?></h3>
			</div>
			<div class="sub-section-content">
				<div data-env="<?php echo esc_attr(LATEPOINT_PAYMENTS_ENV_DEV); ?>"
				     class="payment-processor-connect-status-wrapper stripe-connect-status-wrapper"
				     data-route-name="<?php echo esc_attr(OsRouterHelper::build_route_name('stripe_connect', 'check_connect_status')); ?>">
					<div class="os-loading-spinner"></div>
				</div>
			</div>
		</div>
		<div class="sub-section-row">
			<div class="sub-section-label">
				<h3><?php esc_html_e('Other Settings', 'latepoint'); ?></h3>
			</div>
			<div class="sub-section-content">
				<?php
				$selected_stripe_country_code = OsSettingsHelper::get_settings_value('stripe_connect_country_code', 'US');
				$selected_stripe_currency_iso_code = OsSettingsHelper::get_settings_value('stripe_connect_currency_iso_code', 'usd'); ?>
				<div class="os-row os-mb-2">
					<div class="os-col-6">
						<?php echo OsFormHelper::select_field('settings[stripe_connect_country_code]', __('Country', 'latepoint'), self::load_countries_list(), $selected_stripe_country_code); ?>
					</div>
					<div class="os-col-6">
						<?php echo OsFormHelper::select_field('settings[stripe_connect_currency_iso_code]', __('Currency Code', 'latepoint'), OsStripeConnectHelper::load_all_currencies_list(), $selected_stripe_currency_iso_code); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function get_stripe_customer_id(OsCustomerModel $customer) {
		return $customer->get_meta_by_key(OsSettingsHelper::append_payment_env_key('stripe_connect_customer_id'), '');
	}

	public static function save_stripe_customer_id(OsCustomerModel $customer, string $stripe_customer_id) {
		return $customer->save_meta_by_key(OsSettingsHelper::append_payment_env_key('stripe_connect_customer_id'), $stripe_customer_id);
	}


	public static function get_customer($stripe_customer_id): \LatePoint\Misc\StripeConnectCustomer {
		$response = self::do_account_request("customers/{$stripe_customer_id}");
		$stripe_connect_customer = new \LatePoint\Misc\StripeConnectCustomer();
		$stripe_connect_customer->id = $response['data']['id'];
		return $stripe_connect_customer;
	}

	public static function update_customer($stripe_customer_id, $customer, $values_to_update = array()) {
		$stripe_customer = self::get_customer($stripe_customer_id);
		if ($stripe_customer && $values_to_update) {
			foreach ($values_to_update as $key => $value) {
				if (in_array($key, self::get_properties_allowed_to_update())) {
					$stripe_customer->$key = $value;
				}
			}
			$stripe_customer->save();
		}
		return $stripe_customer;
	}

	public static function create_customer($customer) {
		$customer_data = [
			'email' => $customer->email,
			'name' => $customer->full_name
		];
		$response = self::do_account_request('customers', OsSettingsHelper::get_payments_environment(), '', 'POST', $customer_data);
		$result = ['id' => $response['data']['customer_id']];
		return $result;
	}


	public static function get_currency_iso_code() {
		return OsSettingsHelper::get_settings_value('stripe_connect_currency_iso_code', self::$default_currency_iso_code);
	}

	public static function get_server_token(string $force_env = ''): string {
		$key = OsSettingsHelper::append_payment_env_key('server_token_for_stripe_connect', $force_env);
		$server_token = OsSettingsHelper::get_settings_value($key, '');
		if (empty($server_token)) {
			$server_token = OsUtilHelper::generate_uuid();
			OsSettingsHelper::save_setting_by_name($key, $server_token);
		}
		return $server_token;
	}

	public static function get_connect_url(string $env = '') {
		$url = LATEPOINT_STRIPE_CONNECT_URL . '/wp/stripe-connection/' . $env . '/start/';
		$url .= self::get_server_token($env) . '/' . base64_encode(implode('|||', [get_bloginfo('name'), get_site_icon_url(), OsUtilHelper::get_site_url()]));
		return $url;
	}


	public static function do_account_request(string $path, string $env = '', string $connection_data = '', string $method = 'GET', array $vars = [], array $headers = []) {
		if (empty($env)) $env = OsSettingsHelper::get_payments_environment();
		$path = self::get_connect_account_id($env) . '/' . $path;
		return self::do_request($path, $connection_data, $method, $vars, $headers);
	}

	public static function do_request(string $path, string $connection_data = '', string $method = 'GET', array $vars = [], array $headers = [], string $force_env = '') {

		$default_vars = [];
		$default_headers = [
			'latepoint-version' => LATEPOINT_VERSION,
			'latepoint-domain' => OsUtilHelper::get_site_url(),
			'latepoint-license-key' => OsLicenseHelper::get_license_key()
		];

		if (!empty($connection_data)) {
			$default_headers['connection-data'] = $connection_data;
		}


		$args = array(
			'timeout' => 15,
			'headers' => array_merge($default_headers, $headers),
			'body' => array_merge($default_vars, $vars),
			'sslverify' => false,
			'method' => $method
		);


        // in our connect server we use test/live, while latepoint plugin uses dev/live
        if(!empty($force_env) && in_array($force_env, [LATEPOINT_PAYMENTS_ENV_DEV, LATEPOINT_PAYMENTS_ENV_LIVE])){
            $env = ($force_env == LATEPOINT_PAYMENTS_ENV_DEV) ? 'test' : 'live';
        }else{
            $env = (OsSettingsHelper::is_env_payments_dev() ? 'test' : 'live');
        }
		$url = LATEPOINT_STRIPE_CONNECT_URL . "/api/wp/v1/stripe-connect/{$env}/{$path}";

		$response = wp_remote_request($url, $args);

		if (!is_wp_error($response)) {
			$data = json_decode(wp_remote_retrieve_body($response), true);
			$data['status'] = $response['response'];
			return $data;
		} else {
			$error_message = $response->get_error_message();
			throw new Exception($error_message);
		}
	}


	public static function get_connection_buttons_and_status(string $env = '') {
		$stripe_connect_account_id = OsSettingsHelper::get_settings_value(OsSettingsHelper::append_payment_env_key('stripe_connect_account_id', $env), false);
		$html = '';
		if ($stripe_connect_account_id) {
			$charges_enabled = OsSettingsHelper::is_on(OsSettingsHelper::append_payment_env_key('stripe_connect_charges_enabled', $env));
			$disconnect_link = '<a class="payment-processor-disconnect-link" href="#"
										data-os-pass-response="yes"
										data-os-pass-this="yes"  
		                data-os-before-after="none" 
		                data-os-after-call="latepointStripeConnectAdmin.reload_connect_status_wrapper"
		                data-os-params="' . OsUtilHelper::build_os_params(['env' => $env]) . '"
										data-os-action="' . OsRouterHelper::build_route_name('stripe_connect', 'disconnect_connect_account') . '"
										><i class="latepoint-icon latepoint-icon-x"></i><span>' . __('disconnect', 'latepoint') . '</span></a>';
			if ($charges_enabled) {
				$html .= '<div class="payment-processor-status-connected"><i class="latepoint-icon latepoint-icon-check"></i><span>' . __('Connected', 'latepoint') . '</span></div>';
				$html .= $disconnect_link;
				$html .= '<div class="stripe-connect-account-info">' . $stripe_connect_account_id . '</div>';
			} else {
				$html .= '<div class="payment-processor-status-charges-disabled"><i class="latepoint-icon latepoint-icon-clock"></i><span>' . __('Pending Action', 'latepoint') . '</span></div>';
				$html .= '<a data-env="' . $env . '" data-route-name="' . OsRouterHelper::build_route_name('stripe_connect', 'start_connect_process') . '" href="#" class="payment-start-connecting"><span>' . __('Continue Setup', 'latepoint') . '</span><i class="latepoint-icon latepoint-icon-arrow-right"></i></a>';
				$html .= '<div class="stripe-connect-account-info">';
				$html .= '<div>' . $stripe_connect_account_id . '</div>';
				$html .= $disconnect_link;
				$html .= '</div>';
			}

		} else {
			$html .= '<a data-env="' . $env . '" data-route-name="' . OsRouterHelper::build_route_name('stripe_connect', 'start_connect_process') . '" href="#" class="payment-start-connecting"><span>' . __('Start Connecting', 'latepoint') . '</span><i class="latepoint-icon latepoint-icon-arrow-right"></i></a>';
		}
		return $html;
	}

	public static function retrieve_payment_intent(string $payment_intent_id): array {
		$payment_request_data = self::do_account_request('payment-intents/' . $payment_intent_id, OsSettingsHelper::get_payments_environment());
		$result = ['id' => $payment_request_data['data']['id'], 'status' => $payment_request_data['data']['status'], 'total' => $payment_request_data['data']['total']];
		return $result;
	}

	private static function get_properties_allowed_to_update($roles = 'admin') {
		return array('source', 'email', 'name');
	}

	public static function get_connect_publishable_key(): string {
		$key = OsSettingsHelper::get_settings_value(OsSettingsHelper::append_payment_env_key('stripe_connect_publishable_key'), '');
		if (empty($key)) {
			$response = self::do_request('public-key/');
			$key = $response['data']['key'];
			OsSettingsHelper::save_setting_by_name(OsSettingsHelper::append_payment_env_key('stripe_connect_publishable_key'), $key);
		}
		return $key;
	}

	public static function zero_decimal_currencies_list() {
		return array('bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf');
	}


	public static function load_countries_list() {
		$country_codes = ['AU' => 'Australia',
			'AT' => 'Austria',
			'BE' => 'Belgium',
			'BR' => 'Brazil',
			'BG' => 'Bulgaria',
			'CA' => 'Canada',
			'HR' => 'Croatia',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'EE' => 'Estonia',
			'FI' => 'Finland',
			'FR' => 'France',
			'DE' => 'Germany',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IN' => 'India',
			'IE' => 'Ireland',
			'IT' => 'Italy',
			'JP' => 'Japan',
			'LV' => 'Latvia',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MY' => 'Malaysia',
			'MT' => 'Malta',
			'MX' => 'Mexico',
			'NL' => 'Netherlands',
			'NZ' => 'New Zealand',
			'NO' => 'Norway',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'RO' => 'Romania',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'ES' => 'Spain',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'TH' => 'Thailand',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States'];
		return $country_codes;
	}


	public static function load_all_currencies_list(): array {
		return ['usd' => 'United States Dollar',
    'aed' => 'United Arab Emirates Dirham',
    'afn' => 'Afghan Afghani',
    'all' => 'Albanian Lek',
    'amd' => 'Armenian Dram',
    'ang' => 'Netherlands Antillean Guilder',
    'aoa' => 'Angolan Kwanza',
    'ars' => 'Argentine Peso',
    'aud' => 'Australian Dollar',
    'awg' => 'Aruban Florin',
    'azn' => 'Azerbaijani Manat',
    'bam' => 'Bosnia-Herzegovina Convertible Mark',
    'bbd' => 'Barbadian Dollar',
    'bdt' => 'Bangladeshi Taka',
    'bgn' => 'Bulgarian Lev',
    'bif' => 'Burundian Franc',
    'bmd' => 'Bermudian Dollar',
    'bnd' => 'Brunei Dollar',
    'bob' => 'Bolivian Boliviano',
    'brl' => 'Brazilian Real',
    'bsd' => 'Bahamian Dollar',
    'bwp' => 'Botswana Pula',
    'bzd' => 'Belize Dollar',
    'cad' => 'Canadian Dollar',
    'cdf' => 'Congolese Franc',
    'chf' => 'Swiss Franc',
    'clp' => 'Chilean Peso',
    'cny' => 'Chinese Yuan',
    'cop' => 'Colombian Peso',
    'crc' => 'Costa Rican Colón',
    'cve' => 'Cape Verdean Escudo',
    'czk' => 'Czech Koruna',
    'djf' => 'Djiboutian Franc',
    'dkk' => 'Danish Krone',
    'dop' => 'Dominican Peso',
    'dzd' => 'Algerian Dinar',
    'egp' => 'Egyptian Pound',
    'etb' => 'Ethiopian Birr',
    'eur' => 'Euro',
    'fjd' => 'Fijian Dollar',
    'fkp' => 'Falkland Islands Pound',
    'gbp' => 'British Pound Sterling',
    'gel' => 'Georgian Lari',
    'gip' => 'Gibraltar Pound',
    'gmd' => 'Gambian Dalasi',
    'gnf' => 'Guinean Franc',
    'gtq' => 'Guatemalan Quetzal',
    'gyd' => 'Guyanese Dollar',
    'hkd' => 'Hong Kong Dollar',
    'hnl' => 'Honduran Lempira',
    'hrk' => 'Croatian Kuna',
    'htg' => 'Haitian Gourde',
    'huf' => 'Hungarian Forint',
    'idr' => 'Indonesian Rupiah',
    'ils' => 'Israeli New Shekel',
    'inr' => 'Indian Rupee',
    'isk' => 'Icelandic Króna',
    'jmd' => 'Jamaican Dollar',
    'jpy' => 'Japanese Yen',
    'kes' => 'Kenyan Shilling',
    'kgs' => 'Kyrgyzstani Som',
    'khr' => 'Cambodian Riel',
    'kmf' => 'Comorian Franc',
    'krw' => 'South Korean Won',
    'kyd' => 'Cayman Islands Dollar',
    'kzt' => 'Kazakhstani Tenge',
    'lak' => 'Lao Kip',
    'lbp' => 'Lebanese Pound',
    'lkr' => 'Sri Lankan Rupee',
    'lrd' => 'Liberian Dollar',
    'lsl' => 'Lesotho Loti',
    'mad' => 'Moroccan Dirham',
    'mdl' => 'Moldovan Leu',
    'mga' => 'Malagasy Ariary',
    'mkd' => 'Macedonian Denar',
    'mmk' => 'Myanmar Kyat',
    'mnt' => 'Mongolian Tögrög',
    'mop' => 'Macanese Pataca',
    'mro' => 'Mauritanian Ouguiya (pre-2018)',
    'mur' => 'Mauritian Rupee',
    'mvr' => 'Maldivian Rufiyaa',
    'mwk' => 'Malawian Kwacha',
    'mxn' => 'Mexican Peso',
    'myr' => 'Malaysian Ringgit',
    'mzn' => 'Mozambican Metical',
    'nad' => 'Namibian Dollar',
    'ngn' => 'Nigerian Naira',
    'nio' => 'Nicaraguan Córdoba',
    'nok' => 'Norwegian Krone',
    'npr' => 'Nepalese Rupee',
    'nzd' => 'New Zealand Dollar',
    'pab' => 'Panamanian Balboa',
    'pen' => 'Peruvian Sol',
    'pgk' => 'Papua New Guinean Kina',
    'php' => 'Philippine Peso',
    'pkr' => 'Pakistani Rupee',
    'pln' => 'Polish Złoty',
    'pyg' => 'Paraguayan Guarani',
    'qar' => 'Qatari Riyal',
    'ron' => 'Romanian Leu',
    'rsd' => 'Serbian Dinar',
    'rub' => 'Russian Ruble',
    'rwf' => 'Rwandan Franc',
    'sar' => 'Saudi Riyal',
    'sbd' => 'Solomon Islands Dollar',
    'scr' => 'Seychellois Rupee',
    'sek' => 'Swedish Krona',
    'sgd' => 'Singapore Dollar',
    'shp' => 'Saint Helena Pound',
    'sll' => 'Sierra Leonean Leone',
    'sos' => 'Somali Shilling',
    'srd' => 'Surinamese Dollar',
    'std' => 'São Tomé and Príncipe Dobra (pre-2018)',
    'svc' => 'Salvadoran Colón',
    'szl' => 'Swazi Lilangeni',
    'thb' => 'Thai Baht',
    'tjs' => 'Tajikistani Somoni',
    'top' => 'Tongan Paʻanga',
    'try' => 'Turkish Lira',
    'ttd' => 'Trinidad and Tobago Dollar',
    'twd' => 'New Taiwan Dollar',
    'tzs' => 'Tanzanian Shilling',
    'uah' => 'Ukrainian Hryvnia',
    'ugx' => 'Ugandan Shilling',
    'uyu' => 'Uruguayan Peso',
    'uzs' => 'Uzbekistani Som',
    'vnd' => 'Vietnamese Đồng',
    'vuv' => 'Vanuatu Vatu',
    'wst' => 'Samoan Tālā',
    'xaf' => 'Central African CFA Franc',
    'xcd' => 'East Caribbean Dollar',
    'xof' => 'West African CFA Franc',
    'xpf' => 'CFP Franc',
    'yer' => 'Yemeni Rial',
    'zar' => 'South African Rand',
    'zmw' => 'Zambian Kwacha'];
	}


	public static function get_connect_account_id(string $env = '') {
		if (empty($env)) $env = OsSettingsHelper::get_payments_environment();
		return OsSettingsHelper::get_settings_value(OsSettingsHelper::append_payment_env_key('stripe_connect_account_id', $env), '');
	}

	public static function generate_payment_intent_id_and_secret_for_transaction_intent(OsTransactionIntentModel $transaction_intent): array {
        $order = new OsOrderModel($transaction_intent->order_id);
		$customer_data = ['name' => $order->customer->full_name, 'email' => $order->customer->email];
		$options = [
			'amount' => $transaction_intent->specs_charge_amount,
			'currency' => self::get_currency_iso_code(),
			'stripe_customer_id' => self::get_stripe_customer_id($order->customer),
            'transaction_description' => esc_html__('Payment for Appointment', 'latepoint'),
			'metadata' => [
				'transaction_intent_key' => $transaction_intent->intent_key
			]
		];


		// pass customer data in case it needs to be created
		$result = self::do_account_request('payment-intents', OsSettingsHelper::get_payments_environment(), '', 'POST', ['payment_intent_options' => $options, 'customer_data' => $customer_data]);
		if (empty($result['data'])) {
            // translators: %s is the payment error
			$error_message = !empty($result['error']) ? sprintf(__('Payment Error: %s', 'latepoint'), esc_html($result['error'])) : __('Payment error', 'latepoint');
			OsDebugHelper::log($error_message);
			throw new Exception($error_message);
		} else {
			// make sure we use correct stripe customer id in case the one that was passed is invalid - a valid one will be returned in this call
			if ($result['data']['stripe_customer_id'] != self::get_stripe_customer_id($order->customer)) self::save_stripe_customer_id($order->customer, $result['data']['stripe_customer_id']);

			return ['id' => $result['data']['id'], 'client_secret' => $result['data']['client_secret']];
		}
	}

	public static function generate_payment_intent_id_and_secret_for_order_intent(OsOrderIntentModel $order_intent): array {
		$customer_data = ['name' => $order_intent->customer->full_name, 'email' => $order_intent->customer->email];
		$options = [
			'amount' => $order_intent->specs_charge_amount,
			'currency' => self::get_currency_iso_code(),
			'stripe_customer_id' => self::get_stripe_customer_id($order_intent->customer),
            'transaction_description' => esc_html__('Payment for Appointment', 'latepoint'),
			'metadata' => [
				'order_intent_key' => $order_intent->intent_key
			]
		];


		// pass customer data in case it needs to be created
		$result = self::do_account_request('payment-intents', OsSettingsHelper::get_payments_environment(), '', 'POST', ['payment_intent_options' => $options, 'customer_data' => $customer_data]);
		if (empty($result['data'])) {
            // translators: %s is the payment error
			$error_message = !empty($result['error']) ? sprintf(__('Payment Error: %s', 'latepoint'), esc_html($result['error'])) : __('Payment error', 'latepoint');
			OsDebugHelper::log($error_message);
			throw new Exception($error_message);
		} else {
			// make sure we use correct stripe customer id in case the one that was passed is invalid - a valid one will be returned in this call
			if ($result['data']['stripe_customer_id'] != self::get_stripe_customer_id($order_intent->customer)) self::save_stripe_customer_id($order_intent->customer, $result['data']['stripe_customer_id']);

			return ['id' => $result['data']['id'], 'client_secret' => $result['data']['client_secret']];
		}
	}

	public static function build_customer_profile_link(string $stripe_customer_id, bool $test_env = false): string {
		return 'https://dashboard.stripe.com/' . ($test_env ? 'test/' : '') . 'customers/' . $stripe_customer_id;
	}
}