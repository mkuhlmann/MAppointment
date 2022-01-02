<?php

namespace App;

class Helper
{
	public static function nanoid($size = 21): string
	{
		$alphabet = '-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
		$nanoid = '';
		for ($i = 0; $i < $size; $i++) {
			$nanoid .= substr($alphabet, mt_rand(0, strlen($alphabet)), 1);
		}
		return $nanoid;
	}
}
