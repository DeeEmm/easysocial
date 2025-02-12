<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class NewsViewForm extends SocialAppsView
{
	/**
	 * Renders the creation form for news
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($uid = null, $docType = null)
	{
		$group = ES::group($uid);

		// We should not display the news app if it's disabled
		$access = $group->getAccess();

		if (!$access->get('announcements.enabled', true) || !$group->canCreateNews()) {
			return $this->redirect($group->getPermalink(false));
		}

		// Get app params
		$params = $this->app->getParams();

		// Get the editor
		$editor = $params->get('editor', 'tinymce');

		$editorEnabled = JPluginHelper::isEnabled("editors", $editor);

		if (!$editorEnabled) {
			$editor = 'bbcode';
		}

		$this->setTitle('APPS_GROUP_NEWS_TITLE_CREATE_ANNOUNCEMENT');

		$id = $this->input->get('newsId', 0, 'int');
		$news = ES::table('ClusterNews');
		$news->load($id);

		$this->page->title('APP_GROUP_NEWS_FORM_UPDATE_PAGE_TITLE');

		// Determine if this is a new record or not
		if (!$id) {
			$news->comments = true;

			$this->page->title('APPS_GROUP_NEWS_TITLE_CREATE_ANNOUNCEMENT');
		}

		$this->set('params', $params);
		$this->set('news', $news);
		$this->set('editor', $editor);
		$this->set('cluster', $group);

		echo parent::display('themes:/site/news/form/default');
	}
}
