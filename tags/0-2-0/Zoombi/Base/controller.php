<?php

/*
 * File: controller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Controller base class
 * @property mixed $model
 * @property mixed $action
 * @property mixed $helper
 */
abstract class ZController extends ZObject
{

	/**
	 * Forwarded controller
	 * @var ZController
	 */
	private $m_forwarder;
	/**
	 * Controller actions
	 * @var array
	 */
	private $m_actions = array( );
	/**
	 * Controller models
	 * @var array
	 */
	private $m_models = array( );

	/**
	 * Constructor
	 * @param ZObject $a_parent
	 * @param string $a_name
	 */
	function __construct( ZObject & $a_parent = null, $a_name = null )
	{
		parent::__construct($a_parent, $a_name);
		if( isset($this->action) )
		{
			$actions = array( );

			switch( gettype($this->action) )
			{
				case 'string':
					$actions = explode(' ', $this->action);
					break;

				case 'array':
					$actions = & $this->action;
					break;
			}

			foreach( $actions as $k => $v )
			{
				$key = trim((string)$k);
				$name = trim((string)$v);

				if( empty($name) )
					continue;

				if( empty($key) OR is_numeric($key) )
					$key = $name;

				try
				{
					$action = $this->load->action($name);
					$action->setController($this);
					$this->m_actions[$key] = & $action;
				}
				catch( ZActionException $e )
				{
					if( $this->application->isMode(ZApplication::MODE_DEBUG) )
						throw new ZControllerException($e->getMessage(), ZControllerException::EXC_MODEL);
				}
			}
			unset($this->actions);
		}

		if( isset($this->model) )
		{
			$models = array( );
			switch( gettype($this->model) )
			{
				default:
					throw new ZControllerException("Controller '{$this->getName()}':  has wrong model type.", ZControllerException::EXC_MODEL);
					break;

				case 'string':
					$models = explode(' ', $this->model);
					break;

				case 'array':
					$models = & $this->model;
					break;
			}

			foreach( $models as $m )
			{
				$name = trim((string)$m);

				if( empty($name) )
					continue;

				try
				{
					$model = $this->load->model($name);
					$model->setController($this);
					$this->m_models[$name] = & $model;
				}
				catch( ZModelException $e )
				{
					if( $this->application->isMode(ZApplication::MODE_DEBUG) )
						throw new ZControllerException($e->getMessage(), ZControllerException::EXC_MODEL);
				}
			}
			unset($this->model);
		}

		if( isset($this->helper) )
		{
			$helpers = array( );
			switch( gettype($this->helper) )
			{
				default:
					throw new ZControllerException("Controller '{$this->getName()}':  has wrong helper type.", ZControllerException::EXC_MODEL);
					break;

				case 'string':
					$helpers = explode(' ', trim($this->helper));
					break;

				case 'array':
					$helpers = & $this->helper;
					break;
			}

			foreach( $helpers as $h )
			{
				$name = trim((string)$h);

				if( empty($name) )
					continue;

				try
				{
					$this->load->helper($name);
				}
				catch( ZHelperException $e )
				{
					if( $this->application->isMode(ZApplication::MODE_DEBUG) )
						throw new ZControllerException($e->getMessage(), ZControllerException::EXC_HELPER);
				}
			}
			unset($this->helper);
		}
	}

	/**
	 * Forward controller/action call to another
	 * @return ZController
	 */
	protected function & forward( $a_to )
	{
		$route = new ZRoute($a_to);
		//$route = clone $this->router->getCurrent();
		//$route->setController($rt->controller)->setAction($rt->action);

		$ret = null;
		try
		{
			$ctl = $this->load->controller($route->controller);
		}
		catch( ZControllerException $e )
		{
			if( Zoombi::getApplication()->isMode(ZApplication::MODE_DEBUG) )
				trigger_error($e->getMessage(), Zoombi::EXC_ERROR);
		}

		if( !$ctl )
			return;

		$old = clone $this->router->setForward($route)->getCurrent();
		$this->router->setCurrent($route);

		try
		{
			$ret = $ctl->setForwarder($this)->requestAction(
							$route->getAction(),
							$route->getParams()
			);
		}
		catch( ZControllerException $e )
		{
			if( $e->getCode() == ZControllerException::EXC_ACTION )
			{
				trigger_error($e->getMessage());
			}
			else
			{
				if( $this->application->isMode(ZApplication::MODE_DEBUG) )
					throw $e;
			}
		}
		$this->router->setCurrent($old);
		return $ret;
	}

	/**
	 * Set forwarded controller
	 * @param ZController $a_forwarder
	 * @return ZController
	 */
	public function & setForwarder( ZController & $a_forwarder )
	{
		$this->m_forwarder = $a_forwarder;
		return $this;
	}

	/**
	 * Get forwarded controller
	 * @return ZController
	 */
	public function & getForwarder()
	{
		return $this->m_forwarder;
	}

	/**
	 * Chek if this controller forwarder from another
	 * @return bool
	 */
	public function isForwarded()
	{
		return ($this->m_forwarder);
	}

	/**
	 * Get property
	 * @param string $a_property
	 * @return mixed
	 */
	public function __get( $a_property )
	{
		if( isset($this->m_models[$a_property]) || array_key_exists($a_property, $this->m_models) )
			return $this->m_models[$a_property];

		return parent::__get($a_property);
	}

	/**
	 * Controller setter
	 * @param mixed $a_name
	 * @param mixed $a_value
	 * @return mixed
	 */
	public function __set( $a_name, $a_value )
	{
		switch( $a_name )
		{
			case 'load':
			case 'registry':
			case 'language':
			case 'application':
				return;
		}
		parent::__set($a_name, $a_value);
	}

	/**
	 * Controller caller
	 * @param string $a_name
	 * @param array $a_arguments
	 * @return mixed
	 */
	public function __call( $a_name, $a_arguments )
	{
		if( strpos($a_name, 'controller.class_prefix') === 0 AND strrpos($a_name, 'controller.class_suffix') === 0 )
			return $this->requestAction($a_name, $a_arguments);

		trigger_error('Method not found: ' . $a_name, E_USER_WARNING);
	}

	/**
	 * Call controller action
	 * @param string $a_action
	 * @param array $a_params
	 * @return mixed
	 */
	public function callAction( $a_action, array $a_params = array( ) )
	{
		return $this->requestAction($a_action, $a_params);
	}

	/**
	 * Check if controller has a action
	 * @param string $a_action
	 * @return bool
	 */
	public function hasAction( $a_action )
	{
		$action_name = Zoombi::config('controller.action_prefix') . (string)$a_action . Zoombi::config('controller.action_suffix', 'Action');
		return method_exists($this, $action_name) || isset($this->m_actions[$a_action]);
	}

	/**
	 * Request controller action
	 * @param string $a_action
	 * @param array $a_params
	 * @return mixed
	 */
	public function requestAction( $a_action, array $a_params = array( ) )
	{
		if( !$this->hasAction($a_action) )
			throw new ZControllerException("Controller '{$this->getName()}' has no action '{$a_action}'", ZControllerException::EXC_ACTION);

		$action_name = Zoombi::config('controller.action_prefix') . (string)$a_action . Zoombi::config('controller.action_suffix', 'Action');

		if( method_exists($this, $action_name) )
			return call_user_func_array(array( &$this, $action_name ), $a_params);
		else
		{
			if( !isset($this->m_actions[$a_action]) )
				return;

			$action = & $this->m_actions[$a_action];
			if( !$action )
				throw new ZActionException('Action error', ZLoader::EXC_EMPTY);

			$action->setController($this);
			return call_user_func_array(array( &$action, 'run' ), $a_params);
		}
	}

	/**
	 * Load action to controller
	 * @param string $a_action
	 * @return ZController
	 */
	public final function & loadAction( $a_action )
	{
		$name = (string)$a_action;
		if( isset($this->m_actions[$name]) )
			return $this->m_actions[$name];

		$action = null;
		try
		{
			$action = $this->load->action($name);
			$this->m_actions[$name] = & $action;
		}
		catch( ZActionException $e )
		{
			throw new ZControllerException($e->getMessage(), $e->getCode());
		}
		return $action;
	}

	/**
	 * Render view by name
	 * @param string $a_view
	 * @param array $a_data
	 * @param bool $a_return
	 * @return mixed
	 */
	public function render( $a_view = null, $a_data = null, $a_return = false )
	{
		if( empty($a_view) )
		{
			if( !($this instanceof ZPlugin) AND $this instanceof ZController )
			{
				$route = $this->isForwarded() ? $this->router->getForward() : $this->router->getCurrent();
				$a_view = implode(Zoombi::DS, array( $route->getController(), $route->getAction() ));
			}
			else if( $this instanceof ZPlugin AND $this instanceof ZController )
			{
				$a_view = 'Plugin' . Zoombi::SS . $this->getName();
			}
		}
		return $this->renderView(new ZView($this, $a_view), $a_data, $a_return);
	}

	/**
	 * Render each item of array $a_each to $a_view view
	 * @param string $a_view
	 * @param array $a_each
	 * @param bool $a_return
	 * @return string
	 */
	public function renderEach( $a_view, array $a_each, $a_return = false )
	{
		return $this->renderEachSeparated($a_view, $a_each, null, $a_return);
	}

	/**
	 * Render each item of array $a_each to $a_view view and separate by separator $a_separator
	 * @param string $a_view View
	 * @param array $a_each Array of elements
	 * @param string $a_separator Element separator
	 * @param bool $a_return Return rendered string flag
	 * @return string
	 */
	public function renderEachSeparated( $a_view, array $a_each, $a_separator, $a_return = false )
	{
		$o = array( );
		foreach( $a_each as $e )
			$o[] = $this->render($a_view, $e, true);
		$out = implode($a_separator, $o);

		if( $a_return )
			return $out;
		echo $out;
	}

	/**
	 * Render view
	 * @param ZView $a_view
	 * @param array $a_data
	 * @param bool $a_return
	 * @return midex
	 */
	private function renderView( ZView & $a_view, $a_data = null, $a_return = false )
	{
		if( $a_data )
			$a_view->setDataRef($a_data);
		return $a_view->display($a_return);
	}

	/**
	 * Get output buffer data action controller
	 * @param string $a_action Action name
	 * @param <type> $_ Arguments
	 * @return string
	 */
	public function actionContent( $a_action, $_ = null )
	{
		$args = func_get_args();
		$action = array_shift($args);
		ob_start();
		try
		{
			$this->requestAction($action, $args);
		}
		catch( ZControllerException $e )
		{
			if( $e->getCode() == ZControllerException::EXC_ACTION )
			{

			}
			else
			{
				if( $this->application->isMode(ZApplication::MODE_DEBUG) )
					throw $e;
			}
		}
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Get url to current controller/action
	 * @param bool $a_parent If true return full current url
	 * @return string
	 */
	public function referer( $a_params = false )
	{
		$route = $this->router->getCurrent();
		return $this->application->getBaseUrl() . ( $a_params ? $route : $route->getController() . Zoombi::US . $route->getAction() );
	}

	/**
	 * Quit from current contrller
	 * @param bool $a_output
	 */
	public function quit( $a_output = false )
	{
		throw new ZControllerException('Quit', $a_output ? ZControllerException::EXC_QUIT_OUTPUT : ZControllerException::EXC_QUIT);
	}

}
