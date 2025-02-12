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

jimport('joomla.application.component.model');

ES::import( 'admin:/includes/model' );

class EasySocialModelCovers extends EasySocialModel
{
	function __construct()
	{
		parent::__construct( 'covers' );
	}

	/**
	 * Determines if one can use the default avatar given the unique id, unique type and default avatar id.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function isAllowed( $id , $uid , $type = SOCIAL_TYPE_PROFILES )
	{
		$db 		= ES::db();
		$query 		= array();

		$query[]	= 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__social_default_covers' );
		$query[]	= 'WHERE ' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $id );
		$query[]	= 'AND ' . $db->nameQuote( 'uid' ) . '=' . $db->Quote( $uid );
		$query[]	= 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( $type );

		$query 		= implode( ' ' , $query );
		$db->setQuery( $query );

		$allowed 	= $db->loadResult() > 0 ? true : false;

		return $allowed;

	}

	/**
	 * Retrieves a list of default avatars for this profile type.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The unique id
	 * @param	string	The unique type. E.g: @SOCIAL_TYPE_USER
	 * @return	Array	A list of default avatars.
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getDefaultCovers( $uid , $type = SOCIAL_TYPE_PROFILES )
	{
		$db     = ES::db();

		$query		= array();

		$query[]	= 'SELECT * FROM ' . $db->nameQuote( '#__social_default_covers' );
		$query[]	= 'WHERE ' . $db->nameQuote( 'uid' ) . '=' . $db->Quote( $uid );
		$query[]	= 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( $type );

		$query 		= implode( ' ' , $query );
		$db->setQuery( $query );

		$result		= $db->loadObjectList();

		if( !$result )
		{
			return $result;
		}

		$covers 	= array();

		foreach( $result as $row )
		{
			$cover 	= ES::table( 'DefaultCover' );
			$cover->bind( $row );

			$covers[]	= $cover;
		}

		return $covers;
	}

	/**
	 * Retrieve a photo object
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getPhoto($uid, $type = SOCIAL_TYPE_USER)
	{
		$db = ES::db();

		$query = 'SELECT b.* FROM ' . $db->nameQuote('#__social_covers') . ' as a';
		$query .= ' INNER JOIN ' . $db->nameQuote('#__social_photos') . ' as b ON a.`photo_id` = b.`id`';
		$query .= ' WHERE a.`uid`=' . $db->Quote($uid);
		$query .= ' AND a.`type`=' . $db->Quote($type);

		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result) {
			return false;
		}

		$photo = ES::table('Photo');
		$photo->bind($result);

		return $photo;
	}

}
