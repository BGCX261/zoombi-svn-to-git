<?php

/*
 * File: html.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Html document class
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class Zoombi_Document_Html extends Zoombi_Document_Xml
{

	private $m_title = 'document title';
	private $m_meta = array();
	private $m_base;
	private $m_keywords;
	private $m_description;
	private $m_link = array();
	private $m_styles;
	private $m_scripts;
	private $m_body = '';
	private $m_doctype;

	public function __construct()
	{
		parent::__construct(self::DOCTYPE_HTML, 'text/html');
		
		$this->m_doctype = null;
		
		$this->m_styles = array('inline' => array(), 'include' => array(), 'url' => array(), 'header' => array());
		$this->m_scripts = array('inline' => array(), 'include' => array(), 'url' => array(), 'header' => array());
	
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
			'rel' => 'alternate',
			'title' => $a_title,
			'type' => 'application/rss+xml',
			'href' => $a_link
		));
		return $this;
	}

	public function & atomAdd( $a_title, $a_link )
	{
		$this->linkAdd(array(
			'rel' => 'alternate',
			'title' => $a_title,
			'type' => 'application/atom+xml',
			'href' => $a_link
		));
		return $this;
	}

	public function & cssAdd( $style )
	{
		if(is_string($style))
			array_push($this->m_styles['inline'], $style);

		if(is_array($style))
			$this->m_styles['inline'] = array_merge($this->m_styles['inline'], $style);

		return $this;
	}

	public function & cssAddFile( $a_style, $a_media = 'all', $a_head = false )
	{
		switch(gettype($a_style))
		{
			case 'string':
				$this->m_styles['include'][] = array($a_style, $a_media, $a_head);
				break;

			case 'array':
				foreach($a_style as $s)
					$this->cssAddFile($s, $a_media, $a_head);
				break;
		}
		return $this;
	}

	public function & cssAddUrl( $a_style, $a_media = 'all', $a_head = false )
	{
		switch(gettype($a_style))
		{
			case 'string':
				$this->m_styles['url'][] = array($a_style, $a_media, $a_head);
				break;

			case 'array':
				foreach($a_style as $s)
					$this->cssAddUrl($s, $a_media, $a_head);
				break;
		}
		return $this;
	}

	public function & cssIncludeFile( $style, $a_head = false )
	{
		$file = file_get_contents($style);
		if($file)
			$this->cssAdd("/**\n *\t Include file: {$style}\r\n */\r\n" . $file);
		return $this;
	}

	public function & jsAdd( $a_script, $a_head = false )
	{
		if($a_head)
		{
			if(is_string($a_script))
				array_push($this->m_scripts['header'], $a_script);

			if(is_array($a_script))
				$this->m_scripts['header'] = array_merge($this->m_scripts['header'], $a_script);

			return $this;
		}

		if(is_string($a_script))
			array_push($this->m_scripts['inline'], $a_script);

		if(is_array($a_script))
			$this->m_scripts['inline'] = array_merge($this->m_scripts['inline'], $a_script);

		return $this;
	}

	public function & jsAddFile( $a_file, $a_head = false )
	{
		switch(gettype($a_file))
		{
			case 'string':
				if($a_head)
					$this->jsAddUrl($a_file);
				else
					$this->m_scripts['include'][] = $a_file;
				break;

			case 'array':
				if($a_head)
					$this->jsAddUrl($a_file);
				else
					$this->m_scripts['include'] = array_merge($this->m_scripts['include'], $a_file);
				break;
		}
		return $this;
	}

	public function & jsAddUrl( $a_url )
	{
		switch(gettype($a_url))
		{
			case 'string':
				$this->m_scripts['url'][] = $a_url;
				break;

			case 'array':
				$this->m_scripts['url'] = array_merge($this->m_scripts['url'], $a_url);
				break;
		}
		return $this;
	}

	public function & jsIncludeFile( $script )
	{
		$file = file_get_contents($script);
		if($file)
			$this->jsAdd("/*** {$script} ***/\n\r" . $file);

		return $this;
	}

	public function & metaAdd( array $a_meta )
	{
		if(array_key_exists('http-equiv', $a_meta))
		{
			$m = & $a_meta['http-equiv'];
			foreach($this->m_meta as $k => $meta)
			{
				if(array_key_exists('http-equiv', $meta) && $meta['http-equiv'] == $m)
				{
					$this->m_meta[$k] = $a_meta;
					return $this;
				}
			}
		}

		if(array_key_exists('name', $a_meta))
		{
			$m = & $a_meta['name'];
			foreach($this->m_meta as $k => $meta)
			{
				if(array_key_exists('name', $meta) && $meta['name'] == $m)
				{
					$this->m_meta[$k] = $a_meta;
					return $this;
				}
			}
		}
		$this->m_meta[] = $a_meta;
		return $this;
	}

	public function & metaAddHttp( $a_equiv, $a_content )
	{
		$this->metaAdd(array(
			'http-equiv' => $a_equiv,
			'content' => $a_content
		));
		return $this;
	}

	public function & metaAddName( $a_name, $a_content )
	{
		$this->metaAdd(array(
			'name' => $a_name,
			'content' => $a_content
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
		if($a_base === null)
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

	public function & setDoctype( $a_doctype, $a_namespace = null )
	{
		if(is_string($a_doctype))
		{
			$doctype = trim(strtolower($a_doctype));
			$types = array(
				'xhtml1-strict' => array(
					'html',
					'-//W3C//DTD XHTML 1.0 Strict//EN',
					'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'
				),
				'xhtml1-tran' => array(
					'html',
					'-//W3C//DTD XHTML 1.0 Transitional//EN',
					'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'
				),
				'xhtml1-frame' => array(
					'html',
					'-//W3C//DTD XHTML 1.0 Frameset//EN',
					'http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd'
				),
				'xhtml11' => array(
					'html',
					'-//W3C//DTD XHTML 1.1//EN',
					'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'
				),
				'html4-strict' => array(
					'html',
					'-//W3C//DTD HTML 4.01//EN',
					'http://www.w3.org/TR/html4/strict.dtd'
				),
				'html4-trans' => array(
					'html',
					'-//W3C//DTD HTML 4.01 Transitional//EN',
					'http://www.w3.org/TR/html4/loose.dtd'
				),
				'html4-frame' => array(
					'html',
					'-//W3C//DTD HTML 4.01 Frameset//EN',
					'http://www.w3.org/TR/html4/frameset.dtd'
				),
				'html5' => array(
					'html',
					null,
					null
				)
			);

			if(!isset($types[$doctype]))
				$doctype = 'html4-trans';

			return parent::setDoctype($types[$doctype], $a_namespace);
		}
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
		if($a_body === null)
			return $this->bodyGet();

		return $this->bodySet($a_body);
	}

	public function compile()
	{
		/* $doc_impl = new DOMImplementation;
		  $implType = $doc_impl->createDocumentType($this->m_doctype[0], $this->m_doctype[1], $this->m_doctype[2]);
		  $doc = null;
		  if( $implType )
		  $doc = $doc_impl->createDocument(null, $this->m_doctype[0], $implType);
		  else
		  return;
		 */
		//$e = parent::getCharset();
		//parent::setDocument($doc);
		//parent::setCharset($e);

		$html = parent::getRoot();
		if($this->getDocument()->doctype->name == 'xhtml')
		{
			$html->attr('xmlns', 'http://www.w3.org/1999/xhtml');
			$html->attr('xml:lang', 'en');
		}

		$head = parent::create('head');
		$body = parent::create('body');

		$title = parent::create('title');
		$title->text($this->m_title);
		$head->append($title);

		if($this->m_base)
		{
			$base = parent::create('base');
			$base->attr('href', $this->m_base);
			$head->append($base);
		}
		$doc = '';

		//$this->metaAddHttp( 'Content-Type', 'text/html; charset=' . $this->getEncoding() );

		if($this->m_keywords)
		{
			$this->metaAddName('keywords', $this->m_keywords);
		}

		if($this->m_description)
		{
			$this->metaAddName('description', $this->m_description);
		}

		foreach($this->m_meta as $m)
		{
			if(!is_array($m) || count($m) < 0)
				continue;

			$mnode = parent::create('meta');
			foreach($m as $key => $val)
				$mnode->attr($key, $val);
			$head->append($mnode);
		}

		foreach($this->m_styles['inline'] as $s)
		{
			$style = parent::create('style');
			$style->attr('type', 'text/css');
			$style->text($s);
			$head->append($style);
		}

		foreach($this->m_link as $link)
		{
			if(!is_array($link) || count($link) < 0)
				continue;

			$l = parent::create('link');
			foreach($link as $key => $val)
			{
				$l->attr($key, $val);
			}
			$head->append($l);
		}

		foreach($this->m_scripts['header'] as $s)
		{
			$script = parent::create('script');
			$script->attr('type', 'text/javascript');
			$script->text($s);
			$head->append($script);
		}

		foreach($this->m_styles['url'] as $s)
		{
			$style = parent::create('link');
			$style->attr('rel', 'stylesheet');
			$style->attr('type', 'text/css');
			$style->attr('media', $s[1]);
			$style->attr('href', $s[0]);
			$head->append($style);
		}

		foreach($this->m_styles['include'] as $s)
		{
			$style = parent::create('link');
			$style->attr('rel', 'stylesheet');
			$style->attr('type', 'text/css');
			$style->attr('media', $s[1]);
			$style->attr('href', $s[0]);
			$head->append($style);
		}

		foreach($this->m_scripts['url'] as $s)
		{
			$script = parent::create('script');
			$script->attr('type', 'text/javascript');
			$script->attr('src', $s);
			$script->text('');
			$head->append($script);
		}

		$placeholder = '[-----REPLACE-HERE-' . md5(time()) . '-----]';

		$body->text($placeholder);

		foreach($this->m_scripts['include'] as $s)
		{
			$script = parent::create('script');
			$script->attr('type', 'text/javascript');
			$script->attr('src', $s);
			$script->text('');
			$body->append($script);
		}

		foreach($this->m_scripts['inline'] as $s)
		{
			$script = parent::create('script');
			$script->attr('type', 'text/javascript');
			$script->text($s);
			$body->append($script);
		}

		$d = $this->getDocument();

		$d->preserveWhiteSpace = true;
		$d->recover = false;
		$d->formatOutput = true;

		$html->append($head);
		$html->append($body);
		parent::append($html);

		$d->normalizeDocument();

		$content = str_replace($placeholder, $this->body(), $d->saveHtml(/* null,/*LIBXML_NOEMPTYTAG|LIBXML_NONET|LIBXML_NOXMLDECL|LIBXML_DTDVALID|LIBXML_NOCDATA */));
		return $content;
	}

	public function & setData( $a_data )
	{
		switch(gettype($a_data))
		{
			case 'string':
				$doc = null;
				if(file_exists($a_data) AND is_file($a_data) AND is_readable($a_data))
					$doc = DOMDocument::loadHTMLFile($a_data);
				else
					$doc = DOMDocument::loadHTML($a_data);
				break;

			case 'object':
				break;
		}
		return $this;
	}

}
