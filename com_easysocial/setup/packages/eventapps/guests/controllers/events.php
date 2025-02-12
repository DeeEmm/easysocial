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

class GuestsControllerEvents extends SocialAppsController
{
	/**
	 * Allows caller to filter event members
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getGuests()
	{
		ES::checkToken();

		$keyword = $this->input->get('keyword', '', 'default');

		// Get the event object
		$id = $this->input->get('id', 0, 'int');
		$event = ES::event($id);

		$appParam = $this->getApp()->getParams();

		if (!$event || !$id) {
			return $this->ajax->reject();
		}

		// Check whether the viewer can really view the contents
		if (!$event->canViewItem()) {
			return $this->ajax->reject();
		}

		// Get the current filter
		$filter  = $this->input->get('filter', '', 'word');
		$options = array();
		$emptyText = 'APP_EVENT_GUESTS_EMPTY';

		$limit = (int) $appParam->get('guests.limit');

		// Followers to display per page.
		$options['limit'] = $limit;

		if ($filter == 'admin') {
			$options['admin'] = true;
		}

		if ($filter == 'going') {
			$options['state'] = SOCIAL_EVENT_GUEST_GOING;
		}

		if ($filter == 'maybe') {
			$options['state'] = SOCIAL_EVENT_GUEST_MAYBE;
		}

		if ($filter == 'notgoing') {
			$options['state'] = SOCIAL_EVENT_GUEST_NOT_GOING;
		}

		if ($filter == 'pending') {
			$options['state'] = SOCIAL_EVENT_GUEST_PENDING;
		}

		if ($filter == 'invited') {
			$options['state'] = SOCIAL_EVENT_GUEST_INVITED;
		}

		if (!empty($keyword)) {
			$options['search'] = $keyword;
			$emptyText = 'APP_EVENT_GUESTS_EMPTY_SEARCH';
		}

		$model = ES::model('Events');
		$guests  = $model->getGuests($event->id, $options);
		$pending = $model->getTotalPendingGuests($event->id);

		// Set pagination properties
		$pagination = $model->getPagination();
		$pagination->setVar('view', 'events');
		$pagination->setVar('layout', 'item');
		$pagination->setVar('id', $event->getAlias());
		$pagination->setVar('appId', $this->getApp()->getAlias());
		$pagination->setVar('Itemid', ESR::getItemId('events', 'item', $event->id));

		if ($pagination && $filter) {
			$pagination->setVar('appFilter', $filter);
		}

		$redirectionOptions = array('layout' => 'item', 'id' => $event->getAlias(), 'appId' => $this->getApp()->getAlias());

		if ($filter) {
			$redirectionOptions['appFilter'] = $filter;
		}

		$returnUrl = ESR::events($redirectionOptions, false);
		$returnUrl = base64_encode($returnUrl);

		if ($guests) {
			foreach ($guests as $guest) {
				$guest->user = ES::user($guest->uid);
			}
		}

		$myGuest = $event->getGuest();

		// Load the contents
		$theme = ES::themes();
		$theme->set('returnUrl', $returnUrl);
		$theme->set('pagination', $pagination);
		$theme->set('event', $event);
		$theme->set('guests', $guests);
		$theme->set('myGuest', $myGuest);
		$theme->set('emptyText', $emptyText);
		$theme->set('pending', $pending);
		$theme->set('filter', $filter);

		$contents = $theme->output('apps/event/guests/events/wrapper');

		return $this->ajax->resolve($contents, count($guests));
	}
}
