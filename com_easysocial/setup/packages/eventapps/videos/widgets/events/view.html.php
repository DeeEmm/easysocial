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

class VideosWidgetsEvents extends SocialAppsWidgets
{
	/**
	 * Determines if the videos are enabled for events
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function enabled(SocialEvent $event)
	{
		$params = $event->getParams();

		if (!$event->allowVideos()) {
			return false;
		}

		if (!$params->get('videos', true)) {
			return false;
		}

		$access = $event->getAccess();

		if (!$access->get('videos.create', true)) {
			return false;
		}

		return true;
	}

	/**
	 * Display admin actions for the event
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function eventAdminStart(SocialEvent $event)
	{
		if (!$this->enabled($event)) {
			return;
		}

		$video = ES::video($event->id, SOCIAL_TYPE_EVENT);

		$theme = ES::themes();
		$theme->set('video', $video);
		$theme->set('app', $this->app);

		echo $theme->output('themes:/site/videos/widgets/events/menu');
	}

	/**
	 * Display user photos on the side bar
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function sidebarBottom($eventId, $event)
	{
		if (!$this->enabled($event)) {
			return;
		}

		// Get recent albums
		$output = $this->getVideos($event);

		echo $output;
	}


	/**
	 * Display the list of photo albums
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getVideos(SocialEvent &$event)
	{
		if (!$this->enabled($event)) {
			return;
		}

		$params = $this->getParams();

		$model = ES::model('Videos');

		// Determines the total number of albums to retrieve
		$limit = $params->get('video_widget_listing_total', 5);

		$options = array();
		$options['uid'] = $event->id;
		$options['type'] = SOCIAL_TYPE_EVENT;
		$options['limit'] = $limit;

		// Get the videos for the event
		$videos = $model->getVideos($options);
		$layout = $params->get('layout', 'card');

		$theme = ES::themes();
		$theme->set('videos', $videos);
		$theme->set('event', $event);
		$theme->set('layout', $layout);

		return $theme->output('themes:/site/videos/widgets/events/recent');
	}
}
