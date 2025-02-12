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

class NewsViewGroups extends SocialAppsView
{
	/**
	 * Renders the list of news available on the group page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($groupId = null, $docType = null)
	{
		// Load up the group
		$group = ES::group($groupId);

		// Check if the viewer is really allowed to view news
		if ($group->isInviteOnly() && $group->isClosed() && !$group->isMember() && !$this->my->isSiteAdmin()) {
			return $this->redirect($group->getPermalink(false));
		}

		// We should not display the news app if it's disabled
		$access = $group->getAccess();

		if (!$access->get('announcements.enabled', true)) {
			return $this->redirect($group->getPermalink(false));
		}

		// Update the title
		$this->setTitle('COM_ES_ANNOUNCEMENTS');

		$params = $this->app->getParams();

		// Set the max length of the item
		$options = array('limit' => (int) $params->get('total', 10));

		$model = ES::model('ClusterNews');
		$items = $model->getNews($group->id, $options);
		$pagination = $model->getPagination();

		// Format the item's content.
		$this->format($items, $params);

		$pagination->setVar('option', 'com_easysocial');
		$pagination->setVar('view', 'groups');
		$pagination->setVar('layout', 'item');
		$pagination->setVar('id', $group->getAlias());
		$pagination->setVar('appId', $this->app->getAlias());

		$this->set('params', $params);
		$this->set('pagination', $pagination);
		$this->set('cluster', $group);
		$this->set('items', $items);

		echo parent::display('themes:/site/news/default/default');
	}

	/**
	 * Render the sidebar for this apps through a modle
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sidebar($moduleLib, $cluster)
	{
		$this->set('moduleLib', $moduleLib);
		$this->set('cluster', $cluster);

		echo parent::display('themes:/site/news/default/sidebar/default');
	}

	private function format(&$items, $params)
	{
		$length	= (int) $params->get('content_length', 350);

		if ($length == 0) {
			return;
		}

		foreach ($items as &$item) {
			$item->content 	= ESJString::substr(strip_tags($item->content), 0, $length) . ' ' . JText::_('COM_EASYSOCIAL_ELLIPSES');
		}
	}
}
