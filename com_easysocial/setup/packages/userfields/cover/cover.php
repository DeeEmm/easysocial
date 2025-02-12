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

ES::import('admin:/includes/fields/dependencies');

require_once(__DIR__ . '/helper.php');

class SocialFieldsUserCover extends SocialFieldItem
{
	/**
	 * Displays the cover form
	 *
	 * @since	2.0.18
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get the default cover picture
		$value = ES::getDefaultCover($this->group);
		$defaultCover = $value;
		$categoryId = 0;

		if (isset($registration->profile_id)) {
			$categoryId = $registration->profile_id;
		}

		if (isset($registration->uid) && !$categoryId) {
			$categoryId = $registration->uid;
		}

		$this->set('value', $value);
		$this->set('defaultCover', $defaultCover);
		$this->set('hasCover', 0);
		$this->set('categoryId', $categoryId);
		$this->set('newRegistration', true);

		// Get registration error
		$error 	= $registration->getErrors($this->inputName);

		// Set error
		$this->set('error', $error);

		// Display the output.
		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 *
	 */
	public function onRegisterValidate(&$post, &$registration)
	{
		$cover = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';
		$obj = ES::makeObject($cover);

		if ($this->isRequired() && (empty($cover) || empty($obj->data))) {
			$this->setError(JText::_('PLG_FIELDS_COVER_VALIDATION_REQUIRED'));

			return false;
		}

		return true;
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

		return $this->saveCover($post, $user->id, $this->group, $deleteTmpFolder, true);
	}

	/**
	 * Processes before the user account is created when user signs in with oauth.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onRegisterOAuthAfterSave(&$data, &$oauthClient, SocialUser &$user, $import)
	{
		if (!$import) {
			return;
		}

		$cover = isset($data['cover']) ? $data['cover'] : '';

		// If cover is not provided, skip this.
		if (!$cover) {
			return;
		}

		// Get the cover URL
		$coverUrl = $cover->url;

		// Get the session object.
		$uid = SocialFieldsUserCoverHelper::genUniqueId($this->inputName);

		// Get the user object.
		$user = ES::user();

		// Store the cover internally first.
		$tmpPath = SOCIAL_TMP . '/' . $uid . '_cover';
		$tmpFile = $tmpPath . '/' . $uid;

		// Now we need to get the image data.
		$connector = ES::connector($coverUrl);
		$contents = $connector
						->execute()
						->getResult();

		jimport('joomla.filesystem.file');

		if (!JFile::write($tmpFile, $contents)) {
			return;
		}

		// Ensure that the image is valid.
		if (!SocialFieldsUserCoverHelper::isValid($tmpFile)) {

			JFile::delete($tmpFile);

			return;
		}

		// Create the default album for this cover.
		$album = SocialFieldsUserCoverHelper::getDefaultAlbum($user->id);

		// Once the album is created, create the photo object.
		$photo = SocialFieldsUserCoverHelper::createPhotoObject($user->id, SOCIAL_TYPE_USER, $album->id, $data['oauth_id'], true);

		// Set the new album with the photo as the cover.
		$album->cover_id = $photo->id;
		$album->store();

		// Generates a unique name for this image.
		$name = md5($data['oauth_id'] . $this->inputName . ES::date()->toMySQL());

		// Load our own image library
		$image = ES::image();

		// Load up the file.
		$image->load($tmpFile, $name);

		// Load up photos library
		$photos = ES::get('Photos', $image);

		$storage = $photos->getStoragePath($album->id, $photo->id);

		// Create avatars
		$sizes = $photos->create($storage);

		foreach ($sizes as $size => $path) {
			// Now we will need to store the meta for the photo.
			$meta = SocialFieldsUserCoverHelper::createPhotoMeta($photo, $size, $storage . '/' . $path);
		}

		// Once all is done, we just need to update the cover table so the user
		// will start using this cover now.
		$coverTable = ES::table('Cover');
		$state = $coverTable->load(array('uid' => $user->id, 'type' => SOCIAL_TYPE_USER));

		// User does not have a cover.
		if (!$state) {
			$coverTable->uid = $user->id;
			$coverTable->type = SOCIAL_TYPE_USER;
			$coverTable->y = $cover->offset_y;
		}

		// Set the cover to pull from photo
		$coverTable->setPhotoAsCover($photo->id);

		// Save the cover.
		$coverTable->store();

		// Once everything is created, delete the temporary file
		JFile::delete($tmpFile);
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since	2.0.18
	 * @access	public
	 */
	public function onEdit(&$post, &$node, $errors)
	{
		$hasCover = $node->hasCover();
		$value = $node->getCover();
		$position = $node->getCoverPosition();
		$categoryId = 0;

		if (isset($node->profile_id)) {
			$categoryId = $node->profile_id;
		}

		if (isset($node->category_id)) {
			$categoryId = $node->category_id;
		}

		// Need to get the category id from the URL query string manually if edit new clusters from backend
		// since backend new cluster creation page doesn't store the session data from this table #__social_step_sessions
		// So unable to use the same way to get which category user select from this "onRegister" method
		if (!isset($node->profile_id) && !isset($node->category_id) && !$categoryId) {
			$queryCatId = $this->input->get('category_id', 0, 'int');

			if ($queryCatId) {
				$categoryId = $queryCatId;
			}
		}

		// If there is a post data, then use the post data
		if (!empty($post[$this->inputName])) {
			$obj = ES::makeObject($post[$this->inputName]);

			if (!empty($obj->data)) {
				$this->set('coverData', $this->escape($obj->data));

				$data = ES::makeObject($obj->data);

				$value = $data->large->uri;

				$hasCover = true;
			}

			if (!empty($obj->position)) {
				$this->set('coverPosition', $this->escape($obj->position));

				$data = ES::makeObject($obj->position);

				if (isset($data->x) && isset($data->y)) {
					$position = $data->x * 100 . '% ' . $data->y * 100 . '%';
				}
			}
		}

		// If the user doesn't have a cover, get the default cover for them
		$defaultCover = ES::getDefaultCover($this->group);

		if (!$hasCover) {
			$value = $defaultCover;
		}

		$error = $this->getError($errors);

		// Set the value
		$this->set('value', $value);
		$this->set('position', $position);
		$this->set('error', $error);
		$this->set('hasCover', $hasCover);
		$this->set('defaultCover', $defaultCover);
		$this->set('categoryId', $categoryId);
		$this->set('newRegistration', false);

		return $this->display();
	}

	public function onAdminEditValidate()
	{
		// Admin shouldn't need to validate
		return true;
	}

	public function onEditValidate(&$post, &$user)
	{
		$cover 	= !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		if ($this->isRequired() && !$user->hasCover()) {
			$obj = ES::makeObject($cover);

			if (empty($obj->data)) {
				$this->setError(JText::_('PLG_FIELDS_COVER_VALIDATION_REQUIRED'));

				return false;
			}
		}

		return true;
	}

	/**
	 * This is executed once the user profile is saved
	 *
	 * @since	2.0.18
	 * @access	public
	 */
	public function onEditAfterSave(&$post, &$user)
	{
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

		return $this->saveCover($post, $user->id, $this->group, $deleteTmpFolder);
	}

	/**
	 * Saves the profile cover
	 *
	 * @since	2.0.18
	 * @access	public
	 */
	public function saveCover(&$post, $uid, $type, $deleteTmpFolder = true, $isRegister = false)
	{
		$coverData = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		unset($post[$this->inputName]);

		if (empty($coverData)) {
			return true;
		}

		$coverData = ES::makeObject($coverData);

		// Get the cover table.
		$cover = ES::table('Cover');

		// Try to load existing cover
		$state = $cover->load(array('uid' => $uid, 'type' => $type));

		// If no existing cover, then we set the uid and type.
		if (!$state) {
			$cover->uid = $uid;
			$cover->type = $type;
		}

		// If both data does not exist, then we don't proceed to store the data.
		if (empty($coverData->data) && empty($coverData->position)) {
			return true;
		}

		if (!empty($coverData->data)) {

			if ($coverData->data === 'delete') {
				$cover->delete();
				return true;
			}

			// Construct the path to where the photo is temporarily uploaded.
			$tmpPath = SocialFieldsUserCoverHelper::getPath($this->inputName);

			$coverObj = ES::makeObject($coverData->data);

			// Get the cover album.
			$album = SocialFieldsUserCoverHelper::getDefaultAlbum($uid, $type);

			// Create the photo object.
			$photo = SocialFieldsUserCoverHelper::createPhotoObject($uid, $type, $album->id, $coverObj->original->title, false);

			// If there are no cover set for this album, set it as cover.
			if (empty($album->cover_id)) {
				$album->cover_id = $photo->id;
				$album->store();
			}

			// Construct the path to where the photo is temporarily uploaded.
			$tmpPath = SocialFieldsUserCoverHelper::getPath($this->inputName);
			$originalPath = $tmpPath . '/' . ES::makeObject($coverData->data)->original->path;

			// Set photo size
			$photo->total_size = filesize($originalPath);
			$photo->store();

			// Get the supposed path of where the cover should be
			// Instead of doing SocialPhotos::getStoragePath, I copied the logic from there but only to create the folders up until albumId section.
			// We do not want JPATH_ROOT to be included in the $storage variable
			$storage = '/' . ES::cleanPath($this->config->get('photos.storage.container'));
			ES::makeFolder(JPATH_ROOT . $storage);

			$storage .= '/' . $album->id;
			ES::makeFolder(JPATH_ROOT . $storage);

			$storage .= '/' . $photo->id;
			ES::makeFolder(JPATH_ROOT . $storage);

			// Copy the photo from the temporary path to the storage folder.
			$state = JFolder::copy($tmpPath, JPATH_ROOT . $storage, '', true);

			// If cannot copy out the photo, then don't proceed
			if ($state !== true) {
				$this->setError(JText::_('PLG_FIELDS_COVER_ERROR_UNABLE_TO_MOVE_FILE'));
				return false;
			}

			// Create the photo meta for each available sizes.
			foreach ($coverObj as $key => $value) {
				SocialFieldsUserCoverHelper::createPhotoMeta($photo, $key, $storage . '/' . $value->file);
			}

			// Set the uploaded photo as cover for this user.
			$cover->setPhotoAsCover($photo->id);
		}

		// Set the position of the cover if available.
		if (!empty($coverData->position)) {
			$position = ES::makeObject($coverData->position);

			if (isset($position->x)) {
				$cover->x = $position->x;
			}

			if (isset($position->y)) {
				$cover->y = $position->y;
			}

			// If there is no cover data, we should not delete any temporary folders
			if (empty($coverData->data)) {
				$deleteTmpFolder = false;
			}
		}

		// Store the cover object
		$cover->store();

		// Delete the temporary folder.
		if ($deleteTmpFolder && $tmpPath) {
			JFolder::delete($tmpPath);
		}

		// Only add the Update Stream if this is not first register
		if (!$isRegister) {
			$photo = $cover->getPhoto();
			$photo->addPhotosStream('updateCover');
		}

		ES::storage()->syncUsage(ES::user()->id);

		// And we're done.
		return true;
	}

	/**
	 * Assigned the appropriate key to retrieve from the OAuth client
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onOAuthGetMetaFields(&$fields, &$client)
	{
		if ($client->getType() == 'facebook') {
			$fields[] = 'cover';
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
		$importCover = $this->config->get('oauth.' . $type . '.registration.cover');

		// Facebook deprecated this #4489
		// if ($type == 'facebook' && $importCover && isset($details['cover'])) {
		// 	$cover = new stdClass();

		// 	$cover->url = $details['cover']['source'];
		// 	$cover->offset_y = $details['cover']['offset_y'];

		// 	$details['cover'] = $cover;
		// }

		if ($type == 'twitter' && $importCover && (isset($details['profile_background_image_url']) || isset($details['profile_banner_url']))) {
			$cover = new stdClass();

			if (isset($details['profile_banner_url'])) {
				$cover->url = $details['profile_banner_url'];
			} else {
				$cover->url = $details['profile_background_image_url'];
			}

			$cover->offset_y = 0;

			$details['cover'] = $cover;
		}
	}

	/**
	 * Copies the cover if the user is linking an existing account with an oauth account
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onLinkOAuthAfterSave(&$meta, &$client, &$user)
	{
		$importCover = $this->input->get('importCover', false, 'bool');

		if ($importCover) {
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
		if ($this->isRequired() && !$user->hasCover()) {
			$this->setError(JText::_('PLG_FIELDS_COVER_VALIDATION_REQUIRED'));
			return false;
		}

		return true;
	}

	/**
	 * Checks if this field is filled in.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onProfileCompleteCheck($user)
	{
		if (!ES::config()->get('user.completeprofile.strict') && !$this->isRequired()) {
			return true;
		}

		// There are cases where $user is changed to registration array instead of user object. #774
		if (!$user instanceof SocialUser) {
			return true;
		}

		$model = ES::model('Covers');
		$hasCover = $model->getPhoto($user->id, SOCIAL_TYPE_USER) ? true : false;

		return $hasCover;
	}
}
