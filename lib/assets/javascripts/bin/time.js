function latepoint_is_timeframe_in_periods(timeframe_start, timeframe_end, periods_arr) {
  var is_inside = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;

  for (var i = 0; i < periods_arr.length; i++) {

    var period_start = 0;
    var period_end = 0;
    var buffer_before = 0;
    var buffer_after = 0;

    var period_info = periods_arr[i].split(':');
    if (period_info.length == 2) {
      period_start = period_info[0];
      period_end = period_info[1];
    } else {
      buffer_before = period_info[2];
      buffer_after = period_info[3];
      period_start = parseFloat(period_info[0]) - parseFloat(buffer_before);
      period_end = parseFloat(period_info[1]) + parseFloat(buffer_after);
    }
    if (is_inside) {
      if (latepoint_is_period_inside_another(timeframe_start, timeframe_end, period_start, period_end)) {
        return true;
      }
    } else {
      if (latepoint_is_period_overlapping(timeframe_start, timeframe_end, period_start, period_end)) {
        return true;
      }
    }
  }
  return false;
}

function latepoint_is_period_overlapping(period_one_start, period_one_end, period_two_start, period_two_end) {
  // https://stackoverflow.com/questions/325933/determine-whether-two-date-ranges-overlap/
  return period_one_start < period_two_end && period_two_start < period_one_end;
}

function latepoint_is_period_inside_another(period_one_start, period_one_end, period_two_start, period_two_end) {
  return period_one_start >= period_two_start && period_one_end <= period_two_end;
}


// Converts time in minutes to hours if possible, if minutes also exists - shows minutes too
function latepoint_minutes_to_hours_preferably(time) {
  var army_clock = latepoint_is_army_clock();

  var hours = Math.floor(time / 60);
  if (!army_clock && hours > 12) hours = hours - 12;

  var minutes = time % 60;
  if (minutes > 0) hours = hours + ':' + minutes;
  return hours;
}


function latepoint_minutes_to_hours(time) {
  var army_clock = latepoint_is_army_clock();

  var hours = Math.floor(time / 60);
  if (!army_clock && hours > 12) hours = hours - 12;
  return hours;
}


function latepoint_am_or_pm(minutes) {
  if (latepoint_is_army_clock()) return '';
  return (minutes < 720 || minutes == 1440) ? 'am' : 'pm';
}

function latepoint_hours_and_minutes_to_minutes(hours_and_minutes, ampm) {
  var hours_and_minutes_arr = hours_and_minutes.split(':');
  var hours = hours_and_minutes_arr[0];
  var minutes = hours_and_minutes_arr[1];
  if (ampm == "pm" && hours < 12) hours = parseInt(hours) + 12;
  if (ampm == "am" && hours == 12) hours = 0;
  minutes = parseInt(minutes) + (hours * 60);
  return minutes;
}

function latepoint_get_time_system() {
  return latepoint_helper.time_system;
}

function latepoint_is_army_clock() {
  return (latepoint_get_time_system() == '24');
}

function latepoint_minutes_to_hours_and_minutes(minutes) {
  var army_clock = latepoint_is_army_clock();
  var format = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '%02d:%02d';

  var hours = Math.floor(minutes / 60);
  if (!army_clock && (hours > 12)) hours = hours - 12;
  if (!army_clock && hours == 0) hours = 12;
  minutes = minutes % 60;
  return sprintf(format, hours, minutes);
}
