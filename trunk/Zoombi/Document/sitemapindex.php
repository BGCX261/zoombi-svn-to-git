<?php

/*
 * File: rss.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


class Zoombi_Document_SiteMapIndex extends Zoombi_Document_Xml
{

	private $m_maps;

	public function __construct()
	{
		parent::__construct(self::DOCTYPE_SITEMAPINDEX, 'application/xml');
		$this->m_maps = array();
	}

	public function addMap( $a_map )
	{
		if( $a_map instanceof ZSiteMap )
		{
			$this->m_maps[] = $a_map;
			return;
		}

		if(is_array($a_map))
		{
			if(isset($a_map['loc']))
				$this->addMap(new Zoombi_SiteMap_Item($a_map));
		}
	}

	public function setMaps( array $a_maps )
	{
		$this->m_maps = $a_maps;
	}

	public function addMaps( array $a_maps )
	{
		$this->m_maps = array_merge($this->m_maps, $a_maps);
	}

	public function compile()
	{
		$index = parent::createElement('sitemapindex');
		$index->attr('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		foreach($this->m_maps as $m)
		{
			$l = $m->getLoc();
			if(empty($l))
				continue;

			$map = parent::create('sitemap');
			$map->append(parent::createElement('loc', $l));

			$l = $m->getLastmod();
			if(!empty($l))
				$map->append(parent::createElement('lastmod', $l));

			$index->append($map);
		}

		parent::append($index);
		return parent::compile();
	}

}

?>