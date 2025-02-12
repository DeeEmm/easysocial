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

class EasySocialControllerStory extends EasySocialController
{
	/**
	 * Allows caller to update a stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function update($stream)
	{
		ES::requireLogin();

		if ($stream->cluster_id) {
			$cluster = ES::cluster($stream->cluster_type, $stream->cluster_id);

			if (!$this->my->isSiteAdmin() && $stream->actor_id != $this->my->id && !$cluster->isAdmin()) {
				return $this->view->exception('COM_EASYSOCIAL_STREAM_NO_PERMISSIONS_TO_EDIT');
			}

			// We only allow cluster admin and site admin to edit the moderated stream item
			if ($stream->state == SOCIAL_STREAM_STATE_MODERATE && !($cluster->isAdmin() || $this->my->isSiteAdmin())) {
				return $this->view->exception('COM_EASYSOCIAL_STREAM_NO_PERMISSIONS_TO_EDIT');
			}

		} else {
			if (!$this->my->isSiteAdmin() && $stream->actor_id != $this->my->id) {
				return $this->view->exception('COM_EASYSOCIAL_STREAM_NO_PERMISSIONS_TO_EDIT');
			}
		}

		// Get posted data.
		$post = $this->input->getArray('post');
		$content = $this->input->get('content', '', 'raw');

		// We need to remove if there is any /n before the first word
		$content = preg_replace('~^[\r\n]+~', '', $content);

		// Determine the post types.
		$type = isset($post['attachment']) && !empty($post['attachment']) ? $post['attachment'] : SOCIAL_TYPE_STORY;

		// Store the location for this story
		$shortAddress = $this->input->get('locations_short_address', '', 'default');
		$address = $this->input->get('locations_formatted_address', '', 'default');
		$lat = $this->input->get('locations_lat', '', 'default');
		$lng = $this->input->get('locations_lng', '', 'default');
		$locationData = $this->input->get('locations_data', '', 'default');

		$location = ES::table('Location');

		// Only store location when there is location data
		if (!empty($address) && !empty($lat) && !empty($lng)) {

			// if there is location_id, load the location
			if ($stream->location_id != 0) {
				$location->load($stream->location_id);

				// Check if there is any changes on location
				if ($shortAddress != $location->short_address) {

					$newLocation = array();
					$newLocation['short_address'] = $shortAddress;
					$newLocation['address'] = $address;
					$newLocation['longitude'] = $lng;
					$newLocation['latitude'] = $lat;
					$newLocation['params'] = $locationData;

					// Update the location
					$state = $location->update($newLocation);
				}
			} else {

				$location->short_address = $shortAddress;
				$location->address = $address;
				$location->longitude = $lng;
				$location->latitude = $lat;
				$location->uid = $stream->id;
				$location->type = 'story';
				$location->user_id = $this->my->id;
				$location->params = $locationData;

				// Try to save the location data.
				$state 	= $location->store();

				if ($state) {
					$stream->location_id = $location->id;
				}
			}
		}

		$giphyLib = ES::giphy();

		// Check if user is trying to update a stream item that contains giphy
		$giphy = $this->input->get('giphy', false, 'string');

		if ($giphy && !$giphyLib->isValidUrl($giphy)) {
			$giphy = false;
		}

		if ($stream->context_type == 'story' && $giphyLib->isEnabledForStory()) {
			$streamItemTable = ES::table('StreamItem');
			$streamItemTable->load(array('uid' => $stream->id));

			$streamItemTable->updateGiphy($giphy);
		}

		// Get which users are tagged in this post.
		$friendIds = $this->input->get('friends_tags', '', 'default');
		$friends = [];

		if (!empty($friendIds)) {

			// Get the friends model
			$model = ES::model('Friends');

			// Check if the user is really a friend of him / her.
			foreach ($friendIds as $id) {
				if (!$model->isFriends($this->my->id, $id) && !$this->my->isSiteAdmin()) {
					continue;
				}

				$friends[]	= $id;
			}
		}

		// Process the mentions here
		$mentions = $this->input->get('mentions', [], 'array');

		// Format the json string to array
		if (!empty($mentions)) {
			foreach ($mentions as &$mention) {
				$mention = ES::json()->decode($mention);

				// readjust the mention start offset if there are emoji in the content.
				$testSubject = ESJString::substr($content, 0, $mention->start);
				$pattern = '/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]/u';

				$emojis = array();
				preg_match_all($pattern, $testSubject, $emojis);

				if ($emojis && isset($emojis[0])) {
					$mention->start = $mention->start - count($emojis[0]);
				}
			}
		}

		// Get the stream model and remove mentions
		$model = ES::model('Stream');
		$model->removeMentions($stream->id);

		// Now we need to add new mentions
		if ($mentions) {
			$model->addMentions($stream->id, $mentions, array('scheduled' => $stream->isScheduled()));
		}

		// Set friends id to this stream
		$model->setWith($stream->id, $friends, array('scheduled' => $stream->isScheduled()));

		// Process moods here
		$mood = ES::table('Mood');

		if ($stream->mood_id != 0) {
			$mood->load($stream->mood_id);
		}

		$hasMood = $mood->bindPost($post);

		// If this exists, we need to store them
		if ($hasMood) {
			$mood->user_id = $this->my->id;
			$state = $mood->store();

			// Save the mood id into the stream
			if ($state) {
				$stream->mood_id = $mood->id;
			}
		}

		$backgroundId = $this->input->get('backgroundId', null, 'int');

		if (!is_null($backgroundId)) {
			$stream->background_id = $backgroundId;
		}

		$stream->content = $content;
		$stream->edited = ES::date()->toSql();
		$stream->store();

		$scheduledDate = $this->input->get('scheduled', false, 'string');
		$scheduledDate = ($scheduledDate === 'false') ? false : $scheduledDate;

		// Updating scheduled stream.
		$scheduled = ES::Scheduler();
		$scheduled->update($stream->id, $scheduledDate);

		// now we need to trigger the onAfterStoryEditSave
		$dispatcher = ES::dispatcher();
		$data = array();

		if ($type == 'photos' && isset($post['photos'])) {
			$data['photos'] = $post['photos'];
		}

		$cluster = isset($post['cluster']) ? $post['cluster'] : '';
		$clusterType = isset($post['clusterType']) ? $post['clusterType'] : '';

		// Determines which trigger group to call
		$group = $cluster ? $clusterType : SOCIAL_TYPE_USER;
		ES::apps()->load($group);

		// Construct our new arguments
		$args = array(&$stream);

		// @trigger onAfterStorySave
		$dispatcher->trigger($group, 'onAfterStoryEditSave', $args);

		// Because we know that story posts only has 1 item, we may safely assume that the first index.
		$items = $stream->getItems();
		$item = $items[0];

		return $this->view->call(__FUNCTION__, $item, $stream->cluster_id, $stream->cluster_type, $stream->state);
	}

	/**
	 * Stores a new story item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function create()
	{
		ES::requireLogin();
		ES::checkToken();

		// if this is from edit form, get the stream id
		$id = $this->input->get('isEdit', false, 'int');

		// Will store this into stream item params column since photo and giphy cannot be together
		$giphy = $this->input->get('giphy', false, 'string');

		$giphyLib = ES::giphy();

		if (!$giphyLib->isValidUrl($giphy)) {
			$giphy = false;
		}

		// If id exists, means caller trying to update the stream
		if ($id) {
			$stream = ES::table('Stream');
			$stream->load($id);

			return $this->update($stream);
		}

		// Check for honeypot traps
		if ($this->config->get('honeypot.stream')) {
			$honeypot = ES::honeypot();
			$trapped = $honeypot->isTrapped('stream');

			if ($trapped) {
				return $this->view->exception('Posted');
			}
		}

		// Load our story library
		$story = ES::story(SOCIAL_TYPE_USER);

		// Determine whether this is a scheduled post.
		// This will give you scheduled post date.
		$scheduled = $this->input->get('scheduled', false, 'string');
		$scheduled = ($scheduled === 'false') ? false : $scheduled;

		// Get posted data.
		$post = $this->input->getArray('post');

		// Check if the user being viewed the same user or other user.
		$id = $post['target'];
		$targetId = $this->my->id != $id ? $id : '';

		// Determine the post types.
		$type = isset($post['attachment']) && !empty($post['attachment']) ? $post['attachment'] : SOCIAL_TYPE_STORY;

		// Check if the content is empty only for story based items.
		// Story can be posted with giphy only without content as well
		if ((!isset($post['content']) || empty($post['content'])) && $type == SOCIAL_TYPE_STORY && !$giphy) {
			return $this->view->exception('COM_EASYSOCIAL_STORY_PLEASE_POST_MESSAGE');
		}

		// Check if the content is empty and there's no photos.
		if ((!isset($post['photos']) || empty($post['photos'])) && $type == 'photos') {
			return $this->view->exception('COM_EASYSOCIAL_STORY_PLEASE_ADD_PHOTO');
		}

		// We need to allow raw because we want to allow <,> in the text but it should be escaped during display
		$content = $this->input->get('content', '', 'raw');

		// We need to remove if there is any /n before the first word
		$content = preg_replace('~^[\r\n]+~', '', $content);

		// Check whether the user can really post something on the target
		if ($targetId) {
			$allowed = $this->my->getPrivacy()->validate('profiles.post.status', $targetId, SOCIAL_TYPE_USER);

			if (!$allowed) {
				return $this->view->exception('COM_EASYSOCIAL_STORY_NOT_ALLOW_TO_POST');
			}
		}

		// Store the location for this story
		$shortAddress = $this->input->get('locations_short_address', '', 'default');
		$address = $this->input->get('locations_formatted_address', '', 'default');
		$lat = $this->input->get('locations_lat', '', 'default');
		$lng = $this->input->get('locations_lng', '', 'default');
		$locationData = $this->input->get('locations_data', '', 'default');
		$location = null;

		// Only store location when there is location data
		if (!empty($address) && !empty($lat) && !empty($lng)) {

			$location = ES::table( 'Location' );
			$location->short_address = $shortAddress;
			$location->address = $address;
			$location->longitude = $lng;
			$location->latitude = $lat;

			// Hitting "Data truncated for column 'uid' at row 1" in Joomla 4 due to the $story->id is uniqid() currently here
			// Assign 0 to it for now #4273
			$location->uid = 0;

			$location->type = $type;
			$location->user_id = $this->my->id;
			$location->params = $locationData;

			// Try to save the location data.
			$state 	= $location->store();
		}

		// Get which users are tagged in this post.
		$friendIds = $this->input->get('friends_tags', '', 'default');
		$friends = array();

		if (!empty($friendIds)) {

			// Get the friends model
			$model = ES::model('Friends');

			// Check if the user is really a friend of him / her.
			foreach ($friendIds as $id) {

				// If friend feature enabled then only check for these ids whether friend with that person or not
				if ($this->config->get('friends.enabled') && !$this->my->isSiteAdmin()) {

					if (!$model->isFriends($this->my->id, $id)) {
						continue;
					}
				}

				$friends[]	= $id;
			}
		}

		$contextIds = 0;

		// For photos that are posted on the story form
		if ($type == 'photos' && isset($post['photos'])) {
			$contextIds = $post['photos'];
		}

		// Check if there are mentions provided from the post.
		$mentions = isset($post['mentions']) ? $post['mentions'] : array();

		// Format the json string to array
		if (isset($post['mentions'])) {
			$mentions = $post['mentions'];

			foreach ($mentions as &$mention) {
				$mention = json_decode($mention);

				// readjust the mention start offset if there are emoji in the content.
				$testSubject = ESJString::substr($content, 0, $mention->start);
				$pattern = '/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]/u';

				$emojis = array();
				preg_match_all($pattern, $testSubject, $emojis);

				if ($emojis && isset($emojis[0])) {
					$mention->start = $mention->start - count($emojis[0]);
				}
			}
		}

		// Process moods here
		$mood = ES::table('Mood');
		$hasMood = $mood->bindPost($post);

		// If this exists, we need to store them
		if ($hasMood) {
			if (!$mood->namespace_uid) {
				$mood->namespace_uid = 0;
			}

			$mood->user_id = $this->my->id;
			$mood->store();
		}

		// Set the privacy for the album
		$privacy = $this->input->get('privacy', '', 'default');
		$customPrivacy = $this->input->get('privacyCustom', '', 'string');
		$fieldPrivacy = $this->input->get('privacyField', '', 'string');

		$privacyRule = 'story.view';

		if ($type == 'photos') {
			$privacyRule = 'photos.view';
		}

		if ($type == 'polls') {
			$privacyRule = 'polls.view';
		}

		if ($type == 'videos') {
			$privacyRule = 'videos.view';
		}

		if ($type == 'audios') {
			$privacyRule = 'audios.view';
		}

		// Determines if the current posting is for a cluster
		$cluster = isset($post['cluster']) ? $post['cluster'] : '';
		$clusterType = isset($post['clusterType']) ? $post['clusterType'] : '';
		$isCluster = $cluster ? true : false;
		$postPermission = true;
		$postActor = isset($post['postActor']) ? $post['postActor'] : null;
		$anywhereId = isset($post['anywhereId']) ? $post['anywhereId'] : null;
		$pageTitle = isset($post['pageTitle']) ? $post['pageTitle'] : null;

		// Check for posting permission
		if ($isCluster) {
			$postPermission = $this->checkClusterPermissions($cluster, $clusterType);
		} else {
			$postPermission = $this->checkPostPermissions();
		}

		// Ensure only permitted user can post the story
		if (!$postPermission) {
			return $this->view->call(__FUNCTION__);
		}

		// Options that should be sent to the stream lib
		$args = array(
						'content' => $content,
						'contextIds' => $contextIds,
						'contextType' => $type,
						'actorId' => $this->my->id,
						'targetId' => $targetId,
						'location' => $location,
						'with' => $friends,
						'mentions' => $mentions,
						'cluster' => $cluster,
						'clusterType' => $clusterType,
						'mood' => $mood,
						'privacyRule' => $privacyRule,
						'privacyValue' => $privacy,
						'privacyCustom' => $customPrivacy,
						'privacyField' => $fieldPrivacy,
						'postActor' => $postActor,
						'anywhereId' => $anywhereId,
						'pageTitle' => $pageTitle,
						'backgroundId' => $this->input->get('backgroundId', 0, 'int'),
						'scheduled' => $scheduled
					);

		// The form may contain params
		if (isset($post['params'])) {
			$args['params'] = $post['params'];
		}

		if ($giphy && $giphyLib->isEnabledForStory()) {
			$args['giphy'] = array('giphy' => $giphy);
		}

		// Create the stream item
		$stream = $story->create($args);

		if ($hasMood) {
			// @TODO: Need to cater for scheduled posting as well.
			$mood->namespace = 'story.user.create';
			$mood->namespace_uid = $stream->id;
			$mood->store();
		}

		// Update with the stream's id. after the stream is created.
		if (!empty($address) && !empty($lat) && !empty($lng)) {
			$location->uid = $stream->id;

			// Try to save the location data.
			$state = $location->store();
		}

		// Add badge for the author when a story is created.
		ES::badges()->log('com_easysocial', 'story.create', $this->my->id, JText::_('COM_EASYSOCIAL_STORY_BADGE_CREATED_STORY'));

		// allow types
		$allowPointTypes = array(SOCIAL_POINTS_STORY_TYPES_STORY, SOCIAL_POINTS_STORY_TYPES_LINKS);

		// Add points for the author when a story is created.
		if (in_array($type, $allowPointTypes)) {
			ES::points()->assign('story.create', 'com_easysocial', $this->my->id);
		}

		// Privacy is only applicable to normal postings
		if (!$isCluster) {

			$privacyLib = ES::privacy();
			$knownTypes = array('photos', 'polls', 'videos', 'files', 'audios');
			$privacyModel = ES::model('Privacy');

			if ($type == 'photos') {

				$photoIds = ES::makeArray($contextIds);

				foreach ($photoIds as $photoId) {
					$privacyLib->add($privacyRule, $photoId, $type, $privacy, null, $customPrivacy, $fieldPrivacy);
				}

				// We also need to update the objects access column
				$access = $privacyLib->toValue($privacy);
				$privacyModel->updateMediaAccess('photos', $photoIds, $access, $customPrivacy, $fieldPrivacy);
			}

			// Process privacy for polls and videos
			if ($type == 'polls' || $type == 'videos' || $type == 'audios') {
				$privacyLib->add($privacyRule, $stream->context_id, $type, $privacy, null, $customPrivacy, $fieldPrivacy);
			}

			// Files privacy depends on the stream aggregated id and type 'activity'. #649
			if ($type == 'files') {
				$privacyLib->add($privacyRule, $stream->id, 'activity', $privacy, null, $customPrivacy, $fieldPrivacy);
			}

			// Unknown types
			if (!in_array($type, $knownTypes)) {
				$privacyLib->add($privacyRule, $stream->uid, $type, $privacy, null, $customPrivacy, $fieldPrivacy);
			}
		}

		return $this->view->call(__FUNCTION__, $stream, $cluster, $clusterType);
	}

	/**
	 * Stores a new story item from module
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function createFromModule()
	{
		ES::checkToken();

		// When the user is a guest, we should display a friendlier message
		if ($this->my->guest) {
			return $this->view->call(__FUNCTION__);
		}

		// Load our story library
		$story = ES::story(SOCIAL_TYPE_USER);

		// Get posted data.
		$post = $this->input->getArray('post');

		$userId = $post['target'];

		// Determine the post types.
		$type = SOCIAL_TYPE_STORY;

		// Check if the content is empty only for story based items.
		if ((!isset($post['content']) || empty($post['content'])) && $type == SOCIAL_TYPE_STORY) {
			return $this->view->exception('COM_EASYSOCIAL_STORY_PLEASE_POST_MESSAGE');
		}

		// We need to allow raw because we want to allow <,> in the text but it should be escaped during display
		$content = $this->input->get('content', '', 'raw');
		$contextIds = 0;

		// Set the privacy for the album
		$privacy = $this->input->get('privacy', '', 'default');
		$customPrivacy = $this->input->get('privacyCustom', '', 'string');

		$privacyRule = 'story.view';
		$postPermission = true;

		// Options that should be sent to the stream lib
		$args = array(
						'content' => $content,
						'contextIds' => $contextIds,
						'contextType' => $type,
						'actorId' => $this->my->id,
						'privacyRule' => $privacyRule,
						'privacyValue' => $privacy,
						'privacyCustom' => $customPrivacy
					);

		// Create the stream item
		$stream = $story->create($args);

		// Add badge for the author when a report is created.
		ES::badges()->log('com_easysocial', 'story.create', $this->my->id, JText::_('COM_EASYSOCIAL_STORY_BADGE_CREATED_STORY'));

		// Add points for the author when a report is created.
		ES::points()->assign('story.create', 'com_easysocial', $this->my->id);

		$privacyLib = ES::privacy();
		$privacyLib->add($privacyRule, $stream->uid, $type, $privacy, null, $customPrivacy);

		return $this->view->call(__FUNCTION__, $stream);
	}

	/**
	 * Checks for posting permissions for clusters
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function checkClusterPermissions($id, $type)
	{
		// For group specific postings, we need to check for permissions
		if (!$this->my->canPostClusterStory($type, $id)) {
			$this->view->setMessage('COM_EASYSOCIAL_STORY_NOT_ALLOW_TO_POST_IN_' . strtoupper($type), SOCIAL_MSG_ERROR);
			return false;
		}

		return true;
	}

	/**
	 * Check if user are allowed to post the story
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function checkPostPermissions()
	{
		if (!$this->my->canPostStory()) {
			$this->view->setMessage('COM_EASYSOCIAL_STORY_NOT_ALLOW_TO_POST_HERE', SOCIAL_MSG_ERROR);
			return false;
		}

		return true;
	}

	/**
	 * Allows user to set their story preferences
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function addFavourite()
	{
		if (!$this->config->get('stream.story.favourite')) {
			die();
		}

		ES::requireLogin();

		$element = $this->input->get('element', '', 'word');

		$this->my->addFavouriteStory($element);
	}

	/**
	 * Allows user to set their story preferences
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function removeFavourite()
	{
		if (!$this->config->get('stream.story.favourite')) {
			die();
		}

		ES::requireLogin();

		$element = $this->input->get('element', '', 'word');

		$this->my->removeFavouriteStory($element);
	}
}
