<?php

/*
 * File: object.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Base class
 */
abstract class Zoombi_Object extends Zoombi_Node
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
	private $m_data = array();

	/**
	 * Constructor
	 * @param Zoombi_Object $a_parent
	 * @param string $a_name
	 */
	function __construct( Zoombi_Object & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent);
		if($a_name !== null)
			$this->setName($a_name);
	}

	/**
	 * Set Zoombi_Object name
	 * @param string $a_name
	 */
	public final function & setName( $a_name )
	{
		$this->m_name = (string)$a_name;
		return $this;
	}

	/**
	 * Get Zoombi_Object name
	 * @return sting
	 */
	public final function & getName()
	{
		if($this->m_name === null)
			$this->m_name = 'Object' . $this->getId();

		return $this->m_name;
	}

	/**
	 * Get or set Zoombi_Object name
	 * @param string $a_name
	 * @return string
	 */
	public final function & name( $a_name = null )
	{
		if($a_name === null)
			return $this->getName();

		return $this->setName($a_name);
	}

	public function & __get( $a_property )
	{
		switch($a_property)
		{
			case 'name':
				return $this->getName();
		}
		return $this->getProperty($a_property);
	}

	public function __set( $a_name, $a_value )
	{
		switch($a_name)
		{
			case 'name':
				return $this->setName($a_value);
		}
		return $this->setProperty($a_name, $a_value);
	}

	public function __unset( $a_name )
	{
		switch($a_name)
		{
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
		if($this->hasProperty($a_name))
			return $this->m_data[$a_name];

		throw new Zoombi_Exception('Property "' . $a_name . '" not found', Zoombi_Exception::EXC_NO_PROPERTY);
		return Zoombi::$null;
	}

	/**
	 * Set object property
	 * @param string $a_name
	 * @param mixed $a_value
	 * @return Zoombi_Controller
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
	 * @return Zoombi_Controller
	 */
	public function & unsetProperty( $a_name, & $a_old = null )
	{
		if($this->hasProperty($a_name))
		{
			$a_old = $this->getProperty($a_name);
			unset($this->m_data[$a_name]);
			return $this;
		}
		return $this;
	}

	public function hasProperty( $a_name )
	{
		if(property_exists($this, $a_name))
			return true;

		switch($a_name)
		{
			case 'name':
				return true;
		}

		return array_key_exists($a_name, $this->m_data);
	}

}
