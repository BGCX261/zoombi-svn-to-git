<?php

/*
 * File: cachefile.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * Cahed file class class
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class Zoombi_Cache_File extends Zoombi_Object
{
	/*
	 * Expires constants
	 */
	const EXPIRE_SECOND = 1;
	const EXPIRE_MINUTE = 60;
	const EXPIRE_HOUR = 3600;
	const EXPIRE_DAY = 86400;
	const EXPIRE_WEEK = 604800;
	const EXPIRE_MOUNTH = 2419200;

	private $m_dir;
	
	/**
	 * Construct
	 * @param type $a_dir Directory to cache
	 * @param type $a_expire Expire time
	 */
	function __construct( $a_dir = null )
	{
		if($a_dir)
			$this->setDirectory($a_dir);
	}

	function setDirectory( $a_dir )
	{
		$this->m_dir = rtrim((string)$a_dir,'/\\') . Zoombi::DS;
	}

	function getDirectory()
	{
		return $this->m_dir;
	}

	function isExpired( $a_key )
	{
		$e = $this->getExpiresTime($a_key);
		if( $e )
			return ( $e < time() );
		
		return true;
	}

	function get( $a_key )
	{
		$key = (string)$a_key;
		$file = $this->m_dir . $key . '.cache';
		return ($this->isExist($key) AND !$this->isExpired($key)) ? file_get_contents($file) : null;
	}
	
	function getExpires( $a_key )
	{
		$key = (string)$a_key;
		
		if(!$this->isExist($key))
			return;

		$data = file_get_contents($this->m_dir . $key . '.info');
		if( $data === false )
			return;
		
		$e = explode(':', $data );
		if( $e AND is_array($e) AND count($e) == 2 )
			return array( intval($e[0]), intval($e[1]) );
	}
	
	function getAge( $a_key )
	{
		$e = $this->getExpires($a_key);
		if( !$e )
			return;
		
		return $e[1];
	}
	
	function getExpiresTime( $a_key )
	{
		$e = $this->getExpires($a_key);
		if( !$e )
			return;
		
		return intval( $e[0] ) + intval( $e[1] );
	}
	
	function getTime( $a_key )
	{
		$e = $this->getExpires($a_key);
		if( !$e )
			return;
		
		return intval( $e[0] );
	}

	function isExist( $a_key )
	{
		$key = (string)$a_key;
		return (
			file_exists($this->m_dir . $key . '.cache') AND
			file_exists($this->m_dir . $key . '.info')
		);
	}

	function put( $a_key, $a_data, $a_expire = self::EXPIRE_MINUTE )
	{
		if(Zoombi_Folder::notexist($this->m_dir))
		{
			if(!mkdir($this->m_dir))
			{
				Zoombi::getApplication()->triggerError('Failed when create directory');
				return;
			}
		}

		$key = (string)$a_key;
		
		$cache = $this->m_dir . $key . '.cache';
		$info = $this->m_dir . $key . '.info';
		$time = time();
		$age = intval($a_expire);
		
		if(file_put_contents($cache, $a_data) === false)
		{
			Zoombi::getApplication()->triggerError('Cache file "' . $cache . '", not written');
			return;
		}

		if(file_put_contents($info, $time.':'.$age ) === false)
		{
			Zoombi::getApplication()->triggerError('Cache info file "' . $info . '", not written');
			return;
		}
		
		return array($time,$age);
	}
	
	static function fastPut( $a_dir, $a_key, $a_data, $a_expire = null )
	{
		$c = new self($a_dir);
		return $c->put( $a_key, $a_data, $a_expire);
	}
	
	static function fastGet( $a_dir, $a_key )
	{
		$c = new self($a_dir);
		return $c->get( $a_key );
	}
}
