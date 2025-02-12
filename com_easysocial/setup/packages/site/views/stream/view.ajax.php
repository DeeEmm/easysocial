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

class EasySocialViewStream extends EasySocialSiteView
{
	/**
	 * Confirmation for deleting stream item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$contents = $theme->output('site/stream/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the edit stream form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function edit()
	{
		$id = $this->input->get('id', 0, 'int');
		$appid = $this->input->get('appid', 0, 'int');

		$stream = ES::table('Stream');
		$stream->load($id);

		// Ensure that the user really can edit this
		if (!$stream->canEdit()) {
			return $this->ajax->reject();
		}

		$mentions = $stream->getTags(array('user', 'hashtag', 'emoticon'));

		// Load the mood
		$mood = ES::table('Mood');
		$mood->load($stream->mood_id);

		$story = ES::story();

		if ($stream->cluster_id && $stream->cluster_type) {
			//$story = ES::story($stream->cluster_type);
			$story->setCluster($stream->cluster_id, $stream->cluster_type);
			$story->showPrivacy(false);
		}

		$story->setContent($stream->content);
		$story->setMentions($mentions);
		$story->setMood($mood);

		// Retrieve scheduled date for edit form.
		$scheduled = $stream->getScheduled();
		$story->setScheduledDate($scheduled);

		$contents = $story->editForm(false, $stream->id);

		// now we need to check if this app can handle content editing or not.
		if ($appid) {

			$triggerApp = true;

			$table = ES::table('App');
			$table->load($appid);

			$file = SOCIAL_APPS . '/' . $table->group . '/' . $table->element . '/' . $table->element . '.php';

			jimport('joomla.filesystem.file');

			if (!JFile::exists($file)) {
				$triggerApp = false;
			}

			require_once($file);

			$appClass = 'Social' . ucfirst($table->group) . 'App' . ucfirst($table->element);
			if (!class_exists($appClass)) {
				$triggerApp = false;
			}

			$app = new $appClass();
			$app->element = $table->element;
			$app->group = $table->group;

			if (!method_exists($app, 'onPrepareStoryEditForm')) {
				$triggerApp = false;
			}

			if ($triggerApp) {

				// onPrepareStream for the specific context
				$arguments = array(&$story, &$stream);
				// $state = $class->$event($field, $arguments);
				$contents = call_user_func_array(array($app, 'onPrepareStoryEditForm'), $arguments);
			}
		}

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post process after a stream item is published on the site
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function publish($stream)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Renders the confirmation dialog to delete a filter
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function confirmFilterDelete()
	{
		$theme = ES::themes();
		$contents = $theme->output('site/stream/dialogs/filter.delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post process after adding a new filter
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function addFilter($filter)
	{
		ES::requireLogin();

		$cluster = '';
		$url = ESR::dashboard(array('type' => 'filter', 'filterid' => $filter->getAlias()));

		if ($filter->utype != 'user') {
			$cluster = ES::cluster($filter->utype, $filter->uid);

			$options = array('layout' => 'item', 'id' => $cluster->getAlias(), 'filterId' => $filter->getAlias());
			$url = call_user_func_array(array('ESR', $cluster->getTypePlural()), array($options));
		}

		$theme = ES::themes();
		$theme->set('filter', $filter);
		$theme->set('cluster', $cluster);
		$theme->set('filterId', 0);
		$theme->set('url', $url);
		$theme->set('fid', '');

		$content = $theme->output('site/stream/sidebar.filters');

		return $this->ajax->resolve($content, JText::_('COM_EASYSOCIAL_STREAM_FILTER_SAVED'));
	}

	/**
	 * Post processing after an item is already pinned
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function addSticky($sticky)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after an item is already unpinned
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function removeSticky($sticky)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after an item is already bookmarked
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bookmark($bookmark)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after an item is already bookmarked
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function removeBookmark($bookmark)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after a stream item is deleted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete()
	{
		$redirectUrl = ESR::dashboard();
		return $this->ajax->resolve($redirectUrl);
	}

	/**
	 * Retrieves the current date
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getCurrentDate()
	{
		$date = ES::date()->toSql();

		return $this->ajax->resolve($date);
	}

	/**
	 * Post processing after retrieving new stream updates
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUpdates($stream)
	{
		$content = $stream->html(true);
		$nextDate = ES::date()->toSql();
		$streamIds = array();
		$ids = $stream->getUids();

		if (!empty($ids)) {
			$streamIds = $ids;
		}

		return $this->ajax->resolve($content, $nextDate, $streamIds);
	}

	/**
	 * Retrieves the filter form for the stream
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilterFormDialog()
	{
		ES::requireLogin();

		// Get the type of the filter
		$allowedTypes = array(SOCIAL_TYPE_USER, SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE);
		$type = $this->input->get('type', '', 'word');

		if (!in_array($type, $allowedTypes)) {
			return $this->ajax->reject();
		}

		$uid = $this->input->get('uid', 0, 'int');

		// If type is user, we automatically set uid as user id
		if ($type == SOCIAL_TYPE_USER) {
			$uid = $this->my->id;
		}

		// Load the correct object
		$object = $this->my;

		// Set filter description
		$desc = JText::_('COM_EASYSOCIAL_STREAM_FILTER_DESCRIPTION');

		if ($type != SOCIAL_TYPE_USER) {
			$object = ES::cluster($type, $uid);
			$desc = JText::sprintf('COM_ES_STREAM_FILTER_CLUSTERS_DESC', $type);
		}

		if (!$uid && !$object->id) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_GROUPS_INVALID_ID_PROVIDED'));
		}

		// Perform the necessary checks to see if the object is allowing the current user to
		// add / edit filters on the respective node.
		if ($type != SOCIAL_TYPE_USER && !$object->canCreateStreamFilter()) {
			return $this->ajax->reject('COM_EASYSOCIAL_STREAM_FILTER_NOT_ALLOWED');
		}

		// Get the filter id if the user is editing the filter
		$id = $this->input->get('id', 0, 'int');

		// Try to load the filter
		$filter = ES::table('StreamFilter');

		if ($id) {
			$filter->load($id);
		}

		// User might want to create a new filter based on a hashtag
		$hashtag = $this->input->get('hashtag', '', 'default');

		$theme = ES::themes();
		$theme->set('hashtag', $hashtag);
		$theme->set('desc', $desc);
		$theme->set('uid', $uid);
		$theme->set('type', $type);
		$theme->set('filter', $filter);

		$output = $theme->output('site/stream/dialogs/filter.form');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after checking for new stream updates
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function checkUpdates($data, $source, $type, $uid, $currentdate)
	{
		// Get the start date
		$startdate = ES::date()->toSql();

		if (count($data) <= 0) {
			return $this->ajax->resolve($data, '', $startdate);
		}

		if ($type == 'list') {
			$type = $type . '-' . $uid;
		}

		$contents = '';

		foreach ($data as $item) {

			$item = (object) $item;

			if ($item->type == $type && $item->cnt && $item->cnt > 0) {
				$theme = ES::themes();

				$theme->set('count', $item->cnt);
				$theme->set('currentdate', $currentdate);
				$theme->set('type', $type);
				$theme->set('uid', $uid);

				$contents = $theme->output('site/stream/new.updates.button');
			}
		}

		return $this->ajax->resolve($data, $contents, $startdate);
	}

	/**
	 * Post processing after actor stream is hidden
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hide($type, $actor, $context)
	{
		$user = ES::user($actor);

		$theme = ES::themes();
		$theme->set('actor', $user);
		$theme->set('context', $context);
		$theme->set('type', $type);

		$contents = $theme->output('site/stream/item/hidden.' . $type);

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after unhiding a stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unhide()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post process after translating stream contents
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function translate($output)
	{
		$output = '<h4><b>' . JText::_('COM_EASYSOCIAL_TRANSLATED_TEXT') . ':</b></h4><div>' . $output . '</div>';

		return $this->ajax->resolve($output);
	}
}
