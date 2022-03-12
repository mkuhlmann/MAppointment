<?php

use App\Helper;

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
	return Helper::nanoid($size);
}

function tzdate($format, $timestamp = null, $timezone = 'UTC') {
	date_default_timezone_set($timezone);
	$time = date($format, $timestamp ?: time());
	date_default_timezone_set('UTC');
	return $time;
}