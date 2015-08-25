<?php
/*
 * File: config.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Modules loader
 */
class ZLoader extends ZObject implements IZSingleton
{
    const EXC_EMPTY = 1;
    const EXC_NO_FILE = 2;
    const EXC_NO_READ = 3;
    const EXC_NO_CLASS = 4;
    const EXC_NO_BASE = 5;

    const SEPARATOR = '/';

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
     * @var ZLoader
     */
    static protected $m_instance = null;

    /**
     * Constructor
     */
    function __construct( ZObject & $a_parent = null, $a_name = null )
    {
        parent::__construct($a_parent,$a_name);
        $this->m_models = array();
        $this->m_controllers = array();
        $this->m_library = array();
        $this->m_plugins = array();
    }

    /**
     * Protect from cloning
     */
    private function  __clone()
    {
    }

    /**
     * Get ZLoader instance
     * @return ZLoader
     */
    static public function & getInstance()
    {
        if( self::$m_instance == null )
            self::$m_instance = new ZLoader;
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

        $exp = explode( self::SEPARATOR, $class_name );
        if( count($exp) > 1 )
        {
            $class_name = array_pop( $exp );
            $class_base = implode( Zoombi::DS, $exp );
        }

        $c_prefix = strtolower( trim((string)$a_prefix) );
        $m_prefix = $c_prefix;

		if( empty($class_name) )
            throw new Exception("{$m_prefix} class name must be not empty. ", ZLoader::EXC_EMPTY );

        if( $a_instances !== null )
        {
            if( isset($a_instances[$class_name]) )
                return $a_instances[$class_name];
        }

		$class_class = Zoombi::config( $c_prefix.'.class_prefix')	.
            $class_name .
            Zoombi::config($c_prefix.'.class_suffix', $m_prefix );

        if( class_exists($class_class) )
		{
            if( !is_subclass_of($class_class, $a_baseclass) )
                throw new Exception("{$m_prefix} class '{$class_class}' must be implement from '{$a_baseclass}'.", ZLoader::EXC_NO_BASE);

            if( $a_instances !== null )
            {
                $a_instances[$class_name] = new $class_class();
                return $a_instances[$class_name];
            }
		}

		$class_file	= Zoombi::config($c_prefix.'.file_prefix') .
            $class_name .
            Zoombi::config($c_prefix.'.file_suffix') .
            '.' .
            Zoombi::config($c_prefix.'.file_extension', 'php' );

        //$class_dir = Zoombi::getApplication()->fromApplicationBaseDir( Zoombi::config( 'path.'.$c_prefix, $c_prefix ) );
        $class_dir = $this->module->fromBaseDir( Zoombi::config( 'path.'.$c_prefix, $c_prefix ) );
		if( $class_base )
            $class_dir .= Zoombi::DS . $class_base;

        $class_path = $class_dir . Zoombi::DS . $class_file;

        if( !file_exists($class_path) )
            throw new Exception("{$m_prefix} class '{$class_class}' file not exist at '{$class_path}'. ", ZLoader::EXC_NO_FILE);

        if( !is_readable($class_path) )
            throw new Exception("{$m_prefix} class '{$class_class}' file not exist at '{$class_path}'. ", ZLoader::EXC_NO_READ);

        include_once($class_path);

		if( !class_exists($class_class) )
			throw new Exception("{$m_prefix} class '{$class_class}' not found '{$class_path}' ", ZLoader::EXC_NO_CLASS);

		if( !is_subclass_of($class_class, $a_baseclass) )
			throw new Exception("{$m_prefix} class '{$class_class}' must be implement from '{$a_baseclass}'.", ZLoader::EXC_NO_BASE);

        $instance = new $class_class($this->module,$class_name);

        if( $a_instances !== null )
        {
            $a_instances[$class_name] =& $instance;
            return $a_instances[$class_name];
        }
        return $instance;
    }

    public final function _loadFile( $a_name, $a_section, $a_title = 'ZLoader' )
    {
        $file_base = null;
        $file_name = (string)$a_name;
        $section = trim((string)$a_section);

        $exp = explode( self::SEPARATOR, $file_name );
        if( count($exp) > 1 )
        {
            $file_name = array_pop( $exp );
            $file_base = implode( Zoombi::DS, $exp );
        }

        $file_file = Zoombi::config( $a_section.'.file_prefix') .
            $file_name .
            Zoombi::config($a_section.'.file_suffix') .
            '.' .
            Zoombi::config($a_section.'.file_extension','php');

        //$file_dir = Zoombi::getApplication()->fromApplicationBaseDir( Zoombi::config('path.'.$a_section,$section) );
        $file_dir = $this->module->fromBaseDir( Zoombi::config('path.'.$a_section,$section) );
		if( $file_base )
            $file_dir .= Zoombi::DS . $file_base;

        $file_path = $file_dir . Zoombi::DS . $file_file;

        if( !file_exists($file_path) )
            throw new Exception("{$a_title}: file '{$file_path}' is not exist", ZLoader::EXC_NO_FILE);

		if( !is_readable($file_path) )
			throw new Exception("{$a_title}: file '{$file_path}' is not readable", ZLoader::EXC_NO_READ);

        return $file_path;
    }

    /**
     * Load controller
     * @param string $a_controller Controller name
     * @return ZController
     */
    public final function & controller( $a_controller )
	{
        $c = null;
        try
        {
            $c = $this->_loadClass( $a_controller, self::getInstance()->m_controllers, 'controller', 'ZController' );
        }
        catch ( Exception $e )
        {
            throw new ZControllerException( $e->getMessage(), $e->getCode() );
        }
        return $c;
	}

    /**
     * Load model
     * @param string $a_model Model name
     * @return ZModel
     */
	public final function & model( $a_model )
	{
        $c = null;
        try
        {
            $c = $this->_loadClass( $a_model, self::getInstance()->m_models, 'model', 'ZModel' );
        }
        catch ( Exception $e )
        {
            throw new ZModelException( $e->getMessage(), $e->getCode() );
        }
        return $c;
	}

    /**
     * Load plugin
     * @param string $a_plugin Plugin name
     * @return ZPlugin
     */
    public final function & plugin( $a_plugin )
	{
        $c = null;
        try
        {
            $c = $this->_loadClass( $a_plugin, $c, 'plugin', 'ZPlugin' );
        }
        catch ( Exception $e )
        {
            throw new ZPluginException( $e->getMessage(), $e->getCode() );
        }
        return $c;
	}

    /**
     * Load action
     * @param string $a_action A action name
     * @return ZAction
     */
    public final function action( $a_action )
    {
        $c = null;
        try
        {
            $c = $this->_loadClass( $a_action, $c, 'action', 'ZAction' );
        }
        catch ( Exception $e )
        {
            throw new ZActionException( $e->getMessage(), $e->getCode() );
        }
        return $c;
    }

    /**
     * Find view file path
     * @param string $a_view View name
     * @return string
     */
    public final function view( $a_view )
    {
        $ret = null;
        try
        {
            $ret = $this->_loadFile($a_view, 'view', 'ZView');
        }
        catch ( Exception $e )
        {
            throw new ZViewException( $e->getMessage(), $e->getCode() );
        }
		return $ret;
    }

	/**
     * Find helper file path
     * @param string $a_helper
     * @return string
     */
	public final function helper( $a_helper )
	{
        try
        {
			$filename = $this->_loadFile($a_helper, 'helper', 'Helper');
        }
        catch ( Exception $e )
        {
            throw new ZHelperException( $e->getMessage(), $e->getCode() );
		}

		if( $filename )
		{
			include_once $filename;
			return true;
		}

        return $ret;
	}

	/**
	 * Load from library
	 * @param string $a_path
	 * @return bool True if load success else return false
	 */
	static public final function library( $a_path )
	{
        $i = ZLoader::getInstance();

		if( key_exists($a_path, $i->m_library) )
			return true;

		$i->m_library[ $a_path ] = true;

		$exp_path   = explode('.',$a_path);
		$classname  = array_pop($exp_path);

        $i->emit( new ZEvent($i, 'onLibrary', $classname ) );

		if( $exp_path[0] == 'zoombi' )
		{
			array_shift($exp_path);

			$new_path = array();
			foreach($exp_path as $p)
				array_push($new_path, ucwords($p) );

			$exp_path = $new_path;
			$new_path = ( is_array($exp_path) ) ? implode(Zoombi::DS, $exp_path ) : $exp_path;

			$filename = Zoombi::fromFrameworkDir($new_path.Zoombi::DS.strtolower($classname).'.php');

			$code = null;

			if( file_exists($filename) )
				$code = include_once( $filename );

			if( $code != -1 )
			{
                $i->emit( new ZEvent( $i, 'onLibrarySucess', $classname ) );
				return true;
			}
		}
		else
		{
			$new_path = implode(Zoombi::DS,$exp_path);
			$filename = Zoombi::fromFrameworkDir($new_path.Zoombi::DS.$classname.'.php');

			$code = $this->loadFile($filename);
			if( $code )
			{
				if( class_exists($classname) )
                    $i->emit( new ZEvent( $i, 'onLibrarySucess', $classname ) );
				return true;
			}
		}
        $i->emit( new ZEvent( $i, 'onLibraryFailed', $classname ) );
		return false;
	}

	/**
	 * Class autoloader
	 * @param string $a_class Class name
	 */
	public final function autoload( $a_class )
	{
		$this->emit( new ZEvent( $this, 'onAutoload', $a_class ) );
		if( $a_class[0] == 'Z' )
		{
			$filename = strtolower( substr( $a_class, 1, strlen($a_class) - 1 ) );
			$base = Zoombi::getFrameworkDir();
			foreach( scandir($base) as $dir )
			{
				if( $dir == '.' OR $dir == '..' )
					continue;

				$path = $base . DIRECTORY_SEPARATOR . $dir;
				if( is_dir($path) == false )
					continue;

				$filepath = $path . DIRECTORY_SEPARATOR . $filename . '.php';
				if( is_file($filepath) )
				{
					require_once $filepath;
					$this->emit( new ZEvent( $this, 'onAutoloadSuccess', $a_class ) );
					return;
				}
			}
		}
		$this->emit( new ZEvent( $this, 'onAutoloadFailed', $a_class ) );
	}
}
