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

ES::import('fields:/user/cover/ajax');

class SocialFieldsPageCover extends SocialFieldsUserCover
{
	public function upload()
	{
		// Get the ajax library
		$ajax = ES::ajax();

		$tmp = $_FILES[$this->inputName];

		$file = array();

		foreach ($tmp as $k => $v) {
			$file[$k] = $v['file'];
		}

		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			return $ajax->reject(JText::_('PLG_FIELDS_COVER_VALIDATION_INVALID_IMAGE'));
		}

		$categoryId = $this->input->get('categoryId', 0, 'int');

		// Get category access
		$access = ES::access($categoryId, SOCIAL_TYPE_CLUSTERS);

		// We need to perform sanity checking here
		$options = array('name' => $this->inputName, 'maxsize' => $access->get('photos.maxsize') . 'M', 'multiple' => true);

		$uploader = ES::uploader($options);
		$file = $uploader->getFile(null, 'image');

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialResponse) {
			return $ajax->reject($file->message);
		}

		$result = $this->createCover($file , $this->inputName);

		return $ajax->resolve($result);
	}
}
