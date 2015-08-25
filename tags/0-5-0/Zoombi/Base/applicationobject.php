<?php

/**
 * Base class
 * @property ZAcl $acl
 * @property ZApplication $application
 * @property ZRegistry $registry
 * @property ZLanguage $language
 * @property ZRouter $router
 * @property ZLoader $load
 * @property ZDatabase $database
 * @property ZModule $module
 * @property ZConfig $config
 * @property ZRegistry $session
 * @property ZRequest $request
 * @property ZRoute $route
 * @property ZResponse $response
 */
class ZApplicationObject extends ZObject
{

	static private $m_reserved = array(
		'application', 'database', 'router',
		'session', 'request', 'module',
		'route', 'registry', 'config',
		'language', 'load', 'name', 'response', 'acl', 'plugin'
	);

	public function __construct( ZModule &$a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
	}

	public function url( $a_prepend = null )
	{
		return Zoombi::getApplication()->fromBaseUrl($a_prepend);
	}

	public function & getModule()
	{
		if( $this instanceof ZModule )
			return $this;

		$mod = $this->getParent();
		return $mod;
	}

	/**
	 * Register exception
	 * @param mixed $a_arg0
	 * @param mixed $a_arg1
	 * @param mixed $a_arg2
	 * @return ZModule
	 */
	public function & triggerError( $a_arg0, $a_arg1 = null, $a_arg2 = null)
	{
		switch( func_num_args() )
		{
			case 1:
				switch( gettype($a_arg0) )
				{
					case 'object':
						if( $a_arg0 instanceof Exception )
						{
							Zoombi::getApplication()->emit( new ZEvent($this,'_triggerError',$a_arg0) );
						}
						break;

					case 'string':
						$tr = debug_backtrace();

						$e = new ZException($a_arg0, E_USER_ERROR );
						$e->setLine($tr[0]['line']);
						$e->setFile($tr[0]['file']);
						Zoombi::getApplication()->emit( new ZEvent($this,'_triggerError',$e) );
						break;
				}
				break;

			case 2:
				$tr = debug_backtrace();

				$e = new ZException( $a_arg0, $a_arg1 );
				$e->setLine($tr[0]['line']);
				$e->setFile($tr[0]['file']);
				Zoombi::getApplication()->emit( new ZEvent($this,'_triggerError',$e) );
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
				return ZRequest::getInstance();

			case 'response':
				return ZResponse::getInstance();

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

			case 'load':
				return $this->getModule()->getLoader();

			case 'acl':
				return $this->getModule()->getAcl();
		}

		try
		{
			return $this->getProperty($a_property);
		}
		catch ( ZException $e )
		{
			if( $e->getCode() == ZException::EXC_NO_PROPERTY )
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

		return $this->unsetProperty($a_name);
	}

	public function hasProperty( $a_name )
	{
		if( in_array($a_name, self::$m_reserved) )
			return true;

		return parent::hasProperty($a_name);
	}
	
}
