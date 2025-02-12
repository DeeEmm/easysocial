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

class EasySocialControllerRepost extends EasySocialController
{
	/**
	 * Retrieves a list of users who reposted a stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRepostAuthors()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the current stream property.
		$id = $this->input->get('id', 0, 'int');
		$element = $this->input->get('element', '', 'string');

		// If id is invalid, throw an error.
		if (!$id || !$element) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
		}

		$model = ES::model('Repost');
		$users = $model->getRepostUsers($id, $element, false);

		return $this->view->call(__FUNCTION__, $users);
	}

	/**
	 * Toggle the likes on an object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function share()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the stream / album / photo id depending on the element
		$id = $this->input->get('id', 0, 'int');
		$element = $this->input->get('element', '', 'string');
		$group = $this->input->get('group', SOCIAL_APPS_GROUP_USER, 'string');
		$clusterId = $this->input->get('clusterId', '', 'int');
		$clusterType = $this->input->get('clusterType', '', 'string');
		$content = $this->input->get('content', '', 'default');
		$shareAs = $this->input->get('shareAs', SOCIAL_TYPE_USER, 'string');
		$streamId = $this->input->get('streamId', '', 'int');
		$currentViewName = $this->input->get('viewName', '', 'cmd');

		// flag to determine if repost should post as user stream or cluster stream.
		// if user stream, it will respect user's privacy
		// if cluster stream, it will respect cluster's type
		// #4364
		$repostOn = $this->input->get('repostOn', SOCIAL_TYPE_USER, 'string');

		if (!$id || !$element) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
		}

		$share = ES::get('Repost', $id, $element, $group, $clusterId, $clusterType, $shareAs);

		if ($streamId) {
			$share->setStreamId($streamId);
		}

		// make sure user can repost on this item.
		if (!$share->canShare()) {
			return $this->view->exception('COM_EASYSOCIAL_REPOST_ERROR_REPOSTING');
		}

		$state = $share->add($this->my->id, $content);

		// If there's an error, log this down here.
		if ($state === false) {
			$this->view->setMessage('COM_EASYSOCIAL_REPOST_ERROR_REPOSTING', ES_ERROR);
			return $this->view->call(__FUNCTION__, $id, $element, $group);
		}

		// Check if there are mentions provided from the post.
		$mentions = $this->input->get('mentions', [], 'array');

		// Format the json string to array
		if ($mentions) {
			foreach($mentions as &$mention) {
				$mention = json_decode($mention);
			}
		}

		// Now lets determine if we need to add the stream or not.
		$streamId = 0;

		if ($state == true) {
			// this is an new share object.
			// lets add this share into stream.
			$stream	= ES::stream();
			$streamTemplate	= $stream->getTemplate();

			// Set the actor.
			$streamTemplate->setActor($state->user_id, SOCIAL_TYPE_USER);

			// Set the context.
			$streamTemplate->setContext($state->id, SOCIAL_TYPE_SHARE);

			// Set the post_as column
			$streamTemplate->setPostAs($shareAs);

			// set the target. photo / stream
			$streamTemplate->setTarget($id);

			// Set mentions
			$streamTemplate->setMentions($mentions);

			// Set the verb.
			$streamTemplate->setVerb('add' . '.' . $element);

			$streamTemplate->setType('full');

			$streamTemplate->setAccess('core.view');

			$moderation = false;
			$moderationNoti = false;
			$cluster = false;
			$isModeration = false;

			if ($repostOn != SOCIAL_TYPE_USER) {
				// always set to 'everyone' first #4364
				$streamTemplate->setAccess('core.view', 0);

				if ($clusterId && $clusterType) {
					$cluster = ES::cluster($clusterType, $clusterId);
					$streamTemplate->setCluster($clusterId, $clusterType, $cluster->type);

					// check for the stream moderation
					$clusterParams = $cluster->getParams();
					$moderation = $clusterParams->get('stream_moderation', false);
					$moderationNoti = $clusterParams->get('stream_moderation_notification', false);

					if ($moderation && (!$cluster->isAdmin() || !$cluster->isOwner() || !$this->my->isSiteAdmin())) {
						$streamTemplate->setState(SOCIAL_STREAM_STATE_MODERATE);
						$isModeration = true;
					}
				}
			}

			// Create the stream data.
			$streamItem = $stream->add($streamTemplate);
			$streamId = $streamItem->uid;

			// notify to the cluster admin if the stream item under moderation
			if ($streamId && $moderation && $moderationNoti && $streamItem->state === SOCIAL_STREAM_STATE_MODERATE) {

				if ($cluster && ($clusterType == SOCIAL_TYPE_GROUP || $clusterType == SOCIAL_TYPE_PAGE)) {
					$emaildata = [
						'userId' => $streamItem->actor_id,
						'content' => $streamTemplate->content,
						'title' => 'COM_EASYSOCIAL_EMAILS_STREAM_NEW_POST_MODERATION_TITLE',
						'template' => 'site/stream/post.moderation',
						'uid' => $streamItem->uid
					];
					$cluster->notifyAdminsModeration($emaildata);
				}
			}
		}

		return $this->view->call(__FUNCTION__, $id, $element, $group, $streamId, $currentViewName, $clusterId, $clusterType, $isModeration);
	}
}
