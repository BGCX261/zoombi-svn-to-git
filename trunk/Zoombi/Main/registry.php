<?php

/*
 * File: registry.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is part of Zoombi PHP Framework
 */


/**
 * Registry like class
 */
class Zoombi_Registry implements ArrayAccess, Countable
{
	const KEYS_DELIMETER = '.';

	/**
	 * Variables holder
	 * @var array
	 */
	private $m_data = array();
	private $m_delimenter = Zoombi_Registry::KEYS_DELIMETER;

	/**
	 * Construct and set data
	 * @param array|object|Zoombi_Registry $a_data
	 */
	public function __construct( $a_data = null, $a_delimeter = null )
	{
		if($a_data)
			$this->setData($a_data);

		$this->setDelimeter($a_delimeter);
	}

	/**
	 * Get variables
	 * @return array
	 */
	public function getData()
	{
		return $this->m_data;
	}

	/**
	 * Get reference data
	 * @return array
	 */
	public function & getDataRef()
	{
		return $this->m_data;
	}

	/**
	 * Set data
	 * @param array|object|Zoombi_Registry $a_data
	 * @return Zoombi_Registry
	 */
	public function & setData( $a_data )
	{
		switch(gettype($a_data))
		{
			case 'array':
				$this->fromArray($a_data);
				break;

			case 'object':
				$this->fromObject($a_data);
				break;
		}
		return $this;
	}

	/**
	 * Set data by reference
	 * @param array|object|Zoombi_Registry $a_data
	 * @return Zoombi_Registry
	 */
	public function & setDataRef( & $a_data )
	{
		switch(gettype($a_data))
		{
			default:
				break;

			case 'array':
				$this->fromArrayRef($a_data);
				break;

			case 'object':
				$this->fromObjectRef($a_data);
				break;
		}
		return $this;
	}

	/**
	 * Conver data to array
	 * @return array
	 */
	public function toArray()
	{
		return $this->m_data;
	}

	/**
	 * Convert data to object
	 * @return object
	 */
	public function toObject()
	{
		return (object)$this->m_data;
	}

	/**
	 * Get data fron array
	 * @param array $a_array
	 * @return Zoombi_Registry
	 */
	public function & fromArray( array $a_array )
	{
		if(is_array($a_array))
			$this->m_data = $a_array;

		return $this;
	}

	/**
	 * Get data from array by reference
	 * @param array $a_array
	 * @return Zoombi_Registry
	 */
	public function & fromArrayRef( array & $a_array )
	{
		if(is_array($a_array))
			$this->m_data =& $a_array;

		return $this;
	}

	/**
	 * Get data from object
	 * @param <type> $a_object
	 * @return Zoombi_Registry
	 */
	public function & fromObject( $a_object )
	{
		if(!is_object($a_object))
			return $this;

		if($a_object instanceof Zoombi_Registry)
			$this->fromArray($a_object->toArray());
		else if($a_object instanceof Zoombi_Model)
			$this->fromArray((array)$a_object->export());
		else
			$this->fromArray((array)$a_object);

		return $this;
	}

	/**
	 * Set data reference  from object
	 * @param object $a_object
	 * @return Zoombi_Registry
	 */
	public function & fromObjectRef( & $a_object )
	{
		if(!is_object($a_object))
			return $this;

		if($a_object instanceof Zoombi_Registry)
			$this->m_data = $this->getDataRef();
		else
		{
			$d = array();
			foreach(get_object_vars($a_object) as $k => $v)
				$d[$k] = & $a_object->$k;
			$this->m_data = $d;
		}
		return $this;
	}

	/**
	 * Set key delimeter
	 * @param string $a_delimeter
	 * @return Zoombi_Registry
	 */
	public function & setDelimeter( $a_delimeter )
	{
		$this->m_delimenter = strval($a_delimeter);
		return $this;
	}

	/**
	 * Get key delimeter
	 * @return Zoombi_Registry
	 */
	public function getDelimeter()
	{
		return empty($this->m_delimenter) ? Zoombi_Registry::KEYS_DELIMETER : $this->m_delimenter;
	}

	/**
	 * Merge data
	 * @param array|object|Zoombi_Registry $a_data
	 * @param bool $a_recursive
	 * @return Zoombi_Registry
	 */
	public function & merge( $a_data, $a_recursive = false )
	{
		if($a_recursive)
			return $this->merge_recursive($a_data);

		$data = $this->toArray();
		$this->setData($a_data);
		$this->fromArray(
			array_merge(
				$data, $this->toArray()
			)
		);
		return $this;
	}

	/**
	 * Merge data recurcive
	 * @param array|object|Zoombi_Registry $a_data
	 * @return Zoombi_Registry
	 */
	public function & merge_recursive( $a_data )
	{
		$data = $this->toArray();
		$this->setData($a_data);
		$this->fromArray(
			array_merge_recursive(
				$data, $this->toArray()
			)
		);
		return $this;
	}

	/**
	 * Get registry Keys
	 * @return array
	 */
	public function keys()
	{
		return array_keys($this->m_data);
	}

	/**
	 * Get registry values
	 * @return array
	 */
	public function values()
	{
		return array_values($this->m_data);
	}

	/**
	 * Clear registry 
	 */
	public function & clear()
	{
		$this->m_data = array();
		return $this;
	}

	/**
	 * Get registry value by key name
	 * @param string $a_key
	 * @param mixed $a_default Return this value if is not found by key name
	 * @return mixed
	 */
	public function get( $a_key, $a_default = null )
	{
		return $this->_get($a_key, $a_default);
	}

	/**
	 * Get registry reference value by key name
	 * @param string $a_key
	 * @param mixed $a_default Return this value if is not found by key name
	 * @return mixed
	 */
	public function & getRef( $a_key, $a_default = null )
	{
		return $this->_get($a_key, $a_default);
	}

	/**
	 * Set registry value
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return Zoombi_Registry
	 */
	public function & set( $a_key, $a_value )
	{
		return $this->_set($a_key, $a_value);
	}

	/**
	 * Set registry reference value
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return Zoombi_Registry
	 */
	public function & setRef( $a_key, & $a_value )
	{
		return $this->_set($a_key, $a_value);
	}

	/**
	 * Get registry value by key name
	 * @param string $a_key
	 * @param mixed $a_default Return this value if is not found by key name
	 * @return mixed
	 */
	public function getValue( $a_key, $a_default = null )
	{
		return $this->_get($a_key, $a_default);
	}

	/**
	 * Get registry reference value by key name
	 * @param string $a_key
	 * @param mixed $a_default Return this value if is not found by key name
	 * @return mixed
	 */
	public function & getValueRef( $a_key, $a_default = null )
	{
		return $this->_get($a_key, $a_default);
	}

	/**
	 * Set registry value
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return Zoombi_Registry
	 */
	public function & setValue( $a_key, $a_value )
	{
		return $this->_set($a_key, $a_value);
	}

	/**
	 * Set registry reference value
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return Zoombi_Registry
	 */
	public function & setValueRef( $a_key, & $a_value )
	{
		return $this->_set($a_key, $a_value);
	}

	/**
	 * Get inner data
	 * @param string $a_key
	 * @param mixed $a_default
	 * @return mixed
	 */
	private function & _get( $a_key, $a_default = null )
	{
		if(array_key_exists($a_key, $this->m_data))
			return $this->m_data[$a_key];

		$ptr = & $this->m_data;
		foreach(explode($this->getDelimeter(), $a_key) as $t)
		{
			if(!$ptr OR !is_array($ptr) OR !isset($ptr[$t]))
				return $a_default;

			$ptr = & $ptr[$t];
		}
		return $ptr;
	}

	/**
	 * Set inner data
	 * @param string $a_key
	 * @param mixed $a_default
	 * @return Zoombi_Registry
	 */
	private function & _set( $a_key, & $a_value )
	{
		if(array_key_exists($a_key, $this->m_data))
		{
			$this->m_data[$a_key] = $a_value;
			return $this;
		}

		$ptr = & $this->m_data;
		foreach(explode($this->getDelimeter(), $a_key) as $t)
		{
			if(!$ptr OR !is_array($ptr) OR !isset($ptr[$t]))
			{
				$ptr[$t] = array(null);
			}
			$ptr = & $ptr[$t];
		}
		$ptr = $a_value;
		return $this;
	}

	/**
	 * Unset registry value by keyname
	 * @param string $a_key
	 * @return Zoombi_Registry
	 */
	private function & _unset( $a_key )
	{
		if(isset($this->m_data[$a_key]))
		{
			unset($this->m_data[$a_key]);
			return $this;
		}

		$tkn = explode($this->getDelimeter(), $a_key);
		$last = null;
		if(count($tkn))
			$last = $tkn[count($tkn) - 1];

		$ptr = & $this->m_data;
		$lp = null;
		$lk = null;
		foreach($tkn as $t)
		{
			if(!$ptr OR !is_array($ptr) OR !isset($ptr[$t]))
				return $this;

			$lp = & $ptr;
			$lk = $t;

			$ptr = & $ptr[$t];
		}

		if(strtolower($lk) == strtolower($last))
		{
			$lp[$lk] = null;
			unset($lp[$lk]);
		}

		return $this;
	}

	/**
	 * Check if value is exist by key name
	 * @param string $a_key
	 * @return bool True if value is exist
	 */
	private function _has( $a_key )
	{
		if(isset($this->m_data[$a_key]))
			return true;

		$ptr = & $this->m_data;
		foreach(explode($this->getDelimeter(), $a_key) as $t)
		{
			if(!$ptr OR !is_array($ptr) OR !isset($ptr[$t]))
				return false;

			$ptr = & $ptr[$t];
		}
		return true;
	}

	/**
	 * Unset registry value by keyname
	 * @param string $a_key
	 * @return Zoombi_Registry
	 */
	public function & unsetValue( $a_key )
	{
		return $this->_unset($a_key);
	}

	/**
	 * Check if value is exist by key name
	 * @param string $a_key
	 * @return bool True if value is exist
	 */
	public function hasValue( $a_key )
	{
		return $this->_has($a_key);
	}

	/**
	 * Check if value is exist by key name
	 * @param string $a_key
	 * @return bool True if value is exist
	 */
	public function exist( $a_key )
	{
		return $this->_has($a_key);
	}

	/**
	 * Check if value is exist by key name
	 * @param string $a_key
	 * @return bool True if value is exist
	 */
	public function has( $a_key )
	{
		return $this->_has($a_key);
	}

	/* Magic methods */

	public function __get( $a_key )
	{
		return $this->_get($a_key);
	}

	public function __set( $a_key, $a_value )
	{
		$this->_set($a_key, $a_value);
	}

	public function __isset( $a_key )
	{
		return $this->_has($a_key);
	}

	public function __unset( $a_key )
	{
		return $this->_unset($a_key);
	}

	/* Implement ArrayAccess */

	public function offsetExists( $a_offset )
	{
		$this->_has($a_offset);
	}

	public function offsetGet( $a_offset )
	{
		return $this->_get($a_offset, null);
	}

	public function offsetSet( $a_offset, $a_value )
	{
		$this->_set($a_offset, $a_value);
	}

	public function offsetUnset( $a_offset )
	{
		$this->_unset($a_offset);
	}

	public function count()
	{
		return count($this->m_data);
	}

}
