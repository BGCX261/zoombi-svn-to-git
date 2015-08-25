<?php

/*
 * File: request.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

class ZRequest implements IZSingleton
{

	static public $m_vars = array(
		'get' => null,
		'post' => null,
		'cookie' => null,
		'request' => null,
		'env' => null,
		'server' => null
	);
	/**
	 * ZRequest singleton
	 * @var ZRequest
	 */
	static private $m_instance;

	static public final function accept( $a_type )
	{
		return (stripos($_SERVER['HTTP_ACCEPT'], (string)$a_type) !== false);
	}

	static public final function lang( $a_lang = null )
	{
		if( $a_lang === null )
		{
			$e = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$e = explode(',', $e[0]);
			return $e[1];
		}
		return (stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], (string)$a_lang) !== false);
	}

	static public final function locale( $a_locale = null )
	{
		if( $a_locale === null )
		{
			$e = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$e = explode(',', $e[0]);
			return $e[0];
		}
		return self::lang($a_locale);
	}

	static public final function acceptPrimary()
	{
		$e = explode(';', $_SERVER['HTTP_ACCEPT']);
		$e = explode(',', $e[0]);
		return $e[0];
	}

	static public final function acceptSecondary()
	{
		$e = explode(';', $_SERVER['HTTP_ACCEPT']);
		$e = explode(',', $e[0]);
		if( isset($e[1]) )
			return $e[1];
		return;
	}

	static public final function user()
	{
		return isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
	}

	static public final function password()
	{
		return isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
	}

	static public final function isAuth()
	{
		return isset($_SERVER['PHP_AUTH_USER']);
	}

	static public final function isAny()
	{
		return self::accept('*/*');
	}

	static public final function isAjax()
	{
		return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' );
	}

	static public final function isImage()
	{
		return self::accept('image');
	}

	static public final function isJson()
	{
		return self::accept('json');
	}

	static public final function isHtml()
	{
		return self::accept('html');
	}

	static public final function isXhtml()
	{
		return self::accept('xhtml');
	}

	static public final function isXml()
	{
		return self::accept('xml');
	}

	static public final function isRss()
	{
		return self::accept('rss');
	}

	static public final function isAtom()
	{
		return self::accept('atom');
	}

	static public final function isText()
	{
		return self::accept('text');
	}

	static public final function isApplication()
	{
		return self::accept('application');
	}

	static public final function isCss()
	{
		return self::accept('css');
	}

	static public final function isJs()
	{
		return self::accept('javascript');
	}

	static public final function isJavaScript()
	{
		return self::isJs();
	}

	static public final function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	static public final function isGet()
	{
		return $_SERVER['REQUEST_METHOD'] == 'GET';
	}

	static public final function isPut()
	{
		return $_SERVER['REQUEST_METHOD'] == 'PUT';
	}

	static public final function isDelete()
	{
		return $_SERVER['REQUEST_METHOD'] == 'DELETE';
	}

	private function __construct()
	{

	}

	public function __destruct()
	{
		foreach( self::$m_vars as $k => $v )
		{
			unset(self::$m_vars[$k]);
		}
	}

	private function __clone()
	{

	}

	/**
	 * Get ZRequest instance
	 * @return ZRequest
	 */
	static public final function & getInstance()
	{
		if( self::$m_instance === null )
			self::$m_instance = new self;
		return self::$m_instance;
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

	static private function _count( & $data )
	{
		return $data AND is_array($data) ? count($data) : 0;
	}

	static public final function countPost()
	{
		return self::_count($_POST);
	}

	static public final function countGet()
	{
		return self::_count($_GET);
	}

	static public final function countRequest()
	{
		return self::_count($_REQUEST);
	}

	static public final function countSession()
	{
		return self::_count($_SESSION);
	}

	static public final function countCookie()
	{
		return self::_count($_COOKIE);
	}

	static public final function countFiles()
	{
		return self::_count($_FILES);
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

	static public final function route()
	{
		return Zoombi::getApplication()->getRouter()->getCurrent();
	}

	static public final function module()
	{
		return self::route()->getModule();
	}

	static public final function controller()
	{
		return self::route()->getController();
	}

	static public final function action()
	{
		return self::route()->getAction();
	}

	static public final function & params()
	{
		return self::route()->getParams();
	}

	static public final function & query()
	{
		return self::route()->getQuery();
	}

	static public final function segment( $a_segment )
	{
		return self::route()->getSegment($a_segment);
	}

	static public final function segments()
	{
		return self::route()->getSegments();
	}

}
