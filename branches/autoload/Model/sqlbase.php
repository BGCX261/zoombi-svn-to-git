<?php

/*
 * File: sqlbase.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


/**
 * Data base model
 */
class Zoombi_Model_SqlBase extends Zoombi_Model
{

	/**
	 * @var Zoombi_SqlBuilder
	 */
	public $query;

	public function __construct( Zoombi_Object & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		$this->query = new Zoombi_SqlBuilder();
	}
	
	public function __call( $a_name, $a_args )
	{		
		if( Zoombi::getApplication()->isModeDebug() )
			$this->triggerError( 'Bad model method: '.$a_name );
	}

	private function _doError()
	{
		$e = $this->database->getAdapter()->hasError();
		if($e AND $this->getModule()->isMode(Zoombi_Module::MODE_DEBUG))
		{
			$e = $this->database->getAdapter()->getError();
			$m = array();
			$m[] = 'Database Error : ' . $e->getCode();
			$m[] = 'Message: ' . $e->getMessage();
			$m[] = 'Query string: ' . $e->getQuery();
			$this->triggerError(implode('<br />', $m), E_USER_WARNING);
		}
		return $e;
	}

	private function _querySelect( $a_query, $a_type = Zoombi_Database::RESULT_TYPE_BOTH )
	{
		$this->database->query($a_query);
		return $this->_doError() ? null : $this->database->result($a_type);
	}

	private function _querySelectOne( $a_query, $a_type = Zoombi_Database::RESULT_TYPE_BOTH )
	{
		$this->database->query($a_query);
		$r = $this->_doError() ? null : $this->database->result($a_type);
		if(!$r)
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
		$v = $this->resultValue();
		return $v === null ? null : intval($v); 
	}
	
	public function resultValue()
	{
		$o = $this->result( Zoombi_Database::RESULT_TYPE_NUM );
		return $o && isset($o[0]) && isset($o[0][0]) ? $o[0][0] : null; 
	}

	/**
	 * Query database and return array with numeric keys
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function resultNum( $a_first = false )
	{
		return $this->result(Zoombi_Database::RESULT_TYPE_NUM, $a_first);
	}

	/**
	 * Query database and return associative array
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function resultAssoc( $a_first = false )
	{
		return $this->result(Zoombi_Database::RESULT_TYPE_ASSOC, $a_first);
	}

	/**
	 * Query database and return associative or numeric keys array
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function resultBoth( $a_first = false )
	{
		return $this->result(Zoombi_Database::RESULT_TYPE_BOTH, $a_first);
	}

	/**
	 * Query database and return result rows as array of objects
	 * @param bool $a_first Get first row
	 * @return object|array|null
	 */
	public function resultObject( $a_first = false )
	{
		return $this->result(Zoombi_Database::RESULT_TYPE_OBJECT, $a_first);
	}

	/**
	 * Query database and return result rows
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function result( $a_type = Zoombi_Database::RESULT_TYPE_BOTH, $a_first = false )
	{
		return $this->query($a_first, $a_type);
	}

	/**
	 * Query database and return result rows
	 * @param bool $a_first Get first row
	 * @return array|null
	 */
	public function query( $a_first = false, $a_type = Zoombi_Database::RESULT_TYPE_BOTH )
	{
		if($a_first)
			$this->query->limit(1);

		$q = $this->query->compile();
		$t = $this->query->type();
		$this->query->clean();

		switch($t)
		{
			default:
				trigger_error('Unknown query type');
				break;

			case Zoombi_SqlBuilder::QUERY_TYPE_SELECT:
				return $a_first ? $this->_querySelectOne($q, $a_type) : $this->_querySelect($q, $a_type);

			case Zoombi_SqlBuilder::QUERY_TYPE_UPADATE:
				return $this->_queryUpdate($q);

			case Zoombi_SqlBuilder::QUERY_TYPE_INSERT:
				return $this->_queryInsert($q);

			case Zoombi_SqlBuilder::QUERY_TYPE_DELETE:
				return $this->_queryDelete($q);
		}
	}

}
