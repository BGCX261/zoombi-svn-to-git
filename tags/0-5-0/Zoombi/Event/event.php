<?php
/*
 * File: event.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Event
 */
class ZEvent
{
    /**
     * Event sender
     * @var ZObject
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
     * @param ZObject $a_sender Object sender
     * @param string $a_name Event name
     * @param mixed $_ Params
     */
    public function __construct( ZObject & $a_sender, $a_name, $_ = null )
    {
        $this->name = (string)$a_name;
        $this->sender =& $a_sender;
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        $this->data = $args;
        $this->accept = false;
    }

    /**
     * Accept event
     * @return ZEvent
     */
    public function & accept()
    {
        $this->accept = true;
        return $this;
    }

    /**
     * Reject event
     * @return ZEvent
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
     * @return ZObject
     */
    public function & getSender()
    {
        return $this->sender;
    }

    /**
     * Set event sender
     * @param ZObject $a_sender
     * @return ZEvent
     */
    public function & setSender( ZObject & $a_sender )
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
     * @return ZEvent
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
		if( isset($this->data[$a_index]) )
			return $this->data[$a_index];

        return $this->data;
    }
}
