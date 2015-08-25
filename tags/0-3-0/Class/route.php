<?php

/*
 * File: node.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * Route class
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
	 * Module name
	 * @var string
	 */
	private $m_module;
	/**
	 * Controller name
	 * @var string
	 */
	private $m_controller;
	/**
	 * Action name
	 * @var string
	 */
	private $m_action;
	/**
	 * Parametres
	 * @var array
	 */
	private $m_params;
	/**
	 * Default URL mode
	 * @var int 
	 */
	private $m_mode;

	const MODE_HUMAN_READABLE_URL = 0;
	const MODE_QUERY_STRING = 1;
	const MODE_MIXED = 2;

	/**
	 * Constructor
	 * @param string|ZRoute $a_params
	 */
	public function __construct( $a_url, $a_mode = self::MODE_HUMAN_READABLE_URL )
	{
		$this->m_params = array( );
		$this->setMode($a_mode);

		if( $a_url instanceof ZRoute )
		{
			$this->module = $a_url->getModule();
			$this->controller = $a_url->getController();
			$this->action = $a_url->getAction();
			$this->params = $a_url->getParams();
			return;
		}

		if( !is_string($a_url) )
			return;

		$req_arr = explode('?', (string)$a_url, 2);
		$path = array_shift($req_arr);

		$exp = explode(Zoombi::US, $path);

		$this->module = count($exp) > 0 ? array_shift($exp) : null;
		$this->controller = count($exp) > 0 ? array_shift($exp) : null;
		$this->action = count($exp) > 0 ? array_shift($exp) : null;

		$params = & $this->m_params;
		if( count($exp) > 1 )
		{
			$key = null;
			$val = null;

			while( $exp )
			{
				$key = array_shift($exp);
				$val = array_shift($exp);
				$params[$key] = $val;
			}
		}
		else if( count($exp) == 1 )
		{
			$params[0] = array_shift($exp);
		}

		if( count($req_arr) )
		{
			$query = array_shift($req_arr);
			$parsed = array( );
			parse_str($query, $parsed);

			if( $parsed && is_array($parsed) )
				$this->m_params = array_merge($this->m_params, $parsed);

			/*
			  if( !$this->controller && count($params) )
			  {
			  foreach( $this->params as $k => $v )
			  {
			  $this->controller = $k;
			  $this->action = $v;
			  break;
			  }
			  array_shift($params);
			  } */
		}
	}

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
		return $this->m_module;
	}

	/**
	 * Set module
	 * @param string $a_module
	 * @return ZRoute
	 */
	public function & setModule( $a_module )
	{
		$this->m_module = (string)$a_module;
		return $this;
	}

	/**
	 * Get route module name
	 * @return string
	 */
	public function getController()
	{
		return $this->m_controller;
	}

	/**
	 * Set controller
	 * @param string $a_controller
	 * @return ZRoute
	 */
	public function & setController( $a_controller )
	{
		$this->m_controller = (string)$a_controller;
		return $this;
	}

	/**
	 * Get route method name
	 * @return string
	 */
	public function getAction()
	{
		return $this->m_action;
	}

	/**
	 * Set action
	 * @param string $a_action
	 * @return ZRoute
	 */
	public function & setAction( $a_action )
	{
		$this->m_action = (string)$a_action;
		return $this;
	}

	/**
	 * Get route params
	 * @return array
	 */
	public function getParams()
	{
		return $this->m_params;
	}

	/**
	 * Get route params as array
	 * @return array
	 */
	public function getParamsArray()
	{
		return $this->m_params;
	}

	/**
	 * Get route params as object
	 * @return array
	 */
	public function getParamsObject()
	{
		return (object)$this->getParamsArray();
	}

	public function setParams( $a_params )
	{
		$this->m_params = $a_params;
	}

	public function toHumanReadableUrl()
	{
		$arr = array( );
		if( $this->m_module )
			$arr[] = $this->m_module;

		if( $this->m_controller )
			$arr[] = $this->m_controller;

		if( $this->m_action )
			$arr[] = $this->m_action;

		foreach( $this->m_params as $i => $v )
		{
			if( empty($v) )
				continue;
			$arr[] = $i . Zoombi::US . $v;
		}
		return implode(Zoombi::US, $arr);
	}

	public function toQueryStringUrl( $a_modvar = 'module', $a_ctlvar = 'controller', $a_actvar = 'action' )
	{
		$out = "?{$a_modvar}={$this->module}&{$a_ctlvar}={$this->controller}&{$a_actvar}={$this->action}";
		if( !is_array($this->params) )
			return $out;

		foreach( $this->params as $k => $v )
			$out .= "&{$k}={$v}";

		return $out;
	}

	public function toMixedUrl()
	{
		$out = '';
		if( $this->m_module )
			$out .= $this->m_module . Zoombi::US;

		if( $this->m_controller )
			$out .= $this->m_controller . Zoombi::US;

		if( $this->m_action )
			$out .= $this->m_action . Zoombi::US;

		if( !is_array($this->params) )
			return $out;

		$arr = array( );

		foreach( $this->params as $k => $v )
			$arr[] = "{$k}={$v}";

		return $out . '?' . implode('&', $arr);
	}

	public function __get( $a_name )
	{
		switch( $a_name )
		{
			case 'module':
				return $this->m_module;

			case 'controller':
				return $this->m_controller;

			case 'action':
				return $this->m_action;

			case 'params':
				return $this->m_params;
		}
	}

	public function __set( $a_name, $a_value )
	{
		switch( $a_name )
		{
			case 'module':
				return $this->setModule($a_value);

			case 'controller':
				return $this->setController($a_value);

			case 'action':
				return $this->setAction($a_value);

			case 'params':
				return $this->setParams($a_value);
		}
	}

	/**
	 * To string conversion
	 * @return string
	 */
	public function __toString()
	{
		switch( $this->getMode() )
		{
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
