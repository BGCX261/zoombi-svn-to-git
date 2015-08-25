<?php

/*
 * File: request.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


/**
 * Request class to serve requests
 *  
 * @property Zoombi_Registery $get
 * @property Zoombi_Registery $post
 * @property Zoombi_Registery $request
 * @property Zoombi_Registery $cookie
 * @property Zoombi_Registery $session
 * @property Zoombi_Input $input
 */
class Zoombi_Request implements Zoombi_Singleton
{

	/**
	 * Zoombi_Request singleton
	 * @var Zoombi_Request
	 */
	static private $m_instance;

	/**
	 * A Post variable of Zoombi_Input->post
	 * @var Zoombi_Registery 
	 */
	public $post;

	/**
	 * A Get variable of Zoombi_Input->get
	 * @var Zoombi_Registery 
	 */
	public $get;

	/**
	 * A Request variable of Zoombi_Input->request
	 * @var Zoombi_Registery 
	 */
	public $request;

	/**
	 * A Cookie variable of Zoombi_Input->cookie
	 * @var Zoombi_Registery 
	 */
	public $cookie;

	/**
	 * A Session variable of Zoombi_Input->session
	 * @var Zoombi_Registery 
	 */
	public $session;

	/**
	 * Primary application routed path
	 * @var Zoombi_RoutePath 
	 */
	private $m_exec_path;

	/**
	 * All another routed path
	 * @var Zoombi_RoutePath 
	 */
	private $m_route_path;

	/**
	 * Set application primary route paths
	 * @param Zoombi_RoutePath $a_path
	 * @return Zoombi_Request
	 */
	public function & setExecPath( Zoombi_RoutePath & $a_path )
	{
		if($this->m_exec_path !== null)
			return $this;

		$this->m_exec_path = $a_path;
		return $this;
	}

	/**
	 * Get application primary route path
	 * @return Zoombi_RoutePath 
	 */
	public function & getExecPath()
	{
		return $this->m_exec_path;
	}

	/**
	 * Set application another route paths
	 * @param Zoombi_RoutePath $a_path
	 * @return Zoombi_Request
	 */
	public function & setRoutePath( Zoombi_RoutePath & $a_path )
	{
		$this->m_route_path = $a_path;
		return $this;
	}

	/**
	 * Get application another route path
	 * @return Zoombi_RoutePath 
	 */
	public function & getRoutePath()
	{
		return $this->m_route_path;
	}

	/**
	 * Get request content type header
	 * @return string
	 */
	static public final function getContentType()
	{
		return self::getHeader('Content-Type');
	}

	/**
	 * Get request content length header
	 * @return string
	 */
	static public final function getContentLenght()
	{
		return self::getHeader('Content-Length');
	}

	/**
	 * Get request orgin header
	 * @return string
	 */
	static public final function getOrgin()
	{
		return self::getHeader('Orgin');
	}
	
	static public final function getHeaders()
	{
		$headers = array();
		foreach ($_SERVER as $name => $value)
		{
			if ( strpos($name, 'HTTP_') === 0 )
			{
				$name = str_replace(array('HTTP_', '_'), array('', '-'), $name);
				$headers[$name] = $value;
			} else if ($name == "CONTENT_TYPE") {
				$headers["Content-Type"] = $value;
			} else if ($name == "CONTENT_LENGTH") {
				$headers["Content-Length"] = $value;	
			} 
       }
	   return $headers;
	}

	/**
	 * Get request heade by name
	 * @param string $a_header A header name to get
	 * @return string 
	 */
	static public final function getHeader( $a_header )
	{
	   foreach( self::getHeaders() as $k => $v)
			if($k == $a_header)
				return $v;   
	}

	/**
	 * Get request accept string
	 * @return string
	 */
	public function getAccept()
	{
		return $_SERVER['HTTP_ACCEPT'];
	}

	/**
	 * Fing given type in request
	 * @param string $a_type A type to find
	 * @return string
	 */
	public function accept( $a_type = null )
	{
		if(func_num_args() == 0)
			return $this->getAccept();

		return stripos($this->getAccept(), (string)$a_type) !== false;
	}

	/**
	 * Find language in request
	 * @param string $a_land Lanf to find
	 * @return string
	 */
	public function lang( $a_lang = null )
	{
		if($a_lang === null)
		{
			$e = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$e = explode(',', $e[0]);
			return $e[1];
		}
		return stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], (string)$a_lang) !== false;
	}

	/**
	 * Find locale in request
	 * @param string $a_local Locale to find
	 * @return string
	 */
	public function locale( $a_locale = null )
	{
		if($a_locale === null)
		{
			$e = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$e = explode(',', $e[0]);
			return $e[0];
		}
		return $this->lang($a_locale);
	}

	/**
	 * Get primary accept value
	 * @return string
	 */
	public function acceptPrimary()
	{
		$e = explode(';', $_SERVER['HTTP_ACCEPT']);
		$e = explode(',', $e[0]);
		return $e[0];
	}

	/**
	 * Get secondary accept value
	 * @return string
	 */
	public function acceptSecondary()
	{
		$e = explode(';', $_SERVER['HTTP_ACCEPT']);
		$e = explode(',', $e[0]);
		if(isset($e[1]))
			return $e[1];
		return;
	}

	/**
	 * Get authentication user
	 * @return string
	 */
	public function user()
	{
		return isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
	}

	/**
	 * Get authentication password
	 * @return string
	 */
	public function password()
	{
		return isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
	}

	/**
	 * Is authentication
	 * @return bool
	 */
	public function isAuth()
	{
		return isset($_SERVER['PHP_AUTH_USER']);
	}

	/**
	 * Is accept any
	 * @return bool
	 */
	public function isAny()
	{
		return $this->accept('*/*');
	}

	/**
	 * Is ajax
	 * @return bool 
	 */
	public function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	/**
	 * Is image
	 * @return bool 
	 */
	public function isImage()
	{
		return $this->accept('image');
	}

	/**
	 * Is json
	 * @return bool 
	 */
	public function isJson()
	{
		return $this->accept('json');
	}

	/**
	 * Is html
	 * @return bool 
	 */
	public function isHtml()
	{
		return $this->accept('html');
	}

	/**
	 * Is xhtml
	 * @return bool 
	 */
	public function isXhtml()
	{
		return $this->accept('xhtml');
	}

	/**
	 * Is xml
	 * @return bool 
	 */
	public function isXml()
	{
		return $this->accept('xml');
	}

	/**
	 * Is rss
	 * @return bool 
	 */
	public function isRss()
	{
		return $this->accept('rss');
	}

	/**
	 * Is atom
	 * @return bool 
	 */
	public function isAtom()
	{
		return $this->accept('atom');
	}

	/**
	 * Is text
	 * @return bool 
	 */
	public function isText()
	{
		return $this->accept('text');
	}

	/**
	 * Is application
	 * @return bool 
	 */
	public function isApplication()
	{
		return $this->accept('application');
	}

	/**
	 * Is css
	 * @return bool 
	 */
	public function isCss()
	{
		return $this->accept('css');
	}

	/**
	 * Is javascript
	 * @return bool 
	 */
	public function isJs()
	{
		return $this->accept('javascript');
	}

	/**
	 * Is javascript
	 * @return bool 
	 */
	public function isJavaScript()
	{
		return $this->isJs();
	}

	/**
	 * Is post method
	 * @return bool 
	 */
	public function isPost()
	{
		return $this->getMethod() == 'post';
	}

	/**
	 * Is get method
	 * @return bool 
	 */
	public function isGet()
	{
		return $this->getMethod() == 'get';
	}

	/**
	 * Is put method
	 * @return bool 
	 */
	public function isPut()
	{
		return $this->getMethod() == 'put';
	}

	/**
	 * Is delete method
	 * @return bool 
	 */
	public function isDelete()
	{
		return $this->getMethod() == 'delete';
	}

	/**
	 * Get request method
	 * @return string 
	 */
	public function getMethod()
	{
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

	private function __construct()
	{
		$i = Zoombi_Input::getInstance();
		$this->post = $i->post;
		$this->get = $i->get;
		$this->request = $i->request;
		$this->cookie = $i->cookie;
		$this->session = $i->session;
	}

	public function __destruct()
	{
		
	}

	private function __clone()
	{
		
	}

	public function __isset( $a_name )
	{
		$r = strtolower(trim((string)$a_name));
		switch($r)
		{
			case 'input':
			case 'get':
			case 'post':
			case 'request':
			case 'cookie':
			case 'session':
				return true;

			default:
				break;
		}
	}

	public function & __get( $a_name )
	{
		$r = strtolower(trim((string)$a_name));
		$ret = null;
		switch($r)
		{
			case 'input':
				$ret = Zoombi_Input::getInstance();
				break;

			case 'get':
			case 'post':
			case 'request':
			case 'cookie':
			case 'session':
				$ret = Zoombi_Input::getInstance()->$r;
				break;
		}
		return $ret;
	}

	/**
	 * Get Zoombi_Registery instance
	 * @return Zoombi_Request
	 */
	static public final function & getInstance()
	{
		if(self::$m_instance === null)
			self::$m_instance = new self;
		return self::$m_instance;
	}

	static public final function getPostBody()
	{
		/*global $HTTP_RAW_POST_DATA;
		$vp = ini_get('always_populate_raw_post_data');
		if( $vp )
			return $HTTP_RAW_POST_DATA;
		else*/
			return file_get_contents('php://input');
	}

	/**
	 * Get get array
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return Zoombi_Registery|mixed
	 */
	public function get( $a_key = null, $a_value = null )
	{
		if(func_num_args() == 0)
			return $this->get;

		if(func_num_args() == 1)
			return $this->get->get($a_key, $a_value);

		if(func_num_args() > 1)
			return $this->get->set($a_key, $a_value);
	}

	/**
	 * Get get array
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return Zoombi_Registery|mixed
	 */
	public function getGet( $a_key, $a_filter = null )
	{
		if(func_num_args() > 1)
		{
			if(function_exists('filter_var'))
				return filter_var($this->get->get($a_key), $a_filter);
		}
		return $this->get->get($a_key);
	}

	/**
	 * Set get array value
	 * @param string $a_key Key to set
	 * @param mixed $a_value Value to set
	 */
	public function setGet( $a_key, $a_value )
	{
		$this->get->$key = $a_value;
	}

	/**
	 * Has key in get array
	 * @param string $a_key
	 * @return bool 
	 */
	public function hasGet( $a_key )
	{
		return $this->get->has($a_key);
	}

	/**
	 * Get post array
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return Zoombi_Registery|mixed
	 */
	public function post( $a_key = null, $a_value = null, $a_filter = null )
	{
		if(func_num_args() == 0)
			return $this->post;

		if(func_num_args() == 1)
			return $this->post->get($a_key);

		if(func_num_args() > 1)
			return $this->post->set($a_key, $a_value);
	}

	/**
	 * Get post array
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return Zoombi_Registery|mixed
	 */
	public function getPost( $a_key, $a_filter = null )
	{
		if(func_num_args() > 1)
		{
			if(function_exists('filter_var'))
				return filter_var($this->post->get($a_key), $a_filter);
		}
		return $this->post->get($a_key);
	}

	/**
	 * Set post array value
	 * @param string $a_key Key to set
	 * @param mixed $a_value Value to set
	 */
	public function setPost( $a_key, $a_value )
	{
		return $this->post->set($a_key, $a_value);
	}

	/**
	 * Has key in post array
	 * @param string $a_key
	 * @return bool 
	 */
	public function hasPost( $a_key )
	{
		return $this->post->has($a_key);
	}

	/**
	 * Get count values of get array
	 * @return string 
	 */
	public function countGet()
	{
		return $this->get->count();
	}

	/**
	 * Get count values of post array
	 * @return string 
	 */
	public function countPost()
	{
		return $this->post->count();
	}

	/**
	 * Get count values of request array
	 * @return string 
	 */
	public function countRequest()
	{
		return $this->request->count();
	}

	/**
	 * Get count values of session array
	 * @return string 
	 */
	public function countSession()
	{
		return $this->session->count();
	}

	/**
	 * Get count values of cookie array
	 * @return string 
	 */
	public function countCookie()
	{
		return $this->cookie->count();
	}

	/**
	 * Get count values of files array
	 * @return string 
	 */
	public function countFiles()
	{
		return $this->files->count();
	}

	/**
	 * Get request array
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return Zoombi_Registery|mixed
	 */
	public function request( $a_key = null, $a_value = null )
	{
		if(func_num_args() == 0)
			return $this->request;

		if(func_num_args() == 1)
			return $this->request->get($a_key);

		if(func_num_args() > 1)
			return $this->request->set($a_key, $a_value);
	}

	/**
	 * Get request array value by key name and optional filter his
	 * @param string $a_key Key name
	 * @param mixed $a_filter Filter
	 * @return Zoombi_Registery|mixed
	 */
	public function getRequest( $a_key, $a_filter = null )
	{
		if(func_num_args() > 1)
		{
			if(function_exists('filter_var'))
				return filter_var($this->request->get($a_key), $a_filter);
		}

		return $this->request->get($a_key);
	}

	/**
	 * Set request array value
	 * @param string $a_key Key to set
	 * @param mixed $a_value Value to set
	 */
	public function setRequest( $a_key, $a_value )
	{
		return $this->post->set($a_key, $a_value);
	}

	/**
	 * Has key in request array
	 * @param string $a_key
	 * @return bool 
	 */
	public function hasRequest( $a_key )
	{
		return $this->request->has($a_key);
	}

	/**
	 * Get or set cookie variable by key name
	 * @param string $a_key Key name
	 * @param string $a_value Value to set
	 * @return Zoombi_Registery|mixed
	 */
	public function cookie( $a_key = null, $a_value = null )
	{
		if(func_num_args() == 0)
			return $this->cookie;

		if(func_num_args() == 1)
			return $this->cookie->get($a_key);

		if(func_num_args() > 1)
			return $this->cookie->set($a_key, $a_value);
	}

	/**
	 * Get cookie variable by key name
	 * @param string $a_key Key name
	 * @return Zoombi_Registery|mixed
	 */
	public function getCookie( $a_key )
	{
		return $this->cookie->get($a_key);
	}

	/**
	 * Set cookie variable by key name
	 * @param string $a_key Key name
	 * @param string $a_value Value to set
	 * @return Zoombi_Registery|mixed
	 */
	public function setCookie( $a_key, $a_value )
	{
		return $this->cookie->set($a_key, $a_value);
	}

	/**
	 * Has cookie value by ket name
	 * @param string $a_key Key name
	 * @return bool
	 */
	public function hasCookie( $a_key )
	{
		return $this->cookie->has($a_key);
	}

	/**
	 * Get current route
	 * @return Zoombi_Route
	 */
	public function route()
	{
		return Zoombi::getApplication()->getRouter()->getCurrent();
	}

	/**
	 * Get current route module name
	 * @return Zoombi_Module
	 */
	public function module()
	{
		return self::route()->getModule();
	}

	/**
	 * Get current route controller name
	 * @return string
	 */
	public function controller()
	{
		return self::route()->getController();
	}

	/**
	 * Get current route action name
	 * @return string
	 */
	public function action()
	{
		return self::route()->getAction();
	}

	/**
	 * Get current route params
	 * @return string
	 */
	public function params()
	{
		return self::route()->getParams();
	}

	/**
	 * Get currnet route query string variables
	 * @return Zoombi_Registery 
	 */
	public function & query()
	{
		return self::route()->getQuery();
	}

	/**
	 * Get segment by name of current route
	 * @param type $a_segment Segment name
	 * @return string 
	 */
	public function segment( $a_segment )
	{
		return self::route()->getSegment($a_segment);
	}

	/**
	 * Get segments of current route 
	 * @return array
	 */
	public function segments()
	{
		return self::route()->getSegments();
	}

}
