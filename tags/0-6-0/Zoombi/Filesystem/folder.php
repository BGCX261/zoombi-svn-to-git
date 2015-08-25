<?php

/*
 * File: folder.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

class ZFolder
{

	private $m_dir;

	public function __construct( $a_dir = null )
	{
		$this->m_dir = $a_dir;
	}

	static public function exist( $a_dir )
	{
		if(isset($this))
			return ZFolder::exist($this->m_dir);

		return is_dir($a_dir);
	}

	static public function notexist( $a_dir )
	{
		return!ZFolder::exist($a_dir);
	}

	static public function files( $a_dir )
	{
		if(isset($this))
			return ZFolder::files($this->m_dir);


		if(!ZFolder::exist($a_dir))
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
