<?php

/*
 * File: json.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


class Zoombi_Document_Json extends Zoombi_Document_Text
{

	public function __construct()
	{
		parent::__construct(self::DOCTYPE_JSON, 'application/json');
	}

	public function compile()
	{
		return Zoombi_Json::encode(parent::getData());
	}

	static public function quickOutput( $a_data )
	{
		$d = new Zoombi_Document_Json();
		$d->setData($a_data);
		$d->output();
		unset($d);
	}

}
