<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

class SocialXml
{
	private static $instance = null;

	private $parser 	= null;
	private $version 	= null;

	public static function getInstance()
	{
		if( !self::$instance )
		{
			self::$instance	= new self();
		}

		return self::$instance;
	}

	public function factory( $contents = '', $isFile = false )
	{
		return new self( $contents, $isFile );
	}

	public function load( $contents = '', $isFile = false )
	{
		$this->version 	= ES::getInstance( 'version' )->getVersion();

		$parser = '';

		if( $this->version >= '3.0' )
		{
			$parser 	= JFactory::getXML( $contents , $isFile );
		}
		else
		{
			$parser 	= JFactory::getXMLParser('Simple');

			if( $isFile )
			{
				$parser->loadFile( $contents );
			}
			else
			{
				$parser->loadString( $contents );
			}
		}

		$this->parser 	= $parser;

		return $this->parser;
	}

	public function __call( $method, $args )
	{
		return call_user_func_array( array( $this->parser , $method ) , $args );
	}

	public function __get( $key )
	{
		return $this->parser->$key;
	}

	/**
	 * Get's the version
	 */
	public function getVersion()
	{
		if( $this->version >= '3.0' )
		{
			$version	= $this->parser->xpath( 'version' );

			return $version[0];
		}

		$element 	= $this->parser->document->getElementByPath( 'version' );
		$version 	= $element->data();

		return $version;
	}
}
