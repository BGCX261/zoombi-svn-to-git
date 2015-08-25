<?php

/*
 * File: jsonencoder.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

class ZXmlEncoder
{

	private function makePair( $a_key, $a_value )
	{
		return '<' . $a_key . '>' . $a_value . '</' . $a_key . '>';
	}

	private function toXmlValue( $a_key, $a_value )
	{
		switch(gettype($a_value))
		{
			default:
				return makePair($a_key, $a_value);

			case 'string':
				if(preg_match("/\\n\\r\\t/i", $a_value))
					return $this->makePair($a_key, '<![CDATA[' . $a_value . ']]>');
				return $this->makePair($a_key, addcslashes($a_value, "\n\r\t\0\""));

			case 'array':
				$o = null;
				foreach($a_value as $k => $v)
					$o .= $this->toXmlValue($k, $v);
				return $this->makePair($a_key, $o);

			case 'object':
				$o = null;
				foreach(get_object_vars($a_value) as $k => $v)
					$o .= $this->toXmlValue($k, $v);
				return $this->makePair($a_key, $o);
		}
		return null;
	}

	static public final function encode( $a_value, $a_root = 'root' )
	{
		$x = new ZXmlEncoder();
		$v = $x->toXmlValue($a_root, $a_value);
		unset($x);
		return $v;
	}

}
