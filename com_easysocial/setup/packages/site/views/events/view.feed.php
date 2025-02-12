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

class EasySocialViewEvents extends EasySocialSiteView
{
	/**
	 * Renders the RSS feed for event page
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function display($tpl = null)
	{
		if (!$this->config->get('rss.enabled')) {
			$this->info->set(false, 'COM_EASYSOCIAL_NOT_ALLOWED_TO_VIEW_SECTION', SOCIAL_MSG_ERROR);
			$this->redirect(ESR::dashboard(array(), false));
			return;
		}

		// Get the event id
		$id = $this->input->get('id', 0, 'int');

		// Load up the event
		$event = ES::event($id);

		// Ensure that the event really exists
		if (empty($event) || empty($event->id)) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_EVENTS_INVALID_EVENT_ID'), 404);
		}

		// Ensure that the event is published
		if (!$event->isPublished()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE'), 404);
		}

		// Determines if the current user is a guest of this event
		$guest = $event->getGuest($this->my->id);

		// Support for group event
		// If user is not a group member, then redirect to group page
		if ($event->isClusterEvent()) {

			$cluster = $event->getCluster();

			if (!$this->my->isSiteAdmin() && !$event->isOpen() && !$cluster->isMember()) {
				throw ES::exception(JText::_('COM_EASYSOCIAL_GROUPS_EVENTS_NO_PERMISSION_TO_VIEW_EVENT'), 404);
			}
		} else {

			if (!$this->my->isSiteAdmin() && $event->isInviteOnly() && !$guest->isParticipant()) {
				throw ES::exception(JText::_('COM_EASYSOCIAL_EVENTS_NO_ACCESS_TO_EVENT'), 404);
			}
		}

		// Check if the current logged in user blocked by the event creator or not.
		if ($this->my->id != $event->creator_uid && $this->my->isBlockedBy($event->creator_uid)) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_EVENTS_EVENT_UNAVAILABLE'), 404);
		}

		// Set the title of the feed
		$this->page->title($event->getName());

		// Get the stream library
		$stream = ES::stream();
		$options = array('clusterId' => $event->id, 'clusterType' => $event->cluster_type, 'nosticky' => true);
		$stream->get($options);

		$items = $stream->data;

		if (!$items) {
			return;
		}

		foreach ($items as $item) {
			$feed = new JFeedItem();

			// Cleanse the title
			$feed->title = strip_tags($item->title);

			$content = $item->content . $item->preview;
			$feed->description = $content;

			// Permalink should only be generated for items with a full content
			$feed->link = $item->getPermalink(true, false, true, false, true);
			$feed->date = $item->created->toSql();
			$feed->category = $item->context;

			$this->doc->addItem($feed);
		}
	}
}
