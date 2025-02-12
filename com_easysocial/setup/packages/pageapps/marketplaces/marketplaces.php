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

class SocialPageAppMarketplaces extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'marketplaces') {
			return;
		}

		$listing = ES::marketplace($uid);

		if (!$listing->id) {
			return false;
		}

		if (!$listing->canViewItem()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the element is supported in this app
	 *
	 * @since	3.3
	 * @access	public
	 */
	private function isSupportedElement($element)
	{
		static $supported = null;

		if (is_null($supported)) {
			$supported = false;
			$allowed = array('marketplaces.listing.create', 'marketplaces.listing.featured', 'marketplaces.listing.update', 'story.listing.create', 'links.listing.create', 'polls.listing.create', 'files.listing.uploaded');

			if (in_array($element, $allowed)) {
				$supported = true;
			}
		}

		return $supported;
	}

	/**
	 * Handles notifications for marketplaces
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		if ($item->cmd == 'page.marketplace.create') {

			// Get the actor that is tagging the target
			$actor = ES::user($item->actor_id);
			$page = ES::page($item->uid);

			// Set the notification title
			$item->title = JText::sprintf('APP_CLUSTER_MARKETPLACES_USER_ADDED_NEW_LISTING', $actor->getName(), $page->getName());

			// Try to get the listing
			$listing = ES::marketplace($item->uid, $item->type, $item->context_ids);

			return;
		}

		return;
	}

	/**
	 * Determine if cmd is allowed for notification
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public function isAllowedCmd($cmd)
	{
		$allowed = array(
			'page.marketplace.create'
		);

		return in_array($cmd, $allowed);
	}

	/**
	 * Redirects the user to the appropriate page
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onBeforeNotificationRedirect(&$item)
	{
		return false;
	}

	/**
	 * Before a comment is deleted, delete notifications tied to the comment
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function onBeforeDeleteComment(SocialTableComments $comment)
	{
		if (!$this->isSupportedElement($comment->element)) {
			return;
		}

		// Here we know that comments associated with article is always
		// comment.uid = notification.uid
		$uid = $comment->uid;
		$element = $comment->element;

		$model = ES::model('Notifications');
		$model->deleteNotificationsWithUid($uid, $element);
	}

	/**
	 * Pre-process the stream item
	 *
	 * @since   3.1.0
	 * @access  public
	 */
	public function isValid(&$item, $viewerId = null)
	{
		if ($item->context != SOCIAL_TYPE_MARKETPLACES) {
			return;
		}

		// Check for privacy
		$privacy = $this->my->getPrivacy();

		// if ($includePrivacy && !$privacy->validate('marketplaces.view', $item->contextId, SOCIAL_TYPE_MARKETPLACES, $item->actor->id)) {
		// 	return;
		// }

		$listing = ES::marketplace($item->contextId);

		// Ensure that the listing is really published
		if (!$listing->isPublished()) {
			return;
		}

		if ($listing->isNew() && $listing->isPending()) {

			// Newly created listing from story panel
			$item->title = JText::_('APP_USER_EVENTS_STREAM_EVENT_PENDING_APPROVAL');
			$item->display = SOCIAL_STREAM_DISPLAY_MINI;
			return false;
		}

		if (!$this->getParams()->get('stream_' . $item->verb, true)) {
			return false;
		}

		if ($this->my->isSiteAdmin() || $this->my->id == $item->actor->id) {
			$item->appid = $this->getApp()->id;
		}

		$target = count($item->targets) > 0 ? $item->targets[0] : '';

		// Get the cluster
		$cluster = $item->getCluster();

		if ($cluster) {
			$target = $cluster;
		}

		return true;
	}

	/**
	 * Prepares the Rest stream item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		if (!$this->isValid($item)) {
			return;
		}

		$listing = ES::marketplace($item->contextId);

		$item->contentObj = $listing->toExportData($viewer, true);

		if ($item->verb == 'create' && ($this->my->isSiteAdmin() || $listing->isOwner() || $item->actor->id == $this->my->id)) {
			$item->edit_link = $listing->getEditLink();
		}

		$cluster = $listing->getCluster();
		// $this->set($cluster->getType(), $cluster);

		$commentParams = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $cluster->id);
		$item->comments = ES::comments($item->contextId, $item->context, $item->verb, $cluster->getType(), $commentParams, $item->uid);

		// For Page, we need to manually ceate the likes and comments object
		$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $cluster->id));

		// Set the actor alias
		if ($item->post_as == SOCIAL_TYPE_PAGE) {
			$item->setActorAlias($cluster);
		}

		$item->targets = $cluster;

		// Get the listing photo
		$photo = $listing->getSinglePhoto();

		$item->photo = $photo;

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		if ($item->verb == 'update') {
			$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		}

		$item->show = true;
	}

	/**
	 * Prepares the stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if (!$this->isValid($item)) {
			return;
		}

		$config = ES::config();
		$cluster = $item->getCluster();

		// Set the actor alias
		$actor = $item->getPostActor($cluster);

		$listing = ES::marketplace($item->contextId);

		if ($item->verb == 'create' && ($this->my->isSiteAdmin() || $listing->isOwner() || $item->actor->id == $this->my->id)) {
			$item->edit_link = $listing->getEditLink();
		}

		$this->set($cluster->getType(), $cluster);

		$commentParams = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $cluster->id);
		$item->comments = ES::comments($item->contextId, $item->context, $item->verb, $cluster->getType(), $commentParams, $item->uid);
		$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $cluster->id));

		// Get the listing photo
		$photo = $listing->getSinglePhoto($config->get('photos.layout.size'));

		$this->set('listing', $listing);
		$this->set('photo', $photo);
		$this->set('actor', $actor);
		$this->set('verb', $item->verb);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		if ($item->verb == 'update') {
			$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		}

		$item->title = parent::display('themes:/site/streams/marketplaces/title');
		$item->preview = parent::display('themes:/site/streams/marketplaces/preview');
	}

	public function onAfterCommentSave($comment)
	{
		$segments = explode('.', $comment->element);

		if (count($segments) !== 3 || $segments[1] !== SOCIAL_TYPE_MARKETPLACE) {
			return;
		}

		list($element, $group, $verb) = explode('.', $comment->element);

		// Get the actor
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

		if ($element === 'marketplaces') {
			$listing = ES::marketplace($comment->uid);

			$stream = ES::table('Stream');
			$stream->load($comment->stream_id);

			$owner = ES::user($stream->actor_id);

			$emailOptions = array(
				'title' => 'APP_USER_MARKETPLACES_EMAILS_' . strtoupper($verb) . '_COMMENT_ITEM_SUBJECT',
				'headingText' => 'APP_USER_MARKETPLACES_EMAILS_' . strtoupper($verb) . '_COMMENT_ITEM_TITLE',
				'contentText' => 'APP_USER_MARKETPLACES_EMAILS_' . strtoupper($verb) . '_COMMENT_ITEM_CONTENT',
				'template' => 'apps/user/marketplaces/comment.item',
				'permalink' => $stream->getPermalink(true, true),
				'actor' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $actor->getPermalink(true, true),
				'comment' => $commentContent
			);

			 $systemOptions  = array(
				'context_type' => $comment->element,
				'content' => $commentContent,
				'url' => $stream->getPermalink(false, false, false),
				'actor_id' => $comment->created_by,
				'uid' => $comment->uid,
				'aggregate' => true
			);

			 // Notify the owner first
			 if ($comment->created_by != $owner->id && !$comment->isChild()) {
				ES::notify('comments.item', array($owner->id), $emailOptions, $systemOptions);
			 }

			 // Get a list of recipients to be notified for this stream item
			 // We exclude the owner of the discussion and the actor of the comment here
			 $recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($owner->id, $comment->created_by));

			 $emailOptions['title'] = 'APP_USER_MARKETPLACES_EMAILS_' . strtoupper($verb) . '_COMMENT_INVOLVED_SUBJECT';
			 $emailOptions['headingText'] = 'APP_USER_MARKETPLACES_EMAILS_' . strtoupper($verb) . '_COMMENT_INVOLVED_TITLE';
			 $emailOptions['contentText'] = 'APP_USER_MARKETPLACES_EMAILS_' . strtoupper($verb) . '_COMMENT_INVOLVED_CONTENT';
			 $emailOptions['template'] = 'apps/user/marketplaces/comment.involved';

			if (!$comment->isChild()) {
				// Notify other participating users
				ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
			}

			$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
			$emailOptions['template'] = 'apps/user/marketplaces/comment.listing.replied';

			// Notify the owner of the parent comment
			if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
				ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
			}
		}
	}

	public function onBeforeGetStream(array &$options, $view = '')
	{
		if ($view != 'dashboard') {
			return;
		}
	}

	public function onStreamVerbExclude(&$exclude)
	{
		$params = $this->getParams();

		$excludeVerb = array();

		if (!$params->get('stream_create', true)) {
			$excludeVerb[] = 'create';
		}

		$excludeVerb[] = 'update';

		$obj = new stdClass();
		$obj->group = SOCIAL_TYPE_MARKETPLACE;
		$obj->excludeVerb = $excludeVerb;

		$exclude['marketplaces'] = $obj;

	}
}

