<?php

/*
 * File: action.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * Controller action
 *
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
abstract class Zoombi_Action extends Zoombi_Component
{

	private $m_this;

	/**
	 * Run action
	 */
	abstract public function run();

	public function setController( Zoombi_Controller & $a_controller )
	{
		$this->m_this = $a_controller;
	}

	public function getController()
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
		$v = $this->m_this->$a_name;
		return $v;
	}

	/**
	 * Object setter
	 * @param string $a_name
	 * @param mixed $a_value
	 * @return mixed
	 */
	public function __set( $a_name, $a_value )
	{
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
		return call_user_func_array(array(&$this->m_this, $a_name), $a_args);
	}

}
