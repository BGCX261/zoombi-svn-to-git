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
	private $m_cache;
	
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

	/**
	 * Constuct responce object
	 */
	public function __construct()
	{
		$this->m_code = 200;
		$this->m_headers = array( );
		$this->m_type = '';
	}

	/**
	 * Append header to responce
	 * @return ZResponse
	 */
	public function & addHeader( $arg0, $arg1 = null )
	{
		$h = null;
		switch( func_num_args() )
		{
			case 1:
				$h = new ZHeader( func_get_arg(0) );
				break;
			
			case 2:
				$h = new ZHeader;
				$h->setKey(func_get_arg(0));
				$h->setValue(func_get_arg(1));
				break;
		}
		if( $h )
			$this->m_headers[trim(strtolower($h->getKey()))] = $h;

		return $this;
	}

	/**
	 * Append header to responce
	 * @return ZResponse
	 */
	public function & setHeader( $a_key, $a_value )
	{
		return $this->addHeader( $a_key, $a_value );
	}

	/**
	 * Set content type
	 * @param string $a_type Mime type of content
	 * @param string $a_encoding Encoding of content
	 * @return ZResponse
	 */
	public function & setContentType( $a_type, $a_encoding = null )
	{
		$this->m_type = (string)$a_type;
		if( $a_encoding )
			return $this->setContentEncoding( $a_encoding );

		return $this;
	}

	/**
	 * Get responce content type
	 * @return string
	 */
	public function getContentType()
	{
		return $this->m_type;
	}

	/**
	 * Set responce content encoding
	 * @param  $a_encoding
	 * @return ZResponse
	 */
	public function & setContentEncoding( $a_encoding )
	{
		if( $a_encoding )
			$this->m_encoding = (string)$a_encoding;

		return $this;
	}

	/**
	 * Get responce content encoding
	 * @return string
	 */
	public function getContentEncoding()
	{
		return $this->m_encoding;
	}

	/**
	 * Add headers to responce
	 * @param array $a_headers
	 * @return ZResponse
	 */
	public function & addHeaders( array $a_headers )
	{
		foreach( $a_headers as $header )
			$this->addHeader($header);

		return $this;
	}

	/**
	 * Set responce headers
	 * @param array $a_headers
	 * @return ZResponse
	 */
	public function & setHeaders( array $a_headers )
	{
		return $this->clearHeaders()->addHeaders($a_headers);
	}

	/**
	 * Clear responce headers
	 * @return ZResponse
	 */
	public function & clearHeaders()
	{
		$this->m_headers = array( );
		return $this;
	}

	/**
	 * Set responce content
	 * @param string $a_content
	 * @return ZResponse
	 */
	public function & setContent( $a_content )
	{
		$this->m_content = strval($a_content);
		return $this;
	}

	/**
	 * Get responce content
	 * @return string
	 */
	public function getContent()
	{
		return $this->m_content;
	}

	public function getContentLength()
	{
		return strlen( $this->m_content );
	}

	/**
	 * Append some data to responce content
	 * @param string $a_content
	 * @return ZResponse
	 */
	public function & appendContent( $a_content )
	{
		$this->m_content = $this->m_content . strval($a_content);
		return $this;
	}

	/**
	 * Preppend some data to content
	 * @param string $a_content
	 * @return ZResponse
	 */
	public function & prependContent( $a_content )
	{
		$this->m_content = strval($a_content) . $this->m_content;
		return $this;
	}

	/**
	 * Set responce code
	 * @param int|string $a_code
	 * @param null|string $a_message
	 * @return ZResponse
	 */
	public function & setCode( $a_code, $a_message = null )
	{
		$this->m_code = intval($a_code);
		if( $a_message )
			$this->m_message = $a_message;

		return $this;
	}

	/**
	 * Set responce message
	 * @param string $a_message
	 * @return ZResponse
	 */
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

	/**
	 * Output responce
	 */
	public function output()
	{
		if( empty($this->m_type) )
			$this->m_type = 'text/plain';

		$this->addHeader('Content-Type', $this->m_type . ( $this->m_encoding ? '; charset='.$this->m_encoding : null ) );

		if( $this->m_cache == false )
		{
			$this->addHeaders( array(
				'Date' => gmdate( DATE_RFC822, time() ) . ' GMT',
				'Expires' =>  'Fri, 01 Jan 1990 00:00:00 GMT',
				'Pragma' => 'no-cache',
				'Cache-Control' => 'no-cache, must-revalidate'
			));
		}

		if( $this->m_code == 302 || $this->m_cache == 307 )
		{
			$this->addHeaders( array(
				'Date' => null,
				'Expires' =>  null,
				'Pragma' => null,
				'Cache-Control' => null
			));
		}

		$this->addHeader('Content-length', $this->getContentLength() );
		ZHeaders::setCode($this->m_code, $this->m_message);
		
		foreach( $this->m_headers as $h )
			$h->send();
		
		echo $this->m_content;
	}

	public function getCache()
	{
		return $this->m_cache;
	}

	public function & setCache( $a_flag )
	{
		$this->m_cache = (bool)$a_flag;
		return $this;
	}

	public function & doCache()
	{
		$this->m_cache = true;
		return $this;
	}

	public function & noCache()
	{
		$this->m_cache = false;
		return $this;
	}

}
