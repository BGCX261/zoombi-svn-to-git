<?php

class ZImage
{
	const TYPE_JPG = 1;
	const TYPE_GIF = 2;
	const TYPE_PNG = 3;

	private $m_handle;
	private $m_width;
	private $m_height;

	function clear()
	{
		if( $this->m_handle )
			imagedestroy($this->m_handle);

		$this->m_width = 0;
		$this->m_height = 0;
	}

	function fromFile($a_file)
	{
		if( file_exists($a_file) && is_file($a_file) && is_readable($a_file) )
			$this->fromString(file_get_contents($a_file));
	}

	function fromString($a_data)
	{
		$this->set( imagecreatefromstring($a_data) );
	}
	
	function set( & $a_image )
	{
		$this->clear();
		
		$this->m_handle = $a_image;
		$this->m_width	= imagesx($this->m_handle);
		$this->m_height	= imagesy($this->m_handle);
	}

	function resize( $a_x, $a_y )
	{
		$r = $this->m_width / $this->m_height;

		$w = $a_x;
		$h = $a_y;

		if( $w == 0 )
			$w = $this->m_width - ( $this->m_height - $a_y );

		if( $h == 0 )
			$h = $this->m_height - ( $this->m_width - $a_x );

		if ( $w/$h > $r )
		{
			$newwidth = $h*$r;
			$newheight = $h;
		}
		else
		{
			$newheight = $w/$r;
			$newwidth = $w;
		}

		$dst = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled(
			$dst,		$this->m_handle,
			0, 0,		0, 0,
			$newwidth, $newheight, $this->m_width, $this->m_height
		);
		$this->set($dst);
	}

	function crop( $a_x, $a_y )
	{
		$r = $this->m_width / $this->m_height;

		$w = $a_x;
		$h = $a_y;
		
		if ($this->m_width > $this->m_height)
		{
			$this->m_width = ceil($this->m_width-($this->m_width*($r-$w/$h)));
		}
		else
		{
			$this->m_height = ceil($this->m_height-($this->m_height*($r-$w/$h)));
		}
		
		$dst = imagecreatetruecolor($w, $h);
		imagecopyresampled(
			$dst,		$this->m_handle,
			0, 0,		0, 0,
			$w, $h,		$this->m_width, $this->m_height
		);
		$this->set($dst);
	}

	function max_width( $a_max )
	{$max = intval($a_max) * ( $this->m_height / $this->m_width );
		
		$dst = imagecreatetruecolor( intval($a_max), $max );
		imagecopyresampled(
			$dst,		$this->m_handle,
			0, 0,		0, 0,
			intval($a_max), $max,		$this->m_width, $this->m_height
		);
		$this->set($dst);
	}

	function max_height( $a_max )
	{

		$max = intval($a_max) * ( $this->m_width / $this->m_height );
		$dst = imagecreatetruecolor( $max, intval($a_max) );
		imagecopyresampled(
			$dst,		$this->m_handle,
			0, 0,		0, 0,
			$max, intval($a_max),		$this->m_width, $this->m_height
		);
		$this->set($dst);
	}

	function max( $a_x, $a_y )
	{
		if( $a_x && $this->m_width > $a_x )
		 $this->max_width ($a_x);

		if( $a_y && $this->m_height > $a_y )
		 $this->max_height ($a_y);
	}

	function wrap( $a_max )
	{
		if( $a_x && $this->m_width > $a_x )
		 $this->max_width ($a_x);

		if( $a_y && $this->m_height > $a_y )
		 $this->max_height ($a_y);
	}

	function output( $a_mime = 'image/png' )
	{
		ob_start();
		imagepng($this->m_handle);
		$c = ob_get_contents();
		ob_end_clean();

		if( $a_mime )
			ZHeaders::setHeader( 'Content-type', $a_mime );

		ZHeaders::setHeader( 'Accept-Ranges', 'bytes' );
		ZHeaders::setHeader( 'Content-Length', strlen($c) );
		ZHeaders::setHeader( 'Content-MD5', md5($c) );
		
		echo $c;
	}
}
