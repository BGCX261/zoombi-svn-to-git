<?php

/*
 * File: header.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * Http header class
 *
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
class ZHeader
{

	/**
	 *
	 * @var string Header key
	 */
	private $m_key;

	/**
	 *
	 * @var string Header value
	 */
	private $m_value;

	/**
	 * constructor
	 * @param array|string Header data
	 */
	public function __construct( $a_data = null )
	{
		switch(gettype($a_data))
		{
			case 'array':
				if(isset($a_data['key']))
					$this->m_key = $a_data['key'];
				else if(isset($a_data[0]))
					$this->m_key = $a_data[0];

				if(isset($a_data['value']))
					$this->m_value = $a_data['value'];
				else if(isset($a_data[1]))
					$this->m_value = $a_data[1];

				break;

			case 'string':
				if(stripos(':', $a_data) !== false)
				{
					list( $this->m_key, $this->m_value ) = explode(':', $a_data, 1);
					$this->m_key = trim($this->m_key);
					$this->m_value = trim($this->m_value);
				}
				break;
		}
	}

	/**
	 * Get header key
	 * @return string
	 */
	public function getKey()
	{
		return $this->m_key;
	}

	/**
	 * Get header value
	 * @return string
	 */
	public function getValue()
	{
		return $this->m_value;
	}

	/**
	 * Remove self from responce headers
	 */
	public final function remove()
	{
		ZHeaders::removeHeader($this->m_key);
	}

	/**
	 * Apply self to responce headers
	 */
	public final function assign()
	{
		ZHeaders::setHeader($this->m_key, $this->m_value);
	}

	/**
	 * Apply self to responce headers
	 */
	public final function sent()
	{
		ZHeaders::setHeader($this->m_key, $this->m_value);
	}

	/**
	 * Apply self to responce headers
	 */
	public final function send()
	{
		ZHeaders::setHeader($this->m_key, $this->m_value);
	}

	/**
	 * Set object property
	 * @param string $a_name
	 * @param string $a_value
	 * @return ZHeader
	 */
	public function __set( $a_name, $a_value )
	{
		switch($a_name)
		{
			case 'key':
				return $this->setKey($a_value);

			case 'value':
				return $this->setValue($a_value);
		}
		return $this;
	}

	/**
	 * Get object property
	 * @param string $a_name
	 * @return string
	 */
	public function __get( $a_name )
	{
		switch($a_name)
		{
			case 'key':
				return $this->getKey();

			case 'value':
				return $this->getValue();
		}
	}

	public function __isset( $a_name )
	{
		switch($a_name)
		{
			case 'key':
			case 'value':
				return true;

			default:
				return false;
		}
	}

	public function & setKey( $a_key )
	{
		$this->m_key = $a_key;
		return $this;
	}

	public function & setValue( $a_value )
	{
		$this->m_value = $a_value;
		return $this;
	}

}