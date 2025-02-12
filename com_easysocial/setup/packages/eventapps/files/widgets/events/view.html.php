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

class FilesWidgetsEvents extends SocialAppsWidgets
{
	/**
	 * Display files widget on the side bar
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function sidebarBottom($eventId)
	{
		$event = ES::event($eventId);
		$params = $this->app->getParams();

		if (!$params->get('widget', true) || !$event->canAccessFiles()) {
			return;
		}

		$limit = $params->get('widget_total', 5);
		$model = ES::model('Files');
		$options = array('limit' => $limit);
		$files = $model->getFiles($event->id, SOCIAL_TYPE_EVENT, $options);

		if (!$files) {
			return;
		}

		$theme = ES::themes();
		$theme->set('files', $files);
		$theme->set('event', $event);

		echo $theme->output('themes:/apps/event/files/widgets/files');
	}
}
