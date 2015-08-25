<?php
/*
 * File: htmldocumentcontroller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Html document controller
 */
class ZHtmlDocumentController extends ZDocumentController
{
    /**
     * Constructor
     * @param ZObject $a_parent
     * @param string $a_name
     */
    public function __construct( ZObject & $a_parent = null, $a_name = null )
    {
        parent::__construct($a_parent,$a_name);
        $this->document = new ZHtmlDocument();
    }

	protected function after()
	{
		$this->document->bodySet( $this->getOutput() );
		$this->setOutput( $this->document->compile() );
		$this->response
			->setContentType( $this->document->getMime() )
			->setContentEncoding( $this->document->getEncoding() );
	}
}