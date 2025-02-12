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

class GuestsWidgetsEvents extends SocialAppsWidgets
{
	/**
	 * Renders the attendees for mobile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($eventId, $event)
	{
		$params = $this->app->getParams();

		if ($params->get('show_guests', true)) {
			// Apply hard limit for mobile
			$limit = 10;

			echo $this->getGuests($event, $limit);
		}
	}

	/**
	 * Display users attending this event
	 *
	 * @since    1.3
	 * @access   public
	 */
	public function sidebarBottom($eventId)
	{
		// Load up the event object
		$event = ES::event($eventId);

		$params = $this->app->getParams();

		// guests list limit
		$limit = $params->get('guests.limit', 20);

		if ($params->get('show_guests', true)) {
			echo $this->getGuests($event, $limit);
		}

		if ($params->get('show_friends', true) && $this->config->get('friends.enabled')) {
			echo $this->getFriends($event);
		}
	}

	/**
	 * Get event guests
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function getGuests(SocialEvent $event, $limit = 20)
	{
		$params = $event->getParams();
		$totalGoing = count($event->going);
		$ids = array();
		$guests = array();

		// Guests are already in $event->guests property
		// Going guests are also in $event->going property
		// Use php random to pick the id out from $event->going, then map it back to $event->guests
		if ($totalGoing > 0) {
			$ids = (array) array_rand($event->going, min($totalGoing, $limit));

			foreach ($ids as $id) {
				$guest = $event->guests[$event->going[$id]];
				$guests[] = ES::user($guest->uid);
			}
		}

		$link = $event->getPermalink(false, false, 'item', true, array('appId' => $this->app->getAlias()));

		$theme = ES::themes();
		$theme->set('guests', $guests);
		$theme->set('event', $event);
		$theme->set('link', $link);

		echo $theme->output('themes:/apps/event/guests/widgets/guests');
	}

	/**
	 * Get friends in the event
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function getFriends($event, $limit = 5)
	{
		$theme = ES::themes();

		$options = array();
		$options['userId'] = $this->my->id;
		$options['randomize'] = true;
		$options['limit'] = $limit;
		$options['published'] = true;
		$options['fromWidget'] = true;

		$model = ES::model('Events');
		$friends = $model->getFriendsInEvent($event->id, $options);

		if (!$friends) {
			return;
		}

		$theme->set('friends', $friends);

		return $theme->output('themes:/apps/event/guests/widgets/friends');
	}
}
