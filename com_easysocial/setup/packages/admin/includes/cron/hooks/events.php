<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialCronHooksEvents
{
	public function execute(&$states)
	{
		// Set all past event to unfeatured state 
		$states[] = $this->unfeaturedPastEvent();

		$config = ES::config();

		if ($config->get('events.reminder.enabled')) {
			$states[] = $this->processUpcomingEventReminder();	
		}
	}

	/**
	 * Notify users about their upcoming event
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function processUpcomingEventReminder()
	{
		$model = ES::model('Events');
		$events = $model->getUpcomingReminder();

		if ($events) {
			$state = $model->sendUpcomingReminder($events);

			if ($state) {
				return JText::sprintf( 'COM_EASYSOCIAL_CRONJOB_EVENT_UPCOMING_REMINDER_PROCESSED', $state );
			}
		}

		return JText::_( 'COM_EASYSOCIAL_CRONJOB_EVENT_UPCOMING_REMINDER_NOTHING_TO_EXECUTE' );
	}

	/**
	 * Change the featured state of the past event
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unfeaturedPastEvent()
	{
		$config = ES::config();

		if (!$config->get('events.unfeatured.pastevent')) {
			return JText::_('COM_ES_CRONJOB_UNFEATURED_PASTEVENT_DISABLED');
		}

		$model = ES::model('Events');

		$featuredPastEventsIds = $model->getEventToUnfeatured(true);

		if ($featuredPastEventsIds) {
			$count = $model->unfeaturedEvents($featuredPastEventsIds);

			return JText::sprintf('COM_ES_CRONJOB_PASTEVENT_UNFEATURED_SUCCESSFULLY', $count);
		}

		return JText::_('COM_ES_CRONJOB_PASTEVENT_NOTHING_TO_UNFEATURED');
	}
}
