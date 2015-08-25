<?php
/*
 * File: url.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

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
class ZUrl extends ZRegistry
{
	public function __construct( $a_url = null )
	{
		$this->setUrl($a_url);
	}

	public function setUrl( $a_url )
	{
		if( empty($a_url) )
		{
			$this->clear();
			return;
		}

		$p  = parse_url($a_url);
		$this->setData($p);
	}
	
	function __call( $a_method, $a_params )
	{
		switch($a_method)
		{
			case 'url':
				return $this->url;
				
			case 'fragment':
				return $this->fragment;
				
			case 'query':
				return $this->query;
				
			case 'path':
				return $this->path;
				
			case 'pass':
				return $this->pass;
				
			case 'user':
				return $this->user;
				
			case 'port':
				return $this->port;
				
			case 'scheme':
			case 'protocol':
				return $this->scheme;
				
			case 'host':
				return $this->host;
		}
	}
}
