<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PhotosWidgetsGroups extends SocialAppsWidgets
{
	/**
	 * Determines if photos functionality should be enabled for groups
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function isEnabled($group)
	{
		static $enabled = null;

		if (is_null($enabled)) {
			$enabled = true;

			if (!$this->config->get('photos.enabled')) {
				$enabled = false;
				return $enabled;
			}

			// Check if category is allowing photos
			$category = $group->getCategory();
			$categoryAcl = $category->getAcl();

			if (!$categoryAcl->get('photos.enabled', true)) {
				$enabled = false;
				return $enabled;
			}

			// Check if group is allowing photos
			$params = $group->getParams();

			if (!$params->get('photo.albums', true)) {
				$enabled = false;
				return $enabled;
			}
		}

		return $enabled;
	}

	public function groupAdminStart($group)
	{
		if (!$this->isEnabled($group)) {
			return;
		}

		$this->set('group', $group);
		$this->set('app', $this->app);

		echo parent::display('widgets/widget.menu');
	}

	/**
	 * Renders the recent photos widget on mobile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($groupId, $group)
	{
		// Enforce hard limit on mobile device
		$limit = 6;

		$output = $this->getPhotos($group, $limit);

		echo $output;
	}

	/**
	 * Display recent photos on the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sidebarBottom($groupId, $group)
	{
		$output = $this->getPhotos($group);

		echo $output;
	}

	/**
	 * Displays the list of photos from the group
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPhotos(&$group, $limit = null)
	{
		if (!$this->isEnabled($group)) {
			return;
		}

		$params = $this->getParams();

		// Check if the photos widget should appear
		if (!$params->get('widgets_album', true)) {
			return;
		}

		if (!$limit) {
			$limit = $params->get('limit', 10);
		}

		$options = array(
			'uid' => $group->id,
			'type' => SOCIAL_TYPE_GROUP,
			'limit' => $limit,
			'ordering' => 'created',
			'sorting' => 'DESC',
			'privacy' => true
		);

		$model = ES::model('Photos');
		$photos = $model->getPhotos($options);

		if (!$photos) {
			return;
		}

		$this->set('photos', $photos);
		$this->set('group', $group);

		return parent::display('widgets/photos');
	}

	/**
	 * We no longer display albums widget. Use @getPhotos instead
	 *
	 * @deprecated	3.0.0
	 */
	public function getAlbums(&$group)
	{
		return $this->getPhotos($group);
	}
}
