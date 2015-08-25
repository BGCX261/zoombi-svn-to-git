<?php

class ZDebug implements IZSingleton
{
	static private $m_instance;

	private $m_traces;

	private function __construct()
	{
		$this->m_traces = array();
	}

	static public function & getInstance()
	{
		if( self::$m_instance == null )
			self::$m_instance = new self;

		return self::$m_instance;
	}


	private function __clone()
	{

	}

	/**
	 * Add data to trace
	 * @param string $a_data
	 * @return ZDebug
	 */
	static public function & trace( $a_data = null )
	{
		$i = self::getInstance();

		if( $a_data == null )
			return $i->m_traces;

		$i->m_traces[] = (string)$a_data;
		return $i;
	}

	static public function printTraces()
	{
		foreach(self::getInstance()->m_traces as $k => $v)
			echo '<b>'.$k.'</b>: ' . $v . '<br />';
	}
}