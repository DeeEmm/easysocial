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

class NewsViewItem extends SocialAppsView
{
	/**
	 * Renders the news item
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function display($uid = null, $docType = null)
	{
		$event = ES::event($uid);

		// We should not display the news app if it's disabled
		$access = $event->getAccess();

		if (!$access->get('announcements.enabled', true)) {
			return $this->redirect($event->getPermalink(false));
		}

		// Get the article item
		$id = $this->input->get('newsId', 0, 'int');

		// Previous news id was using articleId, so this is a fallback input. #1604
		if (!$id) {
			$id = $this->input->get('articleId', 0, 'int');
		}

		// Get the article item
		$news = ES::table('ClusterNews');
		$news->load($id);

		// Check if the user is really allowed to view this item
		if (!$news->id || !$event->canViewItem()) {
			return $this->redirect($event->getPermalink(false));
		}

		$this->setTitle('COM_ES_ANNOUNCEMENTS');

		// Get the author of the article
		$author = $news->getAuthor();

		// Get the url for the article
		$url = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT, 'id' => $this->app->getAlias(), 'newsId' => $news->id, 'sef' => false), false);

		// Apply comments for the article
		$comments = ES::comments($news->id, 'news', 'create', SOCIAL_APPS_GROUP_EVENT, array('url' => $url, 'clusterId' => $news->cluster_id));

		// Apply likes for the article
		$likes = ES::likes()->get($news->id, 'news', 'create', SOCIAL_APPS_GROUP_EVENT);

		// Increament news hit
		$news->hit();

		// Set the page title
		ES::document()->title($news->get('title'));

		// Retrieve the params
		$params = $this->app->getParams();

		// Render Meta Object
		$news->renderMetaObj();

		$this->set('app', $this->app);
		$this->set('params', $params);
		$this->set('cluster', $event);
		$this->set('likes', $likes);
		$this->set('comments', $comments);
		$this->set('author', $author);
		$this->set('news', $news);

		echo parent::display('themes:/site/news/item/default');
	}
}
