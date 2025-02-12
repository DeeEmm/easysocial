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

class AudiosWidgetsProfile extends SocialAppsWidgets
{
	/**
	 * Display user audios on the side bar
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function sidebarBottom($user)
	{
		// Get the user params
		$params = $this->getParams();

		if (!$this->config->get('audio.enabled') || !$params->get('showaudios', true)) {
			return;
		}

		echo $this->getAudios($user, $params);
	}


	/**
	 * Display the list of audios a user has uploaded/shared
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAudios($user, $params)
	{
		// Get audio model
		$model = ES::model('Audios');

		// Get the audio limit from the app setting
		$limit = $params->get('audio_widget_listing_total', 5);
		$sort = $params->get('ordering', 'latest');

		$options = array('userid' => $user->id, 'filter' => SOCIAL_TYPE_USER, 'maxlimit' => $limit, 'sort' => $sort);
		$audios = $model->getAudios($options);

		if (!$audios) {
			return;
		}

		$theme = ES::themes();
		$theme->set('params', $params);
		$theme->set('limit', $limit);
		$theme->set('user', $user);
		$theme->set('audios', $audios);

		return $theme->output('themes:/site/audios/widgets/profile/audios');
	}
}
