<?php
/*
 * File: log.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Log class
 */
class ZLog extends ZNode implements IZSingleton
{
    /**
     * Logs base directory
     * @var string
     */
	private $m_path;

    /**
     * Singleton instance
     * @var ZLog
     */
    static protected $m_instance = null;

    /**
     * Contructor
     */
    public function  __construct()
	{
        parent::__construct();
		$this->m_path = null;
	}

    /**
     * Protect from cloning
     */
    private function  __clone()
    {
    }

    /**
     * Get ZLog instance
     * @return ZLog
     */
    static public function & getInstance()
    {
        if( self::$m_instance == null )
            self::$m_instance = new ZLog;
        return self::$m_instance;
    }

    /**
     * Set logs base directory
     * @param string $a_path
     * @return ZLog
     */
	public function & setPath( $a_path )
	{
		if( !is_string( $a_path ) || !ZFolder::exist($a_path) )
            return $this;

		$this->m_path = $a_path;

		return $this;
	}

    /**
     * Log message
     * @param string $a_message
     * @param string $a_prefix
     * @return ZLog
     */
	public function log( $a_message, $a_prefix = null )
	{
		if( $this->m_path === null )
		{
			$this->m_path = Zoombi::config('log.directory_name','log');
		}

		$filename = 'log.txt';

		if( $a_prefix )
            $filename =  $a_prefix . '-' . $filename;

		$filepath = Zoombi::getApplication()->fromApplicationBaseDir( $filename );
        $message = (string)$a_message;
        $header = '';
        if ( !file_exists($filepath) )
		{
			$head = array();
			$head[] = "# Software:\tZoombi PHP Framework";
			$head[] = "# File name:\t".$filename;
			$head[] = "# Version:\t1.0";
			$head[] = "# File creation date:\t" . date("d.m.Y");
			$head[] = "# File creation time:\t" . date("H:i:s");
			$head[] = "#";
			$header = implode("\n", $head)."\n";
		}

        $fp = @fopen($filepath, 'a');
		if (!$fp)
			return false;

		$msg = array();
		$msg[] = "[".date("d.m.Y")."]";
		$msg[] = "[".date("H:i:s")."]";
		$msg[] = "[".$_SERVER['REMOTE_ADDR']."]";
		$msg[] = '- ' . $message;
        $message = implode(" ", $msg)."\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $header.$message);
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}
