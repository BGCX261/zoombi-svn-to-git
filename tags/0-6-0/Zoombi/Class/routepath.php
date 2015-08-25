<?php

class ZRoutePath
{

	/**
	 * @var ZRoutePath $parent;
	 */
	public $parents;

	/**
	 * @var ZController $controller
	 */
	public $controller;

	/**
	 * @var string $action
	 */
	public $action;

	/**
	 * Construct
	 * @param array|object $a_data Data to set 
	 */
	public function __construct( $a_data = null )
	{
		$this->parents = array();

		switch(gettype($a_data))
		{
			case 'object':
				if(isset($a_data->parents))
					$this->parents = $a_data->parents;

				if(isset($a_data->controller))
					$this->controller = & $a_data->controller;

				if(isset($a_data->action))
					$this->action = & $a_data->action;
				break;

			case 'array':
				if(isset($a_data['parents']))
					$this->parents = $a_data['parents'];

				if(isset($a_data['controller']))
					$this->controller = & $a_data['controller'];

				if(isset($a_data['action']))
					$this->action = $a_data['action'];

				break;
		}
	}

	/**
	 * Check if route path is valid
	 * @return bool
	 */
	public function isValid()
	{
		$o = true;

		foreach($this->parents as $p)
		{
			if($p AND $p instanceof ZModule)
				continue;
			else
				return false;
		}

		return ZModule AND $this->controller instanceof ZController AND !empty($this->action);
	}

	/**
	 * Check if route path is invalid
	 * @return bool
	 */
	public function isInvalid()
	{
		return!$this->isValid();
	}

	/**
	 * Convert data to array
	 * @return array
	 */
	public function toArray()
	{
		$parents = array();

		foreach($this->parents as $p)
			$parents [] = (string)$p->getName();

		if($this->controller)
			$parents [] = (string)$this->controller->getName();

		if($this->action)
			$parents [] = (string)$this->action;

		return $parents;
	}

	public function & __get( $a_value )
	{
		if($a_value == 'module')
		{
			if(count($this->parents) > 0)
				return $this->parents[count($this->parents) - 1];
		}
		return Zoombi::null();
	}

	public function __toString()
	{
		$a = $this->toArray();
		return implode(Zoombi::SS, $a);
	}

}