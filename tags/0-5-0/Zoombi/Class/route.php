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
	private $m_segments;
	/**
	 * Route data
	 * @var ZRegistry
	 */
	private $m_query;

	/**
	 * Constructor
	 * @param string|ZRoute $a_params
	 */
	public function __construct( $a_url = null )
	{
		$this->m_segments = array(
		);
		$this->m_query = new ZRegistry;
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
			$this->m_segments = $a_route->getSegments();
			$this->m_query->setData( $a_route->getQuery() );
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
		while( substr($a_url,0, 1) == Zoombi::SS )
			$a_url = substr( $a_url, 1 );

		$this->clear();
		$req_arr = explode('?', (string)$a_url, 2);
		$this->setSegments(explode(Zoombi::SS, array_shift($req_arr)));
		parse_str(array_shift($req_arr), $var);
		$this->m_query->setData($var);
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
	public function getParams()
	{
		return array_slice($this->m_segments, 3);
	}

	/**
	 * Get route param by key
	 * @param int|string $a_key A Key
	 * @param mixed $a_default Default value if $a_key is not exist
	 * @return string
	 */
	public function getParam( $a_key, $a_default = null )
	{
		$params = $this->getParams();
		return isset( $params[$a_key] ) ? $params[$a_key] : $a_default;
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
		foreach( func_get_args( ) as $a )
			array_unshift($this->m_segments, $a);
		return $this;
	}

	/**
	 * Append segment to end of route
	 * @param string $a_value
	 * @param mixed $_ [optional]
	 * @return ZRoute
	 */
	public function & push_end( $a_value )
	{
		foreach( func_get_args( ) as $a )
			array_push($this->m_segments, $a);
		return $this;
	}

	/**
	 * Pop segment from start
	 * @return ZRoute
	 */
	public function & pop_start()
	{
		array_shift($this->m_segments);
		return $this;
	}

	/**
	 * Pop segment from end
	 * @return ZRoute
	 */
	public function & pop_end()
	{
		array_pop($this->m_segments);
		return $this;
	}

	/**
	 * Clear route
	 * @return ZRoute
	 */
	public function & clear()
	{
		$this->m_segments = array();
		$this->m_query->clear();
		return $this;
	}

	/**
	 * Cloning
	 */
	public function __clone()
	{
		$this->m_segments = $this->getSegments();
		$this->m_query = clone $this->getQuery();
	}

	/**
	 * Route destructor
	 */
	public function __destruct()
	{
		unset($this->m_segments, $this->m_query);
	}

	/**
	 * Get route module name
	 * @return string
	 */
	public function getModule()
	{
		return $this->m_segments[0];
	}

	/**
	 * Set module
	 * @param string $a_module
	 * @return ZRoute
	 */
	public function & setModule( $a_module )
	{
		$this->m_segments[0] = (string)$a_module;
		return $this;
	}

	/**
	 * Get route controller name
	 * @return string
	 */
	public function getController()
	{
		return $this->m_segments[1];
	}

	/**
	 * Set controller
	 * @param string $a_controller
	 * @return ZRoute
	 */
	public function & setController( $a_controller )
	{
		$this->m_segments[1] = (string)$a_controller;
		return $this;
	}

	/**
	 * Get route method name
	 * @return string
	 */
	public function getAction()
	{
		return $this->m_segments[2];
	}

	/**
	 * Set action
	 * @param string $a_action
	 * @return ZRoute
	 */
	public function & setAction( $a_action )
	{
		$this->m_segments[2] = (string)$a_action;
		return $this;
	}

	/**
	 * Get route pathway segments
	 * @return array
	 */
	public function getSegments()
	{
		return $this->m_segments;
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
		$d = array( );
		$i = 0;
		foreach( $a_seg as $s )
			if( !empty($s) )
				$this->m_segments[$i++] = $s;

		return $this;
	}

	/**
	 * Get roue pathway segment segment
	 * @param mixed $a_segment
	 * @return string
	 */
	public function getSegment( $a_segment )
	{
		return isset( $this->m_segments[$a_segment] ) ? $this->m_segments[$a_segment] : null;
	}

	/**
	 * Set route pathway segment
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return ZRoute
	 */
	public function & setSegment( $a_key, $a_value )
	{
		$this->m_segments[$a_key] = $a_value;
		return $this;
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
		$o = implode(Zoombi::SS, $this->m_segments);

		$arr = array();
		foreach( $this->m_query->getData() as $k => $v )
			$arr[]= $k.'='.$v;

		if( count( $arr ) > 0 )
			$o .= '?' . implode( '&', $arr );

		return $o;
	}

	public function getDebug()
	{
		$d = new ZDummyController(Zoombi::getApplication(),'__dummy_ctl__');
		$o = $d->render( Zoombi::fromFrameworkDir('Views/view_route_debug.php'), array('route'=>&$this), true );
		unset($d);
		return $o;
	}

	public function printDebug()
	{
		echo $this->getDebug();
	}

}
