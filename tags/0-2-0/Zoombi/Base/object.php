<?php

/*
 * File: object.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Base class
 * @property ZApplication $application
 * @property ZRegistry $registry
 * @property ZLanguage $language
 * @property ZRouter $router
 * @property ZLoader $load
 * @property ZDatabase $database
 */
abstract class ZObject extends ZNode
{

	/**
	 * Object name
	 * @var string
	 */
	private $m_name;
	/**
	 * Array of controller properties
	 * @var array
	 */
	private $m_data = array( );
	/**
	 *
	 * @var ZDispatcher
	 */
	private $m_dispatcher;

	/**
	 * Constructor
	 * @param ZObject $a_parent
	 * @param string $a_name
	 */
	function __construct( ZObject & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent);
		if( $a_name !== null )
			$this->setName($a_name);
	}

	/**
	 *
	 * @return ZDispatcher
	 */
	public function & getDispatcher()
	{
		$d = ($this->m_dispatcher) ?
				$this->m_dispatcher :
				Zoombi::getDispatcher();
		return $d;
	}

	/**
	 *
	 * @param ZDispatcher $a_dispatcher
	 * @return ZObject
	 */
	public function & setDispatcher( ZDispatcher & $a_dispatcher )
	{
		if( !$a_dispatcher )
		{
			unset($this->m_dispatcherr);
			return $this;
		}

		$this->m_dispatcherr = $a_dispatcher;
		return $this;
	}

	public function & emit( ZEvent & $a_event )
	{
		$this->getDispatcher()->emit($a_event);
		return $this;
	}

	/**
	 * Set ZObject name
	 * @param string $a_name
	 */
	public final function & setName( $a_name )
	{
		$this->m_name = (string)$a_name;
		return $this;
	}

	/**
	 * Get ZObject name
	 * @return sting
	 */
	public final function & getName()
	{
		if( $this->m_name === null )
			$this->m_name = 'Object' . $this->getId();

		return $this->m_name;
	}

	/**
	 * Get or set ZObject name
	 * @param string $a_name
	 * @return string
	 */
	public final function & name( $a_name = null )
	{
		if( $a_name === null )
			return $this->getName();

		return $this->setName($a_name);
	}

	public function __get( $a_property )
	{
		switch( $a_property )
		{
			default:
				break;

			case 'application':
				return Zoombi::getApplication();

			case 'database':
				return Zoombi::getApplication()->getDatabase();

			case 'registry':
				return Zoombi::getApplication()->getRegistry();

			case 'language':
				return Zoombi::getApplication()->getLanguage();

			case 'router':
				return Zoombi::getApplication()->getRouter();

			case 'load':
				return ZLoader::getInstance();

			case 'name':
				return $this->getName();
		}
		return $this->getProperty($a_property);
	}

	public function __set( $a_name, $a_value )
	{
		switch( $a_name )
		{
			case 'load':
			case 'registry':
			case 'language':
			case 'application':
				return;

			case 'name':
				return $this->setName($a_value);
		}
		return $this->setProperty($a_name, $a_value);
	}

	public function __unset( $a_name )
	{
		switch( $a_name )
		{
			case 'load':
			case 'registry':
			case 'language':
			case 'application':
				return;

			case 'name':
				return $this->setName(null);
		}
		return $this->unsetProperty($a_name);
	}

	/**
	 * Get object property
	 * @param string $a_name
	 * @return mixed
	 */
	public function & getProperty( $a_name )
	{
		$null = null;
		if( $this->hasProperty($a_name) )
		{
			return $this->m_data[$a_name];
		}
		return $null;
	}

	/**
	 * Set object property
	 * @param string $a_name
	 * @param mixed $a_value
	 * @return ZController
	 */
	public function & setProperty( $a_name, $a_value )
	{
		$this->m_data[$a_name] = $a_value;
		return $this;
	}

	/**
	 * Unset object property
	 * @param string $a_name
	 * @param mixed $a_value
	 * @return ZController
	 */
	public function & unsetProperty( $a_name, & $a_old = null )
	{
		if( $this->hasProperty($a_name) )
		{
			$a_old = $this->getProperty($a_name);
			unset($this->m_data[$a_name]);
			return $this;
		}
		return $this;
	}

	public function hasProperty( $a_name )
	{
		return isset($this->m_data[$a_name]);
	}

}
