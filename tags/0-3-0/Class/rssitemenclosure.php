<?php
/*
 * File: rssenclosure.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class ZRssItemEnclosure
{
	private $m_url;
	private $m_lenght;
	private $m_type;

	public function __construct( array $a_data = array() )
	{
		$this->fromArray( $a_data );
	}

	public final function getUrl()
	{
		return $this->m_url;
	}

	public final function setUrl( $a_value )
	{
		$this->m_url = $a_value;
	}

	public final function getLenght()
	{
		return $this->m_lenght;
	}

	public final function setLenght( $a_value )
	{
		$this->m_lenght = $a_value;
	}

	public final function getType()
	{
		return $this->m_type;
	}

	public final function setType( $a_value )
	{
		$this->m_type = $a_value;
	}

	public final function fromArray( array $a_value )
	{
		if( isset( $a_value['url'] ) )
		$this->setUrl( $a_value['url'] );

		if( isset( $a_value['lenght'] ) )
		$this->setLenght( $a_value['lenght'] );

		if( isset( $a_value['type'] ) )
		$this->setType( $a_value['type'] );
	}

	public final function toArray()
	{
		return array(
            'url' => $this->getUrl(),
            'lenght' => $this->getLenght(),
            'type' => $this->getType()
		);
	}
}