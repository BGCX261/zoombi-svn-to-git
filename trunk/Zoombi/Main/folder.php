<?php

/*
 * File: folder.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

class Zoombi_Folder
{

	private $m_dir;

	public function __construct( $a_dir = null )
	{
		$this->m_dir = $a_dir;
	}

	static public function exist( $a_dir )
	{
		if(isset($this))
			return Zoombi_Folder::exist($this->m_dir);

		return is_dir($a_dir);
	}

	static public function notexist( $a_dir )
	{
		return!Zoombi_Folder::exist($a_dir);
	}

	/**
	 * Return array of files
	 * @param string $a_dir Directory
	 * @return array
	 */
	static public function files( $a_dir )
	{
		if(isset($this))
			return Zoombi_Folder::files($this->m_dir);
		
		if(!Zoombi_Folder::exist($a_dir))
			return array();

		$result = array();
		foreach(scandir($a_dir) as $file)
		{
			if($file == '.' || $file == '..')
				continue;

			$result[] = $file;
		}
		return $result;
	}

}
