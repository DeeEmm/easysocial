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

class AudiosWidgetsPages extends SocialAppsWidgets
{
	/**
	 * Display admin actions for the page
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function pageAdminStart(SocialPage $page)
	{
		$access = $page->getAccess();

		if (!$page->allowAudios()) {
			return;
		}

		if (!$access->get('audios.create')) {
			return;
		}

		$theme = ES::themes();
		$theme->set('page', $page);
		$theme->set('app', $this->app);

		echo $theme->output('themes:/site/audios/widgets/pages/widget.menu');
	}

	/**
	 * Display user audio on the side bar
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function sidebarBottom($pageId, SocialPage $page)
	{
		// Get recent audio
		$output = $this->getAudios($page);

		echo $output;
	}

	/**
	 * Display the list of audio albums
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAudios(SocialPage &$page)
	{
		$params = $this->getParams();

		// If the app is disabled, do not continue
		if (!$params->get('widget_audios', true)) {
			return;
		}

		$limit = $params->get('limit', 10);

		$options = array();
		$options['uid'] = $page->id;
		$options['type'] = SOCIAL_TYPE_PAGE;

		$model = ES::model('Audios');
		$audios = $model->getAudios($options);

		if (!$audios) {
			return;
		}

		$theme = ES::themes();
		$theme->set('audios', $audios);
		$theme->set('page', $page);

		return $theme->output('themes:/site/audios/widgets/pages/audios');
	}
}
