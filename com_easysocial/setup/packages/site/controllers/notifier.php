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

class EasySocialControllerNotifier extends EasySocialController
{
	/**
	 * check for new notification by apps
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function check()
	{
		ES::requireLogin();
		ES::checkToken();

		$post = $this->input->get('data', array(), 'array');

		// dump($post);

		$info = new stdClass();
		$info->total = -1;
		$info->data = '';

		$data = new stdClass();

		// new stream
		if ($this->config->get('stream.updates.enabled') && isset($post['stream'])) {

			$data->stream = clone $info;

			$totalCount = 0;

			// $postData = $post['stream'];
			$streamData = $post['stream'];

			$container = array();

			foreach ($streamData as $key => $postData) {

				$type = isset($postData['type']) ? $postData['type'] : '';
				$view = isset($postData['view']) ? $postData['view'] : '';
				$currentdate = isset($postData['currentdate']) ? $postData['currentdate'] : '';
				$exclude = isset($postData['exclude']) ? $postData['exclude'] : '';
				$uid = isset($postData['id']) ? $postData['id'] : '';

				// make sure the exlude ids are all in integer
				if ($exclude) {
					$tmp = explode(',', $exclude);
					$tmpEx = array();
					foreach ($tmp as $ex) {
						$val = (int) $ex;

						if ($val) {
							$tmpEx[] = $val;
						}
					}
					$exclude = implode(',', $tmpEx);
				}

				$container[$key] = '';

				if ($currentdate && $type) {
					if ($type == 'module') {
						// lets overwrite the data so that we can get the updates.
						$type = 'everyone';
						$view = 'dashboard';
					}

					$model = ES::model('Stream');

					//cluster types
					$clusters = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT);

					$clusterViews = array('groups' => 'group', 'events' => 'event', 'pages' => 'page');
					$clusterType = isset($clusterViews[$view]) ? $clusterViews[$view] : '';
					$isCluster = false;

					if (in_array($clusterType, $clusters)) {
						$isCluster = true;
						$newStream = $model->getClusterUpdateCount($view, $currentdate, $clusterType, $uid, $exclude);
					} else {
						$newStream = $model->getUpdateCount($view, $currentdate, $type, $uid, $exclude);
					}

					// Get the start date
					$startdate = ES::date()->toSql();

					$data->stream->total += count($newStream);


					$streamInfo = new stdClass();
					$streamInfo->startdate = '';
					$streamInfo->contents = '';
					$streamInfo->data = $newStream;

					$streamInfo->startdate = $startdate;

					if (count($newStream) > 0) {
						$contents = '';
						foreach ($newStream as $item) {
							$item = (object) $item;

							if ((($item->type == $type && !$isCluster) || ($isCluster && $item->type == $clusterType)) && $item->cnt && $item->cnt > 0) {
							// if ($item->type == $type && $item->cnt && $item->cnt > 0) {

								$theme = ES::themes();

								$theme->set('count', $item->cnt);
								$theme->set('currentdate', $currentdate);
								if ($isCluster) {
									$theme->set('type', $clusterType);
								} else {
									$theme->set('type', $type);
								}
								$theme->set('uid', $uid);

								$contents = $theme->output('site/stream/new.updates.button');
							}
						}

						$streamInfo->contents = $contents;
					}

					// var_dump($key, $streamInfo);

					$container[$key] = $streamInfo;
				}

			}

			$data->stream->data = $container;
		}

		// comments
		if (isset($post['comment']) && $post['comment']) {
			$data->comment = clone $info;

			$newComments = $this->getCommentUpdates($post['comment']);
			$data->comment->total = count($newComments);
			$data->comment->data = $newComments;
		}

		// now we will trigger to user apps
		// Get apps library.
		$apps = ES::apps();

		// Try to load user apps
		$state = $apps->load(SOCIAL_APPS_GROUP_USER);
		if ($state) {
			// Only go through dispatcher when there is some apps loaded, otherwise it's pointless.
			$dispatcher = ES::dispatcher();

			$args = array(&$data);

			// onNotifierCheck for the specific context
			$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onNotifierCheck', $args);
		}

		// dump($data);

		return $this->view->call(__FUNCTION__, $data);
	}

	/**
	 * Retrieves a list of comments that needs to be updated in realtime
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	private function getCommentUpdates($items)
	{
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

		return $data;
	}
}
