<?php

/*
 * File: response.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
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
	private $m_cache;
	private $m_charset;

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
		if(self::$m_instance === null)
			self::$m_instance = new self;
		return self::$m_instance;
	}

	/**
	 * Constuct responce object
	 */
	public function __construct()
	{
		$this->m_code = 200;
		$this->m_message = 'OK';
		$this->m_headers = array();
		$this->m_type = 'text/plain';
		$this->m_charset = 'utf-8';
		$this->m_cache = false;
	}

	public function & getHeader( $a_name )
	{
		$name = trim(strtolower($a_name));
		$r = null;
		if(array_key_exists($name, $this->m_headers))
			$r = $this->m_headers[$name];

		return $r;
	}

	/**
	 * Append header to responce
	 * @return ZResponse
	 */
	public function & addHeader( $a_header )
	{
		$h = null;
		switch(func_num_args())
		{
			case 1:
				$h = new ZHeader(func_get_arg(0));
				break;

			case 2:
				$h = new ZHeader(func_get_args());
				break;
		}

		if($h)
			$this->m_headers[trim(strtolower($h->getKey()))] = $h;

		return $this;
	}

	/**
	 * Append header to responce
	 * @return ZResponse
	 */
	public function & setHeader( $a_key, $a_value )
	{
		return $this->addHeader($a_key, $a_value);
	}

	/**
	 * Set content type
	 * @param string $a_type Mime type of content
	 * @param string $a_encoding Encoding of content
	 * @return ZResponse
	 */
	public function & setContentType( $a_type )
	{
		$this->m_type = strval($a_type);
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

	public function getContentCharset()
	{
		return $this->m_charset;
	}

	public function & setContentCharset( $a_charset )
	{
		$this->m_charset = $a_charset;
		return $this;
	}

	public function setDocument( ZDocument & $a_document )
	{
		$this->setContentType($a_document->getMime());
		$this->setContent($a_document->compile());
		$this->setEncoding($a_document->getCharset());
	}

	public function setEncoding( $a_encoding )
	{
		$this->m_charset = trim($a_encoding);
		return $this;
	}

	/**
	 * Get responce content encoding
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->m_charset;
	}

	/**
	 * Add headers to responce
	 * @param array $a_headers
	 * @return ZResponse
	 */
	public function & addHeaders( array $a_headers )
	{
		foreach($a_headers as $key => $value)
		{
			if(is_numeric($key))
				$this->addHeader($value);
			else
				$this->addHeader($key, $value);
		}

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
		$this->m_headers = array();
		return $this;
	}

	/**
	 * Set responce content
	 * @param string $a_content
	 * @return ZResponse
	 */
	public function & setContent( $a_content )
	{
		$this->m_content = $a_content;
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

	/**
	 * Get responce status code
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->m_code;
	}

	/**
	 * Get responce status message
	 * @return string
	 */
	public function getStatusMessage()
	{
		return $this->m_message;
	}

	/**
	 * Get responce content length
	 * @return int
	 */
	public function getContentLength()
	{
		return strlen($this->m_content);
	}

	/**
	 * Append some data to responce content
	 * @param string $a_content
	 * @return ZResponse
	 */
	public function & appendContent( $a_content )
	{
		$this->m_content .= $a_content;
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
		$this->setStatusCode($a_code);

		if($a_message)
			$this->setStatusMessage($a_message);

		return $this;
	}

	/**
	 * Set responce code
	 * @param int|string $a_code
	 * @param null|string $a_message
	 * @return ZResponse
	 */
	public function & setStatus( $a_code, $a_message = null )
	{
		return $this->setCode($a_code, $a_message);
	}

	/**
	 * Set responce statuc code
	 * @param int $a_code
	 * @return ZResponse
	 */
	public function & setStatusCode( $a_code )
	{
		$this->m_code = intval($a_code);
		return $this;
	}

	/**
	 * Set responce status message
	 * @param string $a_message
	 * @return ZResponse
	 */
	public function & setStatusMessage( $a_message )
	{
		$this->m_message = strval($a_message);
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

	public function __call( $a_name, $a_arguments )
	{
		switch($a_name)
		{
			case 'sent':
			case 'send':
			case 'send_headers':
			case 'sent_headers':
				return $this->output();
		}
	}

	/**
	 * Send header now
	 * @return ZResponse
	 */
	public function & send_headers()
	{
		ZHeaders::setCode($this->m_code, $this->m_message);
		foreach($this->m_headers as $h)
			$h->send();

		return $this;
	}

	/**
	 * Output responce
	 */
	public function output()
	{
		if(headers_sent())
			return;

		if(!$this->m_type OR empty($this->m_type))
			$this->setContentType('text/html');

		$ct = $this->getContentType();
		if($this->m_charset)
			$ct .= '; charset=' . $this->m_charset;

		$this->setHeader('Content-Type', $ct);

		ob_clean();

		$this->send_headers();
		flush();
		echo $this->m_content;
		flush();
	}

	/**
	 * Get cache is enabled
	 * @return bool
	 */
	public function getCache()
	{
		return $this->m_cache;
	}

	/**
	 * Set responce cache
	 * @param bool $a_flag
	 * @return ZResponse
	 */
	public function & setCache( $a_flag )
	{
		$this->m_cache = (bool)$a_flag;
		return $this;
	}

	/**
	 * Enable responce cache
	 * @return ZResponse
	 */
	public function & doCache()
	{
		$this->m_cache = true;
		return $this;
	}

	/**
	 * Disable responce cache
	 * @return ZResponse
	 */
	public function & noCache()
	{
		$this->m_cache = false;
		return $this;
	}

	/**
	 * Magix
	 * @param string $a_name
	 * @param mixed $a_value
	 */
	public function __set( $a_name, $a_value )
	{
		switch(strtolower($a_name))
		{
			case 'statuscode':
			case 'status':
			case 'code':
				$this->setStatusCode($a_value);
				break;

			case 'statusmessage':
			case 'message':
				$this->setStatusMessage($a_value);
				break;

			case 'type':
			case 'mime':
				$this->setContentType($a_value);
				break;

			case 'cache':
				$this->setCache($a_value);
				break;

			default:
				$this->addHeader($a_name, $a_value);
				break;
		}
	}

	public function __get( $a_name )
	{
		switch(strtolower($a_name))
		{
			case 'type':
			case 'mime':
				return $this->getContentType();

			case 'cache':
				return $this->getCache();

			case 'statuscode':
			case 'status':
			case 'code':
				return $this->getStatusCode();

			case 'message':
			case 'statusmessage':
				return $this->getStatusMessage();

			default:
				return $this->getHeader($a_name);
		}
	}

}
