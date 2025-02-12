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

class DiscussionsWidgetsEvents extends SocialAppsWidgets
{
	/**
	 * Display admin actions for the event
	 *
	 * @since    1.2
	 * @access    public
	 */
	public function eventAdminStart($event)
	{
		if ($this->app->state == SOCIAL_STATE_UNPUBLISHED) {
			return;
		}

		if (!$event->getParams()->get('discussions', true)) {
			return;
		}

		$access = $event->getAccess();

		if (!$access->get('discussions.enabled', true)) {
			return;
		}

		$model = ES::model('Discussions');

		$theme = ES::themes();
		$theme->set('event', $event);
		$theme->set('app', $this->app);

		echo $theme->output('themes:/apps/event/discussions/widgets/widget.menu');
	}

	/**
	 * Renders the recent discussion widget
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function sidebarBottom($id)
	{
		// Set the max length of the item
		$params = $this->app->getParams();
		$enabled = $params->get('widget', true);

		$cluster = ES::cluster(SOCIAL_TYPE_EVENT, $id);

		if (!$enabled || !$this->app->hasAccess($cluster->category_id)) {
			return;
		}

		$theme = ES::themes();
		$options = array('limit' => (int) $params->get('widgets_total', 5));

		$model = ES::model('Discussions');
		$discussions = $model->getDiscussions($cluster->id, SOCIAL_TYPE_EVENT, $options);

		if (!$discussions) {
			return;
		}

		$theme->set('cluster', $cluster);
		$theme->set('discussions', $discussions);

		echo $theme->output('themes:/site/discussions/widgets/list');
	}

	/**
	 * Renders the discussion for mobile
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($eventId, $event)
	{
		// Enforce hard limit on mobile device
		$limit = 6;

		return $this->sidebarBottom($eventId, $event, $limit);
	}
}
