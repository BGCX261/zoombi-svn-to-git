<?php

/*
 * File: interfaces.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * Singleton inteface
 */
interface IZSingleton
{

	static public function & getInstance();
}

interface IZPluginManager
{

	/**
	 * Get all plugins
	 * @return array
	 */
	function & getPlugins();

	/**
	 * Get plugin
	 * @return ZPlugin
	 */
	function & getPlugin( $a_plugin );

	/**
	 * Set plugins
	 * @param array $a_plugins
	 * @return ZPluginManager
	 */
	function & setPlugins( array $a_plugins );

	/**
	 * Add plugin to stack
	 * @param mixed $a_plugin
	 * @return ZPluginManager
	 */
	function & addPlugin( $a_plugin );

	/**
	 * Remove plugin from stack
	 * @param int|ZPlugin $a_plugin
	 * @return ZPluginManager
	 */
	function & removePlugin( $a_plugin );
}
