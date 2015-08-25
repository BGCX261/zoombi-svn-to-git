<?php

class ZViewBlock extends ZObjectBinder
{
	private $m_file;
	private $m_begin;
	private $m_end;
	private $m_compiled;

	function setViewFile( $a_view )
	{
		$this->m_file = $a_view;
	}

	function _compile()
	{
		$this->m_compiled = true;

		$content = '---==timestamp_' . md5( time() ) . '_now==---';
		ob_start();
		include( $this->m_file );
		$c = ob_get_contents();
		ob_end_clean();

		if( strpos($c,$content) === false )
			return;

		list( $this->m_begin, $this->m_end ) = explode($content,$c);
	}


	function getBegin()
	{
		if( $this->m_compiled != true )
			$this->_compile ();
		return $this->m_begin;
	}

	function getEnd()
	{
		if( $this->m_compiled != true )
			$this->_compile ();
		return $this->m_end;
	}

	function begin()
	{
		echo $this->getBegin();
	}

	function end()
	{
		echo $this->getEnd();
	}
}