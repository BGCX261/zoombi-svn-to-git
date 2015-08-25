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
 * Application emit events:<br />
 * - preExecute<br />
 * - preRoute<br />
 * - postRoute<br />
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
	/**
	 * Application flags
	 */
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
	public function __construct( ZObject & $parent = null, $a_name = null )
	{
		ZProfiler::start('Application');
		parent::__construct($parent, $a_name);

		$this->m_errors = array( );
		$this->m_except = array( );
		$this->m_database = null;
		$this->m_app_route_deep = 0;
	}

	public function __destruct()
	{
		parent::__destruct();

		if( $this->m_session )
			$_SESSION = $this->m_session->toArray();

		unset($this->m_router, $this->m_session, $this->m_database);
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
		return parent::__call($a_name, $a_params);
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
		if( $this->m_router === null )
			$this->m_router = new ZRouter();

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
		$url = $this->getConfig()->getValue('baseurl', null);
		if( $url === null )
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
		if( strpos($url, Zoombi::DS, 0) === 0 )
			$url = substr($url, -1);

		return $this->getBaseUrl() /*. Zoombi::DS .*/ . $url;
	}

	/**
	 * Redirect
	 * @param string $a_to String to redirect
	 * @param bool $a_exit Exit now
	 * @return ZApplication
	 */
	public function & redirect( $a_to, $a_exit = true )
	{
		$this->response->setCode('302', 'Found');
		$this->response->setHeader('location', (string)$a_to);
		if( $a_exit )
			throw new ZControllerException('Redirect to', ZControllerException::EXC_QUIT);

		return $this;
	}

	/**
	 * Execute application
	 */
	private final function _execute()
	{
		$_errtype = E_ALL/* | E_STRICT*/;
		// Set application plugin manager listen global event dispatcher
		$this->getPluginManager()->setTarget( Zoombi::getDispatcher() );
		$this->getDispatcher()->connect('_triggerError', array(&$this,'_error_trigger'));

		try
		{
			// Notify for start execution
			$this->emit(new ZEvent($this, 'preExecute'));

			// Attach error and exception handlers if applicatio execution mode is 'debug'
			if( $this->isMode(self::MODE_DEBUG) )
			{
				$old_errr = error_reporting($_errtype);
				set_error_handler(array( $this, '_error_handler' ), $_errtype);
				//set_exception_handler(array( $this, '_exception_handler' ));
			}

			// Notify befor route start
			//$this->emit(new ZEvent($this, 'preRoute'));

			if( !$this->getFlag(self::FLAG_NO_ROUTE) )
			{
				$request = null;

				// Process request info
				$req_qry = explode('?', $_SERVER['REQUEST_URI'], 2);

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
				$routes = new ZConfig();
				$rs = $this->getConfig()->getValue('routes', null);

				switch( gettype($rs) )
				{
					case 'array':
					case 'object':
						$routes->setData($rs);
						break;

					case 'string':
						$routes->fromFile($this->fromBaseDir($rs));
						break;
				}
				$this->getRouter()->setRules($routes->toArray());
				unset($routes);

				$this->emit(new ZEvent($this, 'preRoute', $rvar));

				$this->getRouter()->setRequest($rvar);
				$redir = $this->getRouter()->route($rvar);
				$p = new ZRoute($redir);

				if( !$this->getLoader()->hasModule($p->getModule()) AND $p->getModule() != $this->getName() )
					$p->push_start($this->getName());

				$fix = $this->fixroute($p);

				$this->getRouter()->setRedirect($fix)->setCurrent($fix)->setForward($fix);
				$this->emit(new ZEvent($this, 'postRoute', $fix));

				$approute = true;
				if( $p->getModule() == $this->getName() )
					$p->pop_start();

				$this->route($p,array('approute'));
				$approute = false;
			}
		}
		catch( ZException $e )
		{
			switch( $e->getCode() )
			{
				case ZControllerException::EXC_QUIT:
					$this->getConfig()->setValue('output', false);
					break;

				case ZControllerException::EXC_QUIT_OUTPUT:
					$this->getConfig()->setValue('output', true);
					break;

				case ZControllerException::EXC_LOAD:
				case ZControllerException::EXC_NO_FILE:
				case ZControllerException::EXC_ACTION:
					$this->emit(new ZEvent($this, 'onError', 404, $e));
					$this->emit(new ZEvent($this, 'on404', $this->getRoute()));
					break;

				case ZControllerException::EXC_DENY:
					$this->emit(new ZEvent($this, 'onError', 403, $e));
					$this->emit(new ZEvent($this, 'on403', $this->getRoute()));
					break;

				case ZControllerException::EXC_AUTH:
					$this->emit(new ZEvent($this, 'onError', 401, $e));
					$this->emit(new ZEvent($this, 'on401', $this->getRoute()));
					break;

				default:
					$this->emit(new ZEvent($this, 'onError', 500, $e));
					$this->emit(new ZEvent($this, 'on500', $e));
					break;
			}
		}
		
		if( Zoombi::ack($this->getConfig()->getValue('output', false)) )
		{
			ob_start();
			if( Zoombi::ack($this->getConfig()->get('showerror','false')) )
				foreach( $this->m_errors as $e )
					$this->showError( $e );
			
			$this->emit(new ZEvent($this, 'onOutput'));
			$cnt =  ob_get_contents();
			ob_end_clean();
			ZResponse::getInstance()->setContent( ZResponse::getInstance()->getContent() . $cnt );
			
		}

		$this->emit(new ZEvent($this, 'postExecute'));

		if( $this->isMode(self::MODE_DEBUG) )
		{
			restore_error_handler();
			error_reporting($old_errr);
		}

		ZResponse::getInstance()->output();
	}

	public function _error_handler( $a_errno, $a_errmsg, $e_errfile, $a_errline )
	{
		$backetrace = debug_backtrace();
		array_shift($backetrace);
		/*$this->m_errors[] = array(
			'code' => $a_errno,
			'message' => $a_errmsg,
			'backtrace' => $backetrace,
			'line' => $a_errline,
			'file' => $e_errfile
		);*/

		$this->triggerError($a_errmsg,$a_errno,$backetrace);
	}

	public function _exception_handler( Exception $e )
	{
		$c = new ZController($this, 'DummyController');
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
	public function showError( $a_code, $a_message = null, $a_line = null, $a_file = null , $a_backtrace = null )
	{
		
		if( is_array($a_code) && func_num_args() == 1 )
		{
			$c = isset( $a_code['code'] ) ? $a_code['code'] : null;
			$m = isset( $a_code['message'] ) ? $a_code['message'] : null;
			$l = isset( $a_code['line'] ) ? $a_code['line'] : null;
			$f = isset( $a_code['file'] ) ? $a_code['file'] : null;
			$b = isset( $a_code['backtrace'] ) ? $a_code['backtrace'] : null;

			return $this->showError($c,$m,$l,$f,$b);
		}
		else if( $a_code instanceof Exception )
			return $this->showError($a_code->getCode(),$a_code->getMessage(),$a_code->getLine(),$a_code->getFile(),$a_code->getTrace());
		else if( $a_code instanceof ZError )
			return $this->showError($a_code->getCode(),$a_code->getMessage(),0,0,$a_code->getTrace());

		$c = new ZDummyController($this, 'DummyController');
		$v = Zoombi::fromFrameworkDir('Views' . Zoombi::DS . 'view_error.php');
		$c->render($v, array(
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
		foreach( $this->m_errors as $e )
		$this->showError($e);
	}

	public function _error_trigger( ZEvent & $a_event )
	{
		$this->m_errors[] = $a_event->getData(0);
	}

}
