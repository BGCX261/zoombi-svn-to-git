<?php

class ZSqlBuilder
{
	const QUERY_TYPE_NONE = 0;
	const QUERY_TYPE_SELECT = 1;
	const QUERY_TYPE_INSERT = 2;
	const QUERY_TYPE_UPADATE = 3;
	const QUERY_TYPE_DELETE = 4;

	private $m_fields;
	private $m_where;
	private $m_where_or;
	private $m_having;
	private $m_having_or;
	private $m_groupby;
	private $m_orderby;
	private $m_result;
	private $m_limit;
	private $m_offset;
	private $m_query_type;
	private $m_table;
	private $m_table_prefix;
	private $m_table_suffix;

	private $m_whr;

	public function __construct()
	{
		$this->clean();
	}

	public function  __toString()
	{
		return $this->compile();
	}

	public function type()
	{
		return $this->m_query_type;
	}

	public function & clean()
	{
		$this->m_result = null;
		$this->m_fields = array( );

		$this->m_where = array( );
		$this->m_where_or = array( );

		$this->m_having = array( );
		$this->m_having_or = array( );

		$this->m_orderby = array( );
		$this->m_groupby = array( );

		$this->m_offset = null;
		$this->m_limit = null;

		$this->m_table = null;

		$this->m_query_type = self::QUERY_TYPE_NONE;
		return $this;
	}

	public function & tablePrefix( $a_prefix = null )
	{
		return func_num_args() == 0 ? $this->getTablePrefix() : $this->setTablePrefix($a_prefix);
	}

	public function & tableSuffix( $a_suffix = null )
	{
		return func_num_args() == 0 ? $this->getTableSuffix() : $this->setTableSuffix($a_suffix);
	}

	public function getTablePrefix()
	{
		return $this->m_table_prefix;
	}

	public function & setTablePrefix( $a_prefix )
	{
		$this->m_table_prefix = $a_prefix;
		return $this;
	}

	public function getTableSuffix()
	{
		return $this->m_table_suffix;
	}

	public function & setTableSuffix( $a_suffix )
	{
		$this->m_table_suffix = $a_suffix;
		return $this;
	}

	public function & select( $a_fields = array( ), $a_from = null )
	{
		$this->clean();
		$this->m_query_type = self::QUERY_TYPE_SELECT;

		if( $a_from !== null )
			$this->from($a_from);

		if( is_string($a_fields) )
		{
			$this->m_fields[] = trim($a_fields);
			return $this;
		}

		if( !is_array($a_fields) )
			return $this;

		foreach( $a_fields as $val )
		{
			$val = trim($val);

			if( empty($val) )
				continue;

			$this->m_fields[] = $val;
		}

		return $this;
	}

	public function & from( $a_from )
	{
		$this->m_table = trim((string)$a_from);
		return $this;
	}

	public function getTable()
	{
		return $this->m_table;
	}

	public function & setTable( $a_table )
	{
		$this->m_table = trim((string)$a_table);
		return $this;
	}

	public function & into( $a_from )
	{
		$this->m_table = trim((string)$a_from);
		return $this;
	}

	private function _where( $a_exp, $a_to )
	{
		switch( $a_to )
		{
			case 'AND':
				$this->m_where[] = $a_exp;
				break;

			case 'OR':
				$this->m_where_or[] = $a_exp;
				break;
		}
	}

	public function & where( $arg1, $arg2 = null, $arg3 = null )
	{
		switch( func_num_args( ) )
		{
			case 1:
				switch( gettype($a_key) )
				{
					case 'string':
						$e = $this->_get_expression($arg1);
						if( $e )
							$this->_where(  $this->_make_expression($e), 'AND');
						break;

					case 'array':
						foreach( $a_key as $v )
						{
							$e = $this->_get_expression($v);
							if( $e )
								$this->_where( $this->_make_expression($e), 'AND');
							else
								$this->where($v);
						}
						break;
				}
				break;

			case 2:				
				$this->_where( $this->_make_expression($arg1,$arg2), 'AND');
				break;

			case 3:
				$this->_where( $this->_make_expression($arg1,$arg2,$arg3), 'AND');
				break;
		}
		return $this;
	}

	public function & or_where( $a_key, $a_operator = null, $a_value = null )
	{
		switch( func_num_args( ) )
		{
			case 1:
				switch( gettype($a_key) )
				{
					case 'string':
						$e = $this->_get_expression(func_get_arg(0));
						if( $e )
							$this->_where(  $this->_make_expression($e), 'OR');
						break;

					case 'array':
						foreach( $a_key as $v )
						{
							$e = $this->_get_expression($v);
							if( $e )
								$this->_where( $this->_make_expression($e), 'OR');
							else
								$this->where($v);
						}
						break;
				}
				break;

			case 2:
					$this->_where( $this->_make_expression(func_get_arg(0),func_get_arg(1)), 'OR');
				break;

			case 3:
					$this->_where( $this->_make_expression(func_get_args()), 'OR');
				break;
		}
		return $this;
	}

	private function _having( $a_exp, $a_to )
	{
		switch( ucwords($a_to) )
		{
			case 'AND':
				$this->m_having[] = $a_exp;
				break;

			case 'OR':
				$this->m_having_or[] = $a_exp;
				break;
		}
	}

	public function & having( $a_key, $a_operator = null, $a_value = null )
	{
		switch( func_num_args( ) )
		{
			case 1:
				switch( gettype($a_key) )
				{
					case 'string':
						$e = $this->_get_expression(func_get_arg(0));
						if( $e )
							$this->_having(  $this->_make_expression($e), 'AND');
						break;

					case 'array':
						foreach( $a_key as $v )
						{
							$e = $this->_get_expression($v);
							if( $e )
								$this->_having( $this->_make_expression($e), 'AND');
							else
								$this->having($v);
						}
						break;
				}
				break;

			case 2:
					$this->_having( $this->_make_expression(func_get_arg(0),func_get_arg(1)), 'AND');
				break;

			case 3:
					$this->_having( $this->_make_expression(func_get_args()), 'AND');
				break;
		}
		return $this;
	}

	public function & or_having( $a_key, $a_operator = null, $a_value = null )
	{
		switch( func_num_args( ) )
		{
			case 1:
				switch( gettype($a_key) )
				{
					case 'string':
						$e = $this->_get_expression(func_get_arg(0));
						if( $e )
							$this->_having(  $this->_make_expression($e), 'OR');
						break;

					case 'array':
						foreach( $a_key as $v )
						{
							$e = $this->_get_expression($v);
							if( $e )
								$this->_having( $this->_make_expression($e), 'OR');
							else
								$this->or_having($v);
						}
						break;
				}
				break;

			case 2:
					$this->_having( $this->_make_expression( func_get_arg(0),func_get_arg(1) ), 'OR');
				break;

			case 3:
					$this->_having( $this->_make_expression(func_get_args()), 'OR');
				break;
		}
		return $this;
	}

	public function & limit( $a_limit, $a_offset = null )
	{
		$e = explode(',', $a_limit);
		if( count($e) > 1 )
			return $this->limit($e[0], $e[1]);

		$l = trim((string)$a_limit);
		if( empty($l) )
			return $this;

		$this->m_limit = $l;
		return $this->offset($a_offset);
	}

	public function & offset( $a_offset )
	{
		$o = trim((string)$a_offset);
		if( empty($o) )
			return $this;

		$this->m_offset = $o;
		return $this;
	}

	public function & group( $a_key, $a_order = 'ASC' )
	{
		return $this->groupby($a_key, $a_order);
	}

	public function & groupby( $a_key, $a_order = 'ASC' )
	{
		if( is_array($a_key) )
		{
			if( isset($a_key[0]) )
				$this->groupby($a_key[0], isset($a_key[1]) ? $a_key[1] : 'ASC');

			return $this;
		}

		$spl = explode(' ', $a_key);
		if( count($spl) > 1 )
			return $this->groupby($spl[0], $spl[1]);

		$key = trim((string)$a_key);
		if( empty($key) )
			return $this;

		$order = ucwords(trim($a_order));
		if( $order != 'ASC' && $order != 'DESC' )
			$order = 'ASC';

		$this->m_groupby[] = '`' . $key . '` ' . $order;
		return $this;
	}
	
	public function & set($a_key,$a_value = null)
	{
		//echo 'Key: ' .  $a_key . '<br />';
		switch( func_num_args( ) )
		{
			case 1:
				switch( gettype(func_get_arg(0)) )
				{
					case 'string':
						$e = $this->_get_expression(func_get_arg(0));				
						if( count($e) == 3 )
							$this->m_fields[$e[0]] = $e[2];
						break;

					case 'object':
						return $this->set( get_object_vars($a_key) );

					case 'array':
						foreach( $a_key as $k => $v )
						{
							$e = $this->_get_expression($v);
							if( count($e) == 3 )
								$this->set($e[0],$e[2]);
							else
								$this->set($k,$v);
						}
						break;
				}
				break;
	
			case 2:
				$this->m_fields[func_get_arg(0)] = func_get_arg(1);
				break;

			case 3:
				$this->m_fields[func_get_arg(0)] = func_get_arg(2);
				break;
		}
		return $this;
	}

	public function & get( $a_key, $a_value = null )
	{
		return $this->set($a_key, $a_value);
	}

	public function & values( $a_key, $a_value = null )
	{
		return $this->set($a_key, $a_value);
	}

	public function & update( $a_table )
	{
		$this->clean();
		$this->m_table = $a_table;
		$this->m_query_type = self::QUERY_TYPE_UPADATE;
		return $this;
	}

	public function & insert( $a_table )
	{
		$this->clean();
		$this->m_table = $a_table;
		$this->m_query_type = self::QUERY_TYPE_INSERT;
		return $this;
	}

	public function & delete( $a_table )
	{
		$this->clean();
		$this->m_table = $a_table;
		$this->m_query_type = self::QUERY_TYPE_DELETE;
		return $this;
	}

	public function & order( $a_key, $a_order = 'ASC' )
	{
		return $this->orderby($a_key, $a_order);
	}

	public function & orderby( $a_key, $a_order = 'ASC' )
	{
		if( is_array($a_key) )
		{
			if( isset($a_key[0]) )
				$this->orderby($a_key[0], isset($a_key[1]) ? $a_key[1] : 'ASC');

			return $this;
		}

		$spl = explode(' ', $a_key);
		if( count($spl) > 1 )
			return $this->orderby((string)$spl[0], (string)$spl[1]);

		$key = trim((string)$a_key);
		if( empty($key) )
			return $this;

		$order = strtoupper(trim((string)$a_order));
		if( $order != 'ASC' && $order != 'DESC' )
			$order = 'ASC';

		$this->m_orderby[] = '`' . $key . '` ' . $order;
		return $this;
	}

	public function compile( $a_clean = true )
	{
		$out = '';
		switch( $this->m_query_type )
		{
			case self::QUERY_TYPE_SELECT:
				$query = 'SELECT ' . (count($this->m_fields) ? implode(', ', $this->m_fields) : '*') . ' FROM ' . $this->m_table_prefix . $this->m_table . $this->m_table_suffix;
				if( count($this->m_where) )
				{
					$where = implode(' AND ', $this->m_where);
					if( strlen($where) )
						$query .= ' WHERE ' . $where;
				}

				if( count($this->m_where_or) )
				{
					if( !count($this->m_where) )
						$query .= ' WHERE ' . implode(' OR ', $this->m_where_or);
					else
						$query .= ' OR ' . implode(' OR ', $this->m_where_or);
				}

				if( count($this->m_groupby) )
				{
					$query .= ' GROUP BY ' . implode(', ', $this->m_groupby);

					if( count($this->m_having) )
					{
						$having = implode(' AND ', $this->m_having);

						if( count($this->m_having_or) )
							$having .= ' OR ' . implode(' OR ', $this->m_having_or);

						if( strlen($having) )
							$query .= ' HAVING ' . $having;
					}
				}

				if( count($this->m_orderby) )
				{
					$query .= ' ORDER BY ' . implode(', ', $this->m_orderby);
				}

				if( $this->m_limit !== null )
					$query .= ' LIMIT ' . $this->m_limit;

				if( $this->m_offset !== null )
					$query .= ' OFFSET ' . $this->m_offset;


				$out = $query . ';';
				break;

			case self::QUERY_TYPE_UPADATE:
				$query = 'UPDATE `' . $this->m_table_prefix . $this->m_table . $this->m_table_suffix . '`';
				if( $this->m_fields )
				{
					$fields = array();
					foreach( $this->m_fields as $k => $v )
						$fields[] = $this->_make_expression( $k,$v );

					$query .= ' SET ' . implode(', ', $fields);

					if( count($this->m_where) )
					{
						$where = implode(' AND ', $this->m_where);

						if( count($this->m_where_or) )
							$where .= ' OR ' . implode(' OR ', $this->m_where_or);

						if( strlen($where) )
							$query .= ' WHERE ' . $where;
					}

					if( $this->m_limit !== null )
						$query .= ' LIMIT ' . $this->m_limit;
				}

				$out = $query . ';';
				break;

			case self::QUERY_TYPE_INSERT:
				$query = 'INSERT INTO `' . $this->m_table_prefix . $this->m_table . $this->m_table_suffix . '`';
				if( $this->m_fields )
				{
					$keys = array();
					$vals = array();

					foreach( $this->m_fields as $k => $v )
					{
						$keys[] = $this->_make_key($k);
						$vals[] = $this->_make_value($v);
					}
					if( count($keys) AND count($vals) )
						$query .= ' (' . implode(', ', $keys) . ') VALUES(' . implode(', ', $vals) . ') ';
				}
				$out = $query . ';';
				break;

			case self::QUERY_TYPE_DELETE:
				$query = 'DELETE FROM ' . $this->m_table_prefix . $this->m_table . $this->m_table_suffix;
				if( count($this->m_where) )
				{
					$where = implode(' AND ', $this->m_where);

					if( count($this->m_where_or) )
						$where .= ' OR ' . implode(' OR ', $this->m_where_or);

					if( strlen($where) )
						$query .= ' WHERE ' . $where;
				}

				if( $this->m_limit !== null )
					$query .= ' LIMIT ' . $this->m_limit;

				$out = $query . ';';
				break;
		}

		$this->m_last_query = $out;
		return $out;
	}

	private $m_expr_pattern = '/`?(\w+(?:(?:\.\w+)+)?)`?\s*(!=|>=|<=|==|=|>|<)\s*(.*)/i';
	private $m_expr_operator = '/`?(\w+(?:(?:\.\w+)+)?)`?\s+(OR|AND|XOR|MOD|LIKE|RLIKE|REGEXP|NOT|IS|IN|AS|BYNARY|CASE|IS NOT|INTERVAL)\s./i';
	private $m_expr_function = '/^\s*[A-Z]{2,8}+\(.*\)/';

	private function _is_expression_function( $a_test )
	{
		return preg_match($this->m_expr_function, (string)$a_test) === 1;
	}

	private function _has_expression( $a_test )
	{
		return preg_match($this->m_expr_pattern, (string)$a_test) === 1;
	}

	private function _get_expression( $a_exp )
	{
		
		$matches = array( );
		$c = preg_match($this->m_expr_pattern, (string)$a_exp, $matches);
		if( $c )
			array_shift($matches);
		else
		{
			$c = preg_match($this->m_expr_operator, (string)$a_exp, $matches);
			if( $c )
				array_shift($matches);
		}

		return $matches;
	}

	private function _get_expression_key( $a_exp )
	{
		$e = $this->_get_expression($a_exp);
		if( $e )
			return $e[0];
	}

	private function _get_expression_operator( $a_exp )
	{
		$e = $this->_get_expression($a_exp);
		if( $e )
			return $e[1];
	}

	private function _get_expression_values( $a_exp )
	{
		$e = $this->_get_expression($a_exp);
		if( $e )
			return $e[2];
	}	
	
	private function _make_value( $a_value )
	{
		switch( gettype($a_value) )
		{
			case null:
				return 'NULL';
	
			case 'float':
			case 'double':
			case 'integer':
				return strval($a_value);

			case 'string':
				switch( strtolower($a_value) )
				{
					default:
						if( is_numeric($a_value) )
							$val = strval($a_value);
						else
						{
							if( $this->_is_expression_function($a_value) )
								return $a_value;
							else
							{
								$s = substr($a_value,0,1);
								$e = substr($a_value,-1,1);

								if( $s == "\"" && $e == "\"" )
									$a_value = substr($a_value,1,strlen($a_value)-2);
								
								if ($s == "'" && $e == "'")
									$a_value = substr($a_value,1,strlen($a_value)-2);

								if ($s == "`" && $e == "`")
									$a_value = substr($a_value,1,strlen($a_value)-2);

								return sprintf('"%s"', trim($a_value));
							}
						}
						break;

					case 'true':
					case 'false':
					case 'null':
					case 'not null';
						return strtoupper($a_value);
				}
				return $a_value;
		}
		return 'NULL';
	}

	private function _make_key( $a_key )
	{
		$k = strval($a_key);
		$s = substr($k,0,1);
		$e = substr($k,-1,1);

		if( $s == "`" && $e == "`" )
			$k = substr($k,1,strlen($k)-2);

		return sprintf('`%s`', trim($k));
	}

	private function _make_expression( $arg1, $arg2 = null, $arg3 = null )
	{
		switch( func_num_args( ) )
		{
			case 1:
				return $this->_make_expression($arg1[0],$arg1[1],$arg1[2]);

			case 2:
				return $this->_make_expression($arg1,'=',$arg2);

			case 3:
				return $this->_make_key($arg1) . $arg2 . $this->_make_value($arg3);
		}
	}

	private function _fix_expression( $a_exp )
	{
		return $this->_make_expression( $this->_get_expression($a_exp) );
	}
}
