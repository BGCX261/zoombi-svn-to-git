<?php

/*
 * File: database.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


class Zoombi_Database extends Zoombi_Object
{
	const RESULT_TYPE_NUM = 0;
	const RESULT_TYPE_ASSOC = 1;
	const RESULT_TYPE_BOTH = 2;
	const RESULT_TYPE_OBJECT = 3;
	const RESULT_TYPE_RAW = 4;

	private $m_adapter;
	private $m_sheme;
	private $m_address;
	private $m_login;
	private $m_password;
	private $m_database;
	private $m_port;
	private $m_db_prefix;
	private $m_db_suffix;
	private $m_db_transaction;
	private $m_query_string;

	public function setTransaction( $a_flag )
	{
		$this->m_db_transaction = (bool)$a_flag;
	}

	public function & getTransaction()
	{
		return $this->m_db_transaction;
	}

	public function & setAdapter( Zoombi_Database_Adapter & $a_adapter )
	{
		$this->m_adapter = $a_adapter;
		return $this;
	}

	public function getInsertId()
	{
		return $this->getAdapter()->getInsertId();
	}

	public function & connect( $a_url )
	{
		return $this->open($a_url);
	}

	public function & open( $a_url )
	{
		switch(gettype($a_url))
		{
			case 'string':
				$url = new Zoombi_Url($a_url);
				$this->m_sheme = $url->scheme();
				$this->m_address = $url->host();
				$this->m_login = $url->user();
				$this->m_password = $url->pass();
				$this->m_database = $url->path();
				$this->m_port = $url->port();
				break;

			case 'array':
				if(isset($a_url['scheme']))
					$this->m_sheme = $a_url['scheme'];

				if(isset($a_url['driver']))
					$this->m_sheme = $a_url['driver'];

				if(isset($a_url['adapter']))
					$this->m_sheme = $a_url['adapter'];

				if(isset($a_url['host']))
					$this->m_address = $a_url['host'];

				if(isset($a_url['address']))
					$this->m_address = $a_url['address'];

				if(isset($a_url['port']))
					$this->m_port = $a_url['port'];

				if(isset($a_url['user']))
					$this->m_login = $a_url['user'];

				if(isset($a_url['login']))
					$this->m_login = $a_url['login'];

				if(isset($a_url['pass']))
					$this->m_password = $a_url['pass'];

				if(isset($a_url['password']))
					$this->m_password = $a_url['password'];

				if(isset($a_url['path']))
					$this->m_database = $a_url['path'];

				if(isset($a_url['database']))
					$this->m_database = $a_url['database'];

				break;

			case 'object':
				if($a_url instanceof Zoombi_Url)
					$this->open($a_url->getData());
				else
					$this->open(get_object_vars($a_url));
				return $this;
		}

		if($this->m_sheme)
		{
			$adapter_class = 'Zoombi_Database_Adapter_' . $this->m_sheme;
			$adapter = new $adapter_class($this);
			if(!$adapter || !($adapter instanceof Zoombi_Database_Adapter))
				throw new Zoombi_Exception_Database('Bad Zoombi_Database_Adapter sheme given: ' . $this->m_sheme, Zoombi_Exception_Database::EXC_OPEN);

			$this->setAdapter($adapter);
			$this->getAdapter()->connect($this->m_address, $this->m_login, $this->m_password);

			if($this->m_database)
			{
				$this->m_database = str_replace('/', '', $this->m_database);
				$this->getAdapter()->selectDatabase($this->m_db_prefix . $this->m_database . $this->m_db_suffix);
			}
		}
		else
		{
			throw new Zoombi_Exception_Database('Bad scheme given ' . $this->m_sheme, Zoombi_Exception_Database::EXC_OPEN);
		}

		return $this;
	}

	public function getDbPrefix()
	{
		return $this->m_db_prefix;
	}

	public function & setDbPrefix( $a_prefix )
	{
		$this->m_db_prefix = $a_prefix;
		return $this;
	}

	public function getDbSuffix()
	{
		return $this->m_db_suffix;
	}

	public function & setDbSuffix( $a_suffix )
	{
		$this->m_db_suffix = $a_suffix;
		return $this;
	}

	public function & disconnect()
	{
		$this->getAdapter()->disconnect();
	}

	public function & close()
	{
		$this->getAdapter()->disconnect();
		return $this;
	}

	public function isConnected()
	{
		return $this->isOpen();
	}

	public function isOpen()
	{
		return ( $this->getAdapter()->getConnection() != null );
	}

	public function result( $a_result_type = self::RESULT_TYPE_BOTH )
	{
		return $this->getAdapter()->result($a_result_type);
	}

	public function & getAdapter()
	{
		if(!$this->m_adapter)
			throw new Zoombi_Exception_Database('Database adapter is not created');

		return $this->m_adapter;
	}

	public function & query( $a_query )
	{
		$this->getAdapter()->query($a_query);
		return $this;
	}

	public function getError()
	{
		return $this->getAdapter()->getError();
	}

	public function getQuery()
	{
		return $this->m_query_string;
	}

	public function & setQuery( $a_query )
	{
		$this->m_query_string = $a_query;
		return $this;
	}
	
	public function execQuery( $a_query, $a_result = self::RESULT_TYPE_BOTH )
	{
		return $this->query( $a_query )->result( $a_result );
	}

}
