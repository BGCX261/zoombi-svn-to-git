<?php
/*
 * File: xmldocumentcontroller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Xml document controller
 */
class ZSitemapIndexDocumentController extends ZDocumentController
{
    /**
     * Constructor
     * @param ZObject $a_parent
     * @param string $a_name
     */
    public function __construct( ZObject & $a_parent = null, $a_name = null )
    {
        parent::__construct($a_parent,$a_name);
        $this->document = new ZSiteMapIndexDocument();
    }
}
