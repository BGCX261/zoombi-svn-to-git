<?php

/*
 * File: router.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file a part of Zoombi PHP Framework
 */


/**
 * Router class
 * @author Zombie
 */
class Zoombi_Router extends Zoombi_Object
{

	/**
	 * Array of rules
	 * @var array
	 */
	private $m_rules;

	/**
	 * Request route
	 * @var Zoombi_Route
	 */
	private $m_request;

	/**
	 * Redirect route
	 * @var Zoombi_Route
	 */
	private $m_redirect;

	/**
	 * Current route
	 * @var Zoombi_Route
	 */
	private $m_current;

	/**
	 * Forwarded route
	 * @var Zoombi_Route
	 */
	private $m_forward;

	/**
	 * Construct
	 */
	public function __construct( Zoombi_Object & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		$this->m_rules = array();
		$this->m_request = new Zoombi_Route();
		$this->m_redirect = new Zoombi_Route();
		$this->m_current = new Zoombi_Route();
		$this->m_request = null;
	}

	public function __destruct()
	{
		unset($this->m_current, $this->m_redirect, $this->m_forward, $this->m_request);
	}

	/**
	 * Set route rules
	 * @param array $a_rules
	 * @return Zoombi_Router
	 */
	public function & setRules( array $a_rules )
	{
		return $this->clearRules()->addRules($a_rules);
	}

	/**
	 * Add route rules
	 * @param array $a_rules
	 * @return Zoombi_Router
	 */
	public function & addRules( array $a_rules )
	{
		foreach($a_rules as $pattern => $location)
			$this->addRule($pattern, $location);
		return $this;
	}

	/**
	 * Add route rule
	 * @param string $a_pattern Pattern rule to match
	 * @return Zoombi_Router
	 */
	public function & addRule( $a_pattern, $a_location )
	{
		$this->m_rules[$a_pattern] = $a_location;
		return $this;
	}

	/**
	 * Clear router rules
	 * @return Zoombi_Router
	 */
	public function & clearRules()
	{
		$this->m_rules = array();
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
			':num' => '[0-9]{1,8}',
			':char' => '[a-z]{1}',
			':word' => '[0-z]{2,}',
			':segment' => '[^/]*',
			':any' => '.*',
			'/' => '\/',
		);
		return '/^\/?' . str_ireplace(array_keys($replace), array_values($replace), $a_pattern) . '$/i';
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
		if(!$this->m_rules)
			return false;

		foreach($this->m_rules as $key => $rule)
		{
			if($this->_match($a_path, $key))
			{
				$url = preg_replace($this->_makeregexp($key), $rule, $a_path);
				return $url;
			}
		}
		return false;
	}

	/**
	 * Rewrite path
	 * @param string $a_path
	 * @return Zoombi_Route
	 */
	public function rewrite( $a_path )
	{
		$tmp = $this->_parse($a_path);
		if($tmp === false)
			return $a_path;

		return $this->rewrite($tmp);
	}

	/**
	 * Set request route
	 * @param string|Zoombi_Route $a_request
	 * @return Zoombi_Router
	 */
	public function & setRequest( $a_route )
	{
		if($a_route instanceof Zoombi_Route)
			$this->m_request = clone $a_route;
		else
			$this->m_request = new Zoombi_Route($a_route);
		return $this;
	}

	/**
	 * Set redirect route
	 * @param string|Zoombi_Route $a_redirect
	 * @return Zoombi_Router
	 */
	public function & setRedirect( $a_route )
	{
		if($a_route instanceof Zoombi_Route)
			$this->m_redirect = $a_route;
		else
			$this->m_redirect = new Zoombi_Route($a_route);
		return $this;
	}

	/**
	 * Set current route
	 * @param string|Zoombi_Route $a_current
	 * @return Zoombi_Router
	 */
	public function & setCurrent( $a_route )
	{
		if($a_route instanceof Zoombi_Route)
			$this->m_current = $a_route;
		else
			$this->m_current = new Zoombi_Route($a_route);
		return $this;
	}

	/**
	 * Set forward route
	 * @param string|Zoombi_Route $a_current
	 * @return Zoombi_Router
	 */
	public function & setForward( $a_route )
	{
		if($a_route instanceof Zoombi_Route)
			$this->m_forward = $a_route;
		else
			$this->m_forward = new Zoombi_Route($a_route);
		
		return $this;
	}

	/**
	 * Get request
	 * @return Zoombi_Route
	 */
	public function & getRequest()
	{
		return $this->m_request;
	}

	/**
	 * Get redirect
	 * @return Zoombi_Route
	 */
	public function & getRedirect()
	{
		return $this->m_redirect;
	}

	/**
	 * Get current
	 * @return Zoombi_Route
	 */
	public function & getCurrent()
	{
		return $this->m_current;
	}

	/**
	 * Get forward
	 * @return Zoombi_Route
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
	public function & __get( $a_name )
	{
		switch($a_name)
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
	}

	/**
	 * Router getter
	 * @param string $a_name
	 * @param mixed $a_value
	 * @return mixed
	 */
	public function __set( $a_name, $a_value )
	{
		switch($a_name)
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
	}

	public function __toString()
	{
		return 'Request: ' . $this->m_request . '<br />' .
			'Redirect: ' . $this->m_redirect . '<br />' .
			'Current: ' . $this->m_current . '<br />' .
			'Forward: ' . $this->m_forward . '<br />';
	}
}
