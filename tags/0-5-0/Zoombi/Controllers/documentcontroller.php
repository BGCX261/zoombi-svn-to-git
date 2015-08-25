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
}
