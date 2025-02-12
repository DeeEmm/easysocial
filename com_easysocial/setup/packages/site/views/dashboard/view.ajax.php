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

class EasySocialViewDashboard extends EasySocialSiteView
{
	/**
	 * Get additional cluster items to render on the sidebar
	 * since the sidebar only displays limited clusters based on theme settings.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getMoreClusters()
	{
		$type = $this->input->get('type', '', 'word');
		$allowedClusters = array('groups', 'events', 'pages');

		if (!in_array($type, $allowedClusters)) {
			return $this->exception();
		}

		$method = 'getMore' . ucfirst($type);
		$output = $this->$method();

		return $this->ajax->resolve($output);
	}

	private function getMoreEvents()
	{
		// Retrieve user's events
		$model = ES::model('Events');
		$options = array('guestuid' => $this->my->id, 'ongoing' => true, 'upcoming' => true, 'ordering' => 'start');

		$events = $model->getEvents($options);

		$theme = ES::themes();
		$theme->set('events', $events);
		$output = $theme->output('site/dashboard/default/filter.events');

		return $output;
	}

	private function getMorePages()
	{
		$model = ES::model('Pages');
		$pages = $model->getUserPages($this->my->id, 0);

		$theme = ES::themes();
		$theme->set('pages', $pages);
		$output = $theme->output('site/dashboard/default/filter.pages');

		return $output;
	}

	private function getMoreGroups()
	{
		$model = ES::model('Groups');
		$groups = $model->getUserGroups($this->my->id);

		$theme = ES::themes();
		$theme->set('groups', $groups);
		$output = $theme->output('site/dashboard/default/filter.groups');

		return $output;
	}

	/**
	 * Retrieves the public stream contents.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPublicStream($stream)
	{
		// Get the stream count
		$count = $stream->getCount();

		$theme = ES::themes();
		$theme->set('stream', $stream);
		$theme->set('streamcount', $count);
		$theme->set('customFilter', false);

		$contents = $theme->output('site/dashboard/default/feeds');

		return $this->ajax->resolve($contents, $count);
	}

	/**
	 * Hides the welcome message
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hideWelcome()
	{
		$this->my->setConfig('showwelcome', 0);
		$this->my->storeConfig();

		$message = JText::_('COM_EASYSOCIAL_WELCOME_MESSAGE_DONE');
		return $this->ajax->resolve($message);
	}

	/**
	 * Display page refresh confirmation dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmPageRefresh()
	{
		$minute = $this->config->get('users.inactivity.duration', '15');

		$theme = ES::themes();
		$theme->set('minute', $minute);
		$contents = $theme->output('site/dashboard/dialogs/refresh');

		return $this->ajax->resolve($contents);
	}

}
