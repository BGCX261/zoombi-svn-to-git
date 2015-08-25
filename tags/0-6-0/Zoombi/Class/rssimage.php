<?php

/*
 * File: rssimage.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class ZRssImage
{

	private $m_title;
	private $m_description;
	private $m_link;
	private $m_url;
	private $m_width;
	private $m_height;

	public function __construct( array $a_data = array() )
	{
		$this->fromArray($a_data);
	}

	public final function fromArray( array $a_data )
	{
		if(isset($a_data['title']))
			$this->m_title = $a_data['title'];

		if(isset($a_data['description']))
			$this->m_description = $a_data['description'];

		if(isset($a_data['link']))
			$this->m_link = $a_data['link'];

		if(isset($a_data['url']))
			$this->m_url = $a_data['url'];

		if(isset($a_data['width']))
			$this->m_width = $a_data['width'];

		if(isset($a_data['height']))
			$this->m_height = $a_data['height'];
	}

	public final function toArray()
	{
		return array(
			'title' => $this->m_title,
			'description' => $this->m_description,
			'link' => $this->m_link,
			'url' => $this->m_url,
			'width' => $this->m_width,
			'height' => $this->m_height
		);
	}

	public final function getTitle()
	{
		return $this->m_title;
	}

	public final function setTitle( $a_title )
	{
		$this->m_title = $a_title;
	}

	public final function getDescription()
	{
		return $this->m_description;
	}

	public final function setDescription( $a_description )
	{
		$this->m_description = $a_description;
	}

	public final function getLink()
	{
		return $this->m_link;
	}

	public final function setLink( $a_link )
	{
		$this->m_link = $a_link;
	}

	public final function getUrl()
	{
		return $this->m_url;
	}

	public final function setUrl( $a_url )
	{
		$this->m_url = $a_url;
	}

	public final function getWidth()
	{
		return $this->m_width;
	}

	public final function setWidth( $a_width )
	{
		$this->m_width = $a_width;
	}

	public final function getHeight()
	{
		return $this->m_height;
	}

	public final function setHeight( $a_height )
	{
		$this->m_height = $a_height;
	}

}
