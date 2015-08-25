<?php

/*
 * File: input.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

/**
 * @property Zoombi_Registry $get
 * @property Zoombi_Registry $post
 * @property Zoombi_Registry $request
 * @property Zoombi_Registry $cookie
 * @property Zoombi_Registry $session
 */
class Zoombi_Input implements Zoombi_Singleton
{

	/**
	 * @var object
	 */
	public $m_vars;
	static private $m_instance;

	private function __construct()
	{
		$this->m_vars = (object)array();

		$this->m_vars->get = new Zoombi_Registry;
		$this->m_vars->get->setDataRef($_GET);

		$this->m_vars->post = new Zoombi_Registry;
		$this->m_vars->post->setDataRef($_POST);
		
		$this->m_vars->request = new Zoombi_Registry;
		$this->m_vars->request->setDataRef($_REQUEST);

		$this->m_vars->cookie = new Zoombi_Registry;
		$this->m_vars->cookie->setDataRef($_COOKIE);

		$this->m_vars->session = new Zoombi_Registry;
		$this->m_vars->session->setDataRef($_SESSION);

		$this->m_vars->put = new Zoombi_Registry;
		$this->m_vars->delete = new Zoombi_Registry;
		$this->m_vars->create = new Zoombi_Registry;
	}

	private function __clone()
	{
		
	}

	public function __destruct()
	{
		foreach(get_object_vars($this->m_vars) as $k => $v)
			unset($this->m_vars->$k);
	}

	public function __isset( $a_name )
	{
		switch(strtolower(trim((string)$a_name)))
		{
			case 'get':
			case 'post':
			case 'request':
			case 'cookie':
			case 'session':
				return true;

			default:
				break;
		}
	}

	public function & __get( $a_name )
	{
		$r = strtolower(trim((string)$a_name));
		$ret = null;
		switch($r)
		{
			case 'get':
			case 'post':
			case 'request':
			case 'cookie':
			case 'session':
				$ret = $this->m_vars->$r;
				break;

			default:
				break;
		}
		return $ret;
	}

	/**
	 * @return Zoombi_Input
	 */
	public static final function & getInstance()
	{
		if(!self::$m_instance)
			self::$m_instance = new self;

		return self::$m_instance;
	}

}