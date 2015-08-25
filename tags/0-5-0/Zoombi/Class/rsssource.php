<?php
/*
 * File: rsssource.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class ZRssSource
{
	private $m_url;
	private $m_title;

	public function __construct( array $a_data = array() )
	{
		$this->fromArray( $a_data );
	}

	public final function getTitle()
	{
		return $this->m_title;
	}

	public final function setTitle( $a_title )
	{
		$this->m_title = $a_title;
	}

	public final function getUrl()
	{
		return $this->m_url;
	}

	public final function setUrl( $a_url )
	{
		$this->m_url = $a_url;
	}

	public final function fromArray( array $a_data )
	{
		if( isset( $a_data['url'] ) )
		$this->setUrl( $a_data );
		if( isset( $a_data['title'] ) )
		$this->setTitle( $a_data['title'] );
	}

	public final function toArray()
	{
		return array(
            'title' => $this->getTitle(),
            'domain' => $this->getDomain()
		);
	}
}