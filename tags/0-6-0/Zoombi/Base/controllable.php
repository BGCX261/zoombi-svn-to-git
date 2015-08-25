<?php

/*
 * File: controllable.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */

if(!defined('ZBOOT'))
	return;

/**
 * Object controllable by parent object
 *
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
class ZControllable extends ZObjectBinder
{

	/**
	 * Set controller
	 * @param ZController $a_ctl
	 * @return ZControllable
	 */
	public function & setController( ZController & $a_ctl )
	{
		return $this->setThis($a_ctl);
	}

	/**
	 * Get controller
	 * @return ZControllable
	 */
	public function & getController()
	{
		return $this->getThis();
	}

}
