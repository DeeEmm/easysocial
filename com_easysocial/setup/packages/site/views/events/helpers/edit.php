<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewEventsEditHelper extends EasySocial
{
	/**
	 * Determines the event that is currently being viewed
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveEvent()
	{
		static $event = null;

		if (is_null($event)) {
			$id = $this->input->get('id', 0, 'int');
			$event = ES::event($id);

			if (!$event || !$event->id || (!$event->isPublished() && !$event->isPending() && !$event->isDraft()) || !$event->canViewEvent()) {
				return ES::raiseError(404, JText::_('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID'));
			}
		}

		return $event;
	}

	/**
	 * Retrieve the steps of the event
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getEventSteps()
	{
		static $steps = null;

		if (is_null($steps)) {
			$event = $this->getActiveEvent();

			$category = ES::table('EventCategory');
			$category->load($event->category_id);

			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($category->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS, SOCIAL_EVENT_VIEW_EDIT);
		}

		return $steps;
	}

	/**
	 * Get current active step
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getActiveStep()
	{
		$activeStep = $this->input->get('activeStep', 0, 'int');
		return $activeStep;
	}
}
