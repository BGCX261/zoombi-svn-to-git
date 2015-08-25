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
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Error' . DIRECTORY_SEPARATOR . 'exception.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Event' . DIRECTORY_SEPARATOR . 'event.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Event' . DIRECTORY_SEPARATOR . 'dispatcher.php';
require_once ZOOMBI_BASE_PATH . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR . 'loader.php';

spl_autoload_register(array( ZLoader::getInstance(), 'autoload' ));

if( !function_exists('get_called_class') )
{

	function get_called_class()
	{
		$bt = debug_backtrace();
		$l = 0;
		do
		{
			$l++;
			$lines = file($bt[$l]['file']);
			$callerLine = $lines[$bt[$l]['line'] - 1];
			preg_match(
					'/([a-zA-Z0-9\_]+)::' . $bt[$l]['function'] . '/',
					$callerLine,
					$matches
			);

			if( $matches[1] == 'self' )
			{
				$line = $bt[$l]['line'] - 1;
				while( $line > 0 && strpos($lines[$line], 'class') === false )
				{
					$line--;
				}
				preg_match(
						'/class[\s]+(.+?)[\s]+/si',
						$lines[$line],
						$matches
				);
			}
		}
		while( $matches[1] == 'parent' && $matches[1] );
		return $matches[1];
	}

}

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
class Zoombi implements IZSingleton
{
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
		if( !file_exists($path) )
		{
			throw new Exception("Application path not exist: '{$path}'");
		}

		if( !is_dir($path) )
		{
			throw new Exception("Application directory not exist: '{$path}'");
		}

		$i->m_application_base = $path;
		$i->m_base_dir = realpath(dirname($_SERVER['PHP_SELF']));

		Zoombi::import('zoombi.base.registry');
		Zoombi::import('zoombi.base.config');
		Zoombi::import('zoombi.base.controller');
		//Zoombi::import('zoombi.base.action');
		Zoombi::import('zoombi.base.model');
		Zoombi::import('zoombi.base.view');
		Zoombi::import('zoombi.class.route');
		Zoombi::import('zoombi.base.router');
		Zoombi::import('zoombi.error.profiler');
		//Zoombi::import('zoombi.language.language');
		Zoombi::import('zoombi.net.headers');

		Zoombi::import('zoombi.event.dispatcher');
		Zoombi::import('zoombi.plugin.plugin');
		Zoombi::import('zoombi.plugin.pluginmanager');

		//Zoombi::import('zoombi.document.document');
		Zoombi::import('zoombi.base.application');

		/* $filename = 'bootstrap.php';
		  $filepath = $path . Zoombi::DS . $filename;

		  if( !file_exists($filepath) )
		  throw new Exception("Application bootstrap path not exist: '{$filepath}'");

		  if( !is_file($filepath) )
		  throw new Exception("Application bootstrap directory not exist: '{$filepath}'");

		  if( !is_readable($filepath) )
		  throw new Exception("Application bootstrap file not readable: '{$filepath}'");

		  $ret = include_once($filepath);
		  if( $ret != 1 )
		  throw new Exception("Application bootstrap error"); */

		return true;
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
		return Zoombi::getInstance()->m_dispatcher;
	}

	/**
	 * Set dispatcher instance
	 * @param ZDispatcher $a_dispatcher
	 */
	static public final function setDispatcher( ZDispatcher & $a_dispatcher )
	{
		Zoombi::getInstance()->m_dispatcher = $a_dispatcher;
	}

	/**
	 * Get application instance
	 * @return ZApplication
	 */
	static public final function & getApplication()
	{
		if( !Zoombi::getInstance()->m_application )
			Zoombi::setApplication(new ZApplication());

		return Zoombi::getInstance()->m_application;
	}

	/**
	 * Set application instance
	 * @param ZApplication $a_application
	 */
	static public final function setApplication( ZApplication & $a_application )
	{
		$i = Zoombi::getInstance();
		$a_application->setApplicationBaseDir($i->m_application_base);
		$i->m_application = & $a_application;

		$run = strtolower(strval(Zoombi::config('autorun')));
		switch( $run )
		{
			case '1':
			case 'ok':
			case 'on':
			case 'yes':
			case 'true':
				Zoombi::getApplication()->execute();
				break;
		}
		return true;
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
	 * Load model
	 * @param $a_model string
	 * @return ZModel
	 */
	static public final function & model( $a_model )
	{
		$m = null;
		try
		{
			$m = ZLoader::model($a_model);
		}
		catch( ZModelException $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
				throw $e;
		}
		catch( Exception $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
				throw $e;
		}
		return $m;
	}

	static public final function & action( $a_action )
	{
		$a = null;
		try
		{
			$a = ZLoader::action($a_action);
		}
		catch( ZActionException $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
				throw $e;
		}
		catch( Exception $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
				throw $e;
		}
		return $a;
	}

	/**
	 * Load controller
	 * @param string $a_controller
	 * @return ZController
	 */
	static public final function & controller( $a_controller )
	{
		$c = null;
		try
		{
			$c = ZLoader::controller($a_controller);
		}
		catch( ZControllerException $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
				throw $e;
		}
		catch( Exception $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
				throw $e;
		}
		return $c;
	}

	/**
	 * Find view file path
	 * @param string $a_view
	 * @return string
	 */
	static public final function view( $a_view )
	{
		$v = null;
		try
		{
			$v = ZLoader::view($a_view);
		}
		catch( ZViewException $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
			{
				throw $e;
			}
		}
		catch( Exception $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
				throw $e;
		}
		return $v;
	}

	/**
	 * Load helper file
	 * @param string $a_helper
	 * @return string
	 */
	static public final function helper( $a_helper )
	{
		$v = null;
		try
		{
			$v = ZLoader::helper($a_helper);
		}
		catch( ZHelperException $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
			{
				throw $e;
			}
		}
		catch( Exception $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
				throw $e;
		}
		return $v;
	}

	/**
	 * Execute MVC stack
	 * @param $a_contoller
	 * @param $a_view
	 * @param $a_model
	 */
	static public final function mvc( $a_contoller, $a_view = null, $a_model = null )
	{
		$c = Zoombi::controller($a_contoller);
		if( $c )
		{
			if( $a_model )
			{
				$c->model = Zoombi::model($a_model);
			}
			if( $a_view )
			{
				
			}
			$c->render($view);
		}
	}

}
