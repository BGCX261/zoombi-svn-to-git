<?php

/*
 * File: querystring.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * Query string
 * @author Zombie!
 */
class ZQueryString
{

	/**
	 * Get value from QUERY_STRING by key name
	 * @param string $a_key
	 * @param bool $a_case
	 * @return mixed
	 */
	public static function get( $a_key = null, $a_case = false )
	{
		$q = & $_SERVER['QUERY_STRING'];

		if(count($q) < 1)
			return;

		$parsed = array();

		$parts = explode('&', $q);

		foreach($parts as $part)
		{
			$exp = null;

			if($a_case)
			{
				if(strpos($a_key, $part) == 0)
				{
					$exp = explode('=', $part, 1);
				}
			}
			else
			{
				if(stripos($a_key, $part) == 0)
				{
					$exp = explode('=', $part, 1);
				}
			}

			if($exp AND is_array($exp) AND count($exp) > 1 AND isset($exp[1]))
				return $exp[1];
		}
	}

	/**
	 * Poin all properties to Get method
	 * @param string $a_name
	 * @return mixed
	 */
	public function __get( $a_name )
	{
		return self::get($a_name);
	}

}
