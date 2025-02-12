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

class FeedsControllerFeeds extends SocialAppsController
{
	/**
	 * Allows caller to save a new feed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function save()
	{
		ES::checkToken();
		ES::requireLogin();

		$table = $this->getTable('Feed');
		$table->user_id	= $this->my->id;
		$table->title = $this->input->get('title', '', 'string');
		$table->url = $this->input->get('url', '', 'default');
		$table->state = SOCIAL_STATE_PUBLISHED;

		// Try to parse the feed to get the description
		$feed = new JFeedFactory();
		$rss = $feed->getFeed($table->url);

		if ($rss) {
			$table->description = $rss->description;
		}

		$state = $table->store();

		if (!$state) {
			return $this->ajax->reject($table->getError());
		}

		// Get the application params
		$params	= $this->getParams();

		// Create new stream item when a new feed is created
		if ($params->get('stream_create', true)) {
			$table->createStream('create');
		}

		$theme = ES::themes();
		$theme->set('user', $this->my);
		$theme->set('feed', $table);
		$output = $theme->output('apps/user/feeds/profile/item');

		return $this->ajax->resolve($output);
	}

	/**
	 * Deletes a feed item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		ES::checkToken();
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$feedId = $this->input->get('feedId', 0, 'int');

		$feed = $this->getTable('Feed');
		$feed->load($feedId);

		if (!$feedId || !$feed->id) {
			return $this->ajax->reject(JText::_('APP_FEEDS_INVALID_ID_PROVIDED'));
		}

		// Ensure that the user is allowed to delete this feed.
		if ($feed->user_id != $this->my->id) {
			return $this->ajax->reject(JText::_('APP_FEEDS_NOT_ALLOWED_TO_DELETE'));
		}

		// Try to delete the feed now.
		$state = $feed->delete();

		if (!$state) {
			return $this->ajax->reject($feed->getError());
		}

		return $this->ajax->resolve();
	}
}
