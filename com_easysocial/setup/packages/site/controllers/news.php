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

class EasySocialControllerNews extends EasySocialController
{
	/**
	 * Allows caller to delete an announcement
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$news = ES::table('ClusterNews');
		$news->load($id);

		if (!$news->id || !$id) {
			return $this->view->exception('APP_NEWS_INVALID_NEWS_ID');
		}

		// Get the cluster
		$cluster = ES::cluster($news->cluster_id);

		if (!$cluster->canDeleteNews($news)) {
			return $this->view->exception('APP_NEWS_NOT_ALLOWED_TO_DELETE');
		}

		$news->delete();

		$this->view->setMessage('APP_GROUP_NEWS_DELETED_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $news, $cluster);
	}

	/**
	 * Allows caller to save a new news item
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function save()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the posted data
		$post = $this->input->post->getArray();

		// Get the post
		$id = $this->input->get('id', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		// Get the cluster
		$cluster = ES::cluster($uid);

		if (!$cluster || !$cluster->id) {
			return $this->view->exception('APP_NEWS_INVALID_CLUSTER_ID');
		}

		$news = ES::table('ClusterNews');
		$news->load($id);

		if (!$cluster->canCreateNews()) {
			return $this->view->exception('APP_NEWS_NOT_ALLOWED_TO_CREATE');
		}

		$options = [
			'title' => $this->input->get('title', '', 'default'),
			'content' => $this->input->get('news_content', '', 'raw'),
			'comments' => $this->input->get('comments', 0, 'bool'),
			'state' => SOCIAL_STATE_PUBLISHED,
			'content_type' => $this->input->get('content_type', '', 'cmd')
		];

		// Only bind this if it's a new item
		if (!$news->id) {
			$options['cluster_id'] = $cluster->id;
			$options['created_by'] = $this->my->id;
			$options['hits'] = 0;
		}

		// Bind the data
		$news->bind($options);

		// Check if there are any errors
		if (!$news->check()) {
			$this->view->setMessage($news->getError(), ES_ERROR);

			return $this->view->call(__FUNCTION__, $news, $cluster);
		}

		$message = !$news->id ? 'APP_GROUP_NEWS_CREATED_SUCCESSFULLY' : 'APP_GROUP_NEWS_UPDATED_SUCCESSFULLY';

		$news->store();

		$this->view->setMessage($message, SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $news, $cluster);
	}
}
