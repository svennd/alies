<?php

/*
  wrapper written do deal with null datetimes in php 8
  format given in user : $user->user_date
*/
function user_format_date(string $datetime = null, string $format)
{
  return (!$datetime || $datetime == "0000-00-00") ? '---' : date_format(date_create($datetime), $format);
}


/*
  say time as x ... ago
*/
function time_ago(string $date = null)
{
  if ($date == null) { return "never"; }

  $current_timestamp = time();
  $input_timestamp = strtotime($date);

  if ($current_timestamp > $input_timestamp){ return timespan($input_timestamp, $current_timestamp, 1) . " Ago"; }

  return "bad time";
}
