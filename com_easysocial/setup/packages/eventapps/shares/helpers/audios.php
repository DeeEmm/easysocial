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

class SocialEventSharesHelperAudios extends SocialEventSharesHelper
{
	/**
	 * Gets the content of the repost
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getContent()
	{
		// Load the audio object
		$audio = $this->getSource();

		// Audio is no longer exists. #3737
		if (!$audio->id) {
			return false;
		}

		// Get user's privacy.
		$my = ES::user();
		$privacy = ES::privacy($my->id);

		// Determines if the current user is allowed to view
		if (!$privacy->validate('audios.view', $audio->id, SOCIAL_TYPE_AUDIOS, $audio->user_id)) {
			return $this->restricted();
		}

		$message = $this->formatContent($this->share->content);

		// Retireve the cluster name
		$sourceActor = $this->item->getCluster();

		$theme = ES::themes();
		$theme->set('audio', $audio);
		$theme->set('message', $message);
		$theme->set('sourceActor', $sourceActor);

		$preview = $theme->output('themes:/site/streams/repost/audios/preview');

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
			$audioTbl = ES::table('Audio');
			$audioTbl->load($this->share->uid);

			$audio = ES::audio($audioTbl);

			$items[$this->share->uid] = $audio;
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
		$link = ESR::audios(array('id' => $this->item->contextId, 'sef' => $sef));

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
		// Load the audio
		$audio = $this->getSource();
		$creator = ES::user($audio->uid);

		// Since it may be aggregated
		$names = ES::string()->namesToStream($this->item->actors, true, 3);

		// Retrieve the repost user data
		$actor = $this->item->actors[0];
		$link = $this->getLink();

		$theme = ES::themes();
		$theme->set('audio', $audio);
		$theme->set('creator', $creator);
		$theme->set('names', $names);
		$theme->set('link', $link);
		$theme->set('actor', $actor);

		$title = $theme->output('themes:/site/streams/repost/audios/title');

		return $title;
	}
}
