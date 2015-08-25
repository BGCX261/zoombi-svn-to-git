<?php

/*
 * File: model.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * MVC (Model) class
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
abstract class ZModel extends ZApplicationObject
{

	/**
	 *
	 */
	public function export()
	{
		return array();
	}

}
