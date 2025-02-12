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

class EasySocialViewProfilesItemHelper extends EasySocial
{
	/**
	 * Determines the profile type that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getActiveProfiles()
	{
		static $profile = null;

		if (is_null($profile)) {

			$id = $this->input->get('id', 0, 'int');

			// Get the profile object
			$profile = ES::table('Profile');
			$profile->load($id);

			if (!$id || !$profile->id || !$profile->isPublished()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_404_PROFILE_NOT_FOUND'));
			}
		}

		return $profile;
	}

	/**
	 * Determines whether need to show members that is currently being viewed
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getMembers()
	{
		static $randomMembers = null;

		if (is_null($randomMembers)) {

			$profile = $this->getActiveProfiles();

			$model = ES::model('Profiles');

			$randomMembers = array();

			// If user does not have community access, we should not display the random members
			if (!$profile->community_access) {
				return $randomMembers;
			}

			$includeAdmin = $this->config->get('users.listings.admin');
			$randomMembers = $model->getMembers($profile->id, array('randomize' => true, 'limit' => 20, 'includeAdmin' => $includeAdmin));
		}

		return $randomMembers;
	}

	/**
	 * Retrieve total of member count
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getMembersCount()
	{
		static $totalUsers = null;

		if (is_null($totalUsers)) {

			$profiles = $this->getActiveProfiles();

			$totalUsers = $profiles->getMembersCount();
		}

		return $totalUsers;
	}

	/**
	 * Retrieve statistics of user registration for this profile type
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getRegistrationStats()
	{
		static $stats = null;

		if (is_null($stats)) {

			$profile = $this->getActiveProfiles();

			$model = ES::model('Profiles');
			$stats = $model->getRegistrationStats($profile->id);

			$stats = $stats->profiles[0]->items;
		}

		return $stats;
	}

	/**
	 * Retrieve stream item for this profile type
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getStreamData()
	{
		static $stream = null;

		if (is_null($stream)) {

			$profile = $this->getActiveProfiles();

			// Get the stream for this profile
			$startlimit = $this->input->get('limitstart', 0);
			$stream = ES::stream();

			$options = array('profileId' => $profile->id);

			if ($startlimit) {
				$options['startlimit'] = $startlimit;
			}

			$stream->get($options);
		}

		return $stream;
	}

	/**
	 * Retrieve a list of albums for this profile type
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getRandomAlbums()
	{
		static $albums = null;

		if (is_null($albums)) {

			$profile = $this->getActiveProfiles();

			$model = ES::model('Profiles');

			// Get a list of random albums from this profile
			$albums = $model->getRandomAlbums(array('core' => false, 'withCovers' => true, 'profileId' => $profile->id, 'limit' => 5));
		}

		return $albums;
	}	
}
