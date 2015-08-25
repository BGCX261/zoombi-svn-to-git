<?php
/*
 * File: config.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Configuration class
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
class ZConfig extends ZRegistry
{
    /**
     * Construct
     * @param mixed $a_data 
     */
	public function __construct( $a_data = null )
	{
		parent::__construct();
		switch( gettype($a_data) )
		{
			case 'string':
				$this->fromFile($a_data);
				break;
				
			case 'array':
			case 'object':
				parent::fromArray($a_data);
				break;
		}
	}

	function & fromFile( $a_filename )
	{
		if( file_exists($a_filename) AND is_file($a_filename) AND is_readable($a_filename) )
		{
			$ext = strtolower( pathinfo( $a_filename, PATHINFO_EXTENSION ) );
			switch( $ext )
			{
				case 'php':
					$this->fromPhp($a_filename);
					break;

				case 'ini':
					$this->fromIni($a_filename);
					break;

				case 'xml':
					$this->fromXml($a_filename);
			}
		}
		return $this;
	}

	/**
     * Load config from array returned by php file
     * @param string $a_filename
     * @return ZConfig
     */
	function & fromPhp( $a_filename )
	{
		$data = include($a_filename);
		if( $data !== false )
		{
			if( $data == 1 )
			{
				if( isset($config) )
					parent::setData($config);
			}
			else parent::setData($data);
		}
		return $this;
	}

    /**
     * Load data from parsed ini file
     * @param string $a_filename
     * @return ZConfig
     */
	function & fromIni( $a_filename )
	{
		$ini = new ZIni( $a_filename );
		parent::setData( $ini->getData() );
		unset($ini);
		return $this;
	}

    /**
     * Load data from xml file
     * @param string $a_filename
     * @return ZConfig
     */
	function & fromXml( $a_filename )
	{
		return $this;		
	}
}
