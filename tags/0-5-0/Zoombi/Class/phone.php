<?php

/*
 * File: phone.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

class ZPhone
{

	public $country;
	public $operator;
	public $alpha;
	public $beta;
	public $omega;

	private static function _crop( & $a_str, $a_len )
	{
		$len = intval($a_len);
		$s = substr($a_str, 0, $len);
		$a_str = substr($a_str, $len);
		return $s;
	}

	public function __construct()
	{
		if( func_num_args() > 0 )
		{
			$arg = func_get_arg(0);
			switch( $arg )
			{
				case 'integer':
					$this->fromArray( self::parse( strval($arg) ) );
					break;

				case 'string':
					$this->fromArray( self::parse($arg) );
					break;

				case 'array':
					$this->fromArray( $arg );
					break;

				case 'object':
					$this->fromObject( $arg );
					break;
			}
		}
	}

	public function fromArray( array $a_array )
	{
		if( isset($a_array['country']) )
		{
			if( $a_array['country'] == '8' )
				$a_array['country'] = '7';
			$this->country = $a_array['country'];
		}

		if( isset($a_array['operator']) )
			$this->operator = $a_array['operator'];

		if( isset($a_array['alpha']) )
			$this->alpha = $a_array['alpha'];

		if( isset($a_array['beta']) )
			$this->beta = $a_array['beta'];

		if( isset($a_array['omega']) )
			$this->omega = $a_array['omega'];
	}

	public function fromObject( object $a_object )
	{
		return $this->fromArray( get_object_vars($a_object) );
	}

	static public function parse( $a_phone )
	{
		$phone = preg_replace('/[^0-9]/', '', (string)$a_phone);
		$offset = 0;
		$arr = array(
			'country' => null,
			'operator' => null,
			'alpha' => null,
			'beta' => null,
			'omega' => null
		);

		if( strlen($phone) > 10 )
		{
			$s = self::_crop($phone, strlen($phone) - 10);
			$offset += strlen($s);
			$arr['country'] = $s;
		}

		if( strlen($phone) == 10 )
		{
			$s = self::_crop($phone, 3);
			$offset += strlen($s);
			$arr['operator'] = $s;
		}

		if( strlen($phone) == 7 )
		{
			$s = self::_crop($phone, 3);
			$offset += strlen($s);
			$arr['alpha'] = $s;
		}

		if( strlen($phone) == 4 )
		{
			$s = self::_crop($phone, 2);
			$offset += strlen($s);
			$arr['beta'] = $s;
		}

		if( strlen($phone) == 2 )
		{
			$s = self::_crop($phone, 2);
			$offset += strlen($s);
			$arr['omega'] = $s;
		}

		return $arr;
	}

	public static function format( $a_phone )
	{
		$f = new ZPhone($a_phone);
		$o = (string)$f;
		unset($f);
		return $o;
	}

	public function __toString()
	{
		$o = '';

		if( $this->country )
			$o .= $this->country . ' ';

		if( $this->operator )
			$o .= '(' . $this->operator . ') ';

		if( $this->alpha )
			$o .= $this->alpha . '-';

		if( $this->beta )
			$o .= $this->beta . '-';

		if( $this->omega )
			$o .= $this->omega;

		return $o;
	}

}
