<?php

/**
 * Data base model
 *
 * @method ZSqlBuilder|ZDatabaseModel select
 * @method ZSqlBuilder|ZDatabaseModel update
 * @method ZSqlBuilder|ZDatabaseModel delete
 * @method ZSqlBuilder|ZDatabaseModel insert
 * @method ZSqlBuilder|ZDatabaseModel where
 * @method ZSqlBuilder|ZDatabaseModel order
 * @method ZSqlBuilder|ZDatabaseModel into
 * @method ZSqlBuilder|ZDatabaseModel having
 * @method ZSqlBuilder|ZDatabaseModel offset
 * @method ZSqlBuilder|ZDatabaseModel group
 * @method ZSqlBuilder|ZDatabaseModel groupby
 * @method string compile
 * @method ZSqlBuilder|ZDatabaseModel set
 */
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

	public function __construct( ZObject & $a_parent = null )
	{
		parent::__construct($a_parent);
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
		$e = $this->database->getAdapter()->hasError();
		if( $e AND $this->getModule()->isMode(ZModule::MODE_DEBUG) )
		{
			$e = $this->database->getAdapter()->getError();
			$m = array( );
			$m[] = 'Database Error : ' . $e->getCode();
			$m[] = 'Message: ' . $e->getMessage();
			$m[] = 'Query string: ' . $e->getQuery();
			$this->triggerError(implode('<br />'), E_USER_WARNING);
			
		}
		return $e;
	}

	private function _querySelect( $a_query )
	{
		$this->database->query($a_query);
		return $this->_doError() ? null : $this->database->result($this->m_result_type);
	}

	private function _querySelectOne( $a_query )
	{
		$this->database->query($a_query);
		$r = $this->_doError() ? null : $this->database->result($this->m_result_type);
		if( !$r )
			return;

		return isset($r[0]) ? $r[0] : null;
	}

	private function _queryUpdate( $a_query )
	{
		$this->database->query($a_query);
		return $this->_doError() ? null : $this->database->result();
	}

	private function _queryInsert( $a_query )
	{
		$this->database->query($a_query);
		return $this->_doError() ? null : $this->database->getInsertId();
	}

	private function _queryDelete( $a_query )
	{
		$this->database->query($a_query);
		return $this->_doError() ? null : $this->database->result();
	}

	/**
	 * Query database and return result as integer
	 * @return int
	 */
	public function resultInt()
	{
		$this->m_result_type = ZDatabase::RESULT_TYPE_NUM;
		$o = $this->query(true);
		return isset( $o[0] ) ? intval($o[0]) : null;
	}

	/**
	 * Query database and return array with numeric keys
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function resultNum( $a_first = false )
	{
		return $this->result(ZDatabase::RESULT_TYPE_NUM, $a_first);
	}

	/**
	 * Query database and return associative array
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function resultAssoc( $a_first = false )
	{
		return $this->result(ZDatabase::RESULT_TYPE_ASSOC, $a_first);
	}

	/**
	 * Query database and return associative or numeric keys array
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function resultBoth( $a_first = false )
	{
		return $this->result(ZDatabase::RESULT_TYPE_BOTH, $a_first);
	}

	/**
	 * Query database and return result rows as array of objects
	 * @param bool $a_first Get first row
	 * @return object|array|null
	 */
	public function resultObject( $a_first = false )
	{
		return $this->result(ZDatabase::RESULT_TYPE_OBJECT, $a_first);
	}

	/**
	 * Query database and return result rows
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function result( $a_type = ZDatabase::RESULT_TYPE_BOTH, $a_first = false )
	{
		$this->m_result_type = $a_type;
		return $this->query($a_first);
	}

	/**
	 * Query database and return result rows
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function query( $a_first = false )
	{
		$c = & $this->m_sql;
		if( $a_first )
			$c->limit( 1 );
		
		$q = $c->compile();
		$t = $c->type();
		$c->clean();

		switch( $t )
		{
			default:
				trigger_error('Unknown query type');
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
