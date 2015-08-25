<?php

/*
 * File: action.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

/**
 * Controller action
 *
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
abstract class ZAction extends ZControllable
{

	/**
	 * Run action
	 */
	abstract public function run();
}
