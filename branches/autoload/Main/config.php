<?php

/*
 * File: config.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Configuration class
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
class Zoombi_Config extends Zoombi_Registry
{

	/**
	 * Construct
	 * @param mixed $a_data
	 */
	public function __construct( $a_data = null )
	{
		parent::__construct();
		if(func_num_args() > 0)
			$this->setData($a_data);
	}

	/**
	 *
	 * @param string $a_filename
	 * @return Zoombi_Config
	 */
	function & fromFile( $a_filename )
	{
		if(file_exists($a_filename) AND is_file($a_filename) AND is_readable($a_filename))
		{
			$ext = strtolower(pathinfo($a_filename, PATHINFO_EXTENSION));
			switch($ext)
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
	 * @return Zoombi_Config
	 */
	function & fromPhp( $a_filename )
	{
		$_____data_____ = include($a_filename);
		if($_____data_____ !== false)
		{
			if($_____data_____ === 1)
			{
				if(isset($config))
					$this->setData($config);
			}
			else
				$this->setData($_____data_____);
		}
		return $this;
	}

	/**
	 * Load data from parsed ini file
	 * @param string $a_filename
	 * @return Zoombi_Config
	 */
	function & fromIni( $a_filename )
	{
		$ini = new Zoombi_Ini($a_filename);
		$this->setData($ini->getData());
		unset($ini);
		return $this;
	}

	/**
	 * Load data from xml file
	 * @param string $a_filename
	 * @return Zoombi_Config
	 */
	function & fromXml( $a_filename )
	{
		return $this;
	}

	/**
	 *
	 * @param Zoombi_File $a_data
	 * @return Zoombi_Config
	 */
	function & setData( $a_data )
	{
		switch(gettype($a_data))
		{
			default:
				parent::setData($a_data);
				break;

			case 'string':
				return $this->fromFile($a_data);

			case 'object':

				if($a_data instanceof Zoombi_File)
				{
					$d = parse_ini_string($a_data->get_contents(), true);
					if($d AND is_array($d))
						parent::setData($d);
				}
				parent::setData($a_data);
				break;
		}
		return $this;
	}

}
