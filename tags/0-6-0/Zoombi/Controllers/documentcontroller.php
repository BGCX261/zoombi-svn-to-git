<?php

/*
 * File: documentcontroller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * Base docement controller
 */
abstract class ZDocumentController extends ZController
{

	/**
	 * Document instance
	 * @var ZDocument
	 */
	public $document;

	protected function after()
	{
		$this->setOutput($this->document->compile());
		ZResponse::getInstance()
			->setContentType($this->document->getMime())
			->setContentCharset($this->document->getCharset());
	}

}
