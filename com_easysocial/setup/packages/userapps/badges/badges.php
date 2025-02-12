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

class SocialUserAppBadges extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'badges') {
			return;
		}

		// the only place that user can submit coments / react on badges is via stream.
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

			if (ESJString::stristr($element, 'badges.user.unlocked') !== false) {
				$supported = true;
			}
		}

		return $supported;
	}

	/**
	 * Process notficiations
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		// When a user comments your recently achievement activity stream
		if (stristr($item->context_type, 'badges.user.unlocked') !== false && $item->type == 'comments') {

			$hook = $this->getHook('notification', 'comments');
			$hook->execute($item);

			return;
		}

		// When a user likes your recently achievement activity stream
		if (stristr($item->context_type, 'badges.user.unlocked') !== false && $item->type == 'likes') {

			$hook = $this->getHook('notification', 'likes');
			$hook->execute($item);

			return;
		}

		$allowed = array('badges.unlocked');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		// Process notifications for followers
		if ($item->cmd == 'badges.unlocked') {

			$badge = ES::table('Badge');
			$badge->load($item->uid);

			// lets load 3rd party component's language file if this is not a core badge
			if ($badge->extension && $badge->extension != 'com_easysocial') {
				ES::language()->load( $badge->extension , JPATH_ROOT);
			}

			// For some reasons, the actor here could be incorrect due to previous data
			$item->actor_id = $item->target_id;

			$item->title = JText::sprintf('APP_USER_BADGES_NOTIFICATIONS_YOU_HAVE_JUST_UNLOCKED', $badge->get('title'));

			// Get the badge image
			$item->image = $badge->getAvatar();
		}

		return $item;
	}

	/**
	 * Responsible to process notifications for likes when someone likes the achieved action
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterLikeSave(&$likes)
	{
		// We need to split it because the type now stores as badges.user.unlocked.[9999]
		$namespace = explode('.', $likes->type);

		array_shift($namespace);

		$context = implode('.', $namespace);

		if (count($namespace) < 4 || $context != 'badges.user.unlocked') {
			return;
		}

		list($element, $group, $verb, $owner) = $namespace;

		// Get the permalink of the achievement item which is the stream item
		$streamItem = ES::table('StreamItem');
		$state = $streamItem->load(array('context_type' => $element, 'verb' => $verb, 'actor_id' => $owner, 'actor_type' => $group));

		if (!$state) {
			return;
		}

		$systemOptions	= array(
			'context_type' => $likes->type,
			'url' => $streamItem->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		if ($likes->created_by != $owner) {
			ES::notify('likes.item', array($owner), array(), $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item.
		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, $group, $verb . '.' . $owner, array(), array($owner, $likes->created_by));

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

		// Here we know that comments associated with story is always
		// comment.id = notification.context_id
		$model = ES::model('Notifications');
		$model->deleteNotificationsWithUid($comment->uid, $comment->element);
	}

	/**
	 * Processes notifications when a comment is stored on the site
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		// We need to split it because the type now stores as badges.user.unlocked.[9999]
		$namespace = explode('.', $comment->element);

		// get the corret context without ownerId
		$contexts = explode('.', $comment->element);
		array_pop($contexts);
		$context = implode('.', $contexts);

		if (count($namespace) < 4 || $context != 'badges.user.unlocked') {
			return;
		}

		list($element, $group, $verb, $owner) = $namespace;

		// Get the permalink of the achievement item which is the stream item
		$streamItem = ES::table('StreamItem');
		$state = $streamItem->load(array('context_type' => $element, 'verb' => $verb, 'actor_id' => $owner, 'actor_type' => $group, 'uid' => $comment->stream_id));

		if (!$state) {
			return;
		}

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

		$emailOptions = array(
			'title' => 'APP_USER_BADGES_EMAILS_COMMENT_ITEM_TITLE',
			'template' => 'apps/user/badges/comment.item',
			'permalink' => $streamItem->getPermalink(true, true),
			'comment' => $commentContent
		);

		$systemOptions	= array(
			'context_type' => $comment->element,
			'content' => $commentContent,
			'url' => $streamItem->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		if ($comment->created_by != $owner && !$comment->isChild()) {
			ES::notify('comments.item', array($owner), $emailOptions, $systemOptions);
		}

		// Get a list of recipients to be notified for this stream item.
		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb . '.' . $owner, array(), array($owner, $comment->created_by));

		$emailOptions['title'] = 'APP_USER_BADGES_EMAILS_COMMENT_INVOLVED_TITLE';
		$emailOptions['template'] = 'apps/user/badges/comment.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 * Responsible to generate the activity contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog( SocialStreamItem &$item, $includePrivacy = true )
	{
		if ($item->context != 'badges') {
			return;
		}

		$id = $item->contextId;
		$actor = $item->actor;

		$badge = ES::table('Badge');
		$badge->load($id);

		$this->set('badge', $badge);
		$this->set('actor', $actor);

		$item->title = parent::display('logs/' . $item->verb);

		if ($includePrivacy) {
			$privacy = $this->my->getPrivacy();
			$item->privacy = $privacy->form($item->uid, SOCIAL_TYPE_ACTIVITY, $item->actor->id, 'core.view', false, $item->aggregatedItems[0]->uid);
		}

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
		if( $item->context_type != 'badges' )
		{
			return false;
		}

		$item->cnt = 1;

		if( $includePrivacy )
		{
			$uid		= $item->id;
			$my         = ES::user();
			$privacy	= ES::privacy( $my->id );

			$sModel = ES::model( 'Stream' );
			$aItem 	= $sModel->getActivityItem( $item->id, 'uid' );

			if( $aItem )
			{
				$uid 	= $aItem[0]->id;

				if( !$privacy->validate( 'core.view', $uid , SOCIAL_TYPE_ACTIVITY , $item->actor_id ) )
				{
					$item->cnt = 0;
				}
			}
		}

		return true;
	}

	public function onStreamValidatePrivacy(SocialStreamItem $item, $user = false)
	{
		if (!$user) {
			$user = $this->my;
		}

		$privacy = ES::privacy($user->id);

		$tbl = ES::table('StreamItem');
		$tbl->load(array('uid' => $item->uid));

		if (!$privacy->validate('core.view', $tbl->id, SOCIAL_TYPE_ACTIVITY, $item->actor->id)) {
			return false;
		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		$params = $this->getParams();

		$excludeVerb = [];

		if(!$params->get('stream_achieved', true)) {
			$exclude['badges'] = true;
		}
	}

	/**
	 * Generates the stream item for REST API
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		if ($item->context != 'badges' || !$this->config->get('badges.enabled')) {
			return;
		}

		// Check if the app should be able to generate the stream.
		$params = $this->getParams();

		if (!$params->get('stream_achieved', true)) {
			return;
		}

		// Get the actor
		$actor = $item->getActor();

		// check if the actor is ESAD profile or not, if yes, we skip the rendering.
		// the same goes with blocked user on the site.
		if (!$actor->hasCommunityAccess() || $actor->block) {
			$item->title = '';
			return;
		}

		// Test if stream item is allowed
		if (!$this->onStreamValidatePrivacy($item, $viewer)) {
			return;
		}

		// Try to get the badge object from the params
		$raw = $item->params;
		$badge = ES::table('Badge');
		$badge->load($item->contextId);

		// lets load 3rd party component's language file if this is not a core badge
		if ($badge->extension && $badge->extension != 'com_easysocial') {
			ES::language()->load($badge->extension, JPATH_ROOT);
		}

		// Stream properties
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->contentObj = $badge->toExportData($viewer);
		$item->likes = ES::likes($item->contextId, $item->context, $item->verb . '.' . $item->actor->id , SOCIAL_APPS_GROUP_USER, $item->uid);
		$commentUrl = ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false));
		$item->comments = ES::comments($item->contextId , $item->context, $item->verb . '.' . $item->actor->id , SOCIAL_APPS_GROUP_USER , array('url' => $commentUrl), $item->uid);
		$item->show = true;
	}

	/**
	 * Responsible to generate the stream contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream( SocialStreamItem &$item )
	{
		if ($item->context != 'badges' || !$this->config->get('badges.enabled')) {
			return;
		}

		// Check if the app should be able to generate the stream.
		$params = $this->getParams();

		if (!$params->get('stream_achieved', true)) {
			return;
		}

		// Get the actor
		$actor = $item->getActor();

		// check if the actor is ESAD profile or not, if yes, we skip the rendering.
		// the same goes with blocked user on the site.
		if (!$actor->hasCommunityAccess() || $actor->block) {
			$item->title = '';
			return;
		}

		// Test if stream item is allowed
		if (!$this->onStreamValidatePrivacy($item)) {
			return;
		}

		// Try to get the badge object from the params
		$raw = $item->params;
		$badge = ES::table('Badge');
		$badge->load($item->contextId);

		// lets load 3rd party component's language file if this is not a core badge
		if ($badge->extension && $badge->extension != 'com_easysocial') {
			ES::language()->load($badge->extension, JPATH_ROOT);
		}

		// Stream properties
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->likes = ES::likes($item->contextId, $item->context, $item->verb . '.' . $item->actor->id , SOCIAL_APPS_GROUP_USER, $item->uid);
		$commentUrl = ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false));
		$item->comments = ES::comments($item->contextId , $item->context, $item->verb . '.' . $item->actor->id , SOCIAL_APPS_GROUP_USER , array('url' => $commentUrl), $item->uid);


		$this->set('badge', $badge);
		$this->set('actor', $actor);

		$item->title = parent::display('themes:/site/streams/badges/title');
		$item->preview = parent::display('themes:/site/streams/badges/preview');

		return true;

		// Append the opengraph tags
		$item->addOgDescription();
	}

	/**
	 * Determines if the app has stream filter
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function hasStreamFilter()
	{
		// If badge is not enabled on the site, don't show the stream filter
		if (!$this->config->get('badges.enabled')) {
			return false;
		}

		return parent::hasStreamFilter();
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		// Run standard notification processing
		$this->onNotificationLoad($item);
		$target = $item->target;

		if (stristr($item->context_type, 'badges.user.unlocked') !== false && ($item->type == 'comments' || $item->type == 'likes')) {

			// Break the namespace
			list($element, $group, $verb, $owner) = explode('.', $item->context_type);

			// Retrieve the stream item for the badges
			$streamItem = ES::table('StreamItem');
			$streamItem->load(array('context_type' => $element, 'verb' => $verb, 'actor_type' => $group, 'actor_id' => $owner));

			$target->id = $streamItem->uid;
			$target->type = 'stream';
			$target->endpoint = 'stream.item';
			$target->query_string = 'stream.item&id=' . $target->id;

			$item->target = $target;

			return;
		}

		$allowed = array('badges.unlocked');

		if (!in_array($item->cmd, $allowed)) {
			return;
		}

		$target->id = $item->uid;
		$target->type = 'badge';
		$target->endpoint = 'badge.item';
		$target->query_string = 'badge.item&id=' . $target->id;

		$item->target = $target;
	}
}
