<?php

/*
 * File: application.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * Application core class
 *
 * @example <br />
 * Application emit events:<br />
 * - preExecute<br />
 * - preRoute<br />
 * - postRoute<br />
 * - onOutput<br />
 * - postExecute<br />
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class ZApplication extends ZModule implements IZSingleton
{
	/**
	 * Application flags
	 */
	const FLAG_NO_ACTION = 'no_action';
	const FLAG_NO_CONTROLLER = 'no_controller';
	const FLAG_NO_ROUTE = 'no_route';
	const FLAG_NO_EXECUTE = 'no_route';

	private $m_route_transform;

	/**
	 * Errors collection
	 * @var array
	 */
	private $m_errors;

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
	 * Singleton instance
	 * @var ZApplication
	 */
	static protected $m_instance = null;

	/**
	 * Session registry
	 * @var ZRegistry
	 */
	private $m_session;
	public $m_app_route_deep;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		ZProfiler::start('Application');

		$this->setName(ZModule::DEFAULT_MODULE_NAME);
		$this->setBaseDir(Zoombi::getBootDir());
		$this->setConfig(Zoombi::fromFrameworkDir('config.php'));

		parent::__construct();

		$this->m_errors = array();
		$this->m_except = array();
		$this->m_database = null;
		$this->m_app_route_deep = 0;
		$this->m_route_transform = array();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this->m_session, $this->m_database);
	}

	public function & getDatabase()
	{
		if($this->m_database)
			return $this->m_database;

		$db = $this->getConfig()->getValue('database', null);
		if($db !== null)
		{
			$d = new ZDatabase($this, 'database');
			$d->open($db);
			$this->setDatabase($d);
			return $this->m_database;
		}
		$this->triggerError('Database is not set');
	}

	public function & getSession()
	{
		return ZSession::getInstance();
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
		switch($a_name)
		{
			case 'run':
			case 'exec':
			case 'start':
			case 'render':
			case 'display':
				if(!$this->getFlag(self::FLAG_NO_EXECUTE))
					return $this->execute();
		}
		return parent::__call($a_name, $a_params);
	}

	/**
	 * Get application instance
	 * @return ZApplication
	 */
	static public final function & getInstance()
	{
		if(self::$m_instance === null)
			self::$m_instance = new self;
		return self::$m_instance;
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
		if(strpos($path, Zoombi::DS, 0) === 0)
			return $this->getApplicationBaseDir() . $path;

		return $this->getApplicationBaseDir() . Zoombi::DS . $path;
	}

	/**
	 * Get application base url
	 * @return string
	 */
	public final function getBaseUrl()
	{
		$url = $this->getConfig()->getValue('baseurl', null);
		if($url === null)
			throw new ZApplicationException('Please define "urlbase" at application config.', E_USER_ERROR);
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
		if(strpos($url, Zoombi::DS, 0) === 0)
			$url = substr($url, -1);

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
		ZResponse::getInstance()
			->setCode('302', 'Found')
			->setHeader('location', (string)$a_to);

		if($a_exit)
			exit(1);
		//throw new ZControllerException('Redirect to', ZControllerException::EXC_QUIT);

		return $this;
	}

	/**
	 * Execute application
	 */
	public final function execute( $a_route_url = null )
	{
		// Attach error and exception handlers
		$olderr = error_reporting(E_ALL);
		set_error_handler(array($this, '_error_handler'));
		set_exception_handler(array($this, '_exception_handler'));

		// Set application plugin manager listen global event dispatcher
		$this->getPluginManager()->setTarget(Zoombi::getDispatcher());
		$this->getDispatcher()->connect('_triggerError', array($this, '_error_trigger'));

		try
		{
			// Notify for start execution
			$this->emit(new ZEvent($this, 'preExecute'));

			// Notify befor route start
			if(!$this->getFlag(self::FLAG_NO_ROUTE))
			{
				// Attach routing rules to router
				$routes = new ZConfig();
				$router = $this->getRouter();
				$rs = $this->getConfig()->getValue('routes', null);

				switch(gettype($rs))
				{
					case 'array':
					case 'object':
						$routes->setData($rs);
						break;

					case 'string':
						$cf = $rs;

						if(!file_exists($cf))
							$cf = $this->fromBaseDir($cf);

						if(file_exists($cf))
							$routes->fromFile($cf);
						break;
				}

				$ra = $routes->toArray();
				$router->setRules($ra);
				unset($routes);

				$request = $a_route_url ? $a_route_url : $_SERVER['REQUEST_URI'];
				$url = new ZUrl($this->getConfig()->getValue('baseurl'));

				if(strstr($request, $url->path) == 0)
					$request = substr($request, strlen($url->path));

				$router->setRequest($request);
				$this->emit(new ZEvent($this, 'preRoute', $router->getRequest()));

				$redirect = $router->rewrite((string)$router->getRequest());
				$router->setRedirect($redirect);

				$this->emit(new ZEvent($this, 'postRoute', $router->getRedirect()));
				$path = clone $router->getRedirect();
				$s = $path->getSegment(0);
				if($s == $this->getName() OR $s == ZModule::DEFAULT_MODULE_NAME)
				{
					if(!$this->getLoader()->hasController($s))
						$path->pop_start();
				}

				$this->exec_route = $this->_route((string)$path);

				if($this->exec_route)
				{
					$this->getRouter()->setCurrent($this->exec_route);
					$this->route($this->exec_route);
				}
			}
		}
		catch(Exception $e)
		{
			switch($e->getCode())
			{
				case ZControllerException::EXC_QUIT:
					$this->getConfig()->setValue('output', false);
					$this->emit(new ZEvent($this, 'onQuit'));
					break;

				case ZControllerException::EXC_QUIT_OUTPUT:
					$this->getConfig()->setValue('output', true);
					$this->emit(new ZEvent($this, 'onQuit'));
					break;

				case ZControllerException::EXC_AUTH:
					$this->emit(new ZEvent($this, 'onError', 401, $e));
					$this->emit(new ZEvent($this, 'on401', $this->getRoute()));
					break;

				case ZControllerException::EXC_DENY:
					$this->emit(new ZEvent($this, 'onError', 403, $e));
					$this->emit(new ZEvent($this, 'on403', $this->getRoute()));
					break;

				case ZControllerException::EXC_LOAD:
				case ZControllerException::EXC_NO_FILE:
				case ZControllerException::EXC_ACTION:
					$this->emit(new ZEvent($this, 'onError', 404, $e));
					$this->emit(new ZEvent($this, 'on404', $this->getRoute()));
					break;

				default:
					$this->emit(new ZEvent($this, 'onError', 500, $e));
					$this->emit(new ZEvent($this, 'on500', $e));
					break;
			}
		}

		restore_error_handler();
		restore_exception_handler();

		if(Zoombi::ack($this->getConfig()->getValue('output', false)))
		{
			ob_start();

			if($this->outputLength() > 0)
				$this->outputFlush();

			if(Zoombi::ack($this->getConfig()->get('showtrace', false)))
				ZDebug::printTraces();

			if(Zoombi::ack($this->getConfig()->get('showerror', false)))
				$this->showErrors();

			ZResponse::getInstance()->appendContent(ob_get_contents());
			ob_end_clean();

			$this->emit(new ZEvent($this, 'onOutput'));
			ZResponse::getInstance()->output();
		}
		$this->emit(new ZEvent($this, 'postExecute'));
		error_reporting($olderr);
	}

	private function & _route( $a_path )
	{
		if(substr($a_path, 0, 1) == Zoombi::SS)
		{
			$rewrite = Zoombi::getApplication()->getName() . Zoombi::SS . substr($a_path, 1);
			$a_path = $rewrite;
		}

		$rewrite = $this
			->getRouter()
			->rewrite($a_path);

		if(empty($rewrite))
			$rewrite = implode(Zoombi::SS, array(ZModule::DEFAULT_MODULE_NAME, ZModule::DEFAULT_CONTROLLER_NAME, ZModule::DEFAULT_ACTION_NAME));

		$path = new ZRoute($rewrite);
		$epath = new ZRoutePath;

		$epath->parents[] = $this;

		if($path->getSegment(0) == $this->getName() AND $this->getLoader()->hasController($path->getSegment(1)))
		{
			$path->pop_start();
		}

		$m = $this;
		do
		{
			$s = $path->getSegment(0);
			$m = $m->getLoader()->module($s);
			if($m)
			{
				$epath->parents[] = $m;
				$path->pop_start();
			}
		}
		while($m);

		$mod = $epath->module;

		if(!$mod)
			throw new ZException('Application router can\'t find module start', ZControllerException::EXC_LOAD);

		$sc = $path->getSegment(0);
		$path->pop_start();

		$ctl = $epath->module->getLoader()->controller($sc);
		if(!$ctl)
			$ctl = $epath->module->getLoader()->controller(ZModule::DEFAULT_CONTROLLER_NAME, false);

		if(!$ctl)
			throw new ZException('Application router can\'t find controller "' . $sc . '" in module "' . $mod->getName() . '"', ZControllerException::EXC_LOAD);

		$epath->controller = $ctl;

		$sa = $path->getSegment(0);
		$path->pop_start();

		if(empty($sa))
			$sa = ZModule::DEFAULT_ACTION_NAME;

		if(!$ctl->hasAction($sa))
			throw new ZException('Application router can\'t find action "' . $sa . '" in controller "' . $sc . '" of module "' . $mod->getName() . '"', ZControllerException::EXC_ACTION);

		$action = $mod->getConfig()->getValue('controller.action_prefix', ZModule::DEFAULT_CONTROLLER_METHOD_PREFIX) . $sa;
		if(substr($action, 0, 1) == '_')
			throw new ZException('External routes to private action requies is disallowed', ZControllerException::EXC_LOAD);

		$epath->action = $sa;
		$_r = implode(Zoombi::SS, array_merge($epath->toArray(), $path->getSegments())) . $path->queryString();
		return $_r;
	}

	public function _error_handler( $a_errno, $a_errmsg, $e_errfile, $a_errline )
	{
		$backetrace = debug_backtrace();
		array_shift($backetrace);
		$this->triggerError($a_errmsg, $a_errno, $backetrace);
	}

	public function _exception_handler( Exception $e )
	{
		$c = new ZDummyController($this, 'DummyController');
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
	 * Print html formated error message
	 * @param int|arrray $a_code
	 * @param string $a_message
	 * @param int $a_line
	 * @param string $a_file
	 * @param array $a_backtrace
	 * @return null
	 */
	public function showError( $a_code, $a_message = null, $a_line = null, $a_file = null, $a_backtrace = null )
	{
		if(is_array($a_code) && func_num_args() == 1)
		{
			return $this->showError(
					isset($a_code['code']) ? $a_code['code'] : null, isset($a_code['message']) ? $a_code['message'] : null, isset($a_code['line']) ? $a_code['line'] : null, isset($a_code['file']) ? $a_code['file'] : null, isset($a_code['backtrace']) ? $a_code['backtrace'] : null
			);
		}

		if($a_code instanceof Exception)
			return $this->showError($a_code->getCode(), $a_code->getMessage(), $a_code->getLine(), $a_code->getFile(), $a_code->getTrace());
		else if($a_code instanceof ZError)
			return $this->showError($a_code->getCode(), $a_code->getMessage(), 0, 0, $a_code->getTrace());

		$c = new ZDummyController($this, 'DummyController');
		$c->render(Zoombi::fromFrameworkDir('Views' . Zoombi::DS . 'view_error.php'), array(
			'code' => $a_code,
			'message' => $a_message,
			'line' => $a_line,
			'file' => $a_file,
			'backtrace' => $a_backtrace
		));

		unset($c);
	}

	public function showErrors()
	{
		foreach($this->m_errors as $e)
		{
			if($e->getCode() != E_STRICT)
				$this->showError($e);
			else
			if(ini_get('error_reporting') & E_STRICT)
				$this->showError($e);
		}
	}

	public function _error_trigger( ZEvent & $a_event )
	{
		$this->m_errors[] = $a_event->getData(0);
	}

}
