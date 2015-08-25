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
class ZApplication extends ZModule implements IZSingleton
{
	const FLAG_NO_ACTION = 'no_action';
	const FLAG_NO_CONTROLLER = 'no_controller';
	const FLAG_NO_ROUTE = 'no_route';
	const FLAG_NO_EXECUTE = 'no_route';
	
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
	 * @var ZController;
	 */
	private $m_current;

	/**
	 * Application base directory
	 * @var string
	 */
	private $m_basedir;

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

	/**
	 * Constructor
	 */
	public function __construct( ZObject & $parent = null, $a_name = null )
	{
		parent::__construct( $parent, $a_name );

		ZProfiler::start('Application');
		
		$this->m_errors = array( );
		$this->m_except = array( );
		$this->m_database = null;

		$this->setRouter(new ZRouter());
	}

	public function & getDatabase()
	{
		if( $this->m_database )
			return $this->m_database;

		$db = $this->getConfig()->getValue('database', null);
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

		foreach( $this->getPluginManager()->getPlugins() as $plugin )
		{
			if( method_exists($plugin, $a_name) )
			{
				return call_user_func_array(array( &$plugin, $a_name ), $a_params);
			}
		}

		trigger_error('Method not found: ' . $a_name, E_USER_WARNING);
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
		$url = $this->getConfig()->getValue('baseurl');
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

	private function _processRoute( ZRoute & $a_route, & $a_code, & $a_message )
	{
		$ctl = null;

		$mod = null;
		if( $a_route->module && $a_route->module != 'default' && $a_route->module != 'index' )
		{
			$mod = new ZModule( $this, 'Module-'.$a_route->module );
			$mod->setBaseDir( $this->fromBaseDir( Zoombi::config('path.module','module') ) . Zoombi::DS . $a_route->module );
		}

		if( !$a_route->controller )
			$a_route->controller = $this->getConfig()->getValue('controller.default_name', 'index');

		if( !$a_route->action )
			$a_route->action = $this->getConfig()->getValue('controller.default_action', 'index');

		if( !$mod )
			$mod =& $this;
		try
		{
			$ctl = $mod->load->controller($a_route->getController());
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
		$this->setMode(strtolower(strval($this->getConfig()->getValue('mode', self::MODE_NORMAL))));

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
			$rt = $this->getConfig()->getValue('routevar', 'rt');

			$rvar = null;
			if( isset($_GET[$rt]) )
			{
				$rvar = $_GET[$rt];
				unset($_GET[$rt]);
			}

			if( $req_qry )
				$rvar .= '?' . $req_qry[0];

			// Attach routing rules to router
			$rc = new ZConfig($this->fromApplicationBaseDir($this->getConfig()->getValue('routes', null)));
			$router = new ZRouter($rvar, $rc->toArray());
			unset($rc);
			$this->setRouter($router);

			//$router->route();

			if( $this->isMode(self::MODE_DEBUG) )
				Zoombi::log('Request route: ' . $router->getRequest(), 'FrontController');

			// Get routing result
			$route = $router->getRedirect();
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

				$route = $this->getRouter()->getRedirect();

				// Notify after controller be created
				$this->emit(new ZEvent($this, 'postController', $route->getController()));

				// Processing controller
				$ctl = $this->getController();
				if( $ctl )
				{
					if( !$ctl->hasAction($route->getAction()) )
						$route->action = $this->getConfig()->getValue('controller.default_action', 'index');

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
								$this->getConfig()->setValue('output', false);
							}
						}
					}
				}
			}
		}
		$this->setOutput(ob_get_contents());
		ob_end_clean();

		$flag = $this->getConfig()->getValue('output', false);
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
					$c = new ZDummyController($this,'DummyController');
					$c->renderEach(Zoombi::fromFrameworkDir('Views' . Zoombi::DS . 'view_error.php'), $this->m_errors);
					unset($c);
				}
				break;
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

		//$this->m_current->setParent($this);

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

}
