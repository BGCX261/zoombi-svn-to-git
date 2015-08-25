<?php

class Zoombi_Container extends Zoombi_Object
{
	public function __construct( Zoombi_Object & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
	}
}