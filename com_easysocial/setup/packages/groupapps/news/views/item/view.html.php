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

class NewsViewItem extends SocialAppsView
{
	/**
	 * Renders the single news page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($uid = null, $docType = null)
	{
		$group = ES::group($uid);

		// We should not display the news app if it's disabled
		$access = $group->getAccess();

		if (!$access->get('announcements.enabled', true)) {
			return $this->redirect($group->getPermalink(false));
		}

		// Get the article item
		$id = $this->input->get('newsId', 0, 'int');
		$news = ES::table('ClusterNews');
		$news->load($id);

		// Check if the user is really allowed to view this item
		if (!$group->canViewItem()) {
			return $this->redirect($group->getPermalink(false));
		}

		$this->setTitle('COM_ES_ANNOUNCEMENTS');

		// Get the author of the article
		$author = $news->getAuthor();

		// Get the url for the article
		$url = $news->getPermalink(true, false, false);

		// Apply comments for the article
		$comments = ES::comments($news->id, 'news', 'create', SOCIAL_APPS_GROUP_GROUP, array('url' => $url, 'clusterId' => $news->cluster_id));

		// Apply likes for the article
		$likes = ES::likes()->get($news->id, 'news', 'create', SOCIAL_APPS_GROUP_GROUP);

		// Increament news hit
		$news->hit();

		// Set the page title
		$this->page->title($news->_('title'));

		// Get a list of other news
		$model = ES::model('Groups');

		// Retrieve the params
		$params = $this->app->getParams();

		// Render meta object
		$news->renderMetaObj();

		$this->set('params', $params);
		$this->set('cluster', $group);
		$this->set('likes', $likes);
		$this->set('comments', $comments);
		$this->set('author', $author);
		$this->set('news', $news);

		echo parent::display('themes:/site/news/item/default');
	}
}
