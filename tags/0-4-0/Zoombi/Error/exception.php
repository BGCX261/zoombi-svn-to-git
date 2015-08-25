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

class ZFileException extends ZException
{
	const EXC_EMPTY = 12;
	const EXC_NO_FILE = 13;
	const EXC_NO_READ = 14;
}

class ZClassException extends ZFileException
{
	const EXC_CLASS = 15;
	const EXC_BASE = 16;
}

class ZLoaderException extends ZClassException
{
	const EXC_LOAD = 17;
}

/**
 * Module exception
 */
class ZModuleException extends ZLoaderException
{
	const EXC_NOT_EXIST = 18;
	const EXC_BAD_MODULE = 19;
	const EXC_NO_CONTROLLER = 20;
}

/**
 * Action exeption
 */
class ZActionException extends ZLoaderException
{

}

/**
 * Application exception
 */
class ZApplicationException extends ZLoaderException
{
	const CODE_EXIT = 21;
}

/**
 * Model exception
 */
class ZModelException extends ZLoaderException
{

}

/**
 * Controller exception
 */
class ZControllerException extends ZLoaderException
{
	const EXC_QUIT = 22;
	const EXC_QUIT_OUTPUT = 23;
	const EXC_MODEL = 24;
	const EXC_ACTION = 25;
	const EXC_VIEW = 26;
	const EXC_HELPER = 27;
	const EXC_BLOCK = 28;
	const EXC_DENY = 29;
	const EXC_AUTH = 30;
}

/**
 * View exception
 */
class ZViewException extends ZLoaderException
{
	const EXC_EMPTY = 31;
	const EXC_NOFILE = 32;
	const EXC_NOREAD = 33;
}

class ZPluginException extends ZLoaderException
{

}

class ZHelperException extends ZLoaderException
{

}

class ZDatabaseException extends ZException
{
	const EXC_OPEN = 34;
}

class ZDatabaseAdapterException extends ZDatabaseException
{
	
}

