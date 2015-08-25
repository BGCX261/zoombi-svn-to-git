<?php

/*
 * File: objectbinder.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * ZObject this binder
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class ZObjectBinder extends ZObject
{

	/**
	 * Binded object
	 * @var ZObject
	 */
	private $m_this;

	/**
	 * Constructor
	 * @param ZObject $a_parent
	 * @param string $a_name
	 */
	public function __construct( ZObject & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent->getModule(), $a_name);
	}

	/**
	 * Bind object
	 * @param ZObject $a_this
	 * @return ZObjectBinder
	 */
	protected function & setThis( ZObject & $a_this )
	{
		if($this->m_this !== $a_this)
			$this->m_this = $a_this;
		return $this;
	}

	/**
	 * Get binded object
	 * @return ZObject
	 */
	protected function & getThis()
	{
		return $this->m_this;
	}

	/**
	 * Object getter
	 * @param string $a_name
	 * @return mixed
	 */
	public function & __get( $a_name )
	{
		$g = null;
		if(!$this->m_this)
			$g = parent::__get($a_name);
		else
			$g = $this->m_this->$a_name;
		return $g;
	}

	/**
	 * Object setter
	 * @param string $a_name
	 * @param mixed $a_value
	 * @return mixed
	 */
	public function __set( $a_name, $a_value )
	{
		if(!$this->m_this)
			parent::__set($a_name, $a_value);

		$this->m_this->$a_name = $a_value;
	}

	/**
	 * Object caller
	 * @param string $a_name
	 * @param array $a_args
	 * @return mixed
	 */
	public function __call( $a_name, $a_args )
	{
		if(!$this->m_this)
			trigger_error('Method not found: ' . $a_name, E_USER_WARNING);

		if(method_exists($this->m_this, $a_name) && is_callable(array(&$this->m_this, $a_name)))
			return call_user_func_array(array(&$this->m_this, $a_name), $a_args);
	}

}
