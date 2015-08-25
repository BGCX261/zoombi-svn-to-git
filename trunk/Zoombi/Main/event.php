<?php

/*
 * File: event.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * Event
 */
class Zoombi_Event
{

	/**
	 * Event sender
	 * @var Zoombi_Object
	 */
	public $sender;

	/**
	 * Event accepted flag
	 * @var bool
	 */
	public $accept;

	/**
	 * Event name
	 * @var string
	 */
	public $name;

	/**
	 * Event data
	 * @var mixed
	 */
	public $data;

	/**
	 * Constructor
	 * @param Zoombi_Object $a_sender Object sender
	 * @param string $a_name Event name
	 * @param mixed $_ Params
	 */
	public function __construct( Zoombi_Object & $a_sender, $a_name, $_ = null )
	{
		$this->name = (string)$a_name;
		$this->sender = & $a_sender;
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		$this->data = $args;
		$this->accept = false;
	}

	/**
	 * Accept event
	 * @return Zoombi_Event
	 */
	public function & accept()
	{
		$this->accept = true;
		return $this;
	}

	/**
	 * Reject event
	 * @return Zoombi_Event
	 */
	public function & reject()
	{
		$this->accept = false;
		return $this;
	}

	/**
	 * Check if event accepted
	 * @return bool
	 */
	public function isAccepted()
	{
		return (bool)$this->accept;
	}

	/**
	 * Get event sender
	 * @return Zoombi_Object
	 */
	public function & getSender()
	{
		return $this->sender;
	}

	/**
	 * Set event sender
	 * @param Zoombi_Object $a_sender
	 * @return Zoombi_Event
	 */
	public function & setSender( Zoombi_Object & $a_sender )
	{
		$this->sender = $a_sender;
		return $this;
	}

	/**
	 * Get event name
	 * @return string
	 */
	public function & getName()
	{
		return $this->name;
	}

	/**
	 * Set event name
	 * @param string $a_name
	 * @return Zoombi_Event
	 */
	public function & setName( $a_name )
	{
		$this->name = (string)$a_name;
		return $this;
	}

	/**
	 * Get event data
	 * @return mixed
	 */
	public function & getData( $a_index = 0 )
	{
		if(isset($this->data[$a_index]))
			return $this->data[$a_index];

		return $this->data;
	}

}
