<?php
/*
 * File: profiler.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Profiler
 */
class ZProfiler extends ZNode
{
    /**
     * Timers
     * @var array
     */
	private $m_times;

    /**
     * Memmory counter
     * @var array
     */
	private $m_mem;

	const DEFAULT_TIMER_NAME = 0;

    /**
     * Singleton instance
     * @var ZProfiler
     */
    static protected $m_instance = null;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->m_times = array();
		$this->m_mem = array();
    }

    /**
     * Protect from cloning
     */
    private function  __clone()
    {
    }

    /**
     * Get ZProfiler instance
     * @return ZProfiler
     */
    static public function & getInstance()
    {
        if( self::$m_instance == null )
            self::$m_instance = new ZProfiler;
        return self::$m_instance;
    }

    /**
     * Start named profile
     * @param string $a_name
     */
	static function start( $a_name = null )
	{
		$a_name = $a_name === null ? ZProfiler::DEFAULT_TIMER_NAME : $a_name;
		ZProfiler::getInstance()->m_times[ (string)$a_name ] = microtime(true);
		ZProfiler::getInstance()->m_mem[ (string)$a_name ] = memory_get_usage();
	}

    /**
     * Reset named profile
     * @param string $a_name
     */
	static function reset( $a_name = null )
	{
		self::start( $a_name );
	}

    /**
     * Get elapsed time from start
     * @param string $a_name
     * @return float
     */
	static public function elapsed( $a_name )
	{
		$a_name = $a_name === null ? ZProfiler::DEFAULT_TIMER_NAME : $a_name;
		return microtime(true) - ZProfiler::getInstance()->m_times[ (string)$a_name ];
	}

    /**
     * Get how memmory load since start
     * @param string $a_name
     * @return float
     */
	static public function memmory( $a_name )
	{
		$a_name = $a_name === null ? ZProfiler::DEFAULT_TIMER_NAME : $a_name;
		return memory_get_usage() - ZProfiler::getInstance()->m_mem[ (string)$a_name ];
	}

    /**
     * Return profile result as string
     * @param string $a_name
     * @return string
     */
	static public function result( $a_name )
	{
		$a_name = $a_name === null ? ZProfiler::DEFAULT_TIMER_NAME : $a_name;
		return "Elapsed '".$a_name."' timer: " . sprintf("%.3f", self::elapsed($a_name) ) . " ms. Memmory usage: " . self::realmem(self::memmory($a_name) );
	}

    /**
     * Convert digit of memmory bytes to short view
     * @param int $a_mem
     * @return string
     */
	static public function realmem( $a_mem )
	{
		$mem_usage = $a_mem;

		if ($mem_usage < 1024)
			return $mem_usage." bytes";
		elseif ($mem_usage < 1048576)
			return round($mem_usage/1024,2)." kilobytes";
		else
			return round($mem_usage/1048576,2)." megabytes";
	}
}
