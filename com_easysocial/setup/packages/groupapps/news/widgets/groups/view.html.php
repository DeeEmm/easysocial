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

class NewsWidgetsGroups extends SocialAppsWidgets
{
	/**
	 * Renders the action to create a new announcement
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function groupAdminStart($group)
	{
		if (!$this->app->hasAccess($group->category_id) || !$group->getParams()->get('news', true)) {
			return;
		}

		$access = $group->getAccess();

		if (!$access->get('announcements.enabled', true)) {
			return false;
		}

		$theme = ES::themes();
		$theme->set('app', $this->app);
		$theme->set('group', $group);

		echo $theme->output('themes:/apps/group/news/widgets/widget.menu');
	}

	/**
	 * Display user photos on the side bar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function sidebarBottom($groupId)
	{
		// Set the max length of the item
		$params = $this->app->getParams();
		$enabled = $params->get('widget', true);
		$group = ES::group($groupId);

		if (!$enabled || !$this->app->hasAccess($group->category_id)) {
			return;
		}

		$theme = ES::themes();

		$options = array('limit' => (int) $params->get('widgets_total', 5));

		$model = ES::model('ClusterNews');
		$items = $model->getNews($group->id, $options);

		if (!$items) {
			return;
		}

		$theme->set('group', $group);
		$theme->set('app', $this->app);
		$theme->set('items', $items);

		echo $theme->output('themes:/apps/group/news/widgets/widget.news');
	}
}
