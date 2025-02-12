<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once( SOCIAL_LIB . '/migrators/helpers/info.php' );

/**
 * DB layer for EasySocial.
 *
 * @since	1.1
 * @author	Sam <sam@stackideas.com>
 */
class SocialMigratorHelperKunena
{
	// component name, e.g. com_kunena
	var $name  			= null;

	// migtration steps
	var $steps 			= null;

	var $info  			= null;

	var $mapping 		= null;

	var $accessMapping 	= null;

	var $limit 		 	= null;

	var $userMapping  	= null;

	public function __construct()
	{
		$this->info     = new SocialMigratorHelperInfo();
		$this->name  	= 'com_kunena';

		$this->limit 	= 10; //10 items per cycle

		$this->steps[] 	= 'topic';
		$this->steps[] 	= 'replies';
		$this->steps[] 	= 'useravatar';

		$this->accessMapping = array(
			'0' 	=> SOCIAL_PRIVACY_PUBLIC,
			'1'		=> SOCIAL_PRIVACY_MEMBER,
			'10'	=> SOCIAL_PRIVACY_MEMBER,
			'20'	=> SOCIAL_PRIVACY_MEMBER,
			'30'	=> SOCIAL_PRIVACY_FRIEND,
			'40'	=> SOCIAL_PRIVACY_ONLY_ME
			);
	}

	public function getVersion()
	{
		if( !$this->isComponentExist() )
		{
			return false;
		}

		// check JomSocial version.
		$xml		= JPATH_ROOT . '/administrator/components/com_kunena/kunena.xml';

		$parser = ES::get( 'Parser' );
		$parser->load( $xml );

		$version	= $parser->xpath( 'version' );
		$version 	= (float) $version[0];

		return $version;
	}

	public function isInstalled()
	{
		$file 	= JPATH_ROOT . '/components/com_kunena/kunena.php';

		if(! JFile::exists( $file ) )
		{
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
		$obj->count   = 0;
		$obj->message = '';

		$file 	= JPATH_ROOT . '/components/com_kunena/kunena.php';

		if(! JFile::exists( $file ) )
		{
			$obj->message = 'Kunena not found in your site. Process aborted.';
			return $obj;
		}

		// @todo check if the db tables exists or not.


		// all pass. return object

		$obj->isvalid = true;
		$obj->count   = $this->getItemCount();

		return $obj;
	}

	public function setUserMapping( $maps )
	{
		// do nothing.
	}

	public function getItemCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$total = count( $this->steps );

		// kunena topics count
		$query = 'select count(1) as `total`';
		$query .= ' from `#__kunena_topics` as a';
		$query .= ' inner join `#__kunena_messages` as b on a.`id` = b.`thread` and a.`first_post_id` = b.`id`';
		$query .= ' where not exists ( ';
		$query .= '		select b.`oid` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'topic' ) . ' and b.`component` = ' . $db->Quote( $this->name );
		$query .= ' )';
		$query .= ' and b.`hold` = ' . $db->Quote( '0' );


		$sql->clear();
		$sql->raw( $query );
		$db->setQuery( $sql );
		$numTotal = $db->loadResult();
		$numTotal = ( $numTotal > 0 ) ? ceil( $numTotal / $this->limit ) : 0;
		$total = $total + $numTotal;

		//kunena replies
		$query = 'select count(1) as `total`';
		$query .= ' from `#__kunena_messages` as a';
		$query .= ' inner join `#__kunena_topics` as b on a.thread = b.id and a.id != b.first_post_id';

		$query .= ' where not exists ( ';
		$query .= '		select b.`oid` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'reply' ) . ' and b.`component` = ' . $db->Quote( $this->name );
		$query .= ' )';
		$query .= ' and a.`hold` = ' . $db->Quote( '0' );

		$sql->clear();
		$sql->raw( $query );
		$db->setQuery( $sql );
		$numTotal = $db->loadResult();
		$numTotal = ( $numTotal > 0 ) ? ceil( $numTotal / $this->limit ) : 0;
		$total = $total + $numTotal;


		// useravatar
		$query = 'select count(1) as `total`';
		$query .= ' from `#__kunena_users` as a';
		$query .= ' where not exists ( ';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote('avatar') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ' )';

		$sql->clear();
		$sql->raw( $query );
		$db->setQuery( $sql );
		$numTotal = $db->loadResult();
		$numTotal = ( $numTotal > 0 ) ? ceil( $numTotal / $this->limit ) : 0;
		$total = $total + $numTotal;


		return $total;
	}

	public function process( $item )
	{
		// @debug
		$obj = new stdClass();

		if( empty( $item ) )
		{
			$item = $this->steps[0];
		}

		$result = '';

		switch( $item )
		{
			case 'topic':
				$result = $this->processTopic();
				break;

			case 'replies':
				$result = $this->processReplies();
				break;

			case 'useravatar':
				$result = $this->processAvatar();
				break;

			default:
				break;
		}

		// this is the ending part to determine if the process is already ended or not.
		if( is_null( $result ) )
		{
			$keys 		= array_keys( $this->steps, $item);
			$curSteps 	= $keys[0];

			if( isset( $this->steps[ $curSteps + 1] ) )
			{
				$item = $this->steps[ $curSteps + 1];
			}
			else
			{
				$item = null;
			}

			$obj->continue = ( is_null( $item ) ) ? false : true ;
			$obj->item 	   = $item;
			$obj->message  = ( $obj->continue ) ? 'Checking for next item to migrate....' : 'No more item found.';

			return $obj;
		}


		$obj->continue = true;
		$obj->item 	   = $item;
		$obj->message  = implode( '<br />', $result->message );

		return $obj;
	}

	private function processAvatar()
	{ 
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__kunena_users` as a';
		$query .= ' where not exists ( ';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`userid` = b.`oid` and b.`element` = ' . $db->Quote('avatar' ) . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ' )';
		$query .= ' ORDER BY a.`userid` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$kunenaUsers = $db->loadObjectList();

		if (count($kunenaUsers) <= 0) {
			return null;
		}

		foreach ($kunenaUsers as $kunenaUser) {

			if (!$kunenaUser->avatar) {

				// no need to process further.
				$this->log('avatar', $kunenaUser->userid , $kunenaUser->userid);

				$this->info->setInfo('User ' . $kunenaUser->userid . ' is using default avatar. no migration is needed.');
				continue;
			}

			$userid = $kunenaUser->userid;

			// media/kunena/avatars/users/avatar***.jpg
			$imagePath = JPATH_ROOT . '/media/kunena/avatars/' . $kunenaUser->avatar;

			$tmp = explode('/', $imagePath);
			$filename = $tmp[count($tmp) - 1];

			if (!JFile::exists($imagePath)) {
				$this->log('avatar', $kunenaUser->userid, $kunenaUser->userid);

				$this->info->setInfo('User ' . $kunenaUser->userid . ' the avatar image file is not found from the server. Process aborted.');
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
			$album = $albumModel->getDefaultAlbum($userid, SOCIAL_TYPE_USER, SOCIAL_ALBUM_PROFILE_PHOTOS);

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
			$photosModel->pushPhotosOrdering($album->id, $photo->id);

			// Render photos library
			$photoLib = ES::get('Photos', $image);
			$storage = $photoLib->getStoragePath($album->id, $photo->id);
			$paths = $photoLib->create($storage);

			// Create metadata about the photos
			foreach($paths as $type => $fileName)
			{
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

			// add photo privacy
			$this->addItemPrivacy('photos.view', $photo->id, SOCIAL_TYPE_PHOTO, $kunenaUser->userid, '0');

			// log into mgirator
			$this->log('avatar', $kunenaUser->userid, $kunenaUser->userid);

			$this->info->setInfo('User avatar ' . $kunenaUser->userid . ' is now migrated into EasySocial.');

		}//end foreach

		return $this->info;

	}

	private function processTopic()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$query	= 'SELECT a.* from `#__kunena_topics` as a';
		$query .= ' inner join `#__kunena_messages` as b on a.`id` = b.`thread` and a.`first_post_id` = b.`id`';
		$query .= ' where not exists ( ';
		$query .= '		select b.`oid` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'topic' ) . ' and b.`component` = ' . $db->Quote( $this->name );
		$query .= ' )';
		$query .= ' and b.`hold` = ' . $db->Quote( '0' );
		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;


		$sql->raw( $query );
		$db->setQuery( $sql );


		$kPosts = $db->loadObjectList();

		if( count( $kPosts ) <= 0 )
		{
			return null;
		}

		foreach( $kPosts as $kItem )
		{
			// add stream.
			$this->addTopicStream( $kItem );

			// add log
			$this->log( 'topic', $kItem->id, $kItem->id );

			$this->info->setInfo( 'Kunena topic post with id \'' . $kItem->id . '\' processed succussfully.' );
		}

		return $this->info;
	}



	private function processReplies()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$query = 'select a.* from `#__kunena_messages` as a';
		$query .= ' inner join `#__kunena_topics` as b on a.`thread` = b.`id` and a.`id` != b.`first_post_id`';
		$query .= ' where not exists ( ';
		$query .= '		select b.`oid` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'reply' ) . ' and b.`component` = ' . $db->Quote( $this->name );
		$query .= ' )';
		$query .= ' and a.`hold` = ' . $db->Quote( '0' );

		// debug code
		// $query .= ' and a.id > 53348';

		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;


		$sql->raw( $query );
		$db->setQuery( $sql );


		$kPosts = $db->loadObjectList();

		if( count( $kPosts ) <= 0 )
		{
			return null;
		}

		foreach( $kPosts as $kItem )
		{
			// add stream.
			$this->addRepliesStream( $kItem );

			// add log
			$this->log( 'reply', $kItem->id, $kItem->id );

			$this->info->setInfo( 'Kunena reply post with id \'' . $kItem->id . '\' processed succussfully.' );
		}

		return $this->info;
	}


	private function addRepliesStream( $kItem )
	{
		$stream				= ES::stream();
		$streamTemplate		= $stream->getTemplate();

		// Set the actor.
		$streamTemplate->setActor( $kItem->userid, SOCIAL_TYPE_USER );

		// Set the context.
		$streamTemplate->setContext( $kItem->id , 'kunena' );

		// Set the verb.
		$streamTemplate->setVerb( 'reply' );

		// set stream content
		$streamTemplate->setContent( $kItem->subject );

		$streamTemplate->setAccess( 'core.view', SOCIAL_PRIVACY_PUBLIC );

		// set the stream creation date
		$date = ES::date( $kItem->time );
		$streamTemplate->setDate( $date->toMySQL() );

		// Create the stream data.
		$stream->add( $streamTemplate );
	}

	private function addTopicStream( $kItem )
	{
		$stream				= ES::stream();
		$streamTemplate		= $stream->getTemplate();

		// Set the actor.
		$streamTemplate->setActor( $kItem->first_post_userid, SOCIAL_TYPE_USER );

		// Set the context.
		$streamTemplate->setContext( $kItem->id , 'kunena' );

		// Set the verb.
		$streamTemplate->setVerb( 'create' );

		// set stream content
		$streamTemplate->setContent( $kItem->first_post_message );

		$streamTemplate->setAccess( 'core.view', SOCIAL_PRIVACY_PUBLIC );

		// set the stream creation date
		$date = ES::date( $kItem->first_post_time );
		$streamTemplate->setDate( $date->toMySQL() );

		// Create the stream data.
		$stream->add( $streamTemplate );
	}


	public function log( $element, $oriId, $newId )
	{
		$tbl = ES::table( 'Migrators' );

		$tbl->oid 		= $oriId;
		$tbl->element 	= $element;
		$tbl->component = $this->name;
		$tbl->uid 		= $newId;
		$tbl->created 	= ES::date()->toMySQL();

		$tbl->store();
	}

	private function addItemPrivacy($command, $esUid, $esUType, $kunenaUserId, $kunenaAccess)
	{
		static $defaultESPrivacy = array();

		$db = ES::db();
		$sql = $db->sql();

		if (!isset($defaultESPrivacy[$command])) {
			$db = ES::db();
			$sql = $db->sql();

			$commands = explode('.', $command);

			$element = $commands[0];
			$rule = $commands[1];

			$query = 'select `id`, `value` from `#__social_privacy`';
			$query .= ' where `type` = ' . $db->Quote($element);
			$query .= ' and `rule` = ' . $db->Quote($rule);

			$sql->raw($query);
			$db->setQuery($sql);

			$defaultESPrivacy[$command] = $db->loadObject();
		}

		$defaultPrivacy = $defaultESPrivacy[$command];

		$privacyValue = (isset($this->accessMapping[$kunenaAccess])) ? $this->accessMapping[$kunenaAccess] : $defaultPrivacy->value;

		$esPrivacyItem = ES::table('PrivacyItems');

		$esPrivacyItem->privacy_id = $defaultPrivacy->id;
		$esPrivacyItem->user_id = $kunenaUserId;
		$esPrivacyItem->uid = $esUid;
		$esPrivacyItem->type = $esUType;
		$esPrivacyItem->value = $privacyValue;

		$esPrivacyItem->store();
	}

}
