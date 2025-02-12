<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

ES::import( 'admin:/tables/app' );

class SocialTableFieldApplication extends SocialTableApp
{

	/*
	 * Loads an addon object based on a given field id from #__social_fields
	 *
	 * @param   int     $fieldId    The field id from #__social_fields
	 * @return  boolean True on success false otherwise.
	 */
	public function loadByField( $fieldId )
	{
	    $db 	= ES::db();
	    $query  = 'SELECT a.* FROM ' . $db->nameQuote( $this->_tbl ) . ' AS a '
	            . 'INNER JOIN ' . $db->nameQuote( '#__social_fields' ) . ' AS b '
				. 'ON b.field_id=a.id '
				. 'WHERE b.' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $fieldId );
		$db->setQuery( $query );
		$data	= $db->loadObject();

		return parent::bind( $data );
	}

	public function render( $raw = '' )
	{
		$path	= SOCIAL_MEDIA . DS . strtolower( $this->type ) . DS . strtolower( $this->element ) . DS . 'tmpl' . DS . 'params.xml';

		return parent::renderParams( $raw , $path );
	}

	public function getParams()
	{

	}
}
