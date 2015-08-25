<?php

/*
 * File: debug.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


class Zoombi_Debug implements Zoombi_Singleton
{

	static private $m_instance;
	private $m_traces;

	private function __construct()
	{
		$this->m_traces = array();
	}

	static public function & getInstance()
	{
		if(self::$m_instance == null)
			self::$m_instance = new self;

		return self::$m_instance;
	}

	private function __clone()
	{
		
	}

	/**
	 * Add data to trace
	 * @param string $a_data
	 * @return Zoombi_Debug
	 */
	static public function & trace( $a_data = null )
	{
		$i = self::getInstance();

		foreach(func_get_args() as $a)
		{
			switch(gettype($a))
			{
				default:
					$i->m_traces[] = (string)$a;
					break;

				case 'array':
				case 'object':
					$i->m_traces[] = Zoombi_Json::encode($a_data);
					break;
			}
		}
		return $i;
	}

	static public function getTraces()
	{
		$out = '';
		foreach(self::getInstance()->m_traces as $k => $v)
			$out .= '<b>' . $k . '</b>: ' . $v . '<br />';
		
		return $out;
	}
	
	static public function printTraces()
	{
		echo self::getTraces();
	}

}