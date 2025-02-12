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

class EasySocialControllerDiscussions extends EasySocialController
{
	/**
	 * Allows caller to delete a discussion
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the discussion id
		$id = $this->input->get('id', 0, 'int');
		$discussion = ES::table('Discussion');
		$discussion->load($id);

		$cluster = ES::cluster($discussion->type, $discussion->uid);

		// Ensure that the person really can delete the discussion
		if (!$cluster->isAdmin() && $discussion->created_by != $this->my->id && !$this->my->isSiteAdmin()) {
			return $this->view->exception('APP_DISCUSSIONS_NOT_ALLOWED_TO_DELETE');
		}

		// Delete the discussion
		$discussion->delete();

		$this->info->set(false, 'APP_GROUP_DISCUSSIONS_DISCUSSION_DELETED_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $cluster);
	}

	/**
	 * Allows caller to delete a reply
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function deleteReply()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the reply object
		$id = $this->input->get('id', 0, 'int');
		$reply = ES::table('Discussion');
		$reply->load($id);

		// Load the discussion
		$discussion = $reply->getParent();

		// Get the cluster
		$cluster = ES::cluster($reply->type, $reply->uid);

		if ($reply->created_by != $this->my->id && !$cluster->isAdmin() && !$this->my->isSiteAdmin()) {
			return $this->view->exception('APP_DISCUSSIONS_NOT_ALLOWED_TO_DELETE');
		}

		$state = $reply->delete();

		// If the reply is the accepted answer for the discussion, remove it
		if ($id == $discussion->answer_id) {
			$discussion->removeAnswered();
		}

		return $this->view->call(__FUNCTION__, $discussion);
	}

	/**
	 * Executes the locking of a discussion
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function lock()
	{
		ES::requireLogin();
		ES::checkToken();

		// Load the discussion
		$id = $this->input->get('id', 0, 'int');
		$discussion = ES::table('Discussion');
		$discussion->load($id);

		// Get the cluster
		$cluster = ES::cluster($discussion->type, $discussion->uid);

		// Check if the viewer can really lock the discussion.
		if (!$cluster->isAdmin() && !$this->my->isSiteAdmin()) {
			return $this->view->exception('APP_DISCUSSIONS_NOT_ALLOWED_TO_LOCK');
		}

		// Lock the discussion
		$discussion->lock();

		return $this->view->call(__FUNCTION__, $discussion, $cluster);
	}

	/**
	 * Allows caller to save the discussion
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function save()
	{
		ES::requireLogin();
		ES::checkToken();

		// Try to get the discussion object
		$id = $this->input->get('id', 0, 'int');
		$discussion = ES::table('Discussion');
		$discussion->load($id);

		// Get the cluster
		$clusterId = $this->input->get('uid', 0, 'int');
		$clusterType = $this->input->get('type', '', 'word');
		$cluster = ES::cluster($clusterType, $clusterId);

		// Ensure that it's a valid cluster
		if (!$cluster || !$cluster->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_CLUSTER');
		}

		// Check if user is really allowed to start a new discussion
		if (!$cluster->canCreateDiscussion()) {
			return $this->view->exception('APP_GROUP_DISCUSSIONS_NOT_ALLOWED_CREATE');
		}

		// Only allow discussion owner and cluster / site admin to modify existing discussions
		if ($discussion->id && $discussion->created_by != $this->my->id && !$cluster->isAdmin() && !$this->my->isSiteAdmin()) {
			return $this->view->exception('APP_DISCUSSIONS_NOT_ALLOWED_TO_EDIT');
		}

		// Assign discussion properties
		$discussion->uid = $cluster->id;
		$discussion->type = $cluster->getType();
		$discussion->title = $this->input->get('title', '', 'string');
		// $discussion->content = JRequest::getVar('discuss_content', '' , 'POST', 'none' , JREQUEST_ALLOWRAW);

		$discussion->content = $this->input->get('discuss_content', '', 'raw');

		//TODO: content_type
		$contentType = $this->input->get('content_type', 'bbcode', 'string');
		$discussion->content_type = $contentType == 'bbcode' ? 'bbcode' : 'html';

		// If discussion is edited, we don't want to modify the following items
		if (!$discussion->id) {
			$discussion->created_by = $this->my->id;
			$discussion->parent_id = 0;
			$discussion->hits = 0;
			$discussion->state = SOCIAL_STATE_PUBLISHED;
			$discussion->votes = 0;
			$discussion->lock = false;
			$discussion->last_replied = ES::normalizeDateString('');
			$discussion->params = '';
			$discussion->answer_id = 0;
			$discussion->last_reply_id = 0;
		}

		// Get the app related to this cluster
		$app = $cluster->getApp('discussions');

		if (!$app || !$app->id) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_APP');
		}

		// Get the redirection link
		$options = array();
		$options['layout'] = 'canvas';
		$options['customView'] ='create';

		// If the discussion id is not provided, then this is a create new discussion.
		if ($discussion->id) {
			$options['customView'] ='edit';
			$options['discussionId'] = $discussion->id;
		}

		$options['uid'] = $cluster->getAlias();
		$options['type'] = $clusterType;
		$options['id'] = $app->getAlias();

		// Get the redirection url
		$redirect = ESR::apps($options, false);

		// Validate the discussion
		if (!$discussion->validate()) {
			$this->info->set(false, $discussion->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__, $redirect);
		}

		// Save the discussion now
		$state = $discussion->store();

		if (!$state) {
			$errorMsg = JText::sprintf('APP_GROUP_DISCUSSIONS_DISCUSSION_CREATED_FAILED', $discussion->getError());
			$this->info->set(false, $errorMsg, ES_ERROR);
			return $this->view->call(__FUNCTION__, $redirect);
		}

		$this->info->set(false, 'APP_GROUP_DISCUSSIONS_DISCUSSION_CREATED_SUCCESS', SOCIAL_MSG_SUCCESS);

		// Process any files that needs to be created.
		$discussion->mapFiles();

		// Get the redirection url
		$redirect = $discussion->getPermalink(false);

		// Perform a redirection
		return $this->view->call(__FUNCTION__, $redirect, $discussion);
	}

	/**
	 * Retrieves the list of discussions
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getDiscussions()
	{
		// Load the cluster
		$id = $this->input->get('id', 0, 'int');
		$filter = $this->input->get('filter', 'all', 'string');
		$cluster = ES::cluster($id);

		// Check if the viewer can really browse discussions from this group.
		if (!$cluster->canViewItem()) {
			return $this->view->exception('APP_GROUP_DISCUSSIONS_NOT_ALLOWED_VIEWING');
		}

		$options = array();

		if ($filter != 'all') {
			$options[$filter] = true;
		}

		// Get the app params
		$app = $cluster->getApp('discussions');
		$params = $app->getParams();

		// Get total number of discussions to display
		$options['limit'] = $params->get('total', ES::getLimit());

		$model = ES::model('Discussions');
		$discussions = $model->getDiscussions($cluster->id, $cluster->getType(), $options);
		$pagination = $model->getPagination();

		$pagination->setVar('view', $cluster->getTypePlural());
		$pagination->setVar('layout' , 'item');
		$pagination->setVar('id', $cluster->getAlias());
		$pagination->setVar('appId', $app->id);
		$pagination->setVar('filter', $filter);

		return $this->view->call(__FUNCTION__, $cluster, $discussions, $pagination, $app, $filter);
	}

	/**
	 * Allows caller to update a discussion
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function update()
	{
		ES::requireLogin();

		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$reply = ES::table('Discussion');
		$reply->load($id);

		// Get the parent
		$discussion = $reply->getParent();

		// Get the cluster
		$cluster = ES::cluster($reply->type, $reply->uid);

		// Ensure that the person can really update the discussion
		if (!$cluster->isAdmin() && !$this->my->isSiteAdmin() && $this->my->id != $reply->created_by) {
			return $this->view->exception('APP_DISCUSSIONS_NOT_ALLOWED_TO_EDIT');
		}

		// Get the content
		$content = $this->input->get('content', '', 'raw');

		if (!$content) {
			$this->info->set(false, 'APP_GROUP_DISCUSSIONS_EMPTY_REPLY_ERROR', ES_ERROR);
			return $this->view->call(__FUNCTION__, $discussion, $reply, $cluster);
		}

		// Update the content
		$reply->content = $content;

		// Save the reply.
		$reply->store();

		// Update the parent's reply counter.
		$discussion->sync($reply);

		return $this->view->call(__FUNCTION__, $discussion, $reply, $cluster);
	}

	/**
	 * Executes the locking of a discussion
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function unlock()
	{
		ES::requireLogin();

		ES::checkToken();

		// Load the discussion
		$id = $this->input->get('id', 0, 'int');
		$discussion = ES::table('Discussion');
		$discussion->load( $id );

		// Get the cluster
		$cluster = ES::cluster($discussion->type, $discussion->uid);

		if (!$cluster->isAdmin() && !$this->my->isSiteAdmin()) {
			return $this->view->exception('APP_DISCUSSIONS_NOT_ALLOWED_TO_UNLOCK');
		}

		// Lock the discussion
		$discussion->unlock();

		return $this->view->call(__FUNCTION__, $discussion);
	}

	/**
	 * Allows caller to submit a reply
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function reply()
	{
		ES::requireLogin();

		// Check for request forgeriess
		ES::checkToken();

		// Get the cluster
		$clusterId = $this->input->get('uid', 0, 'int');
		$clusterType = $this->input->get('type', '', 'word');
		$cluster = ES::cluster($clusterType, $clusterId);

		// Get the discussion
		$id = $this->input->get('id', 0, 'int');
		$discussion = ES::table('Discussion');
		$discussion->load($id);

		// Check whether the viewer can really reply to the discussion
		if (!$cluster->isMember() && !$this->my->isSiteAdmin()) {
			return $this->view->exception('APP_DISCUSSIONS_NOT_ALLOWED_TO_REPLY');
		}

		// Test if the user can really reply to the discussion
		if (!$discussion->canReply()) {
			return $this->view->exception($discussion->getError());
		}

		// Get the content
		$content = $this->input->get('content', '', 'raw');

		if (!$content) {
			$this->info->set(false, 'APP_GROUP_DISCUSSIONS_EMPTY_REPLY_ERROR', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$contentType = $this->input->get('content_type', 'bbcode', 'string');
		$contentType = $contentType == 'bbcode' ? 'bbcode' : 'html';

		$reply = ES::table('Discussion');
		$reply->uid = $discussion->uid;
		$reply->type = $discussion->type;
		$reply->title = '';
		$reply->content = $content;
		$reply->created_by = $this->my->id;
		$reply->parent_id = $discussion->id;
		$reply->state = SOCIAL_STATE_PUBLISHED;
		$reply->content_type = $contentType;
		$reply->last_replied = ES::normalizeDateString('');
		$reply->votes = 0;
		$reply->answer_id = 0;
		$reply->last_reply_id = 0;
		$reply->params = '';

		// Save the reply.
		$reply->store();

		// Before we populate the output, we need to format it according to the theme's specs.
		$reply->author = $this->my;
		$reply->likes = ES::likes($reply->id, 'discussion', 'reply', 'group');

		return $this->view->call(__FUNCTION__, $reply, $cluster);
	}

	/**
	 * Accepts a reply item as an answer
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function accept()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the reply object
		$id = $this->input->get('id', 0, 'int');
		$reply = ES::table('Discussion');
		$reply->load($id);

		// Load the discussion
		$discussion = $reply->getParent();

		// Get the cluster
		$cluster = ES::cluster($reply->type, $reply->uid);

		// Check if the viewer can accept this reply as an answer.
		if (!$cluster->isAdmin() && $this->my->id != $discussion->created_by && !$this->my->isSiteAdmin()) {
			return $this->view->exception('APP_DISCUSSIONS_NOT_ALLOWED_TO_MARK_AS_ANSWERED');
		}

		// Set this discussion as answered
		$discussion->setAnswered($reply);

		return $this->view->call(__FUNCTION__, $discussion, $reply);
	}

	/**
	 * Reject this reply item from answer
	 *
	 * @since	2.0.19
	 * @access	public
	 */
	public function reject()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the reply object
		$id = $this->input->get('id', 0, 'int');
		$reply = ES::table('Discussion');
		$reply->load($id);

		// Load the discussion
		$discussion = $reply->getParent();

		// Get the cluster
		$cluster = ES::cluster($reply->type, $reply->uid);

		// Check for permission
		if (!$cluster->isAdmin() && $this->my->id != $discussion->created_by && !$this->my->isSiteAdmin()) {
			return $this->view->exception('APP_DISCUSSIONS_NOT_ALLOWED_TO_REJECT_REPLY_ANSWER');
		}

		// Reject the reply
		$discussion->rejectAnswer($reply);

		return $this->view->call(__FUNCTION__, $discussion, $reply);
	}
}
