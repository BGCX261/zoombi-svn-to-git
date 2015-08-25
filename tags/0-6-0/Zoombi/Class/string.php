<?php

/*
 * File: string.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

class ZString
{

	static public function trim( $a_string )
	{
		return trim($a_string);
	}

	static public function slug( $a_string )
	{
		return preg_replace('/[^A-Za-z0-9-]+/', '-', (string)$a_string);
	}

	static public function camelize( $a_string )
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $a_string)));
	}

	static public function underscore( $a_string )
	{
		return mb_strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $a_string), 'utf-8');
	}

	static public function humanize( $a_string )
	{
		return ucwords(str_replace('_', ' ', $a_string));
	}

	static function truncate( $a_string, $a_limit, $a_pad='...' )
	{
		if(mb_strlen($a_string, 'utf-8') <= $a_limit)
			return $a_string;

		return mb_substr($a_string, 0, $a_limit, 'utf-8') . $a_pad;
	}

}
