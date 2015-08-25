<?php

/*
 * File: request.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

class ZRequest
{

	static public $m_vars = array(
		'get' => null,
		'post' => null,
		'cookie' => null,
		'request' => null,
		'env' => null,
		'server' => null
	);

	static public final function is_ajax()
	{
		return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' );
	}

	static public final function get( $a_key = null, $a_value = null, $a_filter = null )
	{
		if( $a_key === null )
		{
			$v = & self::$m_vars['get'];
			if( $v === null )
			{
				$v = new ZRegistry;
				$v->setDataRef($_GET);
			}
			return $v;
		}

		if( $a_value !== null )
			self::setGet($a_key, $a_value);

		return self::getGet($a_key, $a_filter);
	}

	static public final function getGet( $a_key, $a_filter = null )
	{
		$var = array_key_exists($a_key, $_GET) ? $_GET[$a_key] : null;
		if( $a_filter && function_exists('filter_var') )
			$var = filter_var($var, $a_filter);
		return $var;
	}

	static public final function setGet( $a_key, $a_value )
	{
		$_GET[$a_key] = strval($a_value);
	}

	static public final function hasGet( $a_key )
	{
		if( function_exists('filter_has_var') )
			return filter_has_var(INPUT_GET, $a_key);

		return array_key_exists($a_key, $_GET);
	}

	static public final function post( $a_key = null, $a_value = null, $a_filter = null )
	{
		if( $a_key === null )
		{
			$v = & self::$m_vars['post'];
			if( $v === null )
			{
				$v = new ZRegistry;
				$v->setDataRef($_POST);
			}
			return $v;
		}

		if( $a_value !== null )
			self::setPost($a_key, $a_value);

		return self::getPost($a_key, $a_filter);
	}

	static public final function getPost( $a_key, $a_filter = null )
	{
		$var = array_key_exists($a_key, $_POST) ? $_POST[$a_key] : null;
		if( $a_filter && function_exists('filter_var') )
			$var = filter_var($var, $a_filter);
		return $var;
	}

	static public final function setPost( $a_key, $a_value )
	{
		$_POST[$a_key] = $a_value;
	}

	static public final function hasPost( $a_key )
	{
		if( function_exists('filter_has_var') )
			return filter_has_var(INPUT_POST, $a_key);

		return array_key_exists($a_key, $_POST);
	}

	static public final function request( $a_key = null, $a_value = null, $a_filter = null )
	{
		if( $a_key === null )
		{
			$v = & self::$m_vars['request'];
			if( $v === null )
			{
				$v = new ZRegistry;
				$v->setDataRef($_REQUEST);
			}
			return $v;
		}

		if( $a_value !== null )
			self::setRequest($a_key, $a_value);

		return self::getRequest($a_key, $a_filter);
	}

	static public final function getRequest( $a_key, $a_filter = null )
	{
		$var = array_key_exists($a_key, $_REQUEST) ? $_REQUEST[$a_key] : null;
		if( $a_filter && function_exists('filter_var') )
			$var = filter_var($var, $a_filter);
		return $var;
	}

	static public final function setRequest( $a_key, $a_value )
	{
		$_REQUEST[$a_key] = $a_value;
	}

	static public final function hasRequest( $a_key )
	{
		if( function_exists('filter_has_var') )
			return filter_has_var(INPUT_REQUEST, $a_key);

		return array_key_exists($a_key, $_REQUEST);
	}

	static public final function cookie( $a_key = null, $a_value = null, $a_filter = FILTER_DEFAULT )
	{
		if( $a_key === null )
		{
			$v = & self::$m_vars['cookie'];
			if( $v === null )
			{
				$v = new ZRegistry;
				$v->setDataRef($_COOKIE);
			}
			return $v;
		}

		if( $a_value === null )
		{
			if( function_exists('filter_input') )
				return filter_input(INPUT_COOKIE, $a_key, $a_filter);

			return isset($_COOKIE[$a_key]) ? $_COOKIE[$a_key] : null;
		}
		$_COOKIE[$a_key] = $a_value;
	}

	static public final function getCookie( $a_key )
	{
		return self::cookie($a_key);
	}

	static public final function setCookie( $a_key, $a_value )
	{
		return self::cookie($a_key, $a_value);
	}

	static public final function hasCookie( $a_key )
	{
		if( function_exists('filter_has_var') )
			return filter_has_var(INPUT_COOKIE, $a_key);

		return array_key_exists($a_key, $_COOKIE);
	}

}
