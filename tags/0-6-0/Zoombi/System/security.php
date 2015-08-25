<?php

/*
 * File: security.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

class ZSecureValue
{

	static public function int( & $a_value )
	{
		return self::secure_int($a_value);
	}

	static public function string( & $a_value )
	{
		return self::secure_string($a_value);
	}

	static public function secure_int( & $a_value )
	{
		return intval($a_value);
	}

	static public function secure_string( & $a_value )
	{
		return stripcslashes(trim($a_value));
	}

}
