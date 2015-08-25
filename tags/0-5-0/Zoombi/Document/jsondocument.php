<?php
/*
 * File: json.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
    return;

class ZJsonDocument extends ZTextDocument
{
	public function __construct()
	{
		parent::__construct( self::DOCTYPE_JSON,'text/json');
	}

	public function compile()
	{
		return ZJsonEncoder::encode( parent::getData() );
	}

	static public function quickOutput( $a_data )
	{
		$d = new ZJsonDocument();
		$d->setData($a_data);
		$d->output();
		unset($d);
	}
}
