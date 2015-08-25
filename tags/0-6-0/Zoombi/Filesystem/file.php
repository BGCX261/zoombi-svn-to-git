<?php

/*
 * File: file.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

class ZFile
{

	static public $seek_set = SEEK_SET;
	static public $seek_cur = SEEK_CUR;
	static public $seek_end = SEEK_END;
	private $m_handle;
	private $m_filename;
	private $m_path;

	public function __construct()
	{
		$this->m_handle = null;
	}

	public function __destruct()
	{
		$this->close();
	}

	public function open( $a_filename, $a_mode = 'rw' )
	{
		$file = (string)$a_filename;
		$this->close();

		if(!self::exist($file))
			return false;

		$this->m_path = dirname($file);
		$this->m_handle = fopen($file, $a_mode);
		$this->m_filename = $file;

		return $this->isValid();
	}

	public function & handle()
	{
		return $this->m_handle;
	}

	public function isValid()
	{
		return $this->m_handle && is_resource($this->m_handle);
	}

	public function rewind()
	{
		if(!$this->isValid())
			return;

		return rewind($this->m_handle);
	}

	public function eof()
	{
		if(!$this->isValid())
			return;

		return feof($this->m_handle);
	}

	public function tell()
	{
		if(!$this->isValid())
			return;

		return ftell($this->m_handle);
	}

	public function flush()
	{
		if(!$this->isValid())
			return;

		return fflush($this->m_handle);
	}

	public function close()
	{
		if(!$this->isValid())
			return;

		return fclose($this->m_handle);
	}

	public function char( $a_char = null )
	{
		if(!$this->isValid())
			return;

		if(func_num_args() > 0)
			return $this->write(substr($a_char, 1));

		return fgetc($this->m_handle);
	}

	public function line( $a_line = null )
	{
		if(!$this->isValid())
			return;

		if(func_num_args() > 0)
		{
			$line = $a_line;
			$f = strpos($a_line, "\n");
			if($f)
				$line = substr($line, 0, $f + 1);

			return $this->write($line);
		}

		return fgets($this->m_handle, $a_max);
	}

	public function read( $a_len = null )
	{
		if(!$this->isValid())
			return;

		$max = 8192;
		$buffer = "";

		if(func_num_args() > 0)
		{
			while(!$this->eof() && $readed < $len)
			{
				$readed = $max;
				if($a_len < $max)
					$readed = $a_len;

				$bytes = fread($this->m_handle, $readed);
				if($bytes === false)
					break;
			}
		}
		else
		{
			while(!$this->eof())
			{
				$bytes = fread($this->m_handle, $max);
				if($bytes === false)
					break;
			}
		}

		return $buffer;
	}

	public function write( $a_data )
	{
		if(!$this->isValid())
			return;

		return fwrite($this->m_handle, $a_data);
	}

	public function seek( $a_offset, $a_whence = null )
	{
		if(!$this->isValid())
			return;

		if(func_num_args() < 2)
			$a_whence = SEEK_SET;

		return fseek($this->m_handle, $a_offset, $a_whence);
	}

	public function get_contents()
	{
		return file_get_contents($this->m_filename);
	}

	public function put_contents( $a_data )
	{
		return file_put_contents($this->m_filename, $a_data);
	}

	static public function path( $a_path )
	{
		return realpath($a_path);
	}

	static public function dir( $a_dir )
	{
		return dirname($a_dir);
	}

	static public function exist( $a_filename )
	{
		return file_exists((string)$a_filename);
	}

	static public function readable( $a_filename )
	{
		return is_readable($a_filename);
	}

	static public function writable( $a_filename )
	{
		return is_writable($a_filename);
	}

}
