<?php
/*
 * File: pluginmanager.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Description of ZPluginManager
 *
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
class ZPluginManager extends ZObject implements IZPluginManager
{
    /**
     * Array of plugins
     * @var array
     */
    private $m_plugins;

    /**
     * Plugins target
     * @var ZDispatcher
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
     * @param ZObject $a_parent
     * @param string $a_name
     */
    function __construct( ZObject & $a_parent = null, $a_name = null )
    {
        parent::__construct($a_parent,$a_name);
        $this->m_plugins = array();
    }

    /**
     * Set listened target
     * @param ZDispatcher $a_target
     */
    function setTarget( ZDispatcher & $a_target )
    {
        if( $this->m_target )
            unset( $this->m_target );

        $this->m_target =& $a_target;
        $this->m_target->addProcessor( array(&$this,'eventProcessor') );

        $this->m_prefix = Zoombi::config('plugin.action_prefix','');
        $this->m_suffix = Zoombi::config('plugin.action_suffix', 'Action');
    }

    /**
     * Event processor
     * @internal
     * @param ZEvent $a_event
     */
    public function eventProcessor( ZEvent & $a_event )
    {
        $a = $a_event->name;
        $action = $this->m_prefix . (string)$a . $this->m_suffix;
        foreach ( $this->m_plugins as $p )
        {
            if( !method_exists($p,$action) || !$p )
                continue;

            call_user_func_array( array($p, $action), $a_event->data );
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
     * @return ZPlugin
     */
    public final function & getPlugin( $a_plugin )
    {
        $null = null;

		if(is_integer($a_plugin) )
		{
			if( isset($this->m_plugins[$a_plugin]) )
				return $this->m_plugins[$a_plugin];
		}
		
        foreach( $this->m_plugins as $index => $plugin )
        {
			if( $plugin->name() == $a_plugin )
				return $this->m_plugins[$index];
        }
        return $null;
    }

    /**
     * Set plugins
     * @param array $a_plugins
     * @return ZPluginManager
     */
	public final function & setPlugins( array $a_plugins )
	{
		foreach( $a_plugins as $plugin )
            $this->addPlugin( $plugin );
		
		return $this;
	}

    /**
     * Add plugin to stack
     * @param mixed $a_plugin
     * @return ZPluginManager
     */
	public final function & addPlugin( $a_plugin, $a_connectall = true )
	{
		$plg = $a_plugin;
        if( is_string($a_plugin) )
            $plg = ZLoader::plugin($a_plugin);

        if( $plg instanceof ZPlugin )
        {
            $this->addChildren( $plg );
            $this->m_plugins[] =& $plg;
        }
		return $this;
	}

    /**
     * Remove plugin from stack
     * @param int|ZPlugin $a_plugin
     * @return ZPluginManager
     */
    public final function & removePlugin( $a_plugin )
    {
        if(is_integer($a_plugin) )
            $id = $a_plugin;
        else if( is_string($a_plugin) )
            $id = intval ($a_plugin);
        else if( $a_plugin instanceof ZPlugin )
            $id = $a_plugin->getId();
        else
            return $this;

        foreach( $this->getPlugins() as $plugin )
        {
            if( $plugin->getId() === $id )
            {
                $plugin->destroy();
                unset($this);
                break;
            }
        }
        return $this;
    }
}
