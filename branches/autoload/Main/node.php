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
class Zoombi_Node extends Zoombi_Instance
{

	/**
	 * Global nodes instancer
	 * @var array
	 */
	static private $m_instances = array();

	/**
	 * Parent holder
	 * @var Zoombi_Node
	 */
	private $m_parent;

	/**
	 * Node childrens array
	 * @var array
	 */
	private $m_childrens = array();

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
	public function __construct( Zoombi_Node & $a_parent = null, array $a_childrens = array() )
	{
		$this->m_id = array_push(self::$m_instances, $this);

		if($a_parent)
			$this->setParent($a_parent);

		$this->setChildrens($a_childrens);
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
	 * @return null|Zoombi_Node
	 */
	public function & getParent()
	{
		return $this->m_parent;
	}

	/**
	 * Set node parent
	 * @param Zoombi_Node $a_parent
	 * @return Zoombi_Node
	 */
	public function & setParent( Zoombi_Node & $a_parent )
	{
		if($this->m_parent)
		{
			if($this->m_parent->getId() == $a_parent->getId())
				return $this;

			$this->m_parent->removeChildren($this);
			unset($this->m_parent);
		}
		$this->m_parent = & $a_parent;
		$this->m_parent->addChildren($this);
		return $this;
	}

	/**
	 * Set node childrens
	 * @param array $a_childrens
	 * @return Zoombi_Node
	 */
	public function & setChildrens( array $a_childrens )
	{
		$this->m_childrens = array();
		return $this->addChildrens($a_childrens);
	}

	/**
	 * Add node childrens
	 * @param array $a_childrens
	 * @return Zoombi_Node
	 */
	public function & addChildrens( array $a_childrens )
	{
		foreach($a_childrens as $v)
			$this->addChildren($v);
		
		return $this;
	}

	/**
	 * Add children to node
	 * @param Zoombi_Node $a_children
	 * @return Zoombi_Node
	 */
	public function & addChildren( Zoombi_Node & $a_children )
	{
		if(!$a_children)
			return $this;
		
		$cid = $a_children->getId();
		if( $this->hasChildren($cid) )
			return $this;
		
		$a_children->setParent($this);
		$this->m_childrens[ $cid ] =& $a_children;
		return $this;
	}

	/**
	 * Remove node children
	 * @param Zoombi_Node $a_children
	 * @return Zoombi_Node
	 */
	public function & removeChildren( Zoombi_Node & $a_children )
	{
		if( !$this->hasChildren($a_child) )
			return $this;
		
		
		switch( gettype($a_children) )
		{
			case 'int':
				unset( $this->m_childrens[$a_children] );
				break;
			
			case 'string':
				unset( $this->m_childrens[intval($a_children)] );
				break;
			
			case 'object':
				if( $a_children instanceof Zoombi_Node )
				{
					unset( $this->m_childrens[$a_children->getId()] );
				}
				break;
		}
		return $this;
	}
	
	/**
	 * Chech if cheldren exist
	 * @param int|string|object $a_child 
	 */
	public function hasChildren( $a_child )
	{
		switch( gettype($a_child) )
		{
			case 'int':
				return isset($this->m_childrens[$a_child]) OR array_key_exists($a_child, $this->m_childrens);
			
			case 'string':
				$id = intval($a_child);
				return isset($this->m_childrens[$id]) OR array_key_exists($id, $this->m_childrens);
			
			case 'object':
				if( $a_child instanceof Zoombi_Node )
				{
					$id = $a_child->getId();
					return isset($this->m_childrens[$id]) OR array_key_exists($id, $this->m_childrens);
				}
				break;
		}
		return false;
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
