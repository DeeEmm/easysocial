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

class AlbumsWidgetsProfile extends SocialAppsWidgets
{
	/**
	 * Display user photos on the side bar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function sidebarBottom($user)
	{
		if (!$this->config->get('photos.enabled')) {
			return;
		}

		// Get the user params
		$params = $this->getUserParams($user->id);

		// Get the app params
		$appParam = $this->app->getParams();

		// User might not want to show this app in their profile.
		if (!$params->get('showalbums', $appParam->get('showalbums', true))) {
			return;
		}

		echo $this->getAlbums($user);

		if ($appParam->get('showfavourite', true)) {
			echo $this->getFavouriteAlbums($user);
		}

	}

	/**
	 * Display the list of favourite album
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getFavouriteAlbums($user)
	{
		// $user is the user of the profile viewed.
		$params = $this->getUserParams($user->id);
		$appParam = $this->app->getParams();

		$albums = array();

		// Load up albums model
		$model = ES::model('Albums');
		$sorting = $params->get('ordering', $appParam->get('ordering', 'latest'));

		$options = array(
			'order' => 'assigned_date',
			'direction' => $sorting == 'latest' ? 'desc' : 'asc'
		);

		$options['excludeblocked'] = 1;
		$userId = $user->id;

		$options['favourite'] = true;
		$options['userFavourite'] = $userId;

		// If displaying favourite album, we don't retrieve albums only from current logged in user
		$userId = '';

		// privacy lib
		$privacy = $user->getPrivacy();
		$results = $model->getAlbums($userId , SOCIAL_TYPE_USER, $options);

		if ($results) {
			foreach ($results as $item) {
				// we need to check the photo's album privacy to see if user allow to view or not.
				if ($privacy->validate('albums.view', $item->id,  SOCIAL_TYPE_ALBUM, $item->user_id)) {
					$albums[] = $item;
				}
			}
		}

		if (!$albums) {
			return;
		}

		// If sorting is set to random, then we shuffle the albums
		if ($sorting == 'random') {
			shuffle($albums);
		}

		$limit = $params->get('limit', $appParam->get('limit', 10));

		$this->set('appParams', $appParam);
		$this->set('params', $params);
		$this->set('user', $user);
		$this->set('albums', $albums);
		$this->set('limit', $limit);
		$this->set('privacy', $privacy);

		return parent::display('widgets/profile/favourite');
	}

	/**
	 * Display the list of photos a user has uploaded
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlbums($user)
	{
		$params = $this->getUserParams($user->id);
		$appParam = $this->app->getParams();

		$albums = array();

		// Load up albums model
		$model = ES::model('Albums');
		$sorting = $params->get('ordering', $appParam->get('ordering', 'latest'));

		$options = array(
			'order' => 'assigned_date',
			'direction' => $sorting == 'latest' ? 'desc' : 'asc'
		);

		$options['limit'] = $params->get('limit', $appParam->get('limit', 10));
		$options['excludeblocked'] = 1;
		$options['withCovers'] = true;

		$userId = $user->id;

		$results = $model->getAlbums($userId, SOCIAL_TYPE_USER, $options);

		if ($this->config->get('privacy.enabled')) {
			$privacy = $this->my->getPrivacy();

			if ($results) {
				foreach ($results as $item) {
					// we need to check the photo's album privacy to see if user allow to view or not.
					if ($privacy->validate('albums.view' , $item->id,  SOCIAL_TYPE_ALBUM, $item->user_id)) {
						$albums[] = $item;

					}
				}
			}
		}

		if (!$albums) {
			return;
		}

		// If sorting is set to random, then we shuffle the albums
		if ($sorting == 'random') {
			shuffle($albums);
		}

		$this->set('appParams', $appParam);
		$this->set('params', $params);
		$this->set('user', $user);
		$this->set('albums', $albums);

		return parent::display('widgets/profile/albums');
	}
}
