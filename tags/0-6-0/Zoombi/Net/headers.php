<?php

/*
 * File: headers.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * Http Headers
 */
class ZHeaders
{

	static private $m_code;
	static private $m_message;

	static public final function hasHeader( $a_name )
	{
		foreach(headers_list() as $header)
		{
			if(stripos((string)$a_name . ':', $header) !== false)
				return $header;
		}
	}

	static public final function sent()
	{
		return headers_sent();
	}

	static public final function isSent()
	{
		return headers_sent();
	}

	static public final function getCode()
	{
		return self::$m_code;
	}

	static public final function setCode( $a_key, $a_message = 'OK' )
	{
		self::$m_code = $a_key;
		self::$m_message = $a_message;
	}

	static public final function setHeader( $a_key, $a_value )
	{
		header($a_key . ':' . $a_value, true, self::$m_code);
	}

	static public final function getHeader( $a_name )
	{
		$h = self::hasHeader($a_name);
		return $h ? new ZHeader($h) : null;
	}

	static public final function removeHeader( $a_name = null )
	{
		if($a_name !== null AND function_exists('header_remove'))
			header_remove($a_name);
	}

	static public final function removeAll()
	{
		if(function_exists('header_remove'))
			header_remove();
	}

}
