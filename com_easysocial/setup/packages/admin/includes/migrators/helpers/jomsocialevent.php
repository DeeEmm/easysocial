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

jimport('joomla.filesystem.file');

require_once(SOCIAL_LIB . '/migrators/helpers/info.php');
require_once(SOCIAL_LIB . '/migrators/helpers/helper.php');

class SocialMigratorHelperJomsocialEvent
{
	var $name = null;
	var $steps = null;
	var $info = null;
	var $limit = null;
	var $stateMapping = null;

	public function __construct()
	{
		$this->info = new SocialMigratorHelperInfo();
		$this->name = 'com_community';

		$this->stateMapping = array();
		$this->stateMapping['0'] = SOCIAL_EVENT_GUEST_INVITED;
		$this->stateMapping['1'] = SOCIAL_EVENT_GUEST_GOING;
		$this->stateMapping['2'] = SOCIAL_EVENT_GUEST_NOT_GOING;
		$this->stateMapping['3'] = SOCIAL_EVENT_GUEST_MAYBE;
		$this->stateMapping['5'] = SOCIAL_EVENT_GUEST_NOT_GOING;
		$this->stateMapping['6'] = SOCIAL_EVENT_GUEST_PENDING;

		$this->limit = 10; //10 items per cycle

		// do not change the steps sequence !
		$this->steps[] = 'eventcategory';
		$this->steps[] = 'events';
		$this->steps[] = 'eventmembers';
		$this->steps[] = 'eventavatar';
		$this->steps[] = 'eventcover';
		$this->steps[] = 'eventphotos';
		$this->steps[] = 'eventwalls';
		$this->steps[] = 'eventwallcomments';
		$this->steps[] = 'eventphotocomments';
		$this->steps[] = 'eventalbumcomments';

	}

	public function getVersion()
	{
		$exists = $this->isComponentExist();

		if (!$exists->isvalid) {
			return false;
		}

		// check JomSocial version.
		$xml = JPATH_ROOT . '/administrator/components/com_community/community.xml';

		$parser = ES::get('Parser');
		$parser->load($xml);

		$version = $parser->xpath('version');
		$version = (float) $version[0];

		return $version;
	}

	/**
	 * function to check if there is image files stored in amazon or not.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasAmazonPhotos()
	{
		$model = ES::model('Migrators');
		$count = $model->getJSAmazonPhotosCount($this->name, 'event');

		return $count ? true : false;
	}

	public function isInstalled()
	{
		$file = JPATH_ROOT . '/components/com_community/libraries/core.php';

		if (! JFile::exists($file)) {
			return false;
		}

		return true;
	}

	public function setUserMapping($maps)
	{
		// do nothing.
	}

	/*
	 * return object with :
	 *     isvalid  : true or false
	 *     messsage : string.
	 *     count    : integer. item count to be processed.
	 */
	public function isComponentExist()
	{
		$obj = new stdClass();
		$obj->isvalid = false;
		$obj->count = 0;
		$obj->message = '';

		$jsCoreFile	= JPATH_ROOT . '/components/com_community/libraries/core.php';

		if (! JFile::exists($jsCoreFile)) {
			$obj->message = 'JomSocial not found in your site. Process aborted.';
			return $obj;
		}

		// all pass. return object
		$obj->isvalid = true;
		$obj->count = $this->getItemCount();

		return $obj;
	}

	public function getItemCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$total = count($this->steps);

		// event category
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_events_category` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventcategory') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// events
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_events` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('events') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('profile');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// ------------  groups members
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_events_members` as a';
		$query .= ' inner join `#__social_migrators` as c on a.`eventid` = c.`oid` and c.`element` = ' . $db->Quote('events') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' inner join `#__community_events` as d on a.`eventid` = d.`id`';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`memberid` = b.`oid` and b.`element` = concat_ws(' . $db->Quote('.') . ',' . $db->Quote('eventmembers') . ', a.`eventid`) and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();

		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// ------------  event avatar
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_events` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventavatar') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// ------------  event cover
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_events` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventcover') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// ------------  events photos
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_photos` as a';
		$query .= ' inner join `#__community_photos_albums` as b on a.`albumid` = b.`id` and b.`type` = ' . $db->Quote('event');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventphotos') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`storage` = ' . $db->Quote('file');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// ------------  event wall post
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_activities` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventwalls') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and `app` = ' . $db->Quote('events.wall');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// photo comments
		$query = 'select count(1) as `total`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('eventphotos') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventphoto.comments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('photos');


		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// albums comments
		$query = 'select count(1) as `total`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('eventalbums') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventalbum.comments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('albums');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// wall's comments
		$query = 'select count(1) as `total`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventwall.comments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('events.wall');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		return $total;
	}

	public function process($item)
	{
		// @debug
		$obj = new stdClass();

		if (empty($item)) {
			$item = $this->steps[0];
		}

		$result = '';

		switch($item)
		{
			case 'eventcategory':
				$result = $this->processEventCategory();
				break;

			case 'events':
				$result = $this->processEvents();
				break;

			case 'eventmembers':
				$result = $this->processMembers();
				break;

			case 'eventavatar':
				$result = $this->processAvatar();
				break;

			case 'eventcover':
				$result = $this->processCover();
				break;

			case 'eventphotos':
				$result = $this->processPhotos();
				break;

			case 'eventwalls':
				$result = $this->processWall();
				break;

			case 'eventphotocomments':
				$result = $this->processPhotoComments();
				break;

			case 'eventalbumcomments':
				$result = $this->processAlbumComments();
				break;

			case 'eventwallcomments':
				$result = $this->processWallComments();
				break;

			default:
				break;
		}

		// this is the ending part to determine if the process is already ended or not.
		if (is_null($result)) {
			$keys 		= array_keys($this->steps, $item);
			$curSteps 	= $keys[0];

			if (isset($this->steps[ $curSteps + 1])) {
				$item = $this->steps[ $curSteps + 1];
			} else {
				$item = null;
			}

			$obj->continue = (is_null($item)) ? false : true ;
			$obj->item = $item;
			$obj->message  = ($obj->continue) ? 'Checking for next item to migrate....' : 'No more item found.';

			return $obj;
		}


		$obj->continue = true;
		$obj->item = $item;
		$obj->message = implode('<br />', $result->message);

		return $obj;
	}

	/**
	 * Process walls comments
	 * @since 2.2
	 * @access private
	 */
	private function processWallComments()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esstreamid`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('eventwalls') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventwall.comments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('events.wall');
		$query .= ' ORDER BY a.`contentid` ASC';
		$query .= ' LIMIT ' . $this->limit;


		$sql->raw($query);
		$db->setQuery($sql);

		$jsEventWallComments = $db->loadObjectList();

		if (count($jsEventWallComments) <= 0) {
			return null;
		}

		foreach($jsEventWallComments as $jsEventWallComment) {
			// there is no es stream id associated. do not process this anymore.
			if (!$jsEventWallComment->esstreamid) {
				$this->log('eventwall.comments', $jsEventWallComment->id, -1);
				$this->info->setInfo('Event wall\'s comment with id \'' . $jsEventWallComment->id . '\' is not associate with stream in EasySocial. Wall commment migration process aborted.');
				continue;
			}

			// We know for sure this is for stream event
			$element = 'story.event.create';

			$esStreamTbl = ES::table('Stream');
			$esStreamTbl->load($jsEventWallComment->esstreamid);

			$obj = new stdClass();
			$obj->url = $esStreamTbl->getPermalink();
			$obj->url = $this->removeAdminSegment($obj->url);

			$esComment = ES::table('Comments');
			$esComment->element = $element;
			$esComment->uid = $jsEventWallComment->esstreamid;
			$esComment->comment = $jsEventWallComment->comment;
			$esComment->created_by = $jsEventWallComment->post_by;
			$esComment->created = $jsEventWallComment->date;
			$esComment->params = ES::json()->encode($obj);
			$esComment->stream_id = $jsEventWallComment->esstreamid;

			//off the trigger for migrated comments.
			$esComment->offTrigger();
			$esComment->store();

			// Regex if has mention in comment
			SocialMigratorHelper::processCommentMentions($esComment);

			// check if this comment has image attachment or not.
			$jsCommentParams = ES::registry($jsEventWallComment->params);
			$jsCommentAttachmentId = $jsCommentParams->get('attached_photo_id', 0);

			if ($jsCommentAttachmentId) {
				SocialMigratorHelper::processCommentAttachment($jsEventWallComment, $esComment);
			}

			$this->log('eventwall.comments', $jsEventWallComment->id, $esComment->id);
			$this->info->setInfo('Event wall\'s comment with id \'' . $jsEventWallComment->id . '\' is now migrated into EasySocial the new comment id: ' . $esComment->id . '.');
		}

		return $this->info;
	}

	/**
	 * Process albums comments
	 * @since 2.2
	 * @access private
	 */
	private function processAlbumComments()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esalbumid`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('eventalbums') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventalbum.comments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('albums');
		$query .= ' ORDER BY a.`contentid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsAlbumComments = $db->loadObjectList();

		if (count($jsAlbumComments) <= 0) {
			return null;
		}

		foreach ($jsAlbumComments as $jsAlbumComment) {
			if (! $jsAlbumComment->esalbumid) {
				// log into mgirator
				$this->log('eventalbum.comments', $jsAlbumComment->id, -1);

				$this->info->setInfo('Event\'s album comment with id \'' . $jsAlbumComment->id . '\' is not associate with album in EasySocial. Album commment migration process aborted.');
				continue;
			}

			$esAlbumTbl = ES::table('Album');
			$esAlbumTbl->load($jsAlbumComment->esalbumid);

			$obj = new stdClass();
			$obj->url = $esAlbumTbl->getPermalink();
			$obj->url = $this->removeAdminSegment($obj->url);

			$esComment = ES::table('Comments');
			$esComment->element = 'albums.event.create';
			$esComment->uid = $jsAlbumComment->esalbumid;
			$esComment->comment = $jsAlbumComment->comment;
			$esComment->created_by = $jsAlbumComment->post_by;
			$esComment->created = $jsAlbumComment->date;
			$esComment->params = ES::json()->encode($obj);
			$esComment->stream_id = $this->getStreamId($jsAlbumComment->esalbumid, SOCIAL_TYPE_ALBUM);

			//off the trigger for migrated commetns.
			$esComment->offTrigger();
			$esComment->store();

			// Regex if has mention in comment
			SocialMigratorHelper::processCommentMentions($esComment);

			// check if this comment has image attachment or not.
			$jsCommentParams = ES::registry($jsAlbumComment->params);
			$jsCommentAttachmentId = $jsCommentParams->get('attached_photo_id', 0);
			if ($jsCommentAttachmentId) {
				SocialMigratorHelper::processCommentAttachment($jsAlbumComment, $esComment);
			}

			// log into mgirator
			$this->log('eventalbum.comments', $jsAlbumComment->id, $esComment->id);
			$this->info->setInfo('Event\'s album comment with id \'' . $jsAlbumComment->id . '\' is now migrated into EasySocial the new comment id: ' . $esComment->id . '.');

		}

		return $this->info;
	}

	/**
	 * Process photos comments
	 * @since 2.2
	 * @access private
	 */
	private function processPhotoComments()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esphotoid`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('eventphotos') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventphoto.comments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('photos');
		$query .= ' ORDER BY a.`contentid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsPhotoComments = $db->loadObjectList();

		if (count($jsPhotoComments) <= 0) {
			return null;
		}

		foreach ($jsPhotoComments as $jsPhotoComment) {
			if (! $jsPhotoComment->esphotoid) {
				// there is no es photo id associated. do not process this anymore.

				// log into mgirator
				$this->log('eventphoto.comments', $jsPhotoComment->id, -1);

				$this->info->setInfo('Event\'s photo comment with id \'' . $jsPhotoComment->id . '\' is not associate with photo in EasySocial. Photo commment migration process aborted.');
				continue;
			}

			// photo link
			$esPhotoTbl = ES::table('Photo');
			$esPhotoTbl->load($jsPhotoComment->esphotoid);

			$obj = new stdClass();
			//$obj->url = FRoute::photos(array('layout' => 'item', 'id' => $jsPhotoComment->esphotoid));
			$obj->url = $esPhotoTbl->getPermalink();
			$obj->url = $this->removeAdminSegment($obj->url);

			$esComment = ES::table('Comments');
			$esComment->element = 'photos.event.add';
			$esComment->uid = $jsPhotoComment->esphotoid;
			$esComment->comment = $jsPhotoComment->comment;
			$esComment->created_by = $jsPhotoComment->post_by;
			$esComment->created = $jsPhotoComment->date;
			$esComment->params = ES::json()->encode($obj);
			$esComment->stream_id = $this->getStreamId($jsPhotoComment->esphotoid, SOCIAL_TYPE_PHOTO);

			//off the trigger for migrated commetns.
			$esComment->offTrigger();
			$esComment->store();

			// Regex if has mention
			// $this->processMentions($esComment);
			SocialMigratorHelper::processCommentMentions($esComment);

			// check if this comment has image attachment or not.
			$jsCommentParams = ES::registry($jsPhotoComment->params);
			$jsCommentAttachmentId = $jsCommentParams->get('attached_photo_id', 0);
			if ($jsCommentAttachmentId) {
				SocialMigratorHelper::processCommentAttachment($jsPhotoComment, $esComment);
			}

			// log into mgirator
			$this->log('eventphoto.comments', $jsPhotoComment->id, $esComment->id);
			$this->info->setInfo('Event\'s photo comment with id \'' . $jsPhotoComment->id . '\' is now migrated into EasySocial the new comment id: ' . $esComment->id . '.');

		}//end foreach

		return $this->info;
	}

	private function getStreamId($esItemId, $type)
	{
		static $_cache = array();

		$db = ES::db();
		$sql = $db->sql();

		if (! isset($_cache[$type][$esItemId])) {

			$sql->select('#__social_stream_item', 'a');
			$sql->column('a.uid');
			$sql->where('a.context_type', $type);
			$sql->where('a.context_id', $esItemId);

			$db->setQuery($sql);

			$uid = (int) $db->loadResult();
			$_cache[$type][$esItemId] = $uid;
		}

		return $_cache[$type][$esItemId];
	}

	private function processWall()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `eseventid`';
		$query .= ' from `#__community_activities` as a';
		$query .= ' 	inner join `#__social_migrators` as c on a.`eventid` = c.`oid` and c.`element` = ' . $db->Quote('events') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventwalls') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and `app` = ' . $db->Quote('events.wall');
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsWalls = $db->loadObjectList();

		if (count($jsWalls) <= 0) {
			return null;
		}

		foreach ($jsWalls as $jsWall) {
			// create story stream for this group.
			$stream = ES::stream();

			// Get the stream template
			$template = $stream->getTemplate();

			$template->setActor($jsWall->actor , SOCIAL_TYPE_USER);
			$template->setContext('0' , SOCIAL_TYPE_STORY);


			$content = ($jsWall->title) ? $jsWall->title : $jsWall->content;

			// process mentions is there is any.
			$mentions = SocialMigratorHelper::processJSMentions($content);

			if ($mentions) {
				$template->setMentions($mentions);
			}

			$template->setContent($content);

			$template->setVerb('create');

			// Set the params to cache the group data

			$event = ES::event($jsWall->eseventid);
			$registry = ES::registry();
			$registry->set('event' , $event);

			// Set the params to cache the group data
			$template->setParams($registry);

			$template->setCluster($jsWall->eseventid, SOCIAL_TYPE_EVENT, $event->type);

			// Set this stream to be public
			$template->setAccess('story.view');

			$template->setDate($jsWall->created);

			$streamItem = $stream->add($template);

			$this->log('eventwalls', $jsWall->id, $streamItem->uid);

			$this->info->setInfo('Event wall \'' . $jsWall->id . '\' is now migrated into EasySocial as event\'s story update.');
		}

		return $this->info;

	}

	private function processCover()
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.uid as `eseventid`';
		$query .= ' from `#__community_events` as a';
		$query .= ' inner join `#__social_migrators` as c on a.`id` = c.`oid` and c.`element` = ' . $db->Quote('events') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventcover') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsEvents = $db->loadObjectList();

		if (count($jsEvents) <= 0) {
			return null;
		}

		foreach ($jsEvents as $jsEvent) {
			if (!$jsEvent->cover) {
				// no need to process further.
				$this->log('eventcover', $jsEvent->id , $jsEvent->id);

				$this->info->setInfo('Event ' . $jsEvent->id . ' is using default cover. no migration is needed.');
				continue;
			}

			$imagePath = JPATH_ROOT . '/' . $jsEvent->cover;

			$tmp = explode('/', $imagePath);
			$filename = $tmp[ count($tmp) - 1 ];

			if (!JFile::exists($imagePath)) {
				$this->log('eventcover', $jsEvent->id , $jsEvent->id);

				$this->info->setInfo('Event ' . $jsEvent->id . ' the cover image file is not found from the server. Process aborted.');
				continue;
			}

			// lets copy this file to tmp folder 1st.
			$tmp = JFactory::getConfig()->get('tmp_path');
			$tmpImageFile = $tmp . '/' . md5(JFactory::getDate()->toSql());
			JFile::copy($imagePath , $tmpImageFile);

			$image = ES::image();
			$image->load($tmpImageFile);


			// Check if there's a profile photos album that already exists.
			$albumModel	= ES::model('Albums');

			// Retrieve the group's default album
			$album 	= $albumModel->getDefaultAlbum($jsEvent->eseventid , SOCIAL_TYPE_EVENT , SOCIAL_ALBUM_PROFILE_COVERS);
			$album->user_id = $jsEvent->creator;
			$album->store();

			$photo = ES::table('photo');
			$photo->uid = $jsEvent->eseventid ;
			$photo->user_id = $jsEvent->creator ;
			$photo->type = SOCIAL_TYPE_EVENT;
			$photo->album_id = $album->id;
			$photo->title = $filename;
			$photo->caption = '';
			$photo->ordering = 0;

			// We need to set the photo state to "SOCIAL_PHOTOS_STATE_TMP"
			$photo->state = SOCIAL_PHOTOS_STATE_TMP;

			// Try to store the photo first
			$state = $photo->store();

			// Push all the ordering of the photo down
			$photosModel = ES::model('photos');
			$photosModel->pushPhotosOrdering($album->id , $photo->id);

			// Render photos library
			$photoLib = ES::get('Photos', $image);

			$storage = $photoLib->getStoragePath($album->id, $photo->id);
			$paths = $photoLib->create($storage);

			// Create metadata about the photos
			foreach ($paths as $type => $fileName) {

				$meta = ES::table('PhotoMeta');

				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				$meta->value = $storage . '/' . $fileName;

				$meta->store();
			}

			// Load the cover
			$cover = ES::table('cover');
			$cover->uid = $jsEvent->eseventid;
			$cover->type = SOCIAL_TYPE_EVENT;

			$cover->setPhotoAsCover($photo->id);

			// Save the cover.
			$cover->store();

			// now we need to update back the photo item to have the cover_id and the state to published
			// We need to set the photo state to "SOCIAL_STATE_PUBLISHED"
			$photo->state = SOCIAL_STATE_PUBLISHED;
			$photo->store();

			if (! $album->cover_id) {
				$album->cover_id = $photo->id;
				$album->store();
			}

			// process cover albums likes.
			$jsAlbum = $this->getJSCoreAlbum('event.Cover', $jsEvent->id);
			if (isset($jsAlbum->id) && $jsAlbum->id) {
				$jsLikes = $this->getJSLikes('album', $jsAlbum->id);
				if ($jsLikes) {
					$this->addAlbumLikes($album, $jsLikes, 'albums.event.create');
				}
			}

			// @Add stream item when a new event cover is uploaded
			// get the cover update date.
			$uploadDate = $this->getMediaUploadDate('cover.upload', $jsEvent->id);

			if (!$uploadDate) {
				// if empty, then lets just use event creation date.
				$uploadDate = $jsEvent->created;
			}

			$photo->addPhotosStream('updateCover', $uploadDate);

			// log into mgirator
			$this->log('eventcover', $jsEvent->id , $jsEvent->id);

			$this->info->setInfo('Event cover ' . $jsEvent->id . ' is now migrated into EasySocial.');
		}

		return $this->info;

	}

	private function getMediaUploadDate($context, $jsEventId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select `created` from `#__community_activities` where `eventid` = '$jsEventId' and `app` = '$context' order by `id` desc limit 1";
		$sql->raw($query);

		$db->setQuery($sql);
		$result = $db->loadResult();

		return $result;
	}

	private function processAvatar()
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.uid as `eseventid`';
		$query .= ' from `#__community_events` as a';
		$query .= ' inner join `#__social_migrators` as c on a.`id` = c.`oid` and c.`element` = ' . $db->Quote('events') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventavatar') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsEvents = $db->loadObjectList();

		if (count($jsEvents) <= 0) {
			return null;
		}

		foreach ($jsEvents as $jsEvent) {
			if (!$jsEvent->avatar) {
				// no need to process further.
				$this->log('eventavatar', $jsEvent->id , $jsEvent->id);

				$this->info->setInfo('Event ' . $jsEvent->id . ' is using default avatar. no migration is needed.');
				continue;
			}

			$imagePath = JPATH_ROOT . '/' . $jsEvent->avatar;

			$tmp = explode('/', $imagePath);
			$filename = $tmp[ count($tmp) - 1 ];

			if (!JFile::exists($imagePath)) {
				$this->log('eventavatar', $jsEvent->id , $jsEvent->id);

				$this->info->setInfo('Event ' . $jsEvent->id . ' the avatar image file is not found from the server. Process aborted.');
				continue;
			}

			// lets copy this file to tmp folder 1st.
			$tmp = JFactory::getConfig()->get('tmp_path');
			$tmpImageFile = $tmp . '/' . md5(JFactory::getDate()->toSql());
			JFile::copy($imagePath , $tmpImageFile);

			$image = ES::image();
			$image->load($tmpImageFile);

			$avatar	= ES::avatar($image, $jsEvent->eseventid, SOCIAL_TYPE_EVENT);

			// Check if there's a profile photos album that already exists.
			$albumModel	= ES::model('Albums');

			// Retrieve the group's default album
			$album 	= $albumModel->getDefaultAlbum($jsEvent->eseventid , SOCIAL_TYPE_EVENT , SOCIAL_ALBUM_PROFILE_PHOTOS);
			$album->user_id = $jsEvent->creator;
			$album->store();

			$photo = ES::table('photo');
			$photo->uid = $jsEvent->eseventid ;
			$photo->user_id = $jsEvent->creator ;
			$photo->type = SOCIAL_TYPE_EVENT;
			$photo->album_id = $album->id;
			$photo->title = $filename;
			$photo->caption = '';
			$photo->ordering = 0;

			// We need to set the photo state to "SOCIAL_PHOTOS_STATE_TMP"
			$photo->state = SOCIAL_PHOTOS_STATE_TMP;

			// Try to store the photo first
			$state = $photo->store();

			// Push all the ordering of the photo down
			$photosModel = ES::model('photos');
			$photosModel->pushPhotosOrdering($album->id , $photo->id);

			// Render photos library
			$photoLib = ES::get('Photos' , $image);
			$storage = $photoLib->getStoragePath($album->id, $photo->id);
			$paths = $photoLib->create($storage);

			// Create metadata about the photos
			foreach($paths as $type => $fileName) {

				$meta = ES::table('PhotoMeta');

				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				$meta->value = $storage . '/' . $fileName;

				$meta->store();
			}

			// Create the avatars now, but we do not want the store function to create stream.
			// so we pass in the option. we will create the stream ourown.
			$options = array('addstream' => false);
			$avatar->store($photo, $options);

			// process avatar albums likes.
			$jsAlbum = $this->getJSCoreAlbum('event.avatar', $jsEvent->id);
			if (isset($jsAlbum->id) && $jsAlbum->id) {
				$jsLikes = $this->getJSLikes('album', $jsAlbum->id);
				if ($jsLikes) {
					$this->addAlbumLikes($album, $jsLikes, 'albums.event.create');
				}
			}

			// @Add stream item when a new event avatar is uploaded
			// get the cover update date.
			$uploadDate = $this->getMediaUploadDate('events.avatar.upload', $jsEvent->id);

			if (!$uploadDate) {
				// if empty, then lets just use event creation date.
				$uploadDate = $jsEvent->created;
			}

			$photo->addPhotosStream('uploadAvatar', $uploadDate);

			// log into mgirator
			$this->log('eventavatar', $jsEvent->id , $photo->id);

			$this->info->setInfo('Event avatar ' . $jsEvent->id . ' is now migrated into EasySocial.');

		}

		return $this->info;

	}

	private function processPhotos()
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select c.`uid` as `eseventid`, b.`eventid`, a.*';
		$query .= ' from `#__community_photos` as a';
		$query .= ' inner join `#__community_photos_albums` as b on a.`albumid` = b.`id` and b.`type` = ' . $db->Quote('event');
		$query .= ' inner join `#__social_migrators` as c on b.`eventid` = c.`oid` and c.`element` = ' . $db->Quote('events') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventphotos') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`storage` = ' . $db->Quote('file');
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsPhotos = $db->loadObjectList();

		if (count($jsPhotos) <= 0) {
			return null;
		}

		foreach ($jsPhotos as $jsPhoto) {

			if (!$jsPhoto->published) {
				// photos not published. do not migrate.

				// log into mgirator
				$this->log('eventphotos', $jsPhoto->id, -1);

				$this->info->setInfo('Photo with id \'' . $jsPhoto->id . '\' is currently in unpublished or delete state. Photo migration process aborted.');
				continue;
			}

			$imagePath = JPATH_ROOT . '/' . $jsPhoto->original;

			if (!JFile::exists($imagePath)) {
				// files from originalphotos not found. let try to get it from photos folder instead.

				// images/photos/84/1/e03fbd75d6e8f5fe0e542665.jpg
				$imagePath = JPATH_ROOT . '/' . $jsPhoto->image;
			}

			if (!JFile::exists($imagePath)) {
				// both image from originalphotos and photos folder not found. Lets give up.

				// log into mgirator
				$this->log('eventphotos', $jsPhoto->id, -1);

				$this->info->setInfo('Photo with id \'' . $jsPhoto->id . '\' not found in the server. Photo migration process aborted.');
				continue;
			}

			// lets get this photo album
			$esAlbumId = $this->processJSPhotoAlbum($jsPhoto);

			// lets copy this file to tmp folder 1st.
			$tmp = JFactory::getConfig()->get('tmp_path');
			$tmpImageFile = $tmp . '/' . md5(JFactory::getDate()->toSql());
			JFile::copy($imagePath , $tmpImageFile);

			$esPhoto = ES::table('Photo');

			$esPhoto->uid = $jsPhoto->eseventid;
			$esPhoto->type = SOCIAL_TYPE_EVENT;
			$esPhoto->user_id = $jsPhoto->creator;
			$esPhoto->album_id = $esAlbumId;

			// we use the filename as the title instead of caption.
			$fileName = JFile::getName($imagePath);
			$esPhoto->title = $fileName;
			$esPhoto->caption = $jsPhoto->caption;

			$esPhoto->created = $jsPhoto->created;
			$esPhoto->assigned_date = $jsPhoto->created;

			$esPhoto->ordering = $this->getPhotoOrdering($esAlbumId);
			$esPhoto->featured = '0';
			$esPhoto->state = ($jsPhoto->published) ? '1' : '0';

			// Let's test if exif exists
			$exif = ES::get('Exif');

			// Load the iamge object
			$image = ES::image();
			$image->load($tmpImageFile);

			// Detect the photo caption and title if exif is available.
			if ($exif->isAvailable() && $image->hasExifSupport()) {
				// Load the image
				$exif->load($tmpImageFile);

				$title = $exif->getTitle();
				$caption= $exif->getCaption();
				$createdAlias= $exif->getCreationDate();

				if ($createdAlias) {
					$esPhoto->assigned_date = $createdAlias;
				}

				if ($title) {
					$esPhoto->title = $title;
				}

				if ($caption) {
					$esPhoto->caption= $caption;
				}
			}

			$esPhoto->store();

			// Get the photos library
			$photoLib = ES::get('Photos' , $image);
			$storage = $photoLib->getStoragePath($esAlbumId , $esPhoto->id);
			$paths = $photoLib->create($storage);

			// Create metadata about the photos
			foreach ($paths as $type => $fileName) {
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $esPhoto->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				$meta->value= $storage . '/' . $fileName;

				$meta->store();
			}

			// add photo stream
			$esPhoto->addPhotosStream('create', $jsPhoto->created);

			//lets add cover photo into this photo's album
			$album = ES::table('Album');
			$album->load($esAlbumId);

			if (!$album->hasCover()) {
				$album->cover_id = $esPhoto->id;
				$album->store();
			}

			// log into mgirator
			$this->log('eventphotos', $jsPhoto->id, $esPhoto->id);

			$this->info->setInfo('Photo with id \'' . $jsPhoto->id . '\' from event \'' . $jsPhoto->eventid . '\' is now migrated into EasySocial.');

		}

		return $this->info;
	}

	private function processJSPhotoAlbum($jsPhoto)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, b.`uid` as `esalbumid`';
		$query .= ' from `#__community_photos_albums` as a';
		$query .= '		left join `#__social_migrators` as b on a.id = b.oid and b.`element` = ' . $db->Quote('eventalbums') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ' where a.id = ' . $db->Quote($jsPhoto->albumid);

		$sql->raw($query);
		$db->setQuery($sql);

		$jsAlbum = $db->loadObject();

		if ($jsAlbum->esalbumid) {
			// this album already migrated. lets us this es album id.
			return $jsAlbum->esalbumid;
		}

		// this album not yet migrate. lets do it!
		$esAlbum = ES::table('Album');

		// Set the album creation alias
		$esAlbum->assigned_date = $jsAlbum->created;
		$esAlbum->created = $jsAlbum->created;

		// Set the uid and type.
		$esAlbum->uid = $jsPhoto->eseventid;
		$esAlbum->type = SOCIAL_TYPE_EVENT;
		$esAlbum->user_id = $jsAlbum->creator;

		// @todo: get the album cover photo.
		$esAlbum->cover_id = '0';
		$esAlbum->title = $jsAlbum->name;

		// JS core value only have 1 defined to user story photo
		$jsCoreAlbumExist = isset($jsAlbum->default) && $jsAlbum->default == 1 ? true : false;

		// Since JS hardcoded those album name in database, it will difficult to use other language translation if the site is running multilingue
		if ($jsCoreAlbumExist) {

			// Override those user story photo default name
			if ($jsAlbum->type == 'event' && $jsAlbum->default == 1) {
				$esAlbum->title = 'COM_EASYSOCIAL_ALBUMS_STORY_PHOTOS';
			}
		}

		$esAlbum->caption = isset($jsAlbum->description) ? $jsAlbum->description : $jsAlbum->name;
		$esAlbum->params = null;

		// ES core value 1 = user avatar photo
		// ES core value 2 = user cover photo
		// ES core value 3 = user story photo
		$esAlbum->core = $jsCoreAlbumExist ? 3 : 0;
		$esAlbum->hits = isset($jsAlbum->hits) ? $jsAlbum->hits : 0;

		// Try to store the album
		$esAlbum->store();

		// process albums likes.
		$jsLikes = $this->getJSLikes('album', $jsAlbum->id);
		if ($jsLikes) {
			$this->addAlbumLikes($esAlbum, $jsLikes, 'albums.event.create');
		}

		$this->log('eventalbums', $jsAlbum->id, $esAlbum->id);

		return $esAlbum->id;
	}

	/**
	 * Method to add the album's likes
	 *
	 * @since  2.2
	 * @access private
	 */
	private function addAlbumLikes($esAlbum, $jsLikes, $esAlbumLikeType)
	{
		$db = ES::db();

		// preparing required data
		$date = ES::date();
		$albumLink = $esAlbum->getPermalink();
		$uri = base64_encode($albumLink);
		$values = array();

		// construct the insert statement
		$likeQuery = "insert into `#__social_likes` (`reaction`, `type`, `uid`, `uri`, `created_by`, `created`) values ";
		foreach ($jsLikes as $likerId) {
			$values[] = "(" . $db->Quote('like') . "," . $db->Quote($esAlbumLikeType) . "," . $db->Quote($esAlbum->id) . "," . $db->Quote($uri) . "," . $db->Quote($likerId) . "," . $db->Quote($date->toSql()) . " )";
		}
		$likeQuery .= implode(',', $values);

		$db->setQuery($likeQuery);
		$db->query();
	}

	/**
	 * Method to get event's avatar / cover albums from JS
	 * @return [type] [description]
	 */
	private function getJSCoreAlbum($type, $eventId)
	{
		$db = ES::db();

		$query = "select * from `#__community_photos_albums`";
		$query .= " where `eventid` = " . $db->Quote($eventId);
		$query .= " and `type` = " . $db->Quote($type);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Get JS albums likes.
	 *
	 * @since	2.2
	 * @access	private
	 */
	private function getJSLikes($element, $uid)
	{
		$db = ES::db();

		$query = "select * from `#__community_likes`";
		$query .= " where `element` = " . $db->Quote($element);
		$query .= " and `uid` = " . $db->Quote($uid);

		$db->setQuery($query);
		$result = $db->loadObject();

		if (! $result) {
			return array();
		}

		$userIds = array();
		$likes = $result->like;
		if ($likes) {
			$userIds = explode(',', $likes);
		}

		return $userIds;
	}


	private function processEvents()
	{
		$config = ES::config();
		$jConfig = ES::jconfig();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.uid as `escatid`';
		$query .= ' from `#__community_events` as a';
		$query .= ' inner join `#__social_migrators` as c on a.`catid` = c.`oid` and c.`element` = ' . $db->Quote('eventcategory') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('events') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.type = ' . $db->Quote('profile');
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsEvents = $db->loadObjectList();

		if (count($jsEvents) <= 0) {
			return null;
		}

		$json = ES::json();

		foreach ($jsEvents as $jsEvent) {
			$parentId = $this->getEsEventParendId($jsEvent);

			$esEvent = ES::table('Cluster');

			$params = array();
			$params['photo'] = array("albums" => true);
			$params['news'] = true;
			$params['discussions'] = true;
			$params['guestlimit'] = $jsEvent->ticket;
			$params['allowmaybe'] = true;
			$params['allownotgoingguest'] = true;

			$jEventParams = $json->decode($jsEvent->params);

			$esEvent->parent_id = $parentId;
			$esEvent->category_id = $jsEvent->escatid;
			$esEvent->cluster_type = SOCIAL_TYPE_EVENT;
			$esEvent->creator_type = SOCIAL_TYPE_USER;
			$esEvent->creator_uid = $jsEvent->creator;
			$esEvent->title = $jsEvent->title;
			$esEvent->description = $jsEvent->description;
			$esEvent->alias = SocialMigratorHelper::generateAlias($jsEvent->title);
			$esEvent->state = $jsEvent->published;
			$esEvent->created = $jsEvent->created;
			$esEvent->params = $json->encode($params);
			$esEvent->hits = $jsEvent->hits;
			$esEvent->type = $jsEvent->permission == 1 ? SOCIAL_EVENT_TYPE_PRIVATE : SOCIAL_EVENT_TYPE_PUBLIC;
			$esEvent->key = ''; // TODO: check what is this key for

			// need to store the address, latitude and longitude
			$esEvent->address = $jsEvent->location;
			$esEvent->latitude = $jsEvent->latitude;
			$esEvent->longitude = $jsEvent->longitude;

			$state = $esEvent->store();

			if ($state) {

				// get joomla timezone offset.
				$offset = $jConfig->getValue('offset', 'UTC');
				$tz = new DateTimeZone($offset);

				// insert into event_meta on start, end and timezone
				$meta = ES::table('EventMeta');
				$meta->cluster_id = $esEvent->id;
				$meta->start = $jsEvent->startdate;
				$meta->end = $jsEvent->enddate;

				// set the timezone and gmt dates.
				$meta->timezone = $jEventParams && isset($jEventParams->timezone) && $jEventParams->timezone ? $jEventParams->timezone : '';

				if ($meta->timezone) {
					$tz = new DateTimeZone($meta->timezone);
				}

				// start date;
				$startDate = new JDate($meta->start, $tz);
				$meta->start_gmt = $startDate->toSql();

				// end date
				$endDate = new JDate($meta->end, $tz);
				$meta->end_gmt = $endDate->toSql();
				$meta->store();

				// now we need to store the address into field_data as well.
				$esFieldId = $this->getFieldId('ADDRESS', $jsEvent->escatid);

				if ($esFieldId) {

					//address
					$data = new stdClass();
					$data->datakey = 'address';
					$data->data = $jsEvent->location;
					$data->raw = $jsEvent->location;
					$this->addFieldData($esFieldId, $esEvent->id, $data);

					//latitude
					$data = new stdClass();
					$data->datakey = 'latitude';
					$data->data = $jsEvent->latitude;
					$data->raw = $jsEvent->latitude;
					$this->addFieldData($esFieldId, $esEvent->id, $data);

					//longitude
					$data = new stdClass();
					$data->datakey = 'longitude';
					$data->data = $jsEvent->longitude;
					$data->raw = $jsEvent->longitude;
					$this->addFieldData($esFieldId, $esEvent->id, $data);

				}

				// TODO: Add event creation stream.
				$stream = ES::stream();
				$streamTemplate = $stream->getTemplate();

				// Set the actor
				$streamTemplate->setActor($jsEvent->creator , SOCIAL_TYPE_USER);

				// Set the context
				$streamTemplate->setContext($esEvent->id , SOCIAL_TYPE_EVENTS);

				$streamTemplate->setVerb('create');
				$streamTemplate->setSiteWide();

				// set cluster
				$streamTemplate->setCluster($esEvent->id, SOCIAL_TYPE_EVENT, $esEvent->type);

				// Set the params to cache the group data
				$registry = ES::registry();
				$registry->set('event' , $esEvent);

				// Set the params to cache the group data
				$streamTemplate->setParams($registry);

				$streamTemplate->setDate($jsEvent->created);

				$streamTemplate->setAccess('core.view');

				// Add stream template.
				$stream->add($streamTemplate);

				// end add stream

				$this->log('events', $jsEvent->id, $esEvent->id);

				$this->info->setInfo('Event \'' . $jsEvent->title . '\' has migrated succefully into EasySocial.');
			}

		}//end foreach

		return $this->info;
	}

	private function processMembers()
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `eseventid`, d.`creator` as `ownerid`, d.`created` as `eventcreatedate`';
		$query .= ' from `#__community_events_members` as a';
		$query .= ' inner join `#__social_migrators` as c on a.`eventid` = c.`oid` and c.`element` = ' . $db->Quote('events') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' inner join `#__community_events` as d on a.`eventid` = d.`id`';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`memberid` = b.`oid` and b.`element` = concat_ws(' . $db->Quote('.') . ',' . $db->Quote('eventmembers') . ', a.`eventid`) and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`eventid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsMembers = $db->loadObjectList();

		if (count($jsMembers) <= 0) {
			return null;
		}

		foreach ($jsMembers as $jsMember) {

			if ($jsMember->status == '4' || $jsMember->status == '7') {
				$this->log('eventmembers' . '.' . $jsMember->eventid , $jsMember->memberid, '0');

				$this->info->setInfo('Member id \'' . $jsMember->memberid. '\' from Event \'' . $jsMember->eventid . '\' was blocked or not invited. Migration aborted for this member.');

				return $this->info;
			}


			// lets check if the event join date is empty or not.
			// if yes, lets use event creation date.
			if (!$jsMember->created || $jsMember->created == '0000-00-00 00:00:00') {
				$jsMember->created = $jsMember->eventcreatedate;
			}

			$esMember = ES::table('ClusterNode');
			$esMember->cluster_id = $jsMember->eseventid;
			$esMember->uid = $jsMember->memberid;
			$esMember->type = SOCIAL_TYPE_USER;
			$esMember->created = $jsMember->created;
			$esMember->state = $this->memberStateMapping($jsMember->status);
			$esMember->owner = ($jsMember->ownerid == $jsMember->memberid) ? 1 : 0;
			$esMember->admin = ($jsMember->ownerid == $jsMember->memberid) ? 1 : 0;
			$esMember->invited_by = $jsMember->invited_by;

			$state = $esMember->store();

			if ($state && $jsMember->status == 1) {

				$event = ES::event($jsMember->eseventid);

				// Load up the stream library
				$stream = ES::stream();

				// Get the stream template
				$tpl = $stream->getTemplate();

				// Set the verb
				$tpl->setVerb('going');

				// Set the context
				// Since this is a "user" action, we set the context id to the guest node id, and the context type to guests
				$tpl->setContext($esMember->id, 'guests');

				// Set the privacy rule
				$tpl->setAccess('core.view');

				// Set the cluster
				$tpl->setCluster($event->id, $event->cluster_type, $event->type);

				// Set the actor
				$tpl->setActor($esMember->uid, $esMember->type);

				// set stream creation date
				$tpl->setDate($jsMember->created);

				// Add stream template.
				$stream->add($tpl);
			}

			$this->log('eventmembers' . '.' . $jsMember->eventid , $jsMember->memberid, $esMember->id);

			$this->info->setInfo('Member id \'' . $jsMember->memberid. '\' from Event \'' . $jsMember->eventid . '\' has migrated succefully into EasySocial.');

		}

		return $this->info;
	}

	private function memberStateMapping($jsState)
	{
		$state = $this->stateMapping[$jsState];
		return $state;
	}


	private function getFieldId($fieldCode, $clusterCategoryId)
	{
		static $_cache = array();

		$db = ES::db();
		$sql = $db->sql();

		$key = $fieldCode . '_' . $clusterCategoryId;

		if (! isset($_cache[$key])) {

			// Get the workflow from the category first
			$category = ES::table('ClusterCategory');
			$category->load($clusterCategoryId);
			$workflow = $category->getWorkflow();

			$query = "select a.`id` from `#__social_fields` as a";
			$query .= "	inner join `#__social_fields_steps` as b on a.`step_id` = b.`id`";
			$query .= " where b.`type` = '".SOCIAL_TYPE_CLUSTERS."'";
			$query .= " and b.`workflow_id` = '$workflow->id'";
			$query .= " and a.`unique_key` = '$fieldCode'";
			$query .= " limit 1";

			$sql->raw($query);
			$db->setQuery($sql);

			$result = $db->loadResult();

			$_cache[$key] = $result;
		}

		return $_cache[$key];
	}

	private function addFieldData($fieldId, $eventId, $data)
	{
		$fieldData = ES::table('FieldData');

		$fieldData->field_id = $fieldId;
		$fieldData->uid = $eventId;
		$fieldData->type = SOCIAL_TYPE_EVENT;
		$fieldData->datakey = ($data->datakey == 'default' || $data->datakey == '') ? '' : $data->datakey;
		$fieldData->data = $data->data;
		$fieldData->raw = $data->raw;

		$fieldData->store();
	}

	private function getEsEventParendId($jsEvent)
	{
		$db = ES::db();
		$sql = $db->sql();

		static $_cache = array();

		if ($jsEvent->parent) {

			$pid = $jsEvent->parent;

			if (! isset($_cache[$pid])) {

				$query = 'select b.`uid` from `#__social_migrators` as b';
				$query .= ' where b.`oid` = '. $db->Quote($pid);
				$query .= ' and b.`element` = ' . $db->Quote('events') . ' and b.`component` = ' . $db->Quote($this->name);

				$sql->raw($query);
				$db->setQuery($sql);

				$_cache[$pid] = $db->loadResult();
			}
			return $_cache[$pid];
		}

		return '0';
	}



	private function processEventCategory()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__community_events_category` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('eventcategory') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsEventCats = $db->loadObjectList();

		if (count($jsEventCats) <= 0) {
			return null;
		}

		// TODO: get superadmin id
		$userModel = ES::model( 'Users' );

		$superadmins = $userModel->getSiteAdmins(true);
		$adminId = ($superadmins) ? $superadmins[0] : '42';

		foreach ($jsEventCats as $jsEventCat) {

			$esClusterCat = ES::table('ClusterCategory');

			$esClusterCat->type = SOCIAL_TYPE_EVENT;
			$esClusterCat->title = $jsEventCat->name;
			$esClusterCat->alias = SocialMigratorHelper::generateAlias($jsEventCat->name);
			$esClusterCat->description = $jsEventCat->description;
			$esClusterCat->created 	= ES::date()->toMySQL();


			$esClusterCat->state = SOCIAL_STATE_PUBLISHED;
			$esClusterCat->uid = $adminId; // default to superadmin id

			$esClusterCat->store();

			$this->createDefaultStepItems($esClusterCat->id, $esClusterCat->title);

			$this->log('eventcategory', $jsEventCat->id, $esClusterCat->id);
			$this->info->setInfo('Event category \'' . $jsEventCat->name . '\' is now migrated into EasySocial with id \'' . $esClusterCat->id . '\'.');

		}// end foreach

		return $this->info;
	}

	/**
	 * Method to create a default workflow used in user profile
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function createWorkflow($uid, $title = '')
	{
		if (! $title) {
			$title = 'JomSocial Event workflow';
		}

		$workflow = ES::table('Workflow');
		$workflow->title = $title;
		$workflow->description = $title;
		$workflow->type = SOCIAL_TYPE_EVENT;
		$workflow->store();

		$workflowId = $workflow->id;

		// now we need to associate this profile with this newly created worflow.
		$workflowMap = ES::table('WorkflowMap');
		$workflowMap->uid = $uid;
		$workflowMap->workflow_id = $workflowId;
		$workflowMap->type = SOCIAL_TYPE_EVENT;
		$workflowMap->store();

		return $workflowId;
	}

	private function createDefaultStepItems($eventId, $title = '')
	{
		// we need to create workflow data as well.
		$workflowMap = ES::table('WorkflowMap');
		$workflowMap->load(array('uid' => $eventId, 'type' => SOCIAL_TYPE_EVENT));

		$workflowId = 0;

		if ($workflowMap->id) {
			$workflowId = $workflowMap->workflow_id;
		} else {
			// there is no existing workflow for this profile.
			// lets create one.
			$workflowId = $this->createWorkflow($eventId, $title);
		}

		// Read the default profile json file first.
		$path = SOCIAL_ADMIN_DEFAULTS . '/fields/event.json';
		$contents = file_get_contents($path);

		$json = ES::json();
		$defaults = $json->decode($contents);

		$newStepIds = array();

		// Let's go through each of the default items.
		foreach ($defaults as $step) {

			// Create default step for this profile.
			$stepTable = ES::table('FieldStep');
			$stepTable->bind($step);

			// always set this to yes.
			// $stepTable->visible_display = 1;

			// Map the correct uid and type.
			$stepTable->uid = $eventId;
			$stepTable->type = SOCIAL_TYPE_CLUSTERS;
			$stepTable->workflow_id = $workflowId;

			$stepTable->state 	= SOCIAL_STATE_PUBLISHED;
			$stepTable->sequence = 1;
			$stepTable->visible_registration = SOCIAL_STATE_PUBLISHED;
			$stepTable->visible_edit = SOCIAL_STATE_PUBLISHED;
			$stepTable->visible_display = SOCIAL_STATE_PUBLISHED;

			// Try to store the default steps.
			$state = $stepTable->store();

			$newStepIds[] = $stepTable->id;

			// Now we need to create all the fields that are in the current step
			if ($step->fields && $state) {

				foreach ($step->fields as $field) {

					$appTable = ES::table('App');
					$appTable->loadByElement($field->element , SOCIAL_TYPE_EVENT , SOCIAL_APPS_TYPE_FIELDS);

					$fieldTable = ES::table('Field');
					$fieldTable->bind($field);

					// Ensure that the main items are being JText correctly.
					$fieldTable->title = $field->title;
					$fieldTable->description = $field->description;
					$fieldTable->default = isset($field->default) ? $field->default : '';

					// Set the app id.
					$fieldTable->app_id = $appTable->id;

					// Set the step.
					$fieldTable->step_id = $stepTable->id;

					// Set this to be published by default.
					$fieldTable->state = isset($field->state) ? $field->state : SOCIAL_STATE_PUBLISHED;

					// Set this to be searchable by default.
					$fieldTable->searchable = isset($field->searchable) ? $field->searchable : SOCIAL_STATE_UNPUBLISHED;

					// Set this to be searchable by default.
					$fieldTable->required = isset($field->required) ? $field->required : SOCIAL_STATE_UNPUBLISHED;

					$fieldTable->display_title = 1;
					$fieldTable->display_description = 1;
					$fieldTable->visible_registration = 1;
					$fieldTable->visible_edit = 1;
					$fieldTable->visible_display = isset($field->visible_display) ? $field->visible_display : SOCIAL_STATE_PUBLISHED;

					if ($field->element == 'startend') {
						$field->params['allow_time'] = isset($field->allow_time) ? $field->allow_time : SOCIAL_STATE_UNPUBLISHED;
						$field->params['allow_timezone'] = isset($field->allow_timezone) ? $field->allow_timezone : SOCIAL_STATE_UNPUBLISHED;
					}

					// Check if the default items has a params.
					if (isset($field->params)) {
						$fieldTable->params = ES::json()->encode($field->params);
					}

					// Store the field item.
					$fieldTable->store();

					// set the unique key
					$fieldTable->checkUniqueKey();
					$fieldTable->store();

				}
			}
		}

		return $newStepIds;
	}

	private function getPhotoOrdering($albumId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select max(`ordering`) from `#__social_photos`';
		$query .= ' where `album_id` = ' . $db->Quote($albumId);

		$sql->raw($query);
		$db->setQuery($sql);

		$ordering = $db->loadResult();

		return (empty($ordering)) ? '1' : $ordering + 1;
	}

	private function getFileMimeType($file)
	{
		if (function_exists("finfo_file")) {
			$finfo 	= finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$mime 	= finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mime;
		} else if (function_exists("mime_content_type")) {
			return mime_content_type($file);
		} else {
			return JFile::getExt($file);
		}
	}

	private function removeAdminSegment($url = '')
	{
		if ($url) {
			$url = '/' . ltrim($url , '/');
			$url = str_replace('/administrator/', '/', $url);
		}

		return $url;
	}

	public function log($element, $oriId, $newId)
	{
		$tbl = ES::table('Migrators');
		$tbl->oid = $oriId;
		$tbl->element = $element;
		$tbl->component = $this->name;
		$tbl->uid = $newId;
		$tbl->created = ES::date()->toMySQL();

		$tbl->store();
	}

}
