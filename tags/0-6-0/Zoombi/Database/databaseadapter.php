<?php

/*
 * File: database.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

abstract class ZDatabaseAdapter extends ZObject
{

	/**
	 * 
	 * @var ZDatabaseQueryError 
	 */
	protected $m_error;

	abstract function & connect( $a_address, $a_login = '', $a_password = '' );

	abstract function & disconnect();

	abstract function & selectDatabase( $a_name );

	abstract function & getConnection();

	abstract function setConnection( & $a_connection );

	abstract function getInsertId();

	abstract function & begin();

	abstract function & rollback();

	abstract function & commit();

	abstract function & query( $a_query );

	public function hasError()
	{
		return ($this->m_error);
	}

	/**
	 *
	 * @return ZDatabaseQueryError
	 */
	public function & getError()
	{
		return $this->m_error;
	}

}
