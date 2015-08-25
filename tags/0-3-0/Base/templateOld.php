<?php
/*
 * File: template.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Template class
 */
class ZTemplate extends ZRegistry
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
	public function __construct( $a_template = null, $a_data = null )
	{
		parent::__construct( $a_data );
		$this->setTemplate( $a_template );
		$this->m_brackets = array('','');
	}
	
	/**
	 * Set template brackets
	 * @param array $a_brackets
	 * @return ZTemplate
	 */
	public function & setBrackets( array $a_brackets )
	{
		if( isset( $a_brackets[0] ) )
			$this->setBracketBegin( $a_brackets[0] );
			
		if( isset( $a_brackets[1] ) )
			$this->setBracketEnd( $a_brackets[1] );
			
		return $this;
	}
	
	/**
	 * Set template begin bracket
	 * @param string $a_begin
	 * @return ZTemplate
	 */
	public function & setBracketBegin( $a_begin )
	{
		$this->m_brackets[0] = (string)$a_begin;
		return $this;
	}
	
	/**
	 * Set template end brackets
	 * @param string $a_end
	 * @return ZTemplate
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
	 * @return ZTemplate
	 */	
	public final function & assign( $a_data )
	{
		$this->setData( $a_data );
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
	 * @return ZTemplate
	 */
	public final function & setTemplate( $a_template )
	{
		$this->m_template = (string)$a_template;
		return $this;
	}

    /**
     * Set empty fill
     * @param bool $a_empty
     * @return ZTemplate
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
	 * Fill template with new values using parse method
	 * @param string $a_template
	 * @param array $a_data
	 * @param array $a_brace
	 * @return string
	 */
	public function parse()
	{
		if( $this->count() < 1 )
			return $this->getTemplate();
			
		$ts = $this->getBracketBegin();
		$te = $this->getBracketEnd();

		$fvar = array();

		$template = $this->getTemplate();
		$lastpos = 0;
		$strlen = strlen( $template ); 
		$output = $template;
		do
		{
			$s = stripos( $template, $ts, $lastpos );

			if( $s === false )
				break;

			$e = stripos( $template, $te, $s );
			$lastpos = $s+1;

			if( $e === false )
				break;

			$sub = substr( $template, $s, ($e - $s) + strlen($te) );

			if( empty($sub) )
				continue;

			$key = strtolower( substr( $sub, strlen($ts), -(strlen($te)) ) );

			if( empty($key) )
				continue;

			$options = explode( '|', $key );
			$tkey = array_shift($options);
			
			$v = $this->getValue( (string)$tkey, ZTemplate::TEMPLATE_VALUE_NOTFOUND );

			if( $v == self::TEMPLATE_VALUE_NOTFOUND )
			{
				if( $this->getEmpty() )
					$fvar[ $sub ] = null;
				
				continue;
			}
				
			foreach( $options as $option )
			{
				switch( strtolower( $option ) )
				{
					case 'strtoupper':
					case 'uppercase':
						$v = strtoupper($v);
						break;

					case 'strtolower':
					case 'lowercase':
						$v = strtolower($v);
						break;

					case 'ucwords':
					case 'cammelcase':
						$v = ucwords($v);
						break;

					case 'ucfirst':
					case 'upperfirst':
						$v = ucfirst($v);
						break;

					case 'strtoint':
					case 'tointeger':
					case 'integer':
					case 'toint':
					case 'int':
						$v = intval($v);
						break;
				}
			}
			$fvar[ $sub ] = $v;
		}
		while( $strlen >= $lastpos );
		return str_ireplace( array_keys($fvar), array_values($fvar), $output);
	}
	
	/**
	 * To string conversion
	 * @return string
	 */
	public function __toString()
	{
		return $this->parse();
	}
}
