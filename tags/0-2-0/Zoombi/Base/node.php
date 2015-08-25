<?php
/*
 * File: node.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * Node class
 * @author Zombie
 */
class ZNode extends ZInstance
{
    /**
     * Global nodes instancer
     * @var array 
     */
    static private $m_instances = array();

	/**
	 * Parent holder
	 * @var ZNode
	 */
	private $m_parent;
	
	/**
	 * Node childrens array
	 * @var array
	 */
	private $m_childrens;

    /**
     * Unique object ID
     * @var int
     */
    private $m_id;
	
	/**
	 * Constructor
	 * @param $a_parent Assign parent
	 * @param array $a_childrens Array of childrens
	 */
	public function __construct( ZNode & $a_parent = null, array $a_childrens = array() )
	{
        $this->m_childrens = array();
        $this->m_id = array_push( self::$m_instances, $this );

		if( $a_parent )
			$this->setParent( $a_parent );

		$this->setChildrens( $a_childrens );
	}

    /**
     * Get node instances
     * @return array
     */
    static public function getNodeInstances()
    {
        return self::$m_instances;
    }

    /**
     * Get unigue id of node
     * @return int
     */
    public function getId()
    {
        return $this->m_id;
    }
	
	/**
	 * Get node parent
	 * @return null|ZNode
	 */
	public function & getParent()
	{
		return $this->m_parent;
	}
	
	/**
	 * Set node parent
	 * @param ZNode $a_parent
	 * @return ZNode
	 */
	public function & setParent( ZNode & $a_parent )
	{
        if( $this->m_parent )
        {
            if( $this->m_parent->getId() == $a_parent->getId() )
                return $this;

            $this->m_parent->removeChildren($this);
            unset($this->m_parent);
        }
        $this->m_parent =& $a_parent;
        $this->m_parent->addChildren($this);
		return $this;	
	}
	
	/**
	 * Set node childrens
	 * @param array $a_childrens
	 * @return ZNode
	 */
	public function & setChildrens( array $a_childrens )
	{
		$this->m_childrens = array();
		$this->addChildrens( $a_childrens );
		return $this;
	}
	
	/**
	 * Add node childrens
	 * @param array $a_childrens
	 * @return ZNode
	 */
	public function & addChildrens( array $a_childrens )
	{
		foreach( $a_childrens as $v )
		{
			$this->addChildrens( $v );
		}
		return $this;
	}
	
	/**
	 * Add children to node
	 * @param ZNode $a_children
	 * @return ZNode
	 */
	public function & addChildren( ZNode & $a_children )
	{
        if( !$a_children )
            return $this;

		$a_children->setParent($this);
		$this->m_childrens[ $a_children->getId() ] =& $a_children;
		return $this;
	}

    /**
     * Remove node children
     * @param ZNode $a_children
     * @return ZNode
     */
    public function & removeChildren( ZNode & $a_children )
    {
        $id = $a_children->getId();
        if( isset($this->m_childrens[$id]) || array_key_exists( $id, $this->m_childrens ) )
            unset($this->m_childrens[$id]);
        return $this;
    }

    /**
     * Get node childrens
     * @return array
     */
    public function & getChildrens()
    {
        return $this->m_childrens;
    }
}
