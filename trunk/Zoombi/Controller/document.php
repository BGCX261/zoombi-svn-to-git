<?php

/*
 * File: documentcontroller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Base docement controller
 */
abstract class Zoombi_Controller_Document extends Zoombi_Controller
{

	/**
	 * Document instance
	 * @var Zoombi_Document
	 */
	public $document;

	protected function after()
	{
		$this->setOutput($this->document->compile());
		Zoombi_Response::getInstance()
			->setContentType($this->document->getMime())
			->setContentCharset($this->document->getCharset());
	}

}
