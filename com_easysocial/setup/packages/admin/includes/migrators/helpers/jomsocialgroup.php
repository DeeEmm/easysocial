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

class SocialMigratorHelperJomsocialGroup
{
	var $name = null;
	var $steps = null;
	var $info = null;
	var $limit = null;

	public function __construct()
	{
		$this->info = new SocialMigratorHelperInfo();
		$this->name = 'com_community';

		// 10 items per cycle
		$this->limit = 10;

		// do not change the steps sequence !
		$this->steps[] = 'groupcategory';
		$this->steps[] = 'groups';
		$this->steps[] = 'groupmembers';
		$this->steps[] = 'groupavatar';
		$this->steps[] = 'groupcover';
		$this->steps[] = 'groupphotos';
		$this->steps[] = 'groupdiscussions';
		$this->steps[] = 'groupdiscussionsfile';
		$this->steps[] = 'groupbulletins';
		$this->steps[] = 'groupwalls';
		$this->steps[] = 'groupwallcomments';
		$this->steps[] = 'groupphotocomments';
		$this->steps[] = 'groupalbumcomments';
	}

	public function getVersion()
	{
		$exists = $this->isComponentExist();

		if (!$exists->isvalid) {
			return false;
		}

		// check JomSocial version.
		$xml= JPATH_ROOT . '/administrator/components/com_community/community.xml';

		$parser = ES::get('Parser');
		$parser->load($xml);

		$version= $parser->xpath('version');
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
		$count = $model->getJSAmazonPhotosCount($this->name, 'group');

		return $count ? true : false;
	}

	public function isInstalled()
	{
		$file = JPATH_ROOT . '/components/com_community/libraries/core.php';

		if (!JFile::exists($file)) {
			return false;
		}

		return true;
	}

	public function setUserMapping($maps)
	{
		// do nothing.
	}

	/**
	 * Determine if JomSocial really exist
	 *
	 * @since   2.2.4
	 * @access  public
	 */
	public function isComponentExist()
	{
		$obj = new stdClass();
		$obj->isvalid = false;
		$obj->count = 0;
		$obj->message = '';

		$jsCoreFile = JPATH_ROOT . '/components/com_community/libraries/core.php';

		if (!JFile::exists($jsCoreFile)) {
			$obj->message = 'JomSocial not found in your site. Process aborted.';
			return $obj;
		}

		$obj->isvalid = true;
		$obj->count = $this->getItemCount();

		return $obj;
	}

	public function getItemCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$total = count($this->steps);

		// groups category
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_groups_category` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupcategory') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// groups
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_groups` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groups') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// groups members
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_groups_members` as a';
		$query .= ' inner join `#__community_groups` as d on a.`groupid` = d.`id`';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`memberid` = b.`oid` and b.`element` = concat_ws(' . $db->Quote('.') . ',' . $db->Quote('groupmembers') . ', a.`groupid`) and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// groups avatar
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_groups` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupavatar') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// groups cover
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_groups` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupcover') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// groups photos
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_photos` as a';
		$query .= ' inner join `#__community_photos_albums` as b on a.`albumid` = b.`id` and b.`type` = ' . $db->Quote('group');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupphotos') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`storage` = ' . $db->Quote('file');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// photo comments
		$query = 'select count(1) as `total`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('groupphotos') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupphoto.comments') . ' and b.`component` = ' . $db->Quote($this->name);
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
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('groupalbums') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupalbum.comments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('albums');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// discussion
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_wall` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupdiscussions') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('discussions');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// discussion files
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_files` as a';

		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupdiscussionsfile') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`discussionid` != ' . $db->Quote('0');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// bulletins
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_groups_bulletins` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupbulletins') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// groups walls
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_activities` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupwalls') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and `app` = ' . $db->Quote('groups.wall');

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
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupwall.comments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('groups.wall');

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
		$obj = new stdClass();

		if (empty($item)) {
			$item = $this->steps[0];
		}

		$result = '';

		switch($item) {
			case 'groupcategory':
				$result = $this->processGroupCategory();
				break;

			case 'groups':
				$result = $this->processGroups();
				break;

			case 'groupmembers':
				$result = $this->processMembers();
				break;

			case 'groupavatar':
				$result = $this->processAvatar();
				break;

			case 'groupcover':
				$result = $this->processCover();
				break;

			case 'groupphotos':
				$result = $this->processPhotos();
				break;

			case 'groupdiscussions':
				$result = $this->processDiscussion();
				break;

			case 'groupdiscussionsfile':
				$result = $this->processDiscussionFiles();
				break;

			case 'groupbulletins':
				$result = $this->processBulletins();
				break;

			case 'groupwalls':
				$result = $this->processWalls();
				break;

			case 'groupwallcomments':
				$result = $this->processWallComments();
				break;

			case 'groupphotocomments':
				$result = $this->processPhotoComments();
				break;

			case 'groupalbumcomments':
				$result = $this->processAlbumComments();
				break;

			default:
				break;
		}

		// this is the ending part to determine if the process is already ended or not.
		if (is_null($result)) {
			$keys = array_keys($this->steps, $item);
			$curSteps = $keys[0];

			if (isset($this->steps[ $curSteps + 1])) {
				$item = $this->steps[ $curSteps + 1];
			} else {
				$item = null;
			}

			$obj->continue = (is_null($item)) ? false : true ;
			$obj->item = $item;
			$obj->message = ($obj->continue) ? 'Checking for next item to migrate....' : 'No more item found.';

			return $obj;
		}

		$obj->continue = true;
		$obj->item = $item;
		$obj->message = implode('<br />', $result->message);

		return $obj;
	}

	private function processCover()
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.uid as `esgroupid`';
		$query .= ' from `#__community_groups` as a';
		$query .= ' inner join `#__social_migrators` as c on a.`id` = c.`oid` and c.`element` = ' . $db->Quote('groups') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupcover') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsGroups = $db->loadObjectList();

		if (count($jsGroups) <= 0) {
			return null;
		}

		foreach($jsGroups as $jsGroup) {

			if (!$jsGroup->cover) {
				$this->log('groupcover', $jsGroup->id , $jsGroup->id);
				$this->info->setInfo('Group ' . $jsGroup->id . ' is using default cover. No migration is needed.');
				continue;
			}

			$imagePath = JPATH_ROOT . '/' . $jsGroup->cover;

			$tmp = explode('/', $imagePath);
			$filename = $tmp[ count($tmp) - 1 ];

			if (!JFile::exists($imagePath)) {
				$this->log('groupcover', $jsGroup->id , $jsGroup->id);
				$this->info->setInfo('Group ' . $jsGroup->id . ': The cover image file is not found from the server. Process aborted.');
				continue;
			}

			// lets copy this file to tmp folder 1st.
			$tmp = JFactory::getConfig()->get('tmp_path');
			$tmpImageFile = $tmp . '/' . md5(JFactory::getDate()->toSql());
			JFile::copy($imagePath , $tmpImageFile);

			$image = ES::image();
			$image->load($tmpImageFile);

			// Check if there's a profile photos album that already exists.
			$albumModel= ES::model('Albums');

			// Retrieve the group's default album
			$album = $albumModel->getDefaultAlbum($jsGroup->esgroupid , SOCIAL_TYPE_GROUP , SOCIAL_ALBUM_PROFILE_COVERS);
			$album->user_id = $jsGroup->ownerid;
			$album->store();

			$photo = ES::table('Photo');
			$photo->uid = $jsGroup->esgroupid ;
			$photo->user_id = $jsGroup->ownerid ;
			$photo->type = SOCIAL_TYPE_GROUP;
			$photo->album_id = $album->id;
			$photo->title = $filename;
			$photo->caption = '';
			$photo->ordering= 0;

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
			foreach ($paths as $type => $fileName) {
				$meta = ES::table('PhotoMeta');
				$meta->photo_id= $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				$meta->value= $storage . '/' . $fileName;

				$meta->store();
			}

			// Load the cover
			$cover = ES::table('Cover');
			$cover->uid = $jsGroup->esgroupid;
			$cover->type = SOCIAL_TYPE_GROUP;

			$cover->setPhotoAsCover($photo->id);

			// Save the cover.
			$cover->store();

			// now we need to update back the photo item to have the cover_id and the state to published
			// We need to set the photo state to "SOCIAL_STATE_PUBLISHED"
			$photo->state = SOCIAL_STATE_PUBLISHED;
			$photo->store();

			if (!$album->cover_id) {
				$album->cover_id = $photo->id;
				$album->store();
			}

			// process cover albums likes.
			$jsAlbum = $this->getJSCoreAlbum('group.Cover', $jsGroup->id);

			if (isset($jsAlbum->id) && $jsAlbum->id) {
				$jsLikes = $this->getJSLikes('album', $jsAlbum->id);
				if ($jsLikes) {
					$this->addAlbumLikes($album, $jsLikes, 'albums.group.create');
				}
			}

			// @Add stream item when a new event cover is uploaded
			// get the cover update date.
			$uploadDate = $this->getMediaUploadDate('cover.upload', $jsGroup->id);

			// if empty, then lets just use event creation date.
			if (!$uploadDate) {
				$uploadDate = $jsGroup->created;
			}

			$photo->addPhotosStream('updateCover', $uploadDate);

			// log into mgirator
			$this->log('groupcover', $jsGroup->id, $jsGroup->id);

			$this->info->setInfo('Group cover ' . $jsGroup->id . ' is now migrated into EasySocial.');

		}

		return $this->info;
	}

	private function getMediaUploadDate($context, $jsGroupId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select `created` from `#__community_activities` where `groupid` = '$jsGroupId' and `app` = '$context' order by `id` desc limit 1";
		$sql->raw($query);

		$db->setQuery($sql);
		$result = $db->loadResult();

		return $result;
	}

	private function processWalls()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esgroupid`';
		$query .= ' from `#__community_activities` as a';
		$query .= ' 	inner join `#__social_migrators` as c on a.`groupid` = c.`oid` and c.`element` = ' . $db->Quote('groups') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupwalls') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and `app` = ' . $db->Quote('groups.wall');
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

			$group = ES::group($jsWall->esgroupid);
			$registry= ES::registry();
			$registry->set('group' , $group);

			// Set the params to cache the group data
			$template->setParams($registry);
			$template->setCluster($jsWall->esgroupid, SOCIAL_TYPE_GROUP, $group->type);

			// Set this stream to be public
			$template->setAccess('story.view');
			$template->setDate($jsWall->created);

			$streamItem = $stream->add($template);

			$this->log('groupwalls', $jsWall->id, $streamItem->uid);

			$this->info->setInfo('Group wall \'' . $jsWall->id . '\' is now migrated into EasySocial as group\'s story update.');
		}

		return $this->info;
	}

	private function processPhotoComments()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esphotoid`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('groupphotos') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupphoto.comments') . ' and b.`component` = ' . $db->Quote($this->name);
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
			if (!$jsPhotoComment->esphotoid) {
				$this->log('groupphoto.comments', $jsPhotoComment->id, -1);
				$this->info->setInfo('Group\'s photo comment with id \'' . $jsPhotoComment->id . '\' is not associate with photo in EasySocial. Photo commment migration process aborted.');
				continue;
			}

			// photo link
			$esPhotoTbl = ES::table('Photo');
			$esPhotoTbl->load($jsPhotoComment->esphotoid);

			$obj = new stdClass();
			$obj->url = $esPhotoTbl->getPermalink();
			$obj->url = $this->removeAdminSegment($obj->url);

			$esComment = ES::table('Comments');
			$esComment->element = 'photos.group.add';
			$esComment->uid = $jsPhotoComment->esphotoid;
			$esComment->comment = $jsPhotoComment->comment;
			$esComment->created_by = $jsPhotoComment->post_by;
			$esComment->created = $jsPhotoComment->date;
			$esComment->params = ES::json()->encode($obj);
			$esComment->stream_id = $this->getStreamId($jsPhotoComment->esphotoid, SOCIAL_TYPE_PHOTO);

			//off the trigger for migrated commetns.
			$esComment->offTrigger();
			$esComment->store();

			// Regex if has mention in comment
			SocialMigratorHelper::processCommentMentions($esComment);

			// check if this comment has image attachment or not.
			$jsCommentParams = ES::registry($jsPhotoComment->params);
			$jsCommentAttachmentId = $jsCommentParams->get('attached_photo_id', 0);
			if ($jsCommentAttachmentId) {
				SocialMigratorHelper::processCommentAttachment($jsPhotoComment, $esComment);
			}

			// log into migrator
			$this->log('groupphoto.comments', $jsPhotoComment->id, $esComment->id);
			$this->info->setInfo('Group\'s photo comment with id \'' . $jsPhotoComment->id . '\' is now migrated into EasySocial the new comment id: ' . $esComment->id . '.');
		}

		return $this->info;
	}

	private function processAlbumComments()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esalbumid`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('groupalbums') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupalbum.comments') . ' and b.`component` = ' . $db->Quote($this->name);
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
			if (!$jsAlbumComment->esalbumid) {
				$this->log('groupalbum.comments', $jsAlbumComment->id, -1);
				$this->info->setInfo('Group\'s album comment with id \'' . $jsAlbumComment->id . '\' is not associate with album in EasySocial. Album commment migration process aborted.');
				continue;
			}

			$esAlbumTbl = ES::table('Album');
			$esAlbumTbl->load($jsAlbumComment->esalbumid);

			$obj = new stdClass();
			$obj->url = $esAlbumTbl->getPermalink();
			$obj->url = $this->removeAdminSegment($obj->url);

			$esComment = ES::table('Comments');
			$esComment->element = 'albums.group.create';
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

			$this->log('groupalbum.comments', $jsAlbumComment->id, $esComment->id);
			$this->info->setInfo('Group\'s album comment with id \'' . $jsAlbumComment->id . '\' is now migrated into EasySocial the new comment id: ' . $esComment->id . '.');
		}

		return $this->info;
	}

	private function processWallComments()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esstreamid`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('groupwalls') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupwall.comments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('groups.wall');
		$query .= ' ORDER BY a.`contentid` ASC';
		$query .= ' LIMIT ' . $this->limit;


		$sql->raw($query);
		$db->setQuery($sql);

		$jsGroupWallComments = $db->loadObjectList();

		if (count($jsGroupWallComments) <= 0) {
			return null;
		}

		foreach($jsGroupWallComments as $jsGroupWallComment) {
			// there is no es stream id associated. do not process this anymore.
			if (!$jsGroupWallComment->esstreamid) {
				$this->log('groupwall.comments', $jsGroupWallComment->id, -1);
				$this->info->setInfo('Group wall\'s comment with id \'' . $jsGroupWallComment->id . '\' is not associate with stream in EasySocial. Wall commment migration process aborted.');
				continue;
			}

			// We know for sure this is for stream group
			$element = 'story.group.create';

			$esStreamTbl = ES::table('Stream');
			$esStreamTbl->load($jsGroupWallComment->esstreamid);

			$obj = new stdClass();
			$obj->url = $esStreamTbl->getPermalink();
			$obj->url = $this->removeAdminSegment($obj->url);

			$esComment = ES::table('Comments');
			$esComment->element = $element;
			$esComment->uid = $jsGroupWallComment->esstreamid;
			$esComment->comment = $jsGroupWallComment->comment;
			$esComment->created_by = $jsGroupWallComment->post_by;
			$esComment->created = $jsGroupWallComment->date;
			$esComment->params = ES::json()->encode($obj);
			$esComment->stream_id = $jsGroupWallComment->esstreamid;

			//off the trigger for migrated comments.
			$esComment->offTrigger();
			$esComment->store();

			// Regex if has mention in comment
			SocialMigratorHelper::processCommentMentions($esComment);

			// check if this comment has image attachment or not.
			$jsCommentParams = ES::registry($jsGroupWallComment->params);
			$jsCommentAttachmentId = $jsCommentParams->get('attached_photo_id', 0);

			if ($jsCommentAttachmentId) {
				SocialMigratorHelper::processCommentAttachment($jsGroupWallComment, $esComment);
			}

			$this->log('groupwall.comments', $jsGroupWallComment->id, $esComment->id);
			$this->info->setInfo('Group wall\'s comment with id \'' . $jsGroupWallComment->id . '\' is now migrated into EasySocial the new comment id: ' . $esComment->id . '.');
		}

		return $this->info;
	}

	private function processBulletins()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esgroupid`';
		$query .= ' from `#__community_groups_bulletins` as a';
		$query .= ' 	inner join `#__social_migrators` as c on a.`groupid` = c.`oid` and c.`element` = ' . $db->Quote('groups') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupbulletins') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsBulletins = $db->loadObjectList();

		if (count($jsBulletins) <= 0) {
			return null;
		}

		foreach ($jsBulletins as $jsBullentin) {

			$esNews = ES::table('ClusterNews');

			$esNews->cluster_id= $jsBullentin->esgroupid;
			$esNews->title = $jsBullentin->title;
			$esNews->content = $jsBullentin->message;
			$esNews->created= $jsBullentin->date;
			$esNews->created_by= $jsBullentin->created_by;
			$esNews->state= $jsBullentin->published;
			$esNews->comments= 1;
			$esNews->hits= 0;

			// we need to override the stream creation date
			$esNews->setStreamDate($jsBullentin->date);

			// store function will create the stream for this as well.
			$esNews->store();

			$this->log('groupbulletins', $jsBullentin->id, $esNews->id);

			$this->info->setInfo('Group bulletin  \'' . $jsBullentin->id . '\' is now migrated into EasySocial as group news.');

		}

		return $this->info;
	}

	private function processDiscussion()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__community_wall` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupdiscussions') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('discussions');
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsDiscussions = $db->loadObjectList();

		if (count($jsDiscussions) <= 0) {
			// lets see if tehre is any discussion that do not have any replies. if yes, we will migrate these items.
			$this->processDiscussParent();

			return null;
		}

		foreach ($jsDiscussions as $jsDiscuss) {

			$esObj = $this->mapDiscussionParent($jsDiscuss);

			$esDiscussParentId= $esObj->esdiscussid;
			$esGroupId= $esObj->esgroupid;

			// lets add the child posts into easysocial.
			$esDiscussChild = ES::table('Discussion');

			$esDiscussChild->parent_id= $esDiscussParentId;
			$esDiscussChild->uid= $esGroupId;
			$esDiscussChild->type = SOCIAL_TYPE_GROUP;
			$esDiscussChild->content = $jsDiscuss->comment;
			$esDiscussChild->created_by= $jsDiscuss->post_by;
			$esDiscussChild->state= $jsDiscuss->published;
			$esDiscussChild->created= $jsDiscuss->date;

			$esDiscussChild->store();

			// now we need to update the parent post.
			$parentPost = ES::table('Discussion');
			$parentPost->load($esDiscussParentId);

			$parentPost->last_reply_id = $esDiscussChild->id;
			$parentPost->total_replies = $parentPost->total_replies + 1;
			$parentPost->store();

			// check if this comment has image attachment or not.
			$jsCommentParams = ES::registry($jsDiscuss->params);
			$jsCommentAttachmentId = $jsCommentParams->get('attached_photo_id', 0);

			if ($jsCommentAttachmentId) {
				$file = $this->processReplyAttachment($jsDiscuss, $esDiscussChild, $esGroupId);
				$esDiscussChild->content .= ' [file id="' . $file['id'] . '"]' . $file['name'] . '[/file]';

				$esDiscussChild->store();
			}

			//load the group
			$group = ES::group($esGroupId);

			// Create a new stream item for this discussion reply
			$stream = ES::stream();

			// Get the stream template
			$tpl= $stream->getTemplate();

			// Someone just joined the group
			$tpl->setActor($esDiscussChild->created_by, SOCIAL_TYPE_USER);

			// Set the context
			$tpl->setContext($parentPost->id , 'discussions');

			// Set the verb
			$tpl->setVerb('reply');

			// Set the params to cache the group data
			$registry = ES::registry();
			$registry->set('group' , $group);
			$registry->set('reply' , $esDiscussChild);
			$registry->set('discussion' , $parentPost);

			$tpl->setParams($registry);
			$tpl->setDate($jsDiscuss->date);

			// Set the cluster
			$tpl->setCluster($group->id , SOCIAL_TYPE_GROUP, $group->type);

			$tpl->setAccess('core.view');

			// Add the stream
			$stream->add($tpl);

			$this->log('groupdiscussions', $jsDiscuss->id, $esDiscussChild->id);

			$this->info->setInfo('Group discussion\'s reply  \'' . $jsDiscuss->id . '\' is now migrated into EasySocial.');
		}

		return $this->info;
	}

	/**
	 * Method to process image attachment from reply.
	 *
	 * @since  3.0
	 * @access private
	 */
	public function processReplyAttachment($jsDiscuss, $esDiscussChild, $esGroupId)
	{
		$config = ES::config();
		$db = ES::db();

		$jsDiscussParams = ES::registry($jsDiscuss->params);
		$jsPhotoId = $jsDiscussParams->get('attached_photo_id', 0);

		if ($jsPhotoId) {

			$query = 'select a.* from `#__community_photos` as a';
			$query .= ' where a.`id` = ' . $db->Quote($jsPhotoId);

			$db->setQuery($query);
			$jsPhoto = $db->loadObject();

			if ($jsPhoto->id) {
				// images/originalphotos/84/1/e03fbd75d6e8f5fe0e542665.jpg
				$imagePath = JPATH_ROOT . '/' . $jsPhoto->original;

				if (!JFile::exists($imagePath)) {
					// files from originalphotos not found. let try to get it from photos folder instead.
					// images/photos/84/1/e03fbd75d6e8f5fe0e542665.jpg
					$imagePath = JPATH_ROOT . '/' . $jsPhoto->image;
				}

				if (!JFile::exists($imagePath)) {
					// both image from originalphotos and photos folder not found. Lets give up.
					return false;
				}

				// load the image file.
				$image = ES::image();
				$image->load($imagePath);

				$file = ES::table('File');
				$file->uid = $esGroupId;
				$file->type = SOCIAL_TYPE_GROUP;
				$file->name = $image->getName();
				$file->hash = md5($file->name);
				$file->mime = $image->getMime();
				$file->size = filesize($imagePath);
				$file->created = $esDiscussChild->created;
				$file->user_id = $esDiscussChild->created_by;
				$file->storage = SOCIAL_STORAGE_JOOMLA;

				$path = JPATH_ROOT . '/' . ES::cleanPath($config->get('files.storage.container'));
				$path = $path .'/' . ES::cleanPath($config->get('files.storage.group.container'));

				//  now let copy the file
				$dest = $path . '/' . $esGroupId;

				if (!JFolder::exists($dest)) {
					JFolder::create($dest);
				}

				// append the filename
				$dest .= '/' . md5($file->name);

				$state = JFile::copy($imagePath , $dest);

				if ($state) {
					$file->store();

					return array('id' => $file->id, 'name' => $file->name);
				}
			}
		}

		return true;
	}

	private function processDiscussionFiles()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, d.`uid` as `esdiscussid`, c.`uid` as `esgroupid`, e.`uid` as `escollectid`';
		$query .= ' from `#__community_files` as a';
		$query .= ' 	inner join `#__social_migrators` as c on a.`groupid` = c.`oid` and c.`element` = ' . $db->Quote('groups') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= '		inner join `#__social_migrators` as d on a.discussionid = d.oid and d.`element` = ' . $db->Quote('groupdiscussionsparent') . ' and d.`component` = ' . $db->Quote($this->name);
		$query .= '		left join `#__social_migrators` as e on a.groupid = e.oid and e.`element` = ' . $db->Quote('groupcollection') . ' and e.`component` = ' . $db->Quote($this->name);

		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupdiscussionsfile') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';

		$sql->raw($query);
		$db->setQuery($sql);

		$jsFiles = $db->loadObjectList();

		if (count($jsFiles) <= 0) {
			return null;
		}

		foreach ($jsFiles as $jsFile) {

			$esCollectionId = $this->getGroupCollectionId($jsFile);

			$filePath = JPATH_ROOT . '/' . $jsFile->filepath;

			if (JFile::exists($filePath)) {

				// add the file extension into the filename.
				$filePathArr = explode('.', $filePath);
				$fileExt = $filePathArr[ count($filePathArr) - 1 ];

				// append file ext into filename.
				$jsFile->name = $jsFile->name . '.' . $fileExt;

				$fileMime = $this->getFileMimeType($filePath);
				$fileHash = md5($jsFile->name . $jsFile->filepath);

				$esFile = ES::table('File');
				$esFile->name= $jsFile->name;
				$esFile->collection_id = $esCollectionId;
				$esFile->hits = $jsFile->hits;
				$esFile->hash = $fileHash;
				$esFile->uid = $jsFile->esgroupid;
				$esFile->type = SOCIAL_TYPE_GROUP;
				$esFile->created = $jsFile->created;
				$esFile->user_id = $jsFile->creator;
				$esFile->size = $jsFile->filesize;
				$esFile->mime = $fileMime;
				$esFile->state = SOCIAL_STATE_PUBLISHED;
				$esFile->storage = SOCIAL_STORAGE_JOOMLA;
				$esFile->store();

				// attach this file into discussion.
				$esDiscussFile = ES::table('DiscussionFile');
				$esDiscussFile->file_id = $esFile->id;
				$esDiscussFile->discussion_id = $jsFile->esdiscussid;
				$esDiscussFile->store();

				// now we need to append the file tag into discussion content.
				$fileTag = "\r\n";
				$fileTag .= '[file id="'. $esFile->id . '"]' . $jsFile->name . '[/file]';

				$esDiscuss = ES::table('Discussion');
				$esDiscuss->load($jsFile->esdiscussid);
				$esDiscuss->content = $esDiscuss->content . $fileTag;
				$esDiscuss->store();

				// now we copy the file into es.
				$storage= $esFile->getStoragePath();

				// Ensure that the storage path exists.
				ES::makeFolder($storage);

				$state = JFile::copy($filePath , $storage . '/' . $esFile->hash);

				// now we add stream
				$stream= ES::stream();
				$tpl= $stream->getTemplate();
				$group = ES::group($jsFile->esgroupid);

				// this is a cluster stream and it should be viewable in both cluster and user page.
				$tpl->setCluster($jsFile->esgroupid, SOCIAL_TYPE_GROUP, $group->type);

				// Set the actor
				$tpl->setActor($jsFile->creator , SOCIAL_TYPE_USER);

				// Set the context
				$tpl->setContext($esFile->id , SOCIAL_TYPE_FILES);

				// Set the verb
				$tpl->setVerb('uploaded');

				// set date
				$tpl->setDate($jsFile->created);

				// Set the params to cache the group data
				$registry= ES::registry();
				$registry->set('group' , $group);
				$registry->set('file'	, $esFile);

				// Set the params to cache the group data
				$tpl->setParams($registry);

				// since this is a cluster and user stream, we need to call setPublicStream
				// so that this stream will display in unity page as well
				// This stream should be visible to the public
				$tpl->setAccess('core.view');

				$stream->add($tpl);

				$this->log('groupdiscussionsfile', $jsFile->id, $esFile->id);
				$this->info->setInfo('File with id ' . $jsFile->id . ' for group discussion ' . $jsFile->discussionid . ' successfully migrated into EasySocial.');
			} else {
				$this->log('groupdiscussionsfile', $jsFile->id, 0);
				$this->info->setInfo('File with id ' . $jsFile->id . ' for group discussion ' . $jsFile->discussionid . ' not found. Migration of this file aborted.');
			}
		}

		return $this->info;

	}

	private function getFileMimeType($file)
	{
		if (function_exists("finfo_file")) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$mime = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mime;
		} else if (function_exists("mime_content_type")) {
			return mime_content_type($file);
		} else {
			return JFile::getExt($file);
		}
	}


	private function getGroupCollectionId($jsFile)
	{
		static $cache = array();

		if (!isset($cache[ $jsFile->esgroupid ])) {

			if ($jsFile->escollectid) {
				$cache[ $jsFile->esgroupid ] = $jsFile->escollectid;
			} else {
				$collection = ES::table('FileCollection');
				$collection->title= 'Group file sharing';
				$collection->owner_id = $jsFile->esgroupid;
				$collection->owner_type = SOCIAL_TYPE_GROUP;
				$collection->user_id = $jsFile->creator;
				$collection->store();

				$this->log('groupcollection', $jsFile->groupid, $collection->id);

				$cache[ $jsFile->esgroupid ] = $collection->id;
			}

		}

		return $cache[ $jsFile->esgroupid ];
	}


	private function processDiscussParent()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esgroupid`';
		$query .= ' from `#__community_groups_discuss` as a';
		$query .= ' 	inner join `#__social_migrators` as c on a.`groupid` = c.`oid` and c.`element` = ' . $db->Quote('groups') . ' and c.`component` = ' . $db->Quote('com_community');

		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupdiscussionsparent') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';

		$sql->raw($query);
		$db->setQuery($sql);

		$jsParents = $db->loadObjectList();

		if ($jsParents) {
			foreach ($jsParents as $jsParent) {
				$esDiscussion = $this->addParentDiscussion($jsParent);
			}
		}

	}


	private function mapDiscussionParent($jsDiscuss)
	{
		static $cache = array();

		$db = ES::db();
		$sql = $db->sql();

		if (!isset($cache[ $jsDiscuss->contentid ])) {
			$query = 'select a.*, b.`uid` as `esdiscussid`, c.`uid` as `esgroupid`';
			$query .= ' from `#__community_groups_discuss` as a';
			$query .= ' 	inner join `#__social_migrators` as c on a.`groupid` = c.`oid` and c.`element` = ' . $db->Quote('groups') . ' and c.`component` = ' . $db->Quote('com_community');
			$query .= '		left join `#__social_migrators` as b on a.id = b.oid and b.`element` = ' . $db->Quote('groupdiscussionsparent') . ' and b.`component` = ' . $db->Quote($this->name);
			$query .= ' where a.id = ' . $db->Quote($jsDiscuss->contentid);

			$sql->raw($query);
			$db->setQuery($sql);

			$jsParent = $db->loadObject();

			if ($jsParent->esdiscussid) {
				$obj = new stdClass();

				$obj->esdiscussid = $jsParent->esdiscussid;
				$obj->esgroupid = $jsParent->esgroupid;

				$cache[ $jsDiscuss->contentid ] = $obj;
			} else {
				$esDiscussion = $this->addParentDiscussion($jsParent);

				$obj = new stdClass();

				$obj->esdiscussid = $esDiscussion->id;
				$obj->esgroupid = $jsParent->esgroupid;

				$cache[ $jsDiscuss->contentid ] = $obj;
			}

		} //end

		return $cache[ $jsDiscuss->contentid ];
	}

	private function addParentDiscussion($jsParent)
	{
		// lets add the parent post into easysocial.
		$esDiscussion = ES::table('Discussion');

		// process mentions is there is any.
		require_once(SOCIAL_LIB . '/migrators/helpers/helper.php');
		$content = SocialMigratorHelper::html2bbcode($jsParent->message);

		$esDiscussion->content = $content;
		$esDiscussion->parent_id = 0;
		$esDiscussion->uid = $jsParent->esgroupid;
		$esDiscussion->type = SOCIAL_TYPE_GROUP;
		$esDiscussion->answer_id = 0; // we will update later
		$esDiscussion->last_reply_id = 0; // we will update later
		$esDiscussion->title = $jsParent->title;
		$esDiscussion->created_by = $jsParent->creator;
		$esDiscussion->hits = 0;
		$esDiscussion->state = 1;
		$esDiscussion->created = $jsParent->created;
		$esDiscussion->last_replied = $jsParent->lastreplied;
		$esDiscussion->votes = 0;
		$esDiscussion->total_replies = 0;
		$esDiscussion->lock = $jsParent->lock;
		$esDiscussion->params = '';

		$esDiscussion->store();

		//TODO: add discusstion creation stream
		$group = ES::group($jsParent->esgroupid);

		// Create a new stream item for this discussion
		$stream = ES::stream();

		// Get the stream template
		$tpl= $stream->getTemplate();

		// Someone just joined the group
		$tpl->setActor($esDiscussion->created_by , SOCIAL_TYPE_USER);

		// Set the context
		$tpl->setContext($esDiscussion->id , 'discussions');

		// Set the verb
		$tpl->setVerb('create');

		// Set the params to cache the group data
		$registry = ES::registry();
		$registry->set('group' 	, $group);
		$registry->set('discussion', $esDiscussion);

		// Set the cluster
		$tpl->setCluster($jsParent->esgroupid , SOCIAL_TYPE_GROUP, $group->type);

		$tpl->setParams($registry);
		$tpl->setDate($jsParent->created);

		$tpl->setAccess('core.view');

		// Add the stream
		$stream->add($tpl);

		$this->log('groupdiscussionsparent', $jsParent->id, $esDiscussion->id);

		return $esDiscussion;
	}

	private function processGroupCategory()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__community_groups_category` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupcategory') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		// echo $query;exit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsGroupCats = $db->loadObjectList();

		if (count($jsGroupCats) <= 0) {
			return null;
		}

		// TODO: get superadmin id
		$userModel = ES::model('Users');
		$superadmins = $userModel->getSiteAdmins(true);
		$adminId = ($superadmins) ? $superadmins[0] : '42';

		foreach ($jsGroupCats as $jsGroupCat) {
			$esClusterCat = ES::table('ClusterCategory');

			$esClusterCat->type = SOCIAL_TYPE_GROUP;
			$esClusterCat->title = $jsGroupCat->name;
			$esClusterCat->alias = SocialMigratorHelper::generateAlias($jsGroupCat->name);
			$esClusterCat->description = strip_tags($jsGroupCat->description);
			$esClusterCat->created = ES::date()->toMySQL();
			$esClusterCat->state = SOCIAL_STATE_PUBLISHED;
			$esClusterCat->uid = $adminId; // default to superadmin id

			$esClusterCat->store();

			// we no longer need to create the default steps items as the store function in cluster catgories will do the job.
			$this->createDefaultStepItems($esClusterCat->id, $esClusterCat->title);

			$this->log('groupcategory', $jsGroupCat->id, $esClusterCat->id);
			$this->info->setInfo('Group category \'' . $jsGroupCat->name . '\' is now migrated into EasySocial with id \'' . $esClusterCat->id . '\'.');

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
			$title = 'JomSocial Group workflow';
		}

		$workflow = ES::table('Workflow');
		$workflow->title = $title;
		$workflow->description = $title;
		$workflow->type = SOCIAL_TYPE_GROUP;
		$workflow->store();

		$workflowId = $workflow->id;

		// now we need to associate this profile with this newly created worflow.
		$workflowMap = ES::table('WorkflowMap');
		$workflowMap->uid = $uid;
		$workflowMap->workflow_id = $workflowId;
		$workflowMap->type = SOCIAL_TYPE_GROUP;
		$workflowMap->store();

		return $workflowId;
	}

	private function createDefaultStepItems($groupId, $title = '')
	{
		// we need to create workflow data as well.
		$workflowMap = ES::table('WorkflowMap');
		$workflowMap->load(array('uid' => $groupId, 'type' => SOCIAL_TYPE_GROUP));

		$workflowId = 0;

		if ($workflowMap->id) {
			$workflowId = $workflowMap->workflow_id;
		} else {
			// there is no existing workflow for this profile.
			// lets create one.
			$workflowId = $this->createWorkflow($groupId, $title);
		}

		// Read the default profile json file first.
		$path = SOCIAL_ADMIN_DEFAULTS . '/fields/group.json';

		$contents= file_get_contents($path);

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
			$stepTable->uid = $groupId;
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
					$appTable->loadByElement($field->element , SOCIAL_TYPE_GROUP , SOCIAL_APPS_TYPE_FIELDS);

					$fieldTable= ES::table('Field');
					$fieldTable->bind($field);

					// Ensure that the main items are being JText correctly.
					$fieldTable->title = $field->title;
					$fieldTable->description= $field->description;
					$fieldTable->default = isset($field->default) ? $field->default : '';

					// Set the app id.
					$fieldTable->app_id = $appTable->id;

					// Set the step.
					$fieldTable->step_id = $stepTable->id;

					// Set this to be published by default.
					$fieldTable->state = isset($field->state) ? $field->state : SOCIAL_STATE_PUBLISHED;

					// Set this to be searchable by default.
					$fieldTable->searchable = isset($field->searchable) ? $field->searchable : SOCIAL_STATE_PUBLISHED;

					// Set this to be searchable by default.
					$fieldTable->required = isset($field->required) ? $field->required : SOCIAL_STATE_PUBLISHED;

					// // Set this to be searchable by default.
					// $fieldTable->required = isset($field->required) ? $field->required : SOCIAL_STATE_PUBLISHED;

					$fieldTable->display_title = 1;
					$fieldTable->display_description = 1;
					$fieldTable->visible_registration = 1;
					$fieldTable->visible_edit = 1;
					$fieldTable->visible_display = isset($field->visible_display) ? $field->visible_display : SOCIAL_STATE_PUBLISHED;


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

	private function processGroups()
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.uid as `escatid`';
		$query .= ' from `#__community_groups` as a';
		$query .= ' inner join `#__social_migrators` as c on a.`categoryid` = c.`oid` and c.`element` = ' . $db->Quote('groupcategory') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groups') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsGroups = $db->loadObjectList();

		if (count($jsGroups) <= 0) {
			return null;
		}

		foreach ($jsGroups as $jsGroup) {
			$esGroup = ES::table('Cluster');

			$esGroup->category_id = $jsGroup->escatid;
			$esGroup->cluster_type = SOCIAL_TYPE_GROUP;
			$esGroup->creator_type = SOCIAL_TYPE_USER;
			$esGroup->creator_uid = $jsGroup->ownerid;
			$esGroup->title = $jsGroup->name;
			$esGroup->description = $jsGroup->description;
			$esGroup->alias = SocialMigratorHelper::generateAlias($jsGroup->name);
			$esGroup->state = $jsGroup->published;
			$esGroup->created = $jsGroup->created;
			$esGroup->params = null; // TODO: check what params we need to store.
			$esGroup->hits = isset($jsGroup->hits) ? $jsGroup->hits : 0;

			$esGroup->type = SOCIAL_GROUPS_PUBLIC_TYPE;
			if ($jsGroup->approvals) {
				$esGroup->type = SOCIAL_GROUPS_PRIVATE_TYPE;

				// unlisted column introduced in 4.5
				// we will map this as invite only. #2093
				if (isset($jsGroup->unlisted) && $jsGroup->unlisted) {
					$esGroup->type = SOCIAL_GROUPS_INVITE_TYPE;
				}
			}

			$esGroup->key = '';

			$state = $esGroup->store();

			if ($state) {
				// Add group creation stream.
				$stream= ES::stream();
				$streamTemplate= $stream->getTemplate();

				// Set the actor
				$streamTemplate->setActor($jsGroup->ownerid , SOCIAL_TYPE_USER);

				// Set the context
				$streamTemplate->setContext($esGroup->id , SOCIAL_TYPE_GROUPS);

				// set cluster
				$streamTemplate->setCluster($esGroup->id, SOCIAL_TYPE_GROUP, $esGroup->type);

				$streamTemplate->setVerb('create');
				$streamTemplate->setSiteWide();

				// Set the params to cache the group data
				$registry= ES::registry();
				$registry->set('group' , $esGroup);

				// Set the params to cache the group data
				$streamTemplate->setParams($registry);

				$streamTemplate->setDate($jsGroup->created);

				$streamTemplate->setAccess('core.view');

				// Add stream template.
				$stream->add($streamTemplate);

				$this->log('groups', $jsGroup->id, $esGroup->id);

				$this->info->setInfo('Group \'' . $jsGroup->name . '\' has migrated succefully into EasySocial.');
			}
		}

		return $this->info;
	}

	private function processMembers()
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esgroupid`, d.`ownerid`, d.`created` as `joindate`';
		$query .= ' from `#__community_groups_members` as a';
		$query .= ' inner join `#__social_migrators` as c on a.`groupid` = c.`oid` and c.`element` = ' . $db->Quote('groups') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' inner join `#__community_groups` as d on a.`groupid` = d.`id`';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`memberid` = b.`oid` and b.`element` = concat_ws(' . $db->Quote('.') . ',' . $db->Quote('groupmembers') . ', a.`groupid`) and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`groupid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsMembers = $db->loadObjectList();

		if (count($jsMembers) <= 0) {
			return null;
		}

		foreach ($jsMembers as $jsMember) {
			$esMember = ES::table('ClusterNode');

			$esMember->cluster_id= $jsMember->esgroupid;
			$esMember->uid = $jsMember->memberid;
			$esMember->type = SOCIAL_TYPE_USER;
			$esMember->created = $jsMember->joindate; // use group creation date as joined date.
			$esMember->state = $jsMember->approved ? SOCIAL_GROUPS_MEMBER_PUBLISHED : SOCIAL_GROUPS_MEMBER_PENDING;
			$esMember->owner = ($jsMember->ownerid == $jsMember->memberid) ? 1 : 0;
			$esMember->admin = ($jsMember->ownerid == $jsMember->memberid) ? 1 : 0;
			$esMember->invited_by= 0;

			$esMember->store();

			/* We cant add the member join stream because JomSocial did not store the join date. */

			$this->log('groupmembers' . '.' . $jsMember->groupid , $jsMember->memberid, $esMember->id);
			$this->info->setInfo('Member id \'' . $jsMember->memberid. '\' from Group \'' . $jsMember->groupid . '\' has migrated succefully into EasySocial.');

		}

		return $this->info;
	}

	private function processAvatar()
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.uid as `esgroupid`';
		$query .= ' from `#__community_groups` as a';
		$query .= ' inner join `#__social_migrators` as c on a.`id` = c.`oid` and c.`element` = ' . $db->Quote('groups') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupavatar') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsGroups = $db->loadObjectList();

		if (count($jsGroups) <= 0) {
			return null;
		}

		foreach ($jsGroups as $jsGroup) {
			if (!$jsGroup->avatar) {
				$this->log('groupavatar', $jsGroup->id , $jsGroup->id);
				$this->info->setInfo('Group ' . $jsGroup->id . ' is using default avatar. no migration is needed.');
				continue;
			}

			$imagePath = JPATH_ROOT . '/' . $jsGroup->avatar;

			$tmp = explode('/', $imagePath);
			$filename = $tmp[ count($tmp) - 1 ];

			if (!JFile::exists($imagePath)) {
				$this->log('groupavatar', $jsGroup->id , $jsGroup->id);
				$this->info->setInfo('Group ' . $jsGroup->id . ' the avatar image file is not found from the server. Process aborted.');
				continue;
			}

			// lets copy this file to tmp folder 1st.
			$tmp = JFactory::getConfig()->get('tmp_path');
			$tmpImageFile = $tmp . '/' . md5(JFactory::getDate()->toSql());
			JFile::copy($imagePath , $tmpImageFile);

			$image = ES::image();
			$image->load($tmpImageFile);

			$avatar = ES::avatar($image, $jsGroup->esgroupid, SOCIAL_TYPE_GROUP);

			// Check if there's a profile photos album that already exists.
			$albumModel = ES::model('Albums');

			// Retrieve the group's default album
			$album = $albumModel->getDefaultAlbum($jsGroup->esgroupid , SOCIAL_TYPE_GROUP , SOCIAL_ALBUM_PROFILE_PHOTOS);
			$album->user_id = $jsGroup->ownerid;
			$album->store();

			$photo = ES::table('Photo');
			$photo->uid = $jsGroup->esgroupid ;
			$photo->user_id = $jsGroup->ownerid ;
			$photo->type = SOCIAL_TYPE_GROUP;
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
			foreach ($paths as $type => $fileName) {
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				$meta->value= $storage . '/' . $fileName;

				$meta->store();
			}

			// Create the avatars now, but we do not want the store function to create stream.
			// so we pass in the option. we will create the stream our own.
			$options = array('addstream' => false);
			$avatar->store($photo, $options);

			// process avatar albums likes.
			$jsAlbum = $this->getJSCoreAlbum('group.avatar', $jsGroup->id);
			if (isset($jsAlbum->id) && $jsAlbum->id) {
				$jsLikes = $this->getJSLikes('album', $jsAlbum->id);
				if ($jsLikes) {
					$this->addAlbumLikes($album, $jsLikes, 'albums.group.create');
				}
			}

			// @Add stream item when a new event cover is uploaded
			// get the cover update date.
			$uploadDate = $this->getMediaUploadDate('groups.avatar.upload', $jsGroup->id);

			// if empty, then lets just use event creation date.
			if (!$uploadDate) {
				$uploadDate = $jsGroup->created;
			}

			$photo->addPhotosStream('uploadAvatar', $uploadDate);

			$this->log('groupavatar', $jsGroup->id , $photo->id);
			$this->info->setInfo('Group avatar ' . $jsGroup->id . ' is now migrated into EasySocial.');
		}

		return $this->info;

	}

	private function processPhotos()
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select c.`uid` as `esgroupid`, b.`groupid`, a.*';
		$query .= ' from `#__community_photos` as a';
		$query .= ' inner join `#__community_photos_albums` as b on a.`albumid` = b.`id` and b.`type` = ' . $db->Quote('group');
		$query .= ' inner join `#__social_migrators` as c on b.`groupid` = c.`oid` and c.`element` = ' . $db->Quote('groups') . ' and c.`component` = ' . $db->Quote('com_community');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('groupphotos') . ' and b.`component` = ' . $db->Quote($this->name);
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
			// photos not published. do not migrate.
			if (!$jsPhoto->published) {
				$this->log('groupphotos', $jsPhoto->id, -1);
				$this->info->setInfo('Photo with id \'' . $jsPhoto->id . '\' is currently in unpublished or delete state. Photo migration process aborted.');
				continue;
			}

			$imagePath = JPATH_ROOT . '/' . $jsPhoto->original;

			// files from originalphotos not found. let try to get it from photos folder instead.
			if (!JFile::exists($imagePath)) {
				// images/photos/84/1/e03fbd75d6e8f5fe0e542665.jpg
				$imagePath = JPATH_ROOT . '/' . $jsPhoto->image;
			}

			// both image from originalphotos and photos folder not found. Lets give up.
			if (!JFile::exists($imagePath)) {
				$this->log('groupphotos', $jsPhoto->id, -1);
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

			$esPhoto->uid = $jsPhoto->esgroupid;
			$esPhoto->type = SOCIAL_TYPE_GROUP;
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
			$this->log('groupphotos', $jsPhoto->id, $esPhoto->id);

			$this->info->setInfo('Photo with id \'' . $jsPhoto->id . '\' from group \'' . $jsPhoto->groupid . '\' is now migrated into EasySocial.');

		}

		return $this->info;
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


	private function processJSPhotoAlbum($jsPhoto)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, b.`uid` as `esalbumid`';
		$query .= ' from `#__community_photos_albums` as a';
		$query .= '		left join `#__social_migrators` as b on a.id = b.oid and b.`element` = ' . $db->Quote('groupalbums') . ' and b.`component` = ' . $db->Quote($this->name);
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
		$esAlbum->uid = $jsPhoto->esgroupid;
		$esAlbum->type = SOCIAL_TYPE_GROUP;
		$esAlbum->user_id = $jsAlbum->creator;

		// @todo: get the album cover photo.
		$esAlbum->cover_id = '0';
		$esAlbum->title = $jsAlbum->name;

		// JS core value only have 1 defined to user story photo
		$jsCoreAlbumExist = isset($jsAlbum->default) && $jsAlbum->default == 1 ? true : false;

		// Since JS hardcoded those album name in database, it will difficult to use other language translation if the site is running multilingue
		if ($jsCoreAlbumExist) {
			// Override those user story photo default name
			if ($jsAlbum->type == 'group' && $jsAlbum->default == 1) {
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
			$this->addAlbumLikes($esAlbum, $jsLikes, 'albums.group.create');
		}

		$this->log('groupalbums', $jsAlbum->id, $esAlbum->id);

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
	 * Method to get group's avatar / cover albums from JS
	 *
	 * @since   2.2.4
	 * @access  private
	 */
	private function getJSCoreAlbum($type, $groupId)
	{
		$db = ES::db();

		$query = "select * from `#__community_photos_albums`";
		$query .= " where `groupid` = " . $db->Quote($groupId);
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
}
