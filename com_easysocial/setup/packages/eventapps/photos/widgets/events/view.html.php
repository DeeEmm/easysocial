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

class PhotosWidgetsEvents extends SocialAppsWidgets
{
	/**
	 * Determines if photos functionality should be enabled for groups
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function isEnabled($event)
	{
		static $enabled = null;

		if (is_null($enabled)) {
			$enabled = true;

			if (!$this->config->get('photos.enabled')) {
				$enabled = false;
				return $enabled;
			}

			// Check if category is allowing photos
			$category = $event->getCategory();
			$categoryAcl = $category->getAcl();

			if (!$categoryAcl->get('photos.enabled', true)) {
				$enabled = false;
				return $enabled;
			}

			// Check if group is allowing photos
			$params = $event->getParams();

			if (!$params->get('photo.albums', true)) {
				$enabled = false;
				return $enabled;
			}
		}

		return $enabled;
	}

	/**
	 * Displays the action for albums
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function eventAdminStart($event)
	{
		if ($this->app->state == SOCIAL_STATE_UNPUBLISHED || !$this->isEnabled($event)) {
			return;
		}

		$this->set('event', $event);
		$this->set('app', $this->app);

		echo parent::display('widgets/menu');
	}

	/**
	 * Renders the photo albums for mobile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($eventId, $event)
	{
		// Enforce hard limit on mobile device
		$limit = 6;

		return $this->sidebarBottom($eventId, $event, $limit);
	}

	/**
	 * Display user photos on the side bar
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function sidebarBottom($eventId, $event, $limit = null)
	{
		$event = ES::event($eventId);

		if (!$this->isEnabled($event)) {
			return;
		}

		$output = $this->getPhotos($event, $limit);

		echo $output;
	}

	/**
	 * Displays the list of photos from the group
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPhotos(&$event, $limit = null)
	{
		if (!$this->isEnabled($event)) {
			return;
		}

		$params = $event->getParams();

		// Check if the photos widget should appear
		if (!$params->get('widgets_album', true)) {
			return;
		}

		if (!$limit) {
			$limit = $params->get('widgets_album_limit', 10);
		}

		$options = array(
			'uid' => $event->id,
			'type' => SOCIAL_TYPE_EVENT,
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
		$this->set('event', $event);

		return parent::display('widgets/photos');
	}

	/**
	 * We no longer display albums widget. Use @getPhotos instead
	 *
	 * @deprecated	3.0.0
	 */
	public function getAlbums(&$event)
	{
		return $this->getPhotos($event);
	}
}
