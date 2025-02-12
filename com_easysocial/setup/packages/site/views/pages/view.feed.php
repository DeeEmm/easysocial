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

class EasySocialViewPages extends EasySocialSiteView
{
	/**
	 * Renders the feed view of a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		if (!$this->config->get('rss.enabled')) {
			$this->info->set(false, 'COM_EASYSOCIAL_NOT_ALLOWED_TO_VIEW_SECTION', SOCIAL_MSG_ERROR);
			$this->redirect(ESR::dashboard(array(), false));
			return;
		}

		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		if (!$id || !$page->id) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_PAGES_INVALID_PAGE_ID'), 404);
		}

		// Ensure that the page is published
		if (!$page->isPublished()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_PAGES_PAGE_NOT_FOUND'), 404);
		}

		// Check for page permissions
		if ($page->isInviteOnly() && !$page->isMember() && !$page->isInvited() && !$this->my->isSiteAdmin()) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_PAGES_PAGE_NOT_FOUND'), 404);
		}

		// If the user is not the owner and the user has been blocked by the page creator
		if ($this->my->id != $page->creator_uid && $this->my->isBlockedBy($page->creator_uid)) {
			throw ES::exception(JText::_('COM_EASYSOCIAL_PAGES_PAGE_NOT_FOUND'), 404);
		}

		// Set the page title
		$this->page->title($page->getName());

		// Get the stream library
		$stream = ES::stream();
		$options = array('clusterId' => $page->id, 'clusterType' => SOCIAL_TYPE_PAGE, 'nosticky' => true);
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
