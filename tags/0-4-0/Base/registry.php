<?php
/*
 * File: registry.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Registry like class
 */
class ZRegistry implements ArrayAccess, Countable
{
	const KEYS_DELIMETER = '.';

	/**
	 * Variables holder
	 * @var array
	 */
	private $m_data = array();
	private $m_delimenter = ZRegistry::KEYS_DELIMETER;

	/**
	 * Construct and set data
	 * @param array|object|ZRegistry $a_data
	 */
	public function __construct( $a_data = null, $a_delimeter = null )
	{
		if( $a_data )
			$this->setData($a_data);

		$this->setDelimeter($a_delimeter);
	}

	/**
	 * Get variables
	 * @return array
	 */
	public final function & getData()
	{
		return $this->m_data;
	}

	/**
	 * Set data
	 * @param array|object|ZRegistry $a_data
	 * @return ZRegistry
	 */
	public final function & setData( $a_data )
	{
		switch ( gettype($a_data) )
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
	 * @param array|object|ZRegistry $a_data
	 * @return ZRegistry
	 */
	public final function & setDataRef( & $a_data )
	{
		switch ( gettype($a_data) )
		{
			default:
				break;
			
			case 'array':
				$this->fromArrayRef($a_data);
				break;

			case 'object':
				$this->fromObject($a_data);
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
     * @return ZRegistry
     */
	public function & fromArray( array $a_array )
	{
		if( is_array($a_array) )
			$this->m_data = $a_array;

		return $this;
	}

    /**
     * Get data from array by reference
     * @param array $a_array
     * @return ZRegistry
     */
	public function & fromArrayRef( array & $a_array )
	{
		if( is_array($a_array) )
			$this->m_data = $a_array;

		return $this;
	}

    /**
     * Get data from object
     * @param <type> $a_object
     * @return ZRegistry
     */
	public function & fromObject( $a_object )
	{
		if( !is_object($a_object) )
			return $this;

		if( is_a($a_object, 'ZRegistry') )
			$this->fromArray( $a_object->toArray() );
		else if( is_a($a_object, 'ZModel' ) )
			$this->fromArray( $a_object->export() );
		else
			$this->fromArray( (array)$a_object );

		return $this;
	}

	/**
	 * Set key delimeter
	 * @param string $a_delimeter
	 * @return ZRegistry
	 */
	public final function & setDelimeter( $a_delimeter )
	{
		$this->m_delimenter = strval($a_delimeter);
		return $this;
	}

	/**
	 * Get key delimeter
	 * @return ZRegistry
	 */
	public final function getDelimeter()
	{
		return empty($this->m_delimenter) ? ZRegistry::KEYS_DELIMETER : $this->m_delimenter;
	}

	/**
	 * Set data
	 * @param array|object|ZRegistry $a_data
	 * @return ZRegistry
	 */
	public final function & merge( $a_data )
	{
		$old = $this->toArray();
		$this->setData($a_data)->fromArray( array_merge( $old, $this->toArray() ) );
		return $this;
	}

	/**
	 * Get registry Keys
	 * @return array
	 */
	public final function keys()
	{
		return array_keys($this->m_data);
	}

	/**
	 * Get registry values
	 * @return array
	 */
	public final function values()
	{
		return array_values($this->m_data);
	}

	/**
	 * Clear registry 
	 */
	public final function & clear()
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
	public final function & getValue( $a_key, $a_default = null )
	{
		if( array_key_exists( $a_key, $this->m_data ) )
			return $this->m_data[$a_key];

		$ptr =& $this->m_data;
		$tkn = explode( $this->getDelimeter(), $a_key );

		foreach( $tkn as $t )
		{
			if( !$ptr || !is_array($ptr) OR !isset($ptr[$t]) )
				return $a_default;

			$ptr =& $ptr[$t];
		}

		return $ptr;
	}

	/**
	 * Set registry value
	 * @param string $a_key
	 * @param mixed $a_value
	 * @return ZRegistry
	 */
	public final function & setValue( $a_key, $a_value )
	{	
		$ptr =& $this->m_data;
		foreach( explode( $this->getDelimeter(), $a_key ) as $t )
		{
			if( !$ptr || !is_array($ptr) || !isset($ptr[$t]) )
			{
				$ptr[$t] = array(null);
			}
			$ptr =& $ptr[$t];
		}
		$ptr = $a_value;
		return $this;
	}

	/**
	 * Unset registry value by keyname
	 * @param string $a_key
	 * @return ZRegistry
	 */
	public final function & unsetValue( $a_key )
	{
		if( array_key_exists( $a_key, $this->m_data ) )
		{
			unset( $this->m_data[$a_key] );
			return $this;
		}
		
		$ptr =& $this->m_data;

		$lp = null;
		$lk = null;
		
		foreach( explode( $this->getDelimeter(), $a_key ) as $t )
		{
			if( !$ptr || !is_array($ptr) || !array_key_exists( $t, $ptr ) )
				return $this;
			
			$lp =& $ptr;
			$lk = $t;
			
			$ptr =& $ptr[$t];
		}
		unset( $lp[$lk] );
		return $this;
	}

	/**
	 * Check if value is exist by key name
	 * @param string $a_key
	 * @return bool True if value is exist
	 */
	public final function hasValue( $a_key )
	{
		if( array_key_exists( $a_key, $this->m_data ) )
			return true;

		$ptr =& $this->m_data;		 
		foreach( explode( $this->getDelimeter(), $a_key ) as $t )
		{
			if( !$ptr || !is_array($ptr) || !array_key_exists( $t, $ptr ) )
				return false;

			$ptr =& $ptr[$t];
		}
		return true;
	}
	
	/**
	 * Check if value is exist by key name
	 * @param string $a_key
	 * @return bool True if value is exist
	 */
	public function exist( $a_key )
	{
		return $this->hasValue($a_key);
	}

	/**
	 * Check if value is exist by key name
	 * @param string $a_key
	 * @return bool True if value is exist
	 */
	public function has( $a_key )
	{
		return $this->hasValue($a_key);
	}

	public function __get( $a_key )
	{
		return $this->getValue($a_key);
	}

	public function __set( $a_key, $a_value )
	{
		$this->setValue( $a_key, $a_value );
	}
	
	public final function __isset( $a_key )
	{
		return $this->hasValue($a_key);
	}
	
	public final function __unset( $a_key )
	{
		return $this->unsetValue($a_key);
	}
		
	/* Implement ArrayAccess */
	public function offsetExists($a_offset)
	{
		$this->hasValue($a_offset);
	}

	public function offsetGet( $a_offset )
	{
		return $this->getValue( $a_offset, null );
	}

	public function offsetSet($a_offset, $a_value)
	{
		$this->setValue( $a_offset, $a_value );
	}

	public function offsetUnset( $a_offset )
	{
		$this->unsetValue($a_offset);
	}
	
	public function count()
	{
		return count($this->m_data);
	}
};
