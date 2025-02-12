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

class PhotosWidgetsProfile extends SocialAppsWidgets
{
	/**
	 * Determines if photos is enabled
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function isEnabled()
	{
		static $enabled = null;

		if (is_null($enabled)) {
			$enabled = true;

			$params = $this->getParams();

			if (!$this->config->get('photos.enabled')) {
				$enabled = false;
				return $enabled;
			}

			// User might not want to show this app in their profile.
			if (!$params->get('showphotos')) {
				$enabled = false;
				return $enabled;
			}
		}

		return $enabled;
	}

	/**
	 * Displays user friends on mobile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($user)
	{
		if (!$this->isEnabled()) {
			return;
		}

		// Enforce hard limit for mobile
		$limit = 6;

		echo $this->getPhotos($user, $limit);
	}

	/**
	 * Display user photos on the side bar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function sidebarBottom($user)
	{
		if (!$this->isEnabled()) {
			return;
		}

		echo $this->getPhotos($user);
	}


	/**
	 * Display the list of photos a user has uploaded
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPhotos($user, $limit = null)
	{
		$params = $this->getParams();

		// Get photos model
		$model = ES::model('Photos');

		// Get the photo limit from the app setting
		if (!$limit) {
			$limit = $params->get('photo_widget_listing_total', 20);
		}

		// limit <- get from the getPhotos function
		$options = array('uid' => $user->id, 'type' => SOCIAL_TYPE_USER, 'limit' => $limit);

		// Set the ordering
		$ordering = $params->get('ordering', 'latest');

		if ($ordering == 'latest') {
			$options['ordering'] = 'created';
			$options['sorting'] = 'DESC';
		}

		if ($ordering == 'oldest') {
			$options['ordering'] = 'created';
			$options['sorting'] = 'ASC';
		}

		$photos = $model->getPhotos($options);

		$this->set('params', $params);
		$this->set('limit', $limit);
		$this->set('user', $user);
		$this->set('photos', $photos);

		return parent::display('widgets/profile/photos');
	}
}
