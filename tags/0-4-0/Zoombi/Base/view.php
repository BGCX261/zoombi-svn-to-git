<?php

/*
 * File: view.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * MVC (View)
 */
class ZView extends ZObject
{

	/**
	 * Name of view
	 * @var string
	 */
	private $m_view;
	/**
	 *
	 */
	private $m_this;
	/**
	 * Data to render
	 * @var ZRegistry
	 */
	protected $m_data;

	/**
	 * Constructor
	 * @param ZController $a_controller
	 * @param string $a_view
	 */
	public function __construct( $a_view = null, $a_data = null )
	{
		parent::__construct();

		$this->m_data = new ZRegistry;

		if( $a_view )
			$this->setView($a_view);

		if( $a_data )
			$this->m_data->setDataRef($a_data);
	}

	/**
	 * Set this replacement
	 * @param mixed $a_this
	 * @return ZView
	 */
	public function & setThis( & $a_this )
	{
		$this->m_this = $a_this;
		return $this;
	}

	/**
	 * Get this replacement
	 * @return mixed
	 */
	public function getThis()
	{
		return $this->m_this;
	}

	/**
	 * Destruction
	 */
	public function __destruct()
	{
		unset($this->m_data);
	}

	/**
	 * Get view
	 * @return string
	 */
	public function & getView()
	{
		return $this->m_view;
	}

	/**
	 * Set view
	 * @param string $a_view
	 * @return ZView
	 */
	public function & setView( $a_view )
	{
		$this->m_view = $a_view;
		return $this;
	}

	/**
	 * Display current view
	 * @param bool $a_return
	 * @return mixed
	 */
	public function display( $a_return = false )
	{
		if( !file_exists($this->m_view) OR !is_file($this->m_view) )
			$this->m_view = Zoombi::getApplication( )->getLoader()->view($this->m_view);
		return $this->renderFile($this->m_view, null, $a_return);
	}

	/**
	 * Display views
	 * @return string
	 */
	public function __toString()
	{
		return $this->display(true);
	}

	/**
	 * Object getter
	 * @param string $a_name
	 * @return mixed
	 */
	public function & __get( $a_name )
	{
		$get = !$this->m_this ?
				parent::__get($a_name) : $this->m_this->$a_name;
		return $get;
	}

	/**
	 * Object setter
	 * @param string $a_name
	 * @param mixed $a_value
	 * @return mixed
	 */
	public function __set( $a_name, $a_value )
	{
		if( !$this->m_this )
			parent::__set($a_name, $a_value);

		$this->m_this->$a_name = $a_value;
	}

	/**
	 * Object caller
	 * @param string $a_name
	 * @param array $a_args
	 * @return mixed
	 */
	public function __call( $a_name, $a_args )
	{
		if( !$this->m_this )
		{
			if( $a_name == 'render' )
				return call_user_func_array(array( &$this, 'display' ), $a_args);

			return $this->triggerError('Method not found: ' . $a_name, E_USER_WARNING);
		}

		if( method_exists($this->m_this, $a_name) && is_callable(array( &$this->m_this, $a_name )) )
			return call_user_func_array(array( &$this->m_this, $a_name ), $a_args);
	}

	/**
	 * Set data to render
	 * @param mixed $a_data
	 * @return ZView
	 */
	public function & setData( $a_data )
	{
		$this->m_data->setData($a_data);
		return $this;
	}

	/**
	 * Set reference of data to render
	 * @param mixed $a_data
	 * @return ZView
	 */
	public function & setDataRef( & $a_data )
	{
		$this->m_data->setDataRef($a_data);
		return $this;
	}

	/**
	 * Get data to render
	 * @return array
	 */
	public function getData()
	{
		return $this->m_data->getData();
	}

	/**
	 * Get data to render
	 * @return array
	 */
	public function & getDataRef()
	{
		return $this->m_data->getDataRef();
	}

	/**
	 * Render file
	 * @param string $a___file
	 * @param array $a___data
	 * @param bool $a___return
	 * @return mixed
	 */
	protected function renderFile()
	{
		$___file___ = func_get_arg(0);
		if( empty($___file___) )
		{
			if( $this->application->isMode(ZApplication::MODE_DEBUG) )
				throw new ZViewException('View file empty.', ZViewException::EXC_EMPTY);
			return;
		}

		if( !file_exists($___file___) )
		{
			if( $this->application->isMode(ZApplication::MODE_DEBUG) )
				throw new ZViewException('View file "' . $___file___ . '" is not found.', ZViewException::EXC_NOFILE);
			return;
		}

		if( !is_readable($___file___) )
		{
			if( $this->application->isMode(ZApplication::MODE_DEBUG) )
				throw new ZViewException('View file "' . $___file___ . '" is not readable.', ZViewException::EXC_NOREAD);
			return;
		}

		if( func_get_arg(1) )
			$this->setDataRef(func_get_arg(1));

		extract(array( 'this' => $this->getThis() ? $this->getThis() : $this ));
		extract($this->getDataRef(), EXTR_REFS | EXTR_PREFIX_INVALID, '_');

		ob_start();
		{
			include($___file___);
		}
		if( func_get_arg(2) )
		{
			$___echo___ = ob_get_contents();
			ob_end_clean();
			return $___echo___;
		}
		ob_end_flush();
	}

	/**
	 * Render string
	 * @param string $a_string
	 * @param array $a_data
	 * @param bool $a_return
	 * @return mixed
	 */
	protected function renderString( $a_string, $a_data = array( ), $a_return = false )
	{
		if( empty($a_string) )
			return;

		if( $a_data )
			$this->setDataRef($a_data);

		if( $this->m_data->count() > 0 )
			extract($this->getDataRef(), EXTR_REFS | EXTR_OVERWRITE);

		ob_start();
		eval(' ?>' . $a_string . '<?php ');
		if( $a_return )
		{
			$echo = ob_get_contents();
			ob_end_clean();
			return $echo;
		}
		ob_end_flush();
	}

}
