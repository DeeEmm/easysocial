<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EventsWidgetsGroups extends SocialAppsWidgets
{
	/**
	 * Display the list of upcoming events
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getUpcomingEvents(SocialGroup &$group)
	{
		$params = $this->getParams();

		if (!$params->get('widget', true)) {
			return;
		}

		$group = ES::group($group->id);

		if (!$group->getAccess()->get('events.groupevent', true)) {
			return;
		}

		$my = ES::user();

		$days = $params->get('widget_days', 14);
		$total = $params->get('widget_total', 5);

		$date = ES::date();
		$now = $date->toSql();
		$future = ES::date($date->toUnix() + ($days * 24*60*60))->toSql();

		$options = array();
		$options['start-after'] = $now;
		$options['start-before'] = $future;
		$options['limit'] = $total;
		$options['state'] = SOCIAL_STATE_PUBLISHED;
		$options['ordering'] = 'start';
		$options['group_id'] = $group->id;

		$events = ES::model('Events')->getEvents($options);

		if (empty($events)) {
			return;
		}

		$this->set('events', $events);
		$this->set('app', $this->app);

		return parent::display('widgets/upcoming');
	}

	/**
	 * Display upcoming event on the side bar
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function sidebarBottom($groupId, $group)
	{
		$output = $this->getUpcomingEvents($group);

		echo $output;
	}

	public function groupAdminStart($group)
	{
		$my = ES::user();
		$config = ES::config();

		if (!$config->get('events.enabled') || !$my->getAccess()->get('events.create')) {
			return;
		}

		if (!$group->canCreateEvent() || !$group->getCategory()->getAcl()->get('events.groupevent')) {
			return;
		}

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('app', $this->app);

		echo $theme->output('themes:/apps/group/events/widgets/widget.menu');
	}
}
