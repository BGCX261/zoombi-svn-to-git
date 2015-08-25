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
abstract class ZController extends ZApplicationObject
{

	/**
	 * Blocks
	 * @var array
	 */
	private $m_block;
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
		$this->m_block = array( );
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
					$action = $this->getModule()->getLoader()->action($name);
					$action->setController($this);
					$this->m_actions[$key] = & $action;
				}
				catch( ZActionException $e )
				{
					if( $this->getModule()->isMode(ZModule::MODE_DEBUG) )
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
					$this->addModel($name);
				}
				catch( ZModelException $e )
				{
					if( $this->getModule()->isMode(ZModule::MODE_DEBUG) )
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

				$mod = $this->getModule();

				if( substr($name, 0, 1) == Zoombi::SS )
				{
					$mod = Zoombi::getApplication();
					$name = substr($name, 1);
				}

				try
				{
					$mod->getLoader()->helper($name);
				}
				catch( ZHelperException $e )
				{
					if( $this->getModule()->isMode(ZModule::MODE_DEBUG) )
						throw new ZControllerException($e->getMessage(), ZControllerException::EXC_HELPER);
				}
			}
			unset($this->helper);
		}
	}

	/**
	 * Desctructor
	 */
	public function __destruct()
	{
		foreach( $this->m_block as &$b )
			unset($b);

		foreach( $this->m_actions as &$a )
			unset($a);
	}

	/**
	 * Add ZBlockView instance by name
	 * @param string $a_name
	 * @return ZController
	 */
	public function & addBlock( $a_name )
	{
		switch( gettype($a_name) )
		{
			case 'int':
			case 'string':
				$name = trim((string)$a_name);
				$exp = explode(' ', $name);
				if( count($exp) > 1 )
					foreach( $exp as $a )
						$this->addBlock($a);

				$file = null;
				$file = $this->getModule()->getLoader()->view($name);
				if( !$file OR empty($file) )
					throw new Exception('Empty block file', E_USER_ERROR);
				$this->setBlock($name, new ZViewBlock($file));
				break;

			case 'array':
				foreach( $a_name as $a )
					$this->addBlock($a);
				break;
		}
		return $this;
	}

	/**
	 * Set ZBlockView instance by name
	 * @param string $a_name
	 * @param ZViewBlock $a_block
	 * @return ZController
	 */
	public function & setBlock( $a_name, ZViewBlock & $a_block )
	{
		$this->m_block[trim((string)$a_name)] = $a_block->setThis($this);
		return $this;
	}

	/**
	 * Set array of ZBlockView instances
	 * @param array $a_blocks
	 * @return ZController
	 */
	public function & setBlocks( array & $a_blocks )
	{
		$this->m_block = array( );
		foreach( $a_blocks as $name => $block )
			$this->setBlock($name, $block);
		return $this;
	}

	/**
	 * Check for ZBlockView instance loaded
	 * @param string $a_name
	 * @return bool
	 */
	public function hasBlock( $a_name )
	{
		return isset($this->m_block[trim((string)$a_name)]);
	}

	/**
	 * Get ZBlockView instance by name
	 * @param string $a_name
	 * @return ZBlockView
	 */
	public function & getBlock( $a_name )
	{
		if( $this->hasBlock($a_name) )
			return $this->m_block[trim((string)$a_name)];
		return Zoombi::null();
	}

	/**
	 * Remove ZBlockView instance by name
	 * @param string $a_name
	 * @return ZController
	 */
	public function & removeBlock( $a_name )
	{
		if( $this->hasBlock($a_name) )
			unset($this->m_block[trim((string)$a_name)]);
		return $this;
	}

	/**
	 * Get or load ZViewBlock instance
	 * @param string $a_name
	 * @return ZViewBlock
	 */
	public function & block( $a_name )
	{
		if( !$this->hasBlock($a_name) )
		{
			try {
				$this->addBlock($a_name);
			} catch( Exception $e ) {
				if( $this->getModule()->isMode(ZModule::MODE_DEBUG) )
					throw new ZControllerException('Block "' . $a_name . '" is not found', ZControllerException::EXC_BLOCK);

				return Zoombi::null();
			}
		}
		return $this->getBlock($a_name);
	}

	/**
	 * Add ZModel instance
	 * @param string|array $a_model
	 * @return ZController
	 */
	function & addModel( $a_model )
	{
		switch( gettype($a_model) )
		{
			case 'string':
				if( $this->hasModel($a_model) )
					break;

				$m = null;
				if( substr($a_model, 0, 1) == Zoombi::SS )
				{
					$a_model = substr($a_model, 1);
					$m = Zoombi::getApplication()->getLoader()->model($a_model);
				}
				else
					$m = $this->getModule()->getLoader()->model($a_model);

				if( $m )
					$this->setModel($a_model, $m);

				break;

			case 'array':
				foreach( $a_model as $model )
					$this->addModel($model);
				break;
		}
		return $this;
	}

	/**
	 * Set ZModel instance
	 * @param string $a_name
	 * @param ZModel $a_model
	 * @return ZController
	 */
	function & setModel( $a_name, ZModel & $a_model )
	{
		$this->m_models[(string)$a_name] = $a_model;
		return $this;
	}

	/**
	 * Set ZModel instances
	 * @param array $a_models
	 * @return ZController
	 */
	function & setModels( array $a_models )
	{
		foreach( $a_models as $name => $model )
			$this->setModel($name, $model);

		return $this;
	}

	/**
	 * Check if ZMoedel instance is load
	 * @param  string $a_name
	 * @return bool
	 */
	function hasModel( $a_name )
	{
		return isset($this->m_models[$a_name]);
	}

	/**
	 * Get ZModel instance by name
	 * @param string $a_name
	 * @return ZModel
	 */
	function & getModel( $a_name )
	{
		if( $this->hasModel($a_name) )
			return $this->m_models[$a_name];

		return Zoombi::$null;
	}

	/**
	 * Forward controller/action call to another
	 * @return ZController
	 */
	protected function & forward( $a_to )
	{
		return $this->setForwarder($this)->getModule()->runRoute($a_to);
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
	public function & __get( $a_property )
	{
		if( $this->hasModel($a_property) )
			return $this->getModel($a_property);

		return parent::__get($a_property);
	}

	/**
	 * Controller caller
	 * @param string $a_name
	 * @param array $a_arguments
	 * @return mixed
	 */
	public function __call( $a_name, $a_arguments )
	{
		$pref = $this->getModule()->getConfig()->getValue('controller.action_prefix');
		$suff = $this->getModule()->getConfig()->getValue('controller.action_suffix');

		$name = $a_name;

		$pc = 1;
		$sc = 1;

		if( strlen($pref) > 0 && strpos($name, $pref) !== false )
			$name = str_ireplace($pref, '', $name, $pc);

		if( strlen($suff) > 0 && strpos($name, $suff) !== false )
			$name = str_ireplace($suff, '', $name, $sc);

		if( $pc AND $sc )
			if( $this->hasAction($name) )
				return $this->requestAction($name, $a_arguments);

		$this->triggerError('Method not found: ' . $a_name, E_USER_WARNING);
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
		$action = (string)$a_action;
		if( isset($this->m_actions[$action]) )
			return true;

		return method_exists($this,
				$this->getModule()->getConfig()->getValue('controller.action_prefix') .
				$action .
				$this->getModule()->getConfig()->getValue('controller.action_suffix', 'Action')
		);
	}

	/**
	 * Request controller action
	 * @param string $a_action
	 * @param array $a_params
	 * @return mixed
	 */
	public function requestAction( $a_action, array $a_params = array( ), & $a_content = null )
	{
		$return = null;
		
		if( func_num_args( ) > 2 )
				ob_start( );

		try
		{
			if( $this->hasAction($a_action) == false )
				throw new ZControllerException("Action '{$a_action}' not found in controller '{$this->getName()}'", ZControllerException::EXC_ACTION);

			$action_name = $this->getModule()->getConfig()->getValue('controller.action_prefix', ZModule::DEFAULT_CONTROLLER_METHOD_PREFIX) .
					(string)$a_action . $this->getModule()->getConfig()->getValue('controller.action_suffix', ZModule::DEFAULT_CONTROLLER_METHOD_SUFFIX);

			if( method_exists($this, $action_name) )
			{
				if( method_exists($this, 'before') )
					$this->before( $a_action );

				//Zoombi::getApplication()->m_app_route_deep++;
				$return = call_user_func_array(array( &$this, $action_name ), $a_params);



	/*
				if( Zoombi::getApplication()->m_route_deep == 1 )
				{
					try
					{
						$d = new ZRegistry($return);
						if( !$d->has('content') )
							$d->content = $content;

						$content = $ctl->render(null,$d,true);
						unset($d);
					}
					catch( ZControllerException $e )
					{
						/*if( $e->getCode() != ZControllerException::EXC_LOAD )
							throw new ZException( $e->getMessage(), $e->getCode() )/
					}
				}
	*/
				if( method_exists($this, 'after') )
					$this->after( $a_action );
			}
			else
			{
				$action = & $this->m_actions[$a_action];
				if( !$action )
					throw new ZControllerException("Action '{$a_action}' not attached to controller '{$this->getName()}'", ZControllerException::EXC_ACTION);

				$return = call_user_func_array(array( &$action, 'run' ), $a_params);
			}
		
		}
		catch( Exception $e) {
			$this->triggerError($e);
		}

		if( func_num_args( ) > 2 ){
			$a_content = ob_get_contents();
			ob_end_clean();
		}
		return $return;
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
			$action = $this->getModule()->getLoader()->action($name);
			$this->m_actions[$name] = & $action;
		}
		catch( ZControllerException $e )
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
			if( !$this instanceof ZPlugin )
			{
				$route = $this->isForwarded() ? Zoombi::getApplication()->getRouter()->getForward() : $this->router->getCurrent();
				$a_view = implode(Zoombi::DS, array( $route->getController(), $route->getAction() ));
			}
			else
			{
				$a_view = 'Plugin' . Zoombi::SS . $this->getName();
			}
		}

		$v = new ZView($a_view, $a_data);
		$o = $this->renderView($v, $a_return);
		unset($v);
		return $o;
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
	private function renderView( ZView & $a_view, $a_return = false )
	{
		$view = $a_view->getView();

		if( file_exists($view) )
			return $a_view->display($a_return);

		$oldload = $this->getModule()->getLoader();
		if( substr($view, 0, 1) == Zoombi::SS )
		{
			$view = substr($view, 1);
			$this->getModule()->setLoader( Zoombi::getApplication()->getLoader() );
		}

		$a_view->setThis($this);

		if( !file_exists($view) )
		{
			try
			{
				$a_view->setView($this->getModule()->getLoader()->view($view));
			}
			catch( ZViewException $e )
			{
				$this->getModule()->setLoader($oldload);
				$this->triggerError($e);
				return;
			}
		}
		$o = $a_view->display($a_return);
		$this->getModule()->setLoader($oldload);
		return $o;
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
			if( $e->getCode() == ZControllerException::EXC_NO_METHOD )
			{

			}
			else
			{
				if( $this->getModule()->isMode(ZModule::MODE_DEBUG) )
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
		$route = $this->getModule()->getRoute();
		$path = $route->getModule() . Zoombi::SS . $route->getController() . Zoombi::SS . $route->getAction();
		if( !$a_params )
			return $path;

		return (string)$route;
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
