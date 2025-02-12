<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(dirname(__FILE__) . '/abstract.php');

class SocialEventSharesHelperStream extends SocialEventSharesHelper
{
	public function getContent()
	{
		$source = explode('.', $this->share->element);
		$element = $source[0];
		$event = $source[1];

		$message = $this->formatContent($this->share->content);
		$preview = "";
		$content = "";
		$title = "";

		$stream = ES::stream();
		$data = $stream->getItem($this->share->uid);

		if ($data !== true && !empty($data)) {
			$title = $data[0]->title;
			$content = $data[0]->content;

			if (isset($data[0]->preview) && $data[0]->preview) {
				$preview = $data[0]->preview;
			}
		}

		$sourceActor = $this->getSourceActor();

		$theme = ES::themes();
		$theme->set('message', $message);
		$theme->set('content', $content);
		$theme->set('preview', $preview);
		$theme->set('title', $title);
		$theme->set('sourceActor', $sourceActor);


		// $html = $theme->output('apps/event/shares/streams/stream/content');

		$html = $theme->output('themes:/site/streams/repost/stream/preview');

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
		$cluster = $this->item->getCluster();

		$actor = ES::user($stream->actor_id);

		if ($stream->post_as == SOCIAL_TYPE_PAGE) {
			$page = $cluster->getPage();
			return $page;
		} else {
			return $actor;
		}
	}

	public function getLink($sef = true)
	{
		$link = ESR::stream(array('layout' => 'item', 'id' => $this->item->contextId, 'sef' => $sef));

		return $link;
	}

	/**
	 * Retrieve the title of the stream
	 *
	 * @since    1.2
	 * @access    public
	 */
	public function getStreamTitle()
	{
		// Get the actors
		$actors = $this->item->actors;

		// Get the source id
		$sourceId = $this->share->uid;

		// Load the stream
		$stream = ES::table('Stream');
		$stream->load($sourceId);

		// If stream cannot be loaded, skip this altogether
		if (!$stream->id) {
			return;
		}


		$cluster = $this->item->getCluster();


		// Build the permalink to the stream item
		$link = FRoute::stream(array('layout' => 'item', 'id' => $sourceId));

		// Get the target user.
		// $target = ES::user($stream->actor_id);
		// $actor = $actors[0];

		// Get the target user.
		$target = $this->getSourceActor();


		$tmpActor = $cluster;
		if ($cluster->isPageEvent()) {
			$tmpActor = $cluster->getPage();
		}

		if ($cluster->isGroupEvent()) {
			$tmpActor = $cluster->getGroup();
		}


		// set the actor alias for this stream item
		$actor = $this->item->getPostActor($tmpActor);


		$theme = ES::themes();

		$theme->set('actor', $actor);
		$theme->set('link', $link);
		$theme->set('target', $target);

		$title = $theme->output('themes:/site/streams/repost/stream/title');

		return $title;
	}
}
