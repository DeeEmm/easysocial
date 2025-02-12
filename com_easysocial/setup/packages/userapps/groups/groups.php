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

ES::import('admin:/includes/group/group');

class SocialUserAppGroups extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'groups') {
			return;
		}

		// user groups apps should not even reach here.
		// just return false
		return false;
	}

	/**
	 * Before a comment is deleted, delete notifications tied to the comment
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onBeforeDeleteComment(SocialTableComments $comment)
	{
		$allowed = array('story.group.create', 'links.group.create', 'polls.group.create', 'feeds.group.create', 'tasks.group.createMilestone');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		$uid = $comment->uid;
		$element = $comment->element;

		if ($comment->element == 'polls.group.create') {
			$element = 'groups';
			$uid = $comment->stream_id;
		}

		// Here we know that comments associated with story is always
		// comment.id = notification.context_id
		$model = ES::model('Notifications');
		$model->deleteNotificationsWithUid($uid, $element);
	}

	/**
	 * Prepares the group activity log
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onBeforeGetStream(array &$options, $view = '')
	{
		if ($view != 'dashboard') {
			return;
		}

		// $allowedContext = array('groups','story','photos', 'tasks', 'discussions');

		// if (is_array($options['context']) && in_array('groups', $options['context'])){
		// 	// we need to make sure the stream return only cluster stream.
		// 	$options['clusterType'] = SOCIAL_TYPE_GROUP;
		// } else if ($options['context'] === 'groups') {
		// 	$options['context'] = $allowedContext;
		// 	$options['clusterType'] = SOCIAL_TYPE_GROUP;
		// }
	}

	/**
	 * Prepares the group activity log
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'groups') {
			return;
		}

		$groupId = $item->contextId;
		$group = ES::group($groupId);

		if (!$group) {
			return;
		}

		$this->set('group', $group);
		$this->set('actor', $item->actor);

		$item->title = parent::display('logs/' . $item->verb . '.title');

		return true;
	}

	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'groups') {
			return false;
		}

		// if this is a cluster stream, let check if user can view this stream or not.
		$params = ES::registry($item->params);
		$group = ES::group($params->get('group'));

		if (!$group) {
			return;
		}

		$item->cnt = 1;

		if ($group->type != SOCIAL_GROUPS_PUBLIC_TYPE) {
			if (!$group->isMember(ES::user()->id)) {
				$item->cnt = 0;
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
		$params	= $this->getParams();

		$excludeVerb = [];

		if (! $params->get('stream_join', true)) {
			$excludeVerb[] = 'join';
		}

		if (! $params->get('stream_create', true)) {
			$excludeVerb[] = 'created';
		}

		$excludeVerb[] = 'leave';
		$excludeVerb[] = 'makeadmin';
		$excludeVerb[] = 'update';

		if ($excludeVerb !== false) {

			// only clusters app for user will return as object!
			$obj = new stdClass();
			$obj->group = SOCIAL_TYPE_GROUP;
			$obj->excludeVerb = $excludeVerb;

			$exclude['groups'] = $obj;
		}
	}
}
