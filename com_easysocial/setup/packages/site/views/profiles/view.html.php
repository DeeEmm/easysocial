<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Include main view file.
ES::import('site:/views/views');

class EasySocialViewProfiles extends EasySocialSiteView
{
	/**
	 * Displays a single profile item layout
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function item()
	{
		ES::setMeta();

		$helper = $this->getHelper('Item');

		// Validate for the current profile type id
		$profile = $helper->getActiveProfiles();

		// Retrieve member from this profile type
		$randomMembers = $helper->getMembers();

		// Retrieve total of member count
		$totalUsers = $helper->getMembersCount();

		// Get statistics of user registration for this profile type
		$stats = $helper->getRegistrationStats();

		// Get the stream for this profile
		$stream = $helper->getStreamData();

		// Set the page title to this category
		ES::document()->title($profile->get('title'));

		// Set the breadcrumbs
		ES::document()->breadcrumb($profile->get('title'));

		// Get a list of random albums from this profile
		$albums = $helper->getRandomAlbums();

		$this->set('albums', $albums);
		$this->set('stream', $stream);
		$this->set('stats', $stats);
		$this->set('randomMembers', $randomMembers);
		$this->set('totalUsers', $totalUsers);
		$this->set('profile', $profile);

		echo parent::display('site/profiles/default/default');
	}
}
