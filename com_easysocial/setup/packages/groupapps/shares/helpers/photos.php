<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(dirname(__FILE__) . '/abstract.php');

class SocialGroupSharesHelperPhotos extends SocialGroupSharesHelper
{
	/**
	 * Get the contents of the repost
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getContent()
	{
		// Load the photo object
		$photo = $this->getSource();

		if (!$photo->id) {
			return false;
		}

		// group access checking
		$group = ES::group($this->item->cluster_id);

		if (!$group) {
			return;
		}

		// Test if the viewer can really view the item
		if (!$group->canViewItem()) {
			return;
		}

		$message = $this->formatContent($this->share->content);

		$photoMessage = '';

		$params = ES::registry($this->share->params);
		$streamId = $params->get('streamId', '');

		if ($streamId) {
			$tbl = ES::table('stream');
			$tbl->load($streamId);

			$photoMessage = $this->formatContent($tbl->content);
		}

		$sourceActor = $photo->getPhotoCreator($group);

		$theme = ES::themes();
		$theme->set('photo', $photo);
		$theme->set('message', $message);
		$theme->set('photoMessage', $photoMessage);
		$theme->set('sourceActor', $sourceActor);


		$html = $theme->output('themes:/site/streams/repost/photos/preview');

		return $html;
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
			$photo = ES::table('Photo');
			$photo->load($this->share->uid);

			$items[$this->share->uid] = $photo;
		}

		return $items[$this->share->uid];
	}

	/**
	 * Generates the unique link id for the original reposted item
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getLink($sef = true)
	{
		$link = ESR::photos(array('id' => $this->item->contextId, 'sef' => $sef));

		return $link;
	}

	/**
	 * Get the stream title
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getStreamTitle()
	{
		$names = ES::string()->namesToStream($this->item->actors, true, 3);
		$actor = ES::user($this->item->getActor());

		// Load the photo object
		$photo = $this->getSource();

		$creator = ES::user($photo->user_id);

		$theme = ES::themes();
		$theme->set('names', $names);
		$theme->set('photo', $photo);
		$theme->set('creator', $creator);
		$theme->set('actor', $actor);

		$title = $theme->output('themes:/site/streams/repost/photos/title');

		return $title;
	}
}
