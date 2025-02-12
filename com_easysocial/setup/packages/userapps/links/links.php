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

ES::import('admin:/includes/apps/apps');

require_once(__DIR__ . '/helper.php');

class SocialUserAppLinks extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'links') {
			return;
		}

		// the only place that user can submit coments / react is via stream.
		// if the stream id is missing, mean something is fishy.
		// just return false.
		return false;
	}

	/**
	 * Determines if the element is supported in this app
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	private function isSupportedElement($element)
	{
		static $supported = null;

		if (is_null($supported)) {
			$supported = false;
			$allowed = array('links.user.create');

			if (in_array($element, $allowed)) {
				$supported = true;
			}
		}

		return $supported;
	}

	/**
	 * event onLiked on shared link
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		if (!$this->isSupportedElement($likes->type)) {
			return;
		}

		// Get the stream object
		$stream = ES::table('Stream');
		$stream->load($likes->uid);

		$actor = ES::user($likes->created_by);
		$owner = ES::user($stream->actor_id);

		$systemOptions  = array(
			'context_type' => $likes->type,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		// Notify the owner of the photo first
		if ($likes->created_by != $stream->actor_id) {
			ES::notify('likes.item', array($stream->actor_id), array(), $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, 'links', 'user', 'create', array(), array($stream->actor_id, $likes->created_by));

		ES::notify('likes.involved', $recipients, array(), $systemOptions);
	}

	/**
	 * Before a comment is deleted, delete notifications tied to the comment
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onBeforeDeleteComment(SocialTableComments $comment)
	{
		if (!$this->isSupportedElement($comment->element)) {
			return;
		}

		// Here we know that comments associated with article is always
		// comment.uid = notification.uid
		$model = ES::model('Notifications');
		$model->deleteNotificationsWithContextId($comment->id, $comment->element);
	}

	/**
	 * Triggered before comments notify subscribers
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		if (!$this->isSupportedElement($comment->element)) {
			return;
		}

		// Since the uid is tied to the album we can get the album object
		$stream = ES::table('Stream');
		$stream->load($comment->uid);

		// Get the actor of the likes
		$actor = ES::user($comment->created_by);

		// retrieve the first mentioned user from the comment
		$exclusion = $comment->getFirstMentionedUserFromComment();

		// Convert the mention tag to permalink only for comment.
		$commentOptions = [
			'commentId' => $comment->id,
			'hasTag' => true,
			'exclusion' => $exclusion
		];

		$parseBBCodeOptions = [
			'escape' => false,
			'links' => true,
			'code' => true
		];

		$stringLib = ES::string();
		$commentContent = $stringLib->normalizeContent($comment->comment, $parseBBCodeOptions, false, '', $commentOptions);

		// Set the email options
		$emailOptions = array(
			'title' => 'APP_USER_LINKS_EMAILS_COMMENT_STATUS_ITEM_SUBJECT',
			'template' => 'apps/user/links/comment.link.item',
			'permalink' => $stream->getPermalink(true, true),
			'comment' => $commentContent,
			'actor' => $actor->getName(),
			'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink' => $actor->getPermalink(true, true),
			'target' => ES::user($stream->actor_id)->getName(),
			'targetLink' => ES::user($stream->actor_id)->getPermalink(false, true)
		);

		$systemOptions  = array(
			'context_type' => $comment->element,
			'context_ids' => $comment->id,
			'url' => $stream->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'content' => $commentContent,
			'aggregate' => true
		);

		// Notify the owner of the photo first
		if ($stream->actor_id != $comment->created_by && !$comment->isChild()) {
			ES::notify('comments.item', array($stream->actor_id), $emailOptions, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($comment->uid, 'links', 'user', 'create', array(), array($stream->actor_id, $comment->created_by));

		$emailOptions['title'] = 'APP_USER_LINKS_EMAILS_COMMENT_STATUS_INVOLVED_SUBJECT';
		$emailOptions['template'] = 'apps/user/links/comment.link.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/user/links/comment.link.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 * Renders the notification item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{

		// Process notifications when someone posts a likes on a link
		// context_type: links.user.create
		// type: comments
		if ($item->type == 'likes' && $item->context_type == 'links.user.create') {

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		// Process notifications when someone posts a comment on your status update
		// context_type: links.user.create
		// type: comments
		if ($item->type == 'comments' && $item->context_type == 'links.user.create') {

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

	}

	/**
	 * Processes a saved story.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		if ($streamItem->context_type != 'links') {
			return;
		}

		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_links', true)) {
			return;
		}

		if ($streamItem->isScheduled()) {
			// Lets create the scheduled item.
			$scheduled = ES::Scheduler();
			$scheduled->create($streamItem->uid, $template);
		}

		// Get the link information from the request
		$link = $this->input->get('links_url', '', 'default');
		$title = $this->input->get('links_title', '', 'default');
		$content = $this->input->get('links_description', '', 'default');
		$image = $this->input->get('links_image', '', 'default');
		$video = $this->input->get('links_video', '', 'default');

		// If there's no data, we don't need to store in the assets table.
		if (empty($title) && empty($content) && empty($image)) {
			return;
		}

		$registry = ES::registry();
		$registry->set('title', $title);
		$registry->set('content', $content);
		$registry->set('image', $image);
		$registry->set('link', $link);

		return true;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamCountValidation( &$item, $includePrivacy = true )
	{
		// If this is not it's context, we don't want to do anything here.
		if( $item->context_type != 'links' ) {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {

			$my = ES::user();
			$privacy = ES::privacy( $my->id );

			if (!$privacy->validate( 'story.view', $item->id, SOCIAL_TYPE_LINKS, $item->actor_id)) {
				$item->cnt = 0;
			}
		}

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

		// Check for privacy
		$privacy = $viewer->getPrivacy();

		if ($includePrivacy && !$privacy->validate('story.view', $item->uid, SOCIAL_TYPE_LINKS, $item->actor->id)) {
			return;
		}

		$actor = $item->getActor();
		$target = $item->getTargets();

		// Get the assets associated with this stream
		$assets = $item->getAssets();

		if (!$assets) {
			return;
		}

		if ($viewer->isSiteAdmin() || $viewer->id == $item->actor->id) {
			$item->editable = true;
			$item->appid = $this->getApp()->id;
		}

		// Apply actions on the stream
		$item->setLikes(SOCIAL_TYPE_USER, $item->uid);
		$item->setComments(SOCIAL_TYPE_USER, $item->uid, array('url' => $item->getPermalink(false, false, false)));
		$item->setRepost(SOCIAL_TYPE_USER, SOCIAL_TYPE_STREAM);

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
			$this->updateLinkAssets($item, $result, $assets);
		}

		if (!$link->id) {
			// if nothing found just return.
			return;
		}

		$item->targets = $this->my;
		$item->contentObj = $link->toExportData($viewer, $assets);

		// Include privacy checking or not
		if ($includePrivacy) {
			$item->privacy = $privacy->form($item->uid, SOCIAL_TYPE_LINKS, $item->actor->id, 'story.view', false, $item->uid);
		}

		$item->show = true;
	}

	/**
	 * Prepares the stream item for links
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$stream, $includePrivacy = true)
	{
		if ($stream->context != 'links') {
			return;
		}

		// Check for privacy
		$privacy = $this->my->getPrivacy();

		if ($includePrivacy && !$privacy->validate('story.view', $stream->uid, SOCIAL_TYPE_LINKS, $stream->actor->id)) {
			return;
		}

		$actor = $stream->getActor();
		$target = $stream->getTargets();

		// Get the assets associated with this stream
		$assets = $stream->getAssets();

		if (!$assets) {
			return;
		}

		if ($this->my->isSiteAdmin() || $this->my->id == $stream->actor->id) {
			$stream->editable = true;
			$stream->appid = $this->getApp()->id;
		}

		// Apply actions on the stream
		$stream->setLikes(SOCIAL_TYPE_USER, $stream->uid);
		$stream->setComments(SOCIAL_TYPE_USER, $stream->uid, array('url' => $stream->getPermalink(false, false, false)));
		$stream->setRepost(SOCIAL_TYPE_USER, SOCIAL_TYPE_STREAM);

		$assets = $assets[0];

		// Get the app params
		$params = $this->getParams();

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
			$this->updateLinkAssets($stream, $result, $assets);
		}

		$isTwitchLink = $link->isTwitchLink($assets->get('link'));

		// Twitch NEW API have to append the parent parmeter specific for the current domain #4203
		if ($isTwitchLink && (isset($oembed->html) && $oembed->html)) {

			preg_match_all('/src=\"(.*?)\"/i', $oembed->html, $matches);

			if ($matches && isset($matches[1])) {

				$url = $matches[1][0];

				$domain = rtrim(JURI::root(), '/');
				$domain = str_ireplace(array('http://', 'https://'), '', $domain);

				// remove the subdomin directory
				if (strstr($domain, '/') !== false) {
					$domain = explode('/', $domain);
					$domain = $domain[0];
				}

				$oembed->html = str_ireplace($url, $url . '&parent=' . $domain, $oembed->html);
			}
		}

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
			$max = $params->get('stream_link_truncate_length', 250);

			$content = ESJString::substr(strip_tags($content), 0, $max) . JText::_('COM_EASYSOCIAL_ELLIPSES');
		}

		// Append the opengraph tags
		if ($image) {
			$stream->addOgImage($image);
		}

		// Always add the opengraph contents
		$stream->addOgDescription($content);

		// Include privacy checking or not
		if ($includePrivacy) {
			$stream->privacy = $privacy->form($stream->uid, SOCIAL_TYPE_LINKS, $stream->actor->id, 'story.view', false, $stream->uid, array(), array('iconOnly' => true));
		}

		$this->set('image', $image);
		$this->set('content', nl2br($content));
		$this->set('params', $params);
		$this->set('oembed', $oembed);
		$this->set('assets', $assets);
		$this->set('actor', $actor);
		$this->set('target', $target);
		$this->set('stream', $stream);
		$this->set('link', $link);

		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;
		$stream->title = parent::display('themes:/site/streams/links/user/title');
		$stream->preview = parent::display('themes:/site/streams/links/preview');

		return true;
	}

	/**
	 * Update link assets for image from re-crawl process
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function updateLinkAssets($streamItem, $crawlData, $previousCrawlData)
	{
		return ES::updateLinkAssets($streamItem, $crawlData, $previousCrawlData);
	}

	/**
	 * Responsible to generate the activity logs.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog( SocialStreamItem &$item, $includePrivacy = true )
	{
		if ($item->context != 'links') {
			return;
		}

		//get story object, in this case, is the stream_item
		$tbl = ES::table( 'StreamItem' );
		$tbl->load( $item->uid ); // item->uid is now streamitem.id

		$uid = $tbl->uid;

		// Get story object, in this case, is the stream_item
		$my = ES::user();
		$privacy = ES::privacy($my->id);

		$actor = $item->actor;
		$target = count($item->targets) > 0 ? $item->targets[0] : '';

		$assets = $item->getAssets($uid);

		if (empty($assets)) {
			return;
		}

		$assets = $assets[0];

		$this->set('actor', $actor);
		$this->set('assets', $assets);
		$this->set('target', $target);
		$this->set('stream', $item);

		$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		$item->title = parent::display('logs/' . $item->verb);

		if ($includePrivacy) {
			$item->privacy = $privacy->form($uid, SOCIAL_TYPE_LINKS, $item->actor->id, 'story.view', false, $item->aggregatedItems[0]->uid);
		}

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
		$link->description = preg_replace("/\r|\n/", "", $link->description);

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
	 * Prepares what should appear in the story form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStoryPanel($story, $isEdit = false, $data = array())
	{
		$params = $this->getParams();

		// Determine if we should attach ourselves here.
		if (!$params->get('story_links', true) || !$this->getApp()->hasAccess($this->my->profile_id)) {
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
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		if ($item->context_type != 'links.user.create') {
			return;
		}

		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		$target->id = $item->uid;
		$target->type = 'stream';
		$target->endpoint = 'stream.item';
		$target->query_string = $target->endpoint . '&id=' . $target->id;

		$item->target = $target;
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
