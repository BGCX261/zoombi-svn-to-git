<?php
/*
 * File: model.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * MVC (Model) class
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
abstract class ZModel extends ZObject implements IZModel
{
    /**
     * Controller
     * @var ZController
     */
    private $m_controler;

    /**
     * Constructor
     * @param ZController $a_controller
     */
    public function __construct( ZController & $a_controller = null )
    {
        parent::__construct();
        if( $this->m_controler )
            $this->m_controler = $a_controller;
    }
    
	/**
	 * Load model from file
	 * @param string $a_model
	 * @return ZModel|null
	 */
	static public final function & load( $a_model )
	{
		return Zoombi::model($a_model);
	}

    /**
     * Set model controller
     * @param ZController $a_controller
     * @return ZModel
     */
    public function & setController( ZController & $a_controller )
    {
        $this->m_controller = $a_controller;
        return $this;
    }

    /**
     * Get model controller
     * @return ZController
     */
    public function & getController()
    {
        return $this->m_controller;
    }

    /**
     *
     */
	public function export()
	{}

    /**
     * Model getter
     * @param string $a_name
     * @return mixed
     */
    public function __get( $a_name )
    {
        switch ( $a_name )
        {
            case 'controller':
                return $this->getController();

            case 'view':
                return $this->getController()->getView();
        }
        return parent::__get( $a_name );
    }
}
