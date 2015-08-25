<?php

/*
 * File: document.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * Base class of ZHtmlDocument, ZXmlDocument and etc...
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
abstract class ZDocument extends ZNode
{
	/*
	 * Document types
	 */
	const DOCTYPE_NONE = null;
	const DOCTYPE_RAW = 'raw';
	const DOCTYPE_JS = 'js';
	const DOCTYPE_JSON = 'json';
	const DOCTYPE_CSS = 'css';
	const DOCTYPE_YML = 'yml';
	const DOCTYPE_SQL = 'sql';
	const DOCTYPE_TEXT = 'text';
	const DOCTYPE_XML = 'xml';
	const DOCTYPE_RSS = 'rss';
	const DOCTYPE_ATOM = 'atom';
	const DOCTYPE_HTML = 'html';
	const DOCTYPE_PDF = 'pdf';
	const DOCTYPE_SITEMAPINDEX = 'sitemapindex';
	const DOCTYPE_SITEMAPURLSET = 'sitemapurlset';

	/**
	 * Document type
	 * @var string
	 */
	private $m_document_type;

	/**
	 * Document mime
	 * @var string
	 */
	private $m_document_mime;

	/**
	 * Document charset
	 * @var string
	 */
	private $m_charset;

	public function __construct( $a_type, $a_mime )
	{
		parent::__construct();
		$this
			->setType($a_type)
			->setMime($a_mime)
			->setCharset('utf-8');
	}

	/**
	 * Get document type
	 * @return string
	 */
	public final function getType()
	{
		return $this->m_document_type;
	}

	/**
	 * Set document type
	 * @param string $a_type
	 * @return ZDocument
	 */
	public final function & setType( $a_type )
	{
		$this->m_document_type = (string)$a_type;
		return $this;
	}

	/**
	 * Get document mime type
	 * @return string
	 */
	public final function getMime()
	{
		return $this->m_document_mime;
	}

	/**
	 * Set document mime type
	 * @param string $a_mime
	 * @return ZDocument
	 */
	public final function & setMime( $a_mime )
	{
		$this->m_document_mime = $a_mime;
		return $this;
	}

	/**
	 * Get document encoding
	 * @return string
	 */
	public function & getCharset()
	{
		return $this->m_charset;
	}

	/**
	 * Set document encoding
	 * @return ZTextDocument
	 */
	public function & setCharset( $a_charset )
	{
		$this->m_charset = trim($a_charset);
		return $this;
	}

	/**
	 * Get or set document encoding
	 * @param string $a_ecoding Document encoding to set
	 * @return string Document encoding
	 */
	public function & charset( $a_charset = null )
	{
		if(func_num_args() == 0)
			return $this->getCharset();

		return $this->setCharset($a_charset);
	}

	/**
	 * Echo document data
	 * @return ZDocument
	 */
	public function & output( $a_encoding = null )
	{
		if($a_encoding)
			$this->setCharset($a_encoding);

		ZResponse::getInstance()
			->setContentType($this->getMime())
			->setContentCharset($this->getCharset());

		echo $this->compile();
		return $this;
	}

	abstract public function compile();
}
