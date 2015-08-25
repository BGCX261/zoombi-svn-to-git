<?php


class Zoombi_StringRouter
{
	private $_routes = array();
	
	public function add( $a_pattern, $a_callback = null )
	{
		if( func_num_args() == 1 && is_array($a_pattern) )
		{
			foreach( $a_pattern as $k => $v ){
				$this->add( $k, $v );
			}
			return false;
		}
		
		if( !is_callable($a_callback) )
			return false;

		return array_push( $this->_routes, (object)array('pattern'=>$a_pattern,'callback'=>$a_callback) ) - 1;
	}
	
	/**
	 * Check if class has routes with index
	 * @param int $a_index
	 * @return bool Return true if index is exist 
	 */
	function has( $a_index )
	{
		return isset( $this->_routes[ intval($a_index) ] );
	}

	/**
	 * Remove route by index
	 * @param int $a_index
	 * @return bool Return true if route is removed 
	 */
	public function remove( $a_index )
	{
		if( $this->has($a_index) )
		{
			unset($this->_routes[intval($a_index)]);
			return true;
		}
		return false;
	}
	
	/**
	 * Create RegExp from pattern
	 * @param string $a_pattern
	 * @return string
	 */
	private function _makeregexp( $a_pattern )
	{
		$replace = array(
			':num' => '[0-9]{1,8}',
			':char' => '[a-z]{1}',
			':word' => '[0-z]{2,}',
			':segment' => '[^/]*',
			':any' => '.*',
			'/' => '\/',
		);
		return '/^\/?' . str_ireplace(array_keys($replace), array_values($replace), $a_pattern) . '.*$/i';
	}	
	
	function route( $a_route, $callback )
	{
		foreach( $this->_routes as $r )
		{
			$match = array();
			if( preg_match($this->_makeregexp($r->pattern), $a_route, $match ) )
			{
				array_shift( $match );
				return call_user_func_array($r->callback, $match);
			}
		}
		
		return false;
	}	
}