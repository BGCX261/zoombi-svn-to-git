<?php

/*
 * File: mysqldatabaseadapter.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class ZMysqliDatabaseAdapter extends ZDatabaseAdapter
{

	/**
	 * Connection handele
	 * @var mysqli
	 */
	private $m_connection;

	/**
	 * Last query result
	 * @var mysqli_result
	 */
	private $m_result;

	public function & connect( $a_address, $a_user = '', $a_pass = '' )
	{
		$connection = new mysqli($a_address, $a_user, $a_pass);

		if($connection && !mysqli_connect_errno())
		{
			$this->setConnection($connection);
			$this->m_connection->set_charset('utf8');
		}
		else
			throw new ZDatabaseAdapterException(mysqli_connect_error(), mysqli_connect_errno());

		return $this;
	}

	/**
	 * Get Mysqli object
	 * @return mysqli
	 */
	public function & getConnection()
	{
		if($this->m_connection && $this->m_connection instanceof mysqli)
			return $this->m_connection;

		throw new ZDatabaseAdapterException('MySqli adapter is not connected');
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
		$this->getConnection()->select_db($this->m_db_name);
		return $this;
	}

	public function & disconnect()
	{
		$this->getConnection()->close();
		return $this;
	}

	public function & free()
	{
		if($this->m_error instanceof ZDatabaseQueryError)
			unset($this->m_error);

		$this->m_error = null;
		if($this->m_result instanceof mysqli_result)
			$this->m_result->free();

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

		$c = $this->getConnection();

		$result = $c->query($a_query, MYSQLI_USE_RESULT);
		if($result === false)
		{
			$this->m_error = new ZDatabaseQueryError(
					$c->errno,
					$c->error,
					$a_query
			);

			if($tr)
				$this->rollback();

			return $this;
		}

		if($tr)
			$this->commit();

		$this->m_result = $result;
		return $this;
	}

	private function _query( $a_query )
	{
		return $this->getConnection()->query($a_query);
	}

	public function getInsertId()
	{
		return $this->getConnection()->insert_id;
	}

	public function resultNum()
	{
		return $this->result(ZDatabase::RESULT_TYPE_NUM);
	}

	public function resultAssoc()
	{
		return $this->result(ZDatabase::RESULT_TYPE_ASSOC);
	}

	public function resultBoth()
	{
		return $this->result(ZDatabase::RESULT_TYPE_BOTH);
	}

	public function resultObject()
	{
		return $this->result(ZDatabase::RESULT_TYPE_OBJECT);
	}

	public function result( $a_type = null )
	{
		if(!$this->m_result OR !($this->m_result instanceof mysqli_result))
			return $this->m_result;

		$r = array();

		do
		{
			$row = false;
			switch($a_type)
			{
				default:
				case ZDatabase::RESULT_TYPE_NUM:
					$row = $this->m_result->fetch_array(MYSQLI_NUM);
					break;

				case ZDatabase::RESULT_TYPE_ASSOC:
					$row = $this->m_result->fetch_array(MYSQLI_ASSOC);
					break;

				case ZDatabase::RESULT_TYPE_BOTH:
					$row = $this->m_result->fetch_array(MYSQLI_BOTH);
					break;

				case ZDatabase::RESULT_TYPE_OBJECT:
					$row = $this->m_result->fetch_object();
					break;

				case ZDatabase::RESULT_TYPE_RAW:
					return $this->m_result;
			}
			if(!$row)
				break;

			$r[] = $row;
		}
		while(1);

		return $r;
	}

}
