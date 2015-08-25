<?php

/*
 * File: rssitem.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class ZRssItem
{

	private $m_title;
	private $m_description;
	private $m_link;
	private $m_pubDate;
	private $m_guid;
	private $m_category;
	private $m_author;
	private $m_comments;
	private $m_enclosures;
	private $m_sourse;

	public function __construct( array $a_data = array() )
	{
		$this->m_category = array();
		$this->m_enclosures = array();
		$this->setAuthor('(Author)');

		$this->fromArray($a_data);
	}

	/**
	 * Get title of the item.
	 * @return string
	 */
	public final function getTitle()
	{
		return $this->m_title;
	}

	/**
	 * Set title of the item.
	 * @param string $a_title
	 */
	public final function setTitle( $a_title )
	{
		$this->m_title = $a_title;
	}

	/**
	 * Get item synopsis.
	 * @return string
	 */
	public final function getDescription()
	{
		return $this->m_description;
	}

	/**
	 * Set item synopsis.
	 * @param string $a_description
	 */
	public final function setDescription( $a_description )
	{
		$this->m_description = $a_description;
	}

	/**
	 * Get URL of the item
	 * @return string
	 */
	public final function getLink()
	{
		return $this->m_link;
	}

	/**
	 * Set URL of the item
	 * @param string $a_link
	 */
	public final function setLink( $a_link )
	{
		$this->m_link = $a_link;
	}

	/**
	 * Get when the item was published
	 * @return mixed
	 */
	public final function getPubDate()
	{
		return $this->m_pubDate;
	}

	/**
	 * Set when the item was published
	 * @param mixed $a_pubDate
	 */
	public final function setPubDate( $a_pubDate )
	{
		$this->m_pubDate = $a_pubDate;
	}

	public final function getGuid()
	{
		return $this->m_guid;
	}

	public final function setGuid( $a_guid )
	{
		$this->m_guid = $a_guid;
	}

	public final function getCategory()
	{
		return $this->m_category;
	}

	public final function setCategory( $a_value )
	{
		$this->m_category = array();

		if(is_a($a_value, 'ZRssCategory'))
		{
			$this->addCategory($a_value);
			return;
		}

		if(is_array($a_value))
		{
			foreach($a_value as $cat)
				$this->addCategory($cat);

			return;
		}

		if(is_string($a_value))
		{
			foreach(explode("/", $a_value) as $cat)
				$this->addCategory($cat);

			return;
		}
	}

	public final function addCategory( $a_value )
	{
		if(is_a($a_value, 'ZRssCategory'))
		{
			$this->m_category[] = $a_value;
			return;
		}

		if(is_array($a_value))
		{
			$this->m_category[] = new ZRssCategory($a_value);
			return;
		}

		if(is_string($a_value))
		{
			$this->m_category[] = new ZRssCategory(array('title' => $a_value));
			return;
		}
	}

	public final function getComments()
	{
		return $this->m_comments;
	}

	public final function setComments( $a_value )
	{
		$this->m_comments = $a_value;
	}

	public final function getAuthor()
	{
		return $this->m_author;
	}

	public final function setAuthor( $a_value )
	{
		$this->m_author = new ZRssAuthor($a_value);
	}

	public final function getSource()
	{
		return $this->m_sourse;
	}

	public final function setSource( $a_source )
	{
		if($this->m_sourse)
			unset($this->m_sourse);

		if(is_a($a_source, 'ZRssSource'))
			$this->m_sourse = $a_source;

		if(is_array($a_source))
			$this->m_sourse = new ZRssSource($a_source);
	}

	public final function addEnclosure( $a_value )
	{
		if(is_a($a_value, 'ZRssItemEnclosure'))
		{
			$this->m_enclosures[] = $a_value;
			return;
		}
		if(is_array($a_value))
		{
			$this->m_enclosures[] = new ZRssItemEnclosure($a_value);
		}
	}

	public final function setEnclosure( array $a_value )
	{
		$this->m_enclosures = array();
		foreach($a_value as $enc)
			$this->addEnclosure($enc);
	}

	public final function getEnclosure()
	{
		return $this->m_enclosures;
	}

	public final function fromArray( array $a_value )
	{
		if(isset($a_value['title']))
			$this->setTitle($a_value['title']);

		if(isset($a_value['description']))
			$this->setDescription($a_value['description']);

		if(isset($a_value['link']))
			$this->setLink($a_value['link']);

		if(isset($a_value['pubDate']))
			$this->setPubDate($a_value['pubDate']);

		if(isset($a_value['guid']))
			$this->setGuid($a_value['guid']);

		if(isset($a_value['category']))
			$this->setCategory($a_value['category']);

		if(isset($a_value['comments']))
			$this->setComments($a_value['comments']);

		if(isset($a_value['author']))
			$this->setAuthor($a_value['author']);

		if(isset($a_value['enclosure']) && is_array($a_value['enclosure']))
			$this->setEnclosure($a_value['enclosure']);
	}

	public final function toArray()
	{
		$enc = array();
		foreach($this->getEnclosure() as $e)
			$enc[] = $e->toArray();

		$cat = array();
		foreach($this->getCategory() as $c)
			$cat[] = $c->toArray();

		return array(
			'title' => $this->getTitle(),
			'description' => $this->getDescription(),
			'link' => $this->getLink(),
			'pubDate' => $this->getPubDate(),
			'guid' => $this->getGuid(),
			'category' => $cat,
			'comments' => $this->getComments(),
			'author' => $this->getAuthor()->toArray(),
			'enclosure' => $enc
		);
	}

}
