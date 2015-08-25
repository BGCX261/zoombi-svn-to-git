<?php

class ZSession extends ZRegistry
{
	/**
	 * Singleton instance
	 * @var ZApplication
	 */
	static protected $m_instance = null;

	/**
	 * Get application instance
	 * @return ZApplication
	 */
	static public final function & getInstance()
	{
		if( self::$m_instance === null )
			self::$m_instance = new self;
		return self::$m_instance;
	}

	public function __construct( $a_delimeter = null )
	{
		parent::__construct(array(), $a_delimeter);
		if( session_id() == '' )
			session_start();

		$this->setDataRef($_SESSION);
	}

	public function  __destruct()
	{
		//$_SESSION = $this->getDataRef();
	}

	private function __clone()
	{

	}

	public function __call( $a_name, $a_arguments )
	{
		$prefix = 'session_'.$a_name;
		if( function_exists($prefix) )
			return call_user_func_array( $prefix, $a_arguments );
	}

	public static function __callStatic( $a_name, $a_arguments )
	{
		$prefix = 'session_'.$a_name;
		if( function_exists($prefix) )
			return call_user_func_array( $prefix, $a_arguments );
	}
}