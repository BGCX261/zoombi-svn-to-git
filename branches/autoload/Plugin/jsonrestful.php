<?php

class Zoombi_JsonRestful_plugin extends Zoombi_Plugin_Application
{
	private $m_rest = true;
	
	public function preExecute()
	{
		if( !$this->request->isAjax() )
			return;

		$t = $this->request->getContentType();
		
		if( stripos($t,'json') === false )
			return;
		
		$_SERVER['HTTP_ACCEPT'] = $t;
		$_POST = json_decode( $this->request->getPostBody(), true );
		$this->request->post->setDataRef( $_POST ); 
		$this->m_rest = true;
	}
}