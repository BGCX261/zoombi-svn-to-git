<?php

/*
 * File: exception.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Base exception
 */
class Zoombi_Exception extends Exception
{
	const EXC_NO_PROPERTY = 10;
	const EXC_NO_METHOD = 11;

	function setCode( $a_code )
	{
		$this->code = $a_code;
	}

	function setMessage( $a_message )
	{
		$this->message = $a_message;
	}

	function setLine( $a_line )
	{
		$this->line = $a_line;
	}

	function setFile( $a_file )
	{
		$this->file = $a_file;
	}

	function setTrace( array $a_trace )
	{
		$this->trace = $a_trace;
	}

}