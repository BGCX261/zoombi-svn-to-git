<?php

/**
 * Base class
 * @property Zoombi_Acl $acl
 * @property Zoombi_Application $application
 * @property Zoombi_Registry $registry
 * @property Zoombi_Language $language
 * @property Zoombi_Router $router
 * @property Zoombi_Loader $load
 * @property Zoombi_Database $database
 * @property Zoombi_Module $module
 * @property Zoombi_Config $config
 * @property Zoombi_Registry $session
 * @property Zoombi_Request $request
 * @property Zoombi_Route $route
 * @property Zoombi_Response $response
 */
class Zoombi_Component extends Zoombi_Object
{

	/**
	 *
	 * @var Zoombi_Dispatcher
	 */
	private $m_dispatcher;
	
	static private $m_reserved = array(
		'application', 'database', 'router',
		'session', 'request', 'module',
		'route', 'registry', 'config',
		'language', 'load', 'name', 'response', 'acl', 'plugin'
	);

	public function __construct( Zoombi_Module &$a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
	}

	/**
	 *
	 * @return Zoombi_Dispatcher
	 */
	public function & getDispatcher()
	{
		$d = ($this->m_dispatcher) ?
				$this->m_dispatcher :
				Zoombi::getDispatcher();
		return $d;
	}

	/**
	 *
	 * @param Zoombi_Dispatcher $a_dispatcher
	 * @return Zoombi_Object
	 */
	public function & setDispatcher( Zoombi_Dispatcher & $a_dispatcher )
	{
		if( !$a_dispatcher )
		{
			unset($this->m_dispatcherr);
			return $this;
		}

		$this->m_dispatcherr = $a_dispatcher;
		return $this;
	}

	/**
	 * Emmit an signal
	 * @param Zoombi_Event $a_event
	 * @return Zoombi_Component 
	 */
	public function & emit( Zoombi_Event & $a_event )
	{
		$this->getDispatcher()->emit($a_event);
		return $this;
	}

	/**
	 * Make url path grom current module location
	 * @param string $a_prepend
	 * @return string 
	 */
	public function url( $a_prepend = null )
	{
		return Zoombi::getApplication()->fromBaseUrl($a_prepend);
	}

	/**
	 * Get parent module
	 * @return Zoombi_Module 
	 */
	public function & getModule()
	{
		$mod = $this->getParent();
		return $mod;
	}

	/**
	 * Register exception
	 * @param mixed $a_arg0
	 * @param mixed $a_arg1
	 * @param mixed $a_arg2
	 * @return Zoombi_Module
	 */
	public function & triggerError( $a_arg0, $a_arg1 = null, $a_arg2 = null )
	{
		switch( func_num_args() )
		{
			case 1:
				switch( gettype($a_arg0) )
				{
					case 'object':
						if( $a_arg0 instanceof Exception )
						{
							Zoombi::getApplication()->emit(new Zoombi_Event($this, '_triggerError', $a_arg0));
						}
						break;

					case 'string':
						$tr = debug_backtrace();

						$e = new Zoombi_Exception($a_arg0, E_USER_ERROR);
						$e->setLine($tr[0]['line']);
						$e->setFile($tr[0]['file']);
						Zoombi::getApplication()->emit(new Zoombi_Event($this, '_triggerError', $e));
						break;
				}
				break;

			case 2:
				$tr = debug_backtrace();

				$e = new Zoombi_Exception($a_arg0, $a_arg1);
				$e->setLine($tr[0]['line']);
				$e->setFile($tr[0]['file']);
				Zoombi::getApplication()->emit(new Zoombi_Event($this, '_triggerError', $e));
				break;

			case 3:
				$e = new Zoombi_Exception($a_arg0, $a_arg1);

				if( $a_arg2 AND is_array($a_arg2) AND count($a_arg2) > 0 )
				{
					$a = & $a_arg2[0];

					if( is_array($a) )
					{
						if( array_key_exists('line', $a) )
							$e->setLine($a['line']);

						if( array_key_exists('file', $a) )
							$e->setFile($a['file']);

						$e->setTrace($a_arg2);
					}
				}
				Zoombi::getApplication()->emit(new Zoombi_Event($this, '_triggerError', $e));
				break;
		}
		return $this;
	}

	public function & __get( $a_property )
	{
		switch( $a_property )
		{
			default:
				break;

			case 'application':
				return Zoombi::getApplication();

			case 'database':
				return Zoombi::getApplication()->getDatabase();

			case 'router':
				return $this->getModule()->getRouter();

			case 'session':
				return Zoombi::getApplication()->getSession();

			case 'request':
				return Zoombi_Request::getInstance();

			case 'response':
				return Zoombi_Response::getInstance();

			case 'module':
				return $this->getModule();

			case 'route':
				return $this->getModule()->getRoute();

			case 'registry':
				return $this->getModule()->getRegistry();

			case 'config':
				return $this->getModule()->getConfig();

			case 'language':
				return $this->getModule()->getLanguage();

			case 'loader':
			case 'load':
				return $this->getModule()->getLoader();

			case 'acl':
				return $this->getModule()->getAcl();
		}

		try
		{
			return $this->getProperty($a_property);
		}
		catch( Zoombi_Exception $e )
		{
			if( $e->getCode() == Zoombi_Exception::EXC_NO_PROPERTY )
				return Zoombi::$null;
		}

		return Zoombi::$null;
	}

	public function __set( $a_name, $a_value )
	{
		if( in_array($a_name, self::$m_reserved) )
			return;

		return $this->setProperty($a_name, $a_value);
	}

	public function __unset( $a_name )
	{
		if( in_array($a_name, self::$m_reserved) )
			return;

		$this->unsetProperty($a_name);
	}

	public function hasProperty( $a_name )
	{
		if( in_array($a_name, self::$m_reserved) )
			return true;

		return parent::hasProperty($a_name);
	}

}
