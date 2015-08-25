<?php

/*
 * File: databasequeryerror.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class ZDatabaseQueryError
{

	public $code;
	public $message;
	public $query;

	public function __construct( $a_code, $a_message, $a_query )
	{
		$this->code = $a_code;
		$this->message = $a_message;
		$this->query = $a_query;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function getQuery()
	{
		return $this->query;
	}

}
