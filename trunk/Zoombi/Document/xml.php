<?php

/*
 * File: xml.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * XML Document
 */
class Zoombi_Document_Xml extends Zoombi_Document_Text
{

	/**
	 * DOM Document instance
	 * @var DOMDocument
	 */
	private $m_document;

	/**
	 * Child nodes instance
	 * @var array
	 */
	private $m_chlds = array();
	
	private $m_flags = array(
		'format' => false,
		'normalize' => false,
		'whitespace' => false,
		'recover' => false
	);

	/**
	 * Constructor
	 * @param string $a_type Document type
	 * @param string $a_mime Document mime
	 */
	public function __construct( $a_type = self::DOCTYPE_XML, $a_mime = 'text/xml' )
	{
		parent::__construct($a_type, $a_mime);
		$this
			->setDoctype('xml')
			->setVersion('1.0')
			->setCharset(parent::getCharset());
	}

	/**
	 * Set document version
	 * @param string $a_ver
	 * @return Zoombi_Document_Xml
	 */
	public function & setVersion( $a_ver )
	{
		$this->getDocument()->version = $a_ver;
		return $this;
	}
	
	function & setFlag( $a_name, $a_value )
	{
		$this->m_flags[ $a_name ] = (bool)$a_value;
		return $this;
	}
	
	function getFlag( $a_name )
	{
		return isset( $this->m_flags[$a_name] ) ? $this->m_flags[ $a_name ] : false;
	}
	
	function getFlags()
	{
		return $this->m_flags;
	}
	
	function & setFlags( array $a_flags )
	{
		foreach( $a_flags as $k => $v )
			$this->setFlag( $k, $v );
		
		return $this;
	}

	/**
	 * Get document version
	 * @return string
	 */
	public function & getVersion()
	{
		return $this->getDocument()->version;
	}

	/**
	 * Get or set document version
	 * @param string $a_ver Document version
	 * @return string Document version
	 */
	public function & version( $a_ver = null )
	{
		if($a_ver == null)
			return $this->getVersion();

		return $this->setVersion($a_ver);
	}

	/**
	 * Set document instance
	 * @param DOMDocument $a_document
	 * @return Zoombi_Document_Xml
	 */
	protected function & setDocument( DOMDocument & $a_document )
	{
		if($this->m_document && $this->m_document != $a_document)
			unset($this->m_document);

		$this->m_document = $a_document;
		return $this;
	}

	/**
	 * Set XmlDocument doctype
	 * @param DOMDocumentType $a_doctype
	 * @param string $a_namespace
	 * @return Zoombi_Document_Xml 
	 */
	public function & setDoctype( $a_doctype, $a_namespace = null )
	{
		switch(gettype($a_doctype))
		{
			default:
				return $this->setDocument(DOMImplementation::createDocument((string)$a_namespace, 'xml'));

			case 'string':
				return $this->setDocument(DOMImplementation::createDocument((string)$a_namespace, (string)$a_doctype));

			case 'array':
				$doctype = DOMImplementation::createDocumentType(
						(string)$a_doctype[0], (string)$a_doctype[1], (string)$a_doctype[2]
				);
				return $this->setDocument(DOMImplementation::createDocument(
							(string)$a_namespace, (string)$doctype->name, $doctype)
				);

			case 'object':
				if($a_doctype instanceof DOMDocumentType)
					return $this->setDocument(DOMImplementation::createDocument(
								(string)$a_namespace, (string)$a_doctype->name, $a_doctype
							));
				return $this;
		}
		return $this;
	}

	/**
	 * Get document instance
	 * @return DOMDocument
	 */
	public function & getDocument()
	{
		if(!$this->m_document)
			throw new Zoombi_Exception('Document not created');

		return $this->m_document;
	}

	/**
	 * Get document root element node
	 * @return Zoombi_XmlTag
	 */
	public function & getRoot()
	{
		$r = null;
		foreach($this->m_document->childNodes as $c)
		{
			if($c->nodeType == XML_ELEMENT_NODE)
			{
				$r = new Zoombi_XmlTag($c);
				break;
			}
		}

		if(!$r)
			$r = new Zoombi_XmlTag($this->m_document);

		return $r;
	}

	/**
	 * Set document encoding
	 * @param string $a_encoding
	 * @return Zoombi_Document_Xml
	 */
	public function & setCharset( $a_encoding )
	{
		parent::setCharset($a_encoding);
		//$this->m_document->actual_encoding = $a_encoding;
		return $this;
	}

	/**
	 * Append childnode to document
	 * @param Zoombi_XmlTag|DOMNode|mixed $a_tag
	 * @return Zoombi_Document_Xml
	 */
	public function & append( $a_tag )
	{
		if($a_tag instanceof Zoombi_XmlTag)
			$this->m_document->appendChild($a_tag->node());
		else if($a_tag instanceof DOMNode)
			$this->m_document->appendChild($a_tag);
		return $this;
	}

	/**
	 * Create child node
	 * @param string $a_name A node name
	 * @return Zoombi_XmlTag
	 */
	public function & create( $a_name )
	{
		$tag = $this->getDocument()->createElement($a_name);
		$p = array_push($this->m_chlds, new Zoombi_XmlTag($tag)) - 1;
		return $this->m_chlds[$p];
	}

	/**
	 * Create element node
	 * @param string $a_name A element name
	 * @param mixed $a_value Element inner data
	 * @param array $a_attr Attributes to set
	 * @return Zoombi_XmlTag
	 */
	public function & createElement( $a_name, $a_value = null, array $a_attr = array() )
	{
		$e = $this->create($a_name);

		foreach($a_attr as $k => $v)
			$e->attr($k, $v);

		if(is_array($a_value))
		{
			foreach($a_value as $k => $v)
				$e->attr($k, $v);
		}
		elseif($a_value !== null)
			$e->append($a_value);

		return $e;
	}

	/**
	 * Compile document DOM to string view
	 * @param bool $a_format Format result
	 * @return string
	 */
	public function compile( $a_format = false )
	{
		$d = parent::getData();
		if($d)
			return Zoombi_Xml::encode($d);

		$this->m_document->preserveWhiteSpace = $this->m_flags['whitespace'];
		$this->m_document->recover = $this->m_flags['recover'];
		$f = $this->m_document->formatOutput;
		$this->m_document->formatOutput = $this->m_flags['format'];

		if( $this->m_flags['normalize'] )
			$this->m_document->normalizeDocument();
		
		$return = $this->m_document->saveXML();
		$this->m_document->formatOutput = $f;

		return $return;
	}

	private function _fromObject( & $a_object, Zoombi_XmlTag & $a_root )
	{
		foreach(get_object_vars($a_object) as $k => $v)
		{
			switch(gettype($v))
			{
				default:
					$a_root->append($this->createElement($k, $v));
					break;

				case 'object':
					$this->_fromObject($v, $a_root);
					break;

				case 'array':
					$this->_fromArray($v, $a_root);
					break;
			}
		}
	}

	private function _fromArray( array & $a_array, Zoombi_XmlTag & $a_root )
	{
		foreach($a_array as $k => $v)
		{
			switch(gettype($v))
			{
				default:
					$a_root->append($this->createElement($k, $v));
					break;

				case 'object':
					$this->_fromObject($v, $a_root);
					break;

				case 'array':
					$this->_fromArray($v, $a_root);
					break;
			}
		}
	}

	/**
	 * Set document data
	 * @param mixed $a_data
	 * @return Zoombi_Document_Xml
	 */
	public function & setData( $a_data )
	{
		switch(gettype($a_data))
		{
			case 'string':
				if(file_exists($a_data) && is_readable($a_data))
				{
					$fdata = file_get_contents($a_data);
					$d = DOMDocument::loadXML($fdata);
					if($d !== false)
						$this->setDocument($d);
				}
				else
				{

					$d = DOMDocument::loadXML($a_data);
					if($d !== false)
					{
						exit($a_data);
						$this->setDocument($d);
					}
				}
				break;

			case 'array':
				$this->_fromArray($a_data, $this->getRoot());
				break;

			case 'object':
				if($a_data instanceof Zoombi_XmlTag)
					$this->setDocument(new DOMDocument)->append($a_data);
				else if($a_data instanceof Zoombi_Document_Xml)
					$this->setDocument($a_data->getDocument());
				else if($a_data instanceof DOMDocument)
					$this->setDocument($a_data);
				else if($a_data instanceof DOMNode)
					$this->setDocument(new DOMDocument)->append($a_data);
				else
					$this->_fromObject($a_data, $this->getRoot());
		}
		return $this;
	}

}
