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
ES::import('apps:/event/links/helper');

class SocialEventAppLinks extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_LINKS) {
			return;
		}

		// Since the data is being tempered by unwanted guest,
		// we can assume that anything beyond here is no longer accessible.
		return false;
	}

	public function onNotificationLoad(SocialTableNotification &$item)
	{
		return false;
	}

	/**
	 * Fixed legacy issues where the app is displayed on apps list of a event.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function appListing($view, $id, $type)
	{
		return false;
	}

	/**
	 * Processes a saved story.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		// Get the link information from the request
		$link = $this->input->get('links_url', '', 'default');
		$title = $this->input->get('links_title', '', 'default');
		$content = $this->input->get('links_description', '', 'default');
		$image = $this->input->get('links_image', '', 'default');

		// If there's no data, we don't need to store in the assets table.
		if (empty($title) && empty($content) && empty($image)) {
			return;
		}

		if ($streamItem->isScheduled()) {
			// Lets create the scheduled item.
			$scheduled = ES::Scheduler();
			$scheduled->create($streamItem->uid, $template);
		}

		$registry = ES::registry();
		$registry->set('title', $title);
		$registry->set('content', $content);
		$registry->set('image', $image);
		$registry->set('link', $link);

		return true;
	}

	/**
	 * Generates the stream item for REST API
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		if ($item->context != 'links') {
			return;
		}

		// Get the event
		$event = $item->getCluster();

		if (!$event || !$event->canViewItem()) {
			return;
		}

		// Check for privacy
		$privacy = $viewer->getPrivacy();

		// Apply stream actions
		$item->likes = ES::likes($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, $item->uid, array('clusterId' => $item->cluster_id));
		$item->comments = ES::comments($item->uid, $item->context, $item->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => $item->getPermalink(false, false, false), 'clusterId' => $item->cluster_id), $item->uid);
		$item->repost = ES::repost($item->uid, SOCIAL_TYPE_STREAM, SOCIAL_APPS_GROUP_EVENT);
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		$assets = $item->getAssets();

		if (empty($assets)) {
			return;
		}

		$access = $event->getAccess();
		if ($viewer->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $viewer->id)) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		$assets = $assets[0];

		// Load the link object
		$link = ES::table('Link');
		$link->loadByLink($assets->get('link'));

		// Get the oembed object
		$oembed = $link->getOembed();

		// let's rerun the crawl if there are no data in cache
		if (!$oembed && $this->config->get('links.cache.data')) {
			$url = $assets->get('link');

			$linkLib = ES::links($url);

			// Get the crawler
			$crawler = ES::crawler();
			$result = $crawler->scrape($url);

			// Now we need to cache the link so that the next time it load, we don't crawl it again.
			$linkLib->cache($result);

			// Reload the links
			$link = ES::table('Link');
			$link->loadByLink($assets->get('link'));

			// Get the oembed object
			$oembed = $link->getOembed();

			// Update the link image asset as well
			ES::updateLinkAssets($item, $result, $assets);
		}

		if (!$link->id) {
			// if nothing found just return.
			return;
		}

		$item->targets = $event;
		$item->contentObj = $link->toExportData($viewer);

		// Include privacy checking or not
		if ($includePrivacy) {
			$item->privacy = $privacy->form($item->uid, SOCIAL_TYPE_LINKS, $item->actor->id, 'story.view', false, $item->uid);
		}

		$item->show = true;
	}

	/**
	 * Generates the stream title of event.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$stream, $includePrivacy = true)
	{
		if ($stream->context != 'links') {
			return;
		}

		// Check for privacy
		$privacy = $this->my->getPrivacy();

		// Get the event
		$event = $stream->getCluster();

		if (!$event || !$event->canViewItem()) {
			return;
		}

		// Apply stream actions
		$stream->likes = ES::likes($stream->uid, $stream->context, $stream->verb, SOCIAL_APPS_GROUP_EVENT, $stream->uid, array('clusterId' => $stream->cluster_id));
		$stream->comments = ES::comments($stream->uid, $stream->context, $stream->verb, SOCIAL_APPS_GROUP_EVENT, array('url' => $stream->getPermalink(false, false, false), 'clusterId' => $stream->cluster_id), $stream->uid);
		$stream->repost = ES::repost($stream->uid, SOCIAL_TYPE_STREAM, SOCIAL_APPS_GROUP_EVENT);
		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;

		$assets = $stream->getAssets();

		if (empty($assets)) {
			return;
		}

		$access = $event->getAccess();
		if ($this->my->isSiteAdmin() || $event->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $stream->actor->id == $this->my->id)) {
			$stream->editable = true;
			$stream->appid = $this->getApp()->id;
		}

		$assets = $assets[0];

		// Get the app params
		$params = $this->getParams();

		// Load the link object
		$link = ES::table('Link');
		$link->loadByLink($assets->get('link'));

		// Get the oembed object
		$oembed = $link->getOembed();

		// Get the image
		$image = $link->getImage($assets);

		// If necessary, feed in our own proxy to avoid http over https issues.
		$uri = JURI::getInstance();

		if ($params->get('stream_link_proxy', false) && ($oembed || $assets->get('image')) && $uri->getScheme() == 'https') {

			// Check if there are any http links
			if (isset($oembed->thumbnail) && $oembed->thumbnail && stristr($oembed->thumbnail, 'http://') !== false) {
				$oembed->thumbnail = ES::proxy($oembed->thumbnail);
			}

			if ($image && stristr($image, 'http://') !== false) {
				$image = ES::proxy($image);
			}
		}

		// Get the contents and truncate accordingly
		$content = $assets->get('content', '');

		if ($params->get('stream_link_truncate')) {
			$content = ESJString::substr(strip_tags($content), 0, $params->get('stream_link_truncate_length', 250)) . JText::_('COM_EASYSOCIAL_ELLIPSES');
		}

		// Append the opengraph tags
		if ($image) {
			$stream->addOgImage($image);
		}

		// Always add the opengraph contents
		$stream->addOgDescription($content);

		// Include privacy checking or not
		if ($includePrivacy) {
			$stream->privacy = $privacy->form($stream->uid, SOCIAL_TYPE_LINKS, $stream->actor->id, 'story.view', false, $stream->uid);
		}

		$this->set('image', $image);
		$this->set('content', nl2br($content));
		$this->set('params', $params);
		$this->set('oembed', $oembed);
		$this->set('event', $event);
		$this->set('assets', $assets);
		$this->set('actor', $stream->actor);
		$this->set('stream', $stream);
		$this->set('link', $link);

		$stream->title = parent::display('themes:/site/streams/links/event/title');
		$stream->preview = parent::display('themes:/site/streams/links/preview');

		return true;
	}


	/**
	 * Responsible to generate the activity logs.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'links') {
			return;
		}

		//get story object, in this case, is the stream_item
		$tbl = ES::table('StreamItem');
		$tbl->load($item->uid); // item->uid is now streamitem.id

		$uid = $tbl->uid;

		//get story object, in this case, is the stream_item
		$my = ES::user();
		$privacy = ES::privacy($my->id);

		$actor = $item->actor;
		$target = count($item->targets) > 0 ? $item->targets[0] : '';

		$assets = $item->getAssets($uid);

		if (empty($assets)) {
			return;
		}

		$assets = $assets[ 0 ];

		$this->set('assets', $assets);
		$this->set('actor', $actor);
		$this->set('target', $target);
		$this->set('stream', $item);


		$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		$item->title = parent::display('logs/' . $item->verb);

		return true;

	}

	/**
	 * Prepares the links in the story edit form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareStoryEditForm(&$story, &$stream)
	{
		// preparing data for story edit.
		$data = array();

		// get all photos from this stream uid.
		$model = ES::model('Links');
		$link = $model->getStreamLink($stream->id);

		if ($link) {
			$data['link'] = $link;
		}

		$plugin = $this->onPrepareStoryPanel($story, true, $data);

		$story->panelsMain = array($plugin);
		$story->panels = array($plugin);
		$story->plugins = array($plugin);

		$contents = $story->editForm(false, $stream->id);

		return $contents;
	}

	/**
	 * Processes a story edit save.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onAfterStoryEditSave(SocialTableStream &$stream)
	{
		// Only process links
		if ($stream->context_type != 'links') {
			return;
		}

		$data = array();
		$data['description'] = $this->input->get('links_description', '', 'default');
		$data['image'] = $this->input->get('links_image', '', 'default');
		$data['title'] = $this->input->get('links_title', '', 'default');
		$data['url'] = $this->input->get('links_url', '', 'default');
		// $data['video'] = $this->input->get('links_video', '', 'default');

		$model = ES::model('Links');
		$state = $model->updateStreamLink($stream->id, $data);

		return true;
	}

	/**
	 * Generates the links in the story form
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onPrepareStoryPanel($story, $isEdit = false, $data = array())
	{
		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_links', true)) {
			return;
		}

		// Create plugin object
		$plugin = $story->createPlugin('links', 'panel');

		$title = JText::_('COM_EASYSOCIAL_STORY_LINK');
		$plugin->title = $title;

		// We need to attach the button to the story panel
		$theme = ES::themes();
		$theme->set('title', $plugin->title);

		$button = $theme->output('site/story/links/button');
		$form = $theme->output('site/story/links/form', array('data' => $data, 'isEdit' => $isEdit));

		$link = new stdClass();
		$link->url = null;

		if (isset($data['link'])) {
			$link = $data['link'];
		}

		// Attach the scripts
		$script = ES::script();
		$script->set('link', $link);
		$script->set('isEdit', $isEdit);
		$scriptFile = $script->output('site/story/links/plugin');

		$plugin->setHtml($button, $form);
		$plugin->setScript($scriptFile);

		return $plugin;
	}

	/**
	 * Publishing the scheduled stream.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function onPublishScheduledAppStory(SocialTableStream &$stream, SocialTableStreamItem &$streamItem, SocialTableStreamScheduled &$scheduled)
	{
		// Publishing the scheduled table.
		$scheduled->publishScheduled($stream, $streamItem);
	}
}
