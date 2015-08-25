<?php

/*
 * File: boot.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * Check if alredy boot
 */
if( defined('ZBOOT') )
	return;

/**
 * Set boot flag
 * @var bool
 */
define('ZBOOT', true);
define('ZOOMBI_BASE_PATH', realpath(dirname(__FILE__)));

/**
 * Include base classes
 */
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . 'instance.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR . 'node.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR . 'object.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR . 'interface.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR . 'debug.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Error' . DIRECTORY_SEPARATOR . 'exception.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Error' . DIRECTORY_SEPARATOR . 'error.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Event' . DIRECTORY_SEPARATOR . 'event.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Event' . DIRECTORY_SEPARATOR . 'dispatcher.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR . 'applicationobject.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR . 'loader.php';

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
 * @method ZController controller( $a_name );
 *
 */

class Zoombi implements IZSingleton {

	static $null = null;
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
	 * @var ZApplication
	 */

	private $m_application;
	/**
	 * Dispatcher instance
	 * @var ZDispatcher
	 */
	private $m_dispatcher;
	/**
	 * Singleton instance
	 * @var Zoombi
	 */
	static protected $m_instance = null;

	private $m_application_base;
	
	/**
	 * Constructor
	 */
	protected function __construct()
	{
		$this->m_dispatcher = new ZDispatcher();
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
		$i = Zoombi::getInstance();

		$path = realpath((string)$a_application);
		if( file_exists($path) == false )
		{
			throw new Exception("Application path not exist: '{$path}'");
			return false;
		}

		if( is_dir($path) == false )
		{
			throw new Exception("Application directory not exist: '{$path}'");
			return false;
		}

		if( is_readable($path) == false )
		{
			throw new Exception("Application directory not accessable: '{$path}'");
			return false;
		}

		$i->m_application_base = $path;

		require_once ZOOMBI_BASE_PATH . self::DS . 'Class' . self::DS . 'registry.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'config.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Class' . self::DS . 'route.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'router.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'controller.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Controllers' . self::DS . 'dummycontroller.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'action.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'model.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'view.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Error' . self::DS . 'profiler.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Net' . self::DS . 'headers.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Event' . self::DS . 'dispatcher.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'plugin.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'pluginmanager.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'acl.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'module.php';
		require_once ZOOMBI_BASE_PATH . self::DS . 'Base' . self::DS . 'application.php';

		spl_autoload_register(array( ZLoader::getInstance(), 'autoload' ));

		if( self::ack(Zoombi::getApplication()->getConfig()->get(ZModule::CONFIG_KEY_AUTO_EXECUTE, false)) )
			self::getApplication()->execute();
		
		return true;
	}

	static public final function null()
	{
		return null;
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
	 * @return ZDispatcher
	 */
	static public final function & getDispatcher()
	{
		return self::getInstance()->m_dispatcher;
		//return self::getApplication()->getDispatcher();
	}

	/**
	 * Set dispatcher instance
	 * @param ZDispatcher $a_dispatcher
	 */
	static public final function setDispatcher( ZDispatcher & $a_dispatcher )
	{
		//self::getApplication()->setDispatcher($a_dispatcher);
		self::getInstance()->m_dispatcher = $a_dispatcher;
	}

	/**
	 * Get application instance
	 * @return ZApplication
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
				$app = new ZApplication;

			$app->setName('application')->setApplicationBaseDir($i->m_application_base)->setBaseDir($i->m_application_base);
			self::setApplication($app);
			$app->initialize();
		}
		return $i->m_application;
	}

	/**
	 * Set application instance
	 * @param ZApplication $a_application
	 */
	static public final function setApplication( ZApplication & $a_application )
	{
		Zoombi::getInstance()->m_application =& $a_application;
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
		ZLog::getInstance()->log($a_message, $a_prefix);
	}

	/**
	 * Library class loader
	 * @param $a_path
	 * @return bool True if load success
	 */
	static public final function library( $a_path )
	{
		return ZLoader::library($a_path, $a_base);
	}

	/**
	 * Library class loader
	 * @param $a_path
	 * @return bool True if load success
	 */
	static public final function import( $a_path )
	{
		return ZLoader::library($a_path);
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
		return !self::ack($a_data);
	}

	static public final function javascript()
	{
		return self::fromFrameworkDir('js.js');
	}

	static public final function & factory( $a_name )
	{
		ZLoader::getInstance()->factory( $a_name );
	}
}
