<?php

/*
 * File: session.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


class Zoombi_Session extends Zoombi_Registry
{

	/**
	 * Singleton instance
	 * @var Zoombi_Session
	 */
	static protected $m_instance = null;

	/**
	 * Get application instance
	 * @return Zoombi_Session
	 */
	static public final function & getInstance()
	{
		if(self::$m_instance === null)
			self::$m_instance = new self;
		return self::$m_instance;
	}

	public function __construct( $a_delimeter = null )
	{
		parent::__construct(array(), $a_delimeter);
		$id = session_id();
		if(empty($id))
			@session_start();

		$this->setDataRef($_SESSION);
	}

	public function __destruct()
	{
		
	}

	private function __clone()
	{
		
	}

	public function __call( $a_name, $a_arguments )
	{
		$function = 'session_' . $a_name;
		if(function_exists($function))
			return call_user_func_array($function, $a_arguments);
	}

	public static function __callStatic( $a_name, $a_arguments )
	{
		$prefix = 'session_' . $a_name;
		if(function_exists($prefix))
			return call_user_func_array($prefix, $a_arguments);
	}

}