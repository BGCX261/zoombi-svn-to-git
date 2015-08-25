<?php

/*
 * File: error.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class Zoombi_Error extends Zoombi_Exception
{

	public $module;

	function & setModule( Zoombi_Module & $a_mod )
	{
		$this->module = & $a_mod;
		return $this;
	}

	function & getModule()
	{
		return $this->module;
	}

}
