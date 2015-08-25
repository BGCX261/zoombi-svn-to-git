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
		$this->request
			->setCode(401)
			->setMessage('Unauthorized')
			->setHeader('Date', date_create(DATE_RSS))
			->setHeader('WWW-Authenticate', 'Basic realm="Secure area"');

		throw new ZControllerException('Unauthorized', ZControllerException::EXC_AUTH);
	}

	private function _do_auth( $a_auth_type )
	{
		if($this->m_once)
			return;

		$this->m_once++;
		switch($a_auth_type)
		{
			case 'basic':

				if(!$this->request->isAuth())
					return $this->_request_auth();

				$this->login($this->request->user(), $this->request->password());

				if(!$this->isAuthorized())
					return $this->_request_auth();

				break;

			case 'post':

				if($this->request->hasPost('login') && $this->request->hasPost('password'))
					$this->login($this->request->getPost('login', FILTER_SANITIZE_STRING), $this->request->getPost('password', FILTER_SANITIZE_STRING));

				if(!$this->isAuthorized())
					throw new ZControllerException('Unauthorized', ZControllerException::EXC_AUTH);

				break;

			case 'get':
				if($this->request->hasGet('login') AND $this->request->hasGet('password'))
					$this->login($this->request->getGet('login'), $this->request->getGet('password'));

				if(!$this->isAuthorized())
					throw new ZControllerException('Unauthorized', ZControllerException::EXC_AUTH);

				break;
		}
	}

	public function postRoute( $a_route )
	{
		if($this->getModule()->getConfig()->has('auth'))
			return $this->_do_auth($this->getModule()->getConfig()->get('auth'));
	}

	abstract function login( $a_user, $a_password );

	abstract function logout();

	abstract function isAuthorized();
}
