<?php

class ZRoutePath
{
	public $module;
	public $controller;
	public $action;

	public function __construct( $a_data = null )
	{
		switch( gettype($a_data) )
		{
			case 'object':
				$this->module =& $a_data->module;
				$this->controller =& $a_data->controller;
				$this->action =& $a_data->action;
				break;

			case 'array':

				if( isset($a_data['module']) )
					$this->module = $a_data['module'];

				if( isset($a_data['controller']) )
					$this->controller = $a_data['controller'];

				if( isset($a_data['action']) )
					$this->action = $a_data['action'];

				break;
		}
	}

	public function isValid()
	{
		return $this->module && $this->controller && $this->action;
	}

	public function isInvalid()
	{
		return !$this->isValid();
	}

	public function __toString()
	{
		$map = '';
		if( $this->module )
			$map .= $this->module->getName();

		if( $this->controller )
			$map .= Zoombi::SS . $this->controller->getName();

		if( $this->action )
			$map .= Zoombi::SS . $this->action;

		return $map;
	}
}