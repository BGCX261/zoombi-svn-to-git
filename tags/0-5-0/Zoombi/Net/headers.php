<?php
/*
 * File: header.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
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
		switch ( gettype( $a_data ) )
		{
			case 'array':
				if( isset( $a_data['key'] ) )
					$this->m_key = $a_data['key'];
				else if( isset( $a_data[0] ) )
					$this->m_key = $a_data[0];

				if( isset( $a_data['value'] ) )
					$this->m_value = $a_data['value'];
				else if( isset( $a_data[1] ) )
					$this->m_value = $a_data[1];

				break;

			case 'string':
				if( stripos(':', $a_data) !== false )
				{
					list( $this->m_key, $this->m_value ) = explode( ':', $a_data, 1 );
					$this->m_key    = trim( $this->m_key );
					$this->m_value  = trim( $this->m_value );
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
		ZHeaders::removeHeader( $this->m_key );
	}

	/**
	 * Apply self to responce headers
	 */
	public final function assign()
	{
		ZHeaders::setHeader( $this->m_key, $this->m_value );
	}

	/**
	 * Set object property
	 * @param string $a_name
	 * @param string $a_value
	 * @return ZHeader
	 */
	public function __set( $a_name, $a_value )
	{
		switch( $a_name )
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
		switch( $a_name )
		{
			case 'key':
				return $this->getKey();

			case 'value':
				return $this->getValue();
		}
	}

	public function  __call( $a_name, $a_arguments )
	{
		switch ( $a_name )
		{
			case 'sent':
			case 'send':
				$this->assign();
		}
	}

	public function __isset( $a_name )
	{
		switch( $a_name )
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

/**
 * Headers
 */
class ZHeaders
{
	static public final function hasHeader( $a_name )
	{
		foreach( headers_list() as $header )
		{
			if( stripos((string)$a_name . ':', $header) !== false )
			return $header;
		}
	}

	static public final function sent()
	{
		return headers_sent();
	}

	static public final function isSent()
	{
		return headers_sent();
	}

	static public final function setCode( $a_key, $a_message = 'OK' )
	{
		$t = $a_key;
		if( !empty($a_message) )
			$t .= ' ' . strval($a_message);
		
		header('HTTP/1.1 ' . $t );
	}

	static public final function setHeader( $a_key, $a_value )
	{
		header( $a_key . ':' . $a_value, true );
	}

	static public final function getHeader( $a_name )
	{
		$h = self::hasHeader( $a_name );
		return $h ? new ZHeader($h) : null;
	}

	static public final function removeHeader( $a_name = null )
	{
		if( $a_name !== null AND function_exists('header_remove') )
			header_remove( $a_name );
	}

	static public final function removeAll()
	{
		if( function_exists('header_remove') )
			header_remove();
	}
}
