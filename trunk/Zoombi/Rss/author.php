<?php

/*
 * File: author.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

class Zoombi_Rss_Author
{

	private $m_title;
	private $m_email;

	public function __construct( $a_data = null )
	{
		if( is_a($a_data, 'Zoombi_Rss_Author'))
		{
			$this->setEmail($a_data->getEmail());
			$this->setTitle($a_data->getTitle());
			return;
		}

		if(is_array($a_data))
		{
			$this->fromArray($a_data);
			return;
		}

		if(is_string($a_data))
		{
			$this->fromString($a_data);
		}
	}

	public final function getTitle()
	{
		return $this->m_title;
	}

	public final function setTitle( $a_value )
	{
		$this->m_title = $a_value;
	}

	public final function getEmail()
	{
		return $this->m_email;
	}

	public final function setEmail( $a_value )
	{
		$this->m_email = $a_value;
	}

	public final function fromString( $a_author )
	{
		$mail_s = stripos($a_author, '(');
		$email = trim(substr($a_author, 0, $mail_s));
		$title = trim(substr($a_author, $mail_s + 1, stripos($a_author, ')') - $mail_s - 1));

		$this->setEmail($email);
		$this->setTitle($title);
	}

	public final function toString()
	{
		return $this->getEmail() . ' (' . $this->getTitle() . ')';
	}

	public final function fromArray( array $a_data )
	{
		if(isset($a_data['email']))
			$this->setEmail($a_data['email']);

		if(isset($a_data['title']))
			$this->setTitle($a_data['title']);
	}

	public final function toArray()
	{
		return array(
			'title' => $this->getTitle(),
			'email' => $this->getEmail()
		);
	}

}