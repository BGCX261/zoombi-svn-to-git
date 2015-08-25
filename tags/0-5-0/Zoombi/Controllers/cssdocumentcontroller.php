<?php
/*
 * File: cssdocumentcontroller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Css document controller
 */
class ZCssDocumentController extends ZDocumentController
{
    /**
     * Constructor
     * @param ZObject $a_parent
     * @param string $a_name
     */
    public function __construct( ZObject & $a_parent = null, $a_name = null )
    {
        parent::__construct( $a_parent, $a_name );
        $this->document = new ZTextDocument( ZDocument::DOCTYPE_CSS, 'text/css' );
    }

	protected function after()
	{
		parent::after();
		$this->document->setData( $this->getOutput() );
		$this->setOutput( $this->document->compile() );
		$this->response
			->setContentType( $this->document->getMime() )
			->setContentEncoding( $this->document->getEncoding() );
	}
}
