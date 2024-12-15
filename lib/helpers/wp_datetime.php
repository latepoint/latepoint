<?php 
class OsWpDateTime extends DateTime {
  function __construct($time = 'now', $timezone = false){
		$timezone = ($timezone instanceof DateTimeZone) ? $timezone : OsTimeHelper::get_wp_timezone();
		try{
		    parent::__construct($time, $timezone);
		}catch(Exception $e){
			OsDebugHelper::log('Error parsing date: '.$e->getMessage() , 'date_parsing' );
			return parent::__construct('now', $timezone);
		}
  }

	public static function datetime_in_utc(DateTime $datetime, $format = false){
		$utc_datetime = clone $datetime;
		$utc_datetime->setTimezone(new DateTimeZone("UTC"));
		return $format ? $utc_datetime->format($format) : $utc_datetime;
	}

	public static function date_to_db_format($date_string, $default = ''){
	  if(empty($date_string)) return $default;
	  try{
		  $date = self::os_createFromFormat(OsSettingsHelper::get_date_format(), $date_string);
		  return $date->format('Y-m-d');
	  }catch(Exception $e){
		  return $default;
	  }
	}

	public static function date_from_db_format($date_string, $default = ''){
	  if(empty($date_string)) return $default;
	  try{
		  $date = self::os_createFromFormat('Y-m-d', $date_string);
		  return $date->format(OsSettingsHelper::get_date_format());
	  }catch(Exception $e){
		  return $default;
	  }
	}

  public static function os_createFromFormat($format, $datetime_string, $timezone = false){
    $timezone = ($timezone) ? $timezone : OsTimeHelper::get_wp_timezone();
  	return self::createFromFormat($format, $datetime_string, $timezone);
  }

	// TODO will be deprecated, moved to GCal addon
  public static function os_get_start_of_google_event($google_event){
  	if(!empty($google_event->start->dateTime)){
  		$date_string = $google_event->start->dateTime;
  		$date_format = \DateTime::RFC3339;
			$timezone = new DateTimeZone($google_event->start->timeZone);
  	}else{
			// Full day event
  		$date_string = $google_event->start->date.' 00:00:00';
  		$date_format = 'Y-m-d H:i:s';
			$timezone = false;
  	}
		return self::os_createFromFormat($date_format, $date_string, $timezone);
  }

	// TODO will be deprecated, moved to GCal addon
  public static function os_get_end_of_google_event($google_event){
  	if(!empty($google_event->end->dateTime)){
  		$date_string = $google_event->end->dateTime;
  		$date_format = \DateTime::RFC3339;
			return self::os_createFromFormat($date_format, $date_string);
  	}else{
			// Full day event
			// !important,  in full day events of Google Calendar - start day is inclusive and the end day is exclusive https://stackoverflow.com/questions/34992747/google-calendar-json-api-full-day-events-always-one-day-longer
  		$date_string = $google_event->end->date.' 23:59:59';
      $date_format = 'Y-m-d H:i:s';
			$temp_date = self::os_createFromFormat($date_format, $date_string);
			// move back 1 day to accomodate Google rule that end date is 1 day ahead of actual end date of a full day event
			$temp_date->modify('-1 day');
			return $temp_date;
  	}
  }
}