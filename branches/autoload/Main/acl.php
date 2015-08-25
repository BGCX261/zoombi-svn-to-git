<?php

/*
 * File: acl.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

class Zoombi_Acl extends Zoombi_Object
{
	const DELIMETER = '/';

	private $m_aeos;

	public function __construct( $a_data = null )
	{
		$this->m_aeos = array();
		if($a_data)
			$this->setData($a_data);
	}

	public function & setData( $a_data )
	{
		$c = new Zoombi_Config($a_data);
		$data = $c->toArray();
		unset($c);

		return $this->add($data);
	}

	public function & getData()
	{
		return $this->m_aeos;
	}

	public function parents()
	{
		$out = array();
		foreach($this->m_aeos as $a)
			$out[] = $a->getName();
		return array_unique($out);
	}

	public function getTitle( $a_name )
	{
		if($this->has($a_name))
			return $this->get($a_name)->getTitle();
	}

	function & remove( $a_name )
	{
		switch(gettype($a_name))
		{
			case 'object':
				if($a_name instanceof Zoombi_Aeo)
					foreach($this->m_aeos as $k => $v)
						if($v === $a_name)
							unset($this->m_aeos[$k]);
				break;

			case 'array':
				foreach($a_name as $v)
					$this->remove($v);
				break;

			case 'string':
				foreach(explode(' ', $this->m_aeos) as $k => $v)
					if(isset($this->m_aeos[$k]))
						unset($this->m_aeos[$k]);

				break;
		}
		return $this;
	}

	function & clear()
	{
		foreach($this->m_aeos as &$a)
			unset($a);
		return $this;
	}

	private function & _append( Zoombi_Aeo & $a_aeo )
	{
		if($this->has($a_aeo->getName()))
		{
			$this->m_aeos[$a_aeo->getName()]->addRules($a_aeo->getRules());
			return $this;
		}
		$this->m_aeos[$a_aeo->getName()] = $a_aeo;
		return $this;
	}

	public function & add( $a_aeo )
	{
		switch(gettype($a_aeo))
		{
			case 'object':
				if($a_aeo instanceof Zoombi_Aeo)
					$this->_append($a_aeo);
				break;

			case 'array':
				if(isset($a_aeo['name']))
					return $this->_append(new Zoombi_Aeo($a_aeo));

				foreach($a_aeo as $a)
					$this->add($a);
				break;
		}
		return $this;
	}

	public function & get( $a_name )
	{
		$name = strval($a_name);
		return $this->m_aeos[(string)$a_name];
	}

	public function has( $a_name )
	{
		return isset($this->m_aeos[(string)$a_name]);
	}

	public function check( $a_aro, $a_aco )
	{
		$aco = strtolower(trim((string)$a_aco));
		$arr = explode(self::DELIMETER, (string)$a_aro);
		$aro = strtolower(trim(end($arr)));

		if(empty($aro))
			return false;

		if(!$this->has($aro))
			return false;

		$a = $this->get($aro);
		if(!$a)
			return false;

		if($a->isAllow($aco) === true && $a->isDeny($aco) !== true)
			return true;

		if($a->isDeny($aco) === true)
			return false;

		$p = $a->getParent();
		if($this->has($p))
			return $this->check($p, $aco);

		return false;
	}

}
