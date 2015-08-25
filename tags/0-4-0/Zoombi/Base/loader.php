<?php

/*
 * File: config.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Modules loader
 */
class ZLoader extends ZApplicationObject implements IZSingleton
{

	/**
	 * Array of plugins
	 * @var array
	 */
	private $m_plugins;
	/**
	 * Array of modules
	 * @var array
	 */
	private $m_modules;
	/**
	 * Array of models
	 * @var array
	 */
	private $m_models;
	/**
	 * Array of controllers
	 * @var array
	 */
	private $m_controllers;
	/**
	 * Array of loaded library holder
	 * @var array
	 */
	private $m_library;
	/**
	 * Singleton instance
	 * @var ZLoader
	 */
	static protected $m_instance = null;

	/**
	 * Constructor
	 */
	function __construct( ZObject & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		$this->m_models = array( );
		$this->m_controllers = array( );
		$this->m_library = array( );
		$this->m_plugins = array( );
		$this->m_modules = array( );
	}

	/**
	 * Protect from cloning
	 */
	private function __clone()
	{

	}

	/**
	 * Get ZLoader instance
	 * @return ZLoader
	 */
	static public function & getInstance()
	{
		if( self::$m_instance == null )
			self::$m_instance = new ZLoader;
		return self::$m_instance;
	}

	/**
	 * Find and instantize class
	 * @param string $a_name
	 * @param array $a_instance
	 * @param string $a_prefix
	 * @parem string $a_classname
	 * @return stdClass
	 */
	private function & _loadClass( $a_name, & $a_instances, $a_prefix, $a_baseclass )
	{
		$class_base = null;
		$class_name = (string)$a_name;

		$exp = explode(Zoombi::SS, $class_name);
		if( count($exp) > 1 )
		{
			$class_name = array_pop($exp);
			$class_base = implode(Zoombi::DS, $exp);
		}

		$c_prefix = strtolower(trim((string)$a_prefix));
		$m_prefix = $c_prefix;

		if( empty($class_name) )
			throw new ZLoaderException("{$m_prefix} class name must be not empty.");

		if( $a_instances !== null AND isset($a_instances[$class_name]) )
			return $a_instances[$class_name];

		$modprefix = null;
		if(
				$a_prefix == 'controller' AND
				$this->getModule()->getName() != $this->getModule()->getConfig()->getValue('module.default_name', 'index')
		)
			$modprefix = $this->getModule()->getName() . '_';

		$class_class = $this->getModule()->getConfig()->getValue($c_prefix . '.class_prefix') .
				$modprefix .
				$class_name .
				$this->getModule()->getConfig()->getValue($c_prefix . '.class_suffix', $m_prefix);


		if( class_exists($class_class) )
		{
			if( !empty($a_baseclass) AND $class_class instanceof $a_baseclass )
				throw new ZLoaderException("{$m_prefix} class '{$class_class}' must be implement from '{$a_baseclass}'.");

			if( $a_instances !== null )
			{
				$a_instances[$class_name] = new $class_class();
				return $a_instances[$class_name];
			}
		}

		$class_file = $this->getModule()->getConfig()->getValue($c_prefix . '.file_prefix') .
				$class_name .
				$this->getModule()->getConfig()->getValue($c_prefix . '.file_suffix') . '.' .
				$this->getModule()->getConfig()->getValue($c_prefix . '.file_extension', 'php');

		$class_dir = $this->getModule()->fromBaseDir($this->getModule()->getConfig()->getValue('path.' . $c_prefix, $c_prefix));

		if( $a_prefix == 'module' )
			$class_dir .= Zoombi::DS . $class_name;

		if( !empty($class_base) )
			$class_dir .= Zoombi::DS . $class_base;

		$class_path = $class_dir . Zoombi::DS . $class_file;

		if( !file_exists($class_path) )
			throw new ZLoaderException("File '{$class_path}' is not found for {$m_prefix} '{$class_class}' class.", ZLoaderException::EXC_NO_FILE);

		if( !is_readable($class_path) )
			throw new ZLoaderException("{$m_prefix} class '{$class_class}' file not exist at '{$class_path}'.", ZLoaderException::EXC_NO_FILE);

		include_once($class_path);

		if( !class_exists($class_class) )
			throw new ZLoaderException("{$m_prefix} class '{$class_class}' not found '{$class_path}'.");

		if( !is_subclass_of($class_class, $a_baseclass) )
			throw new ZLoaderException("{$m_prefix} class '{$class_class}' must be implement from '{$a_baseclass}'.");

		$instance = new $class_class($this->getModule(), $class_name);

		if( $a_instances !== null )
		{
			$a_instances[$class_name] = & $instance;
			return $a_instances[$class_name];
		}
		return $instance;
	}

	public final function _loadFile( $a_name, $a_section, $a_title = 'ZLoader' )
	{
		$file_base = null;
		$file_name = (string)$a_name;
		$section = trim((string)$a_section);

		$exp = explode(Zoombi::SS, $file_name);
		if( count($exp) > 1 )
		{
			$file_name = array_pop($exp);
			$file_base = implode(Zoombi::DS, $exp);
		}

		$file_file = $this->getModule()->getConfig()->getValue($a_section . '.file_prefix') .
				$file_name .
				$this->getModule()->getConfig()->getValue($a_section . '.file_suffix') .
				'.' .
				$this->getModule()->getConfig()->getValue($a_section . '.file_extension', 'php');

		$file_dir = $this->getModule()->fromBaseDir($this->getModule()->getConfig()->getValue('path.' . $a_section, $section));
		if( $file_base )
			$file_dir .= Zoombi::DS . $file_base;

		$file_path = $file_dir . Zoombi::DS . $file_file;

		if( !file_exists($file_path) OR !is_file($file_path) )
			throw new ZLoaderException("{$a_title}: file '{$file_path}' is not exist.");

		if( !is_readable($file_path) )
			throw new ZLoaderException("{$a_title}: file '{$file_path}' is not readable.");

		return $file_path;
	}

	/**
	 * Load controller
	 * @param string $a_controller Controller name
	 * @return ZController
	 */
	public final function & controller( $a_controller )
	{
		try
		{
			return $this->_loadClass($a_controller, $this->m_controllers, 'controller', 'ZController');
		}
		catch( ZLoaderException $e )
		{
			throw new ZControllerException($e->getMessage(), ZControllerException::EXC_LOAD);
		}
		return Zoombi::$null;
	}

	/**
	 * Load model
	 * @param string $a_model Model name
	 * @return ZModel
	 */
	public final function & model( $a_model )
	{
		try
		{
			return $this->_loadClass($a_model, $this->m_models, 'model', 'ZModel');
		}
		catch( ZLoaderException $e )
		{
			throw new ZModelException($e->getMessage(), ZControllerException::EXC_LOAD);
		}
		return Zoombi::$null;
	}

	/**
	 * Load plugin
	 * @param string $a_plugin Plugin name
	 * @return ZPlugin
	 */
	public final function & plugin( $a_plugin )
	{
		try
		{
			return $this->_loadClass($a_plugin, $this->m_plugins, 'plugin', 'ZPlugin');
		}
		catch( ZLoaderException $e ) 
		{
			throw new ZPluginException($e->getMessage(), ZControllerException::EXC_LOAD);
		}
		return Zoombi::$null;
	}

	/**
	 * Load action
	 * @param string $a_action A action name
	 * @return ZAction
	 */
	public final function action( $a_action )
	{
		try
		{
			return $this->_loadClass($a_action, $c, 'action', 'ZAction');
		}
		catch( ZLoaderException $e )
		{
			throw new ZActionException($e->getMessage(), ZControllerException::EXC_LOAD);
		}
		return Zoombi::$null;
	}

	/**
	 * Find view file path
	 * @param string $a_view View name
	 * @return string
	 */
	public final function view( $a_view )
	{
		try
		{
			return $this->_loadFile($a_view, 'view', 'ZView');
		}
		catch( ZLoaderException $e )
		{
			throw new ZViewException($e->getMessage(), ZControllerException::EXC_LOAD);
		}
		return Zoombi::$null;
	}

	/**
	 * Find and load module
	 * @param string $a_module
	 * @return ZModule
	 */
	public final function & module( $a_module )
	{
		$m = trim(strtolower($a_module));
		if( isset($this->m_modules[$m]) OR array_key_exists($m, $this->m_modules) )
			return $this->m_modules[$m];

		$p = $this->getModule()->getConfig()->getValue('path.module', 'module') . Zoombi::DS . $m;
		$d = $this->getModule()->fromBaseDir($p);
		if( !file_exists($d) )
			throw new ZModuleException('Module "' . $m . '" not exist', ZControllerException::EXC_LOAD);

		if( file_exists($d) AND is_dir($d) )
		{
			$mod = null;

			try
			{
				$mod = $this->_loadClass($m, $this->m_modules, 'module', 'ZModule');
			}
			catch( ZLoaderException $e )
			{
				switch( $e->getCode() )
				{
					case ZLoaderException::EXC_NO_FILE:
						$mod = new ZModule($this->getModule(), $m);
						break;

					default:
						throw new ZModuleException($e->getMessage(), ZModuleException::EXC_LOAD);
						break;
				}
			}
			$mod
				->setBaseDir($d)
				->setConfig( $mod->fromConfigDir('config.php') )
				->initialize();

			$mod->setFlag('initialized');

			$this->m_modules[$m] = & $mod;
			return $mod;
		}
		return Zoombi::$null;
	}

	/**
	 * Check if module is exist
	 * @param string $a_module
	 * @return bool
	 */
	public function hasModule( $a_module )
	{
		$m = trim(strtolower($a_module));

		if( empty($m) )
			$m = $this->getModule()->getConfig()->getValue('module.default_name', 'index');

		if( isset($this->m_modules[$m]) )
			return true;

		$p = $this->getModule()->getConfig()->getValue('path.module', 'module') . Zoombi::DS . $m;
		$d = $this->getModule()->fromBaseDir($p);
		return file_exists($d);
	}

	/**
	 * Check if controller is exist
	 * @param string $a_controller
	 * @return bool
	 */
	public function hasController( $a_controller )
	{
		$c = trim(strtolower($a_controller));
		if( empty($c) )
			$c = $this->getModule()->getConfig()->getValue('controller.default_name', 'index');

		if( isset($this->m_controllers[$c]) OR array_key_exists($c, $this->m_controllers) )
			return true;

		$file = null;
		$file = $this->_loadFile($c, 'controller');

		if( !$file )
			return false;

		return file_exists($file);
	}

	/**
	 * Find helper file path
	 * @param string $a_helper
	 * @return string
	 */
	public final function helper( $a_helper )
	{
		try
		{
			$filename = $this->_loadFile($a_helper, 'helper', 'Helper');
		}
		catch( ZLoaderException $e )
		{
			throw new ZHelperException($e->getMessage(), ZControllerException::EXC_LOAD);
		}

		if( $filename )
		{
			include_once $filename;
			return true;
		}
	}

	/**
	 * Load from library
	 * @param string $a_path
	 * @return bool True if load success else return false
	 */
	static public final function library( $a_path )
	{
		$i = ZLoader::getInstance();

		if( key_exists($a_path, $i->m_library) )
			return true;

		$i->m_library[$a_path] = true;

		$exp_path = explode('.', $a_path);
		$classname = array_pop($exp_path);

		$i->emit(new ZEvent($i, 'onLibrary', $classname));

		if( $exp_path[0] == 'zoombi' )
		{
			array_shift($exp_path);

			$new_path = array( );
			foreach( $exp_path as $p )
				array_push($new_path, ucwords($p));

			$exp_path = $new_path;
			$new_path = ( is_array($exp_path) ) ? implode(Zoombi::DS, $exp_path) : $exp_path;

			$filename = Zoombi::fromFrameworkDir($new_path . Zoombi::DS . strtolower($classname) . '.php');

			$code = null;

			if( file_exists($filename) )
				$code = include_once( $filename );

			if( $code != -1 )
			{
				$i->emit(new ZEvent($i, 'onLibrarySucess', $classname));
				return true;
			}
		}
		else
		{
			$new_path = implode(Zoombi::DS, $exp_path);
			$filename = Zoombi::fromFrameworkDir($new_path . Zoombi::DS . $classname . '.php');

			$code = $this->loadFile($filename);
			if( $code )
			{
				if( class_exists($classname) )
					$i->emit(new ZEvent($i, 'onLibrarySucess', $classname));
				return true;
			}
		}
		$i->emit(new ZEvent($i, 'onLibraryFailed', $classname));
		return false;
	}

	/**
	 * Class autoloader
	 * @param string $a_class Class name
	 */
	public final function autoload( $a_class )
	{
		$this->emit(new ZEvent($this, 'onAutoload', $a_class));
		if( $a_class[0] == 'Z' )
		{
			$filename = strtolower(substr($a_class, 1, strlen($a_class) - 1));
			$base = Zoombi::getFrameworkDir();
			foreach( scandir($base) as $dir )
			{
				if( $dir == '.' OR $dir == '..' )
					continue;

				$path = $base . Zoombi::DS . $dir;
				if( is_dir($path) == false )
					continue;

				$filepath = $path . Zoombi::DS . $filename . '.php';
				if( is_file($filepath) )
				{
					require_once $filepath;
					$this->emit(new ZEvent($this, 'onAutoloadSuccess', $a_class));
					return;
				}
			}
		}
		$this->emit(new ZEvent($this, 'onAutoloadFailed', $a_class));
	}

}
