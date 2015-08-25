<?php

/*
 * File: autoload.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Autoloading for Zoombi PHP Framework
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'boot.php';

if(!function_exists('zoombi_autoload'))
{

	function zoombi_autoload( $class )
	{	
		$segments = explode( '_', $class );
		$is_zoombi = array_shift($segments);
		
		if( $is_zoombi != 'Zoombi' )
			return;

		if( count($segments) == 1 )
			array_unshift($segments, 'Main');

		$filename = array_pop($segments);
		if( count($segments) )
			$filename = DIRECTORY_SEPARATOR . $filename;
		$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $segments) . strtolower($filename) . '.php';

		if(file_exists($file)){
			require_once $file;
		}
		
	}

	spl_autoload_register('zoombi_autoload');
}