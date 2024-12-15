<?php

class OsUtilHelper {

	public static function add_auto_print_script(){
		wp_register_script('latepoint-auto-print', false);
	    wp_enqueue_script('latepoint-auto-print');
	    wp_add_inline_script('latepoint-auto-print', '
	        window.onload = function() {
	            window.print();
	        }
	    ');
	}

  public static function generate_missing_addon_link($label){
    $html = '<a target="_blank" href="'.esc_url(LATEPOINT_UPGRADE_URL).'" class="os-add-box" >
              <div class="add-box-graphic-w"><div class="add-box-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div></div>
              <div class="add-box-label">'.esc_html($label).'</div>
            </a>';
    return $html;
  }

	public static function extract_plugin_name_from_path(string $plugin_path) : string{
		preg_match('/latepoint-([^-\/]+(?:-[^-\/]+)*)\//', $plugin_path, $matches);
		if (isset($matches[1])) {
			// Convert hyphenated words to capitalized words
			$name = ucwords(str_replace('-', ' ', $matches[1]));
		}else{
			$name = 'n/a';
    }
		return $name;
	}

	public static function first_value_if_array($value){
		if(!empty($value) && is_array($value) && isset($value[0])) return $value[0];
		else return $value;
	}

	public static function get_array_of_ids_from_array_of_models(array $models): array{
		$ids = [];
		if($models){
			foreach($models as $model){
				if(property_exists($model, 'id')) $ids[] = $model->id;
			}
		}
		return $ids;
	}

	/**
	 * Cleans array of ids and makes sure they are integers
	 *
	 * @param array $ids
	 * @return array
	 */
	public static function clean_numeric_ids(array $ids): array{
		$clean_ids = [];
		if(!$ids) return $clean_ids;
		foreach($ids as $id){
			if(filter_var($id, FILTER_VALIDATE_INT)) $clean_ids[] = $id;
		}
		return $clean_ids;
	}

	public static function explode_and_trim($string){
		return preg_split('/(\s*,*\s*)*,+(\s*,*\s*)*/', $string, -1, PREG_SPLIT_NO_EMPTY);
	}

	public static function replace_single_curly_with_double(string $string): string{
		$string = preg_replace("/(?<!{){(?!{)/", "{{", $string);
		$string = preg_replace("/(?<!})}(?!})/", "}}", $string);
		return $string;
	}

	public static function compare_model_data_vars($new_data_vars, $old_data_vars, $parent = false){
		$level = $parent ? [$parent] : [];
		$changes = [];
		foreach($new_data_vars as $key => $var){
			// check if both empty (could be null and '', that doesn't mean its changed)
			if(empty($old_data_vars[$key]) && empty($var)) continue;
			if(!isset($old_data_vars[$key]) || $var != $old_data_vars[$key]) $changes[$key] = ['before' => $old_data_vars[$key] ?? '', 'after' => $var];
		}
		foreach($old_data_vars as $key => $var){
			// check if both empty (could be null and '', that doesn't mean its changed)
			if(empty($new_data_vars[$key]) && empty($var)) continue;
			if(!isset($changes[$key]) && !isset($new_data_vars[$key]) || $var != $new_data_vars[$key]) $changes[$key] = ['before' => $var ?? '', 'after' => $new_data_vars[$key] ?? ''];
		}
		return $changes;
	}

	public static function ordered_column_html($order_by, $column){
		if($order_by['column'] == $column) return ' class="ordered-'.$order_by['direction'].'"';
	}

  public static function generate_uuid(){
    $data = openssl_random_pseudo_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }

  public static function get_site_url(){
    return network_home_url();
  }

  public static function is_off($value){
    return ($value != 'on');
  }

  public static function is_on($value){
    return ($value == 'on');
  }

  public static function template_variables_link_html(){
    return '<a href="#" class="field-note-info-link open-template-variables-panel"><i class="latepoint-icon latepoint-icon-info"></i><span>'.esc_html__('Show Available Variables', 'latepoint').'</span></a>';
  }

  public static function hex2rgba($color, $opacity = false) {
  $default = 'rgb(0,0,0)';
  if(empty($color))
    return $default;

  //Sanitize $color if "#" is provided
  if ($color[0] == '#' ) {
    $color = substr( $color, 1 );
  }

  //Check if color has 6 or 3 characters and get values
  if (strlen($color) == 6) {
          $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
  } elseif ( strlen( $color ) == 3 ) {
          $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
  } else {
          return $default;
  }

  //Convert hexadec to rgb
  $rgb =  array_map('hexdec', $hex);

  //Check if opacity is set(rgba or rgb)
  if($opacity){
    if(abs($opacity) > 1)
      $opacity = 1.0;
    $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
  } else {
    $output = 'rgb('.implode(",",$rgb).')';
  }

  //Return rgb(a) color string
  return $output;
}

  public static function percent_diff($before, $now){
		if($before == $now) return 0;
		$sign = ($before > $now) ? '-' : '+';
    if($before > 0 && $now > 0){
      if($before > $now){
        return $sign.round(($before - $now) / $before * 100);
      }else{
        return $sign.round(($now - $before) / $before * 100);
      }
    }else{
      return $sign.'100';
    }
  }


  public static function get_weekday_numbers(){
    return array(1,2,3,4,5,6,7);
  }

  public static function is_valid_email($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }

  public static function merge_default_atts($defaults = [], $settings = []){
    return array_merge($defaults, array_intersect_key($settings, $defaults));
  }

  public static function random_text( $type = 'nozero', $length = 6 ): string{
    switch ( $type ) {
      case 'nozero':
        $pool = '123456789';
        break;
      case 'alnum':
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        break;
      case 'alpha':
        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        break;
      case 'hexdec':
        $pool = '0123456789abcdef';
        break;
      case 'numeric':
        $pool = '0123456789';
        break;
      case 'distinct':
        $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
        break;
      default:
        $pool = (string) $type;
        break;
    }


    $crypto_rand_secure = function ( $min, $max ) {
      $range = $max - $min;
      if ( $range < 0 ) return $min; // not so random...
      $log    = log( $range, 2 );
      $bytes  = (int) ( $log / 8 ) + 1; // length in bytes
      $bits   = (int) $log + 1; // length in bits
      $filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
      do {
        $rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
        $rnd = $rnd & $filter; // discard irrelevant bits
      } while ( $rnd >= $range );
      return $min + $rnd;
    };

    $token = "";
    $max   = strlen( $pool );
    for ( $i = 0; $i < $length; $i++ ) {
      $token .= $pool[$crypto_rand_secure( 0, $max )];
    }
    return $token;
  }

	public static function ellipse_string( $string, $length ): string {
		return substr( $string, 0, $length ) . ( strlen( $string ) > $length ? '...' : '' );
	}

  public static function get_month_name_by_number($month_number, $short = false){
    $full_names = [__('January', 'latepoint'),
                    __('February', 'latepoint'),
                    __('March', 'latepoint'),
                    __('April', 'latepoint'),
                    __('May', 'latepoint'),
                    __('June', 'latepoint'),
                    __('July', 'latepoint'),
                    __('August', 'latepoint'),
                    __('September', 'latepoint'),
                    __('October', 'latepoint'),
                    __('November', 'latepoint'),
                    __('December', 'latepoint')];
		$short_names = [__('Jan', 'latepoint'),
                    __('Feb', 'latepoint'),
                    __('Mar', 'latepoint'),
                    __('Apr', 'latepoint'),
                    _x('May', 'short version of May month', 'latepoint'),
                    __('Jun', 'latepoint'),
                    __('Jul', 'latepoint'),
                    __('Aug', 'latepoint'),
                    __('Sep', 'latepoint'),
                    __('Oct', 'latepoint'),
                    __('Nov', 'latepoint'),
                    __('Dec', 'latepoint')];

	if(empty($month_number)) return '';
    if($short){
	    $month_name = isset($short_names[$month_number - 1]) ? $short_names[$month_number - 1] : 'n/a';
    }else{
	    $month_name = isset($full_names[$month_number - 1]) ? $full_names[$month_number - 1] : 'n/a';
    }
    return $month_name;
  }


  public static function translated_months(){
    return [ 'January' => __('January', 'latepoint'),
                'February' => __('February', 'latepoint'),
                'March' => __('March', 'latepoint'),
                'April' => __('April', 'latepoint'),
                'May' => __('May', 'latepoint'),
                'June' => __('June', 'latepoint'),
                'July' => __('July', 'latepoint'),
                'August' => __('August', 'latepoint'),
                'September' => __('September', 'latepoint'),
                'October' => __('October', 'latepoint'),
                'November' => __('November', 'latepoint'),
                'December' => __('December', 'latepoint'),
                'Jan' => __('Jan', 'latepoint'),
                'Feb' => __('Feb', 'latepoint'),
                'Mar' => __('Mar', 'latepoint'),
                'Apr' => __('Apr', 'latepoint'),
                'Jun' => __('Jun', 'latepoint'),
                'Jul' => __('Jul', 'latepoint'),
                'Aug' => __('Aug', 'latepoint'),
                'Sep' => __('Sep', 'latepoint'),
                'Oct' => __('Oct', 'latepoint'),
                'Nov' => __('Nov', 'latepoint'),
                'Dec' => __('Dec', 'latepoint')];
  }

  public static function translate_months($date_string){
    $date_string = str_replace(array_keys(self::translated_months()), array_values(self::translated_months()), $date_string);
    return $date_string;
  }

  public static function get_months_for_select(){
    $months = [];
    for($i = 1; $i<= 12; $i++){
      $months[] = ['label' => self::get_month_name_by_number($i), 'value' => $i];
    }
    return $months;
  }

  public static function get_weekday_name_by_number($weekday_number, $short = false){
    $weekday_names = [__('Monday', 'latepoint'),
                      __('Tuesday', 'latepoint'),
                      __('Wednesday', 'latepoint'),
                      __('Thursday', 'latepoint'),
                      __('Friday', 'latepoint'),
                      __('Saturday', 'latepoint'),
                      __('Sunday', 'latepoint')];
    $weekday_name = isset($weekday_names[$weekday_number - 1]) ? $weekday_names[$weekday_number - 1] : 'n/a';
    if($short) $weekday_name = mb_substr($weekday_name, 0, 3);
    return $weekday_name;
  }

  // Checks if array is associative
  public static function is_array_a($array){
    return count(array_filter(array_keys($array), 'is_string')) > 0;
  }


	public static function models_to_select_options(array $models, string $value_key, string $label_key): array{
		if(empty($models)) return [];
		$options = [];
		foreach($models as $model){
			if(method_exists($model, $label_key)){
				$label = call_user_func_array([$model, $label_key], []);
			}elseif(property_exists($model, $label_key)){
				$label = $model->$label_key;
			}
			if(method_exists($model, $value_key)){
				$value = call_user_func_array([$model, $value_key], []);
			}elseif(property_exists($model, $value_key)){
				$value = $model->$value_key;
			}
			if(isset($label) && isset($value)){
				$options[] = ['value' => $value, 'label' => $label];
			}
			unset($value);
			unset($label);
		}
		return $options;
	}


	public static function array_to_select_options(array $array, string $value_key, string $label_key): array{
		if(empty($array)) return [];
		$options = [];
		foreach($array as $item){
			if(isset($item[$value_key]) && isset($item[$label_key])) $options[$item[$value_key]] = $item[$label_key];
		}
		return $options;
	}

	public static function transform_flat_list_for_multi_select( array $flat_list ): array {
		$result_list = [];

		foreach ( $flat_list as $key => $value ) {
			$result_list[] = [
				'value'   => $key,
				'label' => $value
			];
		}

		return $result_list;
	}


	/**
	 *
	 * Returns phone number in format +18888888888, strips all none plus characters and digits, prepends country code if provided
	 *
	 * @param string $number
	 * @param string $country_code
	 * @return string
	 */
	public static function sanitize_phone_number(string $number, string $country_code = ''): string{
		if(empty($number)) return '';
		$add_country_code = (substr($number, 0, 1) == '+') ? '+' : $country_code; // figure out if country code is missing
		if(stripos($number, 'x') !== false) $number = substr($number, 0, stripos($number, 'x')); // strip extension

		$formatted_phone = preg_replace('/[^\d]/', '', $number);
		if(!empty($formatted_phone)){
			return $add_country_code.$formatted_phone;
		}else{
			return '';
		}
	}

  public static function get_countries_list(): array {
		return [ "us" => "United States", "af" => "Afghanistan", "al" => "Albania", "dz" => "Algeria", "as" => "American Samoa", "ad" => "Andorra", "ao" => "Angola", "ai" => "Anguilla", "ag" => "Antigua and Barbuda", "ar" => "Argentina", "am" => "Armenia (Հայաստան)", "aw" => "Aruba", "ac" => "Ascension Island", "au" => "Australia", "at" => "Austria (Österreich)", "az" => "Azerbaijan (Azərbaycan)", "bs" => "Bahamas", "bh" => "Bahrain (‫البحرين‬‎)", "bd" => "Bangladesh (বাংলাদেশ)", "bb" => "Barbados", "by" => "Belarus (Беларусь)", "be" => "Belgium (België)", "bz" => "Belize", "bj" => "Benin (Bénin)", "bm" => "Bermuda", "bt" => "Bhutan (འབྲུག)", "bo" => "Bolivia", "ba" => "Bosnia and Herzegovina (Босна и Херцеговина)", "bw" => "Botswana", "br" => "Brazil (Brasil)", "io" => "British Indian Ocean Territory", "vg" => "British Virgin Islands", "bn" => "Brunei", "bg" => "Bulgaria (България)", "bf" => "Burkina Faso", "bi" => "Burundi (Uburundi)", "kh" => "Cambodia (កម្ពុជា)", "cm" => "Cameroon (Cameroun)", "ca" => "Canada", "cv" => "Cape Verde (Kabu Verdi)", "bq" => "Caribbean Netherlands", "ky" => "Cayman Islands", "cf" => "Central African Republic (République centrafricaine)", "td" => "Chad (Tchad)", "cl" => "Chile", "cn" => "China (中国)", "cx" => "Christmas Island", "cc" => "Cocos (Keeling) Islands", "co" => "Colombia", "km" => "Comoros (‫جزر القمر‬‎)", "cd" => "Congo (DRC) (Jamhuri ya Kidemokrasia ya Kongo)", "cg" => "Congo (Republic) (Congo-Brazzaville)", "ck" => "Cook Islands", "cr" => "Costa Rica", "ci" => "Côte d’Ivoire", "hr" => "Croatia (Hrvatska)", "cu" => "Cuba", "cw" => "Curaçao", "cy" => "Cyprus (Κύπρος)", "cz" => "Czech Republic (Česká republika)", "dk" => "Denmark (Danmark)", "dj" => "Djibouti", "dm" => "Dominica", "do" => "Dominican Republic (República Dominicana)", "ec" => "Ecuador", "eg" => "Egypt (‫مصر‬‎)", "sv" => "El Salvador", "gq" => "Equatorial Guinea (Guinea Ecuatorial)", "er" => "Eritrea", "ee" => "Estonia (Eesti)", "sz" => "Eswatini", "et" => "Ethiopia", "fk" => "Falkland Islands (Islas Malvinas)", "fo" => "Faroe Islands (Føroyar)", "fj" => "Fiji", "fi" => "Finland (Suomi)", "fr" => "France", "gf" => "French Guiana (Guyane française)", "pf" => "French Polynesia (Polynésie française)", "ga" => "Gabon", "gm" => "Gambia", "ge" => "Georgia (საქართველო)", "de" => "Germany (Deutschland)", "gh" => "Ghana (Gaana)", "gi" => "Gibraltar", "gr" => "Greece (Ελλάδα)", "gl" => "Greenland (Kalaallit Nunaat)", "gd" => "Grenada", "gp" => "Guadeloupe", "gu" => "Guam", "gt" => "Guatemala", "gg" => "Guernsey", "gn" => "Guinea (Guinée)", "gw" => "Guinea-Bissau (Guiné Bissau)", "gy" => "Guyana", "ht" => "Haiti", "hn" => "Honduras", "hk" => "Hong Kong (香港)", "hu" => "Hungary (Magyarország)", "is" => "Iceland (Ísland)", "in" => "India (भारत)", "id" => "Indonesia", "ir" => "Iran (‫ایران‬‎)", "iq" => "Iraq (‫العراق‬‎)", "ie" => "Ireland", "im" => "Isle of Man", "il" => "Israel (‫ישראל‬‎)", "it" => "Italy (Italia)", "jm" => "Jamaica", "jp" => "Japan (日本)", "je" => "Jersey", "jo" => "Jordan (‫الأردن‬‎)", "kz" => "Kazakhstan (Казахстан)", "ke" => "Kenya", "ki" => "Kiribati", "xk" => "Kosovo", "kw" => "Kuwait (‫الكويت‬‎)", "kg" => "Kyrgyzstan (Кыргызстан)", "la" => "Laos (ລາວ)", "lv" => "Latvia (Latvija)", "lb" => "Lebanon (‫لبنان‬‎)", "ls" => "Lesotho", "lr" => "Liberia", "ly" => "Libya (‫ليبيا‬‎)", "li" => "Liechtenstein", "lt" => "Lithuania (Lietuva)", "lu" => "Luxembourg", "mo" => "Macau (澳門)", "mk" => "North Macedonia (Македонија)", "mg" => "Madagascar (Madagasikara)", "mw" => "Malawi", "my" => "Malaysia", "mv" => "Maldives", "ml" => "Mali", "mt" => "Malta", "mh" => "Marshall Islands", "mq" => "Martinique", "mr" => "Mauritania (‫موريتانيا‬‎)", "mu" => "Mauritius (Moris)", "yt" => "Mayotte", "mx" => "Mexico (México)", "fm" => "Micronesia", "md" => "Moldova (Republica Moldova)", "mc" => "Monaco", "mn" => "Mongolia (Монгол)", "me" => "Montenegro (Crna Gora)", "ms" => "Montserrat", "ma" => "Morocco (‫المغرب‬‎)", "mz" => "Mozambique (Moçambique)", "mm" => "Myanmar (Burma) (မြန်မာ)", "na" => "Namibia (Namibië)", "nr" => "Nauru", "np" => "Nepal (नेपाल)", "nl" => "Netherlands (Nederland)", "nc" => "New Caledonia (Nouvelle-Calédonie)", "nz" => "New Zealand", "ni" => "Nicaragua", "ne" => "Niger (Nijar)", "ng" => "Nigeria", "nu" => "Niue", "nf" => "Norfolk Island", "kp" => "North Korea (조선 민주주의 인민 공화국)", "mp" => "Northern Mariana Islands", "no" => "Norway (Norge)", "om" => "Oman (‫عُمان‬‎)", "pk" => "Pakistan (‫پاکستان‬‎)", "pw" => "Palau", "ps" => "Palestine (‫فلسطين‬‎)", "pa" => "Panama (Panamá)", "pg" => "Papua New Guinea", "py" => "Paraguay", "pe" => "Peru (Perú)", "ph" => "Philippines", "pl" => "Poland (Polska)", "pt" => "Portugal", "pr" => "Puerto Rico", "qa" => "Qatar (‫قطر‬‎)", "re" => "Réunion (La Réunion)", "ro" => "Romania (România)", "ru" => "Russia (Россия)", "rw" => "Rwanda", "bl" => "Saint Barthélemy", "sh" => "Saint Helena", "kn" => "Saint Kitts and Nevis", "lc" => "Saint Lucia", "mf" => "Saint Martin (Saint-Martin (partie française))", "pm" => "Saint Pierre and Miquelon (Saint-Pierre-et-Miquelon)", "vc" => "Saint Vincent and the Grenadines", "ws" => "Samoa", "sm" => "San Marino", "st" => "São Tomé and Príncipe (São Tomé e Príncipe)", "sa" => "Saudi Arabia (‫المملكة العربية السعودية‬‎)", "sn" => "Senegal (Sénégal)", "rs" => "Serbia (Србија)", "sc" => "Seychelles", "sl" => "Sierra Leone", "sg" => "Singapore", "sx" => "Sint Maarten", "sk" => "Slovakia (Slovensko)", "si" => "Slovenia (Slovenija)", "sb" => "Solomon Islands", "so" => "Somalia (Soomaaliya)", "za" => "South Africa", "kr" => "South Korea (대한민국)", "ss" => "South Sudan (‫جنوب السودان‬‎)", "es" => "Spain (España)", "lk" => "Sri Lanka (ශ්‍රී ලංකාව)", "sd" => "Sudan (‫السودان‬‎)", "sr" => "Suriname", "sj" => "Svalbard and Jan Mayen", "se" => "Sweden (Sverige)", "ch" => "Switzerland (Schweiz)", "sy" => "Syria (‫سوريا‬‎)", "tw" => "Taiwan (台灣)", "tj" => "Tajikistan", "tz" => "Tanzania", "th" => "Thailand (ไทย)", "tl" => "Timor-Leste", "tg" => "Togo", "tk" => "Tokelau", "to" => "Tonga", "tt" => "Trinidad and Tobago", "tn" => "Tunisia (‫تونس‬‎)", "tr" => "Turkey (Türkiye)", "tm" => "Turkmenistan", "tc" => "Turks and Caicos Islands", "tv" => "Tuvalu", "vi" => "U.S. Virgin Islands", "ug" => "Uganda", "ua" => "Ukraine (Україна)", "ae" => "United Arab Emirates (‫الإمارات العربية المتحدة‬‎)", "gb" => "United Kingdom", "uy" => "Uruguay", "uz" => "Uzbekistan (Oʻzbekiston)", "vu" => "Vanuatu", "va" => "Vatican City (Città del Vaticano)", "ve" => "Venezuela", "vn" => "Vietnam (Việt Nam)", "wf" => "Wallis and Futuna (Wallis-et-Futuna)", "eh" => "Western Sahara (‫الصحراء الغربية‬‎)", "ye" => "Yemen (‫اليمن‬‎)", "zm" => "Zambia", "zw" => "Zimbabwe", "ax" => "Åland Islands", ];
  }

  public static function is_date_valid($date_string){
    return (bool)strtotime($date_string);
  }

  public static function build_os_params($params = array(), string $nonce_action = ''){
		if(!empty($nonce_action)){
			$params['_wpnonce'] = wp_create_nonce($nonce_action);
		}
    return http_build_query($params);
  }


  public static function get_user_ip() : string{
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ){
      $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
    }elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
      $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
    }else{
      $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));
    }

	// Sanitize the IP address
	$ip = filter_var( $ip, FILTER_VALIDATE_IP );

	if ( $ip === false ) {
	    // Handle invalid IP case
	    $ip = 'n/a';
	}

    return $ip;
  }

  public static function create_nonce($string = ''){
    return wp_create_nonce( 'latepoint_'.$string );
  }

  public static function verify_nonce($nonce, $string = ''){
    return wp_verify_nonce( $nonce, 'latepoint_'.$string );
  }

	public static function obfuscate_license(string $license_key): string {
		return preg_replace_callback('/-(.*)/', function($matches) {
      return '-' . str_repeat('*', strlen($matches[1]));
			}, $license_key);
	}

	public static function generate_form_id(): string {
		return 'new_'.self::random_text();
	}

	public static function pro_feature_block($label_override = false) {
		$label = $label_override ? $label_override : __('Requires upgrade to a premium version', 'latepoint');
		return '<a href="'.esc_url(LATEPOINT_UPGRADE_URL).'" class="os-add-box" >
            <div class="add-box-graphic-w"><div class="add-box-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div></div>
            <div class="add-box-label">'.esc_html($label).'</div>
          </a>';
	}

	public static function generate_key_to_manage() : string {
		return bin2hex( random_bytes( 18 ) );
	}
}