<?php

/*
 * File: router.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Router class
 * @author Zombie
 */
class ZRouter extends ZObject
{

	/**
	 * Array of ZRouteRule
	 * @var array
	 */
	private $m_rules;
	/**
	 * Request route
	 * @var ZRoute
	 */
	private $m_request;
	/**
	 * Redirect route
	 * @var ZRoute
	 */
	private $m_redirect;
	/**
	 * Current route
	 * @var ZRoute
	 */
	private $m_current;
	/**
	 * Forwarded route
	 * @var ZRoute
	 */
	private $m_forward;

	/**
	 * Construct
	 */
	function __construct( $a_route = null, $a_rules = array( ) )
	{
		parent::__construct();
		$this->m_rules = array( );
		if( $a_rules )
			$this->setRules($a_rules);

		$this->route($a_route);
	}

	/**
	 * Set route rules
	 * @param array $a_rules
	 * @return ZRouter
	 */
	public function & setRules( array $a_rules )
	{
		return $this->clearRules()->addRules($a_rules);
	}

	/**
	 * Add route rules
	 * @param array $a_rules
	 * @return ZRouter
	 */
	public function & addRules( array $a_rules )
	{
		foreach( $a_rules as $pattern => $location )
			$this->addRule($pattern, $location);
		return $this;
	}

	/**
	 * Add route rule
	 * @param ZRouteRule $a_rule
	 * @return ZRouter
	 */
	public function & addRule( $a_pattern, $a_location )
	{
		$this->m_rules[$a_pattern] = $a_location;
		return $this;
	}

	/**
	 * Clear router rules
	 * @return ZRouter
	 */
	public function & clearRules()
	{
		$this->m_rules = array( );
		return $this;
	}

	/**
	 * Create RegExp from pattern
	 * @param string $a_pattern
	 * @return string
	 */
	private function _makeregexp( $a_pattern )
	{
		$replace = array(
			'/' => '\/',
			':num' => '[0-9]{1,8}',
			':char' => '[a-z]{1}',
			':word' => '[0-z]{2,}',
			':any' => '.+'
		);
		return '/^\/?' . str_ireplace(array_keys($replace), array_values($replace), $a_pattern) . '/i';
	}

	/**
	 * Check if rule is match
	 * @param string $a_path Target to match
	 * @param string $a_pattern Pattern to match
	 * @return bool True if match else false
	 */
	private function _match( $a_path, $a_pattern )
	{
		return preg_match($this->_makeregexp($a_pattern), $a_path);
	}

	/**
	 * Parse path
	 * @param string $a_path
	 * @return string
	 */
	private function _parse( $a_path )
	{
		foreach( $this->m_rules as $key => $rule )
		{
			if( $this->_match($a_path, $key) )
			{
				$url = preg_replace($this->_makeregexp($key), $rule, $a_path);
				return $url;
			}
		}
		return false;
	}

	/**
	 * Route path
	 * @param string $a_path
	 * @return ZRoute
	 */
	public function route( $a_path )
	{
		$route = (string)$a_path;

		$this->setRequest($route);
		$this->setRedirect(clone $this->getRequest());

		for( $i = 0; $i < 10; $i++ )
		{
			$tmp = $this->_parse((string)$this->getRedirect());
			if( $tmp )
				$this->setRedirect($tmp);
		}
	}

	/**
	 * Set request route
	 * @param string|ZRoute $a_request
	 * @return ZRouter
	 */
	public function & setRequest( $a_request )
	{
		$this->m_request = new ZRoute($a_request);
		return $this;
	}

	/**
	 * Set redirect route
	 * @param string|ZRoute $a_redirect
	 * @return ZRouter
	 */
	public function & setRedirect( $a_redirect )
	{
		$this->m_redirect = new ZRoute($a_redirect);
		return $this;
	}

	/**
	 * Set current route
	 * @param string|ZRoute $a_current
	 * @return ZRouter
	 */
	public function & setCurrent( $a_current )
	{
		$this->m_current = new ZRoute($a_current);
		return $this;
	}

	/**
	 * Set forward route
	 * @param string|ZRoute $a_current
	 * @return ZRouter
	 */
	public function & setForward( $a_forward )
	{
		$this->m_forward = new ZRoute($a_forward);
		return $this;
	}

	/**
	 * Get request
	 * @return ZRoute
	 */
	public function & getRequest()
	{
		return $this->m_request;
	}

	/**
	 * Get redirect
	 * @return ZRoute
	 */
	public function & getRedirect()
	{
		return $this->m_redirect;
	}

	/**
	 * Get current
	 * @return ZRoute
	 */
	public function & getCurrent()
	{
		return $this->m_current;
	}

	/**
	 * Get forward
	 * @return ZRoute
	 */
	public function & getForward()
	{
		return $this->m_forward;
	}

	/**
	 * Router getter
	 * @param string $a_name
	 * @return mixed
	 */
	public function __get( $a_name )
	{
		switch( $a_name )
		{
			case 'current':
				return $this->m_current;

			case 'redirect':
				return $this->m_redirect;

			case 'request':
				return $this->m_request;

			case 'forward':
				return $this->m_forward;
		}
		return parent::__get($a_name);
	}

	/**
	 * Router getter
	 * @param string $a_name
	 * @param mixed $a_value
	 * @return mixed
	 */
	public function __set( $a_name, $a_value )
	{
		switch( $a_name )
		{
			case 'current':
				$this->setCurrent($a_value);
				return;

			case 'redirect':
				$this->setRedirect($a_value);
				return;

			case 'request':
				$this->setRequest($a_value);
				return;

			case 'forward':
				$this->setForward($a_value);
				return;
		}
		return parent::__set($a_name, $a_value);
	}

}
