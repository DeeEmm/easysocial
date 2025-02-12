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

class AudiosWidgetsEvents extends SocialAppsWidgets
{
	/**
	 * Determines if the audios are enabled for events
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function enabled(SocialEvent $event)
	{
		$access = $event->getAccess();

		if (!$event->allowAudios()) {
			return false;
		}

		if (!$access->get('audios.create')) {
			return false;
		}

		return true;
	}

	/**
	 * Display admin actions for the event
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function eventAdminStart(SocialEvent $event)
	{
		if (!$this->enabled($event)) {
			return;
		}

		$audio = ES::audio($event->id, SOCIAL_TYPE_EVENT);

		$theme = ES::themes();
		$theme->set('audio', $audio);
		$theme->set('app', $this->app);

		echo $theme->output('themes:/site/audios/widgets/events/menu');
	}

	/**
	 * Display user audio on the side bar
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function sidebarBottom($eventId, $event)
	{
		if (!$this->enabled($event)) {
			return;
		}

		// Get recent audio
		$output = $this->getAudios($event);

		echo $output;
	}


	/**
	 * Display the list of audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAudios(SocialEvent &$event)
	{
		if (!$this->enabled($event)) {
			return;
		}

		$params = $this->getParams();

		// Determines the total number of audio to retrieve
		$limit = $params->get('audio_widget_listing_total', 10);

		$options = array();
		$options['uid'] = $event->id;
		$options['type'] = SOCIAL_TYPE_EVENT;
		$options['limit'] = $limit;

		// Get the audios for the event
		$model = ES::model('Audios');
		$audios = $model->getAudios($options);

		if (!$audios) {
			return;
		}

		$theme = ES::themes();
		$theme->set('audios', $audios);
		$theme->set('event', $event);

		return $theme->output('themes:/site/audios/widgets/events/recent');
	}
}
