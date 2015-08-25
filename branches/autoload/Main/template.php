<?php

/*
 * File: template.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file a part of Zoombi PHP Framework
 */


/**
 * Template class
 */
class Zoombi_Template extends Zoombi_Registry
{
	const TEMPLATE_VALUE_NOTFOUND = 0xDEADBEEF;
	private $m_template;
	private $m_brackets;
	private $m_empty;

	/**
	 * Constructor
	 * @param string $a_template Template
	 * @param array $a_data Data
	 */
	public function __construct( $a_template = null, $a_data = null, $a_brackets = array(null, null) )
	{
		parent::__construct($a_data);
		$this->setTemplate($a_template)->setBrackets($a_brackets);
	}

	/**
	 * Set template brackets
	 * @param array $a_brackets
	 * @return Zoombi_Template
	 */
	public function & setBrackets( array $a_brackets )
	{
		if(isset($a_brackets[0]))
			$this->setBracketBegin($a_brackets[0]);

		if(isset($a_brackets[1]))
			$this->setBracketEnd($a_brackets[1]);

		return $this;
	}

	/**
	 * Set template begin bracket
	 * @param string $a_begin
	 * @return Zoombi_Template
	 */
	public function & setBracketBegin( $a_begin )
	{
		$this->m_brackets[0] = (string)$a_begin;
		return $this;
	}

	/**
	 * Set template end brackets
	 * @param string $a_end
	 * @return Zoombi_Template
	 */
	public function & setBracketEnd( $a_end )
	{
		$this->m_brackets[1] = (string)$a_end;
		return $this;
	}

	/**
	 * Get template brackets
	 * @return array
	 */
	public function getBrackets()
	{
		return $this->m_brackets;
	}

	/**
	 * Get template begin bracket
	 * @return string
	 */
	public function getBracketBegin()
	{
		return $this->m_brackets[0];
	}

	/**
	 * Get template end bracket
	 * @return string
	 */
	public function getBracketEnd()
	{
		return $this->m_brackets[1];
	}

	/**
	 * Assign variables
	 * @param mixed $a_data
	 * @return Zoombi_Template
	 */
	public final function & assign( $a_data )
	{
		$this->setData($a_data);
		return $this;
	}

	/**
	 * Get template
	 * @return string
	 */
	public final function & getTemplate()
	{
		return $this->m_template;
	}

	/**
	 * Set template
	 * @param string $a_template
	 * @return Zoombi_Template
	 */
	public final function & setTemplate( $a_template )
	{
		$this->m_template = (string)$a_template;
		return $this;
	}

	/**
	 * Set empty fill
	 * @param bool $a_empty
	 * @return Zoombi_Template
	 */
	public final function & setEmpty( $a_empty )
	{
		$this->m_empty = ( $a_empty );
		return $this;
	}

	/**
	 * Get empty fille
	 * @return bool
	 */
	public final function getEmpty()
	{
		return ($this->m_empty);
	}

	/**
	 * Replace callback
	 * @param array $a_matches
	 * @return string
	 */
	public function _callback( $a_matches )
	{
		$v = $this->getValue($a_matches[1], Zoombi_Template::TEMPLATE_VALUE_NOTFOUND);
		if($v == self::TEMPLATE_VALUE_NOTFOUND)
			return $this->m_empty;

		switch(strtolower($a_matches[2]))
		{
			case 'strtoupper':
			case 'uppercase':
				return strtoupper($v);

			case 'strtolower':
			case 'lowercase':
				return strtolower($v);

			case 'ucwords':
			case 'cammelcase':
				return ucwords($v);

			case 'ucfirst':
			case 'upperfirst':
				return ucfirst($v);

			case 'strtoint':
			case 'tointeger':
			case 'integer':
			case 'toint':
			case 'int':
				return intval($v);

			case 'callback':
				$e = explode('->', $v);
				$func = $e[0];
				if(count($e) > 1)
					$func = array($e[0], $e[1]);

				if($func AND is_callable($func))
					return call_user_func($func);
		}
		return $v;
	}

	/**
	 * Fill template with new values using parse method
	 * @return string
	 */
	public function parse()
	{
		if($this->count() < 1)
			return $this->getTemplate();

		$bs = addcslashes($this->getBracketBegin(), "/.*+?|()[]{}\\");
		$be = addcslashes($this->getBracketEnd(), "/.*+?|()[]{}\\");
		$preg = '/' . $bs . '((?:[\w\d\.?]+))+\|?((?:[\w\d]*))' . $be . '/im';
		return preg_replace_callback($preg, array($this, '_callback'), $this->getTemplate());
	}

	/**
	 * To string conversion
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->parse();
	}

	static public function compile( $a_template, $a_data = null, $a_brackets = null )
	{
		$t = new Zoombi_Template;
		if($a_template)
			$t->setTemplate($a_template);

		if($a_data)
			$t->setData($a_data);

		if($a_brackets)
			$t->setBrackets($a_brackets);

		$o = $t->parse();
		unset($t);
		return $o;
	}

	static public function output( $a_template, $a_data = null, $a_brackets = null )
	{
		echo self::compile($a_template, $a_data, $a_brackets);
	}

}
