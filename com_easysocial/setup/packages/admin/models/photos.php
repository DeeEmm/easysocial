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
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelPhotos extends EasySocialModel
{
	static $_photometas = array();
	static $_cache = null;

	public function __construct()
	{
		if (is_null(self::$_cache)) {
			self::$_cache = false;
		}

		parent::__construct('photos');
	}

	/**
	 * Retrieves the total amount of storage used by a specific user
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getDiskUsage($userId, $unit = 'b')
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'SELECT SUM(b.' . $db->quoteName('total_size') . ') FROM '
				. $db->quoteName('#__social_albums') . ' AS a '
				. 'INNER JOIN ' . $db->quoteName('#__social_photos') . ' AS b '
				. 'ON a.' . $db->quoteName('id') . ' = b.' . $db->quoteName('album_id') . ' '
				. 'WHERE a.' . $db->quoteName('user_id') . '=' . $db->Quote($userId);


		$sql->raw($query);
		$db->setQuery($sql);

		$total = $db->loadResult();

		if ($unit == 'b') {
			return $total;
		}

		if ($unit == 'mb') {
			$total = round(($total / 1024) / 1024, 2);
		}

		return $total;
	}

	/**
	 * Given the album id, determine the next ordering for the photo
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function getNextOrdering($albumId)
	{
		$db = ES::db();

		$query = [
			'SELECT MAX(`ordering`) + 1 FROM `#__social_photos` WHERE `album_id`=' . $db->Quote((int) $albumId)
		];

		$db->setQuery($query);

		$next = (int) $db->loadResult();

		return $next;
	}


	/**
	 * Retrieves the list of items which stored in Amazon
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function getPhotosStoredExternally($storageType = 'amazon', $options = array())
	{
		// Get the number of files to process at a time
		$config = ES::config();
		$limit = $config->get('storage.amazon.limit', 10);

		$db = ES::db();
		$sql = $db->sql();
		$sql->select('#__social_photos');
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
	 * Stores the exif data for this photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function storeCustomMeta(SocialTablePhoto $photo, SocialExif $exif)
	{
		$config = ES::config();
		$storableItems = $config->get('photos.exif');

		foreach ($storableItems as $property) {
			$method = 'get' . ucfirst($property);

			if (is_callable(array($exif, $method))) {
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;

				$meta->group = "exif";
				$meta->property = $property;

				$meta->value = '';

				$value = $exif->$method();

				if (!is_array($value)) {
					$meta->value = ES::string()->escape(strip_tags($value));
				}

				$meta->store();
			}
		}

		return true;
	}

	/**
	 * Retrieve a list of tags for a particular photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTags($id , $peopleOnly = false)
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select('#__social_photos_tag');
		$sql->where('photo_id' , $id);

		if ($peopleOnly) {
			$sql->where('uid' , '' , '!=' , 'AND');
			$sql->where('type' , 'person' , '=' , 'AND');
		}

		$db->setQuery($sql);
		$result 	= $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$tags = array();

		foreach ($result as $row) {
			$tag = ES::table('PhotoTag');
			$tag->bind($row);

			$tags[]	= $tag;
		}

		return $tags;
	}


	/**
	 * Some desc
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function tag()
	{
	}

	/**
	 * Get a total photos for user/cluster
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalPhotos($options = array())
	{
		$db	= ES::db();

		// Get the query object
		$sql = $db->sql();

		$state = isset($options['state']) ? $options['state'] : SOCIAL_STATE_PUBLISHED;
		$albumId = isset($options['album_id']) ? $options['album_id'] : null;
		$storage = isset($options['storage']) ? $options['storage'] : '';
		$uid = isset($options['uid']) ? $options['uid'] : false;
		$day = isset($options['day']) ? $options['day'] : false;
		$type = isset($options['type']) ? $options['type'] : SOCIAL_TYPE_USER;

		$query = 'select count(1) from `#__social_photos`';

		if ($state == 'all') {
			$query .= ' WHERE (`state`=' . $db->Quote(SOCIAL_STATE_PUBLISHED) . ' OR `state`=' . $db->Quote(SOCIAL_STATE_UNPUBLISHED) . ')';
		} else {
			$query .= ' where `state` = ' . $db->Quote($state);
		}

		if ($uid) {
			$query .= ' and `uid` = ' . $db->Quote($uid);
		}

		if ($type) {
			$query .= ' and `type` = ' . $db->Quote($type);
		}

		if ($albumId) {
			$query .= ' and `album_id` = ' . $db->Quote($albumId);
		}


		if ($storage) {
			$query .= ' and `storage` = ' . $db->Quote($storage);
		}

		if ($day) {
			$start = $day . ' 00:00:01';
			$end = $day . ' 23:59:59';
			$query .= ' and (`created` >= ' . $db->Quote($start) . ' and `created` <= ' . $db->Quote($end) . ')';
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$count = $db->loadResult();
		return $count;
	}



	/**
	 * Retrieves list of photos
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlbumPhotos($albumIds, $limit = 5)
	{
		$db = ES::db();
		$sql = $db->sql();
		$config = ES::config();

		$privacy = $config->get('privacy.enabled') ? true : false;

		$viewer = ES::user()->id;

		if (!ES::user()->isSiteAdmin() && $privacy) {

			$query[] = "select * from (";

			$segments = array();
			foreach($albumIds as $aid) {
				$tmp = " (select a.*";
				$tmp .= " from `#__social_photos` as a";
				$tmp .= " where a.`state` = " . $db->Quote(1);
				$tmp .= " and a.`album_id` = " . $db->Quote($aid);
				$tmp .= " order by a.`ordering` limit 5)";
				$segments[] = $tmp;
			}

			$query[] = implode(' union all ', $segments);
			$query[] = ") as x";
			// privacy here.
			$query[] = ' WHERE (';

			//public
			$query[] = '(x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

			//member
			$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				//friends
				$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $this->generateIsFriendSQL('x.`user_id`', $viewer) . ') > 0)) OR ';
			} else {
				// fall back to member
				$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			//only me
			$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (x.`user_id` = ' . $viewer . ')) OR ';

			// custom
			$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (x.`custom_access` LIKE ' . $db->Quote('%,' . $viewer . ',%') . '   )) OR ';

			// field
			if ($config->get('users.privacy.field')) {
				// field
				$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
				$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
				$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
				$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
				$fieldPrivacyQuery .= ' where fi.`uid` = x.`id`';
				$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('photos');
				$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
				$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
				$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

				$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (x.`field_access` <= ' . $fieldPrivacyQuery . ')) OR ';
			} else {
				$query[] = '((x.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			// my own items.
			$query[] = '(x.`user_id` = ' . $viewer . ')';

			// privacy checking end here.
			$query[] = ')';

		} else {

			$query[] = "select * from (";

			$segments = array();
			foreach($albumIds as $aid) {
				$tmp = " (select a.*";
				$tmp .= " from `#__social_photos` as a";
				$tmp .= " where a.`state` = " . $db->Quote(1);
				$tmp .= " and a.`album_id` = " . $db->Quote($aid);
				$tmp .= " order by a.`ordering` limit 5)";
				$segments[] = $tmp;
			}

			$query[] = implode(' union all ', $segments);
			$query[] = ") as x";

		}


		$query = implode(' ', $query);

		// echo $query;exit;

		$sql->raw($query);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$photos = array();
		$photosIds = array();

		foreach($result as $row) {
			$photo = ES::table('Photo');
			$photo->bind($row);

			$photos[$photo->album_id][]	= $photo;

			$photosIds[] = $photo->id;
		}


		if ($photosIds) {
			// lets cache photos meta here.
			ES::cache()->cachePhotos($photosIds);
		}


		return $photos;
	}

	/**
	 * Retrieves list of photos for module
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getModulePhotos($options = [])
	{
		$db = ES::db();
		$sql = $db->sql();
		$config = ES::config();

		$albumId = isset($options['album_id']) ? $options['album_id'] : null;
		$limit = isset($options['limit']) ? $options['limit'] : 10;
		$uid = isset($options['uid']) ? $options['uid'] : false;
		$privacy = isset($options['privacy']) ? $options['privacy'] :'';
		$noavatar = isset($options['noavatar']) && $options['noavatar'] ? true : false;
		$nocover = isset($options['nocover']) && $options['nocover'] ? true : false;
		$ordering = isset($options['ordering']) ? $options['ordering'] : '';
		$featured = isset($options['featured']) ? $options['featured'] : false;

		$my = ES::user();
		$viewer = $my->id;

		$query = [];
		$query[] = "select a.*";
		$query[] = "from `#__social_photos` as a";

		if ($noavatar || $nocover || $privacy) {
			$query[] = " inner join `#__social_albums` as b on a.album_id = b.id";
		}

		// cluster privacy
		if ($privacy) {
			// join with cluster table.
			$query[] = " left join `#__social_clusters` as cc on a.`uid` = cc.`id` and a.`type` = cc.`cluster_type`";
			if ($viewer) {
				$query[] = " left join `#__social_events_meta` AS em ON cc.`id` = em.`cluster_id`";
			}
		}

		$query[] = "where a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		if ($featured) {
			$query[] = "and a.`featured` = " . $db->Quote(1);
		}

		if (!is_null($albumId)) {
			$query[] = "and a.`album_id` = " . $db->Quote($albumId);
		}

		// If user id is specified, we only fetch photos that are created by the user.
		if ($uid) {
			$query[] = "and a.`uid` = " . $db->Quote($uid);
			$query[] = "and a.`type` = " . $db->Quote(SOCIAL_TYPE_USER);
		}

		if ($noavatar && $nocover) {
			$query[] = " and b.`core` not in (1, 2)";
		} else if ($noavatar) {
			$query[] = " and b.`core` != 1";
		} else if ($nocover) {
			$query[] = " and b.`core` != 2";
		}

		// Marketplace photos shouldn't be display in photo module
		$query[] = "and a.`type` != " . $db->Quote(SOCIAL_TYPE_MARKETPLACE);

		if ($privacy && !$my->isSiteAdmin()) {
			// cluster privacy
			$query[] = 'AND (';

			// Check for album password as well
			$query[] = '(a.`type` = ' . $db->Quote(SOCIAL_TYPE_USER);
			$query[] = ') OR';

			$query[] = '(a.`type` != ' . $db->Quote(SOCIAL_TYPE_USER) . ' and cc.`type` IN (1,4))';

			if ($viewer) {
				$query[] = 'OR (a.`type` != ' . $db->Quote(SOCIAL_TYPE_USER) . ' and cc.`type` > 1 and ' . $viewer . ' IN (select scn.`uid` from `#__social_clusters_nodes` as scn where (scn.`cluster_id` = cc.`id` OR scn.`cluster_id` = em.`group_id`) and scn.`type` = ' . $db->Quote(SOCIAL_TYPE_USER) . ' and scn.`state` = 1))';
			}

			$query[] = ')';

			$query[] = 'AND (a.`user_id` = ' . $db->Quote($viewer) . ' AND (b.`password` IS NOT NULL OR b.`password` != ' . $db->Quote('') . '))';
			$query[] = 'OR';
			$query[] = '( a.`user_id` != ' . $db->Quote($viewer) . ' AND (b.`password` IS NULL OR b.`password` = ' . $db->Quote('') . '))';
		}

		// user privacy
		if ($privacy && $config->get('privacy.enabled') && !$my->isSiteAdmin()) {

			// privacy here.
			$query[] = ' AND (';

			//public
			$query[] = '(a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

			//member
			$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				//friends
				$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $this->generateIsFriendSQL('a.`user_id`', $viewer) . ') > 0)) OR ';
			} else {
				// fall back to member
				$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			//only me
			$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (a.`user_id` = ' . $viewer . ')) OR ';

			// custom
			$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (a.`custom_access` LIKE ' . $db->Quote('%,' . $viewer . ',%') . '  )) OR ';

			// field
			if ($config->get('users.privacy.field')) {
				// field
				$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
				$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
				$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
				$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
				$fieldPrivacyQuery .= ' where fi.`uid` = a.`id`';
				$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('photos');
				$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
				$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
				$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

				$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (a.`field_access` <= ' . $fieldPrivacyQuery . ')) OR ';
			} else {
				$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $viewer . ' > 0)) OR ';
			}


			// my own items.
			$query[] = '(a.`user_id` = ' . $viewer . ')';

			// privacy checking end here.
			$query[] = ')';


			// album privacy
			$query[] = ' AND (';

			//public
			$query[] = '(b.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

			//member
			$query[] = '((b.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				//friends
				$query[] = '((b.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $this->generateIsFriendSQL('a.`user_id`', $viewer) . ') > 0)) OR ';
			} else {
				// fall back to member
				$query[] = '((b.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			//only me
			$query[] = '((b.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (a.`user_id` = ' . $viewer . ')) OR ';

			// custom
			$query[] = '((b.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (b.`custom_access` LIKE ' . $db->Quote('%,' . $viewer . ',%') . '  )) OR ';

			// field
			if ($config->get('users.privacy.field')) {
				// field
				$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
				$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
				$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
				$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
				$fieldPrivacyQuery .= ' where fi.`uid` = b.`id`';
				$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('albums');
				$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
				$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
				$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

				$query[] = '((b.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (b.`field_access` <= ' . $fieldPrivacyQuery . ')) OR ';
			} else {
				$query[] = '((b.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $viewer . ' > 0)) OR ';
			}


			// my own items.
			$query[] = '(b.`user_id` = ' . $viewer . ')';

			// privacy checking end here.
			$query[] = ')';

		}

		if (!empty($ordering)) {

			if ($ordering === 'random') {

				$rndColumns = ['a.id', 'a.title', 'a.ordering', 'a.uid', 'a.album_id', 'a.id', 'a.title'];
				$rndSorts = ['asc', 'desc', 'desc', 'asc', 'asc', 'desc'];

				$rndColumn = $rndColumns[array_rand($rndColumns)];
				$rndSort = $rndSorts[array_rand($rndSorts)];

				$query[] = "order by $rndColumn $rndSort";
			}

			if ($ordering === 'created') {
				$query[] = "order by a.`created` DESC";
			}

			if ($ordering === 'ordering') {
				$query[] = "order by a.`ordering` DESC";
			}

		} else {
			$query[] = "order by a.`ordering` DESC";
		}

		$query[] = "limit 0," . $limit;

		$query = implode(' ', $query);

		$sql->raw($query);

// var_dump($sql->toString());exit;
		// echo $sql;
		// echo '<br />';
		// exit;

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$photos = [];

		foreach ($result as $row) {
			$photo = ES::table('Photo');
			$photo->bind($row);

			$photos[] = $photo;
		}

		return $photos;
	}

	/**
	 * Method to retrieve unused legacy image sizes
	 *
	 * @since   2.2.3
	 * @access  public
	 */
	public function getLegacyPhotos($limit = 20)
	{
		$db = $this->db;

		$query = 'SELECT * FROM ' . $db->nameQuote('#__social_photos_meta');
		$query .= ' WHERE ' . $db->nameQuote('property') . ' IN (' . $db->Quote('stock') . ', ' . $db->Quote('square') . ', ' . $db->Quote('featured') . ')';
		$query .= ' AND ' . $db->nameQuote('group') . ' = ' . $db->Quote('path');

		$query .= ' LIMIT 0,' . $limit;

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves list of photos
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPhotos($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();
		$config = ES::config();

		$albumId = isset($options['album_id']) ? $options['album_id'] : null;
		$start = isset($options['start']) ? $options['start'] : 0;
		$limit = isset($options['limit']) ? $options['limit'] : 10;

		$state = isset($options['state']) ? $options['state'] : SOCIAL_STATE_PUBLISHED;

		// Render photos by specific object ype
		$uid = isset($options['uid']) ? $options['uid'] : false;
		$type = ES::normalize($options, 'type', 'user');

		$storage = isset($options['storage']) ? $options['storage'] : '';
		$pagination = isset($options['pagination']) ? $options['pagination'] : true;

		if (!$limit) {
			$pagination = false;
		}

		$exclusion = isset($options['exclusion']) ? $options['exclusion'] :'';
		$inclusion = isset($options['inclusion']) ? $options['inclusion'] :'';
		$privacy = isset($options['privacy']) ? $options['privacy'] :'';
		$isUnlocked = isset($options['isUnlocked']) ? $options['isUnlocked'] : false;

		$userId = isset($options['userId']) ? $options['userId'] : false;
		$streamId = isset($options['streamId']) ? $options['streamId'] : false;

		// Ensure that we respect the privacy settings
		if (!$config->get('privacy.enabled')) {
			$privacy = false;
		}

		$inclusion = isset($options['inclusion']) ? $options['inclusion'] : '';

		$noavatar = isset($options['noavatar']) && $options['noavatar'] ? true : false;
		$nocover = isset($options['nocover']) && $options['nocover'] ? true : false;

		$ordering = isset($options['ordering']) ? $options['ordering'] : 'created';
		$sort = isset($options['sort']) ? $options['sort'] : $config->get('photos.layout.ordering');

		$query = array();

		$query[] = "select a.*";
		$query[] = "from `#__social_photos` as a";

		$query[] = " inner join `#__social_albums` as b on a.album_id = b.id";

		if ($streamId) {
			// we only want to retrieve photos from stream item.
			$query[] = " inner join `#__social_stream_item` as si on si.`context_id` = a.`id` and si.`context_type` = 'photos'";

		}

		$query[] = "where a.`state` = " . $db->Quote($state);


		if (!is_null($albumId)) {
			$query[] = "and a.`album_id` = " . $db->Quote($albumId);
		}

		// If user id is specified, we only fetch photos that are created by the user.
		if ($uid) {
			$query[] = "and a.`uid` = " . $db->Quote($uid);
			$query[] = "and a.`type` = " . $db->Quote($type);
		}

		// Get all photos created by user including from the clusters
		if ($userId) {
			$query[] = "and a.`user_id` = " . $db->Quote($userId);
		}

		if ($storage) {
			$query[] = " and a.`storage` = " . $db->Quote($storage);
		}

		// If there's an exclusion list, exclude it
		if (!empty($exclusion)) {

			// Ensure that it's an array
			$exclusion = ES::makeArray($exclusion);

			$eIds = implode(',', $exclusion);
			$query[] = "and a.`id` NOT IN ($eIds)";
		}

		if (!empty($inclusion)) {
			$inclusion = ES::makeArray($inclusion);

			$includeIds = implode(',', $inclusion);
			$query[] = "and a.`id` IN (" . $includeIds . ")";
		}

		if ($streamId) {
			$query[] = "and si.`uid` = " . $db->Quote($streamId);
		}

		if ($noavatar && $nocover) {
			$query[] = " and b.`core` not in (1, 2)";
		} else if ($noavatar) {
			$query[] = " and b.`core` != 1";
		} else if ($nocover) {
			$query[] = " and b.`core` != 2";
		}

		if ($privacy && !ES::user()->isSiteAdmin()) {

			$viewer = ES::user()->id;

			if (!$isUnlocked) {
				$query[] = 'AND (';
				$query[] = '((b.`password` IS NOT NULL OR b.`password` != ' . $db->Quote('') . ') AND a.`user_id` = ' . $db->Quote($viewer) . ')';
				$query[] = 'OR';
				$query[] = '((b.`password` IS NULL OR b.`password` = ' . $db->Quote('') . ')))';
			}

			// privacy here.
			$query[] = ' AND (';

			//public
			$query[] = '(a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

			//member
			$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

			if ($config->get('friends.enabled')) {
				//friends
				$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $this->generateIsFriendSQL('a.`user_id`', $viewer) . ') > 0)) OR ';
			} else {
				// fall back to member
				$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			//only me
			$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (a.`user_id` = ' . $viewer . ')) OR ';

			// custom
			$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (a.`custom_access` LIKE ' . $db->Quote('%,' . $viewer . ',%') . '   )) OR ';

			// field
			if ($config->get('users.privacy.field')) {
				// field
				$fieldPrivacyQuery = '(select count(1) from `#__social_privacy_items_field` as fa';
				$fieldPrivacyQuery .= ' inner join `#__social_privacy_items` as fi on fi.`id` = fa.`uid` and fa.utype = ' . $db->Quote('item');
				$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`unique_key` = ff.`unique_key`';
				$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
				$fieldPrivacyQuery .= ' where fi.`uid` = a.`id`';
				$fieldPrivacyQuery .= ' and fi.`type` = ' . $db->Quote('photos');
				$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
				$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
				$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`value`,' . $db->Quote('%') . '))';

				$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (a.`field_access` <= ' . $fieldPrivacyQuery . ')) OR ';
			} else {
				$query[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $viewer . ' > 0)) OR ';
			}

			// my own items.
			$query[] = '(a.`user_id` = ' . $viewer . ')';

			// privacy checking end here.
			$query[] = ')';

		}

		if (!empty($ordering)) {

			if ($ordering == 'random') {

				$rndColumns = array('a.id', 'a.title', 'a.ordering', 'a.uid', 'a.album_id', 'a.id', 'a.title');
				$rndSorts = array('asc', 'desc', 'desc', 'asc', 'asc', 'desc');

				$rndColumn = $rndColumns[array_rand($rndColumns)];
				$rndSort = $rndSorts[array_rand($rndSorts)];

				$query[] = "order by $rndColumn $rndSort";
			}

			if ($ordering == 'created') {
				$query[] = "order by a.`created` $sort";
			}

			if ($ordering == 'ordering') {
				$query[] = "order by a.`ordering` $sort";
			}

			if ($ordering == 'album_id') {
				$query[] = "order by a.`album_id` $sort";
			}

		} else {
			$query[] = "order by a.`ordering` $sort";
		}


		// Determine if we should paginate items
		if ($pagination) {
			$query[] = "limit " . $start . "," . $limit;
		}

		$query = implode(' ', $query);

		$sql->raw($query);

		// if ($privacy) {
		// 	echo $sql;
		// 	echo '<br />';
		// 	exit;
		// }

		$db->setQuery($sql);


		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$photos = array();

		foreach ($result as $row) {
			$photo = ES::table('Photo');
			$photo->bind($row);

			$photos[] = $photo;
		}

		return $photos;
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function generateIsFriendSQL($source, $target)
	{
		$query = "select count(1) from `#__social_friends` where (`actor_id` = $source and `target_id` = $target) OR (`target_id` = $source and `actor_id` = $target) and `state` = 1";
		return $query;
	}

	/**
	 * Retrieves the meta data about a photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getMeta($photoId, $group = '', $property = false)
	{
		$db = ES::db();
		$sql 	= $db->sql();

		if (! self::$_cache) {
			$sql->select('#__social_photos_meta');
			$sql->where('photo_id' , $photoId);

			if ($group) {
				$sql->where('group', $group);
			}

			if ($property) {
				$sql->where('property', $property);
			}

			$db->setQuery($sql);
			$metas 	= $db->loadObjectList();

			return $metas;
		}


		if (!isset(self::$_photometas[$photoId])) {

			self::$_photometas[$photoId]	= array();

			$sql->select('#__social_photos_meta');
			$sql->where('photo_id', $photoId);

			$db->setQuery($sql);

			$metas 	= $db->loadObjectList();

			if ($metas) {
				foreach ($metas as $row) {
					self::$_photometas[$row->photo_id][$row->group][$row->property][] = $row;
				}
			}
		}

		// Default values
		$metas = array();

		if ($group && $property) {
			if (isset(self::$_photometas[$photoId][$group][$property])) {
				$metas = self::$_photometas[$photoId][$group][$property];

				return $metas;
			}

			return $metas;
		}


		if ($group) {
			if (isset(self::$_photometas[$photoId][$group])) {
				foreach (self::$_photometas[$photoId][$group] as $property => $items) {
					if ($items) {
						foreach ($items as $item) {
							$metas[] = $item;
						}
					}
				}

				return $metas;
			}

			return $metas;
		}


		if (isset(self::$_photometas[$photoId])) {
			foreach (self::$_photometas[$photoId] as $group => $items) {
				if ($items) {
					foreach ($items as $item) {
						$metas[] = $item;
					}
				}
			}
		}

		return $metas;
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setCacheable($cache = false)
	{
		self::$_cache  = $cache;
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setMetasBatch($ids)
	{

		$db = ES::db();
		$sql = $db->sql();

		$photoIds = array();

		foreach ($ids as $pid) {
			if (! isset(self::$_photometas[$pid])) {
				$photoIds[] = $pid;
			}
		}

		if ($photoIds) {
			foreach ($photoIds as $pid) {
				self::$_photometas[$pid] = array();
			}

			$query = '';
			$idSegments = array_chunk($photoIds, 5);
			//$idSegments = array_chunk($photoIds, count($photoIds));

			for ($i = 0; $i < count($idSegments); $i++) {
				$segment = $idSegments[$i];
				$ids = implode(',', $segment);

				$query .= 'select * from `#__social_photos_meta` where `photo_id` IN (' . $ids . ')';

				if (($i + 1)  < count($idSegments)) {
					$query .= ' UNION ';
				}
			}

			$sql->raw($query);
			$db->setQuery($sql);

			$results = $db->loadObjectList();

			if ($results) {
				foreach($results as $row)
				{
					self::$_photometas[$row->photo_id][$row->group][$row->property][] = $row;
				}
			}
		}
	}

	/**
	 * Allows caller to delete all the metadata about a photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteMeta($photoId , $group = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_photos_meta');
		$sql->where('photo_id' , $photoId);

		if (!is_null($group))
		{
			$sql->where('group' , $group);
		}

		$db->setQuery($sql);
		$db->Query();

		return true;
	}

	/**
	 * Deletes all tags associated with a photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteTags($photoId)
	{
		$db = ES::db();
		$sql 	= $db->sql();

		$sql->delete('#__social_photos_tag');
		$sql->where('photo_id' , $photoId);

		$db->setQuery($sql);

		$db->Query();
	}


	/**
	 * Deletes all tags associated with a photo
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteSystemNotifications($photoId)
	{
		$db = ES::db();

		// delete all notification items that associated with this photo.
		$url = '%option=com_easysocial&view=photos&layout=item&id=' . $photoId . ':%';

		$query = 'DELETE FROM `#__social_notifications` WHERE `url` LIKE ' . $db->Quote($url);
		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Deletes all photos within the album.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteAlbumPhotos($albumId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_photos');
		$sql->column('id');
		$sql->where('album_id', $albumId);

		$db->setQuery($sql);

		$photoIds 	= $db->loadColumn();

		if (!$photoIds) {
			return false;
		}

		foreach ($photoIds as $id) {
			$photo = ES::table('Photo');
			$photo->load($id);

			$photo->delete();
		}

		return true;
	}

	/**
	 * Determines if the photo is used as a profile cover
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isProfileCover($photoId , $uid , $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_covers');
		$sql->column('COUNT(1)');
		$sql->where('photo_id' , $photoId);
		$sql->where('uid' , $uid);
		$sql->where('type' , $type);

		$db->setQuery($sql);

		$exists	= $db->loadResult() > 0 ? true : false;

		return $exists;
	}

	/**
	 * Update the ordering of each of the photos
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function pushPhotosOrdering($albumId, $except = 0, $index = 0, $type = '+')
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'UPDATE `#__social_photos` SET `ordering` = `ordering` ' . $type . ' 1';
		$query .= ' WHERE `album_id` = ' . $db->Quote($albumId);
		$query .= ' AND `ordering` >= ' . $db->Quote($index);
		$query .= ' AND `id` <> ' . $db->Quote($except);

		if ($type != '+') {
			$query .= ' AND `ordering` != ' . $db->Quote('0');
		}

		$sql->raw($query);

		$db->setQuery($sql);

		return $db->query();
	}

	/**
	 * Determines if the photo should be associated with the stream item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getPhotoStreamId($photoId, $verb, $validate = true)
	{
		static $_cache = array();

		$db = ES::db();
		$sql = $db->sql();

		$index = $photoId . $verb . $validate;

		if (! isset($_cache[$index])) {

			$sql->select('#__social_stream_item', 'a');

			// Always get the latest stream item
			$sql->column('a.uid', '', 'MAX');

			$sql->where('a.context_type', SOCIAL_TYPE_PHOTO);
			$sql->where('a.context_id', $photoId);

			if ($verb == 'upload') {
				$sql->where('(');
				$sql->where('a.verb', 'share');
				$sql->where('a.verb', 'upload', '=', 'OR');
				$sql->where('a.verb', 'create', '=', 'OR');
				$sql->where(')');
			} else if ($verb == 'add') {
				$sql->where('a.verb', 'create');
			} else {
				$sql->where('a.verb', $verb);
			}

			$db->setQuery($sql);

			$uid = (int) $db->loadResult();
			$_cache[$index] = $uid;

			if (!$uid) {
				$_cache[$index] = false;
				return;
			}

			// Check if the uid exists multiple times and if this is a shared record
			if ($validate && $verb == 'share') {

				$sql->clear();

				$sql->select('#__social_stream_item', 'a');
				$sql->column('COUNT(a.`uid`)');
				$sql->where('a.uid', $uid);

				$db->setQuery($sql);

				$total 	= $db->loadResult();

				if ($total == 1) {
					$_cache[$index] = false;
					return false;
				}

				// return $uid;
				$_cache[$index] = $uid;
			}
		}

		return $_cache[$index];
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPhotoStreamIdx($photoId, $verb)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_stream_item', 'a');
		$sql->column('a.uid');
		$sql->where('a.context_type', SOCIAL_TYPE_PHOTO);
		$sql->where('a.context_id', $photoId);
		$sql->where('a.verb', $verb);

		$db->setQuery($sql);

		$uid 	= (int) $db->loadResult();

		if (!$uid){
			return false;
		}

		// If the photo is uploaded in the story form, we need to link to the stream only when there's more than 1 photo
		if ($verb == 'share') {
			$sql->group('a.uid');
			$sql->having('count(a.uid)', '1', '=');
		}


		return $uid;
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delPhotoStream($photoId, $photoOnwerId, $albumId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select a.`id`, a.`uid` from `#__social_stream_item` as a";
		$query .= " where a.`context_type` = '" . SOCIAL_TYPE_PHOTO . "'";
		$query .= " and a.`context_id` = '$photoId'";
		$query .= " and a.`target_id` = '$albumId'";
		$query .= " and a.`actor_id` = '$photoOnwerId'";

		$sql->raw($query);
		$db->setQuery($sql);

		$row = $db->loadObject();

		if ($row) {
			$itemId 	= $row->id;
			$streamId 	= $row->uid;

			$query = "delete from `#__social_stream_item` where `id` = '$itemId'";
			$sql->raw($query);

			$db->setQuery($sql);
			$state = $db->query();

			//check if this stream id still have other records or not. if no, then we remove the main stream as well.
			$query = "select count(1) from `#__social_stream_item` where `uid` = '$streamId'";
			$sql->raw($query);
			$db->setQuery($sql);

			$result = $db->loadResult();

			if (empty($result)){
				$query = "delete from `#__social_stream` where `id` = '$streamId'";
				$sql->raw($query);

				$db->setQuery($sql);
				$state = $db->query();
			}


		}

		return true;
	}

	/**
	 * Retrieve photos that associated with stream uid.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getStreamPhotos($streamId)
	{
		$db = ES::db();

		$query = "select a.* from `#__social_photos` as a";
		$query .= " inner join `#__social_stream_item` as b on a.`id` = b.`context_id`";
		$query .= " where b.`uid` = " . $db->Quote($streamId);
		$query .= " and a.`state` in (" . $db->Quote(SOCIAL_STATE_PUBLISHED) . ',' . $db->Quote(SOCIAL_STATE_SCHEDULED) . ')';

		$db->setQuery($query);
		$results = $db->loadObjectList();

		$photos = array();

		if ($results) {
			foreach ($results as $row) {
				$tbl = ES::table('Photo');
				$tbl->bind($row);

				$photos[] = $tbl;
			}
		}

		return $photos;
	}

	/**
	 * update [add or remove] photos that associated with stream uid.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateStreamPhotos($streamId, $photos)
	{
		$db = ES::db();

		$photosRemove = array();

		if ($photos) {

			// get the default values
			$streamItem = ES::table('StreamItem');
			$streamItem->load(array('uid' => $streamId));

			// Now that we know the saving is successfull, we want to update the state of the photo table.
			foreach ($photos as $photoId) {

				if (! is_numeric($photoId)) {
					if (is_array($photoId)) {
						$photosRemove[] = $photoId['remove'];

					} else if (is_object()) {
						$photosRemove[] = $photoId->remove;

					}
					continue;
				}

				$table = ES::table('Photo');
				$table->load($photoId);

				// $album = ES::table('Album');
				// $album->load($table->album_id);

				$table->state = SOCIAL_STATE_PUBLISHED;

				if ($streamItem->isScheduled()) {
					$table->state = SOCIAL_STATE_SCHEDULED;
				}

				$table->store();

				// add into stream item.
				$item = ES::table('StreamItem');
				$item->bind($streamItem);

				// reset id and context_id;
				$item->id = null;
				$item->context_id = $photoId;

				// Let's try to store the stream item now.
				$item->store();
			}
		}

		if ($photosRemove) {

			// Now that we know the saving is successfull, we want to update the state of the photo table.
			foreach ($photosRemove as $photoId) {
				$table = ES::table('Photo');
				$table->load($photoId);

				$state = $table->delete();
			}
		}

		return true;
	}

	/**
	 * update photos stream access.
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function updateStreamAccess(SocialTableStream $streamTbl)
	{
		$db = ES::db();

		$privacyRule = 'photos.view';
		$element = 'photos';
		$uid = 0;

		$photoItems = array();

		// get photo items from this stream.
		$query = "select `context_id` from `#__social_stream_item` where `uid` = " . $db->Quote($streamTbl->id);
		$db->setQuery($query);
		$photoItems = $db->loadColumn();

		if (!$photoItems) {
			return false;
		}

		if ($photoItems && count($photoItems) == 1) {
			// Determine the privacy rule to use.
			$uid = $photoItems[0];
		}

		if ($photoItems && count($photoItems) > 1) {

			$photo = ES::table('photo');
			$photo->load($photoItems[0]);

			// Determine the privacy rule to use.
			$privacyRule = 'albums.view';
			$uid = $photo->album_id;

			// Load up the album object
			$album = ES::table('Album');
			$album->load($photo->album_id);

			// Determine if this album is story album
			if ($album->isStory()) {
				$uid = $streamTbl->id;
				$element = 'story';
				$privacyRule = 'core.view';
			}
		}

		$model = ES::model('Privacy');
		$pItem = $model->getPrivacyItem($uid, $element, $streamTbl->actor_id, $privacyRule);

		$access = $pItem->value;
		$privacyId = $pItem->id;

		$query = "update `#__social_stream`";
		$query .= " set `access` = " . $db->Quote($access);
		$query .= " , `privacy_id` = " . $db->Quote($privacyId);
		$query .= " where `id` = " . $db->Quote($streamTbl->id);

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}

	/**
	 * Delete the user's photos that has been posted in the cluster
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function deleteClusterPhotos($userId)
	{
		$db = ES::db();
		$query = array();

		// Get the photo data that belongs to the user first
		$query[] = 'SELECT * FROM ' . $db->nameQuote('#__social_photos');
		$query[] = 'WHERE ' . $db->nameQuote('user_id') . ' = ' . $db->quote($userId);
		$query[] = 'AND ' . $db->nameQuote('type') . ' != ' . $db->quote(SOCIAL_TYPE_USER);

		$query = implode(' ', $query);

		$db->setQuery($query);
		$results = $db->loadObjectList();

		if (!$results) {
			return;
		}

		foreach ($results as $row) {
			$table = ES::table('Photo');
			$table->bind($row);

			// Delete the photo
			$table->delete();
		}
	}
}
