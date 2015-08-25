<?php

class ZSqlBuilder
{
	const QUERY_TYPE_NONE		= 0;
	const QUERY_TYPE_SELECT		= 1;
	const QUERY_TYPE_INSERT		= 2;
	const QUERY_TYPE_UPADATE	= 3;
	const QUERY_TYPE_DELETE		= 4;

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

	public function __construct()
	{
		$this->clean();
	}

	public function type()
	{
		return $this->m_query_type;
	}

	public function & clean()
	{
		$this->m_result = null;
		$this->m_fields = array();

		$this->m_where = array();
		$this->m_where_or = array();

		$this->m_having = array();
		$this->m_having_or = array();

		$this->m_orderby = array();
		$this->m_groupby = array();
		
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

	public function & select( $a_fields = array(), $a_from = null )
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

			if ( empty($val) )
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

	public function & where( $a_key, $a_operator = null , $a_value = null )
	{
		switch( func_num_args() )
		{
			default:
				break;

			case 1:
				switch( gettype($a_key) )
				{
					case 'string':
						foreach( explode(',',$a_key) as $v )
						{
							$e = $this->_fix_expression($v);
							if($e)
								$this->_where($e,'AND');
						}
						break;

					case 'array':
						foreach($a_key as $v)
							$this->where($v);
						break;
				}
				break;

			case 3:
				$e = $this->_make_expression(func_get_args());
				if($e)
					$this->_where($e,'AND');
				break;
		}
		return $this;
	}

	public function & or_where( $a_key, $a_operator = null , $a_value = null )
	{
		switch( func_num_args() )
		{
			default:
				break;

			case 1:
				switch( gettype($a_key) )
				{
					case 'string':
						foreach( explode(',',$a_key) as $v )
						{
							$e = $this->_fix_expression($v);
							if($e)
								$this->_where($e,'OR');
						}
						break;

					case 'array':
						foreach($a_key as $v)
							$this->or_where($v);
						break;
				}
				break;

			case 3:
				$e = $this->_make_expression(func_get_args());
				if($e)
					$this->_where($e,'OR');
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

	public function & having( $a_key, $a_operator = null , $a_value = null )
	{
		switch( func_num_args() )
		{
			default:
				return $this;

			case 1:
				switch( gettype($a_key) )
				{
					case 'string':
						foreach( explode(',',$a_key) as $v )
						{
							$e = $this->_fix_expression($v);
							if($e)
								$this->_having($e,'AND');
						}
						break;

					case 'array':
						foreach($a_key as $v)
							$this->having($v);
						break;
				}
				return $this;

			case 3:
				$e = $this->_make_expression(func_get_args());
				if($e)
					$this->_having($e,'AND');
				break;
		}
		return $this;
	}

	public function & or_having( $a_key, $a_operator = null , $a_value = null )
	{
		switch( func_num_args() )
		{
			default:
				break;

			case 1:
				switch( gettype($a_key) )
				{
					case 'string':
						foreach( explode(',',$a_key) as $v )
						{
							$e = $this->_fix_expression($v);
							if($e)
								$this->_having($e,'OR');
						}
						break;

					case 'array':
						foreach($a_key as $v)
							$this->or_having($v);
						break;
				}
				return $this;

			case 3:
				$e = $this->_make_expression(func_get_args());
				if($e)
					$this->_having($e,'OR');
				break;
		}
		return $this;
	}

	public function & limit( $a_limit, $a_offset = null )
	{
		$e = explode(',',$a_limit);
		if( count($e) > 1 )
			return $this->limit($e[0],$e[1]);
		
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
		return $this->groupby( $a_key, $a_order );
	}

	public function & groupby( $a_key, $a_order = 'ASC' )
	{
		if( is_array($a_key) )
		{
			if( isset($a_key[0]) )
				$this->groupby( $a_key[0], isset($a_key[1] ) ? $a_key[1] : 'ASC' );

			return $this;
		}

		$spl = explode(' ', $a_key);
		if( count($spl) > 1 )
			return $this->groupby($spl[0],$spl[1]);

		$key = trim( (string)$a_key );
		if( empty($key) )
			return $this;

		$order = ucwords( trim($a_order) );
		if( $order != 'ASC' && $order != 'DESC' )
			$order = 'ASC';

		$this->m_groupby[] = '`'.$key . '` ' . $order;
		return $this;
	}
	
	public function & set( $a_key, $a_value = null )
	{
		if( func_num_args() == 3 )
		{
			$k = func_get_arg(0);
			$v = func_get_arg(1);
			$this->set($k,$v);
			return $this;
		}

		switch( gettype($a_key) )
		{
			case 'string':
				$this->m_fields[$a_key] = $a_value;
				break;
				
			case 'array':
				foreach( $a_key as $k => $v )
					$this->set($k,$v);
				break;

			case 'object':
				foreach( get_object_vars($a_key) as $k => $v )
					$this->set($k,$v);
				break;
		}
		return $this;
	}

	public function & get( $a_key, $a_value = null )
	{
		return $this->set( $a_key, $a_value );
	}

	public function & values( $a_key, $a_value = null )
	{
		return $this->set($a_key, $a_value);
	}

	public function & update($a_table)
	{
		$this->clean();
		$this->m_table = $a_table;
		$this->m_query_type = self::QUERY_TYPE_UPADATE;
		return $this;
	}

	public function & insert($a_table)
	{
		$this->clean();
		$this->m_table = $a_table;
		$this->m_query_type = self::QUERY_TYPE_INSERT;
		return $this;
	}

	public function & delete($a_table)
	{
		$this->clean();
		$this->m_table = $a_table;
		$this->m_query_type = self::QUERY_TYPE_DELETE;
		return $this;
	}

	public function & order( $a_key, $a_order = 'ASC' )
	{
		return $this->orderby( $a_key, $a_order );
	}

	public function & orderby( $a_key, $a_order = 'ASC' )
	{
		if( is_array($a_key) )
		{
			if( isset($a_key[0]) )
				$this->orderby( $a_key[0], isset($a_key[1] ) ? $a_key[1] : 'ASC' );

			return $this;
		}

		$spl = explode(' ', $a_key);
		if( count($spl) > 1 )
			return $this->orderby((string)$spl[0],(string)$spl[1]);

		$key = trim( (string)$a_key );
		if( empty($key) )
			return $this;

		$order = strtoupper( trim( (string)$a_order ) );
		if( $order != 'ASC' && $order != 'DESC' )
			$order = 'ASC';

		$this->m_orderby[] = '`'.$key . '` ' . $order;
		return $this;
	}

	public function compile( $a_clean = true )
	{
		$out = '';
		switch( $this->m_query_type )
		{
			case self::QUERY_TYPE_SELECT:
				$query = 'SELECT ' . (count($this->m_fields) ? implode(', ',  $this->m_fields) : '*') . ' FROM ' . $this->m_table_prefix . $this->m_table . $this->m_table_suffix;
				if( count($this->m_where) )
				{
					$where = implode( ' AND ', $this->m_where );
					if( strlen($where) )
						$query .= ' WHERE ' . $where;
				}

				if( count($this->m_where_or) )
				{
					if( !count($this->m_where) )
						$query .= ' WHERE ' . implode( ' OR ', $this->m_where_or );
					else
						$query .= ' OR ' . implode( ' OR ', $this->m_where_or );
				}
				
				if( count($this->m_groupby) )
				{
					$query .= ' GROUP BY ' .  implode( ', ', $this->m_groupby );
				
					if( count($this->m_having) )
					{
						$having = implode( ' AND ', $this->m_having );

						if( count($this->m_having_or) )
							$having .= ' OR ' . implode( ' OR ', $this->m_having_or );

						if( strlen($having) )
							$query .= ' HAVING ' . $having;
					}
				}

				if( count($this->m_orderby) )
				{
					$query .= ' ORDER BY ' .  implode( ', ', $this->m_orderby );
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
					$set = array();
					foreach( $this->m_fields as $k=>$v )
					{
						$var = "`{$k}`=";

						if( is_numeric($v) )
							$var .= $v;
						else if( is_null($v) )
							$var .= 'NULL';
						else if( is_string($v) )
							$var .= "'" . addslashes($v) ."'";
						else
							continue;

						$set[] = $var;
					}


					if( !empty($set) )
					{
						$query .= ' SET ' . implode(', ', $set);
					}

					if( count($this->m_where) )
					{
						$where = implode( ' AND ', $this->m_where );

						if( count($this->m_where_or) )
							$where .= ' OR ' . implode( ' OR ', $this->m_where_or );

						if( strlen($where) )
							$query .= ' WHERE ' . $where;
					}

					if( $this->m_limit !== null )
						$query .= ' LIMIT ' . $this->m_limit;
				}

				$out = $query . ';';
				break;

			case self::QUERY_TYPE_INSERT:
				$query = 'INSERT INTO `' . $this->m_table_prefix . $this->m_table . $this->m_table_suffix.'`';
				if( $this->m_fields )
				{
					$keys = array();
					$vals = array();

					foreach( $this->m_fields as $k=>$v )
					{
						$keys[] = "`{$k}`";

						if( is_numeric($v) )
							$vals[] = $v;
						else if( is_null($v) )
								$vals[] = 'NULL';
						else if( is_string($v) )
							$vals[] = "'" . addslashes($v) ."'";
						else
							continue;
					}

					if( count($keys) )
					{
						$query .= ' (' . implode(', ',$keys) . ')';
					}

					if( count($vals) )
					{
						$query .= ' VALUES( ' . implode(', ',$vals) . ') ';
					}
				}
				$out = $query . ';';
				break;

			case self::QUERY_TYPE_DELETE:
				$query = 'DELETE FROM ' . $this->m_table_prefix . $this->m_table . $this->m_table_suffix;
				if( count($this->m_where) )
				{
					$where = implode( ' AND ', $this->m_where );

					if( count($this->m_where_or) )
						$where .= ' OR ' . implode( ' OR ', $this->m_where_or );

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

	private function _get_expression( $a_exp )
	{
		$pattern = '/`?(\w+)`?\s*(!=|>=|<=|==|=|>|<|LIKE|NOT|IS NOT)\s*(?:\'|\")?(.*)(?:\'|\")?/i';
		$matches = array();
		$c = preg_match( $pattern, (string)$a_exp, $matches );
		if( count($matches) )
			array_shift($matches);
		
        return $matches;
	}

	private function _make_expression( $a_exp )
	{
		if( !is_array($a_exp) || count($a_exp) < 3 )
			return;

		$res[] = "`{$a_exp[0]}`";
		$res[] = $a_exp[1];

		$val =& $a_exp[2];

		switch( strtolower($val) )
		{
			default:
				if(is_numeric($val))
					$res[] = $val;
				else
					$res[] = "'{$val}'";
				break;

			case 'true':
			case 'false':
			case 'null':
			case 'not null';
				$res[] = $val;
				break;
		}
		return implode(' ', $res);
	}

	private function _fix_expression( $a_exp )
	{
        $exp = $this->_get_expression($a_exp);
		return $this->_make_expression( $exp );
	}

	public function is_expression( $a_exp )
	{
		return count($this->_get_expression($a_exp)) == 3;
	}
}
