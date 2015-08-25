<?php
/*
 * File: rss.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

class ZRssDocument extends ZXmlDocument
{
	private $m_version;
	private $m_channel;
	private $m_self;

	public function __construct()
	{
		parent::__construct( self::DOCTYPE_RSS,'application/rss+xml');
		$this->setVersion('2.0');
		$this->setChannel( array() );
	}

	public final function setSelf( $a_value )
	{
		$this->m_self = $a_value;
	}

	public final function getSelf()
	{
		return $this->m_self;
	}

	public final function getVersion()
	{
		return $this->m_version;
	}

	public final function setVersion( $a_value )
	{
		$this->m_version = $a_value;
	}

	public final function getChannel()
	{
		return $this->m_channel;
	}

	public final function setChannel( $a_channel )
	{
		if( is_a( $a_channel, 'ZRssChannel') )
		{
			$this->m_channel = $a_channel;
			return;
		}

		if( is_array( $a_channel) )
		{
			$this->m_channel = new ZRssChannel($a_channel);
			return;
		}
		$this->m_channel = new ZRssChannel();
	}

	public function compile()
	{
		$rss = parent::create('rss');
		$rss->attr("xmlns:atom", "http://www.w3.org/2005/Atom");
		$rss->attr('version', $this->getVersion() );
		$c = $this->getChannel();

		$channel = parent::create('channel');

		$self = $this->getSelf();
		if( !empty( $self ) )
		{
			$selflink = parent::create('atom:link');
			$selflink->attr( 'href', (string)$self );
			$selflink->attr( 'rel', 'self' );
			$selflink->attr( 'type', "application/rss+xml");
			$channel->append( $selflink );
		}

		$title = parent::create('title');
		$title->text( $c->getTitle() );
		$channel->append( $title );

		$desc = parent::create('description');
		$desc->text( $c->getDescription() );
		$channel->append( $desc );

		$link = parent::create('link');
		$link->text( $c->getLink() );
		$channel->append( $link );

		$language = $c->getLanguage();
		if( !empty ( $language ) )
		{
			$node = parent::createElement('language', $language);
			$channel->append( $node );
		}

		$copyright = $c->getCopyright();
		if( !empty ( $language ) )
		{
			$node = parent::createElement('copyright', $copyright);
			$channel->append( $node );
		}

		$managingEditor = $c->getManagingEditor();
		if( !empty ( $language ) )
		{
			$node = parent::createElement('managingEditor', $managingEditor->toString());
			$channel->append( $node );
		}

		$webMaster = $c->getWebMaster();
		if( !empty ( $language ) )
		{
			$node = parent::createElement('webMaster', $webMaster->toString());
			$channel->append( $node );
		}

		$pubDate = $c->getPubDate();
		if( !empty ( $language ) )
		{
			$node = parent::createElement('pubDate', gmdate("D, d M Y H:i:s",strtotime($pubDate)) . ' +0000');
			$channel->append( $node );
		}

		$lastBuildDate = $c->getLastBuildDate();
		if( !empty ( $lastBuildDate ) )
		{
			$node = parent::createElement('lastBuildDate', gmdate("D, d M Y H:i:s",strtotime($lastBuildDate)) . ' +0000');
			$channel->append( $node );
		}

		$category = $c->getCategory();
		if( count( $category ) )
		{
			foreach ( $category as $cat )
			{
				$node = parent::createElement('category', $cat->getTitle() );
				$domain = $cat->getDomain();
				if( !empty($domain) )
				$node->attr('domain', $domain);

				$channel->append( $node );
			}
		}

		$generator = $c->getGenerator();
		if( !empty( $generator ) )
		{
			$node = parent::createElement('generator', $generator);
			$channel->append( $node );
		}

		$docs = $c->getDocs();
		if( !empty( $docs ) )
		{
			$node = parent::createElement('docs', $docs);
			$channel->append( $node );
		}

		$ttl = $c->getTtl();
		if( !empty( $ttl ) )
		{
			$node = parent::createElement('ttl', $ttl);
			$channel->append( $node );
		}

		$image = $c->getImage();

		$img_url = $image->getUrl();
		if( !empty($img_url) )
		{
			$imgnode = parent::create('image');

			$image_title = $image->getTitle();
			$node = parent::createElement('title',  empty( $image_title ) ? $c->getTitle() : $image_title );
			$imgnode->append( $node );

			$image_link = $image->getLink();
			$node = parent::createElement('link', empty( $image_link ) ? $c->getLink() : $image_link );
			$imgnode->append( $node );

			$node = parent::createElement('url', $img_url );
			$imgnode->append( $node );


			$img_description = $image->getDescription();
			if( !empty($img_description) )
			{
				$node = parent::createElement('description', $img_description );
				$imgnode->append( $node );
			}

			$channel->append( $imgnode );
		}

		$items = $c->getItems();
		foreach( $items as $item )
		{
			$itemnode   = parent::create('item');

			$item_title = parent::createElement('title', $item->getTitle() );
			$itemnode->append( $item_title );

			$item_desc = parent::createElement( 'description', $item->getDescription() );
			$itemnode->append( $item_desc );

			$item_link = parent::createElement( 'link', $item->getLink() );
			$itemnode->append( $item_link );

			$item_pubDate = $item->getPubDate();
			if( !empty( $item_pubDate ) )
			{
				$inode = parent::createElement('pubDate', gmdate("D, d M Y H:i:s",strtotime($item_pubDate)) . ' +0000');
				$itemnode->append( $inode );
			}

			$item_guid = $item->getGuid();
			if( empty( $item_guid ) )
			{
				$item_guid = $item_link;
			}
			$inode = parent::createElement('guid', $item->getGuid() );
			$itemnode->append( $inode );

			$item_author = $item->getAuthor()->toString();
			if( !empty( $item_author ) )
			{
				$inode = parent::createElement('author', $item_author );
				$itemnode->append( $inode );
			}

			$item_coments = $item->getComments();
			if( !empty( $item_coments ) )
			{
				$inode = parent::createElement('comments', $item_coments );
				$itemnode->append( $inode );
			}

			$item_source = $item->getSource();
			if( !empty( $item_source ) )
			{
				$inode = parent::createElement('source', $c->getTitle() );
				$inode->attr( $c->getUrl() );
				$itemnode->append( $inode );
			}

			foreach( $item->getCategory() as $cat )
			{
				$cat_title = $cat->getTitle();
				if( empty( $cat_title ) )
				continue;

				$catnode = parent::createElement('category', $cat_title );
				$domain = $cat->getDomain();

				if( !empty( $domain ) )
				$catnode->attr('domain', $domain);

				$itemnode->append( $catnode );
			}

			foreach( $item->getEnclosure() as $enc )
			{
				$encnode = parent::create('enclosure');
				$encnode->attr('url', $enc->getUrl() );
				$encnode->attr('length', $enc->getLength() );
				$encnode->attr('type', $enc->getType() );
				$itemnode->append( $encnode );
			}
			$channel->append( $itemnode );
		}

		$rss->append($channel);
		parent::append( $rss );
		return parent::compile();
	}
}

?>