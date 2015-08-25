<?php

/*
 * File: config.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Modules loader
 */
class Zoombi_Loader extends Zoombi_Component implements Zoombi_Singleton
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
	 * @var Zoombi_Loader
	 */
	static protected $m_instance = null;

	/**
	 * Constructor
	 */
	function __construct( Zoombi_Object & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		$this->m_models = array();
		$this->m_controllers = array();
		$this->m_library = array();
		$this->m_plugins = array();
		$this->m_modules = array();
	}

	/**
	 * Protect from cloning
	 */
	private function __clone()
	{
		
	}

	/**
	 * Get Zoombi_Loader instance
	 * @return Zoombi_Loader
	 */
	static public function & getInstance()
	{
		if(self::$m_instance == null)
			self::$m_instance = new Zoombi_Loader;
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
		if(count($exp) > 1)
		{
			if($this->hasModule($exp[0]))
			{
				$module_name = array_shift($exp);
				$name = implode(Zoombi::DS, $exp);

				$module_instance = $this->module($module_name);
				if($module_instance)
				{
					$class_instance = $module_instance->getLoader()->_loadClass($name, $a_instances, $a_prefix, $a_baseclass); //$a_prefix($name);
					if($class_instance)
						return $class_instance;
				}
			}

			$class_name = array_pop($exp);
			$class_base = implode(Zoombi::DS, $exp);
		}

		$c_prefix = strtolower(trim((string)$a_prefix));
		$m_prefix = $c_prefix;

		if(empty($class_name))
			throw new Zoombi_Exception_Loader("{$m_prefix} class name must be not empty.");

		if($a_instances !== null AND isset($a_instances[$class_name]))
			return $a_instances[$class_name];

		$modprefix = null;
		if(
			($a_prefix == 'controller' OR $a_prefix == 'model' ) AND
			$this->getModule()->getName() != $this->getModule()->getConfig()->getValue('module.default_name', 'index')
		)
			$modprefix = $this->getModule()->getName() . '_';

		$class_class = $this->getModule()->getConfig()->getValue($c_prefix . '.class_prefix') .
			$modprefix .
			$class_name .
			$this->getModule()->getConfig()->getValue($c_prefix . '.class_suffix');
		
		if(class_exists($class_class))
		{
			if(!empty($a_baseclass) AND $class_class instanceof $a_baseclass)
				throw new Zoombi_Exception_Loader("{$m_prefix} class '{$class_class}' must be implement from '{$a_baseclass}'.");

			if($a_instances !== null)
			{
				$a_instances[$class_name] = new $class_class();
				return $a_instances[$class_name];
			}
		}

		$class_file = $this->getModule()->getConfig()->getValue($c_prefix . '.file_prefix') .
			$class_name .
			$this->getModule()->getConfig()->getValue($c_prefix . '.file_suffix') . '.' .
			$this->getModule()->getConfig()->getValue($c_prefix . '.file_extension', 'php');

		$class_dir = $this->getModule()->fromBaseDir($this->getModule()->getConfig()->getValue($c_prefix . '.directory_name', $c_prefix));

		if($a_prefix == 'module')
			$class_dir .= Zoombi::DS . $class_name;

		if(!empty($class_base))
			$class_dir .= Zoombi::DS . $class_base;

		$class_path = $class_dir . Zoombi::DS . $class_file;

		if(!file_exists($class_path))
			throw new Zoombi_Exception_Loader("File '{$class_path}' is not found for {$m_prefix} '{$class_class}' class.", Zoombi_Exception_Loader::EXC_NO_FILE);

		if(!is_readable($class_path))
			throw new Zoombi_Exception_Loader("{$m_prefix} class '{$class_class}' file not exist at '{$class_path}'.", Zoombi_Exception_Loader::EXC_NO_FILE);

		$inc_return = include_once($class_path);

		if(!$inc_return)
			throw new Zoombi_Exception_Loader("{$m_prefix} include path '{$class_path}' not included.");

		if(!class_exists($class_class))
			throw new Zoombi_Exception_Loader("{$m_prefix} class '{$class_class}' not found '{$class_path}'.");

		if(!is_subclass_of($class_class, $a_baseclass))
			throw new Zoombi_Exception_Loader("{$m_prefix} class '{$class_class}' must be implement from '{$a_baseclass}'.");
			
		$instance = new $class_class($this->getModule(), $class_name);
		if($a_instances !== null)
		{
			$a_instances[$class_name] = & $instance;
			return $a_instances[$class_name];
		}
		return $instance;
	}

	protected final function _loadFile( $a_name, $a_section, $a_title = 'Zoombi_Loader' )
	{
		$file_base = null;
		$file_name = (string)$a_name;
		$section = trim((string)$a_section);

		if(substr($file_name, 0, 1) == Zoombi::SS)
			return Zoombi::getApplication()->getLoader()->_loadFile(substr($file_name, 1), $a_section, $a_title);

		$exp = explode(Zoombi::SS, $file_name);
		if(count($exp) > 1)
		{
			$file_name = $exp[count($exp) - 1];
			$tm = $exp[0];
			if($this->hasModule($tm))
			{
				array_shift($exp);
				$f = $this->module($tm)->getLoader()->_loadFile(implode(Zoombi::SS, $exp), $a_section, $a_title);
				return $f;
			}
			else
			{
				array_pop($exp);
				$file_base = implode(Zoombi::DS, $exp);
			}
		}

		$file_dir = $this->getModule()->fromBaseDir($this->getModule()->getConfig()->getValue($a_section . 'directory_name', $section));
		if($file_base)
			$file_dir .= Zoombi::DS . $file_base;

		$file_path = $file_dir . Zoombi::DS . $file_name;
		if(file_exists($file_path) && is_file($file_path))
			return $file_path;

		$file_file = $this->getModule()->getConfig()->getValue($a_section . '.file_prefix') .
			$file_name .
			$this->getModule()->getConfig()->getValue($a_section . '.file_suffix');

		$file_path = $file_dir . Zoombi::DS . $file_file;

		if(file_exists($file_path) && is_file($file_path))
			return $file_path;

		$file_file .= '.' . $this->getModule()->getConfig()->getValue($a_section . '.file_extension', 'php');

		$file_path = $file_dir . Zoombi::DS . $file_file;

		if(!file_exists($file_path))
			throw new Zoombi_Exception_Loader("{$a_title}: '{$file_path}' is not exist.");

		if(!is_file($file_path))
			throw new Zoombi_Exception_Loader("{$a_title}: '{$file_path}' is not a file.");

		if(!is_readable($file_path))
			throw new Zoombi_Exception_Loader("{$a_title}: '{$file_path}' is not readable.");

		return $file_path;
	}
	
	private function _decorateController( Zoombi_Controller & $a_controller )
	{
		if( isset($a_controller->action) )
		{
			$a_controller->addAction($a_controller->action);
			unset($a_controller->action);
		}

		if( isset($a_controller->model) )
		{
			if( is_string($a_controller->model) )
				$a_controller->setModels( explode(' ', $a_controller->model) );

			if( is_array($a_controller->model) )
				$a_controller->setModels( $a_controller->model );

			unset($a_controller->model);
		}

		if( isset($a_controller->helper) )
		{
			$helpers = array( );
			switch( gettype($a_controller->helper) )
			{
				default:
					$this->triggerError("Controller '{$a_controller->getName()}':  has wrong helper type.", Zoombi_Exception_Controller::EXC_MODEL);
					break;

				case 'string':
					$helpers = explode(' ', trim($a_controller->helper));
					break;

				case 'array':
					$helpers = & $a_controller->helper;
					break;
			}

			foreach( $helpers as $h )
			{
				$name = Zoombi_String::normalize($h);

				if( empty($name) )
					continue;

				$loader =& $this;

				if( substr($name, 0, 1) == Zoombi::SS )
				{
					$loader =& Zoombi::getApplication()->getLoader();
					$name = substr($name, 1);
				}
				
				if( !$loader )
					if( $this->getModule()->isMode(Zoombi_Module::MODE_DEBUG) )
						$this->triggerError('Bad loader');
					
				try
				{
					$loader->helper($name);
				}
				catch( Zoombi_Exception_Helper $e )
				{
					if( $this->getModule()->isMode(Zoombi_Module::MODE_DEBUG) )
						$this->triggerError($e);
				}
			}
			unset($a_controller->helper);
		}

		if( isset($a_controller->map) )
		{
			if( is_array( $a_controller->map ) )
				$a_controller->setMaps( $a_controller->map );

			if( is_object( $a_controller->map ) )
				$a_controller->setMaps( get_object_vars( $a_controller->map ) );

			unset( $a_controller->map );
		}
	}

	/**
	 * Load controller
	 * @param string $a_controller Controller name
	 * @return Zoombi_Controller
	 */
	public final function controller( $a_controller, $a_throw = false )
	{
		try
		{
			$this->emit(new Zoombi_Event($this, 'preController', $a_controller));
			$ctl = $this->_loadClass($a_controller, $this->m_controllers, 'controller', 'Zoombi_Controller');
			$this->_decorateController( $ctl );
			$this->emit(new Zoombi_Event($this, 'postController', $ctl->getName(), $a_controller));
			return $ctl;
		}
		catch( Zoombi_Exception_Loader $e )
		{
			if($a_throw)
				throw new Zoombi_Exception_Controller($e->getMessage(), Zoombi_Exception_Controller::EXC_LOAD );
		}
		return false;
	}

	/**
	 * Load model
	 * @param string $a_model Model name
	 * @return Zoombi_Model
	 */
	public final function model( $a_model, $a_throw = false )
	{
		try
		{
			return $this->_loadClass($a_model, $this->m_models, 'model', 'Zoombi_Model');
		}
		catch(Zoombi_Exception_Loader $e)
		{
			if($a_throw)
				throw new Zoombi_Exception_Model($e->getMessage(), Zoombi_Exception_Controller::EXC_LOAD);
		}
		return false;
	}

	/**
	 * Load plugin
	 * @param string $a_plugin Plugin name
	 * @return Zoombi_Plugin
	 */
	public final function plugin( $a_plugin, $a_throw = false )
	{
		try
		{
			$instance = $this->_loadClass($a_plugin, $this->m_plugins, 'plugin', 'Zoombi_Plugin');
			$this->_decorateController($instance);
			return $instance;
		}
		catch( Zoombi_Exception_Loader $e )
		{
			$classname = 'Zoombi_Plugin_' . ucfirst($a_plugin);

			$instance = new $classname($this->getModule(), $classname);
			if( $instance )
			{
				$this->_decorateController($instance);
				return $instance;
			}

			if($a_throw)
			{
				throw new Zoombi_Exception_Plugin($e->getMessage(), Zoombi_Exception_Controller::EXC_LOAD);
			}
		}
		return false;
	}

	/**
	 * Load action
	 * @param string $a_action A action name
	 * @return Zoombi_Action
	 */
	public final function action( $a_action, $a_throw = false )
	{
		try
		{
			return $this->_loadClass($a_action, $c, 'action', 'Zoombi_Action');
		}
		catch(Zoombi_Exception_Loader $e)
		{
			if($a_throw)
				throw new Zoombi_Exception_Action($e->getMessage(), Zoombi_Exception_Controller::EXC_LOAD);
		}
		return false;
	}

	/**
	 * Find view file path
	 * @param string $a_view View name
	 * @return string
	 */
	public final function view( $a_view, $a_throw = false )
	{
		try
		{
			return $this->_loadFile($a_view, 'view', 'Zoombi_View');
		}
		catch(Zoombi_Exception_Loader $e)
		{
			if($a_throw)
				throw new Zoombi_Exception_View($e->getMessage(), Zoombi_Exception_Controller::EXC_LOAD);
		}
		return false;
	}

	/**
	 * Find and load module
	 * @param string $a_module
	 * @return Zoombi_Module
	 */
	public final function module( $a_module, $a_throw = false )
	{
		$mod_name = trim(strtolower($a_module));
		if(isset($this->m_modules[$mod_name]) OR array_key_exists($mod_name, $this->m_modules))
			return $this->m_modules[$mod_name];

		$this->emit(new Zoombi_Event($this, 'preModule', $mod_name));

		$mod_dir = $this->getModule()->fromModuleDir($mod_name);

		if(!file_exists($mod_dir))
		{
			if($a_throw)
				throw new Zoombi_Exception_Module('Module "' . $mod_name . '" not exist', Zoombi_Exception_Controller::EXC_LOAD);
		}
		else
		{
			$module = false;

			try
			{
				$module = $this->_loadClass($mod_name, $this->m_modules, 'module', 'Zoombi_Module');
			}
			catch(Zoombi_Exception_Loader $e)
			{
				if($a_throw)
					throw new Zoombi_Exception_Module($e->getMessage(), Zoombi_Exception_Module::EXC_LOAD);

				$module = new Zoombi_Module($this->getModule(), $mod_name);
				$this->m_modules[$mod_name] = $module;
			}
			$module->initialize();
			$module->setFlag('initialized');
			$this->emit(new Zoombi_Event($this, 'postModule', $mod_name, $module));
			return $module;
		}
		return false;
	}

	/**
	 * Check if module is exist
	 * @param string $a_module
	 * @return bool
	 */
	public function hasModule( $a_module )
	{
		$m = trim(strtolower($a_module));

		if(isset($this->m_modules[$m]))
			return true;

		/*$p = $this->getModule()->getConfig()->getValue('module.directory_name', Zoombi_Module::DEFAULT_MODULE_DIR) . Zoombi::DS . $m;
		$d = $this->getModule()->fromBaseDir($p);
		return file_exists($d);*/
		
		return $this->module( $m ) !== false;
	}

	/**
	 * Check if controller is exist
	 * @param string $a_controller
	 * @return bool
	 */
	public function hasController( $a_controller )
	{
		$c = trim(strtolower($a_controller));

		if(isset($this->m_controllers[$c]) OR array_key_exists($c, $this->m_controllers))
			return true;

		/*$file = null;
		try
		{
			$file = $this->_loadFile($c, 'controller');
		}
		catch(Exception $e)
		{
			
		}
		if(!$file)
			return false;

		return file_exists($file);*/
		return $this->controller( $c ) !== false;
	}

	/**
	 * Find helper file path
	 * @param string $a_helper
	 * @return string
	 */
	public final function helper( $a_helper, $a_throw = false )
	{
		try
		{
			$filename = $this->_loadFile($a_helper, 'helper', 'Helper');
		}
		catch(Zoombi_Exception_Loader $e)
		{
			throw new Zoombi_Exception_Helper($e->getMessage(), Zoombi_Exception_Controller::EXC_LOAD);
		}

		if($filename)
		{
			include_once $filename;
			return true;
		}
	}

	public final function & factory( $a_name )
	{
		$n = 'Zoombi_' . ucfirst($a_name);
		$c = Zoombi::null();
		try
		{
			$c = new $n;
		}
		catch(Exception $e)
		{
			Zoombi::getApplication()->triggerError($e);
		}
		return $c;
	}

}
