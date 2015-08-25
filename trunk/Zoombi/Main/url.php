<?php

/*
 * File: url.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Url parser class
 * @author Andrew Saponenko <roguevoo@gmail.com>
 * 
 * @method string url
 * @method string fragment
 * @method string query
 * @method string path
 * @method string pass
 * @method string user
 * @method string port
 * @method string scheme
 * @method string protocol
 * @method string host
 * @property string $url
 * @property string $fragment
 * @property string $query
 * @property string $path
 * @property string $pass
 * @property string $user
 * @property string $port
 * @property string $scheme
 * @property string $protocol
 * @property string $host
 */
class Zoombi_Url extends Zoombi_Registry
{

	public function __construct( $a_url = null )
	{
		$this->setUrl($a_url);
	}

	public function setUrl( $a_url )
	{
		if(empty($a_url))
		{
			$this->clear();
			return;
		}

		$p = parse_url($a_url);
		$this->setData($p);
	}

	function __call( $a_method, $a_params )
	{
		switch($a_method)
		{
			case 'scheme':
			case 'protocol':
				return $this->scheme;
		}
		return $this->get($a_method);
	}

}
