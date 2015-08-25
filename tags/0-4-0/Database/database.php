<?php

class ZDatabase extends ZObject
{
	const RESULT_TYPE_NUM = 0;
	const RESULT_TYPE_ASSOC = 1;
	const RESULT_TYPE_BOTH = 2;
	const RESULT_TYPE_OBJECT = 3;

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

	public function setTransaction( $a_flag )
	{
		$this->m_db_transaction = (bool)$a_flag;
	}

	public function & getTransaction()
	{
		return $this->m_db_transaction;
	}

	public function & setAdapter( ZDatabaseAdapter & $a_adapter )
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
		switch( gettype($a_url) )
		{
			case 'string':
				$url = new ZUrl($a_url);
				$this->m_sheme = $url->scheme();
				$this->m_address = $url->host();
				$this->m_login = $url->user();
				$this->m_password = $url->pass();
				$this->m_database = $url->path();
				$this->m_port = $url->port();
				break;

			case 'array':
				if( isset($a_url['scheme']) )
					$this->m_sheme = $a_url['scheme'];

				if( isset($a_url['driver']) )
					$this->m_sheme = $a_url['driver'];

				if( isset($a_url['adapter']) )
					$this->m_sheme = $a_url['adapter'];

				if( isset($a_url['host']) )
					$this->m_address = $a_url['host'];

				if( isset($a_url['address']) )
					$this->m_address = $a_url['address'];

				if( isset($a_url['port']) )
					$this->m_port = $a_url['port'];

				if( isset($a_url['user']) )
					$this->m_login = $a_url['user'];

				if( isset($a_url['login']) )
					$this->m_login = $a_url['login'];

				if( isset($a_url['pass']) )
					$this->m_password = $a_url['pass'];

				if( isset($a_url['password']) )
					$this->m_password = $a_url['password'];

				if( isset($a_url['path']) )
					$this->m_database = $a_url['path'];

				if( isset($a_url['database']) )
					$this->m_database = $a_url['database'];

				break;

			case 'object':
				if( $a_url instanceof ZUrl )
					$this->open($a_url->getData());
				else
					$this->open(get_object_vars($a_url));
				return $this;
		}

		if( $this->m_sheme )
		{
			$adapter_class = 'Z' . $this->m_sheme . 'DatabaseAdapter';
			$adapter = new $adapter_class($this);
			if( !$adapter || !($adapter instanceof ZDatabaseAdapter) )
				throw new ZDatabaseException('Bad ZDatabaseAdapter sheme given: ' . $this->m_sheme);

			$this->setAdapter($adapter);
			$this->getAdapter()->connect($this->m_address, $this->m_login, $this->m_password);

			if( $this->m_database )
			{
				$this->m_database = str_replace('/', '', $this->m_database);
				$this->getAdapter()->selectDatabase($this->m_db_prefix . $this->m_database . $this->m_db_suffix);
			}
		}
		else
		{
			throw new ZDatabaseException('Bad scheme given ' . $this->m_sheme);
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

	public function result()
	{
		return $this->getAdapter()->result();
	}

	public function & getAdapter()
	{
		if( !$this->m_adapter )
			throw new ZDatabaseException('Database adapter is not created');

		return $this->m_adapter;
	}

	public function & query( $a_query )
	{
		$this->getAdapter()->query($a_query);
		return $this;
	}

}
