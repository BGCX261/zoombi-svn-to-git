<?php

/*
 * File: dispatcher.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * Event dispatcher class
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
class ZDispatcher extends ZObject
{

	/**
	 * Array of listeners
	 * @var array
	 */
	private $m_listeners;

	/**
	 * Array of processors
	 * @var array
	 */
	private $m_processor;

	/**
	 * Constructor
	 * @param ZObject $a_parent
	 * @param string $a_name
	 */
	public function __construct( ZObject & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		$this->m_listeners = array();
		$this->m_processor = array();
	}

	/**
	 * Connect listener
	 * @param string $a_name
	 * @param callback $a_listener
	 * @return ZDispatcher
	 */
	public function & connect( $a_name, $a_listener )
	{
		if(!$this->hasListeners($a_name))
			$this->m_listeners[(string)$a_name] = array();

		$this->m_listeners[(string)$a_name][] = & $a_listener;
		$this->notify(new ZEvent($this, 'onConnect', $a_name, $a_listener));
		return $this;
	}

	/**
	 * Disconnect listener
	 * @param string $a_name
	 * @param callback $a_listenter
	 * @return ZDispatcher
	 */
	public function & disconnect( $a_name, $a_listenter )
	{
		if(!$this->hasListeners($a_name))
			return $this;

		foreach($this->getListeners($a_name) as $index => $listenter)
		{
			if($listenter !== $a_listener)
				continue;

			unset($this->m_listeners[$a_name][$index]);
			$this->notify(new ZEvent($this, 'onDisconnect', $a_name, $a_listener));
		}
		return $this;
	}

	/**
	 * Check for listeners
	 * @param string $a_name
	 * @return bool
	 */
	public function hasListeners( $a_name )
	{
		$name = (string)$a_name;
		return isset($this->m_listeners[$name]) || array_key_exists($name, $this->m_listeners);
	}

	/**
	 * Check for listener
	 * @param string $a_name
	 * @return bool
	 */
	public function hasListener( $a_name )
	{
		$name = (string)$a_name;
		return isset($this->m_listeners[$name]) || array_key_exists($name, $this->m_listeners);
	}

	/**
	 * Get listeners
	 * @param string $a_name
	 * @return array
	 */
	public function getListeners( $a_name )
	{
		if(!$this->hasListeners($a_name))
			return array();

		return $this->m_listeners[$a_name];
	}

	/**
	 * Disconnect all listeners
	 * @param string $a_name
	 * @return ZDispatcher
	 */
	public function & disconnectAll( $a_name )
	{
		foreach($this->getListeners($a_name) as $listenter)
			$this->disconnect($a_name, $listenter);
		return $this;
	}

	/**
	 * Add event processor
	 * @param callback $a_processor
	 * @return ZDispatcher
	 */
	public function & addProcessor( $a_processor )
	{
		if(is_callable($a_processor))
			$this->m_processor[] = $a_processor;
		return $this;
	}

	/**
	 * Add event processors
	 * @param array $a_processor
	 * @return ZDispatcher
	 */
	public function & addProcessors( array $a_processor )
	{
		foreach($this->m_processor as $p)
			$this->addProcessor($p);
		return $this;
	}

	/**
	 * Set event processor
	 * @param callback $a_processor
	 * @return ZDispatcher
	 */
	public function & setProcessor( $a_processor )
	{
		$this->m_processor = array();
		if(is_callable($a_processor))
			$this->m_processor[] = $a_processor;
		return $this;
	}

	/**
	 * Set event processors
	 * @param array $a_processors
	 * @return ZDispatcher
	 */
	public function & setProcessors( array $a_processors )
	{
		$this->m_processor = array();
		return $this->addProcessors($a_processors);
	}

	/**
	 * Get event processors
	 * @return array
	 */
	public function & getProcessors()
	{
		return $this->m_processor;
	}

	/**
	 * Remove event processor
	 * @param callback $a_processor
	 * @return ZDispatcher
	 */
	public function & removeProcessor( $a_processor )
	{
		if(!is_callable($a_processor))
			return $this;

		foreach($this->m_processor as $index => $processor)
		{
			if($processor !== $a_processor)
				continue;

			unset($this->m_processor[$index]);
		}
		return $this;
	}

	/**
	 * Do event processing
	 * @param ZEvent $a_event
	 */
	public function _eventProcessor( ZEvent & $a_event )
	{
		$name = $a_event->name;
		$expr = ( $name != 'preNotify' && $name != 'postNotify' );

		if($expr)
		{
			$n = new ZEvent($this, 'preNotify', $name, $a_event->data);
			$this->notify($n);
		}

		foreach($this->getListeners($name) as $listener)
			call_user_func($listener, $a_event);

		foreach($this->m_processor as $processor)
			call_user_func($processor, $a_event);

		if($expr)
		{
			$n = new ZEvent($this, 'postNotify', $name, $a_event->data);
			$this->notify($n);
		}
	}

	/**
	 * Start notify event listeners
	 * @param ZEvent $a_event
	 * @return ZDispatcher
	 */
	public function & notify( ZEvent & $a_event )
	{
		$this->_eventProcessor($a_event);
		$a_event = null;
		unset($a_event);
		return $this;
	}

	/**
	 * Emit event to listeners
	 * @param ZEvent $a_event
	 * @return ZDispatcher
	 */
	public function & emit( ZEvent & $a_event )
	{
		$this->_eventProcessor($a_event);
		$a_event = null;
		unset($a_event);
		return $this;
	}

}
