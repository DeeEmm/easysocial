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

class EasySocialViewStory extends EasySocialSiteView
{
	/**
	 * Generates the story meta language string
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function buildStoryMeta()
	{
		$ids = $this->input->get('ids', 0, 'default');

		if (!is_array($ids)) {
			return;
		}

		$users = ES::user($ids);

		$caption = ES::themes()->html('string.with', $users);
		$caption = ESJString::trim($caption);

		return $this->ajax->resolve($caption);
	}

	/**
	 * Post processes after a user submits a story.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function create($streamItemTable = '', $clusterId = '', $clusterType = '')
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$displayOptions = array();

		// Scheduled post stream.
		// We'll need to pass scheduled flag because stream->getItem() only receive id.
		if ($streamItemTable->isScheduled()) {
			$displayOptions['isScheduled'] = true;
			$displayOptions['disableActions'] = true;
		}

		$stream = ES::stream();
		$stream->getItem($streamItemTable->uid, $clusterId, $clusterType, true, $displayOptions);

		$output = $stream->html();

		// If app explicitly wants to hide the stream item, do not display anything here.
		if (isset($streamItemTable->hidden) && $streamItemTable->hidden) {
			$output = '';
		}

		// If app explicitly wants to display notice, do it here.
		if (isset($streamItemTable->notice) && $streamItemTable->notice) {
			$theme = ES::themes();
			$theme->set('notice', $streamItemTable->notice);
			$output = $theme->output('site/stream/default/notice');
		}

		// Success Message
		$message = 'COM_EASYSOCIAL_NOTIFICATIONS_NEW_STORY_POSTED';
		$type = SOCIAL_MSG_SUCCESS;

		if (!$streamItemTable) {
			$message = 'COM_EASYSOCIAL_NOTIFICATIONS_NEW_STORY_POSTED_FAILED';
			$type = SOCIAL_MSG_ERROR;
		}

		$this->setMessage($message, $type);

		return $this->ajax->resolve($output, $streamItemTable->uid, $this->getMessage());
	}

	/**
	 * Post processes after a user submits a simple story.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function createFromModule($streamItemTable = '')
	{
		// Default message
		$message = JText::_('COM_EASYSOCIAL_NOTIFICATIONS_NEW_STORY_POSTED');

		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		// If we know that there is no argument, the process failed because they are not logged in.
		if (!$streamItemTable) {
			$message = JText::_('COM_EASYSOCIAL_NOTIFICATIONS_NEW_STORY_POSTED_FAILED');

			return $this->ajax->resolve(false, $message);
		}

		$stream = ES::stream();
		$stream->getItem($streamItemTable->uid, '', '', true);

		$output = $stream->html();

		// If app explicitly wants to hide the stream item, do not display anything here.
		if (isset($streamItemTable->hidden) && $streamItemTable->hidden) {
			$output = '';
		}

		return $this->ajax->resolve(true, $message, $output, $streamItemTable->uid);
	}

	/**
	 * Allows caller to retrieve the story form
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getForm()
	{
		ES::requireLogin();

		if (!$this->config->get('stream.quickposting.enabled')) {
			die();
		}

		$type = $this->input->get('type', '', 'default');
		$story = ES::story($type);

		// Assuming that the user is able to browse for selection, we should always allow them to change
		$story->showPostTo();

		if ($type != SOCIAL_TYPE_USER) {
			$uid = $this->input->get('uid', '', 'int');

			if (!$uid) {
				die('Invalid id provided');
			}

			$story->setCluster($uid, $type);
			$story->showPrivacy(false);
		}

		$contents = $story->html(false, false, array('showClusterAvatar' => true));

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to retrieve the story form
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getSelection()
	{
		ES::requireLogin();

		if (!$this->config->get('stream.quickposting.enabled')) {
			die();
		}

		$groups = $this->getGroups();
		$pages = $this->getPages();
		$events = $this->getEvents();

		$theme = ES::themes();
		$theme->set('groups', $groups);
		$theme->set('pages', $pages);
		$theme->set('events', $events);
		$output = $theme->output('site/story/selection/default');

		return $this->ajax->resolve($output);
	}

	private function filterClusterItems($items)
	{
		$clusters = array();

		if ($items) {
			foreach ($items as $cluster) {
				if ($this->my->canPostClusterStory($cluster->getType(), $cluster->id)) {
					$clusters[] = $cluster;
				}
			}
		}

		return $clusters;
	}

	private function getGroups($limit = 20, $search = '')
	{
		// Do not proceed if the feature is not enabled
		if (!$this->config->get('groups.enabled')) {
			return;
		}

		// Get a list of groups that I can post to
		$model = ES::model('Groups');
		$options = array(
			'featured' => '',
			'types' => 'participated',
			'state' => SOCIAL_STATE_PUBLISHED,
			'uid' => $this->my->id,
			// 'limit' => $limit,
			'search' => $search,
			'ordering' => 'name'
		);

		$result = $model->getGroups($options);
		$result = $this->filterClusterItems($result);

		return $result;
	}

	private function getEvents($limit = 20, $search = '')
	{
		// Do not proceed if the feature is not enabled
		if (!$this->config->get('events.enabled')) {
			return;
		}

		// Get a list of events that I can post to
		$model = ES::model('Events');
		$options = array(
			'creator_uid' => $this->my->id,
			'creator_type' => SOCIAL_TYPE_USER,
			'creator_join' => true,
			'type' => 'all',
			'featured' => 'all',
			'search' => $search,
			'limit' => $limit
		);

		$result = $model->getEvents($options);
		$result = $this->filterClusterItems($result);

		return $result;
	}

	private function getPages($limit = 20, $search = '')
	{
		// Do not proceed if the feature is not enabled
		if (!$this->config->get('pages.enabled')) {
			return;
		}

		$model = ES::model('Pages');
		$options = array(
			'state' => SOCIAL_STATE_PUBLISHED,
			'liked' => $this->my->id,
			'types' => 'all',
			'featured' => '',
			'limit' => $limit,
			'search' => $search,
			'ordering' => 'name'
		);

		$result = $model->getPages($options);
		$result = $this->filterClusterItems($result);

		return $result;
	}

	/**
	 * Renders the story form used in quick posting
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renderForm()
	{
		ES::requireLogin();

		// Determine if we should render form from specific type
		$type = $this->input->get('type', '', 'default');

		$story = ES::story(SOCIAL_TYPE_USER);
		$story->setTarget($this->my->id);

		$contents = $story->html(true, $type);

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to search for a specific list of cluster
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function searchSelection()
	{
		ES::requireLogin();

		$type = $this->input->get('type', '', 'cmd');
		$allowedTypes = array('group', 'event', 'page');

		if (!in_array($type, $allowedTypes)) {
			die('Invalid');
		}

		$query = $this->input->get('query', '', 'default');

		$clusters = array();


		$limit = 20;

		// Get a list of groups that I can post to
		if ($type == 'group') {
			$clusters = $this->getGroups($limit, $query);

			$emptyIcon = 'fa-users';
		}

		// Get a list of events that I can post to
		if ($type == 'event') {
			$clusters = $this->getEvents($limit, $query);
			$emptyIcon = 'far fa-calendar-alt';
		}

		// Get a list of pages that I can post to
		if ($type == 'page') {
			$clusters = $this->getPages($limit, $query);
			$emptyIcon = 'fa-briefcase';
		}

		$emptyText = JText::_('COM_ES_EMPTY_' . strtoupper($type) . '_POST_TO');

		$theme = ES::themes();
		$theme->set('clusters', $clusters);
		$theme->set('clusterType', $type);
		$theme->set('emptyText', $emptyText);
		$theme->set('emptyIcon', $emptyIcon);

		$output = $theme->output('site/story/selection/result');

		return $this->ajax->resolve($output);
	}

	/**
	 * Display flood protection warning
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function showFloodWarning()
	{
		$theme	= ES::themes();
		$output	= $theme->output('site/story/dialogs/flood.protection');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processes after a user updates a stream.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function update($streamTable = '', $clusterId = null, $clusterType = null, $state = null)
	{
		ES::requireLogin();

		$isModerated = false;

		// If this coming from cluster, we need to check for stream moderation state
		if ($clusterId && $state == SOCIAL_STREAM_STATE_MODERATE) {
			$isModerated = true;
		}

		$isScheduled = false;

		if ($state == SOCIAL_STREAM_STATE_SCHEDULED) {
			$isScheduled = true;
		}

		$displayOptions = array(
			'isScheduled' => $isScheduled
		);

		$theme = ES::themes();

		$stream = ES::stream();
		$streamItem = $stream->getItem($streamTable->uid, $clusterId, $clusterType, $isModerated, $displayOptions);
		$streamItem = $streamItem[0];

		$output = $stream->html(false, '', array('contentOnly' => true));
		$preview = '';
		$locationPreview = '';
		$giphy = false;

		if ($streamItem && $streamItem->hasPreview()) {
			$preview = $streamItem->preview;
		}

		if ($streamItem && $streamItem->hasGiphy()) {
			$theme->set('giphy', $streamItem->getGiphy());
			$giphy = $theme->output('site/giphy/preview/display');
		}

		if ($streamItem && $streamItem->location && ($this->config->get('stream.location.style') === 'inline') || $this->config->get('location.provider') == 'osm') {
			$theme->set('stream', $streamItem);
			$theme->set('isEdit', true);
			$theme->set('provider', $this->config->get('location.provider'));
			$locationPreview = $theme->output('site/stream/default/location');
		}

		$backgroundId = '';

		if ($streamItem && $streamItem->background_id) {
			$backgroundId = $streamItem->background_id;
		}

		$scheduled = '';

		if ($isScheduled && $streamItem->scheduled) {
			$scheduled = $streamItem->scheduled;
		}

		$privacyHtml = '';

		// if this stream is a photo stream,
		// there is a posibility the privacy might be changed
		// after the editing. we need to update the privacy form
		// in the stream item. #3587
		if ($streamItem && $streamItem->context == SOCIAL_STREAM_CONTEXT_PHOTOS && $streamItem->hasPrivacy()) {
			$privacyHtml = $streamItem->getPrivacyHtml();
		}

		return $this->ajax->resolve($output, $streamTable->uid, '', $preview, $backgroundId, $locationPreview, $privacyHtml, $giphy, $scheduled);
	}
}
