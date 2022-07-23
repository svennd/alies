<?php

/*
  wrapper written do deal with null datetimes in php 8
  format given in user : $user->user_date
*/
function user_format_date(String $datetime = null, String $format)
{
  return (!$datetime) ? 'none' : date_format(date_create($datetime), $format);
}


/*
  say time as x ... ago
*/
function time_ago(String $date = null)
{
  if ($date == null) 
  {
    return "never";
  }

  $current_timestamp = time();
  $input_timestamp = strtotime($date);

  if ($current_timestamp > $input_timestamp)
  {
    return timespan($input_timestamp, $current_timestamp, 1) . " Ago";
  }

  return "In " . timespan($current_timestamp, $input_timestamp, 1);
}
