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

class PhotosWidgetsPages extends SocialAppsWidgets
{
	/**
	 * Determines if photos functionality should be enabled for the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function isEnabled($page)
	{
		static $enabled = null;

		if (is_null($enabled)) {
			$enabled = true;

			if (!$this->config->get('photos.enabled')) {
				$enabled = false;
				return $enabled;
			}

			// Check if category is allowing photos
			$category = $page->getCategory();
			$categoryAcl = $category->getAcl();

			if (!$categoryAcl->get('photos.enabled', true)) {
				$enabled = false;
				return $enabled;
			}

			// Check if page is allowing photos
			$params = $page->getParams();

			if (!$params->get('photo.albums', true)) {
				$enabled = false;
				return $enabled;
			}
		}

		return $enabled;
	}

	public function pageAdminStart($page)
	{
		if (!$this->isEnabled($page)) {
			return;
		}

		$this->set('page', $page);
		$this->set('app', $this->app);

		echo parent::display('widgets/widget.menu');
	}

	/**
	 * Renders the recent photos on mobile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($pageId, $page)
	{
		// Enforce hard limit on mobile device
		$limit = 6;

		$output = $this->getPhotos($page, $limit);

		echo $output;
	}

	/**
	 * Display user photos on the side bar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function sidebarBottom($pageId, SocialPage $page)
	{
		$output = $this->getAlbums($page);

		echo $output;
	}

	/**
	 * Displays the list of photos from the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPhotos(&$page, $limit = null)
	{
		if (!$this->isEnabled($page)) {
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
			'uid' => $page->id,
			'type' => SOCIAL_TYPE_PAGE,
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

		$this->set('page', $page);
		$this->set('photos', $photos);

		return parent::display('widgets/photos');
	}

	/**
	 * We no longer display albums widget. Use @getPhotos instead
	 *
	 * @deprecated	3.0.0
	 */
	public function getAlbums(SocialPage &$page)
	{
		return $this->getPhotos($page);
	}
}
