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

// ES::import('admin:/inclues/migrators/helpers/info');
require_once(SOCIAL_LIB . '/migrators/helpers/info.php');
require_once(SOCIAL_LIB . '/migrators/helpers/helper.php');

class SocialMigratorHelperJomsocial
{
	var $name = null;
	var $steps = null;
	var $info = null;
	var $mapping = null;
	var $accessMapping = null;
	var $limit = null;
	var $userMapping = null;

	public function __construct()
	{
		$this->info = new SocialMigratorHelperInfo();
		$this->name = 'com_community';

		$this->limit 	= 50; //10 items per cycle

		$this->steps[] = 'profiles';
		$this->steps[] = 'profileusers';
		$this->steps[] = 'connection';
		$this->steps[] = 'points';
		$this->steps[] = 'photos';
		$this->steps[] = 'albumscover';
		$this->steps[] = 'conversation';
		$this->steps[] = 'useravatar';
		$this->steps[] = 'usercover';
		$this->steps[] = 'userwalls';
		$this->steps[] = 'wallcomments';
		$this->steps[] = 'photocomments';
		$this->steps[] = 'albumcomments';
		$this->steps[] = 'fbconnects';

		$this->mapping = array(
			'group' 		=> 'header',
			'select' 		=> 'dropdown',
			'singleselect'  => 'dropdown',
			'birthdate'  	=> 'birthday',
			'textarea' 		=> 'textarea',
			'text'			=> 'textbox',
			'country'		=> 'country',
			'url'			=> 'url',
			'radio'			=> 'checkbox',
			'checkbox'		=> 'checkbox',
			'gender'		=> 'gender',
			'email'			=> 'email',
			'time'			=> 'datetime',
			'date' 			=> 'datetime',
			'label' 		=> 'textbox',
			'list'			=> 'multilist'
			);

		$this->accessMapping = array(
			'0' 	=> SOCIAL_PRIVACY_PUBLIC,
			'20'	=> SOCIAL_PRIVACY_MEMBER,
			'30'	=> SOCIAL_PRIVACY_FRIEND,
			'40'	=> SOCIAL_PRIVACY_ONLY_ME
			);
	}

	public function setUserMapping($maps)
	{
		if ($maps) {
			$userMap = array();
			foreach ($maps as $map) {
				if (strpos($map['name'], 'field_') !== false) {
					$fid = str_replace('field_', '', $map['name']);
					if ($fid) {
						$userMap[$fid] = $map['value'];
					}
				}
			}

			$this->userMapping = $userMap;
		}
	}

	public function getCountryCode($country)
	{
		static $countries = null;

		$country = strtolower($country);

		if (!$countries) {
			$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/defaults/countries.json';
			$contents = file_get_contents($file);

			$json = ES::json();
			$data = $json->decode($contents);

			foreach ($data as $key => $value) {
				$countries[strtolower($value)] = $key;
			}
		}


		if (isset($countries[$country])) {
			// return country code.
			return $countries[$country];
		} else {
			// just return the coountry value
			return $country;
		}

	}

	public function getFieldsMap()
	{
		return $this->mapping;
	}

	/**
	 * Retrieves a list of custom fields that is created within JomSocial
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCustomFields()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__community_fields');
		$sql->order('ordering' , 'ASC');

		$db->setQuery($sql);
		$fields	= $db->loadObjectList();

		return $fields;
	}

	public function getVersion()
	{
		// $installed = $this->isInstalled();

		// if (!$installed) {
		// 	return false;
		// }

		static $version = null;

		if (is_null($version)) {
			// check JomSocial version.
			$xml = JPATH_ROOT . '/administrator/components/com_community/community.xml';

			$parser = ES::get('Parser');
			$parser->load($xml);

			$version = $parser->xpath('version');
			$version = (float) $version[0];
		}

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
		$count = $model->getJSAmazonPhotosCount($this->name, 'user');

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

		// @todo check if the db tables exists or not.


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

		// profiles
		$query = 'select count(1) as `total`';
		$query .= 'from `#__community_profiles` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 		where a.`id` = b.`oid` and b.`element` = ' .$db->Quote('profiles') . ' and b.`component` = ' . $db->Quote($this->name) . ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$total = $total + $numTotal;

		// profileusers
		$query = 'select count(1) as `total`';
		$query .= ' 	from `#__community_users` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = '. $db->Quote('profileusers') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// connection
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_connection` as a';
		$query .= ' left join `#__community_connection` as c on a.`connect_from` = c.`connect_to` and a.`connect_to` = c.`connect_from` and c.`connection_id` = a.`connection_id` + 1';
		$query .= ' where c.connection_id is null';
		$query .= ' and not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`connection_id` = b.`oid` and b.`element` = ' . $db->Quote('connection') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// points
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_users` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote('points') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// photos
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_photos` as a';
		$query .= ' inner join `#__community_photos_albums` as pa on a.`albumid` = pa.`id` and pa.`type` = ' . $db->Quote('user');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('photos') . ' and b.`component` = ' . $db->Quote($this->name) .')';
		$query .= ' and a.`storage` = ' . $db->Quote('file');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// album covers
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_photos_albums` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('albumscover') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;


		// photo comments
		$query = 'select count(1) as `total`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('photos') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('photocomments') . ' and b.`component` = ' . $db->Quote($this->name);
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
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('albums') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('albumcomments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('albums');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// conversation
		$version = $this->getVersion();
		if ($version >= 4.4) {
			$query = 'select count(1) as `total`';
			$query .= ' from `#__community_chat` as a';
			$query .= ' where not exists (';
			$query .= '		select b.`id` from `#__social_migrators` as b';
			$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('conversation') . ' and b.`component` = ' . $db->Quote($this->name);
			$query .= ')';

		} else {
			$query = 'select count(1) as `total`';
			$query .= ' from `#__community_msg` as a';
			$query .= ' where not exists (';
			$query .= '		select b.`id` from `#__social_migrators` as b';
			$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('conversation') . ' and b.`component` = ' . $db->Quote($this->name);
			$query .= ')';
			$query .= ' and a.`id` = a.`parent`';
		}

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// useravatar
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_users` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote('avatar') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// usercover
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_users` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote('cover') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// user walls
		$query = 'select count(1) as `total`';
		$query .= ' from `#__community_activities` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('userwalls') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and `app` = ' . $db->Quote('profile');
		$query .= ' and `verb` = ' . $db->Quote('post');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// walls comments
		$query = 'select count(1) as `total`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('userwalls') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('wallcomments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('profile.status');

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

		switch ($item)
		{
			case 'profiles':
				$result = $this->processProfiles();
				break;

			case 'profileusers':
				$result = $this->processProfileUsers();
				break;

			case 'connection':
				$result = $this->processConnection();
				break;

			case 'points':
				$result = $this->processPoints();
				break;

			case 'photos':
				$result = $this->processPhotos();
				break;

			case 'albumscover':
				$result = $this->processJSAlbumCover();
				break;

			case 'photocomments':
				$result = $this->processPhotoComments();
				break;

			case 'albumcomments':
				$result = $this->processAlbumComments();
				break;

			case 'conversation':
				$result = $this->processConversation();
				break;

			case 'useravatar':
				$result = $this->processAvatar();
				break;

			case 'usercover':
				$result = $this->processCover();
				break;

			case 'userwalls':
				$result = $this->processWalls();
				break;

			case 'wallcomments':
				$result = $this->processWallComments();
				break;

			case 'fbconnects':
				$result = $this->processFBConnect();
				break;

			default:
				break;
		}

		// this is the ending part to determine if the process is already ended or not.
		if (is_null($result)) {
			$keys = array_keys($this->steps, $item);
			$curSteps = $keys[0];

			if (isset($this->steps[$curSteps + 1])) {
				$item = $this->steps[$curSteps + 1];
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

	/**
	 * Process user wall's comments
	 * @since 2.2
	 */
	private function processWallComments()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esstreamid`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('userwalls') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('wallcomments') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`type` = ' . $db->Quote('profile.status');
		$query .= ' ORDER BY a.`contentid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsWallComments = $db->loadObjectList();

		if (count($jsWallComments) <= 0) {
			return null;
		}

		foreach ($jsWallComments as $jsWallComment) {
			if (! $jsWallComment->esstreamid) {
				// log into mgirator
				$this->log('wallcomments', $jsWallComment->id, -1);

				$this->info->setInfo('Wall comment with id \'' . $jsWallComment->id . '\' is not associate with any stream in EasySocial. Wall commment migration process aborted.');
				continue;
			}

			$esStreamTbl = ES::table('Stream');
			$esStreamTbl->load($jsWallComment->esstreamid);

			$obj = new stdClass();
			$obj->url = $esStreamTbl->getPermalink();
			$obj->url = $this->removeAdminSegment($obj->url);

			$esComment = ES::table('Comments');
			$esComment->element = 'story.user.create';
			$esComment->uid = $jsWallComment->esstreamid;
			$esComment->comment = $jsWallComment->comment;
			$esComment->created_by = $jsWallComment->post_by;
			$esComment->created = $jsWallComment->date;
			$esComment->params = ES::json()->encode($obj);
			$esComment->stream_id = $jsWallComment->esstreamid;

			//off the trigger for migrated commetns.
			$esComment->offTrigger();
			$esComment->store();

			// Regex if has mention
			// $this->processMentions($esComment);
			SocialMigratorHelper::processCommentMentions($esComment);

			// check if this comment has image attachment or not.
			$jsCommentParams = ES::registry($jsWallComment->params);
			$jsCommentAttachmentId = $jsCommentParams->get('attached_photo_id', 0);
			if ($jsCommentAttachmentId) {
				SocialMigratorHelper::processCommentAttachment($jsWallComment, $esComment);
			}

			// log into mgirator
			$this->log('wallcomments', $jsWallComment->id, $esComment->id);
			$this->info->setInfo('Wall comment with id \'' . $jsWallComment->id . '\' is now migrated into EasySocial the new comment id: ' . $esComment->id . '.');

		}

		return $this->info;
	}


	/**
	 * Process user's walls
	 * @since 2.2
	 */
	private function processWalls()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__community_activities` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('userwalls') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and `app` = ' . $db->Quote('profile');
		$query .= ' and `verb` = ' . $db->Quote('post');

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

			// Set this stream to be public
			$template->setAccess('story.view');

			$template->setDate($jsWall->created);

			$streamItem = $stream->add($template);

			$this->log('userwalls', $jsWall->id, $streamItem->uid);

			$this->info->setInfo('User wall \'' . $jsWall->id . '\' is now migrated into EasySocial as user\'s story update.');

		}

		return $this->info;
	}

	private function processFBConnect()
	{
		$db = ES::db();
		$sql = $db->sql();

		// update user type
		$query = "update `#__social_users` set `type` = 'facebook' where `user_id` in (select `userid` from `#__community_connect_users` where `type` = 'facebook')";
		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		// now we need to add the records into oauth table.
		$query = "insert into `#__social_oauth` (`oauth_id`, `uid`, `type`, `client`, `pull`, `push`, `created`)";
		$query .= " select b.`connectid`, b.`userid`, 'user', b.`type`, '0', '0', now() from `#__community_connect_users` as b";
		$query .= " 	where b.`userid` not in (select c.`uid` from `#__social_oauth` as c where c.`type` = 'user' and c.`client` = 'facebook')";

		$sql->clear();
		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return null;
	}

	private function processConversation()
	{
		$db = ES::db();
		$sql = $db->sql();

		$version = $this->getVersion();

		$isNewChat = $version >= 4.4 ? true : false;

		$query = 'select a.*';
		$query .= ' from `#__community_msg` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('conversation') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`id` = a.`parent`';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		if ($isNewChat) {

			$query = 'select a.*';
			$query .= ' from `#__community_chat` as a';
			$query .= ' where not exists (';
			$query .= '		select b.`id` from `#__social_migrators` as b';
			$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('conversation') . ' and b.`component` = ' . $db->Quote($this->name);
			$query .= ')';
			$query .= ' ORDER BY a.`id` ASC';
			$query .= ' LIMIT ' . $this->limit;

		}

		$sql->raw($query);
		$db->setQuery($sql);

		$jsMsgs = $db->loadObjectList();

		if (count($jsMsgs) <= 0) {
			return null;
		}

		foreach ($jsMsgs as $jsMsg) {

			$esConvObj = false;

			if ($isNewChat) {
				$esConvObj = $this->addChatConversation($jsMsg);
			} else {
				$esConvObj = $this->addConversation($jsMsg);
			}

			if ($esConvObj === false) {
				// this means this conversation do not have any recipient. we abort the process.
				$this->log('conversation', $jsMsg->id, '0');
				$this->info->setInfo('Conversation with id \'' . $jsMsg->id . '\' do not have any recipients found in JomSocial. This conversation is not migrated.');
				continue;
			}

			$esConvId = $esConvObj->esConvId;
			$recipients = $esConvObj->recipients;

			//now we need to process all the messages here.
			$query = 'select * from `#__community_msg`';
			$query .= ' where `parent` = ' . $db->Quote($jsMsg->id);
			$query .= ' order by `id` asc';

			if ($isNewChat) {
				$query = 'select * from `#__community_chat_activity`';
				$query .= ' where `chat_id` = ' . $db->Quote($jsMsg->id);
				$query .= ' and `action` = ' . $db->Quote('sent');
				$query .= ' order by `id` asc';
			}

			$db->setQuery($query);
			$jsMessages = $db->loadObjectList();

			$lastRepliedDate = '';
			foreach ($jsMessages as $jsMessage) {

				if ($isNewChat) {
					$this->addChatMessage($jsMessage, $esConvObj);
				} else {
					$this->addMessage($jsMessage, $esConvObj);
				}

				$lastRepliedDate = ($isNewChat) ? $jsMessage->created_at : $jsMessage->posted_on;
			}

			//now update the last replied date in conversation.
			$query = 'update `#__social_conversations` set `lastreplied` = ' . $db->Quote($lastRepliedDate);
			$query .= ' where `id` = ' . $db->Quote($esConvId);

			$sql->clear();
			$sql->raw($query);
			$db->setQuery($sql);
			$db->query();

			$this->info->setInfo('Conversation with id \'' . $jsMsg->id . '\' is now migrated into EasySocial the new id: ' . $esConvId . ' together with the associated messages.');

		} //end foreach

		return $this->info;
	}

	/**
	 * To support JomSocial 4.4+ the new chat system
	 *
	 * @since	2.1.10
	 * @access	private
	 */
	private function addChatMessage($jsMessage, $esConvObj)
	{
		$esConvId = $esConvObj->esConvId;
		$recipients = $esConvObj->recipients;

		$esMessage = ES::table('ConversationMessage');

		$esMessage->conversation_id	= $esConvId;
		$esMessage->type = SOCIAL_CONVERSATION_TYPE_MESSAGE;

		$esMessage->message = $jsMessage->content;
		$esMessage->created = $jsMessage->created_at;
		$esMessage->created_by = $jsMessage->user_id;

		$esMessage->store();

		// add into message mapping table
		foreach ($recipients as $recipient) {
			$esMessageMap = ES::table('ConversationMessageMap');
			$esMessageMap->user_id = $recipient;
			$esMessageMap->conversation_id = $esConvId;
			$esMessageMap->state = SOCIAL_STATE_PUBLISHED;
			$esMessageMap->isread = SOCIAL_CONVERSATION_READ; // mark all message to read.
			$esMessageMap->message_id = $esMessage->id;
			$esMessageMap->store();
		}

		$this->log('messages', $jsMessage->id, $esMessage->id);
	}

	/**
	 * To support JomSocial 4.3 or lower version the old message system
	 *
	 * @since	2.0.0
	 * @access	private
	 */
	private function addMessage($jsMessage, $esConvObj)
	{
		$esConvId = $esConvObj->esConvId;
		$recipients = $esConvObj->recipients;

		$esMessage = ES::table('ConversationMessage');

		$esMessage->conversation_id	= $esConvId;
		$esMessage->type = SOCIAL_CONVERSATION_TYPE_MESSAGE;


		//lets decode the content and if it return an object or not.
		$content = $jsMessage->body;
		$msgContainer = ES::json()->decode($jsMessage->body);

		if (is_object($msgContainer)) {
			$content = $msgContainer->content;
		}

		$esMessage->message = $content;
		$esMessage->created = $jsMessage->posted_on;
		$esMessage->created_by = $jsMessage->from;

		$esMessage->store();

		// add into message mapping table
		foreach ($recipients as $recipient) {
			$esMessageMap = ES::table('ConversationMessageMap');
			$esMessageMap->user_id = $recipient;
			$esMessageMap->conversation_id = $esConvId;
			$esMessageMap->state = SOCIAL_STATE_PUBLISHED;
			$esMessageMap->isread = SOCIAL_CONVERSATION_READ; // mark all message to read.
			$esMessageMap->message_id = $esMessage->id;
			$esMessageMap->store();
		}

		$this->log('messages', $jsMessage->id, $esMessage->id);
	}

	/**
	 * To support JomSocial 4.4+ the new chat system
	 *
	 * @since	2.1.10
	 * @access	private
	 */
	private function addChatConversation($jsMsg)
	{
		// we need to know if this conversation is a single or multiple recipiant
		$db = ES::db();

		$query = "select user_id from `#__community_chat_participants`";
		$query .= " where `chat_id` = " . $db->Quote($jsMsg->id);
		// $query .= " and `enabled` = " . $db->Quote(1);

		$db->setQuery($query);
		$recipients = $db->loadColumn();

		if (count($recipients) == 0) {
			return false;
		}

		// remove duplicate values
		$recipients = array_unique($recipients);

		// get basic details.
		// always assume the 1st record will be the user who initiate this chat.
		$query = "select * from `#__community_chat_activity` where `chat_id` = " . $db->Quote($jsMsg->id);
		$query .= " order by id asc limit 1";

		$db->setQuery($query);
		$tmp = $db->loadObject();

		// add into conversation table.
		$esConv = ES::table('Conversation');

		$esConv->created = $tmp->created_at;
		$esConv->created_by = $tmp->user_id;
		$esConv->lastreplied = ''; // this last replied will be updated later.

		$convType = $jsMsg->type == 'group' ? SOCIAL_CONVERSATION_MULTIPLE : SOCIAL_CONVERSATION_SINGLE;
		$esConv->type = $convType;
		$esConv->store();

		$esConvId = $esConv->id;

		// now we need to add into participants table
		foreach ($recipients as $recipient) {
			$esParty = ES::table('ConversationParticipant');

			$esParty->conversation_id = $esConvId;
			$esParty->user_id = $recipient;
			$esParty->state = '1';

			$esParty->store();
		}

		//done. lets log this conversation id into migrator table.
		// log into mgirator
		$this->log('conversation', $jsMsg->id, $esConvId);

		$obj = new stdClass();

		$obj->esConvId = $esConvId;
		$obj->recipients = $recipients;

		return $obj;
	}

	/**
	 * To support JomSocial 4.3 or lower version the old message system
	 *
	 * @since	2.0.0
	 * @access	private
	 */
	private function addConversation($jsMsg)
	{
		// we need to know if this conversation is a single or multiple recipiant
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select if(`msg_from` = ' . $db->Quote($jsMsg->from) . ', `to`, `msg_from`) as `user_id` from `#__community_msg_recepient` where `msg_parent` = ' . $db->Quote($jsMsg->id);

		$sql->raw($query);
		$db->setQuery($sql);

		$recipients = $db->loadColumn();

		if (count($recipients) == 0) {
			return false;
		}

		// add creator into the list.
		$recipients[] = $jsMsg->from;

		// remove duplicate values
		$recipients = array_unique($recipients);

		// add into conversation table.
		$esConv = ES::table('Conversation');

		$esConv->created = $jsMsg->posted_on;
		$esConv->created_by = $jsMsg->from;
		$esConv->lastreplied = ''; // this last replied will be updated later.

		$convType = (count($recipients) > 2) ? SOCIAL_CONVERSATION_MULTIPLE : SOCIAL_CONVERSATION_SINGLE;
		$esConv->type = $convType;
		$esConv->store();

		$esConvId = $esConv->id;

		// now we need to add into participants table
		foreach ($recipients as $recipient) {
			$esParty = ES::table('ConversationParticipant');

			$esParty->conversation_id = $esConvId;
			$esParty->user_id = $recipient;
			$esParty->state = '1';

			$esParty->store();
		}

		//done. lets log this conversation id into migrator table.
		// log into mgirator
		$this->log('conversation', $jsMsg->id, $esConvId);

		$obj = new stdClass();

		$obj->esConvId = $esConvId;
		$obj->recipients = $recipients;

		return $obj;
	}

	private function processPoints()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__community_users` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote('points') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`userid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);


		$jsUsers = $db->loadObjectList();

		if (count($jsUsers) <= 0) {
			return null;
		}

		$points = ES::points();

		$message = JTEXT::_('COM_EASYSOCIAL_JOMSOCIAL_MIGRATION_POINT_HISTORY_MESSAGE');

		foreach ($jsUsers as $jsUser) {
			$points->assignCustom($jsUser->userid , $jsUser->points , $message);

			// log into mgirator
			$this->log('points', $jsUser->userid , $jsUser->userid);

			$this->info->setInfo('Points from user ' . $jsUser->userid . ' is now migrated into EasySocial.');
		}

		return $this->info;
	}


	private function processCover()
	{
		$config = ES::config();
		$db 	= ES::db();
		$sql 	= $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__community_users` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote('cover') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`userid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsUsers = $db->loadObjectList();

		if (count($jsUsers) <= 0) {
			return null;
		}

		foreach ($jsUsers as $jsUser) {
			if (!$jsUser->cover || stristr($jsUser->cover, '-default.png') !== false) {
				// no need to process further.
				$this->log('cover', $jsUser->userid , $jsUser->userid);

				$this->info->setInfo('User ' . $jsUser->userid . ' is using default cover. no migration is needed.');
				continue;
			}

			$imagePath = JPATH_ROOT . '/' . $jsUser->cover;

			$tmp = explode('/', $imagePath);
			$filename = $tmp[count($tmp) - 1];

			if (!JFile::exists($imagePath)) {
				$this->log('cover', $jsUser->userid , $jsUser->userid);

				$this->info->setInfo('User ' . $jsUser->userid . ' the cover image file is not found from the server. Process aborted.');
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
			$album 	= $albumModel->getDefaultAlbum($jsUser->userid , SOCIAL_TYPE_USER , SOCIAL_ALBUM_PROFILE_COVERS);
			$album->user_id = $jsUser->userid;
			$album->store();

			$photo = ES::table('Photo');
			$photo->uid = $jsUser->userid ;
			$photo->user_id = $jsUser->userid ;
			$photo->type = SOCIAL_TYPE_USER;
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
				$meta->value = $storage . '/' . $fileName;

				$meta->store();
			}

			// Load the cover
			$cover = ES::table('Cover');
			$cover->uid = $jsUser->userid;
			$cover->type = SOCIAL_TYPE_USER;

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
			$jsAlbum = $this->getJSCoreAlbum('profile.Cover', $jsUser->userid);
			if (isset($jsAlbum->id) && $jsAlbum->id) {
				$jsLikes = $this->getJSLikes('album', $jsAlbum->id);
				if ($jsLikes) {
					$this->addAlbumLikes($album, $jsLikes, 'albums.user.create');
				}
			}

			// add privacy
			$this->addItemPrivacy('photos.view', $photo->id, SOCIAL_TYPE_PHOTO, $jsUser->userid, '0');


			// @Add stream item when a new event cover is uploaded
			// get the cover update date.
			$uploadDate = $this->getMediaUploadDate('cover.upload', $jsUser->userid);
			$photo->addPhotosStream('updateCover', $uploadDate);

			// log into mgirator
			$this->log('cover', $jsUser->userid , $jsUser->userid);

			$this->info->setInfo('User cover ' . $jsUser->userid . ' is now migrated into EasySocial.');

		}

		return $this->info;

	}

	private function getMediaUploadDate($context, $userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select `created` from `#__community_activities` where `actor` = '$userId' and `app` = '$context' and `groupid` = '0' and `eventid` = '0' order by `id` desc limit 1";
		$sql->raw($query);

		$db->setQuery($sql);

		$lastUploadDate = $db->loadResult();

		if (!$lastUploadDate || $lastUploadDate == '00-00-00 00:00:00') {
			// date empty; this could be from older version of JS where the 'profile.avatar.upload' doesnt exits in app column
			$query = "select `created` from `#__community_activities` where `actor` = '$userId'";
			$query .= " and `app` = 'profile'";
			if ($context == 'profile.avatar.upload') {
				$query .= " and `cid` = '0'";
			}
			$query .= " and `comment_type` IN ('profile.status', '$context')";
			$query .= " order by `id` desc limit 1";

			$sql->clear();
			$sql->raw($query);
			$db->setQuery($sql);

			$lastUploadDate = $db->loadResult();

			if (!$lastUploadDate || $lastUploadDate == '00-00-00 00:00:00') {
				// if still empty, then we use user's registration date.
				$query = "select `registerDate` from `#__users` where `id` = '$userId'";

				$sql->clear();
				$sql->raw($query);
				$db->setQuery($sql);

				$lastUploadDate = $db->loadResult();
			}
		}

		return $lastUploadDate;
	}

	private function processAvatar()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__community_users` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote('avatar') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`userid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);


		$jsUsers = $db->loadObjectList();

		if (count($jsUsers) <= 0) {
			return null;
		}


		foreach ($jsUsers as $jsUser) {

			if (!$jsUser->avatar || $jsUser->avatar == 'components/com_community/assets/default.jpg') {
				// no need to process further.
				$this->log('avatar', $jsUser->userid , $jsUser->userid);

				$this->info->setInfo('User ' . $jsUser->userid . ' is using default avatar. no migration is needed.');
				continue;
			}

			$userid = $jsUser->userid;

			// images/avatar/c7a88f6daec02aea3fd3bc4e.jpg
			$imagePath = JPATH_ROOT . '/' . $jsUser->avatar;

			$tmp = explode('/', $imagePath);
			$filename = $tmp[count($tmp) - 1];

			if (!JFile::exists($imagePath)) {
				$this->log('avatar', $jsUser->userid , $jsUser->userid);

				$this->info->setInfo('User ' . $jsUser->userid . ' the avatar image file is not found from the server. Process aborted.');
				continue;
			}

			// lets copy this file to tmp folder 1st.
			$tmp = JFactory::getConfig()->get('tmp_path');
			$tmpImageFile = $tmp . '/' . md5(JFactory::getDate()->toSql());
			JFile::copy($imagePath , $tmpImageFile);

			$image = ES::image();
			$image->load($tmpImageFile);

			$avatar	= ES::avatar($image, $userid, SOCIAL_TYPE_USER);

			// Check if there's a profile photos album that already exists.
			$albumModel	= ES::model('Albums');

			// Retrieve the user's default album
			$album = $albumModel->getDefaultAlbum($userid , SOCIAL_TYPE_USER , SOCIAL_ALBUM_PROFILE_PHOTOS);

			// we need to update the album user_id to this current user.
			$album->user_id = $userid;
			$album->store();

			$photo = ES::table('Photo');
			$photo->uid = $userid;
			$photo->type = SOCIAL_TYPE_USER;
			$photo->album_id = $album->id;
			$photo->user_id = $userid;
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

			// Create the avatars now, but we do not want the store function to create stream.
			// so we pass in the option. we will create the stream ourown.
			$options = array('addstream' => false);
			$avatar->store($photo, $options, true);


			// add photo privacy
			$this->addItemPrivacy('photos.view', $photo->id, SOCIAL_TYPE_PHOTO, $jsUser->userid, '0');


			// add photo stream
			// let get the avatar upload date.
			$lastAvatarUploadDate = $this->getMediaUploadDate('profile.avatar.upload', $jsUser->userid);
			$photo->addPhotosStream('uploadAvatar', $lastAvatarUploadDate);

			// process avatar albums likes.
			$jsAlbum = $this->getJSCoreAlbum('profile.avatar', $jsUser->userid);
			if (isset($jsAlbum->id) && $jsAlbum->id) {
				$jsLikes = $this->getJSLikes('album', $jsAlbum->id);
				if ($jsLikes) {
					$this->addAlbumLikes($album, $jsLikes, 'albums.user.create');
				}
			}

			// log into mgirator
			$this->log('avatar', $jsUser->userid , $jsUser->userid);

			$this->info->setInfo('User avatar ' . $jsUser->userid . ' is now migrated into EasySocial.');

		}//end foreach


		return $this->info;

	}

	/**
	 * Method to get user's avatar / cover albums from JS
	 * @return [type] [description]
	 */
	private function getJSCoreAlbum($type, $userId)
	{
		$db = ES::db();

		$query = "select * from `#__community_photos_albums`";
		$query .= " where `creator` = " . $db->Quote($userId);
		$query .= " and `type` = " . $db->Quote($type);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	private function processAlbumComments()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esalbumid`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('albums') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('albumcomments') . ' and b.`component` = ' . $db->Quote($this->name);
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
				$this->log('albumcomments', $jsAlbumComment->id, -1);

				$this->info->setInfo('Album comment with id \'' . $jsAlbumComment->id . '\' is not associate with album in EasySocial. Album commment migration process aborted.');
				continue;
			}

			$esAlbumTbl = ES::table('Album');
			$esAlbumTbl->load($jsAlbumComment->esalbumid);

			$obj = new stdClass();
			$obj->url = $esAlbumTbl->getPermalink();
			$obj->url = $this->removeAdminSegment($obj->url);

			$esComment = ES::table('Comments');
			$esComment->element = 'albums.user.create';
			$esComment->uid = $jsAlbumComment->esalbumid;
			$esComment->comment = $jsAlbumComment->comment;
			$esComment->created_by = $jsAlbumComment->post_by;
			$esComment->created = $jsAlbumComment->date;
			$esComment->params = ES::json()->encode($obj);
			$esComment->stream_id = $this->getAlbumStreamId($jsAlbumComment->esalbumid);

			//off the trigger for migrated commetns.
			$esComment->offTrigger();
			$esComment->store();

			// Regex if has mention
			// $this->processMentions($esComment);
			SocialMigratorHelper::processCommentMentions($esComment);

			// check if this comment has image attachment or not.
			$jsCommentParams = ES::registry($jsAlbumComment->params);
			$jsCommentAttachmentId = $jsCommentParams->get('attached_photo_id', 0);
			if ($jsCommentAttachmentId) {
				SocialMigratorHelper::processCommentAttachment($jsAlbumComment, $esComment);
			}

			// log into mgirator
			$this->log('albumcomments', $jsAlbumComment->id, $esComment->id);
			$this->info->setInfo('Album comment with id \'' . $jsAlbumComment->id . '\' is now migrated into EasySocial the new comment id: ' . $esComment->id . '.');

		}

		return $this->info;
	}

	private function processPhotoComments()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esphotoid`';
		$query .= ' 	from `#__community_wall` as a';
		$query .= '		inner join `#__social_migrators` as c on a.`contentid` = c.`oid` and c.`element` = ' . $db->Quote('photos') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('photocomments') . ' and b.`component` = ' . $db->Quote($this->name);
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
				$this->log('photocomments', $jsPhotoComment->id, -1);

				$this->info->setInfo('Photo comment with id \'' . $jsPhotoComment->id . '\' is not associate with photo in EasySocial. Photo commment migration process aborted.');
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
			$esComment->element = 'photos.user.add';
			$esComment->uid = $jsPhotoComment->esphotoid;
			$esComment->comment = $jsPhotoComment->comment;
			$esComment->created_by = $jsPhotoComment->post_by;
			$esComment->created = $jsPhotoComment->date;
			$esComment->params = ES::json()->encode($obj);
			$esComment->stream_id = $this->getPhotoStreamId($jsPhotoComment->esphotoid);

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
			$this->log('photocomments', $jsPhotoComment->id, $esComment->id);
			$this->info->setInfo('Photo comment with id \'' . $jsPhotoComment->id . '\' is now migrated into EasySocial the new comment id: ' . $esComment->id . '.');

		}//end foreach

		return $this->info;

	}

	private function getAlbumStreamId($esAlbumId)
	{
		static $_cache = array();

		$db = ES::db();
		$sql = $db->sql();

		if (! isset($_cache[$esAlbumId])) {

			$sql->select('#__social_stream_item', 'a');
			$sql->column('a.uid');
			$sql->where('a.context_type', SOCIAL_TYPE_ALBUM);
			$sql->where('a.context_id', $esAlbumId);

			$db->setQuery($sql);

			$uid = (int) $db->loadResult();
			$_cache[$esAlbumId] = $uid;
		}

		return $_cache[$esAlbumId];
	}

	private function getPhotoStreamId($esPhotoId)
	{
		static $_cache = array();

		$db = ES::db();
		$sql = $db->sql();

		if (! isset($_cache[$esPhotoId])) {

			$sql->select('#__social_stream_item', 'a');
			$sql->column('a.uid');
			$sql->where('a.context_type', SOCIAL_TYPE_PHOTO);
			$sql->where('a.context_id', $esPhotoId);

			$db->setQuery($sql);

			$uid = (int) $db->loadResult();
			$_cache[$esPhotoId] = $uid;
		}

		return $_cache[$esPhotoId];
	}

	private function processPhotos()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__community_photos` as a';
		$query .= ' inner join `#__community_photos_albums` as pa on a.`albumid` = pa.`id` and pa.`type` = ' . $db->Quote('user');
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('photos') . ' and b.`component` = ' . $db->Quote($this->name);
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
			if (! $jsPhoto->published) {
				// photos not published. do not migrate.

				// log into mgirator
				$this->log('photos', $jsPhoto->id, -1);

				$this->info->setInfo('Photo with id \'' . $jsPhoto->id . '\' is currently in unpublished or delete state. Photo migration process aborted.');
				continue;
			}

			// images/originalphotos/84/1/e03fbd75d6e8f5fe0e542665.jpg
			$imagePath = JPATH_ROOT . '/' . $jsPhoto->original;

			if (!JFile::exists($imagePath)) {
				// files from originalphotos not found. let try to get it from photos folder instead.

				// images/photos/84/1/e03fbd75d6e8f5fe0e542665.jpg
				$imagePath = JPATH_ROOT . '/' . $jsPhoto->image;
			}

			if (!JFile::exists($imagePath)) {
				// both image from originalphotos and photos folder not found. Lets give up.

				// log into mgirator
				$this->log('photos', $jsPhoto->id, -1);

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

			$esPhoto->uid = $jsPhoto->creator;
			$esPhoto->type = SOCIAL_TYPE_USER;
			$esPhoto->album_id = $esAlbumId;
			$esPhoto->user_id = $jsPhoto->creator;

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
				$caption = $exif->getCaption();
				$createdAlias = $exif->getCreationDate();

				if ($createdAlias) {
					$esPhoto->assigned_date = $createdAlias;
				}

				if ($title) {
					$esPhoto->title = $title;
				}

				if ($caption) {
					$esPhoto->caption = $caption;
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
				$meta->value = $storage . '/' . $fileName;

				$meta->store();
			}

			// add photo privacy
			$this->addItemPrivacy('photos.view', $esPhoto->id, SOCIAL_TYPE_PHOTO, $jsPhoto->creator, $jsPhoto->permissions);

			// add photo stream
			$esPhoto->addPhotosStream('create', $jsPhoto->created);

			// log into mgirator
			$this->log('photos', $jsPhoto->id, $esPhoto->id);


			$this->info->setInfo('Photo with id \'' . $jsPhoto->id . '\' is now migrated into EasySocial the new photo id: ' . $esPhoto->id . ' together with the associated album.');


		}//end foreach

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

	private function processJSAlbumCover()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, b.`uid` as `esalbumid`, c.`uid` as `esphotoid`';
		$query .= ' from `#__community_photos_albums` as a';
		$query .= '		inner join `#__social_migrators` as b on a.id = b.oid and b.`element` = ' . $db->Quote('albums') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= '		inner join `#__social_migrators` as c on a.photoid = c.oid and c.`element` = ' . $db->Quote('photos') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select d.`id` from `#__social_migrators` as d';
		$query .= ' 			where a.`id` = d.`oid` and d.`element` = ' . $db->Quote('albumscover') . ' and d.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsAlbums = $db->loadObjectList();

		if (count($jsAlbums) <= 0) {
			return null;
		}

		foreach ($jsAlbums as $jsAlbum) {

			$esAlbumId = $jsAlbum->esalbumid;
			$esPhotoId = $jsAlbum->esphotoid;

			//lets add cover photo into this photo's album
			$album = ES::table('Album');
			$album->load($esAlbumId);

			$album->cover_id = $esPhotoId;
			$album->store();

			// log into mgirator
			$this->log('albumscover', $jsAlbum->id, $esAlbumId);

			$this->info->setInfo('Cover photo for album \'' . $jsAlbum->id . '\' is now migrated into EasySocial.');
		}

		return $this->info;
	}

	private function processJSPhotoAlbum($jsPhoto)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, b.`uid` as `esalbumid`';
		$query .= ' from `#__community_photos_albums` as a';
		$query .= '		left join `#__social_migrators` as b on a.id = b.oid and b.`element` = ' . $db->Quote('albums') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ' where a.id = ' . $db->Quote($jsPhoto->albumid);

		$sql->raw($query);
		$db->setQuery($sql);

		$jsAlbum = $db->loadObject();

		if (! isset($jsAlbum->id)) {
			// js album not found. lets get the core album for this user from ES.
			// @todo: create core album
		}

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
		$esAlbum->uid = $jsAlbum->creator;
		$esAlbum->type = SOCIAL_TYPE_USER;
		$esAlbum->user_id = $jsAlbum->creator;

		// @todo: get the album cover photo.
		$esAlbum->cover_id = '0';

		$esAlbum->title = $jsAlbum->name;

		// JS core value only have 1 defined to user story photo
		$jsCoreAlbumExist = isset($jsAlbum->default) && $jsAlbum->default == 1 ? true : false;

		// Since JS hardcoded those album name in database, it will difficult to use other language translation if the site is running multilingue
		if ($jsCoreAlbumExist) {

			// Override those user story photo default name
			if ($jsAlbum->type == 'user' && $jsAlbum->default == 1) {
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
			$this->addAlbumLikes($esAlbum, $jsLikes, 'albums.user.create');
		}

		$this->addItemPrivacy('albums.view', $esAlbum->id, SOCIAL_TYPE_ALBUM, $jsAlbum->creator, $jsAlbum->permissions);

		// no need to add album stream here. the album jtable already taken care.

		$this->log('albums', $jsAlbum->id, $esAlbum->id);

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

		if (isset($result->like) && $result->like) {
			$likes = $result->like;
			$userIds = explode(',', $likes);
		}

		return $userIds;
	}

	private function processConnection()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__community_connection` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`connection_id` = b.`oid` and b.`element` = ' . $db->Quote('connection') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`connection_id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsConns = $db->loadObjectList();

		if (count($jsConns) <= 0) {
			return null;
		}

		foreach ($jsConns as $jsConn) {
			//let check if this two user already a fren in ES or not.
			$actor 	= $jsConn->connect_from;
			$target = $jsConn->connect_to;


			$query = 'select a.`id` from `#__social_friends` as a';
			$query .= ' where (a.`actor_id` = ' . $db->Quote($actor) . ' and a.`target_id` = ' . $db->Quote($target);
			$query .= ' OR a.`target_id` = ' . $db->Quote($actor) . ' and a.`actor_id` = ' . $db->Quote($target) . ')';

			$sql->clear();
			$sql->raw($query);

			$db->setQuery($sql);
			$esFriends = $db->loadObjectList();

			if (count($esFriends) > 0) {
				// this mean they are already have connection in ES. lets log into mgirator table.
				foreach ($esFriends as $friend) {
					$this->log('connection', $jsConn->connection_id, $friend->id);
				}

				$this->info->setInfo('User \'' . $actor . '\' already connected with user \'' . $target . '\' so no migration need for these two users.');
				continue;
				// return $this->info;
			}

			// lets add into social friend table
			$esFriendTbl = ES::table('Friend');

			$esFriendTbl->actor_id = $actor;
			$esFriendTbl->target_id = $target;
			$esFriendTbl->state = ($jsConn->status == '0') ? SOCIAL_FRIENDS_STATE_PENDING : SOCIAL_FRIENDS_STATE_FRIENDS ;
			$esFriendTbl->created = $jsConn->created;

			$esFriendTbl->store();
			$newFriendId = $esFriendTbl->id;

			// done. lets add into log table.
			$this->log('connection', $jsConn->connection_id, $newFriendId);

			if ($esFriendTbl->state == SOCIAL_FRIENDS_STATE_FRIENDS) {
				$stream = ES::stream();
				$streamTemplate = $stream->getTemplate();

				// Set the actor.
				$streamTemplate->setActor($actor, SOCIAL_TYPE_USER);

				// Set the context.
				$streamTemplate->setContext($esFriendTbl->id , SOCIAL_TYPE_FRIEND);

				// Set the actor.
				$streamTemplate->setTarget($target);

				// Set the verb.
				$streamTemplate->setVerb('add');

				// set to aggreate friend
				$streamTemplate->setAggregate(true);

				// set the stream creation date
				$streamTemplate->setDate($jsConn->created);

				$streamTemplate->setAccess('core.view');

				// Create the stream data.
				$stream->add($streamTemplate);
			}

			// // we knwo jomsocial the connection table always come with pair records for approved request. lets add the next record as well.
			// if($jsConn->status == '1')
			// {
			// 	$nextJsConnId = $jsConn->connection_id + 1;
			// 	$this->log('connection', $nextJsConnId, $newFriendId);
			// }

			$this->info->setInfo('User \'' . $actor . '\' is now connected with user \'' . $target . '\' in your EasySocial.');


		}// end foreach

		return $this->info;
	}

	private function processProfileUsers()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, c.`uid` as `esprofileid`';
		$query .= ' 	from `#__community_users` as a';
		$query .= '		left join `#__social_migrators` as c on a.`profile_id` = c.`oid` and c.`element` = ' . $db->Quote('profiles') . ' and c.`component` = ' . $db->Quote($this->name);
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote('profileusers') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$query .= ' ORDER BY a.`userid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$jsUsers = $db->loadObjectList();

		if (count($jsUsers) <= 0) {
			return null;
		}


		foreach ($jsUsers as $jsUser) {

			// before we start, we need to remove this user from ES profile map table so that same user will not have two profile in the mapping table.
			// this is because during the ES installation, all uses will be mapped into a default profile.
			$query = 'delete from `#__social_profiles_maps` where `user_id` = ' . $db->Quote($jsUser->userid);
			$sql->clear();
			$sql->raw($query);
			$db->setQuery($sql);
			$db->query();


			$esProMapId = '';

			if (!$jsUser->profile_id && !$jsUser->esprofileid) {
				// this user didnt assigned to any profile.
				// this also means that the user do not have any customfields attached.
				// let give this user a default ES profile.

				$query = 'select `id` from `#__social_profiles` where `default` = ' . $db->Quote('1');

				$sql->clear();
				$sql->raw($query);

				$db->setQuery($sql);
				$esProMapId = $db->loadResult();

				if (! $esProMapId) {
					// default profile is not set. let get the 1st profile
					$query = 'select `id` from `#__social_profiles` order by `id` asc limit 1';
					$sql->clear();
					$sql->raw($query);

					$db->setQuery($sql);
					$esProMapId = $db->loadResult();
				}


				// update this user to es profile id.
				$userMapPro = new stdClass();

				$userMapPro->profile_id = $esProMapId;
				$userMapPro->user_id = $jsUser->userid;
				$userMapPro->state = 1;
				$userMapPro->created = ES::date()->toMySQL();

				$db->insertObject('#__social_profiles_maps', $userMapPro, 'id');
				// $newId = $db->insertID();

				$this->info->setInfo('User id \'' . $jsUser->userid . '\' has not associated in any profile in JomSocial. Assigning default EasySocial profile to this user.');
			} else {
				// lets get js fiels from this user.
				$profile = ES::table('Profile');
				$profile->load($jsUser->esprofileid);
				$workflow = $profile->getWorkflow();

				$query = 'select a.*, b.`uid` as `esFieldId`, e.`type` as `jsfield_type`, e.`fieldcode` as `jsfield_fieldcode`';
				$query .= ' from `#__community_fields_values` as a';
				$query .= ' inner join `#__community_fields` as e on a.field_id = e.id';
				$query .= ' inner join `#__social_migrators` as b on a.`field_id` = b.`oid` and b.`element` = ' . $db->Quote('fields') . ' and b.`component` = ' . $db->Quote('com_community');
				$query .= ' inner join `#__social_fields` as c on b.`uid` = c.`id`';
				$query .= ' inner join `#__social_fields_steps` as d on c.`step_id` = d.`id`';
				$query .= ' where d.`workflow_id` = ' . $db->Quote($workflow->id);
				$query .= ' and a.`user_id` = ' . $db->Quote($jsUser->userid);
				$query .= ' order by a.`field_id` asc';


				$sql->clear();
				$sql->raw($query);

				$db->setQuery($sql);

				$jsUserFields = $db->loadObjectList();

				if (count($jsUserFields) <= 0) {
					// add into es profie mapping table and return.
					$esProMapId = $this->addESProfileMap($jsUser->userid , $jsUser->esprofileid);

					$this->info->setInfo('User id \'' . $jsUser->userid . '\' has succefully mapped into EasySocial profile id ' . $jsUser->esprofileid . '.');
				} else {
					// lets map the fields into es fields and es profile.
					$esProMapId = $this->addESProfileMap($jsUser->userid , $jsUser->esprofileid);


					//user latitude and longitude from JS
					$userLat = (isset($jsUser->latitude) && $jsUser->latitude && ($jsUser->latitude != '255' && $jsUser->latitude != '255.000000')) ? $jsUser->latitude : '';
					$userLon = (isset($jsUser->longitude) && $jsUser->longitude && ($jsUser->longitude != '255' && $jsUser->longitude != '255.000000')) ? $jsUser->longitude : '';


					//json lib
					$json = ES::json();

					$dataContainer = array();

					foreach ($jsUserFields as $jsUserField) {
						// $esFieldData 	= ES::table('FieldData');
						// $fieldElement 	= $this->mapping[$jsUserField->jsfield_type];
						$jsFieldObj = new stdClass();
						$jsFieldObj->id 		= $jsUserField->field_id;
						$jsFieldObj->type 		= $jsUserField->jsfield_type;
						$jsFieldObj->fieldcode 	= $jsUserField->jsfield_fieldcode;

						$esFieldApp 	= $this->getESFieldApp($jsFieldObj);

						$fieldElement 	= $esFieldApp->element;

						if (! isset($dataContainer[$jsUserField->esFieldId])) {
							$esFieldData 	= new stdClass();
							$esFieldData->field_id 	= $jsUserField->esFieldId;
							$esFieldData->uid 		= $jsUser->userid;
							$esFieldData->type 		= SOCIAL_TYPE_USER;
							$esFieldData->element 	= $fieldElement;
							$esFieldData->access 	= $jsUserField->access;
							$esFieldData->data = array();
						} else {
							$esFieldData = $dataContainer[$jsUserField->esFieldId];
						}

						switch ($fieldElement)
						{
							case 'gender':

								$gender = 0;

								$jsValue = strtolower($jsUserField->value);

								if ($jsValue == 'male' || $jsValue == 'com_community_male') {
									$gender = 1;
								} else if($jsValue == 'female' || $jsValue == 'com_community_female') {
									$gender = 2;
								}

								$valueObj = new stdClass();
								$valueObj->datakey = '';
								$valueObj->data = $gender;
								$valueObj->raw 	= $gender;

								$esFieldData->data['default'] = $valueObj;

								break;

							case 'country':
								if (stristr($jsUserField->value, 'COM_COMMUNITY_LANG_NAME_') !== false) {
									$jsUserField->value = str_replace('COM_COMMUNITY_LANG_NAME_', '', $jsUserField->value);
								}

								$jsCountryValue = $this->getCountryCode($jsUserField->value);

								$valueObj = new stdClass();
								$valueObj->datakey = '0';
								$valueObj->data = $jsCountryValue;
								$valueObj->raw 	= $jsCountryValue;

								$esFieldData->data['default'] = $valueObj;

								break;

							case 'dropdown':
							case 'textbox':
							case 'textarea':
							case 'email':
							case 'url':

								$valueObj = new stdClass();
								$valueObj->datakey = '';
								$valueObj->data = $jsUserField->value;
								$valueObj->raw 	= $jsUserField->value;

								$esFieldData->data['default'] = $valueObj;

								break;

							case 'multilist':
							case 'checkbox':

								$jsArrValues = explode(",", $jsUserField->value);

								// remove empty value elements.
								$jsArrValues = array_diff($jsArrValues, array(''));

								$valueObj = new stdClass();
								$valueObj->datakey = '';
								$valueObj->data = $json->encode($jsArrValues);
								$valueObj->raw 	= implode(', ', $jsArrValues);

								$esFieldData->data['default'] = $valueObj;

								break;

							case 'birthday':

								// lets check if the value is a valid datetime or not.
								if (strtotime($jsUserField->value) !== false && strtotime($jsUserField->value) != -1) {
									$date = ES::date($jsUserField->value);

									$valueObj = new stdClass();
									$valueObj->datakey = 'date';
									$valueObj->data = $date->toSql();
									$valueObj->raw 	= $date->toSql();

									$esFieldData->data['date'] = $valueObj;
								}

								break;

							case 'datetime':

								$data 	= '';
								$raw 	= '';

								if ($jsUserField->jsfield_type == 'time') {
									$data = '0000-00-00 ' . $jsUserField->value;
									$raw  = '0000-00-00 ' . $jsUserField->value;
								} else {
									// lets check if the value is a valid datetime or not.
									if (strtotime($jsUserField->value) !== false && strtotime($jsUserField->value) != -1) {
										$date = ES::date($jsUserField->value);
										$data = $date->toSql();
										$raw = $date->toSql();
									}
								}

								$valueObj = new stdClass();
								$valueObj->datakey = 'date';
								$valueObj->data = $data;
								$valueObj->raw 	= $raw;

								$esFieldData->data['date'] = $valueObj;

								break;

							case 'joomla_fullname':

								if (strpos(strtolower($jsUserField->jsfield_fieldcode) , 'givenname') !== false) {
									$valueObj = new stdClass();
									$valueObj->datakey = 'first';
									$valueObj->data = $jsUserField->value;
									$valueObj->raw 	= $jsUserField->value;

									$esFieldData->data['first'] = $valueObj;
								}

								if (strpos(strtolower($jsUserField->jsfield_fieldcode) , 'familyname') !== false) {
									$valueObj = new stdClass();
									$valueObj->datakey = 'last';
									$valueObj->data = $jsUserField->value;
									$valueObj->raw 	= $jsUserField->value;

									$esFieldData->data['last'] = $valueObj;
								}

								break;

							case 'address':

								if (strtolower($jsUserField->jsfield_fieldcode) == 'field_address' || strtolower($jsUserField->jsfield_fieldcode) == 'field_street') {
									//let split the address based on the 1st comma.
									$data = explode(',', $jsUserField->value);

									$add1 = array_shift($data);

									$add2 = (!empty($data)) ? implode('',$data) : '';
									// remove new line feed.
									$add2 = trim(preg_replace('/\s\s+/', ' ', $add2));

									//address 1
									$valueObj = new stdClass();
									$valueObj->datakey = 'address1';
									$valueObj->data = $add1;
									$valueObj->raw 	= $add1;
									$esFieldData->data['address1'] = $valueObj;

									//address 2
									$valueObj = new stdClass();
									$valueObj->datakey = 'address2';
									$valueObj->data = $add2;
									$valueObj->raw 	= $add2;

									$esFieldData->data['address2'] = $valueObj;

								}

								if (strtolower($jsUserField->jsfield_fieldcode) == 'field_state') {
									$valueObj = new stdClass();
									$valueObj->datakey = 'state';
									$valueObj->data = $jsUserField->value;
									$valueObj->raw 	= $jsUserField->value;

									$esFieldData->data['state'] = $valueObj;
								}

								if (strtolower($jsUserField->jsfield_fieldcode) == 'field_city') {
									$valueObj = new stdClass();
									$valueObj->datakey = 'city';
									$valueObj->data = $jsUserField->value;
									$valueObj->raw 	= $jsUserField->value;

									$esFieldData->data['city'] = $valueObj;
								}

								if (strtolower($jsUserField->jsfield_fieldcode) == 'field_zip') {
									// we will store country as long name.
									$valueObj = new stdClass();
									$valueObj->datakey = 'zip';
									$valueObj->data = $jsUserField->value;
									$valueObj->raw 	= $jsUserField->value;

									$esFieldData->data['zip'] = $valueObj;
								}

								if (strtolower($jsUserField->jsfield_fieldcode) == 'field_country') {
									// we will store country as long name.

									if (stristr($jsUserField->value, 'COM_COMMUNITY_LANG_NAME_') !== false) {
										$jsUserField->value = str_replace('COM_COMMUNITY_LANG_NAME_', '', $jsUserField->value);
									}

									$valueObj = new stdClass();
									$valueObj->datakey = 'country';
									$valueObj->data = $jsUserField->value;
									$valueObj->raw 	= $jsUserField->value;

									$esFieldData->data['country'] = $valueObj;
								}

								break;

							default:
								break;

						}

						// $esFieldData->store();
						$dataContainer[$jsUserField->esFieldId] = $esFieldData;
					}// end foreach


					// now we will do the actual saving here
					if ($dataContainer) {
						foreach($dataContainer as $dataItem) {
							$datas = $dataItem->data;
							$itemCount = count($datas);

							if ($itemCount > 1) {

								if ($dataItem->element == 'address') {

									if (! isset($datas['zip'])) {
										$valueObj = new stdClass();
										$valueObj->datakey = 'zip';
										$valueObj->data = '';
										$valueObj->raw 	= '';
										$datas['zip'] = $valueObj;
									}

									if (! isset($datas['country'])) {
										$valueObj = new stdClass();
										$valueObj->datakey = 'country';
										$valueObj->data = '';
										$valueObj->raw 	= '';
										$datas['country'] = $valueObj;
									}

									$fullAddress = $datas['address1']->raw . ' '
												. $datas['address2']->raw . ' '
												. $datas['city']->raw . ' '
												. $datas['zip']->raw . ' '
												. $datas['state']->raw . ' '
												. $datas['country']->raw;

									$valueObj = new stdClass();
									$valueObj->datakey = 'address';
									$valueObj->data = $fullAddress;
									$valueObj->raw 	= $fullAddress;
									$datas['address'] = $valueObj;

									$valueObj = new stdClass();
									$valueObj->datakey = 'latitude';
									$valueObj->data = $userLat;
									$valueObj->raw 	= $userLat;
									$datas['latitude'] = $valueObj;


									$valueObj = new stdClass();
									$valueObj->datakey = 'longitude';
									$valueObj->data = $userLon;
									$valueObj->raw 	= $userLon;
									$datas['longitude'] = $valueObj;
								}

								if ($dataItem->element == 'joomla_fullname') {
									$fullName = $datas['first']->raw . ' '
											. $datas['last']->raw;

									$valueObj = new stdClass();
									$valueObj->datakey = 'middle';
									$valueObj->data = '';
									$valueObj->raw 	= '';
									$datas['middle'] = $valueObj;

									$valueObj = new stdClass();
									$valueObj->datakey = 'name';
									$valueObj->data = $fullName;
									$valueObj->raw 	= $fullName;
									$datas['name'] = $valueObj;
								}

								foreach ($datas as $data) {
									$this->addFieldData($dataItem, $data);
								}

							} else {
								$data = array_pop($datas);
								$this->addFieldData($dataItem, $data);
							}

							// add privacy to this field
							$this->addItemPrivacy('field.' . $dataItem->element , $dataItem->field_id, 'field', $dataItem->uid, $dataItem->access);

						} // end foreach

					} //end if dataContainer

					$this->info->setInfo('User id \'' . $jsUser->userid . '\' has succefully mapped into EasySocial profile id ' . $jsUser->esprofileid . ' and all the associated fields has migrated into EasySocial.');
				}

			}

			$this->log('profileusers', $jsUser->userid, $esProMapId);

		}//end foreach

		return $this->info;

	}

	private function addFieldData($dataItem, $data)
	{
		$fieldData = ES::table('FieldData');

		$fieldData->field_id = $dataItem->field_id;
		$fieldData->uid = $dataItem->uid;
		$fieldData->type = $dataItem->type;
		$fieldData->datakey = (isset($data->datakey) && $data->datakey != 'default') ? $data->datakey : '';
		$fieldData->data = isset($data->data) ? $data->data : '';
		$fieldData->raw = isset($data->raw) ? $data->raw : '';

		$fieldData->store();
	}

	private function addItemPrivacy($command, $esUid, $esUType, $jsUserId, $jsAccess)
	{
		static $defaultESPrivacy = array();

		$db = ES::db();
		$sql = $db->sql();


		if (! isset($defaultESPrivacy[$command])) {
			$db 	= ES::db();
			$sql 	= $db->sql();

			$commands = explode('.', $command);

			$element = $commands[0];
			$rule  	 = $commands[1];

			$query = 'select `id`, `value` from `#__social_privacy`';
			$query .= ' where `type` = ' . $db->Quote($element);
			$query .= ' and `rule` = ' . $db->Quote($rule);

			$sql->raw($query);
			$db->setQuery($sql);

			$defaultESPrivacy[$command] = $db->loadObject();
		}

		$defaultPrivacy = $defaultESPrivacy[$command];

		$privacyValue = (isset($this->accessMapping[$jsAccess])) ? $this->accessMapping[$jsAccess] : $defaultPrivacy->value;


		$esPrivacyItem = ES::table('PrivacyItems');

		$esPrivacyItem->privacy_id = $defaultPrivacy->id;
		$esPrivacyItem->user_id = $jsUserId;
		$esPrivacyItem->uid = $esUid;
		$esPrivacyItem->type = $esUType;
		$esPrivacyItem->value = $privacyValue;

		$esPrivacyItem->store();
	}

	private function addESProfileMap($jsUserId, $esProfileId)
	{
		$esProfile = ES::table('ProfileMap');

		$esProfile->profile_id = $esProfileId;
		$esProfile->user_id = $jsUserId;
		$esProfile->state = 1;
		$esProfile->created = ES::date()->toMySQL();

		$esProfile->store();

		return $esProfile->id;
	}

	private function createDefaultProfile()
	{
		$db = ES::db();
		$sql = $db->sql();

		// check if we already created the profile or not.
		$query = 'select b.`uid` from `#__social_migrators` as b';
		$query .= ' where b.`oid` = ' . $db->Quote('0');
		$query .= ' and b.`element` = ' . $db->Quote('profiles') . ' and b.`component` = ' . $db->Quote($this->name);

		$sql->clear();
		$sql->raw($query);

		$db->setQuery($sql);
		$pid = $db->loadResult();

		if ($pid) {
			return $pid;
		}

		// setup the default profile for JomSocial
		$jsProfile = new stdClass();

		$jsProfile->id = 0; // we default this to 0 so that the log table get registered this 'virtual' profile.
		$jsProfile->approvals = 1;
		$jsProfile->name = 'JomSocial default profile';
		$jsProfile->description = 'Default profile that migrated based on JomSocial default custom fields.';
		$jsProfile->created = ES::date()->toMySQL();
		$jsProfile->published = 1;

		$newProfileId = $this->processProfileItem($jsProfile);

		//now we need to install the profiles steps.
		$newSteps = $this->createDefaultItems($newProfileId, $jsProfile->name);

		$lastStepId = $newSteps[count($newSteps) - 1];


		$query = 'select a.* from `#__community_fields` as a';
		$query .= ' order by a.`ordering` asc';

		$sql->clear();
		$sql->raw($query);

		$db->setQuery($sql);

		$jsfields = $db->loadObjectList();

		if ($jsfields) {
			foreach ($jsfields as $jsfield) {
				$newFieldId = $this->processField($lastStepId, $jsfield, $newProfileId);
			}
		}

		// lets add privacy for this newly create profile :)
		$this->addPrivacyMap($newProfileId, 'profile');

		return $newProfileId;
	}


	private function processProfiles()
	{
		$db = ES::db();
		$sql = $db->sql();

		// check if JomSocial created any profile or not.
		$query = 'select count(1) from `#__community_profiles`';
		$sql->raw($query);
		$db->setQuery($sql);
		$cnt = $db->loadResult();

		if (!$cnt) {
			// no profile found. lets create one.
			$newProfileId = $this->createDefaultProfile();
			return null;
		}


		$query = 'select a.* from `#__community_profiles` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote('profiles') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' ORDER BY a.`ordering` ASC';
		$query .= ' LIMIT 1';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);

		$jsProfile = $db->loadObject();

		if (! isset($jsProfile->id)) {
			// before we exits, we need to check if any JS users that do not have ant profiles assigned. IF yes, let create a default profile.
			$query = 'select count(1) from `#__community_users` where `profile_id` = ' . $db->Quote('0');
			$sql->clear();
			$sql->raw($query);
			$db->setQuery($query);
			$emptyProfileCnt = $db->loadResult();

			if($emptyProfileCnt)
			{
				// create new profile for these lost souls.
				$newProfileId = $this->createDefaultProfile();
			}

			return null;
		}

		// now we get all the js fields from this profiles.
		// $query = 'select a.*, b.`uid` from `#__community_fields` as a';
		// $query .= ' 	inner join `#__community_profiles_fields` as p on a.`id` = p.`field_id`';
		// $query .= '		left join `#__social_migrators` as b on a.`id` = b.`oid` and b.`element` = ' . $db->Quote('fields') . ' and b.`component` = ' . $db->Quote($this->name);
		// $query .= ' where p.`parent` = ' . $db->Quote($jsProfile->id);
		// $query .= ' order by a.`ordering` asc';

		$query = 'select a.* from `#__community_fields` as a';
		$query .= ' 	inner join `#__community_profiles_fields` as p on a.`id` = p.`field_id`';
		$query .= ' where p.`parent` = ' . $db->Quote($jsProfile->id);
		$query .= ' order by a.`ordering` asc';

		$sql->clear();
		$sql->raw($query);

		$db->setQuery($sql);

		$jsfields = $db->loadObjectList();

		if (count($jsfields) <= 0) {
			//lets create a default profile for this profile.
			$newProfileId = $this->createDefaultProfile();

			//now we log this one into history table.
			$this->log('profiles', $jsProfile->id, $newProfileId);

			// @todo later only deal with error handling.
			$this->info->setInfo('No fields found on profile \'' . $jsProfile->name . '\'. Field import for this profile aborted.');
			return $this->info;
		} else {
			$newProfileId = $this->processProfileItem($jsProfile);

			//now we need to install the profiles steps.
			$newSteps = $this->createDefaultItems($newProfileId, $jsProfile->name);

			$lastStepId = $newSteps[count($newSteps) - 1];

			foreach ($jsfields as $jsfield) {
				$newFieldId = $this->processField($lastStepId, $jsfield, $newProfileId);
			}

			// lets add privacy for this newly create profile :)
			$this->addPrivacyMap($newProfileId, 'profile');
		}

		$this->info->setInfo('Profile \'' . $jsProfile->name . '\' has migrated succefully into EasySocial.');
		return $this->info;

	}


	private function addPrivacyMap($uid, $utype)
	{
		$db = ES::db();
		$sql = $db->sql();

		// lets add privacy for this newly create profile :)
		$query = 'insert into `#__social_privacy_map` (`privacy_id`, `uid`, `utype`, `value`)';
		$query .= ' select id, ' . $db->Quote($uid) . ', ' . $db->Quote($utype) . ', value';
		$query .= ' from `#__social_privacy`';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$state = $db->query();

		return $state;

	}


	private function getESFieldApp($jsField)
	{
		static $_cache = array();

		if (isset($_cache[$jsField->id])) {
			return $_cache[$jsField->id];
		}

		$appTable = ES::table('App');

		if ($this->userMapping) {
			// let try to get from user mapping

			$esFid = isset($this->userMapping[$jsField->id]) ? $this->userMapping[$jsField->id] : 0;

			if ($esFid) {
				if (!isset($_cache[$jsField->id])) {
					$appTable->load($esFid);

					if ($appTable->element && $appTable->id) {
						$obj = new stdClass();
						$obj->element = $appTable->element;
						$obj->appid = $appTable->id;

						$_cache[$jsField->id] = $obj;
					}
				}

				return $_cache[$jsField->id];
			}

		}

		// lets use the old method.
		$fieldElement = $this->mapping[$jsField->type];

		// js gender fields might be a select dropdown.
		if ($fieldElement == 'dropdown' && strpos(strtolower($jsField->fieldcode) , 'gender') !== false) {
			$fieldElement = 'gender';
		}

		if ($fieldElement == 'datetime' && (strpos(strtolower($jsField->fieldcode) , 'birthday') !== false || strpos(strtolower($jsField->fieldcode) , 'birthdate') !== false)) {
			$fieldElement = 'birthday';
		}

		$appTable->loadByElement($fieldElement , SOCIAL_TYPE_USER , SOCIAL_APPS_TYPE_FIELDS);

		if (! $appTable->id) {
			//lets fall back to textbox
			$fieldElement = 'textbox';
		}

		$obj = new stdClass();
		$obj->element = $fieldElement;
		$obj->appid = $appTable->id;

		$_cache[$jsField->id] = $obj;

		return $_cache[$jsField->id];
	}


	private function processField($stepId, $jsField, $esProfileId)
	{
		static $jsAddressFieldId = null;

		$db = ES::db();
		$sql = $db->sql();

		// Load profile
		$profile = ES::table('Profile');
		$profile->load($esProfileId);

		// Get workflow for this profile
		$workflow = $profile->getWorkflow();

		// // --------------------------------------------------------------

		// $fieldElement = $this->mapping[$jsField->type];

		// // js gender fields might be a select dropdown.
		// if($fieldElement == 'dropdown' && strpos(strtolower($jsField->fieldcode) , 'gender') !== false)
		// {
		// 	$fieldElement = 'gender';
		// }

		// $appTable 		= ES::table('App');
		// $appTable->loadByElement($fieldElement , SOCIAL_TYPE_USER , SOCIAL_APPS_TYPE_FIELDS);

		// if(! $appTable->id)
		// {
		// 	//lets fall back to textbox
		// 	$fieldElement = 'textbox';
		// }

		// // --------------------------------------------------------------

		if ($jsField->type == 'text' && (strpos(strtolower($jsField->fieldcode) , 'givenname') !== false || strpos(strtolower($jsField->fieldcode) , 'familyname') !== false)) {

			// this means this is a givenname / familyname mapping to default joomla_fullname field.
			// there is no need to create this field.

			$query = 'select `id` from `#__social_fields`';
			$query .= ' where `unique_key` = ' . $db->Quote('JOOMLA_FULLNAME');
			$query .= ' and `step_id` = (select `id` from `#__social_fields_steps` where `workflow_id` = ' . $db->Quote($workflow->id) ;
			$query .= '						and `type` = ' . $db->Quote(SOCIAL_TYPE_PROFILES);
			$query .= '						order by `id` ASC limit 1)';

			$sql->clear();
			$sql->raw($query);
			$db->setQuery($sql);

			$jfullnameField = $db->loadResult();

			if ($jfullnameField) {
				// log into migrator table
				$this->log('fields', $jsField->id, $jfullnameField);
				return $jfullnameField;
			}
		}

		//map address
		if ((($jsField->type == 'text' || $jsField->type == 'select') && strtolower($jsField->fieldcode) == 'field_state')
		||  ($jsField->type == 'text' && strtolower($jsField->fieldcode) == 'field_city')
		||  ($jsField->type == 'text' && strtolower($jsField->fieldcode) == 'field_zip')
		||  (($jsField->type == 'textarea' || $jsField->type == 'text') && (strtolower($jsField->fieldcode) == 'field_address' || strtolower($jsField->fieldcode) == 'field_street'))
		||  (($jsField->type == 'country' || $jsField->type == 'select') && strtolower($jsField->fieldcode) == 'field_country')) {

			// this means this is a givenname / familyname mapping to default joomla_fullname field.
			// there is no need to create this field.

			if (! $jsAddressFieldId) {

				$query = 'select `id` from `#__social_fields`';
				$query .= ' where (`unique_key` LIKE ' . $db->Quote('%ADDRESS%');
				$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_ZIP');
				$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_STATE');
				$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_CITY');
				$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_COUNTRY');
				$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_STREET') . ')';
				$query .= ' and `step_id` IN (select `id` from `#__social_fields_steps` where `workflow_id` = ' . $db->Quote($workflow->id) ;
				$query .= '                             and `type` = ' . $db->Quote(SOCIAL_TYPE_PROFILES) . ')';
				$query .= ' order by `id` DESC limit 1';

				$sql->clear();
				$sql->raw($query);
				$db->setQuery($sql);

				$jsAddressFieldId = $db->loadResult();
			}

			if ($jsAddressFieldId) {
				// log into migrator table
				$this->log('fields', $jsField->id, $jsAddressFieldId);
				return $jsAddressFieldId;
			}
		}


		$esFieldApp = $this->getESFieldApp($jsField);

		$fieldElement = $esFieldApp->element;
		$appId = $esFieldApp->appid;


		$fieldTable = ES::table('Field');
		// $fieldTable->bind($field);

		// Ensure that the main items are being JText correctly.
		$uniqueKey = ($jsField->fieldcode) ? $jsField->fieldcode : '';
		if (empty($uniqueKey)) {
			$uniqueKey = str_replace(' ', '_', $jsField->name);
			$uniqueKey = 'FIELD_' . $uniqueKey;
			$uniqueKey = strtoupper($uniqueKey);
		}

		//overide gender field code to match es gender unique_key
		if ($uniqueKey == 'FIELD_GENDER') {
			$uniqueKey = 'GENDER';
		}

		$fieldTable->title = $jsField->name;
		$fieldTable->description = $jsField->tips;
		$fieldTable->default = '';
		$fieldTable->unique_key = $uniqueKey;

		// Set the app id.
		$fieldTable->app_id = $appId;
		$fieldTable->step_id = $stepId;
		$fieldTable->state = $jsField->published;
		$fieldTable->searchable = ($fieldElement == 'header') ? 0 : $jsField->searchable;
		$fieldTable->required = $jsField->required;

		$fieldTable->visible_registration = ($jsField->registration) ? 1 : 0 ;
		$fieldTable->visible_edit = 1;
		$fieldTable->visible_display = $jsField->visible;
		$fieldTable->ordering = $jsField->ordering;

		$fieldTable->display_title = 1;
		$fieldTable->display_description = 1;

		// need to get the default params from ES fields.
		$params = array();
		switch ($fieldElement)
		{
			case 'country':
				$params['select_type'] = 'dropdown';
				break;

			case 'multilist':
				$params['multiple'] = 1;
				break;

			case 'textbox':

				if (!$jsField->min && !$jsField->max) {
					//this means the setting was a custimized one. lets get from the fields params;
					if ($jsField->params) {
						$jsParams = ES::json()->decode($jsField->params);
						$jsField->min = $jsParams->min_char;
						$jsField->max = $jsParams->max_char;
					}
				}

				$params['min'] = ($jsField->min) ? $jsField->min : "1";
				$params['max'] = ($jsField->max) ? $jsField->max : "255";

				break;

			case 'textarea':

				if (!$jsField->min && !$jsField->max) {
					//this means the setting was a custimized one. lets get from the fields params;
					$jsParams = ES::json()->decode($jsField->params);
					$jsField->min = $jsParams->min_char;
					$jsField->max = $jsParams->max_char;
				}

				$params['placeholder'] = $jsField->tips;
				$params['min'] = ($jsField->min) ? $jsField->min : "1";
				$params['max'] = ($jsField->max) ? $jsField->max : "255";

				break;

			case 'birthday':

				$params['calendar'] = 1;
				$params['form_format'] = 1;

				break;

			case 'url':

				$params['placeholder'] = $jsField->tips;
				$params['linkable'] = 1;
				$params['nofollow'] = 1;
				$params['target'] = "_blank";

				break;

			case 'email':

				$params['disallowed'] = "";
				$params['forbidden'] = "";

				break;

			case 'datetime':

				$params['calendar'] = ($jsField->type == 'time') ? 0 : 1;
				$params['form_format'] = 1;
				break;

			default:
				break;

		}

		$fieldTable->params = ($params) ? ES::json()->encode($params) : '';

		// Store the field item.
		$state = $fieldTable->store();

		if (! $state) {
			return false;
		}

		$newFieldId = $fieldTable->id;

		//lets check if this field is a dropdown list / multilist/ checkbox or radio button.
		$hasOptions = array(
						'dropdown',
						'checkbox',
						'multilist',
						'gender',
						'country'
						);

		if (in_array($fieldElement, $hasOptions) && $jsField->options) {
			$jsOptions = explode("\n", $jsField->options);

			//remove empty value elements
			$jsOptions = array_diff($jsOptions, array(''));

			// $valueKey 	= str_replace('FIELD_', '', $jsField->fieldcode);
			// $valueKey 	= strtolower($valueKey);

			$valueKey  = 'items';

			//$valueTitle = $jsField->name;

			$insert = 'insert into `#__social_fields_options` (`parent_id`, `key`, `title`, `value`) values ';

			$count = 1;
			foreach ($jsOptions as $jsOption) {
				$insert .= '(' . $db->Quote($newFieldId) . ',' . $db->Quote($valueKey) . ',' . $db->Quote($jsOption) . ',' . $db->Quote($jsOption) . ')';
				if ($count < count($jsOptions)) {
					$insert .= ',';
					$count++;
				}
			}

			// now let do the insert
			$sql->clear();
			$sql->raw($insert);

			$db->setQuery($sql);
			$db->query();
		}


		// log into migrator table
		$this->log('fields', $jsField->id, $fieldTable->id);

		return $fieldTable->id;
	}

	/**
	 * Method to create a default workflow used in user profile
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function createWorkflow($profileId, $jsProfileName = '')
	{
		if (! $jsProfileName) {
			$jsProfileName = 'JomSocial default workflow';
		}

		$workflow = ES::table('Workflow');
		$workflow->title = $jsProfileName;
		$workflow->description = $jsProfileName;
		$workflow->type = SOCIAL_TYPE_USER;
		$workflow->store();

		$workflowId = $workflow->id;

		// now we need to associate this profile with this newly created worflow.
		$workflowMap = ES::table('WorkflowMap');
		$workflowMap->uid = $profileId;
		$workflowMap->workflow_id = $workflowId;
		$workflowMap->type = SOCIAL_TYPE_USER;
		$workflowMap->store();

		return $workflowId;
	}

	/**
	 * Method to create a default user profile
	 *
	 * @since	1.2
	 * @access	private
	 */
	private function createDefaultItems($profileId, $jsProfileName = '')
	{
		// we need to create workflow data as well.
		$workflowMap = ES::table('WorkflowMap');
		$workflowMap->load(array('uid' => $profileId, 'type' => SOCIAL_TYPE_USER));

		$workflowId = 0;

		if ($workflowMap->id) {
			$workflowId = $workflowMap->workflow_id;
		} else {
			// there is no existing workflow for this profile.
			// lets create one.
			$workflowId = $this->createWorkflow($profileId, $jsProfileName);
		}

		// Read the default profile json file first.
		$path = SOCIAL_ADMIN_DEFAULTS . '/fields/profile_migrator.json';
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
			$stepTable->uid = $profileId;
			$stepTable->type = SOCIAL_TYPE_PROFILES;
			$stepTable->workflow_id = $workflowId;

			// Try to store the default steps.
			$state = $stepTable->store();

			$newStepIds[] = $stepTable->id;

			// Now we need to create all the fields that are in the current step
			if ($step->fields && $state) {

				foreach ($step->fields as $field) {
					$appTable = ES::table('App');
					$appTable->loadByElement($field->element , SOCIAL_TYPE_USER , SOCIAL_APPS_TYPE_FIELDS);

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
					$fieldTable->searchable = isset($field->searchable) ? $field->searchable : SOCIAL_STATE_PUBLISHED;

					// Set this to be searchable by default.
					$fieldTable->required = isset($field->required) ? $field->required : SOCIAL_STATE_PUBLISHED;

					// // Set this to be searchable by default.
					// $fieldTable->required = isset($field->required) ? $field->required : SOCIAL_STATE_PUBLISHED;

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


	private function processProfileItem($jsProfile)
	{
		$db = ES::db();
		$config = ES::config();

		$newProfile = new stdClass();

		// default setting
		$data = array();
		$data['delete_account'] = "0";
		$data['theme'] = $config->get('theme.site', 'wireframe');
		$data['registration'] = ($jsProfile->approvals) ? "approvals" : "login";
		$data['email'] = array("users" => "1", "moderators" => "1");

		$newProfile->title = $jsProfile->name;
		$newProfile->alias = '';
		$newProfile->description = $jsProfile->description;
		$newProfile->gid = ES::json()->encode(array("2"));
		$newProfile->default = '';
		$newProfile->default_avatar = '';
		$newProfile->created = $jsProfile->created;
		$newProfile->state = ($jsProfile->published) ? "1" : "0";
		$newProfile->params = ES::json()->encode($data);
		$newProfile->registration = 0; // for profiles that migrated from JS, we dont want to appear in registration form.
		$newProfile->ordering = $this->profileLastOrdering() + 1;

		$db->insertObject('#__social_profiles', $newProfile, 'id');
		$newId = $db->insertID();

		$tbl = ES::table('Profile');
		$tbl->load($newId);

		// Filter the name to ensure that it is a valid permalink
		$permalink 	= JFilterOutput::stringURLSafe($jsProfile->name);
		$tbl->alias = $tbl->id . ':' . $permalink;
		$tbl->store();

		// log records
		$this->log('profiles', $jsProfile->id, $newId);

		return $newId;
	}

	private function removeAdminSegment($url = '')
	{
		if ($url) {
			$url = '/' . ltrim($url , '/');
			$url = str_replace('/administrator/', '/', $url);
		}

		return $url;
	}

	private function profileLastOrdering()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select max(`ordering`) from `#__social_profiles`';

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadResult();

		return (empty($result)) ? 0 : $result;
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
