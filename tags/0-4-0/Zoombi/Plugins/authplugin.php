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
				->setHeader('Date', date_to_string(date_create(DATE_RSS)))
				->setHeader('WWW-Authenticate', 'Basic realm="Secure area"');

		throw new ZControllerException('Unauthorized', ZControllerException::EXC_AUTH);
	}

	private function _do_auth( $type )
	{
		if( $this->m_once )
			return;

		$this->m_once++;
		switch( $type )
		{
			case 'basic':
				if( !ZRequest::getInstance()->isAuth() )
					return $this->_request_auth();

				$this->login(ZRequest::getInstance()->user(), ZRequest::getInstance()->password());

				if( !$this->isAuthorized() )
					return $this->_request_auth();
				
				break;

			case 'post':
				if( ZRequest::hasPost('login') AND ZRequest::hasPost('password')  )
					$this->login(ZRequest::getPost('login'), ZRequest::getPost('password'));

				if( !$this->isAuthorized() )
					throw new ZControllerException('Unauthorized', ZControllerException::EXC_AUTH);

				break;

			case 'get':
				if( ZRequest::hasGet('login') AND ZRequest::hasGet('password')  )
					$this->login(ZRequest::getPost('login'), ZRequest::getPost('password'));

				if( !$this->isAuthorized() )
					throw new ZControllerException('Unauthorized', ZControllerException::EXC_AUTH);

				break;
		}
	}

	public function postModule($a_module)
	{
		if( isset($this->getModule()->auth) )
			return $this->_do_auth( $this->getModule()->auth );
	}

	public function postRoute( $a_route )
	{
		if( $this->getModule()->getConfig()->has('auth') )
			return $this->_do_auth( $this->getModule()->getConfig()->get('auth') );
	}

	public function postController( $a_ctl_name, $a_class )
	{
		if( isset($a_class->auth) )
			return $this->_do_auth( $a_class->auth );
	}

	abstract function login( $a_user, $a_password );
	abstract function logout();
	abstract function isAuthorized();	
}
