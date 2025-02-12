<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class DiscussionsViewItem extends SocialAppsView
{
	/**
	 * Renders the discussion item page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function display($uid = null, $docType = null)
	{
		ES::requireLogin();

		$event = ES::event($uid);

		if (!$event->canViewItem()) {
			return $this->redirect($event->getPermalink(false));
		}

		// Load up the app params
		$params = $this->app->getParams();
		$sorting = $params->get('sorting', 'asc');

		// Get the discussion item
		$id = $this->input->get('discussionId', 0, 'int');
		$discussion = ES::table('Discussion');
		$discussion->load($id);

		if (!$discussion->id) {
			return $this->redirect($event->getPermalink(false));
		}

		// Get the author of the article
		$author = $discussion->getAuthor();

		// Get the url for the article
		$url = ESR::apps(array('layout' => 'canvas', 'customView' => 'item', 'uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT, 'id' => $this->app->getAlias(), 'discussionId' => $discussion->id), false);

		// Set the page title
		ES::document()->title($discussion->get('title'));

		// Increment the hits for this discussion item
		$discussion->hit();

		// Limit should be dependent on joomla's global list limit
		$limit = (int) $params->get('replies_limit', 20);

		$model = ES::model('Discussions');
		$replies = $model->getReplies($discussion->id, array('ordering' => 'created', 'direction' => $sorting, 'limit' => $limit));

		$pagination = $model->getPagination();

		$participants = $model->getParticipants($discussion->id);

		// Get the answer
		$answer = false;

		if ($discussion->answer_id) {
			$answer = ES::table('Discussion');
			$answer->load($discussion->answer_id);

			$answer->author = $answer->getAuthor();
		}

		// Get likes for discussion
		$discussion->likes = ES::likes($discussion->id, 'discussion', 'post', SOCIAL_APPS_GROUP_EVENT);

		// Determines if we should allow file sharing
		$access = $event->getAccess();
		$files = $access->get('files.enabled', true);

		$this->set('pagination', $pagination);
		$this->set('sorting', $sorting);
		$this->set('app', $this->app);
		$this->set('files', $files);
		$this->set('params', $params);
		$this->set('answer', $answer);
		$this->set('participants', $participants);
		$this->set('discussion', $discussion);
		$this->set('cluster', $event);
		$this->set('replies', $replies);
		$this->set('author', $author);

		echo parent::display('themes:/site/discussions/item/default');
	}
}
