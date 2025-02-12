<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsEventRecurring extends SocialFieldItem
{
	/**
	 * Renders a list of scheduled events that will be created
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getScheduledEvents()
	{
		$start = $this->input->getString('start');
		$timezone = $this->input->getString('timezone');
		$end = $this->input->getString('end');
		$type = $this->input->getString('type');
		$daily = $this->input->getVar('daily', array());
		$eventId = $this->input->getString('eventId');
		$allday = $this->input->getBool('allday', false);

		// Since we preview that next schedule event only, so no need to perform any timezone here #2362
		// if (!empty($timezone) && $timezone !== 'UTC') {
		//     $dtz = new DateTimeZone($timezone);

		//     // This is to reverse the time back to UTC
		//     $start = JFactory::getDate($start, $dtz)->toSql();
		// }

		$eventStart = ES::date($start, false);

		$model = ES::model('Events');
		$options = array(
						'eventStart' => $eventStart,
						'end' => $end,
						'type' => $type,
						'daily' => $daily
					);

		$result = $model->getRecurringSchedule($options);

		$schedules = array();
		$format = $this->config->get('events.timeformat', '12h');

		foreach ($result as $time) {
			// Do not proceed any offset for this preview
			$schedules[] = ES::date($time, false)->format(JText::_($allday ? 'COM_EASYSOCIAL_DATE_DMY' : 'COM_EASYSOCIAL_DATE_DMY' . ($format == '12h' ? '12H' : '24H')));
		}

		if (!$schedules && $type != 'none') {
			return $this->ajax->reject('FIELD_EVENT_RECURRING_NO_RECURRING_EVENT_WILL_BE_CREATED');
		}

		$theme = ES::themes();
		$theme->set('schedules', $schedules);
		$html = $theme->output('fields/event/recurring/dialog.schedules');

		return $this->ajax->resolve($html);
	}

	public function calculateRecurringEvents()
	{
		$start = $this->input->getString('start');
		$timezone = $this->input->getString('timezone');
		$end = $this->input->getString('end');
		$type = $this->input->getString('type');
		$daily = $this->input->getVar('daily', array());
		$allday = $this->input->getBool('allday', false);

		$eventId = $this->input->getString('eventId');
		$changed = $this->input->getInt('changed');
		$showWarningMessages = $this->input->getInt('showWarningMessages');

		if (!empty($timezone) && $timezone !== 'UTC') {
			$dtz = new DateTimeZone($timezone);

			// This is to reverse the time back to UTC
			$start = JFactory::getDate($start, $dtz)->toSql();
		}

		$eventStart = ES::date($start, false);

		$result = ES::model('Events')->getRecurringSchedule(array(
			'eventStart' => $eventStart,
			'end' => $end,
			'type' => $type,
			'daily' => $daily
		));

		$schedule = array();

		$tf = ES::config()->get('events.timeformat', '12h');

		foreach ($result as $time) {
			$schedule[] = ES::date($time)->format(JText::_($allday ? 'COM_EASYSOCIAL_DATE_DMY' : 'COM_EASYSOCIAL_DATE_DMY' . ($tf == '12h' ? '12H' : '24H')));
		}

		if (empty($schedule) && $type != 'none') {
			ES::ajax()->reject(JText::_('FIELD_EVENT_RECURRING_NO_RECURRING_EVENT_WILL_BE_CREATED'));
		}

		$theme = ES::themes();

		$total = count($schedule);
		$limit = $this->config->get('events.recurringlimit', 0);

		if (!empty($limit) && $limit != 0 && $total > $limit) {
			$msg = JText::sprintf('FIELD_EVENT_RECURRING_VALIDATION_MAX_RECURRING_LIMIT', $total, $limit);
			return $this->ajax->reject($msg);
		}

		$theme->set('schedule', $schedule);
		$theme->set('type', $type);

		$hasChildren = !empty($eventId) && ES::model('Events')->getTotalEvents(array(
			'state' => SOCIAL_STATE_PUBLISHED,
			'parent_id' => $eventId
		)) > 0;

		$theme->set('hasChildren', $hasChildren);

		if ($type == 'daily') {
			$theme->set('days', array(
				JText::_('SUNDAY'),
				JText::_('MONDAY'),
				JText::_('TUESDAY'),
				JText::_('WEDNESDAY'),
				JText::_('THURSDAY'),
				JText::_('FRIDAY'),
				JText::_('SATURDAY')
			));
			$theme->set('daily', $daily);
		}

		$theme->set('changed', $changed);
		$theme->set('showWarningMessages', $showWarningMessages);

		$html = $theme->output('fields/event/recurring/summary');

		return $this->ajax->resolve($html);
	}
}
