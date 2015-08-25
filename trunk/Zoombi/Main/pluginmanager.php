<?php

/*
 * File: pluginmanager.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */


/**
 * Description of Zoombi_PluginManager
 *
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
class Zoombi_PluginManager extends Zoombi_Component
{

	/**
	 * Array of plugins
	 * @var array
	 */
	private $m_plugins;

	/**
	 * Plugins target
	 * @var Zoombi_Dispatcher
	 */
	private $m_target;

	/**
	 * Action prefix
	 * @var string
	 */
	private $m_prefix;

	/**
	 * Action suffix
	 * @var string
	 */
	private $m_suffix;

	/**
	 * Constructor
	 * @param Zoombi_Object $a_parent
	 * @param string $a_name
	 */
	function __construct( Zoombi_Object & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		$this->m_plugins = array();
		if($a_parent)
			$this->setTarget($a_parent->getDispatcher());
	}

	/**
	 * Set listened target
	 * @param Zoombi_Dispatcher $a_target
	 */
	function setTarget( Zoombi_Dispatcher & $a_target )
	{
		if($this->m_target)
		{
			$this->m_target->removeProcessor(array($this, 'processEvent'));
			unset($this->m_target);
		}

		$this->m_target = $a_target;

		$this->m_target->addProcessor(array($this, 'processEvent'));

		$this->m_prefix = $this->getModule()->getConfig()->getValue('plugin.action_prefix', Zoombi_Module::DEFAULT_PLUGIN_METHOD_PREFIX);
		$this->m_suffix = $this->getModule()->getConfig()->getValue('plugin.action_suffix', Zoombi_Module::DEFAULT_PLUGIN_METHOD_SUFFIX);
	}

	/**
	 * Event processor
	 * @internal
	 * @param Zoombi_Event $a_event
	 */
	public function processEvent( Zoombi_Event & $a_event )
	{
		$a = $a_event->name;
		$action = $this->m_prefix . (string)$a . $this->m_suffix;
		foreach($this->m_plugins as &$p)
		{
			if(!method_exists($p, $action) || !$p)
				continue;

			$callback = array($p, $action);
			if(is_callable($callback))
				call_user_func_array($callback, $a_event->data);
		}
	}

	/**
	 * Get all plugins
	 * @return array
	 */
	public final function & getPlugins()
	{
		return $this->m_plugins;
	}

	/**
	 * Get plugin
	 * @return Zoombi_Plugin
	 */
	public final function & getPlugin( $a_plugin )
	{
		switch(gettype($a_plugin))
		{
			case 'int':
			case 'string':
				if(isset($this->m_plugins[$a_plugin]))
					return $this->m_plugins[$a_plugin];
				break;

			case 'object':
				if($a_plugin instanceof Zoombi_Plugin)
					foreach($this->m_plugins as $k => $plg)
						if($plg === $a_plugin)
							return $this->m_plugins[$k];

				break;
		}
		return Zoombi::null();
	}

	/**
	 * Set plugins
	 * @param array $a_plugins
	 * @return Zoombi_PluginManager
	 */
	public final function & setPlugins( array $a_plugins )
	{
		foreach($a_plugins as $plugin)
			$this->addPlugin($plugin);

		return $this;
	}

	/**
	 * Add plugin to stack
	 * @param mixed $a_plugin
	 * @return Zoombi_PluginManager
	 */
	public final function & addPlugin( $a_plugin )
	{
		$plg = $a_plugin;
		if(is_string($a_plugin))
			$plg = $this->getModule()->getLoader()->plugin($a_plugin);

		if($plg instanceof Zoombi_Plugin)
			$this->m_plugins[$plg->getName()] = & $plg;

		return $this;
	}

	/**
	 * Remove plugin from stack
	 * @param int|Zoombi_Plugin $a_plugin
	 * @return Zoombi_PluginManager
	 */
	public final function & removePlugin( $a_plugin )
	{
		if(is_integer($a_plugin))
			$id = $a_plugin;
		else if(is_string($a_plugin))
			$id = intval($a_plugin);
		else if($a_plugin instanceof Zoombi_Plugin)
		{
			foreach($this->m_plugins as $k => $plugin)
			{
				if($plugin === $a_plugin)
				{
					unset($this->m_plugins[$k]);
					return $this;
				}
			}
			return $this;
		}

		if(isset($this->m_plugins[$id]) OR array_key_exists($id, $this->m_plugins))
			unset($this->m_plugins[$id]);

		return $this;
	}

}
