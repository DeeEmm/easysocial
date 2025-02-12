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

class EasySocialControllerComments extends EasySocialController
{
	/**
	 * Allows caller to save a comment.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function save()
	{
		ES::requireLogin();

		// Check for request forgeries.
		ES::checkToken();

		// Check for permission first
		$access = ES::access();

		// Ensure that the user is allowed to post comments
		if (!$access->allowed('comments.add')) {
			return $this->view->call(__FUNCTION__);
		}

		$element = $this->input->get('element', '', 'string');
		$group = $this->input->get('group', '', 'string');
		$verb = $this->input->get('verb', '', 'string');
		$uid = $this->input->get('uid', 0, 'int');

		$input = $this->input->get('input', '', 'raw');
		$data = $this->input->get('data', array(), 'array');
		$streamid = $this->input->get('streamid', 0, 'int');
		$parent = $this->input->get('parent', 0, 'int');

		$clusterid = $this->input->get('clusterid', 0, 'int');
		$postActor = $this->input->get('postActor', 'user', 'string');

		// We need to store the cluster to be used later
		if ($clusterid) {
			$data['clusterType'] = $group;
			$data['clusterId'] = $clusterid;
		}

		// Ensure that the current viewer is really allowed to post comments
		$comments = ES::comments($uid, $element, $verb, $group, $data, $streamid);

		if (!$comments->canComment()) {
			$this->view->setMessage('Not allowed to comment', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$giphy = ES::giphy();

		// Just to make sure that it is a valid GIPHY URL
		if (isset($data['giphy']) && (!$giphy->isValidUrl($data['giphy']) || !$giphy->isEnabledForComments())) {
			unset($data['giphy']);
		}

		// Construct the composite key
		$composite = $element . '.' . $group . '.' . $verb;

		$table = ES::table('comments');
		$table->element = $composite;
		$table->uid = $uid;
		$table->comment = $input;
		$table->created_by = $this->my->id;
		$table->created = ES::date()->toSQL();
		$table->parent = $parent;
		$table->params = $data;
		$table->stream_id = $streamid;
		$table->post_as = $postActor;

		// Exclude stream id if stream element is albums. #4984
		if ($element == 'albums') {
			$table->stream_id = 0;
		}

		// Process mentions for this comment
		$mentions = isset($data['mentions']) && !empty($data['mentions']) ? $data['mentions'] : '';

		if ($mentions) {
			$table->_mentions = $mentions;
		}

		$state = $table->store();

		if (!$state) {
			return $this->view->call(__FUNCTION__, $table);
		}

		// Process attachments
		$attachments = $this->input->get('attachmentIds', array(), 'array');

		if ($attachments && $this->config->get('comments.attachments.enabled')) {

			foreach ($attachments as $attachmentId) {

				$attachmentId = (int) $attachmentId;

				$uploader = ES::table('Uploader');
				$uploader->load($attachmentId);

				if (!ES::isImage($uploader->mime)) {
					die('Invalid attachment provided');
				}

				$file = ES::table('File');
				$file->uid = $table->id;
				$file->type = SOCIAL_TYPE_COMMENTS;

				$file->collection_id = 0;
				$file->state = 0;
				$file->hits = 0;

				// Copy some of the data from the temporary table.
				$file->copyFromTemporary($attachmentId);

				// We need to resize it if necessary
				if ($this->config->get('comments.resize.enabled') && $this->config->get('comments.resize.width') && $this->config->get('comments.resize.height')) {
					$file->resize($this->config->get('comments.resize.width'), $this->config->get('comments.resize.height'));
				}
			}
		}

		$doStreamUpdate = true;

		if ($streamid) {
			if ($element == 'photos') {
				$sModel = ES::model('Stream');
				$totalItem = $sModel->getStreamItemsCount($streamid);

				if ($totalItem > 1) {
					$doStreamUpdate = false;
				}
			}
		} else {
			// no stream id.
			$doStreamUpdate = false;

			// special handling for new comment on album page. #5455
			if ($element == 'albums' && $verb == 'create') {
				// lets get the latest photo stream that tied to this album
				$albumsModel = ES::model('Albums');
				$streamid = $albumsModel->getStreamId($uid);

				if ($streamid) {

					$doStreamUpdate = true;

					$sModel = ES::model('Stream');
					$totalItem = $sModel->getStreamItemsCount($streamid);

					// Only update the stream if the album has more than one photo
					if ($totalItem == 1) {
						$doStreamUpdate = false;
					}
				}
			}
		}

		if ($doStreamUpdate) {
			$stream = ES::stream();
			$stream->updateModified($streamid, $this->my->id, SOCIAL_STREAM_LAST_ACTION_COMMENT);
		}

		// Update goals progress
		$this->my->updateGoals('postcomment');

		$comments = array(&$table);
		$args = array(&$comments);

		// @trigger: onPrepareComments
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger($group, 'onPrepareComments', $args);

		return $this->view->call(__FUNCTION__, $table);
	}

	/**
	 * Updates a comment
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function update()
	{
		ES::requireLogin();
		ES::checkToken();

		$access = ES::access();
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Comments');
		$state = $table->load($id);

		// get the ori params;
		$params = $table->getParams()->toArray();

		if (!$state) {
			$this->view->setMessage($table->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		if (!$this->my->isSiteAdmin() && !($access->allowed('comments.edit') || ($access->allowed('comments.editown') && $table->isAuthor()))) {
			$this->view->setMessage('COM_EASYSOCIAL_COMMENTS_NOT_ALLOWED_TO_EDIT', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$input = $this->input->get('input', null, 'raw');
		$newData = ['comment' => $input, 'params' => $params];

		$mentions = ES::input()->get('mentions', '', 'var');

		if ($mentions) {
			$table->_mentions = $mentions;
		}

		$giphy = ES::giphy();
		$giphyUrl = $this->input->get('giphy', '', 'default');

		$giphyInValid = false;
		if ($giphyUrl && (!$giphy->isValidUrl($giphyUrl) || !$giphy->isEnabledForComments())) {
			$giphyUrl = '';
			$giphyInValid = true;
		}

		// User might removed their giphy url
		if (!$giphyUrl) {
			unset($newData['params']['giphy']);
		} else {
			$newData['params']['giphy'] = $giphyUrl;
		}

		$state = $table->update($newData);

		if (!$state) {
			$this->view->setMessage($table->getError(), ES_ERROR);
		}

		// Process attachments
		$attachments = $this->input->get('attachmentIds', array(), 'array');

		if ($attachments && $this->config->get('comments.attachments.enabled')) {

			foreach ($attachments as $attachmentId) {

				$attachmentId = (int) $attachmentId;

				$file = ES::table('File');
				$file->uid = $table->id;
				$file->type = SOCIAL_TYPE_COMMENTS;

				$file->collection_id = 0;
				$file->state = 0;
				$file->hits = 0;

				// Copy some of the data from the temporary table.
				$file->copyFromTemporary($attachmentId);

				// We need to resize it if necessary
				if ($this->config->get('comments.resize.enabled') && $this->config->get('comments.resize.width') && $this->config->get('comments.resize.height')) {
					$file->resize($this->config->get('comments.resize.width'), $this->config->get('comments.resize.height'));
				}
			}
		}

		$this->view->call(__FUNCTION__, $table, $giphyInValid);
	}

	/**
	 * Renders the remaining comments after a comment has been paginated
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function load()
	{
		ES::requireLogin();
		ES::checkToken();

		// Determines if the user can really read / view comments
		$access = $this->my->getAccess();

		if (!$access->allowed('comments.read')) {
			return $this->view->exception('COM_EASYSOCIAL_COMMENTS_NOT_ALLOWED_TO_READ');
		}

		$element = $this->input->get('element', '', 'string');
		$group = $this->input->get('group', SOCIAL_APPS_GROUP_USER, 'string');
		$verb = $this->input->get('verb', null, 'string');
		$uid = $this->input->get('uid', 0, 'int');
		$parentId = $this->input->get('parentId', 0, 'int');
		$clusterId = $this->input->get('clusterid', 0, 'int');

		// Pagination
		$start = $this->input->get('start', 0, 'int');
		$limit = $this->input->get('length', 0, 'int');

		$key = $element . '.' . $group . '.' . $verb;

		$options = array('element' => $key, 'uid' => $uid, 'start' => $start, 'limit' => $limit);

		// let set the includeReplies flag to false so that getComments will not inlcude the replies.
		// the reason is to get the proper parent counts.

		$options['includeReplies'] = 0;

		if ($parentId) {
			$options['parentid'] = $parentId;
		}

		if (!$parentId) {
			$options['parentid'] = 0;
		}

		if ($clusterId) {
			$options['clusterId'] = $clusterId;
		}

		$model = ES::model('Comments');
		$parents = $model->getComments($options);
		$count = count($parents);

		if (!$parents) {
			$this->view->setMessage('COM_EASYSOCIAL_COMMENTS_ERROR_RETRIEVING_COMMENTS', ES_ERROR);
		}

		// now we will get the comments replies.
		$comments = [];
		foreach ($parents as $comment) {

			$comments[] = $comment;

			// Retrieve the total childs to be shown initally that been set
			if ($comment->isParent() && $comment->child > 0) {
				$childs = $model->getChilds($comment->id, 0, $this->config->get('comments.totalreplies'), $options);
				$comments = array_merge($comments, $childs);
			}
		}


		return $this->view->call(__FUNCTION__, $comments, $count);
	}

	/**
	 * Removes a comment attachment on the site
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function deleteAttachment()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the attachment id
		$id = $this->input->get('id', 0, 'int');

		$file = ES::table('File');
		$file->load($id);

		// Check if the owner of the attachment is really correct
		if ($file->user_id != $this->my->id && !$this->my->isSiteAdmin()) {
			throw ES::exception(JText::_('You are not allowed to remove this file.'), 500);
		}

		// Delete the file
		$file->delete();

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Triggered to delete a comment
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Check for permission first
		$access = ES::access();

		// Get the comment id
		$id = $this->input->get('id', 0, 'int');

		// Load the comment object
		$table = ES::table('Comments');
		$state = $table->load($id);

		if (!$state) {
			$this->view->setMessage($table->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// There are cases where the app may need to allow the user to delete the comments.
		$apps = ES::apps();
		$apps->load(SOCIAL_TYPE_USER);

		$args = [&$table, &$this->my];

		$dispatcher = ES::dispatcher();
		$allowed = $dispatcher->trigger(SOCIAL_TYPE_USER, 'canDeleteComment', $args);

		$canDelete = $table->canDelete($this->my->id);

		if ($canDelete || in_array(true, $allowed)) {
			$isParent = $table->isParent();
			$state = $table->delete();

			if (!$state) {
				$this->view->setMessage($table->getError(), ES_ERROR);
			}

			return $this->view->call(__FUNCTION__, $isParent);
		}

		// Failed to delete comments because they do not have permissions
		$this->view->setMessage('COM_EASYSOCIAL_COMMENTS_NOT_ALLOWED_TO_DELETE', ES_ERROR);
	}

	/**
	 * Retrieves new updates for comments that should be updated on the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getUpdates()
	{
		$items = $this->input->get('data', '', 'default');

		// We should only be updating based on the limit
		$updateLimit = $this->config->get('comments.limit');

		$model = ES::model('Comments');
		$data = array();

		$disallowed = array('albums', 'photos');

		$string = ES::string();

		foreach ($items as $element => $blocks) {

			$data[$element] = array();

			foreach ($blocks as $blockKey => $block) {

				// Since the id's are always in the form of x.x, we need to get the difference between the id and the stream id
				$parts = explode('.', $blockKey);

				$streamid = isset($parts[0]) ? $parts[0] : '';
				$streamid = $string->escape($streamid);

				$uid = isset($parts[1]) ? $parts[1] : '';
				$uid = $string->escape($uid);

				// Construct mandatory options
				$element = $string->escape($element);
				$options = array('element' => $element, 'limit' => 0, 'parentid' => 0);

				// Ensure that the element for photos and albums doesn't check against the stream_id.
				// Because the albums and photos has a different method of retrieving the count.
				$elementTmp = explode('.', $element);

				if ($streamid && !in_array($elementTmp[0], $disallowed)) {
					$options['stream_id'] = $streamid;
				}

				if ($uid) {
					$options['uid'] = $uid;
				}

				// Initialize the start data
				$item = new stdClass();
				$item->ids = array();

				// Ids could be non-existent if the passed in array is empty
				$ids = array();

				if (array_key_exists('ids', $block) && is_array($block['ids'])) {
					$ids = $block['ids'];
				}

				// Current counters
				$currentTimestamp = $block['timestamp'];
				$options['since'] = ES::date($currentTimestamp)->toSql();

				// 1. We need to track new comments added since the "timestamp"
				$comments = $model->getComments($options);

				// Check for newly inserted comments
				if ($comments) {
					foreach ($comments as $comment) {
						// If newId is not in the list of ids, means it is a new comment
						if (!in_array($comment->id, $ids)) {
							$item->ids[$comment->id] = $comment->renderHTML();
						}
					}
				}

				// 2. We need to track removed comments. Simply by determining missing id's from the id's provided
				if ($ids) {
					$missing = $model->getMissingItems($ids);

					if ($missing) {
						foreach ($missing as $id) {
							$item->ids[$id] = false;
						}
					}
				}

				// Assign the new timestamp
				$item->timestamp = ES::date()->toUnix();

				$data[$element][$blockKey] = $item;
			}
		}

		return $this->view->call(__FUNCTION__, $data);
	}

	/**
	 * Renders the edit comment form
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function edit()
	{
		ES::requireLogin();

		// Check for request forgeries
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$comment = ES::table('Comments');
		$comment->load($id);

		if (!$comment->id || !$comment->canEdit()) {
			return $this->view->exception();
		}

		$this->view->call(__FUNCTION__, $comment);
	}
}
