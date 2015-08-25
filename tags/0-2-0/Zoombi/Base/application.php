<?php

/*
 * File: application.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Application core class
 *
 * @example <br />
 * Application emit event:<br />
 * - preExecute<br />
 * - preRoute<br />
 * - postRoute<br />
 * - preController<br />
 * - on404<br />
 * - postController<br />
 * - preAction<br />
 * - postAction<br />
 * - onOutput<br />
 * - postExecute<br />
 *
 * @method int run()
 * @method int exec()
 * @method int execute()
 * @method int display()
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class ZApplication extends ZObject implements IZSingleton
{
	const MODE_NORMAL = 'normal';
	const MODE_DEBUG = 'debug';
	const MODE_PREFORMANCE = 'preformance';

	/**
	 * Errors collection
	 * @var array
	 */
	private $m_errors;
	/**
	 * @access private
	 * @var ZRouter
	 */
	private $m_router;
	/**
	 * @access private
	 * @var string
	 */
	private $m_mode;
	/**
	 * @access private
	 * @var ZLanguage
	 */
	private $m_language;
	/**
	 * @access private
	 * @var ZRegistry
	 */
	private $m_registry;
	/**
	 * @access private
	 * @var array
	 */
	private $m_flag;
	/**
	 * @access private
	 * @var ZController;
	 */
	private $m_current;
	/**
	 * Application config instance
	 * @var ZConfig
	 */
	private $m_config;
	/**
	 * Application base directory
	 * @var string
	 */
	private $m_basedir;
	/**
	 * Plugin manage
	 * @var ZPluginManager
	 */
	private $m_plugin_mgr;
	/**
	 * Application output
	 * @var string
	 */
	private $m_output;
	/**
	 * Application database
	 * @var ZDatabase
	 */
	private $m_database;
	/**
	 * Application controller return data
	 * @var mixed
	 */
	private $m_return;
	/**
	 * Singleton instance
	 * @var ZApplication
	 */
	static protected $m_instance = null;

	const FLAG_NO_ACTION = 'no_action';
	const FLAG_NO_CONTROLLER = 'no_controller';
	const FLAG_NO_ROUTE = 'no_route';
	const FLAG_NO_EXECUTE = 'no_route';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->m_config = new ZConfig();

		ZProfiler::start('Application');

		$this->m_errors = array( );
		$this->m_except = array( );
		$this->m_flag = array( );
		$this->m_database = null;

		//$this->setLanguage( new ZLanguage() );
		$this->setRegistry(new ZRegistry());

		$this->setMode(ZApplication::MODE_NORMAL);

		$this->setRouter(new ZRouter());
		$this->setDispatcher(Zoombi::getDispatcher());
		$this->setPluginManager(new ZPluginManager());
	}

	public function & getDatabase()
	{
		if( $this->m_database )
			return $this->m_database;

		$db = $this->m_config->getValue('database', null);
		if( $db !== null )
		{
			$d = new ZDatabase($this, 'database');
			$d->open($db);
			$this->setDatabase($d);
			return $this->m_database;
		}

		trigger_error('Database is not set');
	}

	public function setDatabase( ZDatabase & $a_database )
	{
		$this->m_database = $a_database;
		return $this;
	}

	/**
	 * Protect from cloning
	 */
	private function __clone()
	{

	}

	/**
	 * Not exist function calls
	 * @param string $a_name
	 * @param array $a_params
	 */
	public function __call( $a_name, $a_params )
	{
		switch( $a_name )
		{
			case 'run':
			case 'exec':
			case 'start':
			case 'render':
			case 'display':
			case 'execute':
				if( !$this->getFlag(self::FLAG_NO_EXECUTE) )
					$this->_execute();
				return;
		}

		foreach( $this->m_plugin_mgr->getPlugins() as $plugin )
		{
			if( method_exists($plugin, $a_name) )
			{
				return call_user_func_array(array( &$plugin, $a_name ), $a_params);
			}
		}

		trigger_error('Method not found: ' . $a_name, E_USER_WARNING);
	}

	/**
	 * Get application flag
	 * @param string $a_flag A flag name
	 * @return bool
	 */
	public final function getFlag( $a_flag )
	{
		if( isset($this->m_flag[(string)$a_flag]) )
			return true;
	}

	/**
	 * Set application named flag to true
	 * @param string $a_flag
	 * @return ZApplication
	 */
	public final function & setFlag( $a_flag )
	{
		$this->m_flag[(string)$a_flag] = true;
		return $this;
	}

	/**
	 * Clear application named flag
	 * @param string $a_flag
	 * @return ZApplication
	 */
	public final function & clearFlag( $a_flag )
	{
		$f = (string)$a_flag;
		if( isset($this->m_flag[$f]) )
			unset($this->m_flag[$f]);

		return $this;
	}

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
	 * Get base directory
	 * @return <type>
	 */
	public final function getBaseDir()
	{
		return realpath(dirname($_SERVER['SCRIPT_FILENAME']));
	}

	/**
	 * Prepend base directory to first parameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromBaseDir( $a_path )
	{
		return $this->getBaseDir() . Zoombi::DS . (string)$a_path;
	}

	/**
	 * Set application base directory
	 * @param string $a_base
	 * @return ZApplication
	 */
	public final function & setApplicationBaseDir( $a_base )
	{
		$this->m_basedir = (string)$a_base;
		return $this;
	}

	/**
	 * Get application base directory
	 */
	public final function getApplicationBaseDir()
	{
		return $this->m_basedir;
	}

	/**
	 * Prepend application base directory
	 * @param string $a_path
	 * @return string
	 */
	public final function fromApplicationBaseDir( $a_path )
	{
		$path = (string)$a_path;
		if( strpos($path, Zoombi::DS, 0) === 0 )
			return $this->getApplicationBaseDir() . $path;

		return $this->getApplicationBaseDir() . Zoombi::DS . $path;
	}

	/**
	 * Get application base url
	 * @return string
	 */
	public final function getBaseUrl()
	{
		$url = $this->m_config->getValue('baseurl');
		if( $url === null )
			throw new ZApplicationException('Please define "urlbase" at application config.', 0);
		return $url;
	}

	/**
	 * Prepend application base url to first parameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromBaseUrl( $a_url )
	{
		$url = (string)$a_url;
		if( strpos($url, '/', 0) === 0 )
			return $this->getBaseUrl() . substr($url, -1) . $url;

		return $this->getBaseUrl() . $url;
	}

	/**
	 * Get models directory
	 * @return string
	 */
	public final function getModelDir()
	{
		return $this->fromApplicationBaseDir($this->m_config->getValue('path.model', 'Model'));
	}

	/**
	 * Prepend application models directory to first parameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromModelDir( $a_path )
	{
		return $this->getModelDir() . Zoombi::DS . (string)$a_path;
	}

	/**
	 * Get application views directory
	 * @return string
	 */
	public final function getViewDir()
	{
		return $this->fromApplicationBaseDir($this->m_config->getValue('path.view', 'View'));
	}

	/**
	 * Prepend application views directry to first parameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromViewDir( $a_path )
	{
		return $this->getViewDir() . Zoombi::DS . (string)$a_path;
	}

	/**
	 * Get application controllers directory
	 * @return string
	 */
	public final function getControllerDir()
	{
		return $this->fromApplicationBaseDir($this->m_config->getValue('path.view', 'View'));
	}

	/**
	 * Prepend application controllers directory to first paraameter
	 * @param string $a_path
	 * @return string
	 */
	public final function fromControllerDir( $a_path )
	{
		return $this->getControllerDir() . Zoombi::DS . $a_path;
	}

	/**
	 * Get application config
	 * @return ZConfig
	 */
	public final function & getConfig()
	{
		return $this->m_config;
	}

	/**
	 * Set application config
	 * @param mixed $a_config
	 * @return ZApplication
	 */
	public final function & setConfig( $a_config )
	{
		if( is_string($a_config) )
		{
			$a_config = $this->fromApplicationBaseDir($a_config);
			if( !file_exists($a_config) OR !is_file($a_config) OR !is_readable($a_config) )
				return $this;

			$this->m_config->fromFile($a_config);
		}

		if( is_array($a_config) )
			$this->m_config->fromArray($a_config);

		return $this;
	}

	/**
	 * Get application registry instance
	 * @return ZRegistry
	 */
	public function & getRegistry()
	{
		return $this->m_registry;
	}

	/**
	 * Set application registry instance
	 * @param array|object|ZRegistry $a_reg
	 * @return ZApplication
	 */
	public function & setRegistry( & $a_reg )
	{
		if( $a_reg !== $this->m_registry )
			$this->m_registry = $a_reg;
		return $this;
	}

	/**
	 * Get or set application registry instance
	 * @param array|object|ZRegistry $a_reg
	 * @return ZRegistry|ZApplication
	 */
	public function & registry( & $a_reg = null )
	{
		if( $a_reg === null )
			return $this->getRegistry();

		return $this->setRegistry($a_reg);
	}

	/**
	 * Get application language instance
	 * @return ZLanguage
	 */
	public final function & getLanguage()
	{
		return $this->m_language;
	}

	/**
	 * Set application language instance
	 * @param ZLanguage $a_lang
	 * @return ZApplication
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
	 * Get or set application language instance
	 * @param ZLanguage $a_lang
	 * @return ZLanguage|ZApplication
	 */
	public function & language( ZLanguage & $a_lang = null )
	{
		if( $a_lang === null )
			return $this->getLanguage();

		return $this->setLanguage($a_lang);
	}

	/**
	 * Set application mode
	 * @param string $a_mode
	 * @return ZApplication
	 */
	public function & setMode( $a_mode )
	{
		$mode = (string)$a_mode;
		switch( $mode )
		{
			case ZApplication::MODE_DEBUG:
			case ZApplication::MODE_NORMAL:
			case ZApplication::MODE_PREGORMANCE:
				$this->m_mode = $mode;
				break;
		}
		return $this;
	}

	/**
	 * Get application mode
	 * @return string
	 */
	public function getMode()
	{
		return $this->m_mode;
	}

	/**
	 * Compare application and parameter model
	 * @param mixed $a_mode
	 * @return bool
	 */
	public function isMode( $a_mode )
	{
		return $this->m_mode == $a_mode;
	}

	/**
	 * Redirect
	 * @param string $a_to String to redirect
	 * @param bool $a_exit Exit now
	 * @return ZApplication
	 */
	public function & redirect( $a_to, $a_exit = true )
	{
		ZHeaders::setHeader('location', (string)$a_to);
		if( $a_exit )
			exit(0);

		return $this;
	}

	/**
	 * Get execute output
	 * @return string
	 */
	public function getOutput()
	{
		return $this->m_output;
	}

	/**
	 * Clear application output buffer
	 * @return ZApplication
	 */
	public function & outputClear()
	{
		$this->m_output = '';
		return $this;
	}

	/**
	 * Clear application output and return last data
	 * @return string
	 */
	public function outputGetClear()
	{
		$c = $this->m_output;
		$this->outputClear();
		return $c;
	}

	public function outputGetClean()
	{
		return $this->outputGetClear();
	}

	public function outputGet()
	{
		return $this->m_output;
	}

	/**
	 * Append data to application output buffer
	 * @param string $a_str
	 * @return ZApplication
	 */
	public function & outputAppend( $a_str )
	{
		$this->m_output .= (string)$a_str;
		return $this;
	}

	/**
	 * Prepend data to application output buffer
	 * @param string $a_str
	 * @return ZApplication
	 */
	public function & outputPrepend( $a_str )
	{
		$this->m_output = (string)$a_str . $this->m_output;
		return $this;
	}

	/**
	 * Flush application output
	 * @return ZApplication
	 */
	public function & outputFlush()
	{
		echo $this->m_output;
		$this->outputClear();
		return $this;
	}

	/**
	 * Set execution otput
	 * @param string $a_output
	 * @return ZApplication
	 */
	public function & setOutput( $a_output )
	{
		$this->m_output = (string)$a_output;
		return $this;
	}

	private function _processRoute( ZRoute & $a_route, & $a_code, & $a_message )
	{
		$ctl = null;

		if( !$a_route->controller )
			$a_route->controller = $this->m_config->getValue('controller.default_name', 'index');

		if( !$a_route->action )
			$a_route->action = $this->m_config->getValue('controller.default_action', 'index');

		try
		{
			$ctl = ZLoader::controller($a_route->getController());
		}
		catch( ZControllerException $e )
		{
			$a_code = $e->getCode();
			$a_message = $e->getMessage();
			return false;
		}

		if( !$ctl )
			return false;

		$this->setController($ctl);
		return true;
	}

	/**
	 * Execute application
	 */
	protected final function _execute()
	{
		// Start output collection
		ob_start();

		// Get application execution mode from config
		$this->setMode(strtolower(strval($this->m_config->getValue('mode', self::MODE_NORMAL))));

		// Attach error and exception handlers if applicatio execution mode is 'debug'
		if( $this->isMode(self::MODE_DEBUG) )
		{
			$old_errr = error_reporting(E_ALL);
			set_error_handler(array( $this, '_error_handler' ), E_ALL);
			set_exception_handler(array( $this, '_exception_handler' ));
		}

		// Set application plugin manager listen global event dispatcher
		$this->getPluginManager()->setTarget(Zoombi::getDispatcher());

		// Notify for start execution
		$this->emit(new ZEvent($this, 'preExecute'));

		// Notify befor route start
		$this->emit(new ZEvent($this, 'preRoute'));
		if( !$this->getFlag(self::FLAG_NO_ROUTE) )
		{
			$request = null;
			// Process request info
			$req_qry = explode('?', $_SERVER['REQUEST_URI'], 2);
			//if( count($req_qry) > 1 )
			$request = array_shift($req_qry);

			// Get content rewrite variabe
			$rt = $this->m_config->getValue('routevar', 'rt');

			$rvar = null;
			if( isset($_GET[$rt]) )
			{
				$rvar = $_GET[$rt];
				unset($_GET[$rt]);
			}

			if( $req_qry )
				$rvar .= '?' . $req_qry[0];

			// Attach routing rules to router
			$rc = new ZConfig($this->fromApplicationBaseDir($this->m_config->getValue('routes', null)));
			$router = new ZRouter($rvar, $rc->toArray());
			unset($rc);
			$this->setRouter($router);

			//$router->route();

			if( $this->isMode(self::MODE_DEBUG) )
				Zoombi::log('Request route: ' . $router->getRequest(), 'FrontController');

			// Get routing result
			$route = clone $router->getRedirect();
			if( $this->isMode(self::MODE_DEBUG) )
				Zoombi::log('Redirect route: ' . $route, 'FrontController');

			// Notify for end of routing process
			$this->emit(new ZEvent($this, 'postRoute', $route));

			// Notify before controller has created
			$this->emit(new ZEvent($this, 'preController', $route->getController()));
			if( !$this->getFlag(self::FLAG_NO_CONTROLLER) )
			{
				// Check if we have requested controller
				if( !$this->_processRoute($route, $_code, $_message) )
				{
					if( $_code == ZLoader::EXC_NO_FILE )
					{
						$this->emit(new ZEvent($this, 'onError', 404, $route, $_code, $_message));
						$this->emit(new ZEvent($this, 'on404', $route, $_code, $_message));
					}
					else
					{
						$this->emit(new ZEvent($this, 'onError', 500, $route, $_code, $_message));
						$this->emit(new ZEvent($this, 'on500', $route, $_code, $_message));
					}
				}

				// Notify after controller be created
				$this->emit(new ZEvent($this, 'postController', $route->getController()));

				// Processing controller
				$ctl = $this->getController();
				if( $ctl )
				{
					if( !$ctl->hasAction($route->getAction()) )
						$route->action = $this->m_config->getValue('controller.default_action', 'index');

					$router->setCurrent($route)->setForward($route);

					if( $this->isMode(self::MODE_DEBUG) )
						Zoombi::log('Current route: ' . (string)$router->getCurrent(), 'FrontController');

					$this->emit(new ZEvent($this, 'preAction', $route->action));
					if( !$this->getFlag(self::FLAG_NO_ACTION) )
					{
						try
						{
							$this->m_return = $ctl->requestAction($route->action, $route->params);
							$this->emit(new ZEvent($this, 'postAction'));
						}
						catch( ZControllerException $e )
						{
							if( $e->getCode() != ZControllerException::EXC_QUIT_OUTPUT )
							{
								$this->emit(new ZEvent($this, 'onError', 500, $e->getMessage()));
								$this->emit(new ZEvent($this, 'on500', $e->getMessage()));
								if( $this->isMode(self::MODE_DEBUG) )
									trigger_error($e->getMessage(), Zoombi::EXC_ERROR);
							}
							else
							{
								$this->m_config->setValue('output', false);
							}
						}
					}
				}
			}
		}
		$this->setOutput(ob_get_contents());
		ob_end_clean();

		$flag = $this->m_config->getValue('output', false);
		switch( strtolower(trim(strval($flag))) )
		{
			case '1':
			case 'ok':
			case 'on':
			case 'yes':
			case 'true':
				$this->emit(new ZEvent($this, 'onOutput'));
				$this->outputFlush();
				if( $this->isMode(self::MODE_DEBUG) )
				{
					$c = new ZDummyController($this);
					$c->renderEach(Zoombi::fromFrameworkDir('Views' . Zoombi::DS . 'view_error.php'), $this->m_errors);
					unset($c);
				}
		}

		$this->emit(new ZEvent($this, 'postExecute'));

		if( $this->isMode(self::MODE_DEBUG) )
		{
			restore_exception_handler();
			restore_error_handler();
			error_reporting($old_errr);
		}
	}

	public function _error_handler( $a_errno, $a_errmsg, $e_errfile, $a_errline )
	{
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		$this->m_errors[] = array(
			'code' => $a_errno,
			'message' => $a_errmsg,
			'backtrace' => $backtrace,
			'line' => $a_errline,
			'file' => $e_errfile
		);
	}

	public function _exception_handler( Exception & $e )
	{
		$c = new ZDummyController($this);
		$c->render(Zoombi::fromFrameworkDir('Views' . Zoombi::DS . 'view_exception.php'), array(
			'code' => $e->getCode(),
			'message' => $e->getMessage(),
			'backtrace' => $e->getTrace(),
			'line' => $e->getLine(),
			'file' => $e->getFile()
		));
		unset($c);
	}

	/**
	 * Get data returned by controller
	 * @return mixed
	 */
	public function & getReturn()
	{
		return $this->m_return;
	}

	/**
	 * Get current controller
	 * @return ZController
	 */
	public function & getController()
	{
		return $this->m_current;
	}

	/**
	 * Set application controller
	 * @param ZController $a_ctl
	 * @return ZApplication
	 */
	public function & setController( ZController & $a_ctl )
	{
		if( $this->m_current != $a_ctl )
			$this->m_current = $a_ctl;
		$this->m_current->setParent($this);
		return $this;
	}

	/**
	 * Get or set application controller
	 * @param ZController $a_ctl
	 * @return ZController|ZApplication
	 */
	public function & controller( ZController & $a_ctl = null )
	{
		if( $a_ctl === null )
			return $this->getController();
		return $this->setController($a_ctl);
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
	 * @return ZApplication
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
		return $this->m_plugin_mgr->getPlugins();
	}

	/**
	 * Get plugin
	 * @return ZPlugin
	 */
	public function & getPlugin( $a_plugin )
	{
		return $this->m_plugin_mgr->getPlugin($a_plugin);
	}

	/**
	 * Set plugins
	 * @param array $a_plugins
	 * @return ZPluginManager
	 */
	public function & setPlugins( array $a_plugins )
	{
		$this->m_plugin_mgr->setPlugins($a_plugins);
		return $this;
	}

	/**
	 * Add plugin to stack
	 * @param mixed $a_plugin
	 * @return ZPluginManager
	 */
	public function & addPlugin( $a_plugin )
	{
		$this->m_plugin_mgr->addPlugin($a_plugin);
		return $this;
	}

	/**
	 * Remove plugin from stack
	 * @param int|ZPlugin $a_plugin
	 * @return ZPluginManager
	 */
	public function & removePlugin( $a_plugin )
	{
		$this->m_plugin_mgr->removePlugin($a_plugin);
		return $this;
	}

}
