<?php

/*
 * File: mysqldatabaseadapter.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class Zoombi_Database_Adapter_Mysql extends Zoombi_Database_Adapter
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

	public function & connect( $a_address, $a_user = '', $a_pass = '' )
	{
		$connection = mysql_connect($a_address, $a_user, $a_pass, true);

		if($connection)
		{
			$this->setConnection($connection);

			if(function_exists('mysql_set_charset'))
			{
				mysql_set_charset('utf8', $connection);
			}
			else
			{
				mysql_query("SET character_set_client='utf8'", $connection) or $this->triggerError('MysqlError: ' . mysql_error($connection));
				mysql_query("SET character_set_results='utf8'", $connection) or $this->triggerError('MysqlError: ' . mysql_error($connection));
				mysql_query("SET collation_connection='utf8_unicode_ci'", $connection) or $this->triggerError('MysqlError: ' . mysql_error($connection));
			}
		}
		else
		{
			throw new Zoombi_Exception_DatabaseAdapter(mysql_error(), mysql_errno());
		}

		return $this;
	}

	public function & getConnection()
	{
		if($this->m_connection)
			return $this->m_connection;

		throw new Zoombi_Exception_DatabaseAdapter('MySql adapter is not connected');
	}

	public function setConnection( & $a_connection )
	{
		if($a_connection)
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
		if($this->m_error instanceof Zoombi_Database_QueryError)
			unset($this->m_error);

		$this->m_error = null;
		if(is_resource($this->m_result))
			mysql_free_result($this->m_result);
		
		$this->m_result = null;

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

		$this->getParent()->setQuery($a_query);

		if($tr)
			$this->begin();

		$result = mysql_query($a_query, $this->getConnection());
		if($result === false)
		{
			$this->m_error = new Zoombi_Database_QueryError(
					mysql_errno($this->getConnection()),
					mysql_error($this->getConnection()),
					$a_query
			);
			if($tr)
				$this->rollback();
			return $this;
		}
		if($tr)
			$this->commit();

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

	public function resultNum()
	{
		return $this->result(Zoombi_Database::RESULT_TYPE_NUM);
	}

	public function resultAssoc()
	{
		return $this->result(Zoombi_Database::RESULT_TYPE_ASSOC);
	}

	public function resultBoth()
	{
		return $this->result(Zoombi_Database::RESULT_TYPE_BOTH);
	}

	public function resultObject()
	{
		return $this->result(Zoombi_Database::RESULT_TYPE_OBJECT);
	}

	public function result( $a_type = null, $a_arg = null )
	{
		if( !is_resource($this->m_result) )
			return $this->m_result;
		
		$rows = mysql_num_rows( $this->m_result );
		
		if( $rows === false )
			return false;

		$r = array();

		do
		{
			$row = false;
			switch($a_type)
			{
				default:
				case Zoombi_Database::RESULT_TYPE_NUM:
					$row = mysql_fetch_array($this->m_result, MYSQL_NUM);
					break;

				case Zoombi_Database::RESULT_TYPE_ASSOC:
					$row = mysql_fetch_array($this->m_result, MYSQL_ASSOC);
					break;

				case Zoombi_Database::RESULT_TYPE_BOTH:
					$row = mysql_fetch_array($this->m_result, MYSQL_BOTH);
					break;

				case Zoombi_Database::RESULT_TYPE_OBJECT:
					$row = func_num_args() > 1 ?
						mysql_fetch_object($this->m_result, func_get_arg(1)) :
						mysql_fetch_object($this->m_result);

					break;
				
				case Zoombi_Database::RESULT_TYPE_RAW:
					return $this->m_result;
			}

			if($row === false)
				break;
			
			//Zoombi::trace( $row, $this->m_query_string );

			$r[] = $row;
		}
		while(1);

		return $r;
	}

}
