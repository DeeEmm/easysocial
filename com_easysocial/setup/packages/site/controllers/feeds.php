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

class EasySocialControllerFeeds extends EasySocialController
{
	/**
	 * Allows caller to create a new feed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function save()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the cluster
		$uid = $this->input->get('uid', 0, 'int');
		$cluster = ES::cluster($uid);

		// Ensure that the viewer is part of the cluster
		if (!$cluster->canCreateFeeds()) {
			return $this->view->exception('APP_FEEDS_NOT_ALLOWED_TO_CREATE');
		}

		// Get app's id.
		$id = $this->input->get('appId', '', 'int');

		// Get feed table
		$table = ES::table('Rss');
		$table->uid = $cluster->id;
		$table->type = $cluster->getType();
		$table->user_id = $this->my->id;
		$table->title = $this->input->get('title', '', 'default');
		$table->url = $this->input->get('url', '', 'default');
		$table->state = true;

		// Load up the feed parser
		$model = ES::model('RSS');
		$parser = $model->getParser($table->url);

		if ($parser) {
			$table->description = $parser->description;
		}

		// Try to save the feed now
		$state = $table->store();

		if (!$state) {
			return $this->ajax->reject($table->getError());
		}

		if ($parser) {
			$table->total = $parser->count();
			$table->parser = $parser;
		}

		// Create new stream item when a new feed is created
		$app = $cluster->getApp('feeds');
		$params = $app->getParams();

		if ($params->get('stream_create', true)) {
			$table->createStream('create');
		}

		$theme = ES::themes();

		$theme->set('totalDisplayed', $table->total);
		$theme->set('feed', $table);
		$theme->set('cluster', $cluster);
		$theme->set('user', $this->my);

		$output = $theme->output('site/feeds/default/item');

		return $this->ajax->resolve($output);
	}

	/**
	 * Allows caller to delete feed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		$uid = $this->input->get('uid', 0, 'int');

		$cluster = ES::cluster($uid);

		$id = $this->input->get('id', 0, 'int');
		$feed = ES::table("Rss");
		$feed->load($id);

		// Ensure that this is the owner or the group admin
		if (!$cluster->isAdmin() && $feed->user_id != $this->my->id) {
			return $this->view->exception('APP_FEEDS_NOT_ALLOWED_TO_DELETE');
		}

		if (!$id || !$feed->id) {
			return $this->view->exception('APP_FEEDS_INVALID_ID_PROVIDED');
		}

		// Try to delete the feed now.
		$state = $feed->delete();

		if (!$state) {
			return $this->view->setMessage($feed->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__);
	}
}
