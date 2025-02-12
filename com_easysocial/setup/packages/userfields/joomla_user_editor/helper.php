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

require_once( dirname( __FILE__ ) . '/helper.php' );

class SocialEditorHelper
{
	/**
	 * Retrieves a list of editors that is published on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getEditors()
	{
		$db 		= ES::db();

		$query		= array();

		$query[]	= 'SELECT * FROM ' . $db->nameQuote( '#__extensions' );
		$query[]	= 'WHERE ' . $db->nameQuote( 'folder' ) . '=' . $db->Quote( 'editors' );
		$query[]	= 'AND ' . $db->nameQuote( 'enabled' ) . '=' . $db->Quote( 1 );
		$query[]	= 'ORDER BY ' . $db->nameQuote( 'ordering' ) . ',' . $db->nameQuote( 'name' );

		$query 		= implode( ' ' , $query );

		$db->setQuery( $query );
		$result 	= $db->loadObjectList();

		// Load language strings.
		$lang		= JFactory::getLanguage();

		foreach( $result as $i => $option )
		{
			$lang->load('plg_editors_'.$option->element, JPATH_ADMINISTRATOR, null, false, false)
			||	$lang->load('plg_editors_'.$option->element, JPATH_PLUGINS .'/editors/'.$option->element, null, false, false)
			||	$lang->load('plg_editors_'.$option->element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
			||	$lang->load('plg_editors_'.$option->element, JPATH_PLUGINS .'/editors/'.$option->element, $lang->getDefault(), false, false);

			$option->name	= JText::_( $option->name );
		}

		return $result;
	}

	/**
	 * Gets the current logged in user's editor.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getUserEditor( $id = null )
	{
		$user 	= JFactory::getUser( $id );

		$editor = $user->getParam( 'editor' );

		return $editor;
	}
}
