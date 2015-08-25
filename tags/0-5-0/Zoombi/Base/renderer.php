<?php

/*
 * File: renderer.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * File renderer
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class ZRenderer extends ZControllable
{

	/**
	 * Data to render
	 * @var ZRegistry
	 */
	private $m_data;

	/**
	 * Constructor
	 * @param ZObject $a_parent
	 * @param string $a_name
	 */
	public function __construct( ZObject & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		$this->m_data = new ZRegistry();
	}

	/**
	 * Set data to render
	 * @param mixed $a_data
	 * @return ZRenderer
	 */
	public function & setData( $a_data )
	{
		$this->m_data->setData($a_data);
		return $this;
	}

	/**
	 * Set reference of data to render
	 * @param mixed $a_data
	 * @return ZRenderer
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
				throw new ZViewException('File to render "' . $___file___ . '" is not found.', ZViewException::EXC_EMPTY);
			return;
		}

		if( !file_exists($___file___) )
		{
			if( $this->application->isMode(ZApplication::MODE_DEBUG) )
				throw new ZViewException('File to render "' . $___file___ . '" is not found.', ZViewException::EXC_NOFILE);
			return;
		}

		if( !is_readable($___file___) )
		{
			if( $this->application->isMode(ZApplication::MODE_DEBUG) )
				throw new ZViewException('File to render "' . $___file___ . '" is not readable.', ZViewException::EXC_NOREAD);
			return;
		}

		if( func_get_arg(1) )
			$this->setDataRef(func_get_arg(1));

		//$data___ = array( "this" => $this->getController() );
		//if( $this->m_data->count() )
		//	$data___ = array_merge($data___, $this->m_data->getData());

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
