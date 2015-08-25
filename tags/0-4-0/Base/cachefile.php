<?php
/*
 * File: cachefile.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Cahed file class class
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class ZCacheFile extends ZObject
{
	/*
	 * Expires constants
	 */
	const EXPIRE_SECOND = 1;
	const EXPIRE_MINUTE = 3600;
	const EXPIRE_HOUR = 216000;
	const EXPIRE_DAY = 5184000;
	const EXPIRE_WEEK = 36288000;
	const EXPIRE_MOUNTH = 145152000;

	
	private $m_dir;
	private $m_exp;

	function __construct( $a_dir = null, $a_expire = self::EXPIRE_MOUNTH )
	{
		if( $a_dir )
			$this->setDirectory($a_dir);

		$this->setExpire($a_expire);
	}

	function setDirectory( $a_dir )
	{
		$d = (string)$a_dir;
		if( substr($d, strlen($d) - 1) != Zoombi::DS )
			$d .= Zoombi::DS;

		$this->m_dir = $d;
	}

	function getDirectory()
	{
		return $this->m_dir;
	}

	function setExpire( $a_exp )
	{
		$this->m_exp = (int)$a_exp;
	}

	function getExpire()
	{
		return $this->m_exp;
	}

	function isExpired( $a_key )
	{
		if( !$this->isExist($a_key) )
			return true;

		$key = (string)$a_key;

		$int = file_get_contents($this->m_dir . $key . '.info');
		$ctime = (int)$int + (int)$this->m_exp;
		return!( (int)$ctime >= time() );
	}

	function get( $a_key )
	{
		$key = (string)$a_key;
		$cache = $this->m_dir . $key . '.cache';
		$info = $this->m_dir . $key . '.info';

		if( file_exists($cache) && file_exists($info) )
		{
			$int = file_get_contents($info);
			$ctime = (int)$int + (int)$this->m_exp;
			$time = time();
			if( (int)$ctime >= (int)$time )
				return file_get_contents($cache);
		}
		return null;
	}

	function isExist( $a_key )
	{
		return (file_exists($this->m_dir . (string)$a_key . '.cache') AND file_exists($this->m_dir . (string)$a_key . '.info') );
	}

	function put( $a_key, $a_data )
	{
		if( !file_exists($this->m_dir) )
			mkdir($this->m_dir);

		$time = time();

		$key = (string)$a_key;
		$cache = $this->m_dir . $key . '.cache';
		$info = $this->m_dir . $key . '.info';

		file_put_contents($cache, $a_data);
		file_put_contents($info, $time);
	}

	static final public function getData( $a_path, $a_key, $a_value )
	{
		
	}

}
