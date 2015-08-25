<?php
class ZSiteMapUrlsetDocument extends ZXmlDocument
{
	private $m_urls;
	public function  __construct()
	{
		parent::__construct( self::DOCTYPE_SITEMAPURLSET,'application/xml');
        $this->m_urls = array();
	}
	
	public function addUrl( $a_url )
	{
		if( is_a( $a_url, 'ZSiteUrl' ) )
		{
			$this->m_urls[] = $a_url;
			return;
		}

		if( is_array( $a_url ) )
		{
			if( isset( $a_url['loc'] ) )
				$this->addUrl( new ZSiteUrl( $a_url ) );
		}
	}
	
	public function addUrls( array $a_urls )
	{
		$this->m_urls = array_merge( $this->m_urls, $a_urls );			
	}
	
	public function setUrls( array $a_urls )
	{
		$this->m_urls = $a_urls;
	}
	
	public function compile()
	{
		$urlset = parent::createElement('urlset');
		$urlset->attr('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );

		foreach ( $this->m_urls as $u )
		{
			$l = $u->getLoc();
			if( empty( $l ) )
				continue;

			$url = parent::create('url');
			$url->append( parent::createElement('loc', $l ) );

			$l = $u->getLastmod();
			if( $l )
				$url->append( parent::createElement('lastmod', $l ) );
				
			$l = $u->getChangefreq();
			if( $l )
				$url->append( parent::createElement('changefreq', $l ) );
				
			$l = $u->getPriority();
			if( $l )
				$url->append( parent::createElement('priority', $l ) );

			$urlset->append( $url );
		}

		parent::append( $urlset );
		return parent::compile();
	}
}