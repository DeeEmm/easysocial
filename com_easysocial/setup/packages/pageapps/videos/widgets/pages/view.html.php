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

class VideosWidgetsPages extends SocialAppsWidgets
{
	/**
	 * Display admin actions for the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function pageAdminStart(SocialPage $page)
	{
		if (!$page->allowVideos()) {
			return;
		}

		// Ensure that video creation is allowed
		if (!$page->getCategory()->getAcl()->get('videos.create', true)) {
			return;
		}

		$theme = ES::themes();
		$theme->set('page', $page);
		$theme->set('app', $this->app);

		echo $theme->output('themes:/apps/page/videos/widgets/widget.menu');
	}

	/**
	 * Display user video on the side bar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function sidebarBottom($pageId, SocialPage $page)
	{
		// Get recent albums
		$output = $this->getVideos($page);

		echo $output;
	}

	/**
	 * Display the list of video albums
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getVideos(SocialPage &$page)
	{
		$params = $this->getParams();

		// If the app is disabled, do not continue
		if (!$params->get('widget_videos', true)) {
			return;
		}

		$limit = $params->get('video_widget_listing_total', 5);

		$options = array();
		$options['uid'] = $page->id;
		$options['type'] = SOCIAL_TYPE_PAGE;
		$options['limit'] = $limit;

		$model = ES::model('Videos');
		$videos = $model->getVideos($options);

		if (!$videos) {
			return;
		}

		$layout = $params->get('layout', 'card');

		$this->set('layout', $layout);
		$this->set('videos', $videos);
		$this->set('page', $page);

		return parent::display('widgets/videos');
	}
}
