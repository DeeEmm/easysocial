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

ES::import('fields:/user/avatar/ajax');

class SocialFieldsGroupAvatar extends SocialFieldsUserAvatar
{
	/**
	 * When user uploads a new photo for a group.
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function upload()
	{
		// Get the ajax library
		$ajax = ES::ajax();

		// Get the file
		$tmp = $_FILES[$this->inputName];

		$file = array();

		foreach ($tmp as $k => $v) {
			$file[$k] = $v['file'];
		}

		// Check if it is a valid file
		if (empty($file['tmp_name'])) {
			return $ajax->reject(JText::_('PLG_FIELDS_AVATAR_ERROR_INVALID_FILE'));
		}

		$categoryId = $this->input->get('categoryId', 0, 'int');

		// Get user access
		$access = ES::access($categoryId, SOCIAL_TYPE_CLUSTERS);

		// We need to perform sanity checking here
		$options = array('name' => $this->inputName, 'maxsize' => $access->get('photos.maxsize') . 'M', 'multiple' => true);

		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialResponse) {
			return $ajax->reject($file->message);
		}

		// Load up the image library so we can get the appropriate extension
		$image 	= ES::image();
		$image->load($file['tmp_name']);

		// Copy this to temporary location first
		$date = ES::date();

		$tmpPath = SocialFieldsUserAvatarHelper::getStoragePath($this->inputName);
		$tmpName = md5($file['name'] . $this->inputName . $date->toSql()) . $image->getExtension();

		$source = $file['tmp_name'];
		$target = $tmpPath . '/' . $tmpName;

		// Try to copy the file to the new location
		$state = JFile::copy($source, $target);

		if (!$state) {
			return $ajax->reject(JText::_('PLG_FIELDS_AVATAR_ERROR_UNABLE_TO_MOVE_FILE'));
		}

		// Generate a tempory url for the image
		$tmpUri = SocialFieldsUserAvatarHelper::getStorageURI($this->inputName);
		$uri = $tmpUri . '/' . $tmpName;

		return $ajax->resolve($file['name'], $uri, $tmpName);
	}
}
