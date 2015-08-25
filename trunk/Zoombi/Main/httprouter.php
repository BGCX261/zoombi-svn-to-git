<?php

class Zoombi_HttpRouter
{	
	public $router;
	
	public function __construct()
	{
		$this->router = (object)array(
			'get' => new Zoombi_StringRouter(),
			'post' => new Zoombi_StringRouter(),
			'put' => new Zoombi_StringRouter(),
			'delete' => new Zoombi_StringRouter()
		);
	}
	
	function route( $a_route = null, $a_method = null )
	{
		$method = Zoombi_String::normalize(!$a_method ? $_SERVER['REQUEST_METHOD'] : '');
		$router = isset($this->router->$method) ? $this->router->$method : null;
		if( $router )
			return $router->route( !$a_route ?  $_SERVER['REQUEST_URI'] : '' );
		
		return false;
	}
	
	public function get( $a_pattern, $a_callback = null )
	{
		if( func_num_args() < 1 )
			return $this->router->get;
			
		call_user_func_array( array($this->router->get, 'add'), func_get_args() );
		return $this;
	}
	
	public function post( $a_pattern, $a_callback = null )
	{			
		if( func_num_args() < 1 )
			return $this->router->post;
		
		call_user_func_array( array($this->router->post, 'add'), func_get_args() );
		return $this;
	}
	
	public function put( $a_pattern, $a_callback = null )
	{
		if( func_num_args() < 1 )
			return $this->router->put;
		
		call_user_func_array( array($this->router->put, 'add'), func_get_args() );
		return $this;
	}
	
	public function delete( $a_pattern, $a_callback = null )
	{
		if( func_num_args() < 1 )
			return $this->router->delete;
		
		call_user_func_array( array($this->router->delete, 'add'), func_get_args() );
		return $this;
	}
}