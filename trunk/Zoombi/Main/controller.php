<?php

/*
 * File: controller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * Controller base class
 * @property object $model
 * @property mixed $action
 * @property mixed $helper
 */
class Zoombi_Controller extends Zoombi_Component
{

	/**
	 * Layouts
	 * @var array
	 */
	private $m_layouts = array( );

	/**
	 * Forwarded controller
	 * @var Zoombi_Controller
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
	 * Actions map
	 * @var array
	 */
	private $m_actions_map = array( );

	/**
	 * Last requested action
	 * @var string
	 */
	private $m_last_action = null;

	/**
	 * Output buffer data of controller after request processed
	 * @var string
	 */
	public $output = '';

	/**
	 * Return value after request processed 
	 * @var mixed 
	 */
	public $return = null;

	/**
	 * Desctructor
	 */
	public function __destruct()
	{
		foreach( $this->m_layouts as &$b )
			unset($b);

		foreach( $this->m_actions as &$a )
			unset($a);
	}

	/**
	 * Call this before action called
	 */
	protected function before()
	{
		return true;
	}

	/**
	 * Call this after action calles
	 */
	protected function after()
	{
		
	}

	/**
	 * Get string output afrer controller processed request 
	 * @return string 
	 */
	public function getOutput()
	{
		return $this->output;
	}

	/**
	 * Get return result afrer controller processed request 
	 * @return mixed
	 */
	public function getReturn()
	{
		return $this->return;
	}

	/**
	 * Set string output afrer controller processed request 
	 * @param type $a_output
	 * @return Zoombi_Controller 
	 */
	protected function & setOutput( $a_output )
	{
		$this->output = $a_output;
		return $this;
	}

	/**
	 * Set return result afrer controller processed request 
	 * @param type $a_return
	 * @return Zoombi_Controller 
	 */
	protected function & setReturn( $a_return )
	{
		$this->return = $a_return;
		return $this;
	}
	
	public function & addMap( $a_key, $a_value )
	{
		$this->m_actions_map[Zoombi_String::normalize($a_key)] = Zoombi_String::normalize($a_key);
	}
	
	public function & addMaps( array $a_maps )
	{
		foreach( $a_maps as $key => $value )
			$this->addMap( $key, $value );

		return $this;
	}
	
	public function & setMap( $a_key, $a_value )
	{
		$this->m_actions_map[Zoombi_String::normalize($a_key)] = Zoombi_String::normalize($a_value);
		return $this;
	}
	
	public function & setMaps( array $a_maps )
	{
		$this->m_actions_map = array();
		return $this->addMaps($a_maps);
	}
	
	public function hasMap( $a_map )
	{
		$key = Zoombi_String::normalize($a_map);
		return isset($this->m_actions_map[$key]) OR array_key_exists($key, $this->m_actions_map);
	}
	
	public function hasMapTo( $a_to )
	{
		return in_array( Zoombi_String::normalize($a_to) , $this->m_actions_map );
	}

	public function & removeMap( $a_key )
	{
		$keys = func_get_args();
		foreach( $keys as $k )
		{		
			if( $this->hasMap($k) )
				unset( $this->m_actions_map[Zoombi_String::normalize($k)] );
		}		
		return $this;
	}

	/**
	 * Add an action to controller
	 * @param Zoombi_Action $a_action
	 * @return Zoombi_Controller 
	 */
	public function & addAction( $a_action )
	{
		switch( gettype($a_action) )
		{
			case 'string':

				foreach( explode(' ', Zoombi_String::normalize($a_action)) as $v )
				{
					$name = Zoombi_String::normalize($v);
					if( empty($name) OR $this->hasAction($name) )
						continue;

					try
					{
						$action = $this->getModule()->getLoader()->action($name);
						$action->setController($this);
						$this->setAction($name, $action);
					}
					catch( Zoombi_Exception_Action $e )
					{
						if( $this->getModule()->isMode(Zoombi_Module::MODE_DEBUG) )
							$this->triggerError($e);
					}
				}
				break;

			case 'array':
				return $this->setActions($a_action);

			case 'object':
				if( $a_action instanceof Zoombi_Action )
				{
					if( !$this->hasAction($a_action) )
						$this->setAction($a_action->getName(), $a_action);
				}
				break;
		}
		return $this;
	}

	/**
	 * Add many actions to controller
	 * @param array $a_actions
	 * @return Zoombi_Controller 
	 */
	public function & addActions( array $a_actions )
	{
		foreach( $a_actions as $action )
			$this->addAction($action);

		return $this;
	}

	/**
	 * Apply action to controller by giving name
	 * @param string $a_name
	 * @param Zoombi_Action $a_action
	 * @return Zoombi_Controller 
	 */
	public function & setAction( $a_name, Zoombi_Action & $a_action )
	{
		$a_action->setController($this);
		$this->m_actions[Zoombi_String::normalize($a_name)] = $a_action;
		return $this;
	}

	/**
	 * Apply many actions to controller
	 * @param array $a_actions
	 * @return Zoombi_Controller 
	 */
	public function & setActions( array $a_actions )
	{
		$this->m_actions = array( );
		foreach( $a_actions as $name => $action )
			$this->setAction($name, $action);

		return $this;
	}

	/**
	 * Remove action from controller by his name or object
	 * @param string|Zoombi_Action $a_action 
	 */
	public function & removeAction( $a_action )
	{
		switch( gettype($a_action) )
		{
			case 'string':
				$name = Zoombi_String::normalize($a_action);
				if( array_key_exists($name, $this->m_actions) )
					unset($this->m_actions[$name]);
				break;

			case 'object':
				foreach( $this->m_actions as $key => $action )
				{
					if( $action === $a_action )
					{
						unset($this->m_actions[$key]);
						break;
					}
				}
				break;
		}
		return $this;
	}

	/**
	 * Add Zoombi_Layout instance by name
	 * @param string $a_name
	 * @return Zoombi_Controller
	 */
	public function & addLayout( $a_name )
	{
		switch( gettype($a_name) )
		{
			default:
				$name = trim((string)$a_name);
				$exp = explode(' ', $name);
				if( count($exp) > 1 )
					foreach( $exp as $a )
						$this->addLayout($a);

				$file = null;
				$file = $this->getModule()->getLoader()->view($name);
				if( !$file OR empty($file) )
				{
					$b = new Zoombi_Layout();
					$this->setLayout($name, $b);
					$this->triggerError('Empty layout file');
				}
				else
				{
					$b = new Zoombi_Layout($file);
					$this->setLayout($name, $b);
				}
				break;

			case 'array':
				foreach( $a_name as $a )
					$this->addLayout($a);

				break;

			case 'object':
				if( $a_name instanceof Zoombi_View )
				{
					if( !$this->hasLayout($a_name->getName()) )
						$this->setLayout($a_name->getName(), $a_name);
				}
				break;
		}
		return $this;
	}

	/**
	 * Add many layouts to controller
	 * @param array $a_layouts
	 * @return Zoombi_Controller 
	 */
	function addLayouts( array $a_layouts )
	{
		foreach( $a_layouts as $layout )
			$this->addLayout($layout);

		return $this;
	}

	/**
	 * Set Zoombi_Layout instance by name
	 * @param string $a_name
	 * @param Zoombi_Layout $a_layout
	 * @return Zoombi_Controller
	 */
	public function & setLayout( $a_name, Zoombi_Layout & $a_layout )
	{
		$this->m_layouts[Zoombi_String::normalize($a_name)] = $a_layout->setThis($this);
		return $this;
	}

	/**
	 * Remove layout from controller
	 * @param string $a_name
	 * @return Zoombi_Controller 
	 */
	public function & removeLayout( $a_layout )
	{
		switch( gettype($a_layout) )
		{
			case 'string':
				$name = Zoombi_String::normalize($a_layout);
				if( isset($this->m_layouts[$name]) )
					unset($this->m_layouts[$name]);

				break;

			case 'object':
				foreach( $this->m_layouts as $key => $layout )
				{
					if( $layout === $a_layout )
					{
						unset($this->m_layouts[$key]);
						break;
					}
				}
				break;
		}
		return $this;
	}

	/**
	 * Set array of Zoombi_Layout instances
	 * @param array $a_layouts
	 * @return Zoombi_Controller
	 */
	public function & setLayouts( array $a_layouts )
	{
		$this->m_layouts = array( );
		foreach( $a_layouts as $name => $layout )
			$this->setLayout($name, $layout);
		return $this;
	}

	/**
	 * Check for Zoombi_Layout instance loaded
	 * @param string $a_name
	 * @return bool
	 */
	public function hasLayout( $a_name )
	{
		return isset($this->m_layouts[Zoombi_String::normalize($a_name)]);
	}

	/**
	 * Get Zoombi_Layout instance by name
	 * @param string $a_name
	 * @return Zoombi_Layout
	 */
	public function & getLayout( $a_name )
	{
		if( $this->hasLayout($a_name) )
			return $this->m_layouts[Zoombi_String::normalize($a_name)];

		return Zoombi::null();
	}

	/**
	 * Get or load Zoombi_Layout instance
	 * @param string $a_name
	 * @param array|object $a_data
	 * @return Zoombi_Layout
	 */
	public function & layout( $a_name, $a_data = null )
	{
		if( !$this->hasLayout($a_name) )
			$this->addLayout($a_name);

		$b = $this->getLayout($a_name);

		if( func_num_args() > 1 )
			$b->setData($a_data);

		return $b;
	}

	/**
	 * Add Zoombi_Model instance
	 * @param string|array $a_model
	 * @return Zoombi_Controller
	 */
	function & addModel( $a_model )
	{
		switch( gettype($a_model) )
		{
			default:
				$this->triggerError("Controller '{$this->getName()}':  has wrong model type.", Zoombi_Exception_Controller::EXC_MODEL);
				break;

			case 'string':
				$m = $this->loadModel($a_model);
				if( $m )
					$this->setModel($m->getName(), $m );
				
				break;

			case 'object':
				if( $a_model instanceof Zoombi_Model )
					$this->setModel($a_model->getName(), $a_model);
				
				break;
		}
		return $this;
	}

	/**
	 * Add many models to controller
	 * @param array $a_models
	 * @return Zoombi_Controller 
	 */
	function & addModels( array $a_models )
	{
		foreach( $a_models as $name => $model )
		{
			if( is_numeric($name) )
			{
				if( $model instanceof Zoombi_Model )
					$this->setModel( $model->getName(), $model );
				else
					$this->addModel( $model );
			}
			else
			{
				if( $model instanceof Zoombi_Model )
					$this->setModel( $name, $model );
				else
					$this->setModel( $name, $this->loadModel($model) );					
			}
		}
		return $this;
	}
	
	/**
	 * Load model
	 * @param string|Zoombi_Model $a_model
	 * @return Zoombi_Model 
	 */
	function loadModel( $a_model )
	{
		if( $a_model instanceof Zoombi_Model )
		{
			return $a_model;
		}
		
		if( !is_string($a_model) )
		{
			$this->triggerError("Controller '{$this->getName()}':  has wrong model type", Zoombi_Exception_Controller::EXC_MODEL);
			return;
		}
		
		$name = Zoombi_String::normalize($a_model);
		
		if( empty($name) )
		{
			$this->triggerError("Controller '{$this->getName()}':  has try load empty model", Zoombi_Exception_Controller::EXC_MODEL);
			return;
		}
		
		if( is_numeric($name) )  
		{
			$this->triggerError("Controller '{$this->getName()}':  has try load model with numeric name", Zoombi_Exception_Controller::EXC_MODEL);
			return;
		}

		$m = null;
		
		try
		{
			if( substr($name, 0, 1) == Zoombi::SS )
			{
				$m = Zoombi::getApplication()->getLoader()->model(substr($name, 1), true);
			}
			else
			{
				$m = $this->getModule()->getLoader()->model($name, true);
			}

			if( $m instanceof Zoombi_Model )
			{
				$this->setModel($m->getName(), $m);
			}
			else
			{
				if( $this->getModule()->isMode(Zoombi_Module::MODE_DEBUG) )
					$this->triggerError('Model: ' . $name . 'not loaded');
			}							

		}
		catch( Zoombi_Exception_Model $e )
		{
			if( $this->getModule()->isMode(Zoombi_Module::MODE_DEBUG) )
				$this->triggerError($e);
		}
		
		return $m;
	}

	/**
	 * Set Zoombi_Model instance
	 * @param string $a_name
	 * @param Zoombi_Model $a_model
	 * @return Zoombi_Controller
	 */
	function & setModel( $a_name, Zoombi_Model & $a_model )
	{
		if( !is_string($a_name) )
		{
			$this->triggerError( 'Model name must be string', Zoombi_Exception_Controller::EXC_MODEL );
			return $this;
		}
		
		$name = Zoombi_String::normalize( $a_name );
		
		if( empty($name) )
		{
			$this->triggerError(  'Model name must be not empty', Zoombi_Exception_Controller::EXC_MODEL );
			return $this;
		}
		
		if( is_numeric($name) )
		{
			$this->triggerError(  'Model name must be not numeric', Zoombi_Exception_Controller::EXC_MODEL );
			return $this;
		}
		
		if( !$a_model )
		{
			$this->triggerError(  'Model must be not null', Zoombi_Exception_Controller::EXC_MODEL );
			return $this;
		}

		$this->m_models[ $name ] =& $a_model;
		
		return $this;
	}

	/**
	 * Set Zoombi_Model instances
	 * @param array $a_models
	 * @return Zoombi_Controller
	 */
	function & setModels( array $a_models )
	{
		$this->m_models = array();
		$this->addModels($a_models);
		return $this;
	}

	/**
	 * Remove model from controller
	 * @param string $a_name
	 * @return Zoombi_Controller 
	 */
	function & removeModel( $a_model )
	{
		switch( gettype($a_model) )
		{
			case 'string':
				$name = Zoombi_String::normalize($a_model);
				if( array_key_exists($name, $this->m_models) )
					unset($this->m_models[$name]);
				break;

			case 'object':
				foreach( $this->m_models as $key => $action )
				{
					if( $action === $a_model )
					{
						unset($this->m_models[$key]);
						break;
					}
				}
				break;
		}
		return $this;
	}
	
	function & removeModels()
	{
		$this->m_models = array();
		return $this;
	}

	/**
	 * Check if Zoombi_Model instance is load
	 * @param  string|Zoombi_Model $a_name
	 * @return bool
	 */
	function hasModel( $a_model )
	{
		switch( gettype($a_model) )
		{
			case 'string':
				return array_key_exists(Zoombi_String::normalize($a_model), $this->m_models);

			case 'object':
				foreach( $this->m_models as $a_model )
				{
					if( $a_model === $model )
						return true;
				}
				break;
		}
		return false;
	}

	/**
	 * Get Zoombi_Model instance by name
	 * @param string $a_name
	 * @return Zoombi_Model
	 */
	function & getModel( $a_name )
	{
		if( $this->hasModel($a_name) )
			return $this->m_models[$a_name];

		return Zoombi::$null;
	}

	/**
	 * Forward controller/action call to another
	 * @return Zoombi_Controller
	 */
	protected function & forward( $a_to )
	{
		$fwd = $this->m_forwarder;
		$this->m_forwarder = $this;

		$this->getModule()->route($a_to);
		$this->m_forwarder = $fwd;

		print $this->getModule()->getOutput();
		$this->return = $this->getModule()->getReturn();
		return $this->return;
	}

	/**
	 * Set forwarded controller
	 * @param Zoombi_Controller $a_forwarder
	 * @return Zoombi_Controller
	 */
	public function & setForwarder( Zoombi_Controller $a_forwarder )
	{
		$this->m_forwarder = $a_forwarder;
		return $this;
	}

	/**
	 * Get forwarded controller
	 * @return Zoombi_Controller
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
		return $this->m_forwarder !== null;
	}

	/**
	 * Get property
	 * @param string $a_property
	 * @return mixed
	 */
	public function & __get( $a_property )
	{
		$v = null;
		if( $this->hasModel($a_property) )
		{
			$v = $this->getModel($a_property);
			return $v;
		}

		$v = parent::__get($a_property);
		return $v;
	}

	/**
	 * Controller caller
	 * @param string $a_name
	 * @param array $a_arguments
	 * @return mixed
	 */
	public function __call( $a_name, $a_arguments )
	{
		$a = $this->_actionMin($a_name);
		if( $this->hasAction($a) )
			return $this->requestAction($a, $a_arguments, true);

		$this->triggerError('Method not found: ' . $a, E_USER_WARNING);
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
		switch( gettype($a_action) )
		{
			default:
				$name = $this->_actionMin(Zoombi_String::normalize($a_action));
				return method_exists($this, $this->_actiomMax($name)) OR array_key_exists($name, $this->m_actions);

			case 'array':
				if( count($a_action) == 2 )
				{
					if( $a_action[0] == $this OR is_a($a_action[0], get_class($this)) )
						return $this->hasAction($a_action[1]);
				}
				break;

			case 'object':
				foreach( $this->m_actions as &$a )
				{
					if( $a === $a_action )
						return true;
				}
				break;
		}

		return false;
	}

	private function _actionMin( $a_action )
	{
		$pref = $this->getModule()->getConfig()->getValue('controller.action_prefix', Zoombi_Module::DEFAULT_CONTROLLER_METHOD_PREFIX);
		$suff = $this->getModule()->getConfig()->getValue('controller.action_suffix', Zoombi_Module::DEFAULT_CONTROLLER_METHOD_SUFFIX);
		$name = $a_action;

		if( strlen($pref) > 0 && strpos($name, $pref) === 0 )
			$name = substr($name, strlen($pref));

		if( strlen($suff) > 0 && strrpos($name, $suff) === 0 )
			$name = substr($name, 0, -strlen($suff));

		return $name;
	}

	private function _actiomMax( $a_action )
	{
		$pref = $this->getModule()->getConfig()->getValue('controller.action_prefix', Zoombi_Module::DEFAULT_CONTROLLER_METHOD_PREFIX);
		$suff = $this->getModule()->getConfig()->getValue('controller.action_suffix', Zoombi_Module::DEFAULT_CONTROLLER_METHOD_SUFFIX);
		return $pref . $a_action . $suff;
	}
	
	/**
	 * Get last requested action
	 * @return string
	 */
	public function requestedAction()
	{
		return $this->m_last_action;
	}

	/**
	 * Request controller action
	 * @param string $a_action
	 * @param array $a_params
	 * @param bool $a_inner
	 * @return Zoombi_Controller
	 */
	public function & requestAction( $a_action, array $a_params = array( ), $a_inner = false )
	{
		$action = $this->_actionMin($a_action);
		$this->m_last_action = $action;

		$this->return = null;
		$this->output = null;

		if( $this->isForwarded() )
			$a_inner = true;

		if( !$a_inner )
			$this->emit(new Zoombi_Event($this, 'preAction', $action, $this));

		if( $this->hasAction($action) == false )
		{
			$this->triggerError("Action '{$action}' not found in controller '{$this->getName()}' -> " . $this->router->getCurrent(), Zoombi_Exception_Controller::EXC_ACTION);
			return $this;
		}

		$action_name = $this->_actiomMax($action);

		$callback = null;
		if( method_exists($this, $action_name) )
			$callback = array( &$this, $action_name );
		else if( array_key_exists($action, $this->m_actions) )
			$callback = array( &$this->m_actions[$action], 'run' );

		if( !$a_inner )
		{
			ob_start();
			if( $this->before($action) )
			{
				if( $callback AND is_callable($callback) )
					$this->return = call_user_func_array($callback, $a_params);

				$this->output = ob_get_contents();

				ob_end_clean();

				$this->after($action);
			}
			else
			{
				$this->output = ob_get_contents();
				ob_end_clean();
			}
		}
		else
		{
			$ret = call_user_func_array($callback, $a_params);
			return $ret;
		}

		if( !$a_inner )
			$this->emit(new Zoombi_Event($this, 'postAction', $action));

		return $this;
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
		$s = (string)$a_view;

		if( empty($s) )
		{
			$s = $this->getName() . Zoombi::SS . $this->m_last_action;

			if( $this instanceof Zoombi_Plugin )
				$s = 'Plugin' . Zoombi::SS . $this->getName();
		}

		$v = new Zoombi_View($s, $a_data);
		$o = $this->renderView($v, $a_return);
		unset($v);
		return $o;
	}
	
	public function returnRender( $a_view = null, $a_data = null )
	{
		return $this->render($a_view, $a_data, true);
	}

	/**
	 * Render default template
	 * @param array|object $a_data
	 * @param bool $a_return
	 * @return Zoombi_Controller 
	 */
	public function renderData( $a_data = null, $a_return = false )
	{
		return $this->render(null, $a_data, $a_return);
	}

	/**
	 * Render layout
	 * @param string $a_layout Layout path
	 * @param array|object $a_data Data to render
	 * @param bool $a_return Return result or print his
	 * @return Zoombi_Controller
	 */
	public function renderLayout( $a_layout, $a_data = null, $a_return = false )
	{
		if( !$this->hasLayout($a_layout) )
			$this->addLayout($a_layout);

		$l = $this->getLayout($a_layout);
		$l->setData($a_data);
		$l->display($a_return);
		return $l;
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
		foreach( $a_each as $k => $v ){
			$data = array();
			
			if( is_object($v) )
				$data = get_object_vars($v);
			else if( is_array($v) ){
				$data = $v;
			} else {
				throw new Exception('Not array data given');
			}
			
			$o[] = $this->render($a_view, array_merge($data,array('index'=>$k)), true);
		}
			
		$out = implode($a_separator, $o);

		if( $a_return )
			return $out;
		echo $out;
	}

	/**
	 * Render view
	 * @param Zoombi_View $a_view
	 * @param array $a_data
	 * @param bool $a_return
	 * @return midex
	 */
	private function renderView( Zoombi_View & $a_view, $a_return = false )
	{
		$a_view->setThis($this);
		$view = $a_view->getView();

		if( !file_exists($view) OR !is_file($view) )
		{
			try
			{
				$a_view->setView( $this->getModule()->getLoader()->view($view,true) );
			}
			catch( Zoombi_Exception_View $e )
			{
				$this->triggerError($e);
			}
		}
		
		try
		{
			return $a_view->display($a_return);
		}
		catch( Zoombi_Exception_View $e )
		{
			$this->triggerError($e);
		}
	}

	/**
	 * Get output buffer of data action controller
	 * @param string $a_action Action name
	 * @param array $a_rapams Action params
	 * @return string
	 */
	public function actionOutput( $a_action, array $a_params = array( ) )
	{
		return $this->requestAction($action, $a_params)->getOutput();
	}

	/**
	 * Get return value of action controller
	 * @param string $a_action Action name
	 * @param array $a_rapams Action params
	 * @return mixed
	 */
	public function actionReturn( $a_action, array $a_params = array( ) )
	{
		return $this->requestAction($action, $a_params)->getReturn();
	}

	/**
	 * Get url to current module/controller/action
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
		throw new Zoombi_Exception_Controller('Quit', $a_output ? Zoombi_Exception_Controller::EXC_QUIT_OUTPUT : Zoombi_Exception_Controller::EXC_QUIT);
	}

}
