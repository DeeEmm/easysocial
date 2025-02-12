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

class SocialPageSharesHelperPhotos extends SocialPageSharesHelper
{	
	/**
	 * Get the content of the repost 
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getContent()
	{
		$message = $this->formatContent($this->share->content);

		$photo = $this->getSource();

		// page access checking
		$page = $this->item->getCluster();

		if (!$page) {
			return;
		}

		// Test if the viewer can really view the item
		if (!$page->canViewItem()) {
			return;
		}

		$sourceActor = $photo->getPhotoCreator($page);

		$theme = ES::themes();
		$theme->set('photo', $photo);
		$theme->set('message', $message);
		$theme->set('sourceActor', $sourceActor);

		$html = $theme->output('themes:/site/streams/repost/photos/page/preview');

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
	 * @param   string
	 * @return
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
	 * @param   string
	 * @return
	 */
	public function getStreamTitle()
	{
		$photo = $this->getSource();

		$page = $this->item->getCluster();

		$actor = $this->item->getPostActor($page);
		
		$target = $photo->getPhotoCreator($page);

		$theme = ES::themes();
		$theme->set('actor', $actor);
		$theme->set('photo', $photo);
		$theme->set('target', $target);

		$title = $theme->output('themes:/site/streams/repost/photos/page/title');

		return $title;
	}
}
