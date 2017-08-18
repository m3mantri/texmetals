<?php
/**
 * includes/library/TIME.php
 * 
 * Used for time zone calculations
 */

/**
 * Takes in a time stamp (SQL format), current time zone, and target time zone
 */
function time_travel($timestamp, $timezone_to, $timezone_from = null)
{
//if ($_SERVER['REMOTE_ADDR'] == '99.8.180.53'){ header('Content-type: text/plain'); echo "$timestamp $timezone_to, $timezone_from";die; }
	if (!is_int($timestamp))
		$timestamp = strtotime($timestamp);
	if (empty($timezone_from))
		$timezone_from = date('T');
	$offset = get_timezone_offset($timezone_to, $timezone_from);
	return date('Y-m-d H:i:s', $timestamp + $offset);
	
}


/**    Returns the offset from the origin timezone to the remote timezone, in seconds.
*    @param $remote_tz;
*    @param $origin_tz; If null the servers current timezone is used as the origin.
*    @return int;
*/
function get_timezone_offset($remote_tz, $origin_tz = null) {
    if($origin_tz === null) {
        if(!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}

function brief_time($time)
{
	$time = date('Y-m-d ') . $time;
	$time = strtotime($time);
	if (date('i', $time) == 0)
	{
		if (date('G', $time) == '0')
			return 'midnight';
		else if (date('G', $time) == '12')
			return 'noon';
		else
			return date('ga', $time);
	}
	else
	{
		return date('g:i a');
	}
}

/**
* returns a 0 if markets are closed at the given time or a 1 if they're open
*/
function market_status($time)
{
	return 1;
	if (!is_int($time))
		$time = strtotime($time);
	
	//TODO: holiday calculations
	
	$dow = date('N', $time);
	$hour = date('G', $time);
	if ($dow >= 1 || $dow <= 5)
	{
		if ($hour >= 20)
		{
			return false; // closed after 8pm mon-fri
		}
	}
	else if ($dow == 6)
	{
		return false; // closed all day saturday
	}
	else if ($hour < 19) // closed before 7pm on Sunday
	{
		return false;
	}
	
	return true;
}

?>