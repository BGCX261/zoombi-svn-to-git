<?php

/*
 * File: response.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 *
 */
class ZResponse implements IZSingleton
{

	private $m_headers;
	private $m_content;
	private $m_code;
	private $m_message;
	private $m_type;
	private $m_encoding;
	
	/**
	 * ZResponse singleton
	 * @var ZResponse
	 */
	static private $m_instance;
	private function __clone()
	{
		
	}

	/**
	 * Get ZResponse instance
	 * @return ZResponse
	 */
	static public final function & getInstance()
	{
		if( self::$m_instance === null )
			self::$m_instance = new self;
		return self::$m_instance;
	}

	public function __construct()
	{
		$this->m_code = 200;
		$this->m_headers = array( );
		$this->m_type = '';
	}

	public function & addHeader( $a_header )
	{
		$this->m_headers[] = new ZHeader($a_header);
		return $this;
	}

	public function & setHeader( $a_key, $a_value )
	{
		return $this->addHeader(func_get_args());
	}

	public function & setContentType( $a_type, $a_encoding = null )
	{
		$this->m_type = $a_type;
		if( $a_encoding )
			return $this->setContentEncoding( $a_encoding );

		return $this;
	}

	public function getContentType()
	{
		return $this->m_type;
	}

	public function & setContentEncoding( $a_encoding )
	{
		if( $a_encoding )
			$this->m_encoding = $a_encoding;

		return $this;
	}

	public function getContentEncoding()
	{
		return $this->m_encoding;
	}

	public function & addHeaders( array $a_headers )
	{
		foreach( $a_headers as $header )
			$this->addHeader($header);

		return $this;
	}

	public function & setHeaders( array $a_headers )
	{
		return $this->clearHeaders()->addHeaders($a_headers);
	}

	public function & clearHeaders()
	{
		$this->m_headers = array( );
		return $this;
	}

	public function & setContent( $a_content )
	{
		$this->m_content = strval($a_content);
		return $this;
	}

	public function getContent()
	{
		return $this->m_content;
	}

	public function & setCode( $a_code, $a_message = null )
	{
		$this->m_code = intval($a_code);
		if( $a_message )
			$this->m_message = $a_message;

		return $this;
	}

	public function & setMessage( $a_message )
	{
		$this->m_message = $a_message;
		return $this;
	}

	public function  __call( $a_name, $a_arguments )
	{
		switch( $a_name )
		{
			case 'sent':
			case 'send':
			case 'send_headers':
			case 'sent_headers':
				return $this->output();
		}
	}

	public function output()
	{
		if( empty($this->m_type) )
			$this->m_type = 'text/plain';

			$this->addHeader('Content-Type', $this->m_type . ( $this->m_encoding ? '; charset='.$this->m_encoding : null ) );

		ZHeaders::setCode($this->m_code, $this->m_message);

		foreach( $this->m_headers as $h )
			$h->send();

		echo $this->getContent();
	}

}
