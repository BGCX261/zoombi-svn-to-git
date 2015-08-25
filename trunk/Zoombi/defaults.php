<?php

/*
 * File: defaults.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Default configuration for Zoombi Application
 */

return array(
	'database' => null,
	'mode' => 'normal', // normal|debug
	'expiretime' => 60,
	'output' => true,
	'autorun' => true,
	'showerror' => true,
	'showtrace' => true,
	'path' => array(
		'module' => Zoombi_Module::DEFAULT_MODULE_DIR,
		'controller' => Zoombi_Module::DEFAULT_CONTROLLER_DIR,
		'plugin' => Zoombi_Module::DEFAULT_PLUGIN_DIR,
		'view' => Zoombi_Module::DEFAULT_VIEW_DIR,
		'model' => Zoombi_Module::DEFAULT_MODEL_DIR,
		'helper' => Zoombi_Module::DEFAULT_HELPER_DIR,
		'action' => Zoombi_Module::DEFAULT_ACTION_DIR,
		'config' => Zoombi_Module::DEFAULT_CONFIG_DIR
	),
	'module' => array(
		'default_name' => Zoombi_Module::DEFAULT_MODULE_NAME,
		'file_prefix' => Zoombi_Module::DEFAULT_MODULE_FILE_PREFIX,
		'file_suffix' => Zoombi_Module::DEFAULT_MODULE_FILE_SUFFIX,
		'class_prefix' => Zoombi_Module::DEFAULT_MODULE_CLASS_PREFIX,
		'class_suffix' => Zoombi_Module::DEFAULT_MODULE_CLASS_SUFFIX,
	),
	'controller' => array(
		'default_name' => Zoombi_Module::DEFAULT_CONTROLLER_NAME,
		'file_prefix' => Zoombi_Module::DEFAULT_CONTROLLER_FILE_PREFIX,
		'file_suffix' => Zoombi_Module::DEFAULT_CONTROLLER_FILE_SUFFIX,
		'class_prefix' => Zoombi_Module::DEFAULT_CONTROLLER_CLASS_PREFIX,
		'class_suffix' => Zoombi_Module::DEFAULT_CONTROLLER_CLASS_SUFFIX,
		'action_prefix' => Zoombi_Module::DEFAULT_CONTROLLER_METHOD_PREFIX,
		'action_suffix' => Zoombi_Module::DEFAULT_CONTROLLER_METHOD_SUFFIX
	),
	'action' => array(
		'file_prefix' => Zoombi_Module::DEFAULT_ACTION_FILE_PREFIX,
		'file_suffix' => Zoombi_Module::DEFAULT_ACTION_FILE_SUFFIX,
		'class_prefix' => Zoombi_Module::DEFAULT_ACTION_CLASS_PREFIX,
		'class_suffix' => Zoombi_Module::DEFAULT_ACTION_CLASS_SUFFIX,
		'action_prefix' => Zoombi_Module::DEFAULT_ACTION_METHOD_PREFIX,
		'action_suffix' => Zoombi_Module::DEFAULT_ACTION_METHOD_SUFFIX
	),
	'plugin' => array(
		'file_prefix' => Zoombi_Module::DEFAULT_PLUGIN_FILE_PREFIX,
		'file_suffix' => Zoombi_Module::DEFAULT_PLUGIN_FILE_SUFFIX,
		'class_prefix' => Zoombi_Module::DEFAULT_PLUGIN_CLASS_PREFIX,
		'class_suffix' => Zoombi_Module::DEFAULT_PLUGIN_CLASS_SUFFIX,
		'action_prefix' => Zoombi_Module::DEFAULT_PLUGIN_METHOD_PREFIX,
		'action_suffix' => Zoombi_Module::DEFAULT_PLUGIN_METHOD_SUFFIX
	),
	'model' => array(
		'file_prefix' => Zoombi_Module::DEFAULT_MODEL_FILE_PREFIX,
		'file_suffix' => Zoombi_Module::DEFAULT_MODEL_FILE_SUFFIX,
		'class_prefix' => Zoombi_Module::DEFAULT_MODEL_CLASS_PREFIX,
		'class_suffix' => Zoombi_Module::DEFAULT_MODEL_CLASS_SUFFIX,
	),
	'view' => array(
		'file_prefix' => Zoombi_Module::DEFAULT_VIEW_FILE_PREFIX,
		'file_suffix' => Zoombi_Module::DEFAULT_VIEW_FILE_SUFFIX,
		'extension' => Zoombi_Module::DEFAULT_VIEW_FILE_EXT
	),
	'helper' => array(
		'file_prefix' => Zoombi_Module::DEFAULT_HELPER_FILE_PREFIX,
		'file_suffix' => Zoombi_Module::DEFAULT_HELPER_FILE_SUFFIX,
		'class_prefix' => Zoombi_Module::DEFAULT_HELPER_CLASS_PREFIX,
		'class_suffix' => Zoombi_Module::DEFAULT_HELPER_CLASS_SUFFIX
	)
);