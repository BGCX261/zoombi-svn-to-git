<?php

class ZXMLHttpRequestPlugin extends ZApplicationPlugin
{

	public function onOutput()
	{
		if(!$this->request->isAjax())
			return;

		$doc = null;

		if($this->request->isJson())
		{
			$doc = new ZJsonDocument();
			$doc->setData($this->application->getReturn());
		}

		if($this->request->isXml())
		{
			$doc = new ZXmlDocument();
			$doc->setData($this->application->getReturn());
		}

		if(!$doc)
			$doc = new ZDocument;

		$this->response->setDocument($doc);
		$this->response->addHeader('X-Response-With', 'XML Http Resquest Plugin');
	}

}