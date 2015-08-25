<?php

class ZModule extends ZObject
{
	const MODE_NORMAL = 'normal';
	const MODE_DEBUG = 'debug';
	const MODE_PREFORMANCE = 'preformance';

	private $m_flag;
	private $m_config;
	private $m_language;
	private $m_output;
	private $m_registry;
	private $m_mode;
	private $m_plugin_mgr;
	private $m_base_dir;
	private $m_loader;

	public function __construct( ZObject & $parent = null, $a_name = null )
	{
		parent::__construct( $parent, $a_name );
		$this->m_config = new ZConfig();
		$this->m_flag = array();

		$this->setLoader(new ZLoader($this));
		$this->setLanguage(new ZLanguage($this));
		$this->setRegistry(new ZRegistry());
		$this->setMode(ZApplication::MODE_NORMAL);
		$this->setDispatcher(Zoombi::getDispatcher($this));
		$this->setPluginManager(new ZPluginManager($this));
	}

	public final function & getLoader()
	{
		return $this->m_loader;
	}

	public final function & setLoader( ZLoader & $a_loader )
	{
		$this->m_loader = $a_loader;
		return $this;
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
	 * Get base directory
	 * @return <type>
	 */
	public final function getBaseDir()
	{
		return $this->m_base_dir;
	}

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
		return $this->getBaseDir() . Zoombi::DS . (string)$a_path;
	}

	/**
	 * Get models directory
	 * @return string
	 */
	public final function getModelDir()
	{
		return $this->fromBaseDir($this->m_config->getValue('path.model', 'Model'));
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
		return $this->fromBaseDir($this->m_config->getValue('path.view', 'View'));
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
		return $this->fromBaseDir($this->m_config->getValue('path.view', 'View'));
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
			$a_config = $this->fromBaseDir($a_config);
			if( !file_exists($a_config) OR !is_file($a_config) OR !is_readable($a_config) )
				return $this;

			$this->m_config->fromFile($a_config);
		}

		if( is_array($a_config) )
			$this->m_config->fromArray($a_config);

		$this->setMode( $this->m_config->getValue('mode', self::MODE_NORMAL) );
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
		echo $this->outputGetClear();
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

	/**
	 * Get data returned by controller
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

	public function __call( $a_name, $a_params )
	{
		foreach( $this->getPlugins() as $plugin )
			if( method_exists($plugin, $a_name) )
				return call_user_func_array(array( &$plugin, $a_name ), $a_params);
		
		$t = debug_backtrace();
		$c =& $t[1];

		$e = new ZException();
		if( $c['file'] )
			$e->setFile( $c['file'] );

		if( $c['line'] )
			$e->setLine( $c['line'] );

		$e->setCode(10);
		$e->setMessage( 'Method not found' . ( $c['class'] ? ' ' . $c['class'].'::' : null ) . $a_name);
		throw $e;
	}

}
