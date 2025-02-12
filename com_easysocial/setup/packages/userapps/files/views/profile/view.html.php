<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class FilesViewProfile extends SocialAppsView
{
	public function display($userId = null, $docType = null)
	{
		$this->setTitle('APP_FILES_APP_TITLE');

		$user = ES::user($userId);

		// // Check for the permission to view the files
		if (!$user->isViewer()) {
			return $this->redirect($user->getPermalink(false));
		}

		// Load up the explorer library.
		$explorer = ES::explorer($user->id, SOCIAL_TYPE_USER);

		// Get total number of files that are already uploaded in the group
		$model = ES::model('Files');
		$total = (int) $model->getTotalFiles($user->id, SOCIAL_TYPE_USER);

		$access = $user->getAccess();
		$allowUpload = $access->get('files.max') == 0 || $total < $access->get('files.max') ? true : false;
		$uploadLimit = $access->get('files.maxsize');

		$allowUpload = true;
		$uploadLimit = 1024;

		// Ensure that they really can upload
		if (!$user->canCreateFiles($this)) {
			$allowUpload = false;
		}

		$params = $this->getParams();
		$allowedExtensions = $params->get('allowed_extensions', 'zip,txt,pdf,gz,php,doc,docx,ppt,xls');

		$this->set('uploadLimit', $uploadLimit);
		$this->set('allowUpload', $allowUpload);
		$this->set('allowedExtensions', $allowedExtensions);
		$this->set('explorer', $explorer);
		$this->set('user', $user);

		echo parent::display('profile/default');
	}
}
