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

class EasySocialControllerStream extends EasySocialController
{
	/**
	 * Allows caller to publish a stream item on the site
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function publish()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the stream id
		$id = $this->input->get('id', 0, 'int');

		// Load up the stream
		$stream = ES::table('Stream');
		$stream->load($id);
		$group = SOCIAL_TYPE_USER;

		// Ensure that the user is allowed to publish stream.
		if ($stream->cluster_type && $stream->cluster_id) {
			$cluster = ES::cluster($stream->cluster_type, $stream->cluster_id);
			$group = $cluster->getType();

			if (!$cluster->isAdmin()) {
				throw ES::exception('Invalid request', 500);
			}

			if (!$stream->isScheduled()) {
				$stream->publish();
			}
		}

		if ($stream->isScheduled()) {
			$scheduled = ES::table('StreamScheduled');
			$scheduled->load(array('stream_id' => $stream->id));

			$streamItem = ES::table('StreamItem');
			$streamItem->load(array('uid' => $stream->id));

			// Load up the dispatcher so that we can trigger this.
			$dispatcher = ES::dispatcher();

			// Build the arguments for the trigger
			$args = array(&$stream, &$streamItem, &$scheduled);

			// @trigger onScheduledAppStoryPublish
			$dispatcher->trigger($group, 'onPublishScheduledAppStory', $args);
		}

		// Super User should be able to publish moderated post
		if ($this->my->isSiteAdmin()) {
			return $stream->publish();
		}

		return $this->view->call(__FUNCTION__, $stream);
	}

	/**
	 * Allows caller to bookmark a stream item
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function addSticky()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the stream object
		$id = $this->input->get('id', 0, 'int');

		$stream = ES::table('Stream');
		$stream->load($id);

		// Validation on the item so that prevent unauthorized attempts.
		if (!$stream->canViewItem() || !$stream->canSticky()) {
			return $this->view->exception('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID');
		}

		$sticky = ES::table('StreamSticky');
		$options = array('stream_id' => $stream->id);
		$exists = $sticky->load($options);

		// Stream item has already been bookmarked before
		if ($exists) {
			return $this->view->exception('COM_EASYSOCIAL_STREAM_ITEM_PINNED_BEFORE');
		}

		$sticky->stream_id = $stream->id;

		// Try to save the sticky
		$state = $sticky->store();

		// now update the stream.sticky_id
		if ($state) {
			$stream->sticky_id = $sticky->id;
			$stream->store();
		}

		return $this->view->call(__FUNCTION__, $sticky);
	}

	/**
	 * Allows caller to remove a bookmark for the stream
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function removeSticky()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the stream object
		$id = $this->input->get('id', 0, 'int');

		$stream = ES::table('Stream');
		$stream->load($id);

		// Validation on the item so that prevent unauthorized attempts.
		if (!$stream->canViewItem() || !$stream->canSticky()) {
			return $this->view->exception('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID');
		}

		// Check if this item has already been bookmarked
		$sticky = ES::table('StreamSticky');
		$options = array('stream_id' => $stream->id);
		$state = $sticky->load($options);

		// Stream item has already been bookmarked before
		if (!$state) {
			return $this->view->exception('COM_EASYSOCIAL_STREAM_STICKY_INVALID_ID_PROVIDED');
		}

		// Delete the sticky
		$state = $sticky->delete();

		if ($state) {
			$stream->sticky_id = 0;
			$stream->store();
		}

		return $this->view->call(__FUNCTION__, $sticky);
	}

	/**
	 * Allows caller to bookmark a stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bookmark()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the stream object
		$id = $this->input->get('id', 0, 'int');

		// Retrieve stream
		$stream = ES::table('Stream');
		$stream->load($id);

		if (!$stream->canViewItem()) {
			return $this->view->exception('COM_EASYSOCIAL_STREAM_INVALID_STREAM_ID');
		}

		$bookmark = ES::table('Bookmark');

		// Check if this item has already been bookmarked
		$options = array('user_id' => $this->my->id, 'uid' => $stream->id, 'type' => SOCIAL_TYPE_STREAM);
		$state = $bookmark->load($options);

		// Stream item has already been bookmarked before
		if ($state) {
			return $this->view->exception('COM_EASYSOCIAL_BOOKMARKS_STREAM_ITEM_BOOKMARKED_BEFORE');
		}

		$bookmark->uid = $stream->id;
		$bookmark->type = SOCIAL_TYPE_STREAM;
		$bookmark->user_id = $this->my->id;

		// Try to save the bookmark
		$bookmark->store();

		return $this->view->call(__FUNCTION__, $bookmark);
	}

	/**
	 * Allows caller to remove a bookmark for the stream
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function removeBookmark()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the stream object
		$id = $this->input->get('id', 0, 'int');

		$stream = ES::table('Stream');
		$stream->load($id);

		$bookmark = ES::table('Bookmark');

		// Check if this item has already been bookmarked
		$options = array('user_id' => $this->my->id, 'uid' => $stream->id, 'type' => SOCIAL_TYPE_STREAM);
		$state = $bookmark->load($options);

		// Stream item has already been bookmarked before
		if (!$state) {
			return $this->view->exception('COM_EASYSOCIAL_BOOKMARKS_INVALID_ID_PROVIDED');
		}

		// Delete the bookmark
		$bookmark->delete();

		return $this->view->call(__FUNCTION__, $bookmark);
	}

	/**
	 * Allows caller to create a new filter or update an existing one
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function saveFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the filter item
		$id = $this->input->get('id', 0, 'int');
		$title = $this->input->get('title', '', 'default');
		$hashtag = trim($this->input->get('hashtag', '', 'default'));

		// Only site admin can change assign sitewide value
		$global = ES::isSiteAdmin() ? $this->input->get('global', false, 'bool') : false;

		// Load the filter table
		$filter = ES::table('StreamFilter');

		if ($id) {
			$filter->load($id);
		}

		$delete = $this->input->get('delete', false, 'bool');

		if ($delete) {
			if (!$id) {
				$this->view->setMessage('Invalid filter id', ES_ERROR);
				return $this->view->call('deleteFilter');
			}

			// Make sure the user is the filter owner before we delete.
			$filter->load(array('id' => $id, 'uid' => $this->my->id, 'utype' => 'user'));

			if (!$filter->id) {
				$this->view->setMessage('Filter not found', ES_ERROR);
				return $this->view->call('deleteFilter');
			}

			$filter->deleteItem();
			$filter->delete();

			$this->view->setMessage('COM_EASYSOCIAL_STREAM_FILTER_DELETED');
			return $this->view->call('deleteFilter');
		}

		// Get the uid and type
		$uid = $this->input->get('uid', $this->my->id, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'string');

		// Set the filter attributes
		$filter->title = $title;
		$filter->uid = $uid;
		$filter->utype = $type;
		$filter->user_id = $this->my->id;
		$filter->global = $global;

		// Test permissions
		if ($filter->utype != SOCIAL_TYPE_USER) {
			$cluster = ES::cluster($filter->utype, $filter->uid);

			if (!$cluster->canCreateStreamFilter()) {
				return $this->view->exception('COM_EASYSOCIAL_STREAM_FILTER_NOT_ALLOWED');
			}

			$filter->global = true;
		}

		// We need to have all filter data before we can proceed with the checking
		if (!$title) {
			$this->view->setMessage('COM_EASYSOCIAL_STREAM_FILTER_WARNING_TITLE_EMPTY', ES_ERROR);
			return $this->view->call(__FUNCTION__, $filter, true);
		}

		if (!$hashtag) {
			$this->view->setMessage('COM_EASYSOCIAL_STREAM_FILTER_WARNING_HASHTAG_EMPTY', ES_ERROR);
			return $this->view->call(__FUNCTION__, $filter, true);
		}

		$filter->store();

		// Try to process the hashtags
		if ($hashtag) {
			$hashtag = ESJString::str_ireplace('#', '', $hashtag);
			$hashtag = ESJString::str_ireplace(' ', '', $hashtag);
			$hashtag = str_replace( ' ', '', $hashtag);

			// Get the filter item
			$item = ES::table('StreamFilterItem');
			$item->load(array('filter_id' => $filter->id, 'type' => 'hashtag'));

			$item->filter_id = $filter->id;
			$item->type = 'hashtag';
			$item->content = $hashtag;
			$item->store();
		}

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_FILTER_SAVED', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $filter);
	}

	/**
	 * Retrieve new stream updates that is not visible to the current user yet
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUpdates()
	{
		ES::requireLogin();
		ES::checkToken();

		$type = $this->input->get('type', 'me', 'word');
		$uid = $this->input->get('id', 0, 'int');
		$source = $this->input->get('source', '', 'word');
		$currentdate = $this->input->get('currentdate', '', 'default');
		$streamType = ($type == 'following') ? 'follow' : SOCIAL_TYPE_USER;
		$userId = '';
		$listId = '';

		// Filter by a specific list
		if ($source == 'dashboard' && $type == 'me' && !empty($uid)) {
			$listId = $uid;
		}

		// Filter by a specific user
		if ($source == 'profile') {
			$userId = $uid;
		}

		// Default filtering options
		$options = array(
						'userId' => $userId,
						'listId' => $listId,
						'context' => SOCIAL_STREAM_CONTEXT_TYPE_ALL,
						'type' => $streamType,
						'limitStart' => $currentdate,
						'direction' => 'later',
						'limit' => 0
		);


		if ($type == 'everyone') {
			$options['guest'] = true;
		}

		// Cluster types
		$clusters = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE);

		if (in_array($type, $clusters)) {

			$options = array(
								'clusterId' => $uid,
								'clusterType' => $type,
								'limitStart' => $currentdate,
								'direction' => 'later',
								'limit' => 0
			);
		}

		$stream = ES::stream();
		$stream->get($options);

		return $this->view->call(__FUNCTION__, $stream);
	}

	/**
	 * Check for updates
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function checkUpdates()
	{
		ES::checkToken();

		$type = $this->input->get('type', 'me', 'string');
		$source = $this->input->get('source', '', 'word');
		$uid = $this->input->get('id', 0, 'int');
		$exclude = $this->input->get('exclude', '', 'default');
		$currentDate = $this->input->get('currentdate', '', 'default');

		// For module, we need to treat it differently
		if ($type == 'module') {
			$type = 'everyone';
			$source = 'dashboard';
		}

		$data = null;
		$model = ES::model('Stream');

		$clusters = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE);

		if (in_array($type, $clusters)) {
			$data = $model->getClusterUpdateCount($source, $currentDate, $type, $uid, $exclude);
		} else {
			$data = $model->getUpdateCount($source, $currentDate, $type, $uid, $exclude);
		}

		return $this->view->call(__FUNCTION__, $data, $source, $type, $uid, $currentdate);
	}

	/**
	 * Renders more activity stream items for guests
	 *
	 * @since 	1.0
	 * @access 	public
	 */
	public function loadmoreGuest()
	{
		ES::requireLogin();
		ES::checkToken();

		// next start date
		$startlimit = $this->input->get('startlimit', 0, 'int');

		// Get the stream
		$stream = ES::stream();
		$stream->getPublicStream(SOCIAL_STREAM_GUEST_LIMIT, $startlimit);

		return $this->view->call(__FUNCTION__, $stream);

	}

	/**
	 * Retrieve more stream items on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function loadmore()
	{
		// Determines if this is a cluster view
		$isCluster = $this->input->get('iscluster', false, 'default');
		$clusterId = $this->input->get('clusterId', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'default');

		// Get the type of the stream to load.
		$filter = $this->input->get('type', '', 'word');
		$source = $this->input->get('source', '', 'word');
		$startlimit = $this->input->get('startlimit', 0, 'int');
		$startdatelimit = $this->input->get('startdatelimit', '', 'default');
		$id = $this->input->get('id', 0, 'int');
		$anyId = $this->input->get('anyid', '', 'default');
		$customlimit = $this->input->get('customlimit', 0, 'int');
		$hashtag = $this->input->get('tag', '', 'default');
		$matchAllTags = $this->input->get('matchAllTags', false, 'default');
		$context = $this->input->get('context', '', 'default');
		$excludeStreamIds = $this->input->get('excludeStreamIds', array(), 'array');

		// In order to access the dashboard apps, user must be logged in.
		if (!$isCluster) {
			ES::requireLogin();
		}

		ES::checkToken();

		// In the event the filter type isn't sent over, we need to figure this out.
		if (!$filter && $source == 'dashboard') {
			$filter = $this->config->get('users.dashboard.start', 'me');
		}

		// Get the stream
		$stream	= ES::stream();

		if (!$filter && !$isCluster) {
			return $this->view->exception('Invalid feed type provided.');
		}

		// lets check if this is a loadmore for guest / modules
		if ($filter == 'module' && $customlimit) {
			$stream->getPublicStream($customlimit, $startlimit, null, 'dashboard');
			return $this->view->call(__FUNCTION__, $stream);
		}

		// Default options
		$options = array('startlimit' => $startlimit, 'startdatelimit' => $startdatelimit);

		if ($filter == 'scheduled') {
			$options['isScheduled'] = true;
		}

		$displayOptions = array();

		if ($customlimit) {
			$options['customlimit'] = $customlimit;
		}

		if ($source == 'dashboard') {
			$options['view'] = 'dashboard';
		}

		if ($excludeStreamIds) {
			$options['excludeStreamIds'] = $excludeStreamIds;
		}

		// Get feeds from user's friend list.
		if ($filter == 'list' && $id) {
			$model = ES::model("Lists");
			$ids = $model->getMembers($id, true);

			if ($ids) {
				$options['listId'] = $id;
				$options['context'] = SOCIAL_STREAM_CONTEXT_TYPE_ALL;
				$options['type'] = SOCIAL_TYPE_USER;
			}
		}

		// Custom stream filters
		if ($filter == 'custom' || ($filter == 'filters' && $id)) {
			$filter = ES::table('StreamFilter');
			$filter->load($id);

			if ($filter->id) {
				$hashtags = $filter->getHashTag();
				$tags = explode(',', $hashtags);

				if ($tags) {
					$options['context'] = SOCIAL_STREAM_CONTEXT_TYPE_ALL;
					$options['tag'] = $tags;

					$hashtagRule = $this->config->get('stream.filter.hashtag', '');
					if ($hashtagRule == 'and') {
						$options['matchAllTags'] = true;
					}
				}
			}
		}

		//hashtag
		if ($filter == 'hashtag' && $hashtag) {
			$hashtag = explode(',', $hashtag);

			$options['tag'] = $hashtag;
			$options['matchAllTags'] = $matchAllTags;
		}


		if ($filter == 'following') {
			$options['context'] = SOCIAL_STREAM_CONTEXT_TYPE_ALL;
			$options['type'] = 'follow';
		}

		if ($filter == 'bookmarks') {
			$options['guest'] = true;
			$options['type'] = 'bookmarks';
		}

		if ($filter == 'sticky') {
			$options['userId'] = $this->my->id;
			$options['type'] = 'sticky';
			$options['includeClusterSticky'] = true;
			$options['isStickyFilter'] = true;
		}

		// Filter by stream context types
		if ($context && $context != 'all') {
			$options['context'] = $context;
		}

		// Get feeds from everyone
		if ($filter == 'everyone') {
			$options['guest'] = true;

			// temporary comment out this because do not want to render banner user stream item #3446
			// $options['ignoreUser'] = true;
		}

		// Get feeds from the user profile.
		if ($filter == 'profile') {
			$options['profileId'] = $id;
		}

		// Get feeds from the current user and friends only.
		if ($filter == 'me') {
			$options['userId'] = ($id) ? $id : $this->my->id;
			$options['type'] = SOCIAL_TYPE_USER;

			if ($source == 'profile') {
				$options['view'] = 'profile';
				$options['nosticky'] = true;
			}
		}

		// Event category
		if ($filter == 'eventcategory') {
			$options['clusterCategory'] = $id;
			$options['clusterType'] = SOCIAL_TYPE_EVENT;
			$displayOptions['perspective'] = 'dashboard';

		}

		// Group category
		if ($filter == 'groupcategory') {
			$options['clusterCategory'] = $id;
			$options['clusterType'] = SOCIAL_TYPE_GROUP;
			$displayOptions['perspective'] = 'dashboard';

		}

		// Page category
		if ($filter == 'pagecategory') {
			$options['clusterCategory'] = $id;
			$options['clusterType'] = SOCIAL_TYPE_PAGE;
			$displayOptions['perspective'] = 'dashboard';

		}

		// Cluster types
		$clustersCategory = array('pagecategory', 'groupcategory', 'eventcategory');

		// Load more from cluster views
		if (!in_array($filter, $clustersCategory) && $isCluster && $clusterId) {

			$cluster = ES::cluster($clusterId);

			$options['clusterId'] = $cluster->id;
			$options['clusterType'] = $cluster->getType();
			$options['nosticky'] = true;

			if ($context) {
				$options['context'] = $context;
			}
		}

		if ($filter == 'moderation') {
			$options['onlyModerated'] = true;
			$options['nosticky'] = true;
		}

		// For anywhere stream items
		if ($filter == 'anywhere' && $anyId) {
			$options['anywhereId'] = $anyId;
			$options['guest'] = true;
			$options['ignoreUser'] = true;
			$displayOptions['perspective'] = 'ITEM';
		}

		// This checking is for ajax call
		if (!isset($options['view']) && in_array($source, array('groups', 'events', 'pages'))) {
			$options['view'] = $source;
			$displayOptions['perspective'] = $source;
		}

		$stream->get($options, $displayOptions);

		return $this->view->call(__FUNCTION__, $stream);
	}

	/**
	 * Hides stream items
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hide()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the stream's uid.
		$id = $this->input->get('id', 0, 'int');
		$context = $this->input->get('context', '', 'word');
		$actor = $this->input->get('actor', 0, 'id');
		$type = $this->input->get('type', '', 'word');

		$allowed = array('actor', 'context', 'item');

		if (!in_array($type, $allowed)) {
			return $this->view->exception();
		}

		// If id is invalid, throw an error.
		if (!$actor) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
		}

		if (!$context) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_APP');
		}

		// Hiding a single item
		if ($type == 'item') {
			$item = ES::table('Stream');
			$item->load($id);

			// If id is invalid, throw an error.
			if (!$id || !$item->id) {
				return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
			}

			// Check if the user is allowed to hide this item
			if (!$item->hideable()) {
				return $this->view->exception('COM_EASYSOCIAL_STREAM_NOT_ALLOWED_TO_HIDE');
			}

			// Get the model
			$model = ES::model('Stream');
			$state = $model->hide($id, $this->my->id);
		}

		if ($type == 'actor' || $type == 'context') {
			$method = $type == 'context' ? 'hideapp' : 'hideactor';
			$uid = $type == 'context' ? $context : $actor;

			$model = ES::model('Stream');
			$state = $model->$method($uid, $this->my->id);
		}

		if (!$state) {
			return $this->view->exception($model->getError());
		}

		return $this->view->call(__FUNCTION__, $type, $actor, $context);
	}

	/**
	 * Unhides a stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unhide()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the stream's uid.
		$id = $this->input->get('id', 0, 'int');
		$context = $this->input->get('context', '', 'word');
		$actor = $this->input->get('actor', 0, 'id');
		$type = $this->input->get('type', '', 'word');

		$allowed = array('actor', 'context', 'item');

		if (!in_array($type, $allowed)) {
			return $this->view->exception();
		}

		// If id is invalid, throw an error.
		if (!$actor) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
		}

		if (!$context) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_APP');
		}

		// Hiding a single item
		if ($type == 'item') {

			$item = ES::table('Stream');
			$item->load($id);

			// If id is invalid, throw an error.
			if (!$id || !$item->id) {
				return $this->view->setMessage('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
			}

			// Check if the user is allowed to hide this item
			if (!$item->hideable()) {
				return $this->view->exception('COM_EASYSOCIAL_STREAM_NOT_ALLOWED_TO_HIDE');
			}

			// Get the model
			$model = ES::model('Stream');
			$state = $model->unhide($id, $this->my->id);
		}


		if ($type == 'actor' || $type == 'context') {
			$method = $type == 'context' ? 'unhideapp' : 'unhideactor';
			$uid = $type == 'context' ? $context : $actor;

			$model = ES::model('Stream');
			$state = $model->$method($uid, $this->my->id);
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Delete a stream item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the stream's uid.
		$id = $this->input->get('id', 0, 'int');

		// Load the stream item.
		$item = ES::table('Stream');
		$item->load($id);

		// If id is invalid, throw an error.
		if (!$id || !$item->id) {
			return $this->view->exception('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID');
		}

		// Check if the user can delete this stream
		if (!$item->canDelete()) {
			return $this->view->exception($item->getError());
		}

		$state = $item->delete();

		// If there's an error, log this down.
		if (!$state) {
			return $this->view->exception($item->getError());
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to translate a wall of text
	 *
	 * @since	2.0.8
	 * @access	public
	 */
	public function translate()
	{
		ES::checkToken();

		// Get the contents to translate
		$contents = $this->input->get('contents', '', 'raw');

		// The target language should always be the language the user is using
		$lang = JFactory::getLanguage();
		$defaultLanguage = $lang->getTag();
		$defaultLanguage = explode('-', $defaultLanguage);
		$defaultLanguage = $defaultLanguage[0];

		$params = new JRegistry($this->my->params);
		$targetLanguage = $params->get('language', $defaultLanguage);

		$translations = ES::translations();

		$output = $translations->translate($contents, $targetLanguage);

		return $this->view->call(__FUNCTION__, $output);
	}
}
