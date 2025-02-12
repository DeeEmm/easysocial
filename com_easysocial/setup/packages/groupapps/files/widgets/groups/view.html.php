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

class FilesWidgetsGroups extends SocialAppsWidgets
{
	/**
	 * Display files widget on the side bar
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function sidebarBottom($groupId)
	{
		// Get the params of the group
		$params = $this->app->getParams();

		// If the widget has been disabled we shouldn't display anything
		if (!$params->get('widget', true)) {
			return;
		}

		$group = ES::group($groupId);

		if (!$group->canAccessFiles()) {
			return;
		}

		$theme = ES::themes();
		$limit = $params->get('widget_total' , 5);

		$model = ES::model('Files');
		$options = array('limit' => $limit);
		$files = $model->getFiles($group->id, SOCIAL_TYPE_GROUP, $options);

		if (!$files) {
			return;
		}

		$theme->set('files', $files);
		$theme->set('group', $group);

		echo $theme->output('themes:/apps/group/files/widgets/files');
	}
}
