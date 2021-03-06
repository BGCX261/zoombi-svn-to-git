<?php

class ZMysqlDatabaseAdapter extends ZDatabaseAdapter
{

	/**
	 * Connection handele
	 * @var resuorce
	 */
	private $m_connection;

	/**
	 * Last query result
	 * @var resource
	 */
	private $m_result;

	/**
	 * Last query error
	 * @var ZDatabaseQueryError
	 */
	private $m_error;

	public function & connect( $a_address, $a_user = '', $a_pass = '' )
	{
		$connection = mysql_connect($a_address, $a_user, $a_pass, true);

		if( $connection )
		{
			$this->setConnection($connection);

			if( function_exists('mysql_set_charset') )
			{
				mysql_set_charset('utf8', $connection);
			}
			else
			{
				mysql_query("SET character_set_client='utf8'", $connection) or trigger_error('MysqlError: ' . mysql_error($connection));
				mysql_query("SET character_set_results='utf8'", $connection) or trigger_error('MysqlError: ' . mysql_error($connection));
				mysql_query("SET collation_connection='utf8_unicode_ci'", $connection) or trigger_error('MysqlError: ' . mysql_error($connection));
			}
		}
		else
		{
			throw new ZDatabaseAdapterException( mysql_error(), mysql_errno() );
		}

		return $this;
	}

	public function & getConnection()
	{
		if( $this->m_connection )
			return $this->m_connection;

		throw new ZDatabaseAdapterException('MySql adapter is not connected');
	}

	public function setConnection( & $a_connection )
	{
		if( $a_connection )
		{
			$this->m_connection = $a_connection;
		}
		return $this;
	}

	public function & selectDatabase( $a_name )
	{
		$this->m_db_name = $a_name;
		mysql_select_db($this->m_db_name, $this->getConnection());
		return $this;
	}

	public function & disconnect()
	{
		mysql_close($this->getConnection());
		return $this;
	}

	public function & free()
	{
		if( $this->m_error && $this->m_error instanceof ZDatabaseQueryError )
			unset($this->m_error);

		$this->m_error = null;
		if( is_resource($this->m_result) )
			mysql_free_result($this->m_result);

		return $this;
	}

	public function & begin()
	{
		$this->_query('BEGIN');
		return $this;
	}

	public function & rollback()
	{
		$this->_query('ROLLBACK');
		return $this;
	}

	public function & commit()
	{
		$this->_query('COMMIT');
		return $this;
	}

	public function & query( $a_query )
	{
		$tr = parent::getParent()->getTransaction();

		$this->free();
		$this->m_query_string = $a_query;
		
		if( $tr )
			$this->begin();
		
		$result = $this->_query($a_query);
		if( $result === false )
		{
			$this->m_error = new ZDatabaseQueryError(
				mysql_errno($this->getConnection()),
				mysql_error($this->getConnection()),
				$a_query
			);
			if( $tr )
				$this->rollback();
			return $this;
		}
		if( $tr )
			$this->commit();

		$this->m_error_no = null;
		$this->m_error_msg = null;

		$this->m_result = & $result;
		return $this;
	}

	private function _query( $a_query )
	{
		return mysql_query($a_query, $this->getConnection());
	}

	public function getInsertId()
	{
		return mysql_insert_id($this->getConnection());
	}

	public function hasError()
	{
		return ($this->m_error !== null);
	}

	public function & getError()
	{
		return $this->m_error;
	}

	public function result()
	{
		if( !is_resource($this->m_result) )
			return $this->m_result;

		$r = array( );
		while( 1 )
		{
			$row = mysql_fetch_assoc($this->m_result);
			if( $row === false )
				break;

			$r[] = $row;
		}
		return $r;
	}
}