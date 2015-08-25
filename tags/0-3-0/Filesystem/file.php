<?php
/*
 * File: file.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

class ZFile
{
	static public $seek_set = SEEK_SET;
	static public $seek_cur = SEEK_CUR;
	static public $seek_end = SEEK_END;

	private $m_handle;
	private $m_filename;
	private $m_path;

	public function  __construct()
	{
		$this->m_handle = null;
	}

	public function __destruct()
	{
		$this->close();
	}

	public function open( $a_filename )
	{
		$file = (string)$a_filename;
		$this->close();

		if( !self::exist($file) )
			return false;

		$this->m_path = dirname($file);
		$this->m_handle = fopen($file,'rw');
		$this->m_filename = $file;

		return ( is_resource($this->m_handle) ) ? true : false;
	}

	public function rewind()
	{
		if( !is_resource($this->m_handle) )
			return;

		return rewind($this->m_handle);
	}

	public function eof()
	{
		if( !is_resource($this->m_handle) )
			return;

		return feof($this->m_handle);
	}

	public function tell()
	{
		if( !is_resource($this->m_handle) )
			return;

		return ftell($this->m_handle);
	}

	public function flush()
	{
		if( !is_resource($this->m_handle) )
			return;

		return fflush($this->m_handle);
	}

	public function close()
	{
		if( !is_resource($this->m_handle) )
			return;

		return fclose($this->m_handle);
	}

	public function char( $a_char = null )
	{
		if( !is_resource($this->m_handle) )
			return;

		if( $a_char !== null )
			return $this->write( substr($a_char, 1) );

		return fgetc($this->m_handle);
	}

	public function line( $a_line = null )
	{
		if( !is_resource($this->m_handle) )
			return;

		if( $a_line !== null )
		{
			$line = $a_line;
			$f = strpos($a_line, "\n");
			if( $f )
				$line = substr($line,0,$f+1);

			return $this->write($line);
		}

		return fgets($this->m_handle,$a_max);
	}

	public function read( $a_len = null )
	{
		if( !is_resource($this->m_handle) )
			return;

		$max = 8192;

		if( $a_len )
		{
			$buffer = "";
			while( !$this->eof() && $readed < $len )
			{
				$readed = $max;
				if( $a_len < $max )
				{
					$readed = $a_len;
				}
				$buffer .= fread($this->m_handle, $readed);
			}
			return $buffer;
		}
		else
		{
			$buffer = "";
			while( !$this->eof() )
				$buffer .= fread($this->m_handle, $max);
			return $buffer;
		}
	}

	public function write( $a_data  )
	{
		if( !is_resource($this->m_handle) )
			return;

		return fwrite($handle, $string);
	}

	public function seek( $a_offset, $a_whence = null )
	{
		if( !is_resource($this->m_handle) )
			return;

		if( $a_whence == null )
			$a_whence = self::$seek_set;

		$result = fseek($this->m_handle, $a_offset, $a_whence );
		return ( $result === 0 ) ? true : false;
	}

	public function get_contents()
	{
		return file_get_contents( $this->m_filename );
	}

	public function put_contents( $a_data )
	{
		return file_put_contents( $this->m_filename, $a_data );
	}

	public function path( $a_path )
	{
		return realpath( $a_path );
	}

	public function dir( $a_dir )
	{
		return dirname( $a_dir );
	}

	static public function exist( $a_filename )
	{
		return file_exists( $a_filename );
	}

	static public function readable( $a_filename )
	{
		return is_readable( $a_filename );
	}

	static public function writable( $a_filename )
	{
		return is_writable( $a_filename );
	}
}
