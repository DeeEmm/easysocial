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

class GuestsViewEvents extends SocialAppsView
{
	public function display($eventId = null, $docType = null)
	{
		// Load up the event
		$event = ES::event($eventId);
		$params = $event->getParams();
		$appParam = $this->app->getParams();

		// Load up the events model
		$model = ES::model('Events');
		$filter = $this->input->get('appFilter', 'going', 'string');

		$this->setTitle('APP_ATTENDEES_APP_TITLE');

		$options = array();

		$limit = (int) $appParam->get('guests.limit');

		$options['limit'] = $limit;

		if ($filter === 'going') {
			$options['state'] = SOCIAL_EVENT_GUEST_GOING;
		}

		if ($params->get('allowmaybe') && $filter === 'maybe') {
			$options['state'] = SOCIAL_EVENT_GUEST_MAYBE;
		}

		if ($params->get('allownotgoingguest') && $filter === 'notgoing') {
			$options['state'] = SOCIAL_EVENT_GUEST_NOT_GOING;
		}

		if ($event->isClosed() && $filter === 'pending') {
			$options['state'] = SOCIAL_EVENT_GUEST_PENDING;
		}

		if ($filter === 'admin') {
			$options['admin'] = 1;
		}

		if ($filter === 'invited') {
			$options['state'] = SOCIAL_EVENT_GUEST_INVITED;
		}

		$guests  = $model->getGuests($event->id, $options);

		// Set pagination properties
		$pagination = $model->getPagination();
		$pagination->setVar('view', 'events');
		$pagination->setVar('layout', 'item');
		$pagination->setVar('id', $event->getAlias());
		$pagination->setVar('appId', $this->app->getAlias());
		$pagination->setVar('Itemid', ESR::getItemId('events', 'item', $event->id));

		if ($pagination && $filter) {
			$pagination->setVar('appFilter', $filter);
		}

		// Redirection url when an action is performed on a event's guest
		$redirectOptions = array('layout' => "item", 'id' => $event->getAlias(), 'appId' => $this->app->getAlias());

		if ($filter) {
			$redirectOptions['appFilter'] = $filter;
		}

		$returnUrl = ESR::events($redirectOptions, false);
		$returnUrl = base64_encode($returnUrl);

		if ($guests) {
			foreach ($guests as $guest) {
				$guest->user = ES::user($guest->uid);
			}
		}

		$emptyText = 'APP_EVENT_GUESTS_EMPTY_SEARCH';

		$myGuest = $event->getGuest();

		$pending = $model->getTotalPendingGuests($event->id);

		$filterLinks = $this->getFilterLinks($event);

		$theme = ES::themes();

		$theme->set('active', $filter);
		$theme->set('event', $event);
		$theme->set('guests', $guests);
		$theme->set('returnUrl', $returnUrl);
		$theme->set('myGuest', $myGuest);
		$theme->set('pagination', $pagination);
		$theme->set('emptyText', $emptyText);
		$theme->set('filterLinks', $filterLinks);
		$theme->set('pending', $pending);
		$theme->set('filter', $filter);

		echo $theme->output('apps/event/guests/events/default');
	}

	public function sidebar($moduleLib, $cluster)
	{
		// Get the current filter.
		$filter = $this->input->get('appFilter', '', 'word');

		$counters = new stdClass;
		$counters->going = $cluster->getTotalGoing();
		$counters->maybe = $cluster->getTotalMaybe();
		$counters->notgoing = $cluster->getTotalNotGoing();
		$counters->admins = $cluster->getTotalAdmins();
		$counters->pending = 0;
		$counters->invited = 0;

		if ($cluster->isAdmin() || $cluster->isOwner() || $this->my->isSiteAdmin()) {
			if ($cluster->isClosed()) {
				$counters->pending = $cluster->getTotalPendingGuests();
			}

			$counters->invited = $cluster->getTotalUndecided();
		}

		$theme = ES::themes();
		$theme->set('moduleLib', $moduleLib);
		$theme->set('counters', $counters);
		$theme->set('cluster', $cluster);
		$theme->set('active', $filter);

		echo $theme->output('apps/event/guests/events/sidebar');
	}

	/**
	 * Retrieves the filters that are available on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilterLinks($event)
	{
		static $links = null;

		if (is_null($links)) {
			$links = new stdClass();

			$appId = $this->input->get('appId', 0, 'int');
			$app = ES::table('App');
			$app->load($appId);

			$options = array(
				'layout' => 'item',
				'id' => $event->getAlias(),
				'appId' => $app->getAlias()
			);

			$options['appFilter'] = 'going';
			$links->going = ESR::events($options);

			$options['appFilter'] = 'maybe';
			$links->maybe = ESR::events($options);

			$options['appFilter'] = 'notgoing';
			$links->notgoing = ESR::events($options);

			$options['appFilter'] = 'pending';
			$links->pending = ESR::events($options);

			$options['appFilter'] = 'admin';
			$links->admin = ESR::events($options);

			$options['appFilter'] = 'invited';
			$links->invited = ESR::events($options);
		}

		return $links;
	}
}
