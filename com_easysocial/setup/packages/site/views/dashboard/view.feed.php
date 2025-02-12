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

class EasySocialViewDashboard extends EasySocialSiteView
{
	/**
	 * Renders the feed view for stream items
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function display($tpl = null)
	{
		if (!$this->config->get('rss.enabled')) {
			$this->info->set(false, 'COM_EASYSOCIAL_NOT_ALLOWED_TO_VIEW_SECTION', SOCIAL_MSG_ERROR);
			$this->redirect(ESR::dashboard(array(), false));
			return;
		}

		$stream = ES::stream();

		// If the user is a guest, we want to retrieve only stream items that the guest may view
		if ($this->my->guest) {
			$stream->getPublicStream($this->config->get('stream.pagination.pagelimit', 10), 0);
		} else {

			$startlimit = $this->input->get('limitstart', 0);

			$start	= $this->config->get('users.dashboard.start');
			$filter	= $this->input->get('filter', $start, 'word');
			$tag = $this->input->get('tag', '', 'word');

			// If there is a tag, ignore all filters since there is no way to have hashtag and filter applied together
			if ($tag) {
				$stream->get(array('tag' => $tag, 'startlimit' => $startlimit));
			} else {

				// The all is taken from the menu item the setting. all == user & friend, which mean in this case, is the 'me' filter.
				if ($filter == 'all') {
					$filter = 'me';
				}

				// Filter to me and my friends
				if ($filter == 'me') {
					$stream->get( array('startlimit' => $startlimit) );
				}

				// Filter by filter
				if ($filter == 'filter') {
					$id = $this->input->get('filterid', 0, 'int');

					$streamFilter = ES::table('StreamFilter');
					$streamFilter->load($id);

					if ($streamFilter->id) {
						$hashtags = $streamFilter->getHashTag();
						$tags = explode(',', $hashtags);

						if ($tags) {
							$stream->get(array('context' => SOCIAL_STREAM_CONTEXT_TYPE_ALL, 'tag' => $tags, 'startlimit' => $startlimit));
						}
					}
				}

				// Filter by following
				if ($filter == 'following') {
					$stream->get(array('context' => SOCIAL_STREAM_CONTEXT_TYPE_ALL, 'type' => 'follow', 'startlimit' => $startlimit));
				}

				// Filter by everyone
				if ($filter == 'everyone') {
					$stream->get(array('guest' => true, 'ignoreUser' => true, 'startlimit' => $startlimit));
				}

				// Filter by bookmarks
				if ($filter == 'bookmarks') {
					$stream->get(array('guest' => true, 'type' => 'bookmarks', 'startlimit' => $startlimit));
				}

				// Filter by friend list
				if ($filter == 'list') {
					$listId = $this->input->get('listId', 0, 'int');
					$stream->get(array('listId' => $listId, 'startlimit' => $startlimit));
				}

			}
		}

		// TODO:: the link should point back to where this feed type come from.
		// $this->doc->link = $this->rssLink;

		foreach ($stream->data as $item) {
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
