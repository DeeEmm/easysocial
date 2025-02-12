<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelAvatars extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('avatars');
	}

	/**
	 * Retrieves the list of items which stored in Amazon
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function getAvatarsStoredExternally($storageType = 'amazon', $options = array())
	{
		// Get the number of files to process at a time
		$config = ES::config();
		$limit = $config->get('storage.amazon.limit', 10);

		$db = ES::db();
		$sql = $db->sql();
		$sql->select('#__social_avatars');
		$sql->where('storage', $storageType);

		// If there's an exclusion list, exclude it
		$exclusion = isset($options['exclusion']) ? $options['exclusion'] :'';

		if (!empty($exclusion)) {

			// Ensure that it's an array
			$exclusion = ES::makeArray($exclusion);

			foreach ($exclusion as $id) {
				$sql->where('id', $id, '!=', 'AND');
			}
		}

		$sql->limit($limit);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Deletes a list of default avatars given the unique id and type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteDefaultAvatars( $uid , $type = SOCIAL_TYPE_PROFILES )
	{
		$avatars 	= $this->getDefaultAvatars( $uid , $type );

		if( !$avatars )
		{
			return;
		}

		jimport( 'joomla.filesystem.folder' );

		$config		= ES::config();

		// Build the path to the default avatars.

		// Get the default avatars storage location.
		$path 	= JPATH_ROOT . '/' . ES::cleanPath( $config->get( 'avatars.storage.container' ) ) . '/' . ES::cleanPath( $config->get( 'avatars.storage.default' ) );
		$path 	= $path . '/' . ES::cleanPath( $config->get( 'avatars.storage.defaults.' . $type ) );
		$path = $path . '/' . $uid;

		if( !JFolder::exists( $path ) )
		{
			$this->setError( JText::_( 'Default avatars path does not exist.' ) );
			return false;
		}

		$state = JFolder::delete( $path );

		return $state;
	}

	/**
	 * Retrieves the photo object
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getPhoto($uid, $type = SOCIAL_TYPE_USER)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_avatars', 'a');
		$sql->column('b.*');
		$sql->join('#__social_photos', 'b', 'INNER');
		$sql->on('a.photo_id', 'b.id');
		$sql->where('a.uid', $uid);
		$sql->where('a.type', $type);

		$db->setQuery($sql);
		$result = $db->loadObject();

		if (!$result) {
			return false;
		}

		$photo 	= ES::table('Photo');
		$photo->bind($result);

		return $photo;
	}

	/**
	 * Retrieves a list of avatars on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAvatars( $options = array() )
	{
		$db		= ES::db();
		$sql 	= $db->sql();

		$sql->select( '#__social_avatars' );

		$storage	= isset( $options[ 'storage' ] ) ? $options[ 'storage' ] : '';

		if( $storage )
		{
			$sql->where( 'storage' , $storage );
		}

		$uploaded	= isset( $options[ 'uploaded' ] ) ? $options[ 'uploaded' ] : '';

		if ($uploaded) {
			$sql->where( 'avatar_id' , 0 );
			$sql->where( 'small' , '' , '!=' );
		}

		$limit 	= isset( $options[ 'limit' ] ) ? $options[ 'limit' ] : 10;

		$sql->limit( $limit );

		// Determines if we should order by specific ordering
		$ordering 	= isset($options['ordering']) ? $options['ordering'] : '';

		if ($ordering) {

			$sorting 	= isset($options['sort']) ? $options['sort'] : 'DESC';

			if ($ordering == 'random') {
				$sql->order('', '', 'RAND');
			}

			if ($ordering == 'id') {
				$sql->order('id', $sorting);
			}

		}

		// If there's an exclusion list, exclude it
		$exclusion = isset($options['exclusion']) ? $options['exclusion'] :'';

		if (!empty($exclusion)) {

			// Ensure that it's an array
			$exclusion	= ES::makeArray($exclusion);

			foreach($exclusion as $id) {
				$sql->where('id', $id, '!=', 'AND');
			}

		}

		$db->setQuery( $sql );

		$rows 	= $db->loadObjectList();

		if (!$rows) {
			return $rows;
		}

		$avatars 	= array();

		foreach ($rows as $row) {
			$avatar 	= ES::table( 'Avatar' );
			$avatar->bind( $row );

			$avatars[]	= $avatar;
		}

		return $avatars;
	}

	/**
	 * Retrieves a list of default avatars for a specific type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getDefaultAvatars($uid, $type = SOCIAL_TYPE_PROFILES, $defaultOnly = false)
	{
		$db 		= ES::db();
		$query		= array();

		$key = $type . '.avatar.' . $uid;

		if (ES::cache()->exists($key)) {
			return ES::cache()->get($key);
		}

		$query[]	= 'SELECT * FROM ' . $db->nameQuote( '#__social_default_avatars' );
		$query[]	= 'WHERE ' . $db->nameQuote( 'uid' ) . '=' . $db->Quote( $uid );
		$query[]	= 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( $type );

		if ($defaultOnly) {
			$query[]	= 'AND ' . $db->nameQuote('default') . '=' . $db->Quote(true);
		}

		$query 		= implode( ' ' , $query );
		$db->setQuery( $query );

		$result		= $db->loadObjectList();

		if( !$result )
		{
			return $result;
		}

		$avatars    = array();

		foreach( $result as $row )
		{
			$avatar = ES::table( 'DefaultAvatar' );
			$avatar->bind( $row );

			$avatars[]  = $avatar;
		}

		return $avatars;
	}

	public function preloadDefaultAvatar($uids, $type = SOCIAL_TYPE_PROFILES)
	{
		static $_cache = array();

		$db = ES::db();
		$query = array();

		$uids = array_unique($uids);

		$userIds = implode(',', $uids);
		$userIds = trim($userIds);

		if (! $userIds) {
			return array();
		}

		// determine if we wanan get from cache or not.
		$doCache = false;
		$idx = $type;

		if (count($uids) == 1) {
			$idx = $uids[0] . '-' . $type;
			$doCache = true;
		}

		$query[] = 'SELECT * FROM ' . $db->nameQuote( '#__social_default_avatars' );
		$query[] = 'WHERE ' . $db->nameQuote( 'uid' ) . ' IN (' . $userIds . ')';
		$query[] = 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( $type );

		$query  = implode( ' ' , $query );

		if (!$doCache || ($doCache && !isset($_cache[$idx]))) {
			$db->setQuery($query);
			$_cache[$idx] = $db->loadObjectList();
		}

		$result	= $_cache[$idx];

		if (!$result) {
			return $result;
		}

		$avatars = array();

		foreach ($result as $row) {
			$avatar = ES::table('DefaultAvatar');
			$avatar->bind($row);

			//cache JTABLE
			$key = 'DefaultAvatar.' . $avatar->id;
			ES::cache()->set($key, $avatar);

			$avatars[$row->uid][] = $avatar;
		}

		return $avatars;
	}

	/**
	 * Determines if one can use the default avatar given the unique id, unique type and default avatar id.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isAllowed( $id , $uid , $type = SOCIAL_TYPE_PROFILES )
	{
		$db 		= ES::db();
		$sql 		= $db->sql();

		$sql->select( '#__social_default_avatars' );
		$sql->where( 'id' , $id );
		$sql->where( 'uid' , $uid );
		$sql->where( 'type' , $type );

		$db->setQuery( $sql );

		$allowed 	= $db->loadResult() > 0 ? true : false;

		return $allowed;
	}

	/**
	 * Determines if the current user editing the user avatar photo from edit album page.
	 *
	 * @since	3.2.6
	 * @access	public
	 */
	public function isEditingCurrentAvatar($uid, $photoId, $type = SOCIAL_TYPE_USER)
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT * FROM ' . $db->nameQuote('#__social_avatars');
		$query[] = 'WHERE ' . $db->nameQuote('uid') . '=' . $db->Quote($uid);
		$query[] = 'AND ' . $db->nameQuote('type') . '=' . $db->Quote($type);
		$query[] = 'AND ' . $db->nameQuote('photo_id') . '=' . $db->Quote($photoId);

		$query = implode(' ', $query);
		$db->setQuery($query);

		$isEditingCurrentAvatar = $db->loadResult() > 0 ? true : false;

		return $isEditingCurrentAvatar;
	}
}
