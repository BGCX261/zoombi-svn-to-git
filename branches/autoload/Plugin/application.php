<?php

/*
 * File: application.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Base class for application plugun
 */
class Zoombi_Plugin_Application extends Zoombi_Plugin
{

	/**
	 * Before application make execute action
	 */
	function preExecute()
	{
		
	}

	/**
	 * Before application do routing action
	 */
	function preRoute( Zoombi_Route $a_route )
	{
		
	}

	/**
	 * After application alredy do routing action
	 */
	function postRoute()
	{
		
	}

	/**
	 * Befor load module
	 */
	function preModule()
	{
		
	}

	/**
	 * After load module
	 */
	function postModule()
	{
		
	}

	/**
	 * Before application create controller
	 */
	function preController()
	{
		
	}

	/**
	 * If routed controller not exist
	 */
	function on404()
	{
		
	}

	/**
	 * After application create controller
	 */
	function postController()
	{
		
	}

	/**
	 * Before application call controller action
	 */
	function preAction()
	{
		
	}

	/**
	 * After application call controller action
	 */
	function postAction()
	{
		
	}

	/**
	 * On application print output to browser
	 */
	function onOutput()
	{
		
	}

	/**
	 * After application execute
	 */
	function postExecute()
	{
		
	}

}
