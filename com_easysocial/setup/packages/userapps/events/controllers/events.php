<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EventsControllerEvents extends SocialAppsController
{
	/**
	 * Renders the event creation form
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function loadStoryForm()
	{
		ES::checkToken();
		ES::requireLogin();

		ES::language()->loadAdmin();

		// Get the category
		$id = $this->input->get('id', 0, 'int');
		$category = ES::table('EventCategory');
		$category->load($id);

		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_fields', 'a');
		$sql->column('a.*');
		$sql->column('d.element');
		$sql->leftjoin('#__social_fields_steps', 'b');
		$sql->on('a.step_id', 'b.id');
		$sql->leftjoin('#__social_clusters_categories', 'c');
		$sql->on('b.workflow_id', $category->getWorkflow()->id);
		$sql->leftjoin('#__social_apps', 'd');
		$sql->on('a.app_id', 'd.id');
		$sql->where('b.type', SOCIAL_TYPE_CLUSTERS);
		$sql->where('c.id', $category->id);
		$sql->where('d.group', SOCIAL_FIELDS_GROUP_EVENT);
		$sql->where('d.type', SOCIAL_APPS_TYPE_FIELDS);
		$sql->where('d.element', array('startend', 'title', 'description'), 'in');

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		$theme = ES::themes();
		$theme->set('category', $category);

		foreach ($result as $row) {
			$field = ES::table('Field');
			$field->bind($row);

			$params = $field->getParams();

			if ($row->element === 'startend') {
				$dateFormat = $params->get('date_format', 'DD-MM-YYYY');

				if ($params->get('allow_time', true)) {
					$dateFormat .= ' ' . $params->get('time_format', 'hh:mm A');
				}

				if ($params->get('allow_timezone', true)) {
					$theme->set('timezones', $this->getTimezones());
				}

				$theme->set('dateFormat', $dateFormat);
				$theme->set('allowTimezone', $params->get('allow_timezone', 1));
				$theme->set('allowTime', $params->get('allow_time', 1));
				$theme->set('yearfrom', $params->get('yearfrom'));
				$theme->set('yearto', $params->get('yearto'));
				$theme->set('disallowPast', $params->get('disallow_past', 0));
				$theme->set('minuteStepping', $params->get('minute_stepping', 15));
				$theme->set('dow', $this->config->get('events.startofweek'));
			}

			if ($row->element === 'title') {
				// We need to cater for the title option such as; default value or readonly
				$theme->set('titleField', $field);
				$theme->set('titleReadOnly', $params->get('readonly'));
			}

			if ($row->element === 'description') {
				$theme->set('descriptionPlaceholder', $field->get('description'));
			}
		}

		$output = $theme->output('site/story/events/panel.form');

		return $this->ajax->resolve($output);
	}

	protected function getTimezones()
	{
		static $timezones = array();

		if (empty($timezones)) {
			$zones = DateTimeZone::listIdentifiers();

			foreach ($zones as $zone) {
				$key = strstr($zone, '/', true);

				if (!empty($key)) {
					$timezones[$key][] = $zone;
				}
			}
		}

		return $timezones;
	}
}
