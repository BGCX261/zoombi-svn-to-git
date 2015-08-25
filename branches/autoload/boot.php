<?php

/*
 * File: boot.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

define('ZOOMBI_BASE_PATH', realpath(dirname(__FILE__)));

/**
 * Framework boot class.
 * Before use any framework classes
 * or this class methods you must
 * call a startup function
 *
 * @example Zoombi::boot( 'application_directory' );
 *
 * @author Zombie
 *
 * @method Zoombi_Controller controller( $a_name );
 *
 */
class Zoombi
{

	static $null = null;

	const VERSION = '0.7.4';

	const US = '/';
	const SS = '/';
	const DS = DIRECTORY_SEPARATOR;
	const PS = PATH_SEPARATOR;

	/*
	 * Custom error codes
	 */
	const EXC_ERROR = E_USER_ERROR;
	const EXC_WARNING = E_USER_WARNING;
	const EXC_INFO = E_USER_NOTICE;
	const EXC_DEBUG = 3;

	/**
	 * Application instance
	 * @var Zoombi_Application
	 */
	private $m_application;

	/**
	 * Dispatcher instance
	 * @var Zoombi_Event_Dispatcher
	 */
	private $m_dispatcher;

	/**
	 * Singleton instance
	 * @var Zoombi
	 */
	static protected $m_instance;
	
	/**
	 * Config to hold default values
	 * @var Zoombi_Config 
	 */
	static public $defaults;

	/**
	 * Hold application directory
	 * @var string
	 */
	private $m_application_base;

	/**
	 * Constructor
	 */
	protected function __construct()
	{
		$this->m_dispatcher = new Zoombi_Dispatcher();
		self::$defaults = new Zoombi_Config( Zoombi::fromFrameworkDir('defaults.php') );
	}

	/**
	 * Protect from cloning
	 */
	private function __clone()
	{
		
	}

	/**
	 * Get Zoombi instance
	 * @return Zoombi
	 */
	static public function & getInstance()
	{
		if( self::$m_instance == null )
			self::$m_instance = new self;
		
		return self::$m_instance;
	}

	/**
	 * Boot framework
	 * @param string $a_application
	 * @return bool
	 */
	static public final function boot( $a_application )
	{
		if( !is_string($a_application) )
			throw new Exception("Boot method apply only string as first argument");
		
		$i = Zoombi::getInstance();
		$path = realpath((string)$a_application);
		
		if( file_exists($path) == false )
			throw new Exception("Application path not exist: '{$path}'");

		if( is_dir($path) == false )
			throw new Exception("Application directory not exist: '{$path}'");

		if( is_readable($path) == false )
			throw new Exception("Application directory not accessable: '{$path}'");

		$i->m_application_base = $path;

		if( self::ack(Zoombi::getApplication()->getConfig()->get(Zoombi_Module::CONFIG_KEY_AUTO_EXECUTE, false)) )
			self::getApplication()->execute();

		return true;
	}

	static public final function null()
	{
		return null;
	}

	static public final function getBootDir()
	{
		return Zoombi::getInstance()->m_application_base;
	}

	/**
	 * Get Framework base directory
	 * @return string
	 */
	static public final function getFrameworkDir()
	{
		return ZOOMBI_BASE_PATH;
	}

	/**
	 * Construct path from framework base directory
	 * @return string
	 */
	static public final function fromFrameworkDir( $a_path )
	{
		return self::getFrameworkDir() . self::DS . $a_path;
	}

	/**
	 * Get dispatcher instance
	 * @return Zoombi_Event_Dispatcher
	 */
	static public final function & getDispatcher()
	{
		return self::getInstance()->m_dispatcher;
	}

	/**
	 * Set dispatcher instance
	 * @param Zoombi_Event_Dispatcher $a_dispatcher
	 */
	static public final function setDispatcher( Zoombi_Event_Dispatcher & $a_dispatcher )
	{
		self::getInstance()->m_dispatcher = $a_dispatcher;
	}

	/**
	 * Get application instance
	 * @return Zoombi_Application
	 */
	static public final function & getApplication()
	{
		$i = Zoombi::getInstance();
		if( !$i->m_application )
		{
			$app = null;
			$fname = $i->m_application_base . Zoombi::DS . 'application.php';
			if( file_exists($fname) && is_file($fname) && is_readable($fname) )
			{
				require_once $fname;
				if( class_exists('Application') )
					$app = new Application;
			}

			if( !$app )
				$app = new Zoombi_Application;

			self::setApplication($app);
			$app->initialize();
		}
		return $i->m_application;
	}

	/**
	 * Set application instance
	 * @param Zoombi_Application $a_application
	 */
	static public final function setApplication( Zoombi_Application & $a_application )
	{
		Zoombi::getInstance()->m_application = & $a_application;
		return Zoombi::getInstance();
	}

	/**
	 * Get value from framework config
	 * @param $a_key
	 * @param $a_default
	 * @return mixed
	 */
	static public final function config( $a_key, $a_default = null )
	{
		return self::getApplication()->getConfig()->getValue($a_key, $a_default);
	}

	/**
	 * Log message
	 * @param $a_message
	 * @param $a_prefix
	 */
	static public final function log( $a_message, $a_prefix = null )
	{
		Zoombi_Log::getInstance()->log($a_message, $a_prefix);
	}

	/**
	 * Is ack
	 * @param mixed $a_data
	 * @return bool
	 */
	static public final function ack( $a_data )
	{
		switch( gettype($a_data) )
		{
			default:
				return $a_data != null;

			case 'string':
				switch( strtolower(trim($a_data)) )
				{
					case '1':
					case 'ok':
					case 'on':
					case 'yes':
					case 'true':
						return true;
				}
				break;
		}
		return false;
	}

	/**
	 * Is nack
	 * @param mixed $a_data
	 * @return bool
	 */
	static public final function nack( $a_data )
	{
		return!self::ack($a_data);
	}

	static public final function javascript()
	{
		return self::fromFrameworkDir('js.js');
	}

	static public final function & factory( $a_name )
	{
		return Zoombi_Loader::getInstance()->factory($a_name);
	}

	static public final function & trace( $a_data )
	{
		$args = func_get_args();
		$instance = Zoombi_Debug::getInstance();
		call_user_func_array(array( $instance, 'trace' ), $args);
		return $instance;
	}

	static public final function getDefault( $a_key )
	{
		return self::$defaults->get($a_key);
	}
	
	static private $_global_routes = null;
	
	static private function & _get_global_routes()
	{
		if( !self::$_global_routes )
			self::$_global_routes = new Zoombi_HttpRouter;
		
		return self::$_global_routes;
	}

	static function dispatch( $a_method = null, $a_path = null )
	{		
		$args = func_get_args();
	
		if( !count($args) )
			$args = array($_SERVER['REQUEST_METHOD'],$_SERVER['REQUEST_URI']);		
		
		return call_user_func_array( array(self::_get_global_routes(),'route'), $args );
	}
	
	static function get( $a_pattern, $a_callback )
	{
		$args = func_get_args();
		return call_user_func_array( array(self::_get_global_routes(),'get'), $args );
	}
	
	static function post( $a_pattern, $a_callback )
	{		
		$args = func_get_args();
		return call_user_func_array( array(self::_get_global_routes(),'post'), $args );
	}
	
	static function put( $a_pattern, $a_callback )
	{
		$args = func_get_args();
		return call_user_func_array( array(self::_get_global_routes(),'put'), $args );
	}
	
	static function delete( $a_pattern, $a_callback )
	{
		$args = func_get_args();
		return call_user_func_array( array(self::_get_global_routes(),'delete'), $args );
	}
}
