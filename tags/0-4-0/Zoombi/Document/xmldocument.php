<?php
/*
 * File: xml.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * XML Document
 */
class ZXmlDocument extends ZTextDocument
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

    /**
     * Constructor
     * @param string $a_type Document type
     * @param string $a_mime Document mime
     */
    public function __construct( $a_type = self::DOCTYPE_XML, $a_mime = 'text/xml' )
    {
        parent::__construct( $a_type, $a_mime );
        $this->m_document = new DOMDocument('1.0','utf-8');
        //$this->setVersion('1.0');->setEncoding('utf-8');
    }

    /**
     * Set document version
     * @param string $a_ver
     * @return ZXmlDocument
     */
	public function & setVersion( $a_ver )
	{
        $this->m_document->version = $a_ver;
        return $this;
	}

    /**
     * Get document version
     * @return string
     */
	public function & getVersion()
	{
		return $this->m_document->actual_encoding;
	}

    /**
     * Get or set document version
     * @param string $a_ver Document version
     * @return string Document version
     */
	public function & version( $a_ver = null )
	{
		if( $a_ver == null )
            return $this->getVersion();

		return $this->setVersion($a_ver);
	}

    /**
     * Set document instance
     * @param DOMDocument $a_document
     * @return ZXmlDocument
     */
	protected function & setDocument( DOMDocument & $a_document )
	{
		if( $this->m_document != $a_document )
            unset($this->m_document);

		$this->m_document = $a_document;
        return $this;
	}

    /**
     * Get document instance
     * @return DOMDocument
     */
	public function & getDocument()
	{
		return $this->m_document;
	}

    /**
     * Get document root element node
     * @return ZXmlTag
     */
	public function & getRoot()
	{
        $r = null;
		foreach( $this->m_document->childNodes as $c )
		{
			if( $c->nodeType == XML_ELEMENT_NODE )
			{
                $r = new ZXmlTag($c);
				break;
			}
		}

		if( !$r )
			$r = new ZXmlTag( $this->m_document );

        return $r;
	}

    /**
     * Get document encoding
     * @return string
     */
    public function & getEncoding()
    {
        return $this->m_document->actual_encoding;
    }

    /**
     * Set document encoding
     * @param string $a_encoding
     * @return ZXmlDocument
     */
    public function & setEncoding( $a_encoding )
    {
        $this->m_document->encoding = $a_encoding;
        return $this;
    }

    /**
     * Get or set document encoding
     * @param string $a_ecoding Document encoding to set
     * @return string Document encoding
     */
    public function & encoding( $a_ecoding = null )
	{
		if( $a_ecoding == null )
            return $this->getEncoding();

        return $this->setEncoding($a_encoding);
	}

	/**
	 * Append childnode to document
	 * @param ZXmlTag|DOMNode|mixed $a_tag
     * @return ZXmlDocument
	 */
	public function & append( $a_tag )
	{
		if( $a_tag instanceof ZXmlTag )
            $this->m_document->appendChild( $a_tag->node() );
		else if ( $a_tag instanceof DOMNode )
            $this->m_document->appendChild( $a_tag );
        return $this;
	}

    /**
     * Create child node
     * @param string $a_name A node name
     * @return ZXmlTag
     */
	public function & create( $a_name )
	{
		$tag = $this->m_document->createElement($a_name);
        $p = array_push( $this->m_chlds, new ZXmlTag($tag) ) - 1;
		return $this->m_chlds[$p];
	}

    /**
     * Create element node
     * @param string $a_name A element name
     * @param mixed $a_value Element inner data
     * @param array $a_attr Attributes to set
     * @return ZXmlTag
     */
	public function & createElement( $a_name, $a_value = null, $a_attr = array() )
	{
		$e = $this->create($a_name);
        foreach( $a_attr as $k => $v )
            $e->attr( $k , $v);

		if( $a_value )
            $e->append( $a_value );

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
		if( $d )
			return ZXmlEncoder::encode($d);

		$this->m_document->preserveWhiteSpace = false;
		$this->m_document->recover = false;
		$f = $this->m_document->formatOutput;
		$this->m_document->formatOutput = (bool)$a_format;

		$this->m_document->normalizeDocument();
		$return = $this->m_document->saveXML();
		$this->m_document->formatOutput = $f;

		return $return;
	}

	private function _fromObject( & $a_object, ZXmlTag & $a_root )
	{
		foreach( get_object_vars($a_object) as $k => $v )
		{
			switch( gettype($v) )
			{
				default:
					$a_root->append( $this->createElement($k,$v) );
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

	private function _fromArray( array & $a_array, ZXmlTag & $a_root )
	{
		foreach( $a_array as $k => $v )
		{
			switch( gettype($v) )
			{
				default:
					$a_root->append( $this->createElement($k,$v) );
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
     * @return ZXmlDocument
     */
	public function & setData( $a_data )
	{
        switch( gettype($a_data) )
        {
            case 'string':
                if( file_exists($a_data) && is_readable($a_data) )
                {
                    $fdata = file_get_contents($a_data);
                    $this->setDocument( DOMDocument::loadXML($fdata) );
                }
                else
                {
                    $this->setDocument( DOMDocument::loadXML($a_data) );
                }
                break;

			case 'array':
				$this->_fromArray($a_data, $this->getRoot() );
				break;

            case 'object':
                if( $a_data instanceof ZXmlTag )
                    $this->setDocument( new DOMDocument )->append( $a_data );
                else if( $a_data instanceof ZXmlDocument )
                    $this->setDocument( $a_data->getDocument() );
                else if( $a_data instanceof DOMDocument )
                    $this->setDocument( $a_data );
                else if( $a_data instanceof DOMNode )
                    $this->setDocument( new DOMDocument )->append( $a_data );
                else
					$this->_fromObject($a_data, $this->getRoot());
        }
        return $this;
	}
}
