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

// Import necessary library
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class SocialFieldsMarketplaceImage extends SocialFieldItem
{
	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get error.
		$error = $registration->getErrors($this->inputName);
		$access = $this->my->getAccess();

		$this->set('maxFileSize', $access->get('photos.uploader.maxsize') . 'M');
		$this->set('error', $error);
		$this->set('isEdit', false);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post, &$registration)
	{
		return $this->onValidate($post);
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onAdminEditValidate(&$post, &$registration)
	{
		return $this->onValidate($post);
	}

	/**
	 * Validation when creating new listing
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onValidate(&$post)
	{
		$value = json_decode($post[$this->inputName]);

		if ($value->photoCount < 1) {
			$this->setError(JText::_('COM_ES_MARKETPLACE_PHOTO_NOTE_MESSAGE'));
			return false;
		}

		return true;
	}

	/**
	 * Once a user registration is completed, the field should automatically
	 * move the temporary file into the user's folder if required.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onRegisterAfterSave(&$post, $listing)
	{
		$photosData = json_decode($post[$this->inputName]);

		// Copy the files over
		if (isset($photosData->path)) {
			$this->savePhotos($listing, $photosData->path);
		}

		return true;
	}

	public function onEditAfterSave(&$post, $listing)
	{
		$photosData = json_decode($post[$this->inputName]);

		// Checked if there is a removed photo
		if (!empty($photosData->removed)) {
			$photoIds = explode(",", $photosData->removed);

			foreach ($photoIds as $photoId) {
				$photo = ES::table('Photo');
				$photo->load($photoId);

				$photo->delete();
			}
		}

		// Copy the files over
		if (isset($photosData->path)) {
			$this->savePhotos($listing, $photosData->path);
		}

		return true;
	}

	public function savePhotos($listing, $photos)
	{
		$listing->savePhotos($photos);
	}

	/**
	 * Displays the field form when user is being edited.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onEdit(&$post, &$listing, $errors)
	{
		$photos = $listing->getPhotos(true);

		$error = $this->getError($errors);
		$access = $this->my->getAccess();

		$this->set('maxFileSize', $access->get('photos.uploader.maxsize') . 'M');
		$this->set('listing', $listing);
		$this->set('error', $error);
		$this->set('isEdit', true);
		$this->set('photos', $photos);

		return $this->display();
	}

	/**
	 * Determines whether there are errors during editing process.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEditValidate(&$post, &$listing)
	{
		if (!$listing->hasPhotos() && $this->isRequired() && empty($post[$this->inputName])) {
			$this->setError(JText::_('PLG_FIELDS_FILE_VALIDATION_REQUIRED_TO_UPLOAD'));
			return false;
		}

		return true;
	}

	/**
	 * Renders the preview of the file when object's item is being viewed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onDisplay($object)
	{
		return;
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onFieldCheck($object)
	{
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
		return true;
	}
}
