<?php

return array(
	'database' => null,
	'mode' => 'normal', // normal|debug
	'expiretime' => 60,
	'output' => true,
	'autorun' => true,
	'showerror' => true,
	'showtrace' => true,
	'path' => array(
		'module' => ZModule::DEFAULT_MODULE_DIR,
		'controller' => ZModule::DEFAULT_CONTROLLER_DIR,
		'plugin' => ZModule::DEFAULT_PLUGIN_DIR,
		'view' => ZModule::DEFAULT_VIEW_DIR,
		'model' => ZModule::DEFAULT_MODEL_DIR,
		'helper' => ZModule::DEFAULT_HELPER_DIR,
		'action' => ZModule::DEFAULT_ACTION_DIR,
		'config' => ZModule::DEFAULT_CONFIG_DIR
	),
	'module' => array(
		'default_name' => ZModule::DEFAULT_MODULE_NAME,
		'file_prefix' => ZModule::DEFAULT_MODULE_FILE_PREFIX,
		'file_suffix' => ZModule::DEFAULT_MODULE_FILE_SUFFIX,
		'class_prefix' => ZModule::DEFAULT_MODULE_CLASS_PREFIX,
		'class_suffix' => ZModule::DEFAULT_MODULE_CLASS_SUFFIX,
	),
	'controller' => array(
		'file_prefix' => ZModule::DEFAULT_CONTROLLER_FILE_PREFIX,
		'file_suffix' => ZModule::DEFAULT_CONTROLLER_FILE_SUFFIX,
		'class_prefix' => ZModule::DEFAULT_CONTROLLER_CLASS_PREFIX,
		'class_suffix' => ZModule::DEFAULT_CONTROLLER_CLASS_SUFFIX,
		'action_prefix' => '',
		'action_suffix' => ''
	),
	'action' => array(
		'file_prefix' => ZModule::DEFAULT_ACTION_FILE_PREFIX,
		'file_suffix' => ZModule::DEFAULT_ACTION_FILE_SUFFIX,
		'class_prefix' => ZModule::DEFAULT_ACTION_CLASS_PREFIX,
		'class_suffix' => ZModule::DEFAULT_ACTION_CLASS_SUFFIX,
		'action_prefix' => ZModule::DEFAULT_ACTION_METHOD_PREFIX,
		'action_suffix' => ZModule::DEFAULT_ACTION_METHOD_SUFFIX
	),
	'plugin' => array(
		'file_prefix' => ZModule::DEFAULT_PLUGIN_FILE_PREFIX,
		'file_suffix' => ZModule::DEFAULT_PLUGIN_FILE_SUFFIX,
		'class_prefix' => ZModule::DEFAULT_PLUGIN_CLASS_PREFIX,
		'class_suffix' => ZModule::DEFAULT_PLUGIN_CLASS_SUFFIX,
		'action_prefix' => ZModule::DEFAULT_PLUGIN_METHOD_PREFIX,
		'action_suffix' => ZModule::DEFAULT_PLUGIN_METHOD_SUFFIX
	),
	'model' => array(
		'file_prefix' => ZModule::DEFAULT_MODEL_FILE_PREFIX,
		'file_suffix' => ZModule::DEFAULT_MODEL_FILE_SUFFIX,
		'class_prefix' => ZModule::DEFAULT_MODEL_CLASS_PREFIX,
		'class_suffix' => ZModule::DEFAULT_MODEL_CLASS_SUFFIX,
	),
	'view' => array(
		'file_prefix' => ZModule::DEFAULT_VIEW_FILE_PREFIX,
		'file_suffix' => ZModule::DEFAULT_VIEW_FILE_SUFFIX,
		'extension' => ZModule::DEFAULT_VIEW_FILE_EXT
	),
	'helper' => array(
		'file_prefix' => ZModule::DEFAULT_HELPER_FILE_PREFIX,
		'file_suffix' => ZModule::DEFAULT_HELPER_FILE_SUFFIX,
		'class_prefix' => ZModule::DEFAULT_HELPER_CLASS_PREFIX,
		'class_suffix' => ZModule::DEFAULT_HELPER_CLASS_SUFFIX
	)
);