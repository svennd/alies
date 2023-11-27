<?php

function get_online_status(int $last_login_timestamp)
{
	$now = time();
	$last_login = $now - $last_login_timestamp;

	# last login 3 hours ago
	if ($last_login < 3*60*60)
	{
		return 'online';
	}
	elseif ($last_login < 7*24*60*60)
	{
		return 'away';
	}
	else
	{
		return 'offline';
	}
}