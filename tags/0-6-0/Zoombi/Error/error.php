<?php

class ZError extends ZException
{

	public $module;

	function & setModule( ZModule & $a_mod )
	{
		$this->module = & $a_mod;
		return $this;
	}

	function & getModule()
	{
		return $this->module;
	}

}
