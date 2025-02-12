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

class SocialPageSharesHelperStream extends SocialPageSharesHelper
{	
	/**
	 * Gets the content of the repost
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getContent()
	{
		$message = $this->formatContent($this->share->content);
		$preview = "";
		$content = "";
		$title = "";

		// $stream = ES::stream();
		// $data = $stream->getItem($this->share->uid);

		// if ($data !== true && !empty($data)) {
		// 	$title = $data[0]->title;
		// 	$content = $data[0]->content;
		// 	$content = $content . $data[0]->getMetaHtml();

		// 	if (isset($data[0]->preview) && $data[0]->preview) {
		// 		$preview = $data[0]->preview;
		// 	}
		// }

		$stream = ES::stream();
		$data = $stream->getItem($this->share->uid, '', '', false, array('perspective' => 'dashboard', 'disableActions' => true));

		if ($data === true || !$data) {
			return $this->restricted();
		}

		// Get the content
		$data = $data[0];
		$content = $data->content;
		$preview = $data->preview;

		$content = $content .  $data->getMetaHtml();

		// Source of repost
		$sourceActor = $this->getSourceActor();

		// Get the real source of the repost stream
		$stream = $this->getSource();
		$actor = ES::user($stream->actor_id);

		// Exclude site admin  stream repost items
		if ($this->config->get('stream.exclude.admin') && !ES::user()->isSiteAdmin() && $actor->isSiteAdmin()) {
			return $this->restricted();
		}

		$theme = ES::themes();

		$theme->set('message', $message);
		$theme->set('content', $content);
		$theme->set('preview', $preview);
		$theme->set('title', $title);
		$theme->set('sourceActor', $sourceActor);

		$html = $theme->output('themes:/site/streams/repost/stream/preview');

		return $html;
	}

	/**
	 * Generates the unique id for the original reposted item
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getLink($sef = true)
	{
		$link = ESR::stream(array('layout' => 'item', 'id' => $this->item->contextId, 'sef' => $sef));

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
			// Load the stream
			$stream = ES::table('Stream');
			$stream->load($this->share->uid);

			$items[$this->share->uid] = $stream;
		}

		return $items[$this->share->uid];
	}

	/**
	 * Retrieves the source text
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSourceActor()
	{
		$stream = $this->getSource();
		$page = $this->item->getCluster();

		$actor = ES::user($stream->actor_id);

		if ($stream->post_as == SOCIAL_TYPE_PAGE) {
			return $page;
		} else {
			return $actor;
		}
	}

	/**
	 * Retrieve the title of the stream
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getStreamTitle()
	{
		// Load the stream
		$stream = $this->getSource();

		// If stream cannot be loaded, skip this altogether
		if (!$stream->id) {
			return;
		}

		// Load the page object
		$page = $this->item->getCluster();

		// Build the permalink to the stream item
		$link = ESR::stream(array('layout' => 'item', 'id' => $this->share->uid));

		// Get the target user.
		$target = $this->getSourceActor();

		// set the actor alias for this stream item
		$actor = $this->item->getPostActor($page);

		$theme = ES::themes();

		$theme->set('actor', $actor);
		$theme->set('link', $link);
		$theme->set('target', $target);

		$title = $theme->output('themes:/site/streams/repost/stream/title');

		return $title;
	}


}
