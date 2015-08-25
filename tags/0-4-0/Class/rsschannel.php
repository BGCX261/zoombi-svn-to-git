<?php
/*
 * File: rsschannel.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class ZRssChannel
{
	private $m_title;
	private $m_description;
	private $m_link;
	private $m_language;
	private $m_copyright;
	private $m_managingEditor;
	private $m_webMaster;
	private $m_pubDate;
	private $m_lastBuildDate;
	private $m_category;
	private $m_generator;
	private $m_docs;
	private $m_ttl;
	private $m_image;
	private $m_items;

	public function __construct( array $a_data = array() )
	{
		$this->m_items = array();
		$this->m_image = new ZRssImage();
		$this->setManagingEditor('(Editor)');
		$this->setWebMaster('(WebMaster)');
		$this->fromArray($a_data);
	}
	public final function setChannel( array & $a_channel )
	{
		$this->m_channel = $a_channel;
	}
	public final function & getChannel()
	{
		return $this->m_channel;
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
	public final function getLanguage()
	{
		return $this->m_language;
	}
	public final function setLanguage( $a_language )
	{
		$this->m_language = $a_language;
	}
	public final function getCopyright()
	{
		return $this->m_copyright;
	}
	public final function setCopyright( $a_copyright )
	{
		$this->m_copyright = $a_copyright;
	}
	public final function getManagingEditor()
	{
		return $this->m_managingEditor;
	}
	public final function setManagingEditor( $a_managingEditor )
	{
		$this->m_managingEditor = new ZRssAuthor( $a_managingEditor );
	}

	public final function getWebMaster()
	{
		return $this->m_webMaster;
	}
	public final function setWebMaster( $a_webMaster )
	{
		$this->m_webMaster = new ZRssAuthor( $a_webMaster );
	}
	public final function getPubDate()
	{
		return $this->m_pubDate;
	}
	public final function setPubDate( $a_pubDate )
	{
		$this->m_pubDate = $a_pubDate;
	}
	public final function getLastBuildDate()
	{
		return $this->m_lastBuildDate;
	}
	public final function setLastBuildDate( $a_lastBuildDate )
	{
		$this->m_lastBuildDate = $a_lastBuildDate;
	}
	public final function getCategory()
	{
		return $this->m_category;
	}
	public final function setCategory( $a_value )
	{
		$this->m_category = array();

		if( is_a( $a_value, 'ZRssCategory' ) )
		{
			$this->addCategory($a_value);
			return;
		}

		if( is_array($a_value) )
		{
			foreach( $a_value as $cat )
			$this->addCategory($cat);

			return;
		}

		if( is_string($a_value) )
		{
			foreach( explode( "/", $a_value ) as $cat )
			$this->addCategory($cat);

			return;
		}

	}

	public final function addCategory( $a_value )
	{
		if( is_a($a_value, 'ZRssCategory') )
		{
			$this->m_category[] = $a_value;
			return;
		}

		if( is_array($a_value) )
		{
			$this->m_category[] = new ZRssCategory($a_value);
			return;
		}

		if( is_string($a_value) )
		{
			$this->m_category[] = new ZRssCategory( array('title'=>$a_value) );
			return;
		}
	}
	public final function getGenerator()
	{
		return $this->m_generator;
	}
	public final function setGenerator( $a_generator )
	{
		$this->m_generator = $a_generator;
	}
	public final function getDocs()
	{
		return $this->m_docs;
	}
	public final function setDocs( $a_docs )
	{
		$this->m_docs = $a_docs;
	}
	public final function getTtl()
	{
		return $this->m_ttl;
	}
	public final function setTtl( $a_ttl )
	{
		$this->m_ttl = $a_ttl;
	}
	public final function getImage()
	{
		return $this->m_image;
	}
	public final function setImage( $a_image )
	{
		if( is_a( $a_image, 'ZRssImage' ) )
		{
			$this->m_image = $a_image;
			return;
		}
		if( is_array($a_image) )
		{
			$this->m_image = new ZRssImage($a_image);
			return;
		}
		$this->m_image = new ZRssImage();
	}
	public final function fromArray( array $a_data )
	{
		if( isset( $a_data['title'] ) )
            $this->setTitle($a_data['title']);

		if( isset( $a_data['description'] ) )
            $this->setDescription($a_data['description']);

		if( isset( $a_data['link'] ) )
            $this->setLink($a_data['link']);

		if( isset( $a_data['language'] ) )
            $this->setLanguage($a_data['language']);

		if( isset( $a_data['copyright'] ) )
            $this->setCopyright($a_data['copyright']);

		if( isset( $a_data['managingEditor'] ) )
            $this->setManagingEditor($a_data['managingEditor']);

		if( isset( $a_data['webMaster'] ) )
            $this->setWebMaster($a_data['webMaster']);

		if( isset( $a_data['pubDate'] ) )
            $this->setPubDate($a_data['pubDate']);

		if( isset( $a_data['lastBuildDate'] ) )
            $this->setLastBuildDate($a_data['lastBuildDate']);

		if( isset( $a_data['category'] ) )
            $this->setCategory($a_data['category']);

		if( isset( $a_data['generator'] ) )
            $this->setGenerator($a_data['generator']);

		if( isset( $a_data['docs'] ) )
            $this->setDocs($a_data['docs']);

		if( isset( $a_data['ttl'] ) )
            $this->setTtl($a_data['ttl']);

		if( isset( $a_data['image'] ) )
            $this->setImage($a_data['image']);

		if( isset( $a_data['items'] ) )
            $this->setItems($a_data['items']);
	}

	public final function toArray()
	{
		$items = array();
		foreach( $this->getItems() as $i )
            $items[] = $i->toArray();

		$cat = array();
		foreach( $this->getCategory() as $c )
            $cat[] = $c->toArray();

		return array(
            'title' =>          $this->getTitle(),
            'description' =>    $this->getDescription(),
            'link' =>           $this->getLink(),
            'language' =>       $this->getLanguage(),
            'copyright' =>      $this->getCopyright(),
            'managingEditor' => $this->getManagingEditor()->toString(),
            'webMaster' =>      $this->getWebMaster()->toString(),
            'pubDate' =>        $this->getPubDate(),
            'lastBuildDate' =>  $this->getLastBuildDate(),
            'category' =>       $cat,
            'generator' =>      $this->getGenerator(),
            'docs' =>           $this->getDocs(),
            'ttl' =>            $this->getTtl(),
            'image' =>          $this->getImage()->toArray(),
            'items' =>          $items
		);
	}

	public final function getItems()
	{
		return $this->m_items;
	}

	public final function setItems( array $a_items )
	{
		$this->m_items = array();
		foreach( $a_items as $i )
		$this->addItem($i);
	}

	public final function addItem( $a_item )
	{
		if( is_a( $a_item, 'ZRssItem') )
		$this->m_items[] = $a_item;

		if( is_array($a_item) )
		$this->m_items[] = new ZRssItem($a_item);
	}

	public final function addItems( array $a_array )
	{
		foreach ( $a_array as $i )
		{
			$this->addItem($i);
		}
	}
}
