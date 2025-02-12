<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Include the fields library
ES::import('admin:/includes/fields/dependencies');

require_once(dirname(__FILE__) . '/helper.php');

class SocialFieldsUserAvatar extends SocialFieldItem
{
	/**
	 * Performs the file uploading here when the user selects their profile picture.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload()
	{
		// Get the file
		$tmp = $_FILES[$this->inputName];

		$file = array();
		foreach ($tmp as $k => $v) {
			$file[$k] = $v['file'];
		}

		// Check if it is a valid file
		if (empty($file['tmp_name'])) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_AVATAR_ERROR_INVALID_FILE'));
		}

		$categoryId = $this->input->get('categoryId', 0, 'int');

		// Get user access
		$access = ES::access($categoryId, SOCIAL_TYPE_PROFILES);

		// We need to perform sanity checking here
		$options = array('name' => $this->inputName, 'maxsize' => $access->get('photos.uploader.maxsize') . 'M', 'multiple' => true);

		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialResponse) {
			return $this->ajax->reject($file->message);
		}

		// Copy this to temporary location first
		$tmpPath = SocialFieldsUserAvatarHelper::getStoragePath($this->inputName);

		// Load up the image library so we can get the appropriate extension
		$image = ES::image();
		$image->load($file['tmp_name']);

		// Get the extension
		$tmpName = md5($file['name'] . $this->inputName . ES::date()->toMySQL()) . $image->getExtension();

		// Must save the image using this method to fix orientation of vertical image. #1183
		// $image->rotate(0);
		// The above fix seems no longer work and created another issue. will comment it out for now.
		$state = $image->save($tmpPath . '/' . $tmpName);

		if (!$state) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_AVATAR_ERROR_UNABLE_TO_MOVE_FILE'));
		}

		$tmpUri = SocialFieldsUserAvatarHelper::getStorageURI($this->inputName);

		return $this->ajax->resolve($file['name'], $tmpUri . '/' . $tmpName, $tmpName);
	}

	/**
	 * Allows caller to set the avatar based on the default avatars
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function loadDefault()
	{
		// Get the avatar id
		$id = $this->input->get('avatarId', 0, 'int');

		if (!$id) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_AVATAR_ERROR_RETRIEVING_AVATAR'));
		}

		$default = ES::table('defaultavatar');
		$default->load($id);

		$uri = $default->getSource(SOCIAL_AVATAR_SQUARE, true);

		return $this->ajax->resolve($uri);
	}

}
