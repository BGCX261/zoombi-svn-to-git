<?php
/*
 * File: text.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Class for Zoombi PHP Framework
 */

if( !defined('ZBOOT') )
	return;

/**
 * class ZTextDocument
 * @author Andrew Saponenko <roguevoo@gmail.com>
 */
class ZTextDocument extends ZDocument
{
	/**
	 * @var string
	 */
	private $m_data;

	/**
	 * Constructor
	 */
	public function __construct( $a_type = self::DOCTYPE_TEXT, $a_mime = 'text/plain' )
	{
        parent::__construct( $a_type, $a_mime);
		$this->m_data = '';
	}

	/**
	 * Set document data
	 * @param string $a_data
     * @return ZTextDocument
	 */
	public function & setData( $a_data )
	{
		$this->m_data = $a_data;
        return $this;
	}

	/**
	 * Get document data
	 * @return string
	 */
	public function & getData()
	{
		return $this->m_data;
	}

    /**
     * Append text data
     * @param string $a_data
     * @return ZTextDocument
     */
    public function & dataAppend( $a_data )
    {
        $this->m_data .= $a_data;
        return $this;
    }

    /**
     * Prepend text data;
     * @param <type> $a_data
     * @return ZTextDocument
     */
    public function & dataPrepend( $a_data )
    {
        $this->m_data = $a_data . $this->m_data;
        return $this;
    }

    /**
	 * Compile data
	 * @return string
	 */
	public function compile()
	{
		return (string)$this->getData();
	}

    public function __toString()
    {
        return $this->compile();
    }
}

?>