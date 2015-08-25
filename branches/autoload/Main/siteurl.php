<?php

/*
 * File: siteurl.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


class Zoombi_SiteMap_Url
{

	private $m_lastmod;
	private $m_loc;
	private $m_changefreq;
	private $m_priority;

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
			case 'changefreq':
				return $this->getChangefreq();
			case 'priority':
				return $this->getPriority();
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
			case 'changefreq':
				$this->setChangefreq($a_value);
			case 'priority':
				$this->setPriority($a_value);
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

	public function setChangefreq( $a_changefreq )
	{
		$this->m_changefreq = $a_changefreq;
	}

	public function getChangefreq()
	{
		return $this->m_changefreq;
	}

	public function setPriority( $a_priority )
	{
		$this->m_priority = $a_priority;
	}

	public function getPriority()
	{
		return $this->m_priority;
	}

	public function toArray()
	{
		return array(
			'loc' => $this->getLoc(),
			'lastmod' => $this->getLastmod(),
			'changefreq' => $this->getChangefreq(),
			'priority' => $this->getPriority()
		);
	}

	public function fromArray( $a_array )
	{
		if(isset($a_array['loc']))
			$this->setLoc($a_array['loc']);

		if(isset($a_array['lastmod']))
			$this->getLastmod($a_array['lastmod']);

		if(isset($a_array['changefreq']))
			$this->getChangefreq($a_array['changefreq']);

		if(isset($a_array['priority']))
			$this->getPriority($a_array['priority']);
	}

}