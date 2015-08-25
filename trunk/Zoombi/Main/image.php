<?php

/*
 * File: image.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

/**
 * Image class
 */
class Zoombi_Image
{
	const TYPE_JPG = 1;
	const TYPE_GIF = 2;
	const TYPE_PNG = 3;
	const TYPE_BMP = 4;

	private $m_handle;

	/**
	 * Clear image data and size
	 * @return Zoombi_Image
	 */
	function & clear()
	{
		if($this->m_handle)
			imagedestroy($this->m_handle);
		
		return $this;
	}

	/**
	 * Load image from file
	 * @param string $a_file File name
	 * @return Zoombi_Image
	 */
	function fromFile( $a_file )
	{
		if(file_exists($a_file) && is_file($a_file) && is_readable($a_file))
			$this->fromString(file_get_contents($a_file));
		
		return $this;
	}

	/**
	 * Load image data from string
	 * @param string $a_data
	 * @return Zoombi_Image 
	 */
	function & fromString( $a_data )
	{
		return $this->set(imagecreatefromstring($a_data));
	}

	/**
	 * Set image resource
	 * @param resource $a_image
	 * @return Zoombi_Image 
	 */
	function & set( $a_image )
	{
		$this->clear();

		$this->m_handle = $a_image;
		return $this;
	}
	
	/**
	 * Get image resource
	 * @return resource 
	 */
	function & get()
	{
		return $this->m_handle;
	}

	/**
	 * Resize image
	 * @param int $a_x
	 * @param int $a_y
	 * @return Zoombi_Image
	 */
	function & resize( $a_x, $a_y )
	{
		$r = $this->width() / $this->height();

		$w = $a_x;
		$h = $a_y;

		if($w == 0)
			$w = $this->width() - ( $this->height() - $a_y );

		if($h == 0)
			$h = $this->height() - ( $this->width() - $a_x );

		if($w / $h > $r)
		{
			$newwidth = $h * $r;
			$newheight = $h;
		}
		else
		{
			$newheight = $w / $r;
			$newwidth = $w;
		}

		$dst = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled(
			$dst, $this->m_handle, 0, 0, 0, 0, $newwidth, $newheight, $this->width(), $this->height()
		);
		
		return $this->set($dst);
	}
	
	function width()
	{
		return imagesx($this->m_handle);
	}
	
	function height()
	{
		return imagesy($this->m_handle);
	}
	
	function x()
	{
		return imagesx($this->m_handle);
	}
	
	function y()
	{
		return imagesy($this->m_handle);
	}

	/**
	 * Crop image
	 * @param int $a_x
	 * @param int $a_y 
	 * @return Zoombi_Image
	 */
	function & crop( $a_x, $a_y )
	{
		$r = $this->width() / $this->height();

		$w = $a_x;
		$h = $a_y;
		
		$cw = $a_x;
		$ch = $a_y;

		if( $this->width() > $this->height() )
			$cw = ceil($this->width() - ($this->width() * ($r - $w / $h)));
		else
			$ch = ceil($this->height() - ($this->height() * ($r - $w / $h)));

		$dst = imagecreatetruecolor($w, $h);
		imagecopyresampled(
			$dst, $this->m_handle, 0, 0, 0, 0, $w, $h, $cw, $ch
		);
		
		return $this->set($dst);
	}

	/**
	 * Crop image to specified width
	 * @param int $a_max
	 * @return Zoombi_Image
	 */
	function & max_width( $a_max )
	{
		$cw = $this->width();
		$ch = $this->height();
		
		$ratio = $cw / $ch;
		
		$tw = intval($a_max);
		$th = $tw / $ratio;
		
		$dst = imagecreatetruecolor($tw, $th);
		if( $dst )
		{
			if( imagecopyresized(
				$dst, $this->m_handle,
				0, 0,
				0, 0,
				$tw, $th,
				$cw, $ch
			) )
				return $this->set($dst);
		}
		return $this;
	}

	/**
	 * Crop image to specified height
	 * @param int $a_max
	 * @return Zoombi_Image 
	 */
	function & max_height( $a_max )
	{
		$cw = $this->width();
		$ch = $this->height();
		
		$ratio = $cw / $ch;
		
		$th = intval($a_max);
		$tw = $th * $ratio;
		
		$dst = imagecreatetruecolor($tw, $th);
		if( $dst )
		{
			if( imagecopyresized(
				$dst, $this->m_handle,
				0, 0,
				0, 0,
				$tw, $th,
				$cw, $ch
			) )
				return $this->set($dst);
		}
		return $this;
	}

	/**
	 * Crop image to specified width and height
	 * @param int $a_x
	 * @param int $a_y 
	 * @return Zoombi_Image
	 */
	function & max( $a_x, $a_y )
	{
		if($a_x && $this->width() > $a_x)
			$this->max_width($a_x);

		if($a_y && $this->height() > $a_y)
			$this->max_height($a_y);
		
		return $this;
	}

	/**
	 * Crop image to specified width and height
	 * @param int $a_x
	 * @param int $a_y 
	 * @return Zoombi_Image
	 */
	function wrap( $a_max )
	{
		if($a_max && $this->width() > $a_max)
			$this->max_width($a_max);

		if($a_max && $this->height() > $a_max)
			$this->max_height($a_max);
		
		return $this;
	}

	/**
	 * Echo image data to output buffer
	 * @param string $a_mime Set image mime
	 */
	function output( $a_mime = 'image/png' )
	{
		$mime = trim(strtolower($a_mime));

		if($this->m_handle)
		{
			ob_start();
			switch($mime)
			{
				default:
				case 'jpg':
				case 'jpeg':
				case 'image/jpeg':
					$mime = 'image/jpeg';
					imagejpeg($this->m_handle);
					break;

				case 'png':
				case 'image/png':
					$mime = 'image/png';
					imagepng($this->m_handle);
					break;

				case 'gif':
				case 'image/gif':
					$mime = 'image/gif';
					imagegif($this->m_handle);
					break;

				case 'gd':
				case 'image/gd':
					$mime = 'image/gd';
					imagegd($this->m_handle);
					break;

				case 'gd2':
				case 'image/gd2':
					$mime = 'image/gd2';
					imagegd2($this->m_handle);
					break;

				case 'bmp':
				case 'wbmp':
				case 'image/bmp':
				case 'image/wbmp':
					$mime = 'image/bmp';
					imagewbmp($this->m_handle);
					break;
			}
			Zoombi_Response::getInstance()->setContent(ob_get_contents());
			ob_end_clean();
		}
		Zoombi_Response::getInstance()->setContentType($mime);
		return $this;
	}

}
