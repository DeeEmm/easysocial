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

class NewsViewForm extends SocialAppsView
{
	/**
	 * Renders the form to create news
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function display($uid = null, $docType = null)
	{
		$event = ES::event($uid);

		// We should not display the news app if it's disabled
		$access = $event->getAccess();

		if (!$access->get('announcements.enabled', true) || !$event->canCreateNews()) {
			return $this->redirect($event->getPermalink(false));
		}

		// Get app params
		$params = $this->app->getParams();

		$editor = $params->get('editor', 'tinymce');

		$editorEnabled = JPluginHelper::isEnabled("editors", $editor);

		if (!$editorEnabled) {
			$editor = 'bbcode';
		}

		$guest = $event->getGuest();

		$this->setTitle('APPS_GROUP_NEWS_TITLE_CREATE_ANNOUNCEMENT');

		// Determines if this item is being edited
		$id = $this->input->get('newsId', 0, 'int');
		$news = ES::table('ClusterNews');
		$news->load($id);

		ES::document()->title(JText::_('APP_EVENT_NEWS_FORM_UPDATE_PAGE_TITLE'));

		// Determine if this is a new record or not
		if (!$id) {
			$news->comments = true;
			ES::document()->title(JText::_('APPS_GROUP_NEWS_TITLE_CREATE_ANNOUNCEMENT'));
		}

		$this->set('params', $params);
		$this->set('news', $news);
		$this->set('editor', $editor);
		$this->set('cluster', $event);

		echo parent::display('themes:/site/news/form/default');
	}
}
