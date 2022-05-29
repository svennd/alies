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
  return ($date == null) ? "never" : timespan(strtotime($date), time(), 1) . " Ago";
}
