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

class SocialUserAppKunena extends SocialAppItem
{
	public function kunenaHelper()
	{
		$helper = new KunenaHelper();
		return $helper;
	}

	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'kunena') {
			return;
		}

		// the only place that user can submit coments / react on apps is via stream.
		// if the stream id is missing, mean something is fishy.
		// just return false.
		return false;
	}

	/**
	 * Determines if the app should be available
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function appListing()
	{
		if (!$this->kunenaHelper()->isEnabled()) {
			return false;
		}

		return true;
	}

	/**
	 * Processes notifications
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$notification)
	{
		$allowed = array('post.reply');

		if (!in_array($notification->cmd, $allowed)) {
			return;
		}

		$message = $this->kunenaHelper()->message($notification->uid);
		$topic = $message->getTopic();
		$actor = $notification->getActor();

		// for some reason above that topic caused the notification error #
		$parent = $this->createParent($message->id);

		$message->message = $this->kunenaHelper()->parseBBCode($message->message, $parent, 80);
		$message->message = $this->formatContent($message->message);
		$message->message = strip_tags($message->message);

		$notification->title = JText::sprintf('APP_KUNENA_NOTIFICATION_NEW_REPLY', $actor->getName(), $topic->subject);
		$notification->content = $message->message;
	}

	public function createParent( $messageId = null )
	{
		$parent = new stdClass();
		$parent->forceSecure	= true;
		$parent->forceMinimal	= false;


		if ($messageId) {
			$message = $this->kunenaHelper()->message($messageId);
			$parent->attachments = $message->getAttachments();
		}

		return $parent;
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
		if ($item->context_type != 'kunena') {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {
			$uid = $item->id;
			$my = ES::user();
			$privacy = ES::privacy($my->id);

			$model = ES::model('Stream');
			$activity = $model->getActivityItem($item->id, 'uid');

			if ($activity) {
				$uid = $activity[0]->id;

				if (!$privacy->validate('core.view', $uid, SOCIAL_TYPE_ACTIVITY, $item->actor_id)) {
					$item->cnt = 0;
				}
			}
		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = [];

		if (!$params->get('stream_create', true)) {
			$excludeVerb[] = 'create';
		}

		if (!$params->get('stream_reply', true)) {
			$excludeVerb[] = 'reply';
		}

		if (!$params->get('stream_thanked', true)) {
			$excludeVerb[] = 'thanked';
		}

		if ($excludeVerb !== false) {
			$exclude['kunena'] = $excludeVerb;
		}
	}

	/**
	 * Prepares the stream item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'kunena') {
			return;
		}

		// Test if Kunena exists;
		if (!$this->kunenaHelper()->isEnabled()) {
			return;
		}

		$verb = $item->verb;

		// Decorate the stream
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Get app params
		$params = $this->getParams();

		// New forum posts
		if ($verb == 'create' && $params->get('stream_create', true)) {
			$this->processNewTopic($item, $includePrivacy);
		}

		if ($verb == 'reply' && $params->get('stream_reply', true)) {
			$this->processReply($item, $includePrivacy);
		}

		if ($verb == 'thanked' && $params->get('stream_thanked', true)) {
			$this->processThanked($item, $includePrivacy);
		}

		$element = $item->context;
		$uid = $item->contextId;
	}

	/**
	 * Processes the stream item for new topics
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function processNewTopic(&$item, $includePrivacy = true)
	{
		$topic = $this->kunenaHelper()->getTopic($item->contextId);
		$category = $topic->getCategory();

		// user not allow to view the content.
		if (!$category->isAuthorised('read') || !$topic->isAuthorised('read')) {
			return;
		}

		// If the topic is not published, do not proceed.
		if ($topic->hold == 2 || $topic->hold == 3) {
			return;
		}

		// Apply likes on the stream
		$likes = ES::likes()->get($item->contextId, 'kunena', 'create', SOCIAL_APPS_GROUP_USER, $item->uid);
		$item->likes = $likes;

		// disable comments on the stream
		$item->comments = false;

		// Set the actor
		$actor = $item->actor;

		JFactory::getLanguage()->load('com_kunena', JPATH_ROOT);

		$parent = $this->createParent($topic->first_post_id);

		$params = $this->getParams();
		$contentLength = $params->get('stream_content_length' , 0);

		$topic->message = $this->kunenaHelper()->parseBBCode($topic->first_post_message, $parent, $contentLength);
		$topic->message = $this->formatContent($topic->message);

		// check if user allow to edit this topic message or not.
		$message = $this->kunenaHelper()->messageHelper($topic->first_post_id);

		if ($this->my->id == $actor->id && $message->isAuthorised('edit')) {
			$url = "index.php?option=com_kunena&view=topic&layout=edit&catid=" . $message->catid . "&id=" . $message->thread . "&mesid=" . $message->id;
			$url = $url . '&Itemid=' . $this->kunenaHelper()->getItemId($url);
			$item->edit_link = JRoute::_($url);
		}

		$this->set('actor', $actor);
		$this->set('topic', $topic);

		$item->title = parent::display('streams/' . $item->verb . '.title');
		$item->preview = parent::display('streams/preview');

		// Append the opengraph tags
		$item->addOgDescription($topic->message);
	}

	/**
	 * Processes the stream item for new topics
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	private function processReply(&$item, $includePrivacy = true)
	{
		$message = $this->kunenaHelper()->messageHelper($item->contextId);

		// If the reply was unpublished do not display the item on the stream
		if ($message->hold == 2 || $message->hold == 3) {
			return;
		}

		$topic = $message->getTopic();

		// user not allow to view.
		if (!$topic->isAuthorised('read')){
			return;
		}

		// If the topic was unpublished do not display the replies
		if ($topic->hold == 2 || $topic->hold == 3) {
			return;
		}

		// Apply likes on the stream
		$likes = ES::likes()->get($item->contextId, 'kunena', 'reply', SOCIAL_APPS_GROUP_USER, $item->uid);
		$item->likes = $likes;
		$item->comments = false;

		$actor = $item->actor;
		$parent = $this->createParent($message->id);
		$params = $this->getParams();
		$contentLength = $params->get('stream_content_length' , 0);

		$message->message = $this->kunenaHelper()->parseBBCode($message->message, $parent, $contentLength);
		$message->message = $this->formatContent($message->message);

		$this->set('actor', $actor);
		$this->set('topic', $topic);
		$this->set('message', $message);

		$item->title = parent::display('streams/' . $item->verb . '.title');
		$item->preview = parent::display('streams/reply.preview');

		// Append the opengraph tags
		$item->addOgDescription($message->message);
	}

	/**
	 * Processes the stream item for new thanks
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function processThanked(&$item, $includePrivacy = true)
	{
		$message = $this->kunenaHelper()->messageHelper($item->contextId);
		$topic = $message->getTopic();
		$category = $topic->getCategory();

		// user not allow to view the content.
		if (!$category->isAuthorised('read') || !$topic->isAuthorised('read')) {
			return;
		}

		// Apply likes on the stream
		$likes = ES::likes()->get($item->contextId, 'kunena', 'thank', SOCIAL_APPS_GROUP_USER, $item->uid);
		$item->likes = $likes;

		$item->comments = false;
		$item->display = SOCIAL_STREAM_DISPLAY_MINI;

		// Set the actor
		$actor = $item->actor;
		$target = $item->targets[0];

		$parent = $this->createParent($message->id);
		$message->message = $this->kunenaHelper()->parseBBCode($message->message, $parent, 250);
		$message->message = $this->filterContent($message->message);

		$this->set('actor', $actor);
		$this->set('target', $target);
		$this->set('topic', $topic);
		$this->set('message', $message);

		$item->title = parent::display('streams/' . $item->verb . '.title');

		// Append the opengraph tags
		$item->addOgDescription($item->title);
	}

	/**
	 * Temporary fix to prevent email cloaking causing ajax to failed.
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	private function filterContent($content)
	{
		$content = strip_tags($content);

		return $content;
	}

	/**
	 * Prepares the activity log item
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'kunena') {
			return;
		}

		// Test if Kunena exists;
		if (!$this->kunenaHelper()->isEnabled()) {
			return;
		}

		// Get the context id.
		$actor = $item->actor;
		$topic = $this->kunenaHelper()->getTopic($item->contextId);

		if ($item->verb == 'thanked') {
			$message = $this->kunenaHelper()->messageHelper($item->contextId);
			$topic = $message->getTopic();

			$target = $item->targets[0];
			$this->set('target', $target);
			$this->set('message', $message);
		} else if ($item->verb == 'reply') {
			$message = $this->kunenaHelper()->messageHelper($item->contextId);
			$topic = $message->getTopic();

			$this->set('message', $message);
		}

		$this->set('topic', $topic);
		$this->set('actor', $actor);


		// Load up the contents now.
		$item->title = parent::display('streams/' . $item->verb . '.title');
		$item->content = '';
	}

	/**
	 * Format's kunena contents
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function formatContent($content)
	{
		$base = JURI::base(true).'/';

		// To check for all unknown protocals (a protocol must contain at least one alpahnumeric fillowed by :
		$protocols = '[a-zA-Z0-9]+:';

		// Pattern to match links
		$regex = '#(src|href|poster)="(?!/|'.$protocols.'|\#|\')([^"]*)"#m';

		$content = preg_replace($regex, "$1=\"$base\$2\"", $content);

		return $content;
	}
}
