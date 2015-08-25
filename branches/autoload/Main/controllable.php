<?php

/*
 * File: controllable.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Object controllable by parent object
 *
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
class Zoombi_Controllable extends Zoombi_Object_Binder
{

	/**
	 * Set controller
	 * @param Zoombi_Controller $a_ctl
	 * @return Zoombi_Controllable
	 */
	public function & setController( Zoombi_Controller & $a_ctl )
	{
		return $this->setThis($a_ctl);
	}

	/**
	 * Get controller
	 * @return Zoombi_Controllable
	 */
	public function & getController()
	{
		return $this->getThis();
	}

}
