<?php

/*
 * File: module.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * @example <br />
 * Module emit events:<br />
 * - preController<br />
 * - on404<br />
 * - postController<br />
 * - preAction<br />
 * - postAction<br />
 *
 */
class Zoombi_Module extends Zoombi_Component
{
	const MODE_NORMAL = 'normal';
	const MODE_DEBUG = 'debug';
	const MODE_PREFORMANCE = 'preformance';

	const CONFIG_KEY_AUTO_RENDER = 'autorender';
	const CONFIG_KEY_AUTO_EXECUTE = 'autorun';

	const DEFAULT_AUTO_RENDER = false;
	const DEFAULT_AUTO_EXECUTE = true;

	const DEFAULT_CONFIG_DIR = 'config';
	const DEFAULT_CONFIG_NAME = 'config';

	const DEFAULT_MODULE_DIR = 'module';
	const DEFAULT_MODULE_NAME = 'index';
	const DEFAULT_MODULE_FILE_PREFIX = '';
	const DEFAULT_MODULE_FILE_SUFFIX = '';
	const DEFAULT_MODULE_CLASS_PREFIX = '';
	const DEFAULT_MODULE_CLASS_SUFFIX = '_module';

	const DEFAULT_CONTROLLER_DIR = 'controller';
	const DEFAULT_CONTROLLER_NAME = 'index';
	const DEFAULT_CONTROLLER_FILE_PREFIX = '';
	const DEFAULT_CONTROLLER_FILE_SUFFIX = '';
	const DEFAULT_CONTROLLER_CLASS_PREFIX = '';
	const DEFAULT_CONTROLLER_CLASS_SUFFIX = '_controller'; 
	const DEFAULT_CONTROLLER_METHOD_PREFIX = '';
	const DEFAULT_CONTROLLER_METHOD_SUFFIX = '';

	const DEFAULT_PLUGIN_DIR = 'plugin';
	const DEFAULT_PLUGIN_FILE_PREFIX = '';
	const DEFAULT_PLUGIN_FILE_SUFFIX = '';
	const DEFAULT_PLUGIN_CLASS_PREFIX = '';
	const DEFAULT_PLUGIN_CLASS_SUFFIX = '_plugin';
	const DEFAULT_PLUGIN_METHOD_PREFIX = '';
	const DEFAULT_PLUGIN_METHOD_SUFFIX = '';

	const DEFAULT_MODEL_DIR = 'model';
	const DEFAULT_MODEL_FILE_PREFIX = '';
	const DEFAULT_MODEL_FILE_SUFFIX = '';
	const DEFAULT_MODEL_CLASS_PREFIX = '';
	const DEFAULT_MODEL_CLASS_SUFFIX = '_model';

	const DEFAULT_HELPER_DIR = 'helper';
	const DEFAULT_HELPER_FILE_PREFIX = '';
	const DEFAULT_HELPER_FILE_SUFFIX = '';
	const DEFAULT_HELPER_CLASS_PREFIX = '';
	const DEFAULT_HELPER_CLASS_SUFFIX = '_helper';

	const DEFAULT_ACTION_DIR = 'action';
	const DEFAULT_ACTION_NAME = 'index';
	const DEFAULT_ACTION_FILE_PREFIX = '';
	const DEFAULT_ACTION_FILE_SUFFIX = '';
	const DEFAULT_ACTION_CLASS_PREFIX = '';
	const DEFAULT_ACTION_CLASS_SUFFIX = '_action';
	const DEFAULT_ACTION_METHOD_PREFIX = '';
	const DEFAULT_ACTION_METHOD_SUFFIX = '';

	const DEFAULT_VIEW_DIR = 'view';
	const DEFAULT_VIEW_FILE_PREFIX = '';
	const DEFAULT_VIEW_FILE_SUFFIX = '';
	const DEFAULT_VIEW_FILE_EXT = 'php';

	/**
	 * Module flags
	 * @var array
	 */
	private $m_flag;

	/**
	 * @access private
	 * @var Zoombi_Router
	 */
	private $m_router;

	/**
	 * Module config
	 * @var Zoombi_Config
	 */
	private $m_config;

	/**
	 * Molule language
	 * @var Zoombi_Language
	 */
	private $m_language;

	/**
	 * Module output data
	 * @var string
	 */
	private $m_output;

	/**
	 * Module registry
	 * @var Zoombi_Registry
	 */
	private $m_registry;

	/**
	 * Module mode
	 * @var string
	 */
	private $m_mode;

	/**
	 * Plugin manager of module
	 * @var Zoombi_PluginManager
	 */
	private $m_plugin_mgr;

	/**
	 * Module work directory
	 * @var string
	 */
	private $m_base_dir;

	/**
	 * Returnned execution data
	 * @var mixed
	 */
	private $m_return;

	/**
	 * Module loader
	 * @var Zoombi_Loader
	 */
	private $m_loader;

	/**
	 * Current module route
	 * @var Zoombi_Route
	 */
	private $m_route;

	/**
	 * Current route arguments
	 * @var array
	 */
	private $m_route_args;

	/**
	 * Acl object
	 * @var Zoombi_Acl
	 */
	private $m_acl;
	protected $m_stack;
	protected $m_stack_iterator;
	private $m_route_deep;

	/**
	 * Module constructor
	 * @param Zoombi_Object $parent
	 * @param string $a_name
	 */
	public function __construct( Zoombi_Object & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);

		$this->m_stack_iterator = 0;

		$this->m_flag = array();
		$this->m_route_args = array();

		$this->m_stack = array();

		$this->setAcl(new Zoombi_Acl);
		$this->setMode(self::MODE_NORMAL);

		if($a_parent instanceof Zoombi_Module)
		{
			$this->setMode($a_parent->getMode());
			$cfg = clone $a_parent->getConfig();
			$cfg->unsetValue('load');
			$this->setConfig($cfg);
			unset($cfg);
			$this->setBaseDir($a_parent->fromModuleDir($a_name));
		}
		$this->setPluginManager(new Zoombi_PluginManager($this));
		$this->setConfig($this->fromConfigDir('config.php'));
	}

	/**
	 * Push module stack
	 * @return Zoombi_Module
	 */
	public function & push()
	{
		$this->m_stack_iterator = array_push(
			$this->m_stack, array(
				'output' => null,
				'return' => null,
				'route' => null,
				'args' => array()
			)
		);

		$this->m_output = & $this->m_stack[$this->m_stack_iterator]['output'];
		$this->m_return = & $this->m_stack[$this->m_stack_iterator]['return'];
		$this->m_route = & $this->m_stack[$this->m_stack_iterator]['route'];
		$this->m_route_args = & $this->m_stack[$this->m_stack_iterator]['args'];
		
		return $this;
	}

	/**
	 * Pop module stack
	 * @return Zoombi_Module
	 */
	public function & pop()
	{
		$data = array_pop($this->m_stack);

		$this->m_stack_iterator--;
		$this->m_output =& $this->m_stack[$this->m_stack_iterator]['output'];
		$this->m_return =& $this->m_stack[$this->m_stack_iterator]['return'];
		$this->m_route =& $this->m_stack[$this->m_stack_iterator]['route'];
		$this->m_route_args =& $this->m_stack[$this->m_stack_iterator]['args'];
		
		return $data;
	}

	/**
	 * Module destruction
	 */
	public function __destruct()
	{
		unset( 
			$this->m_config,
			$this->m_router,
			$this->m_route,
			$this->m_language,
			$this->m_registry,
			$this->m_plugin_mgr,
			$this->m_acl,
			$this->m_loader
		);
	}

	public function initialize()
	{
		
	}

	/**
	 * Get module object loader
	 * @return Zoombi_Loader
	 */
	public final function & getLoader()
	{
		if($this->m_loader === null)
			$this->m_loader = new Zoombi_Loader($this);

		return $this->m_loader;
	}

	/**
	 * Set module object loader
	 * @param Zoombi_Loader $a_loader
	 * @return Zoombi_Module
	 */
	public final function & setLoader( Zoombi_Loader & $a_loader )
	{
		$this->m_loader = $a_loader;
		return $this;
	}

	/**
	 * Get module flag
	 * @param string $a_flag A flag name
	 * @return bool
	 */
	public final function getFlag( $a_flag )
	{
		if(isset($this->m_flag[(string)$a_flag]))
			return true;
	}

	/**
	 * Set module named flag to true
	 * @param string $a_flag
	 * @return Zoombi_Module
	 */
	public final function & setFlag( $a_flag )
	{
		$this->m_flag[(string)$a_flag] = true;
		return $this;
	}

	/**
	 * Clear module named flag
	 * @param string $a_flag
	 * @return Zoombi_Module
	 */
	public final function & clearFlag( $a_flag )
	{
		$f = (string)$a_flag;
		if(isset($this->m_flag[$f]))
			unset($this->m_flag[$f]);

		return $this;
	}

	/**
	 * Get base directory
	 * @return <type>
	 */
	public final function getBaseDir()
	{
		return $this->m_base_dir;
	}

	/**
	 * Set module base directory
	 * @param string $a_dir
	 * @return Zoombi_Module
	 */
	public final function & setBaseDir( $a_dir )
	{
		$this->m_base_dir = realpath($a_dir);
		return $this;
	}

	/**
	 * Prepend base directory to first parameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromBaseDir( $a_path )
	{
		return empty($a_path) ? $this->getBaseDir() : $this->getBaseDir() . Zoombi::DS . $a_path;
	}

	/**
	 * Get module models directory
	 * @return string
	 */
	public final function getModelDir()
	{
		return $this->fromBaseDir($this->getConfig()->getValue('model.directory_name', self::DEFAULT_MODEL_DIR));
	}

	/**
	 * Prepend module models directory to first parameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromModelDir( $a_path )
	{
		return empty($a_path) ? $this->getModelDir() : $this->getModelDir() . Zoombi::DS . (string)$a_path;
	}

	/**
	 * Get module views directory
	 * @return string
	 */
	public final function getViewDir()
	{
		return $this->fromBaseDir($this->getConfig()->getValue('view.directory_name', self::DEFAULT_VIEW_DIR));
	}

	/**
	 * Prepend module views directry to first parameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromViewDir( $a_path )
	{
		return empty($a_path) ? $this->getViewDir() : $this->getViewDir() . Zoombi::DS . (string)$a_path;
	}

	/**
	 * Get module controllers directory
	 * @return string
	 */
	public final function getControllerDir()
	{
		return $this->fromBaseDir($this->getConfig()->getValue('view.directory_name', self::DEFAULT_CONTROLLER_DIR));
	}

	/**
	 * Prepend module controllers directory to first paraameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromControllerDir( $a_path )
	{
		return empty($a_path) ? $this->getControllerDir() : $this->getControllerDir() . Zoombi::DS . $a_path;
	}

	/**
	 * Get module plugins directory
	 * @return string
	 */
	public final function getPluginDir()
	{
		return $this->fromBaseDir($this->getConfig()->getValue('plugin.directory_name', self::DEFAULT_PLUGIN_DIR));
	}

	/**
	 * Prepend module plugins directory to first paraameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromPluginDir( $a_path )
	{
		return empty($a_path) ? $this->getPluginDir() : $this->getPluginDir() . Zoombi::DS . $a_path;
	}

	/**
	 * Get module actions directory
	 * @return string
	 */
	public final function getActionDir()
	{
		return $this->fromBaseDir($this->getConfig()->getValue('action.directory_name', self::DEFAULT_ACTION_DIR));
	}

	/**
	 * Prepend module actions directory to first paraameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromActionDir( $a_path )
	{
		return empty($a_path) ? $this->getActionDir() : $this->getActionDir() . Zoombi::DS . $a_path;
	}

	/**
	 * Get module helpers directory
	 * @return string
	 */
	public final function getHelperDir()
	{
		return $this->fromBaseDir($this->getConfig()->getValue('helper.directory_name', self::DEFAULT_HELPER_DIR));
	}

	/**
	 * Prepend module helpers directory to first paraameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromHelperDir( $a_path )
	{
		return empty($a_path) ? $this->getHelperDir() : $this->getHelperDir() . Zoombi::DS . $a_path;
	}

	/**
	 * Get module modules directory
	 * @return string
	 */
	public final function getModuleDir()
	{
		return $this->fromBaseDir($this->getConfig()->getValue('module.directory_name', self::DEFAULT_MODULE_DIR));
	}

	/**
	 * Prepend module modules directory to first paraameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromModuleDir( $a_path )
	{
		return empty($a_path) ? $this->getModuleDir() : $this->getModuleDir() . Zoombi::DS . $a_path;
	}

	/**
	 * Get module config directory
	 * @return string
	 */
	public final function getConfigDir()
	{
		return $this->fromBaseDir($this->getConfig()->getValue('config.directory_name', self::DEFAULT_CONFIG_DIR));
	}

	/**
	 * Prepend module config directory to first paraameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromConfigDir( $a_path )
	{
		return empty($a_path) ? $this->getConfigDir() : $this->getConfigDir() . Zoombi::DS . $a_path;
	}

	/**
	 * Get module config
	 * @return Zoombi_Config
	 */
	public final function & getConfig()
	{
		if($this->m_config === null)
			$this->m_config = new Zoombi_Config();

		return $this->m_config;
	}

	/**
	 * Set module config
	 * @param mixed $a_config
	 * @return Zoombi_Module
	 */
	public final function & setConfig( $a_config )
	{
		switch(gettype($a_config))
		{
			case 'string':

				if(!file_exists($a_config) OR !is_file($a_config))
					$a_config = $this->fromBaseDir($a_config);

				if(file_exists($a_config) AND is_file($a_config) AND is_readable($a_config))
				{
					$c = new Zoombi_Config($a_config);
					$this->getConfig()->merge($c->getData());
					unset($c);
				}
				break;

			case 'array':
			case 'object':

				if($a_config instanceof Zoombi_Registry)
				{
					$c = new Zoombi_Config($a_config);
					$this->getConfig()->merge($c->getData());
					unset($c);
				}
				else if($a_config instanceof Zoombi_Module)
				{
					$c = new Zoombi_Config($a_config);
					$this->getConfig()->merge($a_config->getConfig()->getData());
					unset($c);
				}
				break;
		}
		
		$this->setMode($this->getConfig()->getValue('mode', self::MODE_NORMAL));
		$plugins = $this->getConfig()->getValue('load.plugin');
		
		switch(gettype($plugins))
		{
			case 'string':
				foreach(explode(' ', $plugins) as $p)
					$this->addPlugin($p);

				break;

			case 'array':
				foreach($plugins as $p)
					$this->addPlugin($p);

				break;
		}

		$aclsource = $this->getConfig()->getValue('acl');
		
		switch(gettype($aclsource))
		{
			case 'array':
			case 'object':
				$this->getAcl()->setData($aclsource);
				break;

			case 'string':
				$this->getAcl()->setData($this->fromBaseDir($aclsource));
				break;

			default:
				break;
		}
		return $this;
	}

	/**
	 * Get module registry instance
	 * @return Zoombi_Registry
	 */
	public function & getRegistry()
	{
		if($this->m_registry === null)
			$this->m_registry = new Zoombi_Registry();

		return $this->m_registry;
	}

	/**
	 * Set module registry instance
	 * @param array|object|Zoombi_Registry $a_reg
	 * @return Zoombi_Module
	 */
	public function & setRegistry( & $a_reg )
	{
		if($a_reg !== $this->m_registry)
			$this->m_registry = $a_reg;

		return $this;
	}

	/**
	 * Get or set module registry instance
	 * @param array|object|Zoombi_Registry $a_reg
	 * @return Zoombi_Registry|Zoombi_Module
	 */
	public function & registry( & $a_reg = null )
	{
		if($a_reg === null)
			return $this->getRegistry();

		return $this->setRegistry($a_reg);
	}

	/**
	 * Get module language instance
	 * @return Zoombi_Language
	 */
	public final function & getLanguage()
	{
		if($this->m_language === null)
			$this->m_language = new Zoombi_Language($this);

		return $this->m_language;
	}

	/**
	 * Set module language instance
	 * @param Zoombi_Language $a_lang
	 * @return Zoombi_Module
	 */
	public function & setLanguage( Zoombi_Language & $a_lang )
	{
		if($a_lang == $this->m_language)
			return $this;

		unset($this->m_language);
		$this->m_language = $a_lang;
		
		return $this;
	}

	/**
	 * Get or set module language instance
	 * @param Zoombi_Language $a_lang
	 * @return Zoombi_Language|Zoombi_Module
	 */
	public function & language( Zoombi_Language & $a_lang = null )
	{
		if($a_lang === null)
			return $this->getLanguage();

		return $this->setLanguage($a_lang);
	}

	/**
	 * Set application router
	 * @param Zoombi_Router $a_router
	 * @return Zoombi_Application
	 */
	public final function & setRouter( Zoombi_Router & $a_router )
	{
		if($this->m_router != $a_router)
			$this->m_router = $a_router;
		return $this;
	}

	/**
	 * Get application router
	 * @return Zoombi_Router
	 */
	public final function & getRouter()
	{
		if(!$this->m_router)
			$this->m_router = new Zoombi_Router($this, $this->name . 'Router');

		return $this->m_router;
	}

	/**
	 * Get or set application router
	 * @param Zoombi_Router $a_router
	 * @return Zoombi_Application
	 */
	public final function & router( Zoombi_Router & $a_router = null )
	{
		if($a_router === null)
			return $this->getRouter();

		return $this->setRouter($a_router);
	}

	/**
	 * Set module mode
	 * @param string $a_mode
	 * @return Zoombi_Module
	 */
	public function & setMode( $a_mode )
	{
		$mode = (string)$a_mode;
		switch($mode)
		{
			case Zoombi_Application::MODE_DEBUG:
			case Zoombi_Application::MODE_NORMAL:
			case Zoombi_Application::MODE_PREFORMANCE:
				$this->m_mode = $mode;
				break;
		}
		return $this;
	}

	public function & setModeDebug()
	{
		return $this->setMode(self::MODE_DEBUG);
	}

	public function setModeNormal()
	{
		return $this->setMode(self::MODE_NORMAL);
	}

	public function setModePerformance()
	{
		return $this->setMode(self::MODE_PREFORMANCE);
	}

	/**
	 * Get module mode
	 * @return string
	 */
	public function getMode()
	{
		return $this->m_mode;
	}

	/**
	 * Compare module and parameter model
	 * @param mixed $a_mode
	 * @return bool
	 */
	public function isMode( $a_mode )
	{
		return $this->m_mode == $a_mode;
	}

	public function isModeDebug()
	{
		return $this->isMode(self::MODE_DEBUG);
	}

	public function isModeNomal()
	{
		return $this->isMode(self::MODE_NORMAL);
	}

	public function isModePreformance()
	{
		return $this->isMode(self::MODE_PREFORMANCE);
	}
	
	public function getController( $a_name )
	{
		return $this->load->controller( $a_name );
	}
	
	public function getModule( $a_name )
	{
		return $this->load->module( $a_name );
	}
	
	public function getView( $a_name )
	{
		return $this->load->view( $a_name );
	}

	/**
	 * Get executed output
	 * @return string
	 */
	public function getOutput()
	{
		return $this->m_output;
	}

	/**
	 * Clear module output buffer
	 * @return Zoombi_Module
	 */
	public function & outputClear()
	{
		$this->m_output = '';
		return $this;
	}

	/**
	 * Clear module output and return last data
	 * @return string
	 */
	public function outputGetClear()
	{
		$c = $this->m_output;
		$this->m_output = '';
		return $c;
	}

	/**
	 * Clear module output and return last data
	 * @return string
	 */
	public function outputGetClean()
	{
		$c = $this->m_output;
		$this->m_output = '';
		return $c;
	}

	public function outputGet()
	{
		return $this->m_output;
	}

	/**
	 * Append data to application output buffer
	 * @param string $a_str
	 * @return Zoombi_Module
	 */
	public function & outputAppend( $a_str )
	{
		$this->m_output .= (string)$a_str;
		return $this;
	}

	/**
	 * Prepend data to module output buffer
	 * @param string $a_str
	 * @return Zoombi_Module
	 */
	public function & outputPrepend( $a_str )
	{
		$this->m_output = (string)$a_str . $this->m_output;
		return $this;
	}

	/**
	 * Flush module output
	 * @return Zoombi_Module
	 */
	public function & outputFlush()
	{
		echo $this->outputGetClear();
		return $this;
	}

	public function outputLength()
	{
		return strlen($this->m_output);
	}

	/**
	 * Set output data
	 * @param string $a_output
	 * @return Zoombi_Module
	 */
	public function & setOutput( $a_output )
	{
		$this->m_output = (string)$a_output;
		return $this;
	}

	public function & setReturn( $a_return )
	{
		$this->m_return = $a_return;
		return $this;
	}

	/**
	 * Get data returned by route
	 * @return mixed
	 */
	public function & getReturn()
	{
		return $this->m_return;
	}

	/**
	 * Get plugin manager
	 * @return Zoombi_PluginManager
	 */
	public function & getPluginManager()
	{
		return $this->m_plugin_mgr;
	}

	/**
	 * Set plugin manager
	 * @param Zoombi_PluginManager $a_manager
	 * @return Zoombi_Module
	 */
	public function & setPluginManager( Zoombi_PluginManager $a_manager )
	{
		$this->m_plugin_mgr = $a_manager;
		return $this;
	}

	/**
	 * Get all plugins
	 * @return array
	 */
	public function & getPlugins()
	{
		return $this->getPluginManager()->getPlugins();
	}

	/**
	 * Get plugin
	 * @return Zoombi_Plugin
	 */
	public function & getPlugin( $a_plugin )
	{
		return $this->getPluginManager()->getPlugin($a_plugin);
	}

	/**
	 * Set plugins
	 * @param array $a_plugins
	 * @return Zoombi_PluginManager
	 */
	public function & setPlugins( array $a_plugins )
	{
		$this->getPluginManager()->setPlugins($a_plugins);
		return $this;
	}

	/**
	 * Add plugin to stack
	 * @param mixed $a_plugin
	 * @return Zoombi_PluginManager
	 */
	public function & addPlugin( $a_plugin )
	{
		$this->m_plugin_mgr->addPlugin($a_plugin);
		return $this;
	}

	/**
	 * Remove plugin from stack
	 * @param int|Zoombi_Plugin $a_plugin
	 * @return Zoombi_PluginManager
	 */
	public function & removePlugin( $a_plugin )
	{
		$this->getPluginManager()->removePlugin($a_plugin);
		return $this;
	}

	/**
	 * Call unknown method
	 * @param string $a_name
	 * @param mixed $a_params
	 * @return mixed
	 */
	public function __call( $a_name, $a_params )
	{
		if(method_exists($this->m_loader, $a_name))
			return call_user_func_array(array(&$this->m_loader, $a_name), $a_params);

		foreach($this->getPlugins() as $plugin)
			if(method_exists($plugin, $a_name))
				return call_user_func_array(array(&$plugin, $a_name), $a_params);

		$this->triggerError('Method not found: ' . $a_name, Zoombi_Exception::EXC_NO_METHOD);
	}

	public function & __get( $a_property )
	{
		switch($a_property)
		{
			case 'router':
				return $this->getRouter();

			case 'route':
				return $this->getRoute();

			case 'registry':
				return $this->getRegistry();

			case 'config':
				return $this->getConfig();

			case 'language':
				return $this->getLanguage();

			case 'load':
				return $this->getLoader();

			case 'acl':
				return $this->getAcl();
		}

		return parent::__get($a_property);
	}

	protected final function fixroute( $a_route )
	{
		$r = new Zoombi_Route($a_route);
		if(!$r->getModule())
			$r->setModule($this->getConfig()->getValue('module.default_name', self::DEFAULT_MODULE_NAME));

		if(!$r->getController())
			$r->setController($this->getConfig()->getValue('controller.default_name', self::DEFAULT_CONTROLLER_NAME));

		if(!$r->getAction())
			$r->setAction($this->getConfig()->getValue('controller.default_action', self::DEFAULT_ACTION_NAME));
		$o = (string)$r;

		unset($r);
		return $o;
	}

	/**
	 * Route path
	 * @param string $a_path
	 * @return Zoombi_Module
	 */
	public final function & route( $a_path, $a_args = array() )
	{
		if(substr($a_path, 0, 1) == Zoombi::SS)
			return Zoombi::getApplication()->route(substr($a_path, 1));

		if(substr($a_path, 0, 3) == '../' . Zoombi::SS)
		{
			$parent = $this->getModule();
			if(!$parent)
				$parent = Zoombi::getApplication();

			return $parent->route(substr($a_path, 3));
		}

		$rewrite = $this->getRouter()->rewrite($a_path);

		$path = new Zoombi_Route($rewrite);
		$epath = new Zoombi_RoutePath;

		if($path->getSegment(0) == $this->getName())
		{
			$seg = $path->getSegment(1);
			if($this->getLoader()->hasController($seg) OR $this->getLoader()->hasModule($path->getSegment($seg)))
				$path->pop_start();
		}

		$epath->parents[] = $this;
		$m = $this;

		do
		{
			$s = $path->getSegment(0);
			//$m = $m->getLoader()->module($s, false);
			if($m->hasModule($s))
			{
				$m = $m->getLoader()->module($s, false);
				$epath->parents[] = $m;
				$path->pop_start();
			}
			else
				break;
		}
		while($m);

		$mod = $epath->module;

		if(!$mod)
			$this->triggerError(new Zoombi_Exception('Application router can\'t find module "' . $path->getSegment(0) . '" -> ' . $rewrite, Zoombi_Exception_Controller::EXC_LOAD));
		
		$sc = $path->getSegment(0);
		$path->pop_start();

		if( !$mod->hasController($sc) )
		{
			return $this->triggerError(new Zoombi_Exception('Application router can\'t find controller "' . $sc . '" in module "' . $mod->getName() . '" -> ' . $rewrite, Zoombi_Exception_Controller::EXC_LOAD));
		}
		
		$ctl = $mod->getLoader()->controller($sc, false);
		
		$epath->controller = $ctl;
		
		$sa = $path->getSegment(0);
		
		$path->pop_start();

		if(!$epath->controller->hasAction($sa))
			return $this->triggerError(new Zoombi_Exception('Application router can\'t find action "' . $sa . '" in controller "' . $sc . '" of module "' . $mod->getName() . '" -> ' . $rewrite, Zoombi_Exception_Controller::EXC_ACTION));

		$epath->action = $sa;

		Zoombi_Request::getInstance()->setExecPath($epath);
		Zoombi_Request::getInstance()->setRoutePath($epath);

		$old_args = $this->getArgs();
		$old_route = $this->getRoute();
		$old_current = $this->getRouter()->getForward();

		$nr = new Zoombi_Route(implode(Zoombi::SS, array_merge($epath->toArray(), $path->getSegments())) . $path->queryString());

		$this->setArgs($a_args);
		$this->setRoute($nr);
		$this->getRouter()->setForward($nr);

		$epath->controller->requestAction($epath->action, $path->getSegments());
		
		foreach($epath->parents as $mod)
		{
			if($mod AND $mod instanceof Zoombi_Module)
			{
				$mod->setReturn($epath->controller->getReturn());
				$mod->setOutput($epath->controller->getOutput());
			}
		}
		
		$this->setArgs($old_args);
		$this->setRoute($old_route);
		$this->getRouter()->setForward($old_current);
		
		return $this;
	}

	/**
	 * Route synonim
	 * @see route
	 * @param string $a_path
	 * @param array $a_args
	 * @return mixed
	 */
	public function & runRoute( $a_path, $a_args = array() )
	{
		$this->route($a_path, $a_args);
		return $this->getReturn();
	}

	public function getRouteData( $a_path, $a_args = array() )
	{
		$this->route($a_path, $a_args);
		return array($this->m_return, $this->m_output);
	}

	/**
	 * Set module current route
	 * @param Zoombi_Route $a_route
	 * @return Zoombi_Module
	 */
	public function & setRoute( Zoombi_Route & $a_route )
	{
		$this->m_route = $a_route;
		return $this;
	}

	/**
	 * Get module current route
	 * @return Zoombi_Route
	 */
	public function & getRoute()
	{
		if($this->m_route === null)
			$this->m_route = new Zoombi_Route(null/* ,Zoombi_Route::MODE_MIXED */);
		return $this->m_route;
	}

	/**
	 * Set current route arguments
	 * @param array $a_args
	 * @return Zoombi_Module
	 */
	public function & setArgs( $a_args )
	{
		if(is_array($a_args))
			$this->m_route_args = $a_args;

		return $this;
	}

	/**
	 * Get current route arguments
	 * @return array
	 */
	public function & getArgs()
	{
		return $this->m_route_args;
	}

	public function getArg( $a_index )
	{
		return array_key_exists($a_index, $this->m_route_args) ? $this->m_route_args[$a_index] : null;
	}

	function & getAcl()
	{
		return $this->m_acl;
	}

	function & setAcl( Zoombi_Acl & $a_acl )
	{
		$this->m_acl = $a_acl;
		return $this;
	}
	
	function hasModule( $a_module )
	{
		return $this->getLoader()->hasModule($a_module);
	}

	function hasController( $a_ctl )
	{
		return $this->getLoader()->hasController( $a_ctl );
	}

}
