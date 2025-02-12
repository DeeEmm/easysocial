<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/apps/apps');

class SocialUserAppApps extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != SOCIAL_TYPE_APPS) {
			return;
		}

		// the only place that user can submit coments / react on apps is via stream.
		// if the stream id is missing, mean something is fishy.
		// just return false.
		return false;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if( $item->context_type != SOCIAL_TYPE_APPS ) {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {
			$uid = $item->id;
			$privacy = $this->my->getPrivacy();

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
		$params = $this->getParams();
		$excludeVerb = [];

		if(!$params->get('stream_install', true)) {
			$exclude[SOCIAL_TYPE_APPS] = true;
		}
	}

	/**
	 * Trigger for onPrepareStream
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		// We only want to process related items
		if ($item->context != SOCIAL_TYPE_APPS) {
			return;
		}

		// Get application params to see if we should render the stream
		$params = $this->getParams();

		if (!$params->get('stream_install', true)) {
			return;
		}

		$item->display	= SOCIAL_STREAM_DISPLAY_FULL;


		$verb = strtolower($item->verb);
		$method = 'prepare' .ucfirst($verb) . 'Stream';

		$this->$method($item);

		return true;
	}

	/**
	 * Formats the activity log
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog( SocialStreamItem &$item, $includePrivacy = true )
	{
		// We only want to process related items
		if ($item->context != SOCIAL_TYPE_APPS) {
			return;
		}

		// Get the necessary data from the stream
		$element = $item->context;
		$data = ES::makeObject($item->params);

		$app = ES::table('App');
		$app->bind($data);

		$this->set('app', $app);

		// Display the title
		$item->title = parent::display('logs/' . $item->verb . '.title');

		return true;
	}

	/**
	 * Prepares the stream item for installed apps
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function prepareInstallStream(SocialStreamItem &$item)
	{
		// Get the params
		$params = $item->getParams();

		$element = $item->context;
		$appId = $item->contextId;
		$actor = $item->actor;

		// Load up the app table
		$app = ES::table('App');
		$app->load($appId);

		// Determine if the current viewer has already installed this app.
		$installed = $app->isInstalled($this->my->id);

		$this->set('installed', $installed);
		$this->set('actor', $actor);
		$this->set('app', $app);
		$this->set('uid', $item->uid);


		// Display the title
		$item->title = parent::display('themes:/site/streams/apps/title');
		$item->preview = parent::display('themes:/site/streams/apps/preview');
	}

	public function onAfterLikeSave($likes)
	{
		$segments = explode('.', $likes->type);

		$userid = array_pop($segments);

		$context = implode('.', $segments);

		$allowed = array('apps.user.install');

		if (!in_array($context, $allowed)) {
			return;
		}

		list($element, $group, $verb) = $segments;

		$streamItem = ES::table('streamitem');
		$state = $streamItem->load(array('context_type' => $element, 'actor_type' => $group, 'verb' => $verb, 'context_id' => $likes->uid));

		if (!$state) {
			return;
		}

		$systemOptions = array(
			'title' => '',
			'context_type' => $likes->type,
			'url' => $streamItem->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		if ($likes->created_by != $userid) {
			ES::notify('likes.item', array($userid), array(), $systemOptions);
		}

		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, $group, $verb, array(), array($userid, $likes->created_by));

		ES::notify('likes.involved', $recipients, array(), $systemOptions);
	}

	public function onAfterCommentSave($comment)
	{
		$segments = explode('.', $comment->element);

		$userid = array_pop($segments);

		$context = implode('.', $segments);

		$allowed = array('apps.user.install');

		if (!in_array($context, $allowed)) {
			return;
		}

		list($element, $group, $verb) = $segments;

		// We restructure the permalink based on the stream item instead of relying on the comments to give us the permalink
		// $permalink = $comment->getPermalink();

		$streamItem = ES::table('streamitem');
		$state = $streamItem->load(array('context_type' => $element, 'actor_type' => $group, 'verb' => $verb, 'context_id' => $comment->uid));

		$emailOptions = array(
			'title' => 'APP_USER_APPS_EMAILS_COMMENT_ITEM_TITLE',
			'template' => 'apps/user/apps/comment.item',
			'permalink' => $streamItem->getPermalink(true, true)
		);

		$systemOptions = array(
			'title' => '',
			'content' => $comment->comment,
			'context_type' => $comment->element,
			'url' => $streamItem->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		if ($comment->created_by != $userid && !$comment->isChild()) {
			ES::notify('comments.item', array($userid), $emailOptions, $systemOptions);
		}

		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($userid, $comment->created_by));

		$emailOptions['title'] = 'APP_USER_APPS_EMAILS_COMMENT_INVOLVED_TITLE';
		$emailOptions['template'] = 'apps/user/apps/comment.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/user/apps/comment.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 * Renders notification item for app
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$segments = explode('.', $item->context_type);
		$userid = array_pop($segments);
		$context = implode('.', $segments);
		$allowed = array('apps.user.install');

		if (!in_array($context, $allowed)) {
			return;
		}

		list($element, $group, $verb) = $segments;

		$obj = $this->getHook('notification', $item->type);
		$obj->execute($item);

		return;
	}
}
