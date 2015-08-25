<?php

/*
 * File: htmldocumentcontroller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Html document controller
 */
class Zoombi_Controller_Document_Html extends Zoombi_Controller_Document
{

	/**
	 * Constructor
	 * @param Zoombi_Object $a_parent
	 * @param string $a_name
	 */
	public function __construct( Zoombi_Object & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		$this->document = new Zoombi_Document_Html();
	}

	protected function after()
	{
		$this->document->bodySet($this->getOutput());
		parent::after();
	}

}