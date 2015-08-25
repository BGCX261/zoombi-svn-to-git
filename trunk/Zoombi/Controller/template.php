<?php

class Zoombi_Controller_Template extends Zoombi_Controller_Document_Html
{

	protected $autorender = false;
	protected $template = null;

	protected function after()
	{
		if($this->autorender == true)
			$this->setOutput($this->render($this->template, array('content' => $this->getOutput()), true));

		parent::after();
	}

}