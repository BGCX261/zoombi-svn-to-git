<?php

abstract class ZDatabaseAdapter extends ZObject
{

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
}