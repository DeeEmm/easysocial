<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewVideos extends EasySocialSiteView
{
	/**
	 * Renders the oembed output for a video
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function item()
	{
		$id = $this->input->get('id', 0, 'int');
		$video = ES::video(null, null, $id);
		$duration = '';

		// Ensure that the viewer can really view the video
		if (!$video->isViewable()) {
			echo 'Unauthorized'; die;
		}

		// Ensure that video have duration
		if ($video->duration) {
			$duration = $video->duration;
		}

		$this->set('thumbnail', $video->getThumbnail());
		$this->set('title', $video->title);
		$this->set('html', $video->getEmbedCodes());
		$this->set('embedUrl', $video->getPermalink(true, null, null, false, true, false) . '&format=embed');
		$this->set('type', SOCIAL_TYPE_VIDEO);
		$this->set('duration', $duration);

		parent::display();
	}
}
