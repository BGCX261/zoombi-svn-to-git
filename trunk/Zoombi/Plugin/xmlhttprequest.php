<?php

/*
 * File: xmlhttprequest.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


class Zoombi_Plugin_XMLHttpRequest extends Zoombi_Plugin_Application
{
	public function onOutput()
	{
		if( !$this->request->isAjax() )
			return;

		$doc = null;
		
		if($this->request->isJson())
		{
			$doc = new Zoombi_Document_Json();
		}

		if($this->request->isXml())
		{
			$doc = new Zoombi_Document_Xml();
		}

		if(!$doc)
			$doc = new Zoombi_TextDocument();
		
		$doc->setData( $this->application->getReturn() );

		$this->response->setDocument($doc);
		$this->response->addHeader('X-Response-With', 'XML Http Resquest Plugin');
	}
}