<?php

class ZViewBlock extends ZView
{
	function getBegin()
	{
		$content = '---==timestamp_' . md5(time()) . '_now==---';
		$this->m_data->setRef('content', $content);

		$c = $this->display(true);
		if( strpos($c, $content) === false )
			return;

		list( $begin, $end ) = explode($content, $c);
		return $begin;
	}

	function getEnd()
	{
		$content = '---==timestamp_' . md5(time()) . '_now==---';
		$this->m_data->setRef('content', $content);

		$c = $this->display(true);
		if( strpos($c, $content) === false )
			return;

		list( $begin, $end ) = explode($content, $c);
		return $end;
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
