<?php
/*
 * File: language.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

class ZLanguage
{
	private $m_path;
	private $m_lang;
	private $m_locale;
	private $m_inis;
	private $m_braces;

	public function  __construct()
	{
		$this->m_locale = array();
		$this->m_inis = array();
		$this->m_path = '';
		$this->m_braces = array(0=>'',1=>'');
	}

	public final function setPath( $a_path )
	{
		$this->m_path = $a_path;
	}

	public final function setBraces( array $a_braces = array('','') )
	{
		if( !$a_braces || !is_array($a_braces) || count($a_braces) != 2 )
		return;

		$this->m_braces = $a_braces;
	}

	public final function setBraceStart( $a_start )
	{
		if( !$a_start || !is_string($a_braces) )
		return;

		$this->m_braces[0] = $a_start;
	}

	public final function setBraceEnd( $a_end )
	{
		if( !$a_end || !is_string($a_end) )
		return;

		$this->m_braces[1] = $a_end;
	}

	public final function setLocale( $a_lang )
	{
		$path = $this->m_path. Zoombi::DS .$a_lang. Zoombi::DS;


		if( is_dir($path) == false )
		{
			zlog('Language path: ' . $path . ' is not exist.');
			return;
		}

		$handle = opendir($path);
		if( $handle === false )
		return;

		while (false !== ($file = readdir($handle)))
		{
			if( strpos($file, '.ini') > 0 )
			{
				$filepath = $path.$file;
				$this->m_inis[$file] = new ZIni($filepath);
			}
		}

		$array = array();
		foreach ( $this->m_inis as $ini )
		{
			if( !$ini ) continue;

			$array = array_merge( $array, $ini->values() );
		}
		$this->m_locale = array_change_key_case($array, CASE_UPPER );
	}

	public final function translateText( $a_text )
	{
		return ZTemplate::fill($a_text, $this->m_locale, $this->m_braces);
	}

	public final function translateDocument( ZDocument & $a_doc )
	{
		$a_doc->bodySet( $this->translateText( $a_doc->body() ) );
	}

	public final function setItem( $key, $value )
	{
		if( !$item || is_string($item) == false )
		return;

		if( !$value || is_string($value) == false )
		return;

		$this->m_locale[ strtoupper($key) ] = $value;

		return true;
	}

	public final function lang()
	{
		return $this->m_lang;
	}

	public final function translate( $a_label, $a_default = null )
	{
		$key = strtoupper( $a_label );
		return array_key_exists($key, $this->m_locale) ? $this->m_locale[ $key ] : $a_default;
	}
}
