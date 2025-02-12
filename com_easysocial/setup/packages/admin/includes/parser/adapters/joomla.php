<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialJoomlaParser
{
	private $path		= null;
	private $document	= null;
	private $current	= null;

	public function __construct()
	{
		$this->parser	= JFactory::getXMLParser( 'Simple' );
	}

	public function read( $file )
	{
		$status	= $this->parser->loadFile( $file );

		$this->document	= $this->parser->document;

		return $status;
	}

	public function getElementByPath( $path )
	{
		$this->current	= $this->document->getElementByPath( $path );

		return $this->current;
	}

	public function data()
	{
		if( !$this->current )
		{
			return $this->current;
		}

		if( !is_array( $this->current ) )
		{
			return $this->current->data();
		}

		return $this->current->data();
	}
}
