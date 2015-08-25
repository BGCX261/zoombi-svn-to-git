<?php
/*
 * File: ini.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * INI File implementation from
 * unoficial specefication at
 * http://www.cloanto.com/specs/ini.html
 */
class ZIni extends ZRegistry
{
	/**
	 * Ini file name
	 *
	 * @var string
	 */
	private $m_filename;

	/**
	 * Constructor with filename and options
	 *
	 * @param string $a_filename Set ini filename
	 */
	public function __construct( $a_filename = null )
	{
		if( $a_filename )
			$this->open($a_filename);
	}

	/**
	 * Open ini file
	 *
	 * @param string $a_filename File name
	 * @return boolean True if file open successful
	 */
	public function open( $a_filename )
	{
		if( !file_exists($a_filename) )
			return false;

		$this->m_filename = $a_filename;
		$this->setData( parse_ini_file( $this->m_filename, true) );
			return true;
	}

	public function load( $a_filename )
	{
		return $this->open( $a_filename );
	}

	/**
	 * Check a section existing
	 *
	 * @param string $a_section Section
	 * @return bool True if section is exist else return false
	 */
	public final function hasSection( $a_section )
	{
		return $this->hasValue( $a_section );
	}

	/**
	 * Get path to ini file
	 * @return string Path
	 */
	public function path()
	{
		return dirname( $this->m_filename );
	}
}
