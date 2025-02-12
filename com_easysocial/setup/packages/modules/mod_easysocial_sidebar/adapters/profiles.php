<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialSidebarProfiles extends SocialSidebarAbstract
{
	/**
	 * Renders the output from the sidebar
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function render()
	{
		$layout = $this->input->get('layout', '', 'cmd');

		// We do not want to render anything on the item layout
		if ($layout == 'item') {
			return $this->renderItem();
		}
	}

	/**
	 * Render single profile type page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function renderItem()
	{
		$helper = ES::viewHelper('Profiles', 'Item');

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

		// Get a list of random albums from this profile
		$albums = $helper->getRandomAlbums();

		$path = $this->getTemplatePath('profiles');
		require($path);
	}
}
