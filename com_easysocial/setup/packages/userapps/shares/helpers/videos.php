<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialSharesHelperVideos extends SocialSharesHelper
{
	/**
	 * Gets the content of the repost
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getContent()
	{
		// Load the video object
		$video = $this->getSource();

		// Video is no longer exists. #3737
		if (!$video->id) {
			return false;
		}

		// Determines if the current user is allowed to view
		$privacy = $this->my->getPrivacy();

		if (!$privacy->validate('videos.view', $video->id, SOCIAL_TYPE_VIDEOS, $video->user_id)) {
			return $this->restricted();
		}

		$message = $this->formatContent($this->share->content);

		$uid = '';
		$utype = '';

		if ($video->type != SOCIAL_TYPE_USER) {
			$uid = $video->uid;
			$utype = $video->type;
		}

		$theme = ES::themes();
		$theme->set('video', $video);
		$theme->set('message', $message);

		// handle for the video category permalink for the cluster as well
		$theme->set('uid', $uid);
		$theme->set('utype', $utype);

		// This variable for cluster
		$theme->set('sourceActor', '');

		$preview = $theme->output('themes:/site/streams/repost/videos/preview');

		return $preview;
	}

	/**
	 * Gets the repost source message
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSource()
	{
		static $items = array();

		if (!isset($items[$this->share->uid])) {
			$videoTbl = ES::table('Video');
			$videoTbl->load($this->share->uid);

			$video = ES::video($videoTbl);

			$items[$this->share->uid] = $video;
		}

		return $items[$this->share->uid];
	}

	/**
	 * Generates the unique link id for the original reposted item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLink($sef = true)
	{
		$link = ESR::videos(array('id' => $this->item->contextId, 'sef' => $sef));

		return $link;
	}

	/**
	 * Get the stream title
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getStreamTitle()
	{
		// Load the video
		$video = $this->getSource();
		$creator = ES::user($video->user_id);

		// Since it may be aggregated
		$names = ES::string()->namesToStream($this->item->actors, true, 3);

		// Retrieve the repost user data
		$actor = $this->item->actors[0];
		$link = $this->getLink();

		$theme = ES::themes();
		$theme->set('video', $video);
		$theme->set('creator', $creator);
		$theme->set('names', $names);
		$theme->set('link', $link);
		$theme->set('actor', $actor);

		$title = $theme->output('themes:/site/streams/repost/videos/title');

		return $title;
	}
}
