<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialPageAppPagesHookNotificationPage
{
	/**
	 * Processes likes notifications
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function execute(&$item)
	{
		// Get the page
		$page = ES::page($item->uid);

		$item->setActorAlias($page);
		// For rejection, we know that there's always only 1 target
		if ($item->cmd == 'pages.promoted') {

			$item->title = JText::sprintf('APP_PAGE_PAGES_YOU_HAVE_BEEN_PROMOTED_AS_THE_PAGE_ADMIN', $page->getName());
			$item->image = $page->getAvatar();
			return;
		}

		// For rejection, we know that there's always only 1 target
		if ($item->cmd == 'pages.user.rejected') {

			$item->title = JText::sprintf('APP_PAGE_PAGES_YOUR_APPLICATION_HAS_BEEN_REJECTED', $page->getName());

			return;
		}

		// For user removal, we know that there's always only 1 target
		if ($item->cmd == 'pages.user.removed') {

			$item->title = JText::sprintf('APP_PAGE_PAGES_YOU_HAVE_BEEN_REMOVED_FROM_PAGE', $page->getName());

			return;
		}
	}

}
