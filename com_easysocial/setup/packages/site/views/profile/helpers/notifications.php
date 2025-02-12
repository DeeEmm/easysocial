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

class EasySocialViewProfileNotificationsHelper extends EasySocial
{

	/**
	 * Get current active tab
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveTab()
	{
		$activeTab = $this->input->get('activeTab', '', 'cmd');
		return $activeTab;
	}


	/**
	 * Get the custom alerts if there is any
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCustomAlerts()
	{
		static $custom = null;

		if (is_null($custom)) {

			// Render custom alert settings from the app
			$custom = array();

			// Load the library.
			$arguments = array(&$custom);

			$dispatcher = ES::dispatcher();
			$dispatcher->trigger(SOCIAL_TYPE_USER, 'onRenderAlerts', $arguments);
		}

		return $custom;
	}

	/**
	 * Get the alert filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilterAlerts()
	{
		static $filteredAlerts = null;

		if (is_null($filteredAlerts)) {

			// Get the user notification settings
			$alerts = ES::alert()->getUserSettings($this->my->id);

			$groups = $this->getGroups();

			// filter the alerts to remove the alerts for those disabled features. #717
			$filteredAlerts = array();

			foreach ($groups as $group) {

				$filteredAlerts[$group] = array();

				if (isset($alerts[$group])) {
					foreach ($alerts[$group] as $element => $alert) {

						if (($element == 'albums' || $element == 'photos') && !$this->config->get('photos.enabled')) {
							continue;
						}

						if ($element == 'broadcast' && !$this->config->get('notifications.broadcast.popup')) {
							continue;
						}

						if ($element == 'conversations' && !$this->config->get('conversations.enabled')) {
							continue;
						}

						if ($element == 'events' && !$this->config->get('events.enabled')) {
							continue;
						}

						if ($element == 'groups' && !$this->config->get('groups.enabled')) {
							continue;
						}

						if ($element == 'pages' && !$this->config->get('pages.enabled')) {
							continue;
						}

						if ($element == 'videos' && !$this->config->get('video.enabled')) {
							continue;
						}

						if (($element == 'badges') && !$this->config->get('badges.enabled')) {
							continue;
						}

						if ($element == 'friends' && !$this->config->get('friends.enabled')) {
							continue;
						}

						if ($element == 'polls' && !$this->config->get('polls.enabled')) {
							continue;
						}


						$filteredAlerts[$group][$element] = $alert;
					}
				}
			}
		}

		return $filteredAlerts;
	}

	/**
	 * Get the alert filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getGroups()
	{
		$groups = array('system', 'others');
		return $groups;
	}
}
