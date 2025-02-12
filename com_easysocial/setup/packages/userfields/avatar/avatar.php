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

ES::import('admin:/includes/fields/dependencies');

require_once(__DIR__ . '/helper.php');

class SocialFieldsUserAvatar extends SocialFieldItem
{
	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		$systemAvatar = $this->getSystemAvatar($registration);

		$avatars = array();
		$config = ES::config();

		$defaultAvatarId = '';
		$categoryId = 0;

		$avatarModel = ES::model('Avatars');

		// Load the default avatars
		if (isset($registration->profile_id)) {
			$avatars = $avatarModel->getDefaultAvatars($registration->profile_id);
			$categoryId = $registration->profile_id;
		}

		if (isset($registration->uid) && !$categoryId) {
			$categoryId = $registration->uid;
		}

		// Need to double check again if there profile id is 0 and do not have detect any avatars
		if (empty($avatars) && (isset($registration->profile_id) && !$registration->profile_id)) {

			$profileId = $this->config->get('registrations.mini.profile', 'default');

			if ($profileId === 'default') {
				$model = ES::model('Profiles');
				$profileId = $model->getDefaultProfile()->id;
			}

			$avatars = $avatarModel->getDefaultAvatars($profileId);
		}

		foreach($avatars as $avatar) {
			if ($avatar->default) {
				$systemAvatar = $avatar->getSource(SOCIAL_AVATAR_SQUARE);
				$defaultAvatarId = $avatar->id;
			}
		}

		// Set errors
		$error = $registration->getErrors($this->inputName);

		// Set the blank avatar
		$this->set('defaultAvatarId', $defaultAvatarId);
		$this->set('imageSource', $systemAvatar);
		$this->set('avatars', $avatars);
		$this->set('error', $error);
		$this->set('hasAvatar', false);
		$this->set('systemAvatar', $systemAvatar);
		$this->set('categoryId', $categoryId);
		$this->set('newRegistration', true);

		// Display the output.
		return $this->display();
	}

	/**
	 * Retrieve text avatar
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getSystemAvatar($registration)
	{
		$systemAvatar =  ES::getDefaultAvatar($this->group, SOCIAL_AVATAR_SQUARE);

		if ($this->group != SOCIAL_TYPE_USER || !$this->config->get('users.avatarUseName') || empty($registration->values)) {
			return $systemAvatar;
		}

		$profileId = $registration->profile_id;

		$profile = ES::table('Profile');
		$profile->load($profileId);

		$options['workflow_id'] = $profile->getWorkflow()->id;
		$options['group'] = SOCIAL_FIELDS_GROUP_USER;
		$options['element'] = 'joomla_fullname';

		// Get fields model
		$fieldsModel = ES::model('Fields');
		$field = $fieldsModel->getCustomFields($options);
		$nameFormat = $field[0]->getParams()->get('format');

		$textAvatar = ES::textavatar();

		// Try to get the first and last name if exists
		$registrationValues = json_decode($registration->values);

		// this happened when the username is at 2nd steps.
		// we should not process further. #4005
		if (!isset($registrationValues->username)) {
			return $systemAvatar;
		}

		$fullName = $registrationValues->username;
		$firstName = false;
		$lastName = false;

		// Generate based on fullname only if this field is visible on registration
		if ($field[0]->getParams()->get('visible_registration')) {
			$firstName = $registrationValues->first_name;
			$middleName = $registrationValues->middle_name;
			$lastName = $registrationValues->last_name;
		}

		// We need to get the correct initials
		if ($this->config->get('users.displayName') == 'realname' && $firstName && $lastName) {
			$firstName = explode(' ', $firstName);
			$lastName = explode(' ', $lastName);

			$fullName = $firstName[0] . ' ' . $lastName[0];

			// Respect the name format if it is last_middle_first or last_first
			if ($nameFormat == 2 || $nameFormat == 5) {
				$fullName = $lastName[0] . ' ' . $firstName[0];
			}
		}

		if ($fullName) {
			$systemAvatar = $textAvatar->getAvatar($fullName);
		}

		return $systemAvatar;
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post, &$registration)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$state 	= $this->validate($value);

		return $state;
	}

	/**
	 * Once a user registration is completed, the field should automatically
	 * move the temporary avatars into the user's folder if required.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterAfterSave(&$post, &$user)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		// If the user did not select an avatar, we should pre-select it for them.
		if ($value) {

			$deleteTmpFolder = true;

			// If the obj is event, we cant delete the tmp folder.
			// Because there is possibility this event has recurring event
			if ($user->getType() == 'event') {

				$hasRecurringFieldData = isset($post['hasRecurringFieldData']) && !empty($post['hasRecurringFieldData']) ? $post['hasRecurringFieldData'] : false;
				$isLastRecurringEvent = isset($post['isLastRecurringEvent']) && !empty($post['isLastRecurringEvent']) ? $post['isLastRecurringEvent'] : false;

				// check for the parent event whether got recurring event or not
				// if got, do not delete that tmp folder
				if (!$user->parent_id && $hasRecurringFieldData) {
					$deleteTmpFolder = false;
				}

				// If processing the recurring event don't delete the tmp folder first
				if ($hasRecurringFieldData && !$isLastRecurringEvent) {
					$deleteTmpFolder = false;
				}

				// Determine if that is last event then only delete those tmp folder
				if ($hasRecurringFieldData && $isLastRecurringEvent) {
					$deleteTmpFolder = true;
				}
			}

			$this->createAvatar($value, $user->id, false, true, true, $deleteTmpFolder);
		}

		$default = false;

		// If the user did not select a default avatar, or did not upload an avatar, check if there's a default avatar selected for them.
		if (isset($user->profile_id)) {
			$default = ES::model('Avatars')->getDefaultAvatars($user->profile_id, SOCIAL_TYPE_PROFILES, true);
		}

		if (!$value && $default) {

			$default = $default[0];

			$tmp = new stdClass();
			$tmp->type = 'gallery';
			$tmp->source = $default->id;
			$tmp->data = '';
			$tmp = ES::json()->encode($tmp);

			$this->createAvatar($tmp, $user->id, false, true, true);
		}

		unset($post[$this->inputName]);
	}


	/**
	 * Processes before the user account is created when user signs in with oauth.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterOAuthAfterSave(&$data, &$oauthClient, SocialUser &$user, $import)
	{
		if (!$import) {
			return;
		}

		// Let's see if avatarUrl is provided.
		if (!isset($data['avatar']) || empty($data['avatar'])) {
			return;
		}

		$avatarUrl = $data['avatar'];

		// Store the avatar internally.
		$key = md5($data['oauth_id'] . $data['username']);
		$tmpAvatarPath = SOCIAL_MEDIA . '/tmp/' . $key;
		$tmpAvatarFile = $tmpAvatarPath . '/' . $key;

		jimport('joomla.filesystem.folder');

		$folderExists = JFolder::exists($tmpAvatarPath);

		if (!$folderExists) {
			$state = JFolder::create($tmpAvatarPath);
		}

		$connector = ES::get('Connector');
		$connector->addUrl($avatarUrl);
		$connector->connect();

		$contents = $connector->getResult($avatarUrl);

		jimport('joomla.filesystem.file');

		if (!JFile::write($tmpAvatarFile, $contents)) {
			return;
		}

		$image = ES::image();
		$image->load($tmpAvatarFile);

		$avatar		= ES::avatar($image, $user->id, SOCIAL_TYPE_USER);

		// Check if there's a profile photos album that already exists.
		$albumModel	= ES::model('Albums');

		// Retrieve the user's default album
		$album		= $albumModel->getDefaultAlbum($user->id, SOCIAL_TYPE_USER, SOCIAL_ALBUM_PROFILE_PHOTOS);

		$photo = ES::table('Photo');
		$photo->uid = $user->id;
		$photo->user_id = $user->id;
		$photo->type = SOCIAL_TYPE_USER;
		$photo->album_id = $album->id;
		$photo->title = $user->getName();
		$photo->caption = JText::_('COM_EASYSOCIAL_PHOTO_IMPORTED_FROM_FACEBOOK');
		$photo->ordering = 0;
		$photo->assigned_date = ES::normalizeDateString('');

		// We need to set the photo state to "SOCIAL_PHOTOS_STATE_TMP"
		$photo->state = SOCIAL_PHOTOS_STATE_TMP;

		// Try to store the photo first
		$state = $photo->store();

		if (!$state) {
			$this->setError(JText::_('PLG_FIELDS_AVATAR_ERROR_CREATING_PHOTO_OBJECT'));
			return false;
		}

		// Push all the ordering of the photo down
		$photosModel = ES::model('photos');
		$photosModel->pushPhotosOrdering($album->id, $photo->id);

		// If album doesn't have a cover, set the current photo as the cover.
		if (!$album->hasCover()) {
			$album->cover_id 	= $photo->id;

			// Store the album
			$album->store();
		}

		// Get the photos library
		$photoLib 	= ES::get('Photos', $image);
		$storage   = $photoLib->getStoragePath($album->id, $photo->id);
		$paths 		= $photoLib->create($storage);

		// Create metadata about the photos
		foreach ($paths as $type => $fileName) {
			$meta 				= ES::table('PhotoMeta');
			$meta->photo_id		= $photo->id;
			$meta->group 		= SOCIAL_PHOTOS_META_PATH;
			$meta->property 	= $type;
			$meta->value		= $storage . '/' . $fileName;

			$meta->store();
		}

		// Synchronize Indexer
		$indexer 	= ES::get('Indexer');
		$template	= $indexer->getTemplate();
		$template->setContent($photo->title, $photo->caption);

		// $url 	= FRoute::photos(array('layout' => 'item', 'id' => $photo->getAlias()));
		$url 	= $photo->getPermalink();
		$url 	= '/' . ltrim($url, '/');
		$url 	= str_replace('/administrator/', '/', $url);

		$template->setSource($photo->id, SOCIAL_INDEXER_TYPE_PHOTOS, $photo->uid, $url);
		$template->setThumbnail($photo->getSource('thumbnail'));

		$indexer->index($template);

		$options = array();

		if ($user->state == SOCIAL_USER_STATE_PENDING) {
			$options['addstream'] = false;
		}

		// Create the avatars now
		$avatar->store($photo, $options, true);

		// Once we are done creating the avatar, delete the temporary folder.
		$state		= JFolder::delete($tmpAvatarPath);
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		$avatars = array();
		$defaultAvatarId = '';
		$categoryId = 0;

		$systemAvatar = ES::getDefaultAvatar($this->group, SOCIAL_AVATAR_SQUARE);

		// Load the default avatars
		if (isset($user->profile_id)) {
			$model = ES::model('Avatars');
			$avatars = $model->getDefaultAvatars($user->profile_id);
			$categoryId = $user->profile_id;

			foreach($avatars as $avatar) {
				if ($avatar->default) {
					$systemAvatar = $avatar->getSource(SOCIAL_AVATAR_SQUARE);
					$defaultAvatarId = $avatar->id;
				}
			}
		}

		if (isset($user->category_id)) {
			$categoryId = $user->category_id;
		}

		// Need to get the category id from the URL query string manually if edit new clusters from backend
		// since backend new cluster creation page doesn't store the session data from this table #__social_step_sessions
		// So unable to use the same way to get which category user select from this "onRegister" method
		if (!isset($user->profile_id) && !isset($user->category_id) && !$categoryId) {
			$queryCatId = $this->input->get('category_id', 0, 'int');

			if ($queryCatId) {
				$categoryId = $queryCatId;
			}
		}

		$imageSource = $user->hasAvatar() ? $user->getAvatar(SOCIAL_AVATAR_SQUARE) : '';

		// Set errors
		$error = $this->getError($errors);

		// Set the blank avatar
		$this->set('group', $this->group);
		$this->set('defaultAvatarId', $defaultAvatarId);
		$this->set('avatars', $avatars);
		$this->set('imageSource', $imageSource);
		$this->set('error', $error);
		$this->set('hasAvatar', $user->hasAvatar());
		$this->set('systemAvatar', $systemAvatar);
		$this->set('categoryId', $categoryId);
		$this->set('newRegistration', false);

		// Display the output.
		return $this->display();
	}

	/**
	 * Performs validation checks when a user edits their profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAdminEditValidate(&$post, &$user)
	{
		return true;
	}

	/**
	 * Triggered after a user is imported
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	public function onAdminAfterImport(&$data, &$user)
	{
		// Nothing to migrate
		if (!isset($data['avatar']) || !$data['avatar']) {
			return;
		}

		if (!$user || !isset($user->id) || !$user->id) {
			// invalid user object. skip
			return;
		}

		// We need to copy from the temporary import folder into the appropriate folder
		$source = SOCIAL_TMP . '/importer/' . $data['avatar'];

		// Ensure that the file exists before doing anything
		if (!JFile::exists($source)) {
			return;
		}

		$targetFolder = SocialFieldsUserAvatarHelper::getStoragePath($this->inputName, true);
		$destination = $targetFolder . '/' . $data['avatar'];

		$state = JFile::copy($source, $destination);

		if (!$state) {
			return false;
		}

		// Delete the source file after copying to the temporary location
		JFile::delete($source);

		$opts = array(
			'type' => 'upload',
			'path' => $data['avatar'],
			'name' => $data['avatar']
		);

		// Create the avatar
		$this->createAvatar($opts, $user->id);
	}

	/**
	 * Performs validation checks when a user edits their profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditValidate(&$post, &$user)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$state 	= $this->validate($value, $user);

		return $state;
	}

	/**
	 * Once a user edit is completed, the field should automatically
	 * move the temporary avatars into the user's folder if required.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditAfterSave(&$post, &$user)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		if (!empty($value)) {

			$deleteTmpFolder = true;

			// If the obj is event, we cant delete the tmp folder.
			// Because there is possibility this event has recurring event
			if ($user->getType() == 'event') {

				// determine if the use got set any schedule event or not
				$hasRecurringFieldData = isset($post['hasRecurringFieldData']) && !empty($post['hasRecurringFieldData']) ? $post['hasRecurringFieldData'] : false;

				// determine if process last one recurring event
				$isLastRecurringEvent = isset($post['isLastRecurringEvent']) && !empty($post['isLastRecurringEvent']) ? $post['isLastRecurringEvent'] : false;

				// determine whether this editing event need to apply for all the recurring event or only single event
				$applyRecurring = isset($post['applyRecurring']) && $post['applyRecurring'] ? $post['applyRecurring'] : false;

				// determine if this event is new or not
				$isNew = isset($post['isNew']) && $post['isNew'] ? $post['isNew'] : false;

				// only apply this if the event form has recurring data
				if ($hasRecurringFieldData) {

					if ($isNew && !$applyRecurring && !$isLastRecurringEvent) {
						$deleteTmpFolder = false;

					} elseif ($isNew && !$applyRecurring && $isLastRecurringEvent) {
						$deleteTmpFolder = true;

					} elseif ($applyRecurring && !$isLastRecurringEvent) {
						$deleteTmpFolder = false;

					} elseif ($applyRecurring && $isLastRecurringEvent) {
						$deleteTmpFolder = true;
					}
				}
			}

			$this->createAvatar($value, $user->id, true, empty($post['applyRecurring']), false, $deleteTmpFolder);
		}

		unset($post[$this->inputName]);
	}

	/**
	 * Performs validation checks when a user edits their profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function validate($value, $user = null)
	{
		if ((empty($user) || !$user->hasAvatar()) && $this->isRequired() && empty($value)) {
			$this->setError(JText::_('PLG_FIELDS_AVATAR_VALIDATION_EMPTY_PROFILE_PICTURE'));

			return false;
		}

		if (!empty($value)) {
			$value = ES::json()->decode($value);
			$sourceValue = isset($value->source) ? $value->source : '';

			// Trim of excess white space
			$sourceValue = trim($sourceValue);

			if ((empty($user) || !$user->hasAvatar()) && $this->isRequired() && empty($sourceValue)) {
				$this->setError(JText::_('PLG_FIELDS_AVATAR_VALIDATION_EMPTY_PROFILE_PICTURE'));

				return false;
			}
		}

		return true;
	}

	/**
	 * Allows caller to create avatar
	 *
	 * @since	1.0.0
	 * @access	public
	 */
	public function createAvatar($value, $uid, $createStream = true, $deleteImage = true, $isNewlyRegistered = false, $deleteTmpFolder = true)
	{
		$value = ES::makeObject($value);

		if (!empty($value->data)) {
			$value->data = ES::makeObject($value->data);
		}

		if (!isset($value->type)) {
			//skip.
			return true;
		}

		if ($value->type === 'remove') {
			$table = ES::table('avatar');
			$state = $table->load(array('uid' => $uid, 'type' => $this->group));

			if ($state) {
				$table->delete();

				if ($this->group == SOCIAL_TYPE_USER) {

					$user = ES::user($uid);

					// Prepare the dispatcher
					ES::apps()->load(SOCIAL_TYPE_USER);
					$dispatcher = ES::dispatcher();
					$args = array(&$user, &$table);

					// @trigger: onUserAvatarRemove
					$dispatcher->trigger(SOCIAL_TYPE_USER, 'onUserAvatarRemove', $args);
				}
			}

			return true;
		}

		if ($value->type === 'gallery') {
			$table = ES::table('avatar');
			$state = $table->load(array('uid' => $uid, 'type' => $this->group));

			if (!$state) {
				$table->uid = $uid;
				$table->type = $this->group;
			}

			$table->avatar_id = $value->source;

			$table->store();

			return true;
		}

		if ($value->type === 'upload') {

			$data = new stdClass();

			if (!empty($value->path)) {
				// Get the media tmp path. Pass false so that the path wont get overwritten
				$storagePath = SocialFieldsUserAvatarHelper::getStoragePath($this->inputName, false);
				$tmpPath = $storagePath . '/' . $value->path;

				$image = ES::image();
				$image->load($tmpPath);

				if (!$image->isValid()) {
					$this->setError(JText::_('PLG_FIELDS_AVATAR_ERROR_INVALID_FILE'));
					return false;
				}

				$avatar	= ES::avatar($image, $uid, $this->group);

				// Check if there's a profile photos album that already exists.
				$albumModel	= ES::model('Albums');

				// Retrieve the user's default album
				$album = $albumModel->getDefaultAlbum($uid, $this->group, SOCIAL_ALBUM_PROFILE_PHOTOS);

				$photo = ES::table('Photo');
				$photo->uid = $uid;
				$photo->type = $this->group;
				$photo->user_id = $this->group == SOCIAL_TYPE_USER ? $uid : ES::user()->id;
				$photo->album_id = $album->id;
				$photo->title = $value->name;
				$photo->caption = '';
				$photo->ordering = 0;
				$photo->assigned_date = ES::normalizeDateString('');
				$photo->total_size = filesize($tmpPath);

				// We need to set the photo state to "SOCIAL_PHOTOS_STATE_TMP"
				$photo->state = SOCIAL_PHOTOS_STATE_TMP;

				// Try to store the photo first
				$state = $photo->store();

				if (!$state) {
					$this->setError(JText::_('PLG_FIELDS_AVATAR_ERROR_CREATING_PHOTO_OBJECT'));
					return false;
				}

				// Push all the ordering of the photo down
				$photosModel = ES::model('photos');
				$photosModel->pushPhotosOrdering($album->id, $photo->id);

				// If album doesn't have a cover, set the current photo as the cover.
				if (!$album->hasCover()) {
					$album->cover_id = $photo->id;

					// Store the album
					$album->store();
				}

				// Get the photos library
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

				// Synchronize Indexer
				$indexer = ES::get('Indexer');
				$template = $indexer->getTemplate();
				$template->setContent($photo->title, $photo->caption);

				$url = $photo->getPermalink();
				$url = '/' . ltrim($url, '/');
				$url = str_replace('/administrator/', '/', $url);

				$template->setSource($photo->id, SOCIAL_INDEXER_TYPE_PHOTOS, $photo->uid, $url);
				$template->setThumbnail($photo->getSource('thumbnail'));

				$indexer->index($template);

				$tmpImagePath = false;

				// Crop the image to follow the avatar format. Get the dimensions from the request.
				if (!empty($value->data) && is_object($value->data)) {
					$width = $value->data->width;
					$height = $value->data->height;
					$top = $value->data->top;
					$left = $value->data->left;

					$tmpImagePath = $avatar->crop($top, $left, $width, $height);
				}

				$options = array();

				// Create the avatars now
				if (!$createStream) {
					$options = array( 'addstream' => false );
				}

				$tmpPath = dirname($tmpPath);

				$options['deleteimage'] = false;

				$avatar->store($photo, $options, $isNewlyRegistered);

				// Delete the temporary folder.
				if ($deleteTmpFolder) {
					JFolder::delete($tmpPath);

					// Now, it save to delete the image in the Joomla tmp folder
					if ($tmpImagePath) {
						JFile::delete($tmpImagePath);
					}
				}

				ES::storage()->syncUsage(ES::user()->id);
			}

			return true;
		}
	}

	/**
	 * Gets the meta data for avatar from the OAuth client
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onOAuthGetUserMeta(&$details, &$client)
	{
		$type = $client->getType();

		if (!isset($details['avatar']) && $this->config->get('oauth.' . $type . '.registration.avatar')) {
			$details['avatar'] = $client->getAvatar($details, 'original');
		}
	}

	/**
	 * Copies the avatar if the user is linking an existing account with an oauth account
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onLinkOAuthAfterSave(&$meta, &$client, &$user)
	{
		$importAvatar = $this->input->get('importAvatar', false, 'bool');

		if ($importAvatar) {
			return $this->onRegisterOAuthAfterSave($meta, $client, $user, true);
		}

		return;
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onFieldCheck($user)
	{
		return $this->validate($this->value, $user);
	}

	/**
	 * Checks if this field is filled in.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onProfileCompleteCheck($user)
	{
		if (!$this->config->get('user.completeprofile.strict') && !$this->isRequired()) {
			return true;
		}

		$hasAvatar = $user->hasAvatar() ? true : false;

		return $hasAvatar;
	}
}
