<?php 

class OsMoneyHelper {


	/**
	 * @param $amount
	 * @param $include_currency
	 * @param $hide_zero_decimals
	 * @return string
	 *
	 * Formats amount from database format (99999.0000) to requested format, optionally can include currency symbol and strip zero cents
	 *
	 */
  public static function format_price($amount, $include_currency = true, $hide_zero_decimals = true): string{
		$decimal_separator = OsSettingsHelper::get_settings_value('decimal_separator', '.');
		$thousand_separator = OsSettingsHelper::get_settings_value('thousand_separator', ',');
		$decimals = OsSettingsHelper::get_settings_value('number_of_decimals', '2');
		if(empty($amount)) $amount = 0;
  	$amount = number_format($amount, $decimals, $decimal_separator, $thousand_separator);
    if($hide_zero_decimals){
			$zeros = '';
			switch($decimals){
				case '1': $zeros = '0'; break;
				case '2': $zeros = '00'; break;
				case '3': $zeros = '000'; break;
				case '4': $zeros = '0000'; break;
			}
			$amount = str_replace($decimal_separator.$zeros, '', $amount);
    }
  	if($include_currency) $amount = implode('', array(OsSettingsHelper::get_settings_value('currency_symbol_before'), $amount, OsSettingsHelper::get_settings_value('currency_symbol_after')));
		$amount = apply_filters('latepoint_format_price', $amount, $include_currency, $hide_zero_decimals);
		return $amount;
  }

	// formats amount to be used in input money fields
	public static function to_money_field_format($amount){
		return self::format_price((float)$amount, false, false);
	}

	// amount stripped from any formatting like currency symbol, thousand separator, just numbers and decimal separator is left
  public static function convert_amount_from_money_input_to_db_format($amount){
		$decimal_separator = OsSettingsHelper::get_settings_value('decimal_separator', '.');
    $amount = preg_replace('/[^-\\d'.$decimal_separator.']+/', '', $amount);
		// database is using dot as a decimal separator, if latepoint is not using dot for currency input - convert it to dot to store in db
		if($decimal_separator != '.') $amount = str_replace($decimal_separator, '.', $amount);
		$amount = self::pad_to_db_format($amount);
    return $amount;
  }

	public static function convert_value_from_percent_input_to_db_format($value){
		$decimal_separator = OsSettingsHelper::get_settings_value('decimal_separator', '.');
    $value = preg_replace('/[^-\\d'.$decimal_separator.']+/', '', $value);
		// database is using dot as a decimal separator, if latepoint is not using dot for input - convert it to dot to store in db
		if($decimal_separator != '.') $value = str_replace($decimal_separator, '.', $value);
		$value = self::pad_to_db_format($value);
		return $value;
	}

	public static function pad_to_db_format($amount){
		return number_format((float)$amount, 4, '.', '');
	}

}