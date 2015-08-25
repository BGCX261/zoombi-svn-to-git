<?php

/*
 * File: aclplugin.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

abstract class ZAclPlugin extends ZPlugin
{
	abstract public function & getUser();
	abstract public function isAllow($a_aro);
	abstract public function isDeny($a_aro);
}