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
class ZModule extends ZApplicationObject
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
	const DEFAULT_MODULE_CLASS_SUFFIX = '';

	const DEFAULT_CONTROLLER_DIR = 'controller';
	const DEFAULT_CONTROLLER_NAME = 'index';
	const DEFAULT_CONTROLLER_FILE_PREFIX = '';
	const DEFAULT_CONTROLLER_FILE_SUFFIX = '';
	const DEFAULT_CONTROLLER_CLASS_PREFIX = '';
	const DEFAULT_CONTROLLER_CLASS_SUFFIX = '';
	const DEFAULT_CONTROLLER_METHOD_PREFIX = '';
	const DEFAULT_CONTROLLER_METHOD_SUFFIX = 'action';

	const DEFAULT_PLUGIN_DIR = 'plugin';
	const DEFAULT_PLUGIN_FILE_PREFIX = '';
	const DEFAULT_PLUGIN_FILE_SUFFIX = '';
	const DEFAULT_PLUGIN_CLASS_PREFIX = '';
	const DEFAULT_PLUGIN_CLASS_SUFFIX = '';
	const DEFAULT_PLUGIN_METHOD_PREFIX = '';
	const DEFAULT_PLUGIN_METHOD_SUFFIX = 'action';

	const DEFAULT_MODEL_DIR = 'model';
	const DEFAULT_MODEL_FILE_PREFIX = '';
	const DEFAULT_MODEL_FILE_SUFFIX = '';
	const DEFAULT_MODEL_CLASS_PREFIX = '';
	const DEFAULT_MODEL_CLASS_SUFFIX = '';

	const DEFAULT_HELPER_DIR = 'helper';
	const DEFAULT_HELPER_FILE_PREFIX = '';
	const DEFAULT_HELPER_FILE_SUFFIX = '';
	const DEFAULT_HELPER_CLASS_PREFIX = '';
	const DEFAULT_HELPER_CLASS_SUFFIX = '';

	const DEFAULT_ACTION_DIR = 'action';
	const DEFAULT_ACTION_NAME = 'index';
	const DEFAULT_ACTION_FILE_PREFIX = '';
	const DEFAULT_ACTION_FILE_SUFFIX = '';
	const DEFAULT_ACTION_CLASS_PREFIX = '';
	const DEFAULT_ACTION_CLASS_SUFFIX = '';
	const DEFAULT_ACTION_METHOD_PREFIX = '';
	const DEFAULT_ACTION_METHOD_SUFFIX = 'action';

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
	 * @var ZRouter
	 */
	private $m_router;
	
	/**
	 * Module config
	 * @var ZConfig
	 */
	private $m_config;
	/**
	 * Molule language
	 * @var ZLanguage
	 */
	private $m_language;
	/**
	 * Module output data
	 * @var string
	 */
	private $m_output;
	/**
	 * Module registry
	 * @var ZRegistry
	 */
	private $m_registry;
	/**
	 * Module mode
	 * @var string
	 */
	private $m_mode;
	/**
	 * Plugin manager of module
	 * @var ZPluginManager
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
	 * @var ZLoader
	 */
	private $m_loader;
	/**
	 * Current module route
	 * @var ZRoute
	 */
	private $m_route;
	/**
	 * Current route arguments
	 * @var array
	 */
	private $m_route_args;
	/**
	 * Acl object
	 * @var ZAcl
	 */
	private $m_acl;
	protected $m_stack;
	protected $m_stack_iterator;
	private $m_route_deep;

	/**
	 * Module constructor
	 * @param ZObject $parent
	 * @param string $a_name
	 */
	public function __construct( ZObject & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);

		$this->m_stack_iterator = 0;

		$this->m_flag = array( );
		$this->m_route_args = array( );

		$this->m_stack = array( );

		$this->setAcl(new ZAcl);
		$this->setMode( self::MODE_NORMAL );
		if( $a_parent instanceof ZModule )
		{
			$this->setMode( $a_parent->getMode() );
			$this->setConfig( $a_parent->getConfig() );
		}
		/*$d = new ZDispatcher($this, $a_name . '_dispatcher');
		$this->setDispatcher($d);*/
		$this->m_plugin_mgr = new ZPluginManager($this);
	}

	/**
	 * Push module stack
	 * @return ZModule
	 */
	public function & push()
	{
		$this->m_stack_iterator = array_push($this->m_stack, array(
			'output' => null,
			'return' => null,
			'route' => null,
			'args' => array( )
		));

		$this->m_output = & $this->m_stack[$this->m_stack_iterator]['output'];
		$this->m_return = & $this->m_stack[$this->m_stack_iterator]['return'];
		$this->m_route = & $this->m_stack[$this->m_stack_iterator]['route'];
		$this->m_route_args = & $this->m_stack[$this->m_stack_iterator]['args'];
		return $this;
	}

	/**
	 * Pop module stack
	 * @return ZModule
	 */
	public function & pop()
	{
		$data = array_pop($this->m_stack);

		$this->m_stack_iterator--;
		$this->m_output = & $this->m_stack[$this->m_stack_iterator]['output'];
		$this->m_return = & $this->m_stack[$this->m_stack_iterator]['return'];
		$this->m_route = & $this->m_stack[$this->m_stack_iterator]['route'];
		$this->m_route_args = & $this->m_stack[$this->m_stack_iterator]['args'];
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
			$this->m_acl
		);
	}

	public function initialize()
	{

	}

	/**
	 * Get module object loader
	 * @return ZLoader
	 */
	public final function & getLoader()
	{
		if( $this->m_loader === null )
			$this->m_loader = new ZLoader($this);

		return $this->m_loader;
	}

	/**
	 * Set module object loader
	 * @param ZLoader $a_loader
	 * @return ZModule
	 */
	public final function & setLoader( ZLoader & $a_loader )
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
		if( isset($this->m_flag[(string)$a_flag]) )
			return true;
	}

	/**
	 * Set module named flag to true
	 * @param string $a_flag
	 * @return ZModule
	 */
	public final function & setFlag( $a_flag )
	{
		$this->m_flag[(string)$a_flag] = true;
		return $this;
	}

	/**
	 * Clear module named flag
	 * @param string $a_flag
	 * @return ZModule
	 */
	public final function & clearFlag( $a_flag )
	{
		$f = (string)$a_flag;
		if( isset($this->m_flag[$f]) )
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
	 * @return ZModule
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
	 * @return ZConfig
	 */
	public final function & getConfig()
	{
		if( $this->m_config === null )
			$this->m_config = new ZConfig();

		return $this->m_config;
	}

	/**
	 * Set module config
	 * @param mixed $a_config
	 * @return ZModule
	 */
	public final function & setConfig( $a_config )
	{
		switch( gettype($a_config) )
		{
			case 'string':

				if( !file_exists($a_config) OR !is_file($a_config) )
					$a_config = $this->fromBaseDir($a_config);

				if( file_exists($a_config) AND is_file($a_config) AND is_readable($a_config) )
				{
					$c = new ZConfig($a_config);
					$this->getConfig()->merge($c->getData());
					unset($c);
				}
				break;

			case 'array':
			case 'object':

				if( $a_config instanceof ZRegistry ) {
					$c = new ZConfig($a_config);
					$this->getConfig()->merge($c->getData());
					unset($c);
					break;
				} else if( $a_config instanceof ZModule ) {
					$c = new ZConfig($a_config);
					$this->getConfig()->merge( $a_config->getConfig()->getData() );
					unset($c);
					break;
				}
		}
		$this->setMode($this->getConfig()->getValue('mode', self::MODE_NORMAL));
		$aclsource = $this->getConfig()->getValue('acl');
		switch( gettype($aclsource) )
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
	 * @return ZRegistry
	 */
	public function & getRegistry()
	{
		if( $this->m_registry === null )
			$this->m_registry = new ZRegistry();

		return $this->m_registry;
	}

	/**
	 * Set module registry instance
	 * @param array|object|ZRegistry $a_reg
	 * @return ZModule
	 */
	public function & setRegistry( & $a_reg )
	{
		if( $a_reg !== $this->m_registry )
			$this->m_registry = $a_reg;

		return $this;
	}

	/**
	 * Get or set module registry instance
	 * @param array|object|ZRegistry $a_reg
	 * @return ZRegistry|ZModule
	 */
	public function & registry( & $a_reg = null )
	{
		if( $a_reg === null )
			return $this->getRegistry();

		return $this->setRegistry($a_reg);
	}

	/**
	 * Get module language instance
	 * @return ZLanguage
	 */
	public final function & getLanguage()
	{
		if( $this->m_language === null )
			$this->m_language = new ZLanguage($this);

		return $this->m_language;
	}

	/**
	 * Set module language instance
	 * @param ZLanguage $a_lang
	 * @return ZModule
	 */
	public function & setLanguage( ZLanguage & $a_lang )
	{
		if( $a_lang == $this->m_language )
			return $this;

		unset($this->m_language);
		$this->m_language = $a_lang;
		return $this;
	}

	/**
	 * Get or set module language instance
	 * @param ZLanguage $a_lang
	 * @return ZLanguage|ZModule
	 */
	public function & language( ZLanguage & $a_lang = null )
	{
		if( $a_lang === null )
			return $this->getLanguage();

		return $this->setLanguage($a_lang);
	}

	/**
	 * Set application router
	 * @param ZRouter $a_router
	 * @return ZApplication
	 */
	public final function & setRouter( ZRouter & $a_router )
	{
		if( $this->m_router != $a_router )
			$this->m_router = $a_router;
		return $this;
	}

	/**
	 * Get application router
	 * @return ZRouter
	 */
	public final function & getRouter()
	{
		if( !$this->m_router )
			$this->m_router = new ZRouter( $this, $this->name.'Router' );

		return $this->m_router;
	}

	/**
	 * Get or set application router
	 * @param ZRouter $a_router
	 * @return ZApplication
	 */
	public final function & router( ZRouter & $a_router = null )
	{
		if( $a_router === null )
			return $this->getRouter();

		return $this->setRouter($a_router);
	}

	/**
	 * Set module mode
	 * @param string $a_mode
	 * @return ZModule
	 */
	public function & setMode( $a_mode )
	{
		$mode = (string)$a_mode;
		switch( $mode )
		{
			case ZApplication::MODE_DEBUG:
			case ZApplication::MODE_NORMAL:
			case ZApplication::MODE_PREFORMANCE:
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
	 * @return ZModule
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
	 * @return ZModule
	 */
	public function & outputAppend( $a_str )
	{
		$this->m_output .= (string)$a_str;
		return $this;
	}

	/**
	 * Prepend data to module output buffer
	 * @param string $a_str
	 * @return ZModule
	 */
	public function & outputPrepend( $a_str )
	{
		$this->m_output = (string)$a_str . $this->m_output;
		return $this;
	}

	/**
	 * Flush module output
	 * @return ZModule
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
	 * @return ZModule
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
	 * @return ZPluginManager
	 */
	public function & getPluginManager()
	{
		return $this->m_plugin_mgr;
	}

	/**
	 * Set plugin manager
	 * @param ZPluginManager $a_manager
	 * @return ZModule
	 */
	public function & setPluginManager( ZPluginManager & $a_manager )
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
	 * @return ZPlugin
	 */
	public function & getPlugin( $a_plugin )
	{
		return $this->getPluginManager()->getPlugin($a_plugin);
	}

	/**
	 * Set plugins
	 * @param array $a_plugins
	 * @return ZPluginManager
	 */
	public function & setPlugins( array $a_plugins )
	{
		$this->getPluginManager()->setPlugins($a_plugins);
		return $this;
	}

	/**
	 * Add plugin to stack
	 * @param mixed $a_plugin
	 * @return ZPluginManager
	 */
	public function & addPlugin( $a_plugin )
	{
		$this->getPluginManager()->addPlugin($a_plugin);
		return $this;
	}

	/**
	 * Remove plugin from stack
	 * @param int|ZPlugin $a_plugin
	 * @return ZPluginManager
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
		if( method_exists($this->m_loader,$a_name) )
			 return call_user_func_array( array( &$this->m_loader, $a_name ), $a_params);

		foreach( $this->getPlugins() as $plugin )
			if( method_exists($plugin, $a_name) )
				return call_user_func_array( array( &$plugin, $a_name ), $a_params);

		$this->triggerError('Method not found: ' . $a_name, ZException::EXC_NO_METHOD);
	}

	protected final function fixroute( $a_route )
	{
		$r = new ZRoute($a_route);
		if( !$r->getModule() )
			$r->setModule($this->getConfig()->getValue('module.default_name', self::DEFAULT_MODULE_NAME));

		if( !$r->getController() )
			$r->setController($this->getConfig()->getValue('controller.default_name', self::DEFAULT_CONTROLLER_NAME));

		if( !$r->getAction() )
			$r->setAction($this->getConfig()->getValue('controller.default_action', self::DEFAULT_ACTION_NAME));
		$o = (string)$r;

		unset($r);
		return $o;
	}

	/**
	 * @return ZRouteMap
	 */
	public function routePath( $a_path, ZRoute & $a_route = null, $a_throws = false )
	{
		$path = new ZRoutePath();
		$p = new ZRoute($a_path);
		
		$route =& $a_route;
		if( !$route )
			$route = new ZRoute();

		$t = $p->getSegment(0);
		$t = empty($t) ? self::DEFAULT_MODULE_NAME : $t;
		if( $this->getLoader()->hasModule( $t ) )
		{
			try
			{
				$route->setModule($t);
				$path->module =& $this->getLoader()->module( $t );
			}
			catch( ZException $e )
			{
				if( $a_throws )
					throw $e;
				
				return $path;
			}
			return $path->module->routePath( $p->pop_start(), $route, $a_throws );
		}
		else
		{
			$route->setModule($this->getName());
			$path->module =& $this;
		}

		if( !$path->module )
			return $path;

		$t = $p->getSegment(0);
		$t = empty($t) ? self::DEFAULT_CONTROLLER_NAME : $t;
		$p->pop_start();
		try
		{
			$route->setController($t);
			$path->controller =& $path->module->getLoader()->controller( $t );
		}
		catch( ZException $e )
		{
			if( $a_throws )
				throw $e;

			return $path;
		}
		
		if( !$path->controller )
			return $path;

		$t = $p->getSegment(0);
		$t = empty($t) ? self::DEFAULT_ACTION_NAME : $t;
		$p->pop_start();
		if( $path->controller->hasAction( $t ) )
		{
			$route->setAction($t);
			$path->action = $t;
		}
		else
		{
			if( $a_throws )
				throw new ZException('Action ' . $t . ' not found at ' . $path->controller->getName() . ' controller in ' . $path->module->getName() . ' module', ZControllerException::EXC_ACTION);

			return $path;
		}

		foreach( $p->getSegments() as $_p )
			$route->push($_p);

		$route->getQuery()->setData( $p->getQuery() );
		unset($p);
		return $path;
	}

	/**
	 * Route path
	 * @param string $a_path
	 * @return ZModule
	 */
	public final function & route( $a_path, $a_args = array( ) )
	{
		Zoombi::getApplication()->m_route_deep++;
		switch( gettype($a_path) )
		{
			case 'string':
				if( substr($a_path, 0, 1) == Zoombi::SS )
				{
					Zoombi::getApplication()->route(substr($a_path, 1), $a_args);
					$this->m_output = Zoombi::getApplication()->outputGet();
					$this->m_return = Zoombi::getApplication()->getReturn();
					return $this;
				}
				break;

			case 'object':
				if( !$a_path instanceof ZRoute )
					return $this;
				break;

			default:
				return $this;
		}

		$this->setArgs($a_args);

		try
		{
			$path = $this->routePath( $this->getRouter()->rewrite($a_path), $route, true );
		}
		catch( Exception $e )
		{
			//return $this->triggerError('Invalid route path: ' . $a_path, 91);
			return $this->triggerError( $e );
		}
		/*if( $path->isInvalid() )
			return $this->triggerError('Invalid route path: ' . $path, 91);*/

		$this->setRoute($route);
		$path->controller->requestAction($path->action, $route->getParams() );
		$this->m_output = $path->controller->getOutput();
		$this->m_return = $path->controller->getReturn();
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
		return $this->route($a_path, $a_args)->outputFlush()->getReturn();
	}

	public function getRouteData( $a_path, $a_args = array() )
	{
		$this->route($a_path,$a_args);
		return array($this->m_return,$this->m_output);
	}

	/**
	 * Set module current route
	 * @param ZRoute $a_route
	 * @return ZModule
	 */
	public function & setRoute( ZRoute & $a_route )
	{
		$this->m_route = $a_route;
		return $this;
	}

	/**
	 * Get module current route
	 * @return ZRoute
	 */
	public function & getRoute()
	{
		if( $this->m_route === null )
			$this->m_route = new ZRoute(null/* ,ZRoute::MODE_MIXED */);
		return $this->m_route;
	}

	/**
	 * Set current route arguments
	 * @param array $a_args
	 * @return ZModule
	 */
	public function & setArgs( array $a_args )
	{
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

	function & setAcl( ZAcl & $a_acl )
	{
		$this->m_acl = $a_acl;
		return $this;
	}

}
