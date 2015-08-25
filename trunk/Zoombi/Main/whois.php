<?php

/*
 * File: phone.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


class Zoombi_WhoIs
{

	static function query( $a_query )
	{
		$query = trim((string)$a_query);
		if(empty($a_query))
			return;

		exec('whois ' . $query, $output, $return);
		return implode('<br />', $output);
	}

}
