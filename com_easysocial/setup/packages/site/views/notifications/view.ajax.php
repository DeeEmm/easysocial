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

class EasySocialViewNotifications extends EasySocialSiteView
{
	/**
	 * Counter checks for new friend notifications
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function friends($total = 0)
	{
		return $this->ajax->resolve($total);
	}

	/**
	 * Counter checks for new system notifications
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function system($total = 0)
	{
		return $this->ajax->resolve($total);
	}

	/**
	 * Counter checks for new system notifications
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function conversations($total = 0)
	{
		$data = new stdClass();
		$data->uid = uniqid();
		$data->title = JText::sprintf('You have %1$s new conversations', $total);
		$data->contents = '';

		return $this->ajax->resolve($total, $data);
	}

	/**
	 * Counter checks for new notifications
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function counters($results)
	{
		return $this->ajax->resolve($results);
	}

	/**
	 * Returns a list of new notifications
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getNotifications($items)
	{
		$theme = ES::themes();
		$theme->set('notifications', $items);

		$output = $theme->output('site/notifications/popbox/notifications');

		return $this->ajax->resolve($output);
	}

	/**
	 * Checks for new broadcasts
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getBroadcasts($broadcasts)
	{
		if (!$broadcasts) {
			return $this->ajax->resolve($broadcasts);
		}

		foreach ($broadcasts as &$broadcast) {

			// Get the author object
			$author = ES::user($broadcast->created_by);

			// Retrieve the author's avatar
			$broadcast->authorAvatar = $author->getAvatar();

			$broadcast->raw_title = $broadcast->title;
			$broadcast->title = $broadcast->getTitle();

		}

		return $this->ajax->resolve($broadcasts);
	}

	/**
	 * Post processing after a state has been set
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setState()
	{
		ES::requireLogin();

		return $this->ajax->resolve();
	}

	/**
	 * Post processing after a state has been set
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setAllRead()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after a state has been set
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function clear()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Counter checks for new friend notifications
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function clearAllConfirm()
	{
		ES::requireLogin();

		$theme = ES::themes();
		$content = $theme->output('site/notifications/dialogs/clearall');

		return $this->ajax->resolve($content);
	}

	/**
	 * Counter checks for new friend notifications
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function clearConfirm()
	{
		ES::requireLogin();

		$theme = ES::themes();
		$content = $theme->output('site/notifications/dialogs/clear');

		return $this->ajax->resolve($content);
	}

	/**
	 * Load more the notifications
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function loadmore($items, $nextlimit)
	{
		ES::requireLogin();

		$content = '';

		if (count($items) > 0) {
			$theme = ES::themes();

			$theme->set('items', $items);
			$content = $theme->output('site/notifications/default/item');
		}

		return $this->ajax->resolve($content, $nextlimit);
	}
}
