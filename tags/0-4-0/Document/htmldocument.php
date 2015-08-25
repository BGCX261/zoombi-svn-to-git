<?php
/*
 * File: html.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */
if( !defined('ZBOOT') )
	return;

/**
 * ZHtmlDocument class
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class ZHtmlDocument extends ZXmlDocument
{
	private $m_title;
	private $m_meta;
	private $m_base;
	private $m_keywords;
	private $m_description;
	private $m_link;
	private $m_styles;
	private $m_scripts;
	private $m_body;
    private $m_encoding;
    private $m_doctype;

    public function __construct()
	{
		parent::__construct( self::DOCTYPE_HTML,'text/html');
		$this->m_doctype      = null;
		$this->m_title        = 'default title';
		$this->m_meta         = array();
		$this->m_base         = null;
		$this->m_keywords     = null;
		$this->m_description  = null;
		$this->m_link         = array();
		$this->m_styles       = array( 'inline'=>array(), 'include'=>array(), 'url'=>array() );
		$this->m_scripts      = array( 'inline'=>array(), 'include'=>array(), 'url'=>array() );
		$this->m_body         = '';
        $this->m_encoding   = 'utf-8';
        $this->setDoctype('html4-trans');
	}

	public function & linkAdd( array $a_link )
	{
		$this->m_link[] = $a_link;
        return $this;
	}

	public function & rssAdd( $a_title, $a_link )
	{
		$this->linkAdd(array(
            'rel'   => 'alternate',
            'title' => $a_title,
            'type'  => 'application/rss+xml',
            'href'  => $a_link
		));
        return $this;
	}

	public function & atomAdd( $a_title, $a_link )
	{
		$this->linkAdd( array(
            'rel'   => 'alternate',
            'title' => $a_title,
            'type'  => 'application/atom+xml',
            'href'  => $a_link
		));
        return $this;
	}

	public function & cssAdd( $style )
	{
		if( is_string($style) )
		array_push( $this->m_styles['inline'], $style );

		if( is_array($style) )
		$this->m_styles['inline'] = array_merge( $this->m_styles['inline'], $style );
        return $this;
	}

	public function & cssAddFile()
	{
		foreach ( func_get_args() as $arg )
		{
			switch ( gettype($arg) )
			{
				case 'string':
					$this->m_styles['include'][] = $arg;
					break;

				case 'array':
					$this->m_styles['include'] = array_merge( $this->m_styles['include'], $arg );
					break;
			}
		}
        return $this;
	}

	public function & cssAddUrl()
	{
		foreach ( func_get_args() as $arg )
		{
			switch ( gettype($arg) )
			{
				case 'string':
					$this->m_styles['url'][] = $arg;
					break;

				case 'array':
					$this->m_styles['url'] = array_merge( $this->m_styles['url'], $arg );
					break;
			}
		}
        return $this;
	}

	public function & cssIncludeFile( $style )
	{
		$file = file_get_contents($style);
		if( $file )
            $this->cssAdd( "/**\n *\t Include file: {$style}\r\n */\r\n".$file );
        return $this;
	}

	public function & jsAdd( $a_script )
	{
		if( is_string($a_script) )
            array_push( $this->m_scripts['inline'], $a_script );

		if( is_array($a_script) )
            $this->m_scripts['inline'] = array_merge( $this->m_scripts['inline'], $a_script );
        return $this;
	}

	public function & jsAddFile( $a_file, $a_head = false)
	{
		switch ( gettype($a_file) )
		{
			case 'string':
				if( $a_head )
					$this->jsAddUrl( $a_file );
				else
					$this->m_scripts['include'][] = $a_file;
				break;

			case 'array':
				if( $a_head )
					$this->jsAddUrl( $a_file );
				else
					$this->m_scripts['include'] = array_merge( $this->m_scripts['include'], $a_file );
				break;
		}
        return $this;
	}

	public function & jsAddUrl( $a_url )
	{
		switch ( gettype($a_url) )
		{
			case 'string':
				$this->m_scripts['url'][] = $a_url;
				break;

			case 'array':
				$this->m_scripts['url'] = array_merge( $this->m_scripts['url'], $a_url );
				break;
		}
        return $this;
	}

	public function & jsIncludeFile( $script )
	{
		$file = file_get_contents($script);
		if( $file )
            $this->jsAdd( "/*** {$script} ***/\n\r".$file );

        return $this;
	}

	public function & metaAdd( array $a_meta )
	{
		if( array_key_exists('http-equiv', $a_meta ) )
		{
			$m =& $a_meta['http-equiv'];
			foreach( $this->m_meta as $k => $meta )
			{
				if( array_key_exists('http-equiv', $meta) && $meta['http-equiv'] == $m )
				{
					$this->m_meta[ $k ] = $a_meta;
					return $this;
				}
			}
		}

		if( array_key_exists('name', $a_meta ) )
		{
			$m =& $a_meta['name'];
			foreach( $this->m_meta as $k => $meta )
			{
				if( array_key_exists('name', $meta) && $meta['name'] == $m )
				{
					$this->m_meta[ $k ] = $a_meta;
					return $this;
				}
			}
		}
		$this->m_meta[] = $a_meta;
        return $this;
	}

	public function & metaAddHttp( $a_equiv, $a_content )
	{
		$this->metaAdd( array(
            'http-equiv'    => $a_equiv,
            'content'       => $a_content
		));
        return $this;
	}

	public function & metaAddName( $a_name, $a_content )
	{
		$this->metaAdd( array(
            'name'      => $a_name,
            'content'   => $a_content
		));
        return $this;
	}

	public function & setMeta( array $a_meta )
	{
		$this->m_meta = $a_meta;
        return $this;
	}

	public function & getMeta()
	{
		return $this->m_meta;
	}

	public function & setTitle( $a_title )
	{
		$this->m_title = $a_title;
        return $this;
	}

	public function & getTitle()
	{
		return $this->m_title;
	}

	public function & setBase( $a_base )
	{
		$this->m_base = $a_base;
        return $this;
	}

	public function & getBase()
	{
		return $this->m_base;
	}

	public function & base( $a_base = null )
	{
        if( $a_base === null )
            return $this->base();

		return $this->setBase($a_base);
	}

	public function & setKeywords( $a_keywords )
	{
		$this->m_keywords = $a_keywords;
        return $this;
	}

	public function & setDescription( $a_desc )
	{
		$this->m_description = $a_desc;
        return $this;
	}

	public function & setDoctype( $a_doctype = null )
	{
		$doctype = trim( strtolower($a_doctype) );
		$types = array(
            'xhtml1-strict'	=> array(
                'type'      => 'html',
                'name'      => '-//W3C//DTD XHTML 1.0 Strict//EN',
                'doctype'   => 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'
            ),
            'xhtml1-tran'	=> array(
                'type'      => 'html',
                'name'      => '-//W3C//DTD XHTML 1.0 Transitional//EN',
                'doctype'   => 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'
            ),
            'xhtml1-frame'	=> array(
                'type'      => 'html',
                'name'      => '-//W3C//DTD XHTML 1.0 Frameset//EN',
                'doctype'   => 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd'
            ),
            'xhtml11' => array(
                'type'      => 'html',
                'name'      => '-//W3C//DTD XHTML 1.1//EN',
                'doctype'   => 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'
            ),
            'html4-strict' => array(
                'type'      => 'html',
                'name'      => '-//W3C//DTD HTML 4.01//EN',
                'doctype'   => 'http://www.w3.org/TR/html4/strict.dtd'
            ),
            'html4-trans' => array(
                'type'      => 'html',
                'name'      => '-//W3C//DTD HTML 4.01 Transitional//EN',
                'doctype'   => 'http://www.w3.org/TR/html4/loose.dtd'
            ),
            'html4-frame' => array(
                'type'      => 'html',
                'name'      => '-//W3C//DTD HTML 4.01 Frameset//EN',
                'doctype'   => 'http://www.w3.org/TR/html4/frameset.dtd'
            ),
            'html5' => array(
                'type'      => 'html',
                'name'      => null,
                'doctype'   => null
            )
        );

        if( !isset( $types[$doctype]) )
            $doctype = 'html4-trans';

        $this->m_doctype = $types[$doctype];
        return $this;
	}

	public function & bodySet( $a_body )
	{
		$this->m_body = $a_body;
        return $this;
	}

	public function & bodyPrepend( $a_body )
	{
		$this->m_body = $a_body . $this->m_body;
        return $this;
	}

	public function & bodyAppend( $a_body )
	{
		$this->m_body .= $a_body;
        return $this;
	}

	public function & bodyGet()
	{
		return $this->m_body;
	}

	public function & body( $a_body = null )
	{
        if( $a_body === null )
            return $this->bodyGet();

		return $this->bodySet($a_body);
	}

    public function & getEncoding()
    {
        return $this->m_encoding;
    }

    public function & setEncoding( $a_encoding )
    {
        $this->m_encoding = $a_encoding;
        return $this;
    }

	public function compile()
	{
        $implType = DOMImplementation::createDocumentType( $this->m_doctype['type'], $this->m_doctype['name'], $this->m_doctype['doctype'] );
        $doc = null;
        if( $implType )
            $doc = DOMImplementation::createDocument(null, $this->m_doctype['type'], $implType);
        else
            return;

        $doc->encoding = $this->getEncoding();
        parent::setDocument( $doc );


		$html = parent::getRoot();
		if( $this->getDocument()->doctype->name == 'xhtml' )
		{
			$html->attr('xmlns', 'http://www.w3.org/1999/xhtml');
			$html->attr('xml:lang', 'en');
		}

		$head = parent::create('head');
		$body = parent::create('body');

		$title = parent::create('title');
		$title->text( $this->m_title );
		$head->append( $title );

		if( $this->m_base )
		{
			$base = parent::create('base');
			$base->attr('href', $this->m_base );
			$head->append( $base );
		}
		$doc = '';

		$this->metaAddHttp( 'Content-Type', 'text/html; charset=' . $this->getEncoding() );

		if( $this->m_keywords )
		{
			$this->metaAddName('keywords', $this->m_keywords);
		}

		if( $this->m_description )
		{
			$this->metaAddName('description', $this->m_description);
		}

        foreach( $this->m_meta as $m )
        {
            if( !is_array($m) || count($m) < 0 )
                continue;

            $mnode = parent::create('meta');
            foreach($m as $key => $val)
                $mnode->attr($key, $val);
            $head->append($mnode);
        }

        foreach( $this->m_styles['inline'] as $s )
        {
            $style = parent::create('style');
            $style->attr('type', 'text/css' );
            $style->text( $s );
            $head->append( $style );
        }

        foreach( $this->m_link as $link )
        {
            if( !is_array( $link ) || count( $link ) < 0 )
            continue;

            $l = parent::create('link');
            foreach( $link as $key => $val )
            {
                $l->attr( $key, $val );
            }
            $head->append( $l );
        }

        foreach( $this->m_styles['url'] as $s )
        {
            $style = parent::create('link');
            $style->attr('rel','stylesheet');
            $style->attr('type','text/css');
            $style->attr('href',$s);
            $head->append( $style );
        }

        foreach( $this->m_styles['include'] as $s )
        {
            $style = parent::create('link');
            $style->attr('rel','stylesheet');
            $style->attr('type','text/css');
            $style->attr('href',$s);
            $head->append( $style );
        }

        foreach( $this->m_scripts['url'] as $s )
        {
            $script = parent::create('script');
            $script->attr('type','text/javascript');
            $script->attr('src',$s);
            $script->text('');
            $head->append( $script );
        }

		$placeholder = '[-----REPLACE-HERE-'.md5(time()).'-----]';

        $body->text($placeholder);
        foreach( $this->m_scripts['include'] as $s )
        {
            $script = parent::create('script');
            $script->attr('type','text/javascript');
            $script->attr('src',$s);
            $script->text('');
            $body->append( $script );
        }

        foreach( $this->m_scripts['inline'] as $s )
        {
            $script = parent::create('script');
            $script->attr('type','text/javascript');
            $script->text($s);
            $body->append( $script );
        }

		$d = $this->getDocument();
		$d->preserveWhiteSpace  = true;
		$d->recover             = false;
		$d->formatOutput        = true;

		$html->append( $head );
		$html->append( $body );
		parent::append( $html );

		$d->normalizeDocument();

        $save = $d->saveHtml(/*null,/*LIBXML_NOEMPTYTAG|LIBXML_NONET|LIBXML_NOXMLDECL|LIBXML_DTDVALID|LIBXML_NOCDATA*/);
        return str_replace($placeholder, $this->body(), $save);
    }

    public function & setData( $a_data )
    {
        switch( gettype($a_data) )
        {
            case 'string':
                $doc = null;
                if( file_exists($a_data) && is_readable($a_data) )
                {
                    $doc = DOMDocument::loadHTMLFile($a_data);
                }
                else
                {
                    $doc = DOMDocument::loadHTML($a_data);
                }
                break;

            case 'object':
                break;
        }
        return $this;
    }
}
