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
	private $m_width;
	private $m_height;

	/**
	 * Clear image data and size
	 * @return Zoombi_Image
	 */
	function & clear()
	{
		if($this->m_handle)
			imagedestroy($this->m_handle);

		$this->m_width = 0;
		$this->m_height = 0;
		
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
	 * Set new image handle
	 * @param mixed $a_image
	 * @return Zoombi_Image 
	 */
	function & set( $a_image )
	{
		$this->clear();

		$this->m_handle = $a_image;
		$this->m_width = imagesx($this->m_handle);
		$this->m_height = imagesy($this->m_handle);
		return $this;
	}

	/**
	 * Resize image
	 * @param int $a_x
	 * @param int $a_y
	 * @return Zoombi_Image
	 */
	function & resize( $a_x, $a_y )
	{
		$r = $this->m_width / $this->m_height;

		$w = $a_x;
		$h = $a_y;

		if($w == 0)
			$w = $this->m_width - ( $this->m_height - $a_y );

		if($h == 0)
			$h = $this->m_height - ( $this->m_width - $a_x );

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
			$dst, $this->m_handle, 0, 0, 0, 0, $newwidth, $newheight, $this->m_width, $this->m_height
		);
		
		return $this->set($dst);
	}

	/**
	 * Crop image
	 * @param int $a_x
	 * @param int $a_y 
	 * @return Zoombi_Image
	 */
	function & crop( $a_x, $a_y )
	{
		$r = $this->m_width / $this->m_height;

		$w = $a_x;
		$h = $a_y;

		if($this->m_width > $this->m_height)
			$this->m_width = ceil($this->m_width - ($this->m_width * ($r - $w / $h)));
		else
			$this->m_height = ceil($this->m_height - ($this->m_height * ($r - $w / $h)));

		$dst = imagecreatetruecolor($w, $h);
		imagecopyresampled(
			$dst, $this->m_handle, 0, 0, 0, 0, $w, $h, $this->m_width, $this->m_height
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
		$max = intval($a_max) * ( $this->m_height / $this->m_width );
		$dst = imagecreatetruecolor(intval($a_max), $max);
		imagecopyresampled(
			$dst, $this->m_handle, 0, 0, 0, 0, intval($a_max), $max, $this->m_width, $this->m_height
		);
		return $this->set($dst);
	}

	/**
	 * Crop image to specified height
	 * @param int $a_max
	 * @return Zoombi_Image 
	 */
	function & max_height( $a_max )
	{
		$max = intval($a_max) * ( $this->m_width / $this->m_height  );
		$dst = imagecreatetruecolor($max,intval($a_max));
		imagecopyresampled(
			$dst, $this->m_handle, 0, 0, 0, 0, $max, intval($a_max), $this->m_width, $this->m_height
		);
		return $this->set($dst);
	}

	/**
	 * Crop image to specified width and height
	 * @param int $a_x
	 * @param int $a_y 
	 * @return Zoombi_Image
	 */
	function & max( $a_x, $a_y )
	{
		if($a_x && $this->m_width > $a_x)
			$this->max_width($a_x);

		if($a_y && $this->m_height > $a_y)
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
		if($a_x && $this->m_width > $a_x)
			$this->max_width($a_x);

		if($a_y && $this->m_height > $a_y)
			$this->max_height($a_y);
		
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
					imagejpeg($this->m_handle);
					break;

				case 'png':
				case 'image/png':
					imagepng($this->m_handle);
					break;

				case 'gif':
				case 'image/gif':
					imagegif($this->m_handle);
					break;

				case 'gd':
				case 'image/gd':
					imagegd($this->m_handle);
					break;

				case 'gd2':
				case 'image/gd2':
					imagegd2($this->m_handle);
					break;

				case 'bmp':
				case 'wbmp':
				case 'image/bmp':
				case 'image/wbmp':
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
