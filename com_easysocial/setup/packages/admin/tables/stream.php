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

ES::import('admin:/tables/table');

class SocialTableStream extends SocialTable
{
	public $id = null;
	public $actor_id = null;
	public $actor_type = null;
	public $post_as	= null;
	public $alias = null;
	public $created	= null;
	public $modified = null;
	public $edited = null;
	public $title = null;
	public $content = '';
	public $sitewide = null;
	public $target_id = null;
	public $context_type = null;
	public $verb = null;
	public $stream_type = null;
	public $with = null;
	public $location_id = null;
	public $anywhere_id	= null;
	public $mood_id	= null;
	public $background_id = null;
	public $ispublic = null;
	public $params = null;
	public $cluster_id = null;
	public $cluster_type = null;
	public $cluster_access = null;
	public $state = null;
	public $privacy_id = null;
	public $access = null;
	public $custom_access = null;
	public $field_access = null;
	public $last_action = null;
	public $last_userid = null;
	public $last_action_date = null;
	public $sticky_id = null;

	static $_streams = array();

	public function __construct(&$db)
	{
		parent::__construct('#__social_stream', 'id', $db);
	}

	/**
	 * Overrides parent's load implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load($keys = null, $reset = true)
	{
		if (is_array($keys)) {
			return parent::load($keys, $reset);
		}

		if (!isset(self::$_streams[$keys])) {
			$state = parent::load($keys);
			self::$_streams[$keys] = $this;

			return $state;
		}

		return parent::bind(self::$_streams[$keys]);
	}

	public function loadByBatch($ids)
	{
		$db = ES::db();
		$sql = $db->sql();

		$streamIds = array();

		foreach ($ids as $pid) {

			if (!isset(self::$_streams[$pid])) {
				$streamIds[] = $pid;
			}
		}

		if ($streamIds) {

			foreach ($streamIds as $pid) {
				self::$_streams[$pid] = false;
			}

			$query = '';
			$idSegments = array_chunk( $streamIds, 5 );
			//$idSegments = array_chunk( $streamIds, count($streamIds) );

			for ($i = 0; $i < count($idSegments); $i++) {
				$segment = $idSegments[$i];

				$ids = implode(',', $segment);
				$query .= 'select * from `#__social_stream` where `id` IN ( ' . $ids . ')';

				if (($i + 1)  < count($idSegments)) {
					$query .= ' UNION ';
				}
			}

			$sql->raw($query);
			$db->setQuery($sql);

			$results = $db->loadObjectList();

			if ($results) {

				foreach ($results as $row) {
					self::$_streams[$row->id] = $row;
				}
			}
		}
	}

	/**
	 * Override the parent's store behavior
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		if (is_null($this->modified)) {
			$date = ES::date();
			$this->modified = $date->toSql();
		}

		// Fixed for 'Fields don't have default value' error in Joomla 4
		if (!$this->with) {
			$this->with = '';
		}

		if (!$this->target_id) {
			$this->target_id = 0;
		}

		if (!$this->location_id) {
			$this->location_id = 0;
		}

		if (!$this->mood_id) {
			$this->mood_id = 0;
		}

		if (!$this->background_id) {
			$this->background_id = 0;
		}

		return parent::store();
	}

	/**
	 * Retrieves a list of #__social_stream_items for the stream object
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getItems()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_stream_item');
		$sql->where('uid', $this->id);

		$db->setQuery($sql);

		$items = $db->loadObjectList();

		return $items;
	}

	public function toJSON()
	{
		return array('id' => $this->id ,
					 'actor_id' => $this->actor_id ,
					 'actor_type' => $this->actor_type,
					 'post_as' => $this->post_as,
					 'alias' => $this->alias,
					 'created' => $this->created,
					 'modified' => $this->modified,
					 'title' => $this->title,
					 'content' => $this->content,
					 'sitewide' => $this->sitewide,
					 'target_id' => $this->target_id,
					 'location_id' => $htis->location_id,
					 'ispublic'	=> $this->ispublic,
					 'params'	=> $this->params,
					 'cluster_id' => $this->cluster_id,
					 'cluster_type' => $this->cluster_type,
					 'cluster_access' => $this->cluster_access,
					 'verb' => $this->verb,
					 'mood_id' => $this->mood_id,
					 'privacy_id' => $this->privacy_id,
					 'access' => $this->access,
					 'custom_access' => $this->custom_access,
					 'field_access' => $this->field_access,
		 );
	}

	/**
	 * Get the uid association to this stream
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function getUID()
	{
		$db = ES::db();
		$sql = $db->sql();
		$sql->select('#__social_stream_item', 'a');
		$sql->column('a.id');
		$sql->where('a.uid', $this->id);

		$db->setQuery($sql);

		$id = $db->loadResult();

		return $id;
	}

	/**
	 * Loads a stream item by uid
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadByUID($uid)
	{
		$db = ES::db();
		$sql = $db->sql();
		$sql->select('#__social_stream', 'a');
		$sql->column('a.*');
		$sql->join('#__social_stream_item', 'b');
		$sql->on('b.uid', 'a.id');
		$sql->where('b.id', $uid);

		$db->setQuery($sql);

		$obj = $db->loadObject();

		return parent::bind($obj);
	}

	/**
	 * Returns the stream's permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $external = false, $sef = true, $adminSef = false)
	{
		return ESR::stream(array('id' => $this->id, 'layout' => 'item', 'external' => $external, 'sef' => $sef, 'adminSef' => $adminSef), $xhtml);
	}

	/**
	 * Retrieves the cluster object for this stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCluster()
	{
		$cluster = ES::cluster($this->cluster_type, $this->cluster_id);

		return $cluster;
	}

	/**
	 * Checks if the provided user is allowed to hide this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hideable($id = null)
	{
		return true;
	}

	/**
	 * Determines whether the stream is moderated
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function isModerated()
	{
		return $this->state == SOCIAL_STREAM_STATE_MODERATE;
	}

	/**
	 * Determines whether the stream is scheduled
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isScheduled()
	{
		return $this->state == SOCIAL_STREAM_STATE_SCHEDULED;
	}

	/**
	 * Determines whether the stream is moderated
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function isTrashed() {
		return $this->state == SOCIAL_STREAM_STATE_TRASHED;
	}


	/**
	 * Checks if the provided user is the owner of this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isAdmin( $id = null )
	{
		$user 	= ES::user( $id );

		if( $user->isSiteAdmin() )
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the provided user is the owner of this item
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function isOwner( $id = null )
	{
		$user 	= ES::user( $id );

		if( $this->actor_id == $user->id )
		{
			return true;
		}

		return false;
	}

	/**
	 * Determines if this item is posted in a cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isCluster()
	{
		if ($this->cluster_id) {
			return true;
		}

		return false;
	}

	/**
	 * Delete this stream and its associated stream_items
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		// Cleanup related items to the stream
		$this->deleteNotifications();
		$this->deleteRepost();
		$this->deleteComments();
		$this->deleteReactions();
		$this->deleteHashtags();
		$this->deleteReport();
		$this->deleteScheduled();

		// Clean up files attached to this stream
		if ($this->context_type == 'files') {
			$this->deleteFiles();
		}

		// Clean up story photos attached to this stream
		// But skip this if that is avatar/cover stream item
		if ($this->context_type == 'photos' && ($this->verb != 'uploadAvatar' && $this->verb != 'updateCover')) {
			$this->deletePhotos();
		}

		// Clean up cached link and link image on this stream item
		if ($this->context_type == 'links') {
			$this->deleteCachedLinks();
		}

		$db = ES::db();
		$sql = $db->sql();

		$query = 'DELETE FROM `#__social_stream_item` WHERE `uid` = ' . $db->Quote($this->id);
		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		$streamId = $this->id;

		// to make sure the comments / reactions really get removed for these items.
		$manualDeleteItems = array('polls', 'pages', 'badges', 'feeds', 'blog', 'discuss', 'easydiscuss');

		if (in_array($this->context_type, $manualDeleteItems)
			|| ($this->context_type == 'calendar' && $this->verb == 'update')
			|| ($this->context_type == 'notes' && $this->verb == 'update')
			|| ($this->context_type == 'audios' && $this->verb == 'featured')
			|| ($this->context_type == 'videos' && $this->verb == 'featured')) {


			// for calendar and notes create stream, since it is associated with calendar / notes comments itself,
			// we will only remove comments / reactions for calendar / notes create stream when user remove the calendar / notes.
			// #2849

			$query = "delete b, c";
			$query .= " from `#__social_stream` as a";
			// remove all associated comments;
			$query .= " left join `#__social_comments` as b on a.`id` = b.`stream_id`";
			// remove all associated reaction;
			$query .= " left join `#__social_likes` as c on a.`id` = c.`stream_id`";
			$query .= " where a.`id` = " . $db->Quote($streamId);

			$db->setQuery($query);
			$db->query();


			// now we delete any 'left overs' reactions on those deleted comments
			$query = "delete a";
			$query .= " from `#__social_likes` as a";
			$query .= "	left join `#__social_comments` as b on a.`uid` = b.`id`";
			$query .= " where a.`type` like " . $db->Quote('comments.%');
			$query .= " and b.`id` is null";

			$db->setQuery($query);
			$db->query();
		}

		$state = parent::delete();

		if ($state) {

			// we need to check if this stream belong to polls or not.
			// if yes, we need to delete the polls as well.
			if ($this->context_type == 'polls' and $this->verb == 'create') {

				$query = "delete a, b, c";
				$query .= " from `#__social_polls` as a";
				$query .= "	left join `#__social_polls_items` as b on a.`id` = b.`poll_id`";
				$query .= "	left join `#__social_polls_users` as c on a.`id` = c.`poll_id`";
				$query .= " where a.`element` = " . $db->Quote('stream');
				$query .= " and a.`uid` = " . $db->Quote($streamId);

				$db->setQuery($query);
				$db->query();

				ES::points()->assign('polls.remove', 'com_easysocial', $this->actor_id);
			}
		}

		// Deduct point from the author when the story is deleted.
		$allowPointTypes = array(SOCIAL_POINTS_STORY_TYPES_STORY, SOCIAL_POINTS_STORY_TYPES_LINKS);

		if (in_array($this->context_type, $allowPointTypes)) {
			ES::points()->assign('story.delete', 'com_easysocial', $this->actor_id);
		}

		return $state;
	}

	/**
	 * Method to delete the cached sef alias when item being removed.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function deleteSEFCache()
	{
		$alias = $this->getPermalink();

		// /en/community/stream/item/321
		// we need to remove the front segment

		// remove leading slash
		$alias = ltrim($alias, '/');

		$segments = explode('/', $alias);
		array_shift($segments);

		// glue again
		$alias = implode('/', $segments);

		$state = ESR::deleteSEFCache($this, $alias);

		return $state;
	}

	/**
	 * Delete link and link image which have cached to this stream item
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function deleteCachedLinks()
	{
		$model = ES::model('Stream');
		$assets = array();

		// Get stream assets to achieve the link and the link image
		$results = $model->getAssets($this->id, 'links');

		// Make sure there is result
		if (!$results) {
			return;
		}

		foreach ($results as $row) {
			$assets[] = ES::registry($row->data);
		}

		$assets = $assets[0];

		// Load the link object
		$link = ES::table('Link');
		$cachedLink = $link->loadByLink($assets->get('link'));

		// If there is cached link, proceed the deletion
		if ($cachedLink) {
			$link->delete();
		}

		// Load the link image object
		$linkImage = ES::table('LinkImage');
		$cachedLinkImage = $linkImage->load(array('internal_url' => $assets->get('image')));

		// If there is cached link image, proceed the deletion
		if ($cachedLinkImage) {
			$linkImage->delete();
		}

		$streamAsset = ES::table('StreamAsset');
		$cachedAssetLink = $streamAsset->load(array('stream_id' => $this->id, 'type' => 'links'));

		// delete the stream assets link data
		if ($cachedAssetLink) {
			$streamAsset->delete();
		}
	}

	/**
	 * Delete any file added to this stream item
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function deleteFiles()
	{
		$streamItem = ES::table('StreamItem');
		$streamItem->load(array('uid' => $this->id));
		$params = $streamItem->getParams();

		$files = $params->get('files');

		if ($this->isCluster()) {
			$files = $params->get('file');
		}

		if (empty($files)) {
			return;
		}

		foreach ($files as $fileId) {
			$file = ES::table('File');
			$file->load($fileId);

			$file->delete(null, '', false);
		}
	}

	/**
	 * Delete any photo added to this stream item via story form
	 *
	 * @since   3.2
	 * @access  public
	 */
	public function deletePhotos()
	{
		// Please take note:
		// When user upload a photo via story form, the element that used in comments and reactions for this photos is 'stream.user.upload'.
		// The reason behind this is so that we can synced the comments / reactions from the stream item and also from the photo page.

		// Now, if we were to delete this stream item and keep the photo, this cause the system to change the elements from 'stream.user.uplaod' to 'photos.user.add'.
		// In another words, all the previous comments or reactions made to this story photo will be gone.

		// To avoid the confusion, we decided we will only delete the photos when deleting a stream provided that photo is belong to story album.
		// For photo stream that belong to normal album, deleting this stream item will not delete the photo.
		// #4048


		$streamModel = ES::model('Stream');
		$photos = $streamModel->getContextItems($this->id);

		if (empty($photos)) {
			return;
		}

		foreach ($photos as $item) {

			$photo = ES::table('Photo');
			$photo->load($item->context_id);

			if (!$photo->id) {
				continue;
			}

			// further check if this photo belong to story album or not.
			// if yes, then only we delete this photo. #4048
			$album = ES::table('Album');
			$album->load($photo->album_id);

			if ($album->id && $album->isStory()) {
				$photo->delete();
			}
		}
	}

	/**
	 * Deletes comments related to the stream item
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function deleteComments()
	{
		$context = $this->getContext();

		// For comments, the type is always stored as cluster type
		if ($this->cluster_id && $this->cluster_type) {
			$context = $this->context_type . '.' . $this->cluster_type . '.' . $this->verb;
		}

		$model = ES::model('Comments');
		return $model->deleteComments($this->id, $context);
	}

	/**
	 * Delete repost that are associated with the stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteRepost()
	{
		if ($this->context_type != 'shares') {
			return;
		}

		$db = ES::db();
		$sql = $db->sql();

		$query = 'DELETE FROM `#__social_shares` WHERE ' . $db->nameQuote('uid') . ' = ' . $db->Quote($this->target_id);
		$query .= ' AND ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($this->actor_id);
		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return;
	}

	/**
	 * Delete report that has been made on this stream
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function deleteReport()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'DELETE FROM `#__social_reports` WHERE ' . $db->nameQuote('uid') . ' = ' . $db->Quote($this->id);
		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return;
	}

	/**
	 * Delete scheduled entry for this stream.
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function deleteScheduled()
	{
		if (!$this->isScheduled()) {
			return true;
		}

		$db = ES::db();

		$query = 'DELETE FROM `#__social_stream_scheduled` WHERE `stream_id` = ' . $db->Quote($this->id);

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}

	/**
	 * Deletes reactions related to the stream item
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function deleteReactions()
	{
		$context = $this->getContext();

		$model = ES::model('Likes');
		return $model->delete($this->id, $context);
	}

	/**
	 * Deletes reactions related to the stream item
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function deleteHashtags()
	{
		$model = ES::model('Hashtags');
		return $model->delete($this->id);
	}

	/**
	 * Delete notification items that related with this stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteNotifications()
	{
		// Since the url is no longer valid, we can safely use the url as identifier to delete the notifications
		$url = $this->getPermalink(true, false, false);

		$db = ES::db();
		$sql = $db->sql();

		$query = 'DELETE FROM `#__social_notifications`';
		$query .= ' WHERE `url` = ' . $db->Quote($url);

		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 * Publishes a stream on the site.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$this->state = SOCIAL_STREAM_STATE_PUBLISHED;

		return $this->store();
	}

	/**
	 * Retrieves the context for the stream
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function getContext()
	{
		$context = $this->context_type . '.' . $this->actor_type . '.' . $this->verb;

		return $context;
	}

	/**
	 * Get a list of tags for the stream
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getTags($types = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_stream_tags');
		$sql->where('stream_id', $this->id);

		$sql->where('utype', $types, 'IN');
		$db->setQuery($sql);

		$tags 	= $db->loadObjectList();

		return $tags;
	}

	/**
	 * Retrieve the scheduled date for this stream if any.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getScheduled()
	{
		if (!$this->isScheduled()) {
			return false;
		}

		$db = ES::db();
		$query = 'SELECT `scheduled` FROM `#__social_stream_scheduled` WHERE `stream_id` = ' . $db->Quote($this->id);

		$db->setQuery($query);

		$scheduled = $db->loadResult();

		if (!$scheduled) {
			$this->setError('Unable to locate scheduled stream item.');

			return false;
		}

		return $scheduled;
	}

	/**
	 * Determines if the viewer can edit this stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canEdit($userId = null)
	{
		$my = ES::user($userId);
		$access = $my->getAccess();

		if ($my->isSiteAdmin()) {
			return true;
		}

		if ($this->isCluster()) {
			$cluster = $this->getCluster();
			$access = $cluster->getAccess();

			// Instead of only allow admin to edit this stream, we should add an acl for cluster
			if ($cluster->isAdmin()) {
				return true;
			}

			if ($access->get('stream.edit', 'admins') == 'members' && $cluster->isMember($my->id) && $this->actor_id == $my->id) {
				return true;
			}

			$this->setError('COM_EASYSOCIAL_STREAM_NOT_ALLOWED_TO_DELETE');

			return false;
		}

		// check if this stream is belogn to user or not.
		if ($this->actor_type == 'user' && $this->actor_id == $my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the viewer can delete this stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canDelete($userId = null, $joins = null)
	{
		$my = ES::user($userId);
		$access = $my->getAccess();

		if ($my->isSiteAdmin()) {
			return true;
		}

		if ($access->allowed('stream.delete')) {
			return true;
		}

		if ($this->isCluster()) {
			$cluster = $this->getCluster();

			if ($cluster->isAdmin()) {
				return true;
			}
		}

		$this->setError('COM_EASYSOCIAL_STREAM_NOT_ALLOWED_TO_DELETE');
		return false;
	}

	/**
	 * Determines if the viewer can sticky this stream item
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canSticky()
	{
		$config = ES::config();

		if (!$config->get('stream.pin.enabled')) {
			return false;
		}

		// If the stream is moderated, it shouldn't be allowed to be stickied
		if ($this->isModerated()) {
			return false;
		}

		if ($this->isScheduled()) {
			return false;
		}

		// Always allow site admin to sticky
		if (ES::isSiteAdmin()) {
			return true;
		}

		if ($this->isCluster()) {
			$cluster = ES::cluster($this->cluster_type, $this->cluster_id);

			// if user is not the cluster owner or the admin, then dont alllow to sticky
			if (!$cluster->isOwner() && !$cluster->isAdmin()) {
				return false;
			}

			return true;
		}

		if (!$this->isOwner() && !ES::isSiteAdmin()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the viewer can view this stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canViewItem($userId = null)
	{
		$user = ES::user($userId);

		if ($user->isSiteAdmin()) {
			return true;
		}

		$lib = ES::stream();
		$stream = $lib->getItem($this->id, $this->cluster_id, $this->cluster_type);

		if ($stream === false) {
			return false;
		}

		return true;
	}

	/**
	 * Get assets related to this stream
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getAssets($type)
	{
		// Get the link object
		$model      = ES::model( 'Stream' );
		$assets		= $model->getAssets($this->id, $type);

		return $assets;
	}
}
