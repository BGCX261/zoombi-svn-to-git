<?php

/*
 * File: exception.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Base exception
 */
class ZException extends Exception
{

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

}

/**
 * Action exeption
 */
class ZActionException extends ZException
{

}

/**
 * Application exception
 */
class ZApplicationException extends ZException
{
	const CODE_EXIT = 1;
}

/**
 * Loader exception
 */
class ZLoaderException extends ZException
{

}

/**
 * Model exception
 */
class ZModelException extends ZException
{

}

/**
 * Controller exception
 */
class ZControllerException extends ZException
{
	const EXC_QUIT = 10;
	const EXC_QUIT_OUTPUT = 20;
	const EXC_MODEL = 100;
	const EXC_ACTION = 200;
	const EXC_VIEW = 300;
	const EXC_HELPER = 400;
}

/**
 * View exception
 */
class ZViewException extends ZException
{
	const EXC_EMPTY = 301;
	const EXC_NOFILE = 302;
	const EXC_NOREAD = 303;
}

class ZPluginException extends ZException
{

}

class ZHelperException extends ZException
{

}

class ZDatabaseException extends ZException
{

}

class ZDatabaseAdapterException extends ZDatabaseException
{

}

