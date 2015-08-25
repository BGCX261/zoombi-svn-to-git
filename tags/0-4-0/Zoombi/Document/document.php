<?php

/*
 * File: document.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
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
	 * @var string
	 */
	private $m_document_type;
	private $m_document_mime;

	public function __construct( $a_type, $a_mime )
	{
		parent::__construct();
		$this->setType($a_type)->setMime($a_mime);
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
	 * Echo document data
	 * @return ZDocument
	 */
	public function & output( $a_encoding = 'utf-8' )
	{
		$this->outputHeader($a_encoding);
		echo $this->compile();
		return $this;
	}

	/**
	 * Send document headers
	 * @param string $a_encoding
	 * @return ZDocument
	 */
	public function & outputHeader( $a_encoding = 'utf-8' )
	{
		ZResponse::getInstance()->setContentType( $this->getMime(), $a_encoding)->output();
		return $this;
	}

	abstract public function compile();
}
