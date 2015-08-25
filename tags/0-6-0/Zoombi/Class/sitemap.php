<?php

/*
 * File: sitemap.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class ZSiteMap
{

	private $m_lastmod;
	private $m_loc;

	public function __construct( array $a_data = null )
	{
		if($a_data)
			$this->fromArray($a_data);
	}

	public function __get( $a_name )
	{
		switch($a_name)
		{
			case 'loc':
				return $this->getLoc();
			case 'lastmod':
				return $this->getLastmod();
		}
	}

	public function __set( $a_name, $a_value )
	{
		switch($a_name)
		{
			case 'loc':
				$this->setLoc($a_value);
			case 'lastmod':
				$this->setLastmod($a_value);
		}
	}

	public function setLastmod( $a_lastmod )
	{
		$this->m_lastmod = $a_lastmod;
	}

	public function getLastmod()
	{
		return $this->m_lastmod;
	}

	public function setLoc( $a_loc )
	{
		$this->m_loc = $a_loc;
	}

	public function getLoc()
	{
		return $this->m_loc;
	}

	public function toArray()
	{
		return array(
			'loc' => $this->getLoc(),
			'lastmod' => $this->getLastmod()
		);
	}

	public function fromArray( $a_array )
	{
		if(isset($a_array['loc']))
			$this->setLoc($a_array['loc']);

		if(isset($a_array['lastmod']))
			$this->getLastmod($a_array['lastmod']);
	}

}
