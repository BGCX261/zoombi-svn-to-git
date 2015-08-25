<?php

/*
 * File: route.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * Route class
 * 
 * @author Zombie
 *
 * @property string module
 * @property string controller
 * @property string action
 * @property array params
 */
class ZRoute
{

	/**
	 * Route data
	 * @var ZRegistry
	 */
	private $m_path;
	/**
	 * Route data
	 * @var ZRegistry
	 */
	private $m_param;
	/**
	 * Route data
	 * @var ZRegistry
	 */
	private $m_query;
	/**
	 * Default URL mode
	 * @var int 
	 */
	private $m_mode;

	/*
	 * Route mode constans
	 */
	const MODE_HUMAN_READABLE_URL = 0;
	const MODE_QUERY_STRING = 1;
	const MODE_MIXED = 2;

	/**
	 * Constructor
	 * @param string|ZRoute $a_params
	 */
	public function __construct( $a_url, $a_mode = self::MODE_HUMAN_READABLE_URL )
	{
		$this->m_path = new ZRegistry;
		$this->m_query = new ZRegistry;
		$this->m_param = new ZRegistry;

		$this->setMode($a_mode);
		switch( gettype($a_url) )
		{
			case 'string':
				$this->fromString($a_url);
				break;

			case 'object':
				$this->fromObject($a_url);
				break;
		}
	}

	/**
	 * Make route from object
	 * @param ZRoute $a_route
	 * @return ZRoute
	 */
	public function & fromObject( $a_route )
	{
		$this->clear();
		if( $a_route instanceof ZRoute )
		{
			$this->m_path = clone $a_route->getPath();
			$this->m_param = clone $a_route->getParams();
			$this->m_query = clone $a_route->getQuery();
			$this->m_mode = $a_route->getMode();
		}
		return $this;
	}

	/**
	 * Make route from string
	 * @param string $a_url
	 * @return ZRoute
	 */
	public function & fromString( $a_url )
	{
		$this->clear();
		$req_arr = explode('?', (string)$a_url, 2);
		$this->setSegments(explode(Zoombi::US, array_shift($req_arr)));
		parse_str(array_shift($req_arr), $var);
		$this->m_query->setData($var);

		/*
		  while( count($exp) > 0 )
		  $this->m_params->set(array_shift($exp), array_shift($exp));

		  if( count($req_arr) )
		  {
		  $parsed = array( );
		  parse_str(array_shift($req_arr), $parsed);
		  if( isset($parsed['module']) )
		  {
		  $this->m_module = $parsed['module'];
		  unset($parsed['module']);
		  }

		  if( isset($parsed['controller']) )
		  {
		  $this->m_controller = $parsed['controller'];
		  unset($parsed['controller']);
		  }

		  if( isset($parsed['action']) )
		  {
		  $this->m_action = $parsed['action'];
		  unset($parsed['action']);
		  }

		  foreach( $parsed as $k => $v )
		  $this->m_params->set((string)$k, (string)$v);
		  } */
		return $this;
	}

	/**
	 * Get route Query params
	 * @return ZRegistry
	 */
	public function & getQuery()
	{
		return $this->m_query;
	}

	/**
	 * Get route params
	 * @return ZRegistry
	 */
	public function & getParams()
	{
		return $this->m_param;
	}

	/**
	 * Get route param by key
	 * @param int|string $a_key A Key
	 * @param mixed $a_default Default value if $a_key is not exist
	 * @return string
	 */
	public function getParam( $a_key, $a_default = null )
	{
		return $this->m_params->get(intval($a_key), $a_default);
	}

	/**
	 * Get route pathway
	 * @return ZRegistry
	 */
	public function & getPath()
	{
		return $this->m_path;
	}

	function push( $a_value )
	{
		return $this->push_end($a_value);
	}

	function pop()
	{
		return $this->pop_end();
	}

	function shift()
	{
		return $this->pop_start();
	}

	function unshift( $a_value )
	{
		return $this->push_start($a_value);
	}

	/**
	 * Prepend segment to start of route
	 * @param string $a_value
	 * @param mixed $_ [optional]
	 * @return ZRoute
	 */
	public function & push_start( $a_value )
	{
		$s = $this->getSegments();
		foreach( func_get_args( ) as $a )
			array_unshift($s, $a);
		return $this->setSegments($s);
	}

	/**
	 * Append segment to end of route
	 * @param string $a_value
	 * @param mixed $_ [optional]
	 * @return ZRoute
	 */
	public function & push_end( $a_value )
	{
		$s = $this->getSegments();
		foreach( func_get_args( ) as $a )
			array_push($s, $a);
		return $this->setSegments($s);
	}

	/**
	 * Pop segment from start
	 * @return ZRoute
	 */
	public function & pop_start()
	{
		$s = $this->getSegments();
		array_shift($s);
		return $this->setSegments($s);
	}

	/**
	 * Pop segment from end
	 * @return ZRoute
	 */
	public function & pop_end()
	{
		$s = $this->getSegments();
		array_pop($s);
		return $this->setSegments($s);
	}

	/**
	 * Clear route
	 * @return ZRoute
	 */
	public function & clear()
	{
		$this->m_path->clear();
		$this->m_param->clear();
		$this->m_query->clear();
		return $this;
	}

	/**
	 * Cloning
	 */
	public function __clone()
	{
		$this->m_path = clone $this->getPath();
		$this->m_query = clone $this->getQuery();
		$this->m_param = clone $this->getParams();
	}

	/**
	 * Route destructor
	 */
	public function __destruct()
	{
		unset($this->m_param, $this->m_path, $this->m_query);
	}

	/**
	 * Set url generation mode
	 * @param string $a_mode
	 * @return ZRoute
	 */
	public function & setMode( $a_mode )
	{
		switch( $a_mode )
		{
			case self::MODE_HUMAN_READABLE_URL:
			case self::MODE_QUERY_STRING:
			case self::MODE_MIXED:
				$this->m_mode = $a_mode;
				break;
		}
		return $this;
	}

	/**
	 * Get url generation mode
	 * @return string
	 */
	public function getMode()
	{
		return $this->m_mode;
	}

	/**
	 * Get route module name
	 * @return string
	 */
	public function getModule()
	{
		return (string)$this->m_path->module;
	}

	/**
	 * Set module
	 * @param string $a_module
	 * @return ZRoute
	 */
	public function & setModule( $a_module )
	{
		$this->m_path->module = (string)$a_module;
		return $this;
	}

	/**
	 * Get route controller name
	 * @return string
	 */
	public function getController()
	{
		return (string)$this->m_path->controller;
	}

	/**
	 * Set controller
	 * @param string $a_controller
	 * @return ZRoute
	 */
	public function & setController( $a_controller )
	{
		$this->m_path->controller = (string)$a_controller;
		return $this;
	}

	/**
	 * Get route method name
	 * @return string
	 */
	public function getAction()
	{
		return (string)$this->m_path->action;
	}

	/**
	 * Set action
	 * @param string $a_action
	 * @return ZRoute
	 */
	public function & setAction( $a_action )
	{
		$this->m_path->action = (string)$a_action;
		return $this;
	}

	/**
	 * Get route params as array
	 * @return array
	 */
	public function getParamsArray()
	{
		return $this->m_param->toArray();
	}

	/**
	 * Get route params as object
	 * @return array
	 */
	public function getParamsObject()
	{
		return $this->m_param->toObject();
	}

	/**
	 * Set route params
	 * @param ZRoute $a_params
	 */
	public function & setParams( $a_params )
	{
		$this->m_param->setData($a_params);
		return $this;
	}

	/**
	 * Get route pathway segments
	 * @return array
	 */
	public function getSegments()
	{
		return array_merge($this->m_path->toArray(), $this->m_param->toArray());
	}

	/**
	 * Get route segments as string
	 * @return string
	 */
	public function getSegmentsString()
	{
		return implode(Zoombi::SS, $this->getSegments());
	}

	/**
	 * Set route segments
	 * @param array $a_seg
	 * @return ZRoute
	 */
	public function & setSegments( array $a_seg )
	{
		$this->m_path->module = array_shift($a_seg);
		$this->m_path->controller = array_shift($a_seg);
		$this->m_path->action = array_shift($a_seg);
		$d = array( );

		foreach( $a_seg as $s )
			if( !empty($s) )
				$d[] = $s;

		$this->m_param->setData($d);
		return $this;
	}

	/**
	 * Get roue pathway segment segment
	 * @param mixed $a_segment
	 * @return string
	 */
	public function getSegment( $a_segment )
	{
		$s = $this->getSegments();
		if( isset($s[$a_segment] ) )
			return $s[$a_segment];

		$v = array_values($s);
		return isset($v[$a_segment]) ? $v[$a_segment] : null;
	}

	/**
	 * Set route pathway segment
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return ZRoute
	 */
	public function & setSegment( $a_key, $a_value )
	{
		$k = intval($a_key);
		switch( $k )
		{
			case 0:
				$this->m_path->module = $a_value;
				return $this;

			case 1:
				$this->m_path->controller = $a_value;
				return $this;

			case 2:
				$this->m_path->action = $a_value;
				return $this;
		}
		$this->m_param->set($k, $a_value);
		return $this;
	}

	/**
	 * Get route as human readable url HRU
	 * @return string
	 */
	public function toHumanReadableUrl( $a_clean = false )
	{
		return implode(Zoombi::US, $this->getSegments());
	}

	/**
	 * Get route string as query string
	 * @param string $a_modvar
	 * @param string $a_ctlvar
	 * @param string $a_actvar
	 * @return string
	 */
	public function toQueryStringUrl( $a_modvar = 'module', $a_ctlvar = 'controller', $a_actvar = 'action' )
	{
		$out = "?{$a_modvar}={$this->m_path->module}&{$a_ctlvar}={$this->m_path->controller}&{$a_actvar}={$this->m_path->action}";
		foreach( $this->getParamsArray() as $k => $v )
			$out .= "&{$k}={$v}";

		return $out;
	}

	/**
	 * Get route string as mixed url string
	 * @return string
	 */
	public function toMixedUrl()
	{
		$arr = array( );
		if( !empty($this->m_path->module) )
			$arr[] = $this->m_path->module;

		if( !empty($this->m_path->controller) )
			$arr[] = $this->m_path->controller;

		if( !empty($this->m_path->action) )
			$arr[] = $this->m_path->action;

		foreach( $this->m_param->getData() as $d )
			$arr[] = $d;

		$o = implode(Zoombi::SS, $arr);

		if( $this->m_query->count() < 1 )
			return strval($o);

		$arr = array( );
		foreach( $this->m_query->getData() as $k => $v )
			$arr[] = "{$k}={$v}";

		return $o . Zoombi::SS . '?' . implode('&', $arr);
	}

	/**
	 * Magic getter
	 * @param string $a_name
	 * @return mixed
	 */
	public function __get( $a_name )
	{
		switch( $a_name )
		{
			case 'module':
				return $this->getModule();

			case 'controller':
				return $this->getController();

			case 'action':
				return $this->getAction();

			case 'params':
				return $this->getParams();

			case 'query':
				return $this->getQuery();
		}
		return $this->m_query->get($a_name);
	}

	/**
	 * Magic setter
	 * @param string $a_name
	 * @param mixed $a_value
	 * @return mixed
	 */
	public function __set( $a_name, $a_value )
	{
		switch( $a_name )
		{
			case 'module':
				$this->setModule($a_value);
				return;

			case 'controller':
				$this->setController($a_value);
				return;

			case 'action':
				$this->setAction($a_value);
				return;

			case 'params':
				$this->setParams($a_value);
				return;

			case 'param':
				$this->setParam($a_value);
				return;

			case 'query':
				$this->setQuery($a_value);
				return;
		}
		return $this->m_query->set($a_name, $a_value);
	}

	/**
	 * To string conversion
	 * @return string
	 */
	public function __toString()
	{
		return $this->toMixedUrl();

		switch( $this->getMode() )
		{
			default:
			case self::MODE_HUMAN_READABLE_URL:
				return $this->toHumanReadableUrl();

			case self::MODE_QUERY_STRING:
				return $this->toQueryStringUrl();

			case self::MODE_MIXED:
				return $this->toMixedUrl();
		}
		return '';
	}

}
