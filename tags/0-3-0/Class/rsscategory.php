<?php
/*
 * File: rsscategory.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class ZRssCategory
{
	private $m_domain;
	private $m_title;

	public function __construct( array $a_data = array() )
	{
		$this->fromArray( $a_data );
	}

	public final function getDomain()
	{
		return $this->m_domain;
	}

	public final function setDomain( $a_data )
	{
		$this->m_domain = $a_data;
	}

	public final function getTitle()
	{
		return $this->m_title;
	}

	public final function setTitle( $a_title )
	{
		$this->m_title = $a_title;
	}

	public final function fromArray( array $a_data )
	{
		if( isset( $a_data['domain'] ) )
		$this->setDomain( $a_data );
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