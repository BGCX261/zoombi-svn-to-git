<?php
/*
 * File: documentcontroller.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * Base docement controller
 */
abstract class ZDocumentController extends ZController
{
    /**
     * Document instance
     * @var ZDocument
     */
    public $document;

	/**
     * Object getter
     * @param string $a_name
     * @return mixed
     */
    public function & __get( $a_name )
    {
		try
		{
			return parent::__get($a_name);
		}
		catch( ZException $e )
		{
			return $this->document->$a_name;
		}
    }

    /**
     * Object setter
     * @param string $a_name
     * @param mixed $a_value
     * @return mixed
     */
    public function __set($a_name, $a_value)
    {
		if( parent::hasProperty($a_name) )
			return parent::__set($a_name, $a_value);
        $this->document->$a_name = $a_value;
    }

    /**
     * Object caller
     * @param string $a_name
     * @param array $a_args
     * @return mixed
     */
    public function __call( $a_name, $a_args )
    {
        if( method_exists($this->document, $a_name) && is_callable( array( &$this->document, $a_name ) ) )
            return call_user_func_array( array( &$this->document, $a_name ), $a_args);
    }
}
