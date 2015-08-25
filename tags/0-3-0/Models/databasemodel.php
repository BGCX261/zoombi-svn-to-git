<?php

class ZDatabaseModel extends ZModel
{

	/**
	 * @var ZSqlBuilder
	 */
	private $m_sql;
	/**
	 * Result type
	 * @var int
	 */
	private $m_result_type;

	public function __construct( ZController & $a_controller = null )
	{
		parent::__construct($a_controller);
		$this->m_sql = new ZSqlBuilder();
		$this->m_result_type = ZDatabase::RESULT_TYPE_BOTH;
	}

	public function __call( $a_name, $a_args )
	{
		if( method_exists($this->m_sql, $a_name) )
		{
			$r = call_user_func_array(array( &$this->m_sql, $a_name ), $a_args);
			if( $r instanceof ZSqlBuilder )
				return $this;

			return $r;
		}
	}

	private function _doError()
	{
		if( $this->database->getAdapter()->hasError() )
		{
			$e = $this->database->getAdapter()->getError();
			$m = array( );
			$m[] = 'Database Error : ' . $e->getCode();
			$m[] = 'Message: ' . $e->getMessage();
			$m[] = 'Query: ' . $e->getQuery();
			$m[] = 'Encoding: ' . mysql_client_encoding();
			trigger_error(implode('<br />', $m));
		}
	}

	private function _querySelect( $a_query )
	{
		$this->database->query($a_query);
		$this->_doError();
		return $this->database->result($this->m_result_type);
	}

	private function _querySelectOne( $a_query )
	{
		$this->database->query($a_query);
		$this->_doError();

		$r = $this->database->result($this->m_result_type);
		if( !$r )
			return;

		return isset($r[0]) ? $r[0] : null;
	}

	private function _queryUpdate( $a_query )
	{
		$this->database->query($a_query);
		$this->_doError();
		return $this->database->result();
	}

	private function _queryInsert( $a_query )
	{
		$this->database->query($a_query);
		$this->_doError();
		return $this->database->getInsertId();
	}

	private function _queryDelete( $a_query )
	{
		$this->database->query($a_query);
		$this->_doError();
		return $this->database->result();
	}

	public function resultNum( $a_first )
	{
		$this->result(ZDatabase::RESULT_TYPE_NUM, $a_first);
	}

	public function resultAssoc( $a_first )
	{
		$this->result(ZDatabase::RESULT_TYPE_ASSOC, $a_first);
	}

	public function resultBoth( $a_first )
	{
		$this->result(ZDatabase::RESULT_TYPE_BOTH, $a_first);
	}

	public function resultObject( $a_first )
	{
		$this->result(ZDatabase::RESULT_TYPE_OBJECT, $a_first);
	}

	public function result( $a_type = ZDatabase::RESULT_TYPE_BOTH, $a_first = false )
	{
		$this->m_result_type = $a_type;
		$this->query($a_first);
	}

	public function query( $a_first = false )
	{
		$c = & $this->m_sql;
		$q = $c->compile();
		switch( $c->type() )
		{
			default:
				trigger_error('Unknown type');
				break;

			case ZSqlBuilder::QUERY_TYPE_SELECT:
				return $a_first ? $this->_querySelectOne($q) : $this->_querySelect($q);

			case ZSqlBuilder::QUERY_TYPE_UPADATE:
				return $this->_queryUpdate($q);

			case ZSqlBuilder::QUERY_TYPE_INSERT:
				return $this->_queryInsert($q);

			case ZSqlBuilder::QUERY_TYPE_DELETE:
				return $this->_queryDelete($q);
		}
	}

}
