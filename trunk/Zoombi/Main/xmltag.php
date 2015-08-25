<?php

/*
 * File: xmptag.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


class Zoombi_XmlTag
{

	/**
	 * @var DOMElement|DOMNode
	 */
	private $m_node;

	public function __construct( DOMNode & $a_tag )
	{
		$this->m_node = $a_tag;
	}

	public function & node()
	{
		return $this->m_node;
	}

	public function & appendNode( DOMNode & $a_tag )
	{
		switch($this->m_node->nodeType)
		{
			case XML_DOCUMENT_NODE:
				$node = $this->m_node->isSameNode($a_tag->ownerDocument) ? $a_tag : $this->m_node->importNode($a_tag, true);
				$this->m_node->appendChild($a_tag);
				return $this;

			case XML_ELEMENT_NODE:
				$node = $this->m_node->ownerDocument->isSameNode($a_tag->ownerDocument) ? $a_tag : $this->m_node->ownerDocument->importNode($a_tag, true);
				$this->m_node->appendChild($node);
				return $this;
		}
		return $this;
	}

	public function append( & $a_data )
	{
		switch(gettype($a_data))
		{
			default:
				$this->text($a_data);
				break;

			case 'array':
				foreach($a_data as $v)
					$this->append($v);
				break;

			case 'object':
				if($a_data instanceof Zoombi_XmlTag)
					$this->appendNode($a_data->node());
				else if($a_data instanceof DOMNode)
					$this->appendNode($a_data);
				break;
		}
	}

	public function text( $a_value = null )
	{
		if(func_num_args() == 0)
		{
			return $this->m_node->textContent;
		}

		$d = $this->m_node->ownerDocument;
		$node = $d->createTextNode($a_value);
		try
		{
			$this->m_node->appendChild($node);
		}
		catch(Exception $e)
		{
			
		}
	}

	public function cdata( $a_value = null )
	{
		if(func_num_args() == 0)
		{
			return $this->m_node->textContent;
		}

		$d = $this->m_node->ownerDocument;
		$node = $d->createCDATASection($a_value);
		try
		{
			$this->m_node->appendChild($node);
		}
		catch(Exception $e)
		{
			
		}
	}

	public function attr( $a_name, $a_value = null )
	{
		if(func_num_args() == 0)
		{
			return $this->m_node->getAttribute($a_name);
		}

		if(func_num_args() > 1)
			$this->m_node->setAttribute($a_name, $a_value);

		if(func_num_args() == 1)
			$this->m_node->getAttribute($a_name);
	}

	public function adopt( $a_data )
	{
		$frag = $this->m_node->ownerDocument->createDocumentFragment();
		$frag->appendXML($a_data);
		$this->m_node->appendChild($frag);
	}

	public function & getDocument()
	{
		$this->m_node->ownerDocument;
	}

	public function __toString()
	{
		$name = $this->node()->tagName;
		$attrs = array();
		$childs = array();
		foreach($this->node()->childNodes as $c)
		{
			if($c->nodeType == XML_ELEMENT_NODE)
				$childs[] = (string)new Zoombi_XmlTag($c);
			if($c->nodeType == XML_ATTRIBUTE_NODE)
				$attrs[] = $c->nodeName . '="' . $c->nodeValue . '"';
		}
		return '<' . $name . (count($attrs) > 0) ? ' ' : '' . implode('', $attrs) . (count($attrs) > 0) ? ' ' : '' . '>' . implode('', $childs) . '</' . $name . '>';
	}

}

?>
