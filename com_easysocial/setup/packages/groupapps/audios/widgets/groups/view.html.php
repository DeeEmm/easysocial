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

class AudiosWidgetsGroups extends SocialAppsWidgets
{
	/**
	 * Display admin actions for the group
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function groupAdminStart(SocialGroup $group)
	{
		$access = $group->getAccess();

		if (!$group->allowAudios()) {
			return;
		}

		if (!$access->get('audios.create')) {
			return;
		}

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('app', $this->app);

		echo $theme->output('themes:/site/audios/widgets/groups/widget.menu');
	}

	/**
	 * Display group audio on the side bar
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function sidebarBottom($groupId, $group)
	{
		// Get recent albums
		$output = $this->getAudios($group);

		echo $output;
	}

	/**
	 * Display the list of audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAudios(SocialGroup &$group)
	{
		$params = $this->getParams();

		// If the app is disabled, do not continue
		if (!$params->get('widget_audios', true)) {
			return;
		}

		$limit = $params->get('audio_widget_listing_total', 10);

		$options = array();
		$options['uid'] = $group->id;
		$options['type'] = SOCIAL_TYPE_GROUP;
		$options['limit'] = $limit;

		$model = ES::model('Audios');
		$audios = $model->getAudios($options);

		if (!$audios) {
			return;
		}

		$theme = ES::themes();
		$theme->set('audios', $audios);
		$theme->set('group', $group);

		echo $theme->output('themes:/site/audios/widgets/groups/audios');
	}
}
