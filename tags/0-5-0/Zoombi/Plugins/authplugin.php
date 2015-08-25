<?php

/*
 * File: aclplugin.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

abstract class ZAuthPlugin extends ZApplicationPlugin
{
	const AUTH_BASIC = 'basic';
	const AUTH_SESSION = 'session';
	const AUTH_FILE = 'file';

	private $m_once;
	
	private function _request_auth()
	{
		ZResponse::getInstance()
		  ->setCode(401)
				->setMessage('Unauthorized')
				->setHeader('Date', date_create(DATE_RSS))
				->setHeader('WWW-Authenticate', 'Basic realm="Secure area"');

		//Zoombi::getApplication()->getRouter()->setCurrent('error/401');
		throw new ZControllerException('Unauthorized', ZControllerException::EXC_AUTH);
	}

	private function _do_auth( $a_auth_type )
	{
		if( $this->m_once )
			return;

		$this->m_once++;
		switch( $a_auth_type )
		{
			case 'basic':
				
				if( !ZRequest::getInstance()->isAuth() )
					return $this->_request_auth();

				$this->login(ZRequest::getInstance()->user(), ZRequest::getInstance()->password());

				if( !$this->isAuthorized() )
					return $this->_request_auth();
				
				break;

			case 'post':
				
				if( ZRequest::hasPost('login') && ZRequest::hasPost('password')  )
					$this->login(ZRequest::getPost('login'), ZRequest::getPost('password'));

				if( !$this->isAuthorized() )
				{
					throw new ZControllerException('Unauthorized', ZControllerException::EXC_AUTH);
					//Zoombi::getApplication()->getRouter()->setCurrent('error/401');
				}

				break;

			case 'get':
				if( ZRequest::hasGet('login') AND ZRequest::hasGet('password')  )
					$this->login(ZRequest::getPost('login'), ZRequest::getPost('password'));

				if( !$this->isAuthorized() )
				{
					throw new ZControllerException('Unauthorized', ZControllerException::EXC_AUTH);
					//Zoombi::getApplication()->getRouter()->setCurrent('error/401');
				}

				break;
		}
	}
/*
	public function postModule($a_module)
	{
		
		if( isset($this->getModule()->auth) )
		{
			return $this->_do_auth( $this->getModule()->auth );
		}
	}*/

	public function postRoute( $a_route )
	{
		if( $this->getModule()->getConfig()->has('auth') )
			return $this->_do_auth( $this->getModule()->getConfig()->get('auth') );
	}

	public function preAction( $a_action, ZController & $a_controller )
	{
		/*if( $this->getModule()->getConfig()->has('auth') )
			return $this->_do_auth( $this->getModule()->getConfig()->get('auth') );

		if( isset($a_controller->getModule()->auth) )
			return $this->_do_auth( $this->getModule()->auth );

		if( isset($a_controller->auth) )
			return $this->_do_auth( $a_controller->auth );*/
	}
/*
	public function postController( $a_ctl_name, $a_class )
	{
		if( isset($a_class->auth) )
			return $this->_do_auth( $a_class->auth );
	}*/

	abstract function login( $a_user, $a_password );
	abstract function logout();
	abstract function isAuthorized();	
}
