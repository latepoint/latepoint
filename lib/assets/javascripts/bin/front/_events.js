/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

function latepoint_reload_day_schedule($day_view){
  $day_view.addClass('os-loading');
  let data = {
    action: latepoint_helper.route_action,
    route_name: $day_view.data('route-name'),
    params: $day_view.find("select, textarea, input").serialize(),
    layout: 'none',
    return_format: 'json'
  }

  jQuery.ajax({
    type: "post",
    dataType: "json",
    url: latepoint_timestamped_ajaxurl(),
    data: data,
    success: function (data) {
      if (data.status === "success") {
        $day_view.replaceWith(data.message);
      }
    }
  });
}

function latepoint_reload_events_calendar($events_calendar){
  $events_calendar.addClass('os-loading');
  let data = {
    action: latepoint_helper.route_action,
    route_name: $events_calendar.data('route-name'),
    params: $events_calendar.find("select, textarea, input").serialize(),
    layout: 'none',
    return_format: 'json'
  }

  jQuery.ajax({
    type: "post",
    dataType: "json",
    url: latepoint_timestamped_ajaxurl(),
    data: data,
    success: function (data) {
      if (data.status === "success") {
        $events_calendar.replaceWith(data.message);
      }
    }
  });

}