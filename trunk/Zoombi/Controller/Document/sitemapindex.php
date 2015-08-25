<?php

/*
 * File: xmldocumentcontroller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Xml document controller
 */
class Zoombi_Controller_Document_SitemapIndex extends Zoombi_Controller_Document
{

	/**
	 * Constructor
	 * @param Zoombi_Object $a_parent
	 * @param string $a_name
	 */
	public function __construct( Zoombi_Object & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		$this->document = new Zoombi_Document_SiteMapIndex();
	}

}
