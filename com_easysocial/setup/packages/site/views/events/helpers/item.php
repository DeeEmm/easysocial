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

class EasySocialViewEventsItemHelper extends EasySocial
{
	/**
	 * Retrieves the about permalink for the event
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAboutPermalink()
	{
		static $permalink = null;

		if (is_null($permalink)) {
			$event = $this->getActiveEvent();
			$defaultDisplay = $this->getDefaultDisplay();

			$permalink = ESR::events(array('id' => $event->getAlias(), 'page' => 'info', 'layout' => 'item'));

			if ($defaultDisplay == 'info') {
				$permalink = $event->getPermalink();
			}
		}

		return $permalink;
	}

	/**
	 * Determines the default display page of the events
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getDefaultDisplay()
	{
		static $default = null;

		if (is_null($default)) {
			$default = $this->config->get('events.item.display', 'timeline');
		}

		return $default;
	}

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

			if (!$event || !$event->id || !$event->canViewEvent()) {
				return ES::raiseError(404, JText::_('COM_EASYSOCIAL_EVENT_NOT_FOUND'));
			}
		}

		return $event;
	}
}
