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

class FilesViewEvents extends SocialAppsView
{
	public function display($eventId = null, $docType = null)
	{
		// Load up the event
		$event = ES::event($eventId);

		if (!$event->canAccessFiles()) {
			ES::raiseError(404, JText::_('COM_ES_FILES_DISABLED'));
		}

		if (!$event->canViewItem()) {
			return $this->redirect($event->getPermalink(false));
		}

		$this->setTitle('APP_FILES_APP_TITLE');

		// Load up the explorer library.
		$explorer = ES::explorer($event->id, SOCIAL_TYPE_EVENT);

		// Get the access object
		$access = $event->getAccess();

		// Determines if the event exceeded their limits
		$allowUpload = $explorer->hook('allowUpload');
		$uploadLimit = $access->get('files.maxsize');

		$params = $this->getParams();
		$allowedExtensions = $params->get('allowed_extensions', 'zip,txt,pdf,gz,php,doc,docx,ppt,xls');

		$this->set('allowedExtensions', $allowedExtensions);
		$this->set('uploadLimit', $uploadLimit);
		$this->set('allowUpload', $allowUpload);
		$this->set('explorer', $explorer);
		$this->set('event', $event);

		echo parent::display('events/default');
	}
}
