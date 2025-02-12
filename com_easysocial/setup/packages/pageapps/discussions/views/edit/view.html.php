<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class DiscussionsViewEdit extends SocialAppsView
{
	public function display($uid = null , $docType = null)
	{
		ES::requireLogin();

		$page = ES::page($uid);

		$this->page->title('APP_GROUP_DISCUSSIONS_EDITING_SUBTITLE');
		$this->setTitle('APP_GROUP_DISCUSSIONS_EDITING_SUBTITLE');

		// Get the discussion item
		$discussion = ES::table('Discussion');
		$discussion->load($this->input->get('discussionId', 0, 'int'));

		// We only alow page admin can edit this discusion. doesn't matter if he/she not the original author
		if (!$page->isAdmin()) {
			ES::info()->set(false, JText::_('COM_EASYSOCIAL_PAGES_ONLY_PAGE_ADMIN_ARE_ALLOWED'), SOCIAL_MSG_ERROR);
			return $this->redirect($page->getPermalink(false));
		}

		// Determines if we should allow file sharing
		$access = $page->getAccess();
		$files = $access->get('files.enabled' , true);

		$params = $this->getParams();
		$editor = $params->get('editor', 'bbcode');

		$this->set('files', $files);
		$this->set('discussion', $discussion);
		$this->set('cluster', $page);
		$this->set('editor', $editor);

		echo parent::display('themes:/site/discussions/form/default');
	}
}
