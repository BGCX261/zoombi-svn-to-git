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
class ZView extends ZRenderer
{

	/**
	 * Blocks
	 * @var array
	 */
	private $m_block;
	/**
	 * Name of view
	 * @var string
	 */
	private $m_view;

	/**
	 * Constructor
	 * @param ZController $a_controller
	 * @param string $a_view
	 */
	public function __construct( ZController & $a_controller, $a_view = null )
	{
		parent::__construct($a_controller);
		$this->m_block = array( );
		$this->setController($a_controller);
		if( $a_view )
			$this->setView($a_view);
	}

	public function & getView()
	{
		return $this->m_view;
	}

	public function & setView( $a_view )
	{
		$this->m_view = $a_view;
		return $this;
	}

	public function & block( $a_name )
	{
		if( isset($this->m_block[$a_name]) )
			return $this->m_block[$a_name];

		$file = null;
		$file = ZLoader::_loadFile($a_name, 'view', 'ZView');
		if( !$file || empty($file) )
			trigger_error('Block ' . $a_name . ' not found.');

		$block = new ZViewBlock($this->getParent(), 'ViewBlock');
		$block->setViewFile($file);
		$this->m_block[$a_name] = & $block;
		return $this->m_block[$a_name];
	}

	public function display( $a_return = false )
	{
		$fname = $this->m_view;
		if( file_exists($fname) == false )
		{
			$fname = $this->load->view($fname);
		}		
		return $this->renderFile($fname, null, $a_return);
	}

	public function __toString()
	{
		return $this->display(true);
	}

}
