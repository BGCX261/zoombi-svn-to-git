<?php

/**
 * Controller exception
 */
class Zoombi_Exception_Controller extends Zoombi_Exception_Loader
{
	const EXC_QUIT = 22;
	const EXC_QUIT_OUTPUT = 23;
	const EXC_MODEL = 24;
	const EXC_ACTION = 25;
	const EXC_VIEW = 26;
	const EXC_HELPER = 27;
	const EXC_BLOCK = 28;
	const EXC_DENY = 29;
	const EXC_AUTH = 30;
}