<?php

/*
 * File: jsonencoder.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

class ZJsonEncoder
{

	static private function makePair( $a_key, $a_value )
	{
		$k = is_int($a_key) ? $a_key : '"' . $a_key . '"';
		return $k . ':' . self::toJsonValue($a_value);
	}

	static private function makeArray( array $a_array )
	{
		$data = array();
		foreach($a_array as $k => $v)
			$data[] = self::toJsonValue($v);

		return '[' . implode(',', $data) . ']';
	}

	static private function makeObject( $a_object )
	{
		$data = array();
		foreach(get_object_vars($a_object) as $k => $v)
			$data[] = self::makePair($k, $v);

		return '{' . implode(',', $data) . '}';
	}

	static private function toJsonValue( $a_value )
	{
		switch(gettype($a_value))
		{
			case 'NULL':
				return 'null';

			case 'array':
				return self::makeArray($a_value);

			case 'string':
				return '"' . addcslashes($a_value, "\0\n\r\t\"\\") . '"';


			case 'object':
				return self::makeObject($a_value);

			case 'float':
			case 'double':
			case 'integer':
				return strval($a_value);
		}
		return null;
	}

	static public final function encode( $a_value )
	{
		if(function_exists('json_encode'))
			return json_encode($a_value);

		return self::toJsonValue($a_value);
	}

}
