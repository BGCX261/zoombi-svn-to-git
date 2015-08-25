<?php

/*
 * File: singleton.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: This file is a part of Zoombi PHP Framework
 */


/**
 * Singleton inteface
 */
interface Zoombi_Singleton
{
	static public function & getInstance();
}