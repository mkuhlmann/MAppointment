<?php


function app() : \App\Application {
	return \App\Application::getInstance();
}

function db() : \ParagonIE\EasyDB\EasyDB {
	return app()->get(\ParagonIE\EasyDB\EasyDB::class);
}

function dbdate($timestamp = null) : string {
	if($timestamp != null) {
		return gmdate('Y-m-d H:i:s', $timestamp);
	}
	return gmdate('Y-m-d H:i:s');
}

function nanoid($size = 21) : string {
	$alphabet = '-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
	$nanoid = '';
	for ($i = 0; $i < $size; $i++) {
		$nanoid .= substr($alphabet, mt_rand(0, strlen($alphabet)), 1);
	}
	return $nanoid;
}

function tzdate(string $format, string|int $timestamp = null, string $timezone = 'UTC'): string {
	if(!is_numeric($timestamp)) {
		$timestamp = strtotime($timestamp);
	}
	date_default_timezone_set($timezone);
	$time = date($format, $timestamp ?: time());
	date_default_timezone_set('UTC');
	return $time;
}

/**
 * Get all of the given array except for a specified array of keys.
 *
 * @param  array  $array
 * @param  array|string  $keys
 *
 * @return array
 */
function array_except(array $array, array$keys): array
{
	foreach($keys as $key) {
		unset($array[$key]);
	}
	return $array;
}