<?php

/**
 * Access Entity Object
 */
class ZAeo
{
	const ALLOW = 1;
	const DENY = 0;

	private $m_name;
	private $m_parent;
	private $m_data;
	private $m_title;

	public function __construct( $a_data = null )
	{
		$this->m_name = null;
		$this->m_parent = null;
		$this->m_data = array();

		if($a_data)
			$this->setData($a_data);
	}

	public function & setData( $a_data )
	{
		switch(gettype($a_data))
		{
			default:
				return $this;

			case 'array':
				return $this->fromArray($a_data);

			case 'object':
				return $this->fromObject($a_data);
		}
	}

	public function & fromArray( array $a_array )
	{
		if(isset($a_array['name']) AND is_string($a_array['name']))
			$this->setName($a_array['name']);

		if(isset($a_array['title']) AND is_string($a_array['title']))
			$this->setTitle($a_array['title']);

		if(isset($a_array['parent']) AND is_string($a_array['parent']))
			$this->setParent($a_array['parent']);

		if(isset($a_array['allow']))
			$this->setAllow($a_array['allow']);

		if(isset($a_array['deny']))
			$this->setDeny($a_array['deny']);

		if(isset($a_array['rules']))
			$this->setRules($a_array['rules']);

		return $this;
	}

	public function & fromObject( $a_object )
	{
		if(isset($a_object->name))
			$this->setName($a_object->name);

		if(isset($a_object->parent))
			$this->setParent($a_object->parent);

		if(isset($a_object->rules))
			$this->setRules($a_object->rules);

		return $this;
	}

	public function & setParent( $a_parent )
	{
		$this->m_parent = strval($a_parent);
		return $this;
	}

	public function getParent()
	{
		return $this->m_parent;
	}

	public function & setName( $a_name )
	{
		$this->m_name = strval($a_name);
		return $this;
	}

	public function & setTitle( $a_title )
	{
		$this->m_title = strval($a_title);
		return $this;
	}

	public function getName()
	{
		return $this->m_name;
	}

	public function getTitle()
	{
		return $this->m_title;
	}

	function & setRules( array $a_rules, $a_type = null )
	{
		return $this->clearRules()->addRules($a_rules, $a_type);
	}

	function & addRules( array $a_rules, $a_type = self::DENY )
	{
		switch($a_type)
		{
			case null:
				foreach($a_rules as $k => $v)
					if($v === self::DENY)
						$this->addRule($k, self::DENY);
					else if($v === self::ALLOW)
						$this->addRule($k, self::ALLOW);
					else
						$this->addRule($k);
				break;

			case self::DENY:
				foreach($a_rules as $v)
					if($v === self::DENY)
						$this->addRule($v, self::DENY);
				break;

			case self::ALLOW:
				foreach($a_rules as $v)
					if($v === self::ALLOW)
						$this->addRule($v, self::ALLOW);
				break;
		}
		return $this;
	}

	function & addRule( $a_rule, $a_type = self::DENY )
	{
		switch(gettype($a_rule))
		{
			case 'array':
				foreach($a_rule as $v)
					$this->addRule($v, $a_type);
				break;

			case 'string':
				foreach(explode(' ', $a_rule) as $v)
				{
					$name = strtolower(trim(strval($v)));
					if(empty($name))
						continue;

					$e = ($a_type === self::ALLOW OR $a_type === self::DENY) ? $a_type : self::DENY;
					$this->m_data[$name] = $e;
				}
				break;
		}
		return $this;
	}

	function & removeRule( $a_name )
	{
		if(isset($this->m_data[$a_name]))
			unset($this->m_data[$a_name]);

		return $this;
	}

	function & clearRules( $a_type = null )
	{
		if($a_type === self::DENY OR $a_type === self::ALLOW)
		{
			foreach($this->m_data as $k => $v)
			{
				if($v === $a_type)
				{
					unset($this->m_data[$k]);
				}
			}
		}
		else
			$this->m_data = array();

		return $this;
	}

	function & clearAllow()
	{
		return $this->clearRules(self::ALLOW);
	}

	function & clearDeny()
	{
		return $this->clearRules(self::DENY);
	}

	function getRules()
	{
		return $this->m_data;
	}

	public function & setAllow( $a_rules )
	{
		return $this->clearAllow()->addAllow($a_rules);
	}

	function & addAllow( $a_rules )
	{
		return $this->addRule($a_rules, self::ALLOW);
	}

	public function & setDeny( $a_rules )
	{
		return $this->clearDeny()->addDeny($a_rules);
	}

	function & addDeny( $a_rules )
	{
		return $this->addRule($a_rules, self::DENY);
	}

	public function getAllow()
	{
		$allow = array();
		foreach($this->m_data as $k => $v)
			if($v === self::ALLOW)
				$allow[] = $k;
		return $allow;
	}

	public function getDeny()
	{
		$deny = array();
		foreach($this->m_data as $k => $v)
			if($v === self::DENY)
				$deny[] = $k;
		return $deny;
	}

	function hasRule( $a_rule )
	{
		return isset($this->m_data[strval($a_rule)]);
	}

	public function isAllow( $a_rule )
	{
		foreach($this->getAllow() as $d)
		{
			$pattern = addcslashes($d, "/.+?|()[]{}\\");
			$pattern = str_replace('*', '.*', $pattern);
			$pattern = '/^' . $pattern . '$/i';
			if(preg_match($pattern, $a_rule) > 0)
				return true;
		}

		return null;
	}

	public function isDeny( $a_rule )
	{
		foreach($this->getDeny() as $d)
		{
			$pattern = addcslashes($d, "/.+?|()[]{}\\");
			$pattern = str_replace('*', '.*', $pattern);
			$pattern = '/^' . $pattern . '$/i';


			if(preg_match($pattern, $a_rule) > 0)
			{
				return true;
			}
		}

		return null;
	}

	public function __get( $a_name )
	{
		switch($a_name)
		{
			case 'name':
				return $this->getName();

			case 'parent':
				return $this->getParent();

			case 'allow':
				return $this->getAllow();

			case 'deny':
				return $this->getDeny();

			case 'rules':
				return $this->getRules();
		}
	}

	public function & __set( $a_name, $a_value )
	{
		switch($a_name)
		{
			case 'name':
				return $this->setName($a_value);

			case 'parent':
				return $this->setParent($a_value);

			case 'allow':
				return $this->setAllow($a_value);

			case 'deny':
				return $this->setDeny($a_value);

			case 'rules':
				return $this->setRules($a_value);
		}
	}

	public function __isset( $name )
	{
		switch($a_name)
		{
			default:
				return false;

			case 'name':
			case 'parent':
			case 'allow':
			case 'deny':
			case 'rules':
				return true;
		}
	}

}
