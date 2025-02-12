<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(dirname(__FILE__) . '/abstract.php');

class SocialPageSharesHelperAlbums extends SocialPageSharesHelper
{	
	/**
	 * Gets the content of the stream
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getContent()
	{
		$message = $this->formatContent($this->share->content);
		
		$album = $this->getSource();

		$page = $this->item->getCluster();

		// Get user's privacy.
		$privacy = $this->my->getPrivacy();

		if (!$privacy->validate('albums.view', $album->id, SOCIAL_TYPE_ALBUM, $album->uid)) {
			return false;
		}

		// Since only admin can create album in page,
		// we directly set Page as the album author
		$sourceActor = $page;

		$theme = ES::themes();
		$theme->set('album', $album);
		$theme->set('message', $message);
		$theme->set('sourceActor', $sourceActor);

		$html = $theme->output('themes:/site/streams/repost/albums/page/preview');

		return $html;
	}

	/**
	 * Generates a link id for the original reposted item
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getLink($sef = true)
	{
		$link = ESR::albums(array('id' => $this->item->contextId, 'sef' => $sef));

		return $link;
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
			$album = ES::table('Album');
			$album->load($this->share->uid);

			$items[$this->share->uid] = $album;
		}

		return $items[$this->share->uid];
	}

	/**
	 * Gets the stream title
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getStreamTitle()
	{
		// Load the album
		$album = $this->getSource();

		$page = $this->item->getCluster();

		// Since only admin can create album in page,
		// we directly set Page as the album author
		$target = $page;

		$actor = $this->item->getPostActor($page);

		$theme = ES::themes();
		$theme->set('actor', $actor);
		$theme->set('album', $album);
		$theme->set('target', $target);

		$html = $theme->output('themes:/site/streams/repost/albums/page/title');

		return $html;
	}
}
