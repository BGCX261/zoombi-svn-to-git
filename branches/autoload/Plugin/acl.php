<?php

/*
 * File: acl.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

abstract class Zoombi_Plugin_Acl extends Zoombi_Plugin
{

	abstract public function & getUser();

	abstract public function isAllow( $a_aro );

	abstract public function isDeny( $a_aro );
}