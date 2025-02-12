<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class NewsWidgetsPages extends SocialAppsWidgets
{
	/**
	 * Display admin actions for the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function pageAdminStart($page)
	{
		if (!$this->app->hasAccess($page->category_id) || !$page->getParams()->get('news', true)) {
			return;
		}

		// We should not display the news app if it's disabled
		$access = $page->getAccess();

		if (!$access->get('announcements.enabled', true)) {
			return;
		}

		$theme = ES::themes();
		$theme->set('app', $this->app);
		$theme->set('page', $page);

		echo $theme->output('themes:/apps/page/news/widgets/widget.menu');
	}

	/**
	 * Display user photos on the side bar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function sidebarBottom($pageId, SocialPage $page)
	{
		// Set the max length of the item
		$params = $this->app->getParams();
		$enabled = $params->get('widget', true);
		$page = ES::page($pageId);

		if (!$enabled || !$this->app->hasAccess($page->category_id)) {
			return;
		}

		$options = array('limit' => (int) $params->get('widgets_total', 5));

		$model = ES::model('ClusterNews');
		$items = $model->getNews($page->id, $options);

		if (!$items) {
			return;
		}

		$theme = ES::themes();
		$theme->set('page', $page);
		$theme->set('app', $this->app);
		$theme->set('items', $items);

		echo $theme->output('themes:/apps/page/news/widgets/widget.news');
	}
}
