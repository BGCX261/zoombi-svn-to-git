<?php
/*
 * File: header.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

class ZHeader
{
	private $m_key;
	private $m_value;

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

	public function getKey()
	{
		return $this->m_key;
	}

	public function getValue()
	{
		return $this->m_value;
	}

	public final function remove()
	{
		ZHeaders::removeHeader( $this->m_key );
	}
	
	public final function assign()
	{
		ZHeaders::setHeader( $this->m_key, $this->m_value );
	}
}

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

	static public final function setCode( $a_key, $a_message = 'OK' )
	{
		$t = intval($a_key) . ' ' . (string)$a_message;
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
		if( $a_name !== null )
		header_remove( $a_name );
	}

	static public final function removeAll()
	{
		header_remove(null);
	}
}
