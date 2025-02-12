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

class SocialUserAppMarketplaces extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	4.0
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
	 * @since	4.0
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
	 * @since	4.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		if (!$this->isAllowedContext($item->context_type)) {
			return;
		}

		// When user comments a single listing
		if ($item->cmd == 'comments.item' || $item->cmd == 'comments.replied' || $item->cmd == 'comments.involved') {
			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// When user likes a single listing
		if ($item->cmd == 'likes.item' || $item->cmd == 'likes.involved') {

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		return;
	}

	/**
	 * Determine if the cmd is allowed for notification
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isAllowedCmd($cmd)
	{
		$allowed = array(
			'comments.item',
			'comments.replied',
			'comments.involved',
			'likes.item',
			'likes.involved'
		);

		return in_array($cmd, $allowed);
	}

	/**
	 * Determine if the context is allowed for notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedContext($context)
	{
		$allowed = array(
			'marketplaces.user.create',
			'marketplaces.user.featured'
		);

		return in_array($context, $allowed);
	}

	/**
	 * Redirects the user to the appropriate page
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onBeforeNotificationRedirect(&$item)
	{
		return false;
	}

	/**
	 * Before a comment is deleted, delete notifications tied to the comment
	 *
	 * @since	4.0
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

		// if the listing is unpublished, skip
		if ($listing->isUnpublished()) {
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
		$listing = ES::marketplace($item->contextId);

		if ($item->verb == 'create' && ($this->my->isSiteAdmin() || $listing->isOwner() || $item->actor->id == $this->my->id)) {
			$item->edit_link = $listing->getEditLink();
		}

		// There are possibility that this is a cluster listing
		if ($listing->isCLusterListing()) {
			$cluster = $listing->getCluster();
			$this->set($cluster->getType(), $cluster);

			$commentParams = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $cluster->id);
			$item->comments = ES::comments($item->contextId, $item->context, $item->verb, $cluster->getType(), $commentParams, $item->uid);

			if ($cluster->getType() == SOCIAL_TYPE_PAGE) {
				// For Page, we need to manually ceate the likes and comments object
				$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $cluster->id));

				// Set an alias for actor
				// This is to change the actor avatar to use Page's avatar
				$item->setActorAlias($cluster);
			}
		}

		// Get the listing photo
		$photo = $listing->getSinglePhoto($config->get('photos.layout.size'));

		$this->set('listing', $listing);
		$this->set('actor', $item->actor);
		$this->set('verb', $item->verb);
		$this->set('photo', $photo);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		if ($item->verb == 'update') {
			$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		}

		$item->title = parent::display('themes:/site/streams/marketplaces/title');
		$item->preview = parent::display('themes:/site/streams/marketplaces/preview');
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

		// There are possibility that this is a cluster listing
		if ($listing->isCLusterListing()) {
			$cluster = $listing->getCluster();
			$this->set($cluster->getType(), $cluster);

			$commentParams = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $cluster->id);
			$item->comments = ES::comments($item->contextId, $item->context, $item->verb, $cluster->getType(), $commentParams, $item->uid);

			if ($cluster->getType() == SOCIAL_TYPE_PAGE) {
				// For Page, we need to manually ceate the likes and comments object
				$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $cluster->id));

				// Set an alias for actor
				// This is to change the actor avatar to use Page's avatar
				$item->setActorAlias($cluster);
			}
		}

		$item->comments = $listing->getComments($item->verb, $item->uid);
		$item->likes = $listing->getLikes($item->verb, $item->uid);

		// Get the listing photo
		$photo = $listing->getSinglePhoto();

		$item->photo = $photo;

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		if ($item->verb == 'update') {
			$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		}

		$item->show = true;
	}

	public function onAfterCommentSave($comment)
	{
		if (!$this->isAllowedContext($comment->element)) {
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
			'context_ids' => $comment->id,
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

		if ($actor->id != $listing->user_id) {
			ES::points()->assign('marketplace.comment.add.owner', 'com_easysocial', $listing->user_id);
		}

		ES::points()->assign('marketplace.comment.add', 'com_easysocial', $comment->created_by);

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

		if (!$params->get('stream_featured', true)) {
			$excludeVerb[] = 'featured';
		}

		if (!$params->get('stream_update', true)) {
			$excludeVerb[] = 'update';
		}

		$obj = new stdClass();
		$obj->group = SOCIAL_TYPE_MARKETPLACE;
		$obj->excludeVerb = $excludeVerb;

		$exclude['marketplaces'] = $obj;
	}

	/**
	 * Publishing the scheduled stream.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function onPublishScheduledAppStory(SocialTableStream &$stream, SocialTableStreamItem &$streamItem, SocialTableStreamScheduled &$scheduled)
	{
		if ($streamItem->context_type != 'marketplaces') {
			return;
		}

		$listing = ES::table('Marketplace');
		$listing->load($scheduled->context_id);

		// Make sure the listing was ready before publishing.
		if (!$listing->isPublished()) {
			return false;
		}

		// Update listing scheduled flag.
		$listing->scheduled = SOCIAL_MARKETPLACE_UNSCHEDULED;
		$listing->isnew = 1;
		$listing->store();

		// Publishing the scheduled table.
		$scheduled->publishScheduled($stream, $streamItem);
	}

	public function onAfterStorySave(&$stream, &$streamItem, &$template)
	{
		if ($streamItem->context_type != 'marketplaces') {
			return;
		}

		// Change the isNew to false
		$table = ES::table("Marketplace");
		$table->load($streamItem->context_id);
		$table->isnew = 0;

		// Set this listing as scheduled so it would not displayed on listing listing.
		if ($streamItem->isScheduled()) {
			$table->scheduled = SOCIAL_MARKETPLACE_SCHEDULED;
		}

		$table->store();

		// Lets create the scheduled item.
		$scheduled = ES::Scheduler();
		$scheduled->create($streamItem->uid, $template);
	}

	/**
	 * Triggers after a like is saved
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function onAfterLikeSave(&$likes, &$isNew)
	{
		$allowed = array('marketplaces.user.create', 'marketplaces.user.featured');

		if (!$this->isAllowedContext($likes->type)) {
			return;
		}

		list($element, $group, $verb) = explode('.', $likes->type);

		$actor = ES::user($likes->created_by);

		$systemOptions  = array(
			'context_type' => $likes->type,
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		$listing = ES::marketplace($likes->uid);
		$author = $listing->getAuthor();

		$systemOptions['context_ids'] = $listing->id;
		$systemOptions['url'] = $listing->getPermalink(false);

		if ($isNew && $actor->id != $author->id) {

			// Assign points to the listing owner
			ES::points()->assign('marketplace.reaction.add.owner', 'com_easysocial', $author->id);

			// assign points to the liker
			ES::points()->assign('marketplace.like', 'com_easysocial', $actor->id);

			// Notify the owner of the listing
			ES::notify('likes.item', array($author->id), false, $systemOptions);
		}

		// ES::badges()->log('com_easysocial', 'marketplaces.react', $likes->created_by, '');

		// Get a list of recipients to be notified for this stream item
		// We exclude the owner of the note and the actor of the like here
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, 'user', $verb, array(), array($listing->user_id, $likes->created_by));

		ES::notify('likes.involved', $recipients, false, $systemOptions);

		return;
	}

	/**
	 * Triggers when unlike happens
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function onAfterLikeDelete(&$likes)
	{
		if ($likes->type != 'marketplaces.user.create') {
			return;
		}

		$actor = ES::user($likes->created_by);

		$listing = ES::marketplace($likes->uid);
		$author = $listing->getAuthor();

		if ($actor->id != $author->id) {
			ES::points()->assign('marketplace.reaction.remove.owner', 'com_easysocial', $author->id);
			ES::points()->assign('marketplace.unlike', 'com_easysocial', $this->my->id);
		}

		return;
	}

	/**
	 * Triggered after a comment is deleted
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function onAfterDeleteComment(SocialTableComments &$comment)
	{
		if ($comment->element != 'marketplaces.user.create') {
			return;
		}

		$actor = ES::user($comment->created_by);
		$listing = ES::marketplace($comment->uid);
		$author = $listing->getAuthor();

		// Assign points when someone comments on the author's listing is deleted
		if ($actor->id != $author->id) {
			ES::points()->assign('marketplace.comment.remove.owner', 'com_easysocial', $author->id);
		}

		// Assign points when a comment is deleted for a listing
		ES::points()->assign('marketplace.comment.remove', 'com_easysocial', $comment->created_by);
	}
}

