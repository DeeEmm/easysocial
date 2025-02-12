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

// Include necessary libraries here.
require_once(__DIR__ . '/dependencies.php');
require_once(__DIR__ . '/item.php');
require_once(__DIR__ . '/template.php');
require_once(SOCIAL_LIB . '/privacy/option.php');

ES::import('admin:/includes/group/group');

class SocialStream extends EasySocial
{
	/**
	 * the unique identifier for each stream instance.
	 * @var	Array
	 */
	private $identifier = null;

	/**
	 * Contains a list of stream data.
	 * @var	Array
	 */
	public $data = null;

	/*
	 * this nextStartDate used as pagination.
	 */
	private $nextdate = null;

	/*
	 * this nextEndDate used as pagination.
	 */
	private $enddate = null;

	private $uids = null;

	/**
	 * Stores the current context
	 *
	 * @var string
	 */
	private $currentContext	= null;

	/*
	 * this nextlimit used as actvities log pagination.
	 */
	private $nextlimit = null;

	/**
	 * Determines if the current request is for a single item output.
	 * @var boolean
	 */
	private $singleItem = false;

	/**
	 * Determines the current filter type.
	 * @var string
	 */
	public $filter = null;

	/**
	 * Determines if the current retrieval is for guest viewing or not.
	 * @var string
	 */
	public $guest = null;


	/**
	 * Determines if the current retrieval is for cluster or not. (groups or event).
	 * @var string
	 */
	public $isCluster = null;
	public $clusterType = null;
	public $clusterId = null;

	/**
	 * used in module / public stream
	 * @var string
	 */
	public $customLimit = 0;


	/**
	 * options
	 * @var string
	 */
	public $options = null;


	/**
	 * public stream pagination
	 *
	 */
	public $limit = 0;
	public $startlimit = 0;

	/*
	 * this nextDatelimit used in conjuction with nextlimit in pagination.
	 */
	private $startdatelimit = null;

	public $pagination = null;
	public $perspective = null;

	// Scheduled.
	public $scheduled = null;
	public $story = null;

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct()
	{
		parent::__construct();

		$this->filter = 'all';
		$this->guest = false;
		$this->options = array();

		$this->identifier = uniqid('stream_');
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}

	/**
	 * Delete stream items given the app type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($contextId, $contextType, $actorId = '', $verb = '')
	{
		// Load dispatcher.
		$dispatcher = ES::dispatcher();
		$args = array($contextId, $contextType, $verb);

		// Trigger onBeforeStreamDelete
		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onBeforeStreamDelete', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP, 'onBeforeStreamDelete', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onBeforeStreamDelete', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_PAGE, 'onBeforeStreamDelete', $args);

		$model = ES::model('Stream');
		$model->delete($contextId, $contextType, $actorId, $verb);

		// Trigger onAfterStreamDelete
		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onAfterStreamDelete', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP, 'onAfterStreamDelete', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onAfterStreamDelete', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_PAGE, 'onAfterStreamDelete', $args);
	}

	/**
	 * Object initialisation for the class to fetch the appropriate user
	 * object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function factory()
	{
		return new self();
	}

	/**
	 * Creates the stream template
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTemplate()
	{
		$template = new SocialStreamTemplate();

		return $template;
	}

	/**
	 * check if activity already exists or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function exists($uid, $context, $verb, $actorId, $options = array())
	{
		$model = ES::model('Stream');
		$exits = $model->exists($uid, $context, $verb, $actorId, $options);
		return $exits;
	}


	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function generateMutualFriendSQL($source, $target)
	{
		$query = '';

		$query = "select count(1) from (";
		$query .= "	select af1.`actor_id` as `fid` from `#__social_friends` as af1 where af1.`target_id` = $source and af1.`state` = 1";
		$query .= "		union ";
		$query .= "	select af2.`target_id` as `fid`  from `#__social_friends` as af2 where af2.`actor_id` = $source and af2.`state` = 1";
		$query .= ") as x";
		$query .= " where exists (";
		$query .= "	select bf1.`actor_id` from `#__social_friends` as bf1 where bf1.`target_id` = $target and bf1.`actor_id` = x.`fid` and bf1.`state` = 1";
		$query .= " 	union ";
		$query .= "	select bf2.`target_id` from #__social_friends as bf2 where bf2.`actor_id` = $target and bf2.`target_id` = x.`fid`  and bf2.`state` = 1";
		$query .= ")";

		return $query;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function generateIsFriendSQL($source, $target)
	{
		$query = "select count(1) from `#__social_friends` where (`actor_id` = $source and `target_id` = $target) OR (`target_id` = $source and `actor_id` = $target) and `state` = 1";

		return $query;
	}


	/**
	 * Creates a new stream item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function add(SocialStreamTemplate $data)
	{
		// Let's try to aggregate the stream item.
		// Get the stream model
		$model = ES::model('Stream');

		// Get the config obj.
		$config = ES::config();

		// The duration between activities.
		$duration = $config->get('stream.aggregation.duration');

		// check if the actor is in ESAD profile or not.
		if ($data->actor_id) {
			$actor = ES::user($data->actor_id);

			if (!$actor->hasCommunityAccess()) {
				return false;
			}
		}

		// Determine which context types should be aggregated.
		$aggregateContext = $config->get('stream.aggregation.contexts');

		// reset this flag to false whenever there are items in child property.
		if (count($data->childs) > 0) {
			$data->isAggregate = false;
		}

		// Now lets bind the isPublic privacy
		$data->bindStreamAccess();

		// @trigger: onPrepareComments
		$dispatcher = ES::dispatcher();
		$args = array(&$data);

		// Determines what group of apps should we trigger
		$eventGroup = $data->cluster_type ? $data->cluster_type : SOCIAL_APPS_GROUP_USER;
		$dispatcher->trigger($eventGroup, 'onBeforeStreamSave', $args);

		// if actor_id is empty, we should stop here.
		if (!$data->actor_id) {
			return false;
		}

		// Get the unique id if necessary.
		$uid = $model->updateStream($data);

		// Fixed for 'Fields don't have default value' error in Joomla 4
		if (!$uid) {
			$uid = 0;
		}

		if (count($data->childs) > 0) {
			foreach ($data->childs as $contextId) {

				// Load the stream item table
				$item = ES::table('StreamItem');
				$item->bind($data);

				//override contextId
				$item->context_id = $contextId;

				// Set the uid for the item.
				$item->uid 	= $uid;

				// Let's try to store the stream item now.
				$state 	= $item->store();

				if (!$state) {
					return false;
				}
			}
		} else {
			// Load the stream item table
			$item = ES::table('StreamItem');
			$item->bind($data);

			// Set the uid for the item.
			$item->uid = $uid;

			// set context item's params
			$item->params = $data->item_params;

			// Let's try to store the stream item now.
			$state = $item->store();

			if (!$state) {
				return false;
			}

		}

		// Determine if there's "with" in this stream and add it in.
		if ($data->with) {
			$model->setWith($uid, $data->with, array('scheduled' => $data->isScheduled()));
		}

		// Determine if there's mentions in this stream and we need to create it.
		if ($data->mentions) {
			$model->addMentions($uid, $data->mentions, array('scheduled' => $data->isScheduled()));
		}

		$dispatcher->trigger($eventGroup, 'onAfterStreamSave', $args);

		return $item;
	}

	/**
	 * Method to update created date of the stream
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function updateCreated($streamId, $date = null, $action = '')
	{
		$model = ES::model('Stream');
		$state = $model->updateCreated($streamId, $date, $action);

		return $state;
	}

	/**
	 * Update stream.modified date.
	 * the context can be 'stream' and when the context is stream, the uid is the stream.id
	 * the context can be 'activity' and when the context is activity, the uid is the stream_item.id
	 * we need to work accordingly based on the context passed in.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function updateModified($streamId, $user_id = '', $user_action = '')
	{
		$model = ES::model('Stream');
		$state = $model->updateModified($streamId, $user_id, $user_action);

		return $state;
	}

	/**
	 * Update stream.last_action and last_userid.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function revertLastAction($streamId, $user_id = '', $user_action = '') {
		$model = ES::model('Stream');
		$state = $model->revertLastAction($streamId, $user_id, $user_action);

		return $state;
	}


	/**
	 * stream's with tagging.
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function getStreamTagWith($streamId)
	{
		$model = ES::model('Stream');
		return $model->getTagging($streamId, 'with');
	}

	/**
	 * stream's mentions tagging.
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function getTags($streamId)
	{
		$model = ES::model('Stream');
		$mentions = $model->getTagging($streamId, 'tags');

		return $mentions;
	}

	/**
	 * Returns a list of hash tags from a particular stream.
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function getHashtags($streamId)
	{
		$model = ES::model('Stream');
		$hashtags = $model->getTagging($streamId, 'hashtags');

		return $hashtags;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function formatItem(SocialStreamItem &$stream)
	{
		$content = $stream->content;
		$tags = $stream->tags;

		if (!$tags) {
			return;
		}

		// @TODO: We need to merge the mentions and hashtags since we are based on the offset.
		foreach ($tags as $tag) {

			if ($tag->type == 'user') {
				$replace 	= '<a href="' . $tag->user->getPermalink() . '" data-popbox="module://easysocial/profile/popbox" data-popbox-position="top-left" data-user-id="' . $tag->user->id . '" class="mentions-user">@' . $tag->user->getName() . '</a>';
			}

			if ($tag->type == 'hashtag') {
				$alias = JFilterOutput::stringURLSafe($tag->title);

				$replace = '<a href="' . FRoute::dashboard(array('layout' => 'hashtag', 'tag' => $alias)) . '" class="mentions-hashtag">#' . $tag->title . '</a>';
			}

			$content = ESJString::substr_replace($content, $replace, $tag->offset, $tag->length);
		}

		$stream->content = $content;
	}

	/**
	 * Formats a stream item with the necessary data.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function format($items, $context = 'all', $viewer = null, $loadCoreAction = true, $defaultEvent = 'onPrepareStream', $options = array(), $enforceLimit = 0)
	{
		$config = ES::config();

		// Get the current user
		$my = ES::user($viewer);

		// Basic display options
		$commentLink = isset($options['commentLink']) && $options['commentLink'] || !isset($options['commentLink']) ? true : false;
		$commentForm = (isset($options['commentForm']) && $options['commentForm']) || !isset($options['commentForm']) ? true : false;
		$forceDisableActions = isset($options['disableActions']) && $options['disableActions'] ? true : false;

		$isActivity = isset($options['isActivity']) ? $options['isActivity'] : false;

		// Default the event to onPrepareStream
		if (!$defaultEvent) {
			$defaultEvent = 'onPrepareStream';
		}

		// Determines if this is a stream
		$isStream = false;

		if (($defaultEvent == 'onPrepareStream' || $defaultEvent == 'onPrepareDigest' || $defaultEvent == 'onPrepareRestStream') && !$isActivity) {
			$isStream = true;
		}


		// If there's no items, skip formatting this because it's pointless to run after this.
		if (!$items) {
			return $items;
		}

		// Prepare default data
		$data = array();
		$activeUser	= ES::user();

		// Get stream model
		$model = ES::model('Stream');

		// Current user being view.
		$targetUser = $this->input->get('id', '', 'int');

		if (empty($targetUser)) {
			$targetUser = $this->my->id;
		}

		if (is_array($targetUser)) {
			$targetUser = (int) $targetUser[0];
		}

		if ($targetUser && strpos($targetUser, ':')) {
			$tmp = explode(':', $targetUser);
			$targetUser = $tmp[0];
		}

		// Link options
		// We only have to do this once instead of putting inside the loop.
		$linkOptions = array('target'=>'_blank');

		// Always apply nofollow
		$linkOptions['rel'] = 'nofollow';

		$itemProcessed = 0;

		// Format items with appropriate objects.
		foreach ($items as &$row) {

			// Get the uid
			$uid = $row->id;

			// Determines if this is a cluster
			$isCluster = ($row->cluster_id) ? true : false;

			// Get the stream item.

			// There is a possiblity that the stream is generated via a module, we need to allow them
			// to change the perspective here
			$itemOptions = array();

			if (isset($options['perspective'])) {
				$itemOptions['perspective'] = $options['perspective'];
			}

			$streamItem = new SocialStreamItem($itemOptions);
			$streamItem->bind($row);

			// Obtain related activities for aggregation.
			$relatedActivities = null;

			if ($isStream) {
				$relatedActivities = $model->getRelatedActivities($uid, $row->context_type, $viewer);
			} else {
				$relatedActivities = $model->getActivityItem($uid);
			}

			$aggregatedData = $this->buildAggregatedData($relatedActivities);

			// Set the aggregated items here so 3rd party can manipulate the data.
			$streamItem->aggregatedItems = $relatedActivities;
			$streamItem->aggregate($aggregatedData);

			// Getting the the with and mention tagging for the stream, only if the item is a stream.
			if ($isStream) {
				$streamItem->with = $this->getStreamTagWith($uid);
				$streamItem->tags = $this->getTags($uid);
			}

			// stream privacy
			$streamItem->privacy = null;

			// Target user. this target user is different from the targets. this is the user who are being viewed currently.
			$streamItem->targetUser = $targetUser;

			// Format the contents before triggering the apps so that the apps can manipulate the content
			$view = $this->input->get('view', '', 'cmd');
			$streamItem->formatContent($view);

			// Stream actions
			$streamItem->comments = (($defaultEvent == 'onPrepareStream' || $defaultEvent == 'onPrepareRestStream') && !$isActivity) ? true : false;
			$streamItem->likes = (($defaultEvent == 'onPrepareStream' || $defaultEvent == 'onPrepareRestStream') && !$isActivity) ? true : false;
			$streamItem->repost = (($defaultEvent == 'onPrepareStream' || $defaultEvent == 'onPrepareRestStream') && !$isActivity) ? true : false;

			// Determines the current perspective of the stream
			$streamItem->view = $view;

			if (isset($options['perspective'])) {
				$streamItem->view = $options['perspective'];
			}

			// determine if stream is editable or not.
			// default to false. app will decide if the stream is editable or not.
			$streamItem->editable = false;

			// default to false. app that do not support stream edit can supply the item's edit link into this variable.
			$streamItem->edit_link = false;

			// @trigger onPrepareStream / onPrepareActivity
			$includePrivacy = $streamItem->isCluster() ? false : true;

			if (isset($options['overridePrivacy'])) {
				$includePrivacy = $options['overridePrivacy'];
			}

			if ($defaultEvent == 'onPrepareRestStream') {
				$result = $this->onPrepareRestStream($streamItem, $includePrivacy, $my);
			} else {
				$result = $this->$defaultEvent($streamItem, $includePrivacy);
			}

			// if disable actions by force, lets disable them.
			if ($forceDisableActions) {
				$streamItem->comments = false;
				$streamItem->likes = false;
				$streamItem->repost = false;
			}

			$itemProcessed++;

			// Allow app to stop loading / generating the stream and
			if ($result === false) {
				continue;
			}

			// if there is still no title, we need to skip this stream altogether as there is no point displaying it
			// But title is not compulsory for REST API (mobile app)
			if ($defaultEvent != 'onPrepareRestStream' && !$streamItem->title) {
				continue;
			}

			// For REST API, we check on $item->show property
			if ($defaultEvent == 'onPrepareRestStream') {
				if (!$streamItem->show && !isset($streamItem->webview)) {
					continue;
				}
			}

			// This mean the plugin did not set any privacy. lets use the stream / activity.
			if (is_null($streamItem->privacy) && $includePrivacy) {
				// Check if the user can really view this item
				if (!$streamItem->canView()) {
					continue;
				}

				// now lets get the privacy form
				$streamItem->privacy = $streamItem->getPrivacyHtml();
			}

			// Get the item group
			$itemGroup = $streamItem->getGroup();

			// Comments
			if ($streamItem->comments) {
				if (!$streamItem->comments instanceof SocialCommentBlock) {
					$url = ESR::stream(array('layout' => 'item', 'id' => $streamItem->uid, 'sef' => false, 'adminSef' => false));
					$commmentOptions = array('url' => $url);

					if ($streamItem->cluster_id) {
						$commmentOptions['clusterId'] = $streamItem->cluster_id;
					}

					$streamItem->comments = ES::comments($streamItem->contextId, $streamItem->context, $streamItem->verb, $itemGroup, $commmentOptions, $streamItem->uid);
				}

				// Set the stream id
				$streamItem->comments->setOption('streamid', $streamItem->uid);

				// If comments link is meant to be disabled, hide it
				if (!$commentLink) {
					$streamItem->commentLink = false;
				}

				// If comments is meant to be disabled, hide it.
				if ($streamItem->comments && ((isset($streamItem->commentForm) && !$streamItem->commentForm) || !$commentForm || !$streamItem->canComment()) ) {
					$streamItem->comments->setOption('hideForm', true);

					// if the user doesn't have permission to add comment, by right it shouldn't appear the comment link as well
					$streamItem->commentLink = false;
				}
			}

			// Likes
			if ($streamItem->canLike()) {
				if (isset($streamItem->likes) && $streamItem->likes) {
					if (!$streamItem->likes instanceof SocialLikes) {
						$likes = ES::likes();

						$likesOptions = array();
						if ($streamItem->cluster_id) {
							$likesOptions['clusterId'] = $streamItem->cluster_id;
						}

						$likes->get($streamItem->contextId, $streamItem->context, $streamItem->verb, $itemGroup, $streamItem->uid, $likesOptions);

						$streamItem->likes = $likes;
					}
				}

				//set likes option the streamid
				if ($streamItem->likes) {
					$streamItem->likes->setOption('streamid', $streamItem->uid);
				}
			}

			// Stream reposting
			if ($streamItem->canRepost() && $streamItem->repost !== false) {

				if (!$streamItem->repost instanceof SocialRepost) {
					$streamItem->repost = ES::get('Repost', $streamItem->uid, SOCIAL_TYPE_STREAM, $itemGroup);
				}

				// set cluseter into repost
				if ($streamItem->isCluster()) {
					$streamItem->repost->setCluster($streamItem->cluster_id, $streamItem->cluster_type);
				}
			}

			if ($streamItem->canShare()) {
				// Backward compatibility for PHP 8.1 #5287
				$streamItem->content = is_null($streamItem->content) ? '' : $streamItem->content;

				$content = strip_tags($streamItem->content);

				if (ESJString::strlen($content) >= 50) {
					$content = ESJString::substr($content, 0, 50) . JText::_('COM_EASYSOCIAL_ELLIPSIS');
				}

				$sharingOptions = array(
					'url' => ESR::stream(array('layout' => 'item', 'id' => $streamItem->uid, 'external' => true), true),
					'display' => 'dialog',
					'text' => JText::_('COM_EASYSOCIAL_STREAM_SOCIAL'),
					'css' => 't-fs--sm',
					'title' => $content,
					'summary' => $content
				);

				$streamItem->sharing = ES::sharing($sharingOptions);
			} else {
				$streamItem->sharing = false;
			}

			// Always enforce actions to be hidden for mini stream items
			if ($streamItem->isMini() || $streamItem->state == SOCIAL_STREAM_STATE_MODERATE) {
				$streamItem->comments = false;
				$streamItem->likes = false;
				$streamItem->repost = false;
				$streamItem->actions = '';
			} else {
				$streamItem->actions = $this->getActions($streamItem);
			}

			$truncate = isset($options['truncate']) ? $options['truncate'] : true;

			// lets truncate the content here
			if ($streamItem->content && $config->get('stream.content.truncate') && $truncate) {
				$tpLib = ES::template();
				$streamItem->content = $tpLib->html('string.truncate', $streamItem->content, $config->get('stream.content.truncatelength'));
			}

			// Re-assign stream item to the result list.
			$data[]	= $streamItem;

			if ($enforceLimit && count($data) == $enforceLimit) {

				$this->startlimit = $this->startlimit + $itemProcessed;

				return $data;
			}
		}


		// here we know, the result from queries contain some records but it might return empty data due to privacy.
		// if that is the case, then we return TRUE so that the library will go retrieve the next set of data.
		if (count($data) <= 0) {
			return true;
		}

		return $data;
	}

	/**
	 * get hashtag title
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getHashTag($id)
	{
		$tb = ES::table('StreamTags');

		$tb->loadByTitle($id);
		return $tb;
	}

	/**
	 * Set stream access.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function updateAccess($streamId, $privacy, $custom = null, $field = null)
	{
		$model = ES::model('Stream');

		$customPrivacy = '';
		if ($privacy == SOCIAL_PRIVACY_CUSTOM) {
			if ($custom) {
				if (! is_array($custom)) {
					$customPrivacy = $custom;
				} else {
					$customPrivacy = implode(',', $custom);
				}

				$customPrivacy = ',' . $customPrivacy . ',';
			}
		}

		$fieldPrivacy = '';
		if ($privacy == SOCIAL_PRIVACY_FIELD) {
			$fieldPrivacy = explode(';', $field);
		}

		$state = $model->updateAccess( $streamId, $privacy, $customPrivacy, $fieldPrivacy );
		return $state;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getActivityNextLimit()
	{
		return $this->nextlimit;
	}

	/**
	 * Some desc
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLogs($max = 10)
	{
		$model = ES::model('Activities');
		$result = $model->getData($max);

		$data = $this->format($result);

		return $data;
	}

	/**
	 * Retrieves a list of stream item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getActivityLogs($options = array())
	{

		$uId = isset($options['uId']) ? $options['uId'] : '';
		$uType = isset($options['uType']) ? $options['uType'] : SOCIAL_TYPE_USER;
		$context = isset($options['context']) ? $options['context'] : SOCIAL_STREAM_CONTEXT_TYPE_ALL;
		$filter = isset($options['filter']) ? $options['filter'] : 'all';
		$max = isset($options['max']) ? $options['max'] : '';
		$limitstart = isset($options['limitstart']) ? $options['limitstart'] : 0;

		if (empty($uId)) {
			$uId = ES::user()->id;
		}

		if (empty($context)) {
			$context = SOCIAL_STREAM_CONTEXT_TYPE_ALL;
		}

		$activity = ES::model('Activities');

		if (! $limitstart) {
			$activity->setState('limitstart', 0);
		}

		$options = array('uId' => $uId,
						'uType' => $uType,
						'context' => $context,
						'filter' => $filter,
						'max' => $max,
						'limitstart' => $limitstart
					);

		$result = $activity->getItems($options);

		$this->nextlimit = $activity->getNextLimit($limitstart);

		// If there's nothing, just return a boolean value.
		if (!$result) {
			return false;
		}

		// register the resultset.
		$streamModel = ES::model('Stream');
		$streamModel->setBatchActivityItems($result);

		// $data = $this->format($result, $context, null, false, 'onPrepareActivityLog');
		$data = $this->format($result, $context, null, false, 'onPrepareStream', array('isActivity' => true));

		if (is_bool($data)) {
			return array();
		}

		for ($i = 0; $i < count($data); $i++) {
			$item =& $data[$i];
			$item->isHidden = ($filter == 'hidden') ? true : false;
		}

		$this->data = $data;
		return $this->data;
	 }

	/**
	 * Retrieves a single stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getItem($streamId, $clusterId = '', $clusterType = '', $loadModerated = false, $displayOptions = array(), $customOptions = array())
	{
		$model = ES::model('Stream');

		$isScheduled = ES::normalize($displayOptions, 'isScheduled', false);

		// Default options
		$options = array(
			'streamId' => $streamId,
			'context' => 'all',
			'ignoreUser' => true,
			'viewer' => $this->my->id,
			'isScheduled' => $isScheduled
		);

		// If configured to retrieve moderated items, we should explicitly let the model know about this
		if ($loadModerated) {
			$options['moderated'] = true;
		}

		// Retrieve data based on the type
		if ($clusterId && $clusterType) {

			$options['clusterId'] = $clusterId;
			$options['clusterType'] = $clusterType;

			$result = $model->getClusterStreamData($options);
		} else {
			$result = $model->getStreamData($options);
		}

		if (!$result) {
			return false;
		}

		$result[0]->isNew = true;

		$options = array();

		// Do not truncate the content for single stream item
		if ($streamId) {
			$options['truncate'] = false;
		}

		if (isset($displayOptions['perspective'])) {
			$options['perspective'] = $displayOptions['perspective'];
		}

		if (isset($displayOptions['disableActions'])) {
			$options['disableActions'] = $displayOptions['disableActions'];
		}

		$defaultEvent = 'onPrepareStream';

		if (isset($customOptions['defaultEvent']) && $customOptions['defaultEvent']) {
			$defaultEvent = $customOptions['defaultEvent'];
		}

		$this->data = $this->format($result, 'all', null, true, $defaultEvent, $options);
		$this->singleItem = true;

		return $this->data;
	}


	/**
	 * Retrieves a single stream item actor.
	 * return: SocialUser object, all false if not found.
	 * @since	1.0
	 */
	public function getStreamActor($streamId)
	{
		$model 	= ES::model('Stream');
		$actor 	= $model->getStreamActor($streamId);
		return $actor;
	}


	public function getPublicStream($limit = 10, $startlimit = 0, $hashtag = null, $perspective = null, $anywhereId = null)
	{
		$this->guest = true;

		$viewerId = ES::user()->id;
		$context = SOCIAL_STREAM_CONTEXT_TYPE_ALL;

		$attempts = 2;
		$keepSearching = true;

		$model = ES::model('Stream');

		$this->startlimit = $startlimit;
		$this->customLimit = $limit;

		// do {
		// 	$options	= array(
		// 					'userid' => '0',
		// 					'context' => $context,
		// 					'direction' => 'older',
		// 					'limit' => $limit,
		// 					'startlimit' => $startlimit,
		// 					'guest' => true,
		// 					'ignoreUser' => true,
		// 					'viewer' => $viewerId,
		// 					'anywhereId' => $anywhereId
		// 				);

		// 	if ($hashtag) {
		// 		$options['tag'] = $hashtag;
		// 	}

		// 	$result = $model->getStreamData($options);

		// 	// If there's nothing, just return a boolean value.
		// 	if (!$result) {
		// 		$this->startlimit = 0; // so that the next cycle will stop
		// 		return $this;
		// 	}

		// 	$streamOptions = array('perspective' => $perspective);

		// 	$requireSearch =  $this->format($result, $context, $viewerId, true, 'onPrepareStream', $streamOptions);

		// 	if ($requireSearch !== true) {
		// 		$this->data = $requireSearch;
		// 		$keepSearching = false;
		// 	}

		// 	$attempts--;

		// 	$startlimit = $startlimit + $limit;
		// 	$this->startlimit = $startlimit ;

		// } while($keepSearching === true && $attempts > 0);


		$options	= array(
						'userid' => '0',
						'context' => $context,
						'direction' => 'older',
						'limit' => $limit,
						'startlimit' => $startlimit,
						'guest' => true,
						'ignoreUser' => false,
						'viewer' => $viewerId,
						'anywhereId' => $anywhereId
					);

		if ($hashtag) {
			$options['tag'] = $hashtag;
		}

		$result = $model->getStreamData($options);

		// If there's nothing, just return a boolean value.
		if (!$result) {
			$this->startlimit = 0; // so that the next cycle will stop
			return $this;
		}

		// $startlimit = $startlimit + $limit;
		$this->startlimit = $startlimit + $limit;

		$hasItems = true;

		$streamOptions = array('perspective' => $perspective);

		$requireSearch =  $this->format($result, $context, $viewerId, true, 'onPrepareStream', $streamOptions);

		// if ($requireSearch !== true) {
		// 	$this->data = $requireSearch;
		// 	$keepSearching = false;
		// }

		if ($requireSearch === true && $hasItems) {

			// reset the options
			$options['startlimit'] = $startlimit + $limit;
			$options['limit'] = 30;

			$result = $model->getStreamData($options);

			if ($result) {
				$requireSearch = $this->format($result, $context, $viewerId, true, 'onPrepareStream', $streamOptions, $limit);
			}
		}

		$this->data = $requireSearch;
	}

	public function getPagination()
	{
		$htmlContent = '';

		if ($this->pagination) {
			$page = $this->pagination;

			$previousLink = '';
			$nextLink = '';

			//build the extra params into the url
			$params = $this->buildPaginationParams();


			if (! is_null($page['previous'])) {
				$previousLink = JRoute::_($params . '&limitstart=' . $page['previous']);
			}

			if (! is_null($page['next'])) {
				$nextLink = JRoute::_($params . '&limitstart=' . $page['next']);
			}

			$theme = ES::themes();

			$theme->set('next', $nextLink);
			$theme->set('previous', $previousLink);

			$htmlContent 	= $theme->output('site/stream/pagination');
		}

		return $htmlContent;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function buildPaginationParams()
	{
		$params = '';

		$view = $this->input->get('view', 'dashboard', 'cmd');
		$layout = $this->input->get('layout');

		if ($view) {
			$params .= '&view=' . $view;
		}

		if ($layout) {
			$params .= '&layout=' . $layout;
		} else {

			if ($view == 'groups' || $view == 'events' || $view == 'pages') {
				$params .= '&layout=item';
			}

			if ($view=='profile') {
				$params .= '&layout=timeline';
			}
		}

		$type = $this->input->get('type', '');
		$filterId = $this->input->get('filterid', '');
		$id = $this->input->get('id', '');

		if ($type == 'custom') {
			$type = 'filter';
		}

		// Loadmore pagination style
		$params .= '&type=' . $type;

		if ($filterId) {
			$params .= '&filterid=' . $filterId;
		}

		if ($id) {

			if ($view == 'profile' || $view == 'profiles' || $view == 'groups' || $view == 'events' || $view == 'pages') {
				$params .= '&id=' . $id;
			} else if ($type == 'group') {
				$params .= '&groupId=' . $id;
			} else if ($type == 'event') {
				$params .= '&eventId=' . $id;
			} else if ($type == 'page') {
				$params .= '&pageId=' . $id;
			} else {
				$params .= '&filterid=' . $id;
			}
		}

		$filter = $this->input->get('filter', '');
		$tag = $this->input->get('tag', '');
		$app = $this->input->get('app', '');

		if ($filter) {
			$params .= '&filter=' . $filter;
		}

		if ($filterId) {
			$params .= '&filterId=' . $filterId;
		}

		if ($tag) {
			$params .= '&tag=' . $tag;
		}

		if ($app) {
			$params .= '&app=' . $app;
		}

		if (!$this->input->get('Itemid')) {
			$Itemid = ESR::getItemId($view);
			$params .= '&Itemid=' . $Itemid;
		}

		return $params;
	}

	/**
	 * Renders the filter form html
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFilterForm($uid, $type, $id = null)
	{
		$filter = ES::table('StreamFilter');

		// If an id is given, try to load it.
		if ($id) {
			$filter->load($id);
		}

		$desc = JText::_('COM_EASYSOCIAL_STREAM_FILTER_DESCRIPTION');

		if ($type != SOCIAL_TYPE_USER) {
			$desc = JText::sprintf('COM_ES_STREAM_FILTER_CLUSTERS_DESC', $type);
		}

		$theme = ES::themes();
		$theme->set('desc', $desc);
		$theme->set('uid', $uid);
		$theme->set('type', $type);
		$theme->set('filter', $filter);

		$contents = $theme->output('site/stream/forms/filter');

		return $contents;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getStickies($options = array(), $displayOptions = array())
	{
		$results = null;

		if (!ES::config()->get('stream.pin.enabled')) {
			return array();
		}

		// lets process default values
		$type = 'sticky';
		$userId = isset($options['userId'])? $options['userId'] : null;
		$viewerId = isset($options['viewerId'])? $options['viewerId'] : null;
		$adminOnly = isset($options['adminOnly'])? $options['adminOnly'] : false;
		$defaultEvent = isset($options['defaultEvent']) ? $options['defaultEvent'] : 'onPrepareStream' ;

		// If viewer is null, we assume the caller wants to fetch from the current user's perspective.
		if (is_null($viewerId)) {
			$viewerId 	= ES::user()->id;
		}

		$adminIds = false;

		// Only retrieve sticky from admin
		if ($adminOnly) {
			$userModel = ES::model('Users');
			$adminIds = $userModel->getSiteAdmins(true);
		}

		// If no userId passed, we get the current logged in user
		$user = ES::user();
		$userId = (empty($userId)) ? $user->id : $userId;

		// Ensure that the user id's are in an array form.
		if (!is_array($userId)) {
			$userId = array($userId);
		}

		// Cluster stream items
		$clusterId = isset($options['clusterId']) ? $options['clusterId'] : null;
		$clusterType = isset($options['clusterType']) ? $options['clusterType'] : null;
		$clusterCategory = isset($options['clusterCategory']) ? $options['clusterCategory'] : null;
		$context = SOCIAL_STREAM_CONTEXT_TYPE_ALL;

		$limit = isset($options['limit']) ? $options['limit'] : 0;

		$configs	= array(
								'userid' => $userId,
								'viewer' => $viewerId,
								'actorid' => $adminIds,
								'context' => $context,
								'issticky' => true,
								'clusterId' => $clusterId,
								'clusterType' => $clusterType,
								'clusterCategory' => $clusterCategory,
								'limit' => $limit
							);

		// if this is user stickes, lets get only users items and not clusters.
		if (! $clusterId && $userId) {
			$configs['userstickyonly'] = true;
		}

		if ($adminOnly) {
			$configs['isadminsticky'] = true;
		}

		if (isset($displayOptions['perspective']) && $displayOptions['perspective']) {
			$configs['perspective'] = $displayOptions['perspective'];
		}

		$model	= ES::model('Stream');


		//trigger onBeforeGetStream
		$this->triggerBeforeGetStream($configs);

		// Bind the context to the object
		$tmpContext = $configs['context'];

		if (is_array($configs['context'])) {
			$tmpContext = (count($configs['context']) > 1) ? implode('|', $configs['context']) : $configs['context'][0];
		}

		$this->currentContext = $tmpContext;

		// since we allow options override, we need to perform checking only after the triggering
		$isCluster = ($configs['clusterId'] || $configs['clusterType'] || $configs['clusterCategory']) ? true : false ;
		$this->isCluster = $isCluster;
		$this->clusterType = $configs['clusterType'];
		$this->clusterId = $configs['clusterId'];

		// $isCluster = ($clusterId || $clusterType || $clusterCategory) ? true : false ;

		if ($isCluster) {
			$results = $model->getClusterStreamData($configs);
		} else {
			$results = $model->getStreamData($configs);
		}

		// If there's nothing, just return a boolean value.
		if (!$results) {
			return array();
		}

		// $this->uids = $model->getUids();

		// now we are safe to run the format function.
		$data  = $this->format($results, $context, $viewerId, true, $defaultEvent, array());
		return $data;
	}

	/**
	 * Retrieves a list of stream item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function get($options = array(), $displayOptions = array())
	{
		$users = array();

		// Lets process default values
		$actorId = isset($options['actorId']) ? $options['actorId'] : null;
		$userId = isset($options['userId']) ? $options['userId'] : null;
		$listId = isset($options['listId']) ? $options['listId'] : null;
		$profileId = isset($options['profileId']) ? $options['profileId'] : null;
		$context = isset($options['context']) ? $options['context'] : SOCIAL_STREAM_CONTEXT_TYPE_ALL;
		$type = isset($options['type']) ? $options['type'] : SOCIAL_TYPE_USER;
		$limitStart = isset($options['limitStart']) ? $options['limitStart'] : '';
		$limitEnd = isset($options['limitEnd']) ? $options['limitEnd'] : '';
		$direction = isset($options['direction'])  ? $options['direction'] : 'older';
		$viewerId = isset($options['viewerId'])   ? $options['viewerId'] : null;
		$guest = isset($options['guest'])   ? $options['guest'] : false;
		$tag = isset($options[ 'tag' ]) ? $options[ 'tag' ] : false;
		$matchAllTags = isset($options['matchAllTags']) ? $options['matchAllTags'] : false;
		$ignoreUser = isset($options[ 'ignoreUser' ]) ? $options[ 'ignoreUser' ] : false ;
		$onlyModerated = isset($options['onlyModerated']) ? $options['onlyModerated'] : false;
		$noSticky = isset($options[ 'nosticky' ]) ? $options[ 'nosticky' ] : false ;
		$userStickyOnly = isset($options[ 'userStickyOnly' ]) ? $options[ 'userStickyOnly' ] : null ;
		$isStickyFilter = isset($options['isStickyFilter']) ? $options['isStickyFilter'] : null;
		$includeClusterSticky = isset($options[ 'includeClusterSticky' ]) ? $options[ 'includeClusterSticky' ] : false ;
		$anywhereId = isset($options['anywhereId' ]) ? $options[ 'anywhereId' ] : false ;
		$defaultEvent = isset($options['defaultEvent' ]) ? $options['defaultEvent'] : 'onPrepareStream' ;
		$includeApps = isset($options['includeApps']) ? $options['includeApps'] : null;

		$aspect = isset($options['aspect']) ? $options['aspect'] : false;

		// exclude streams
		$excludeStreamIds = isset($options['excludeStreamIds']) ? $options['excludeStreamIds'] : null;

		// Scheduled posts
		$isScheduled = ES::normalize($options, 'isScheduled', false);

		$customView = isset($options[ 'view' ]) ? $options[ 'view' ] : false ;

		// Cluster stream items
		$clusterId = isset($options['clusterId']) ? $options['clusterId'] : null;
		$clusterType = isset($options['clusterType']) ? $options['clusterType'] : null;
		$clusterCategory = isset($options['clusterCategory']) ? $options['clusterCategory'] : null;
		$clusterUserId = isset($options['clusterUserId']) ? $options['clusterUserId'] : null;

		// Pagination stuffs
		$limit = isset($options['limit']) ? $options['limit'] : ES::config()->get('stream.pagination.pagelimit', 10);


		// debug
		// $limit = 1;

		// check if custom limit passed in or not.
		if (isset($options['customlimit']) && $options['customlimit']) {
			$limit = $options['customlimit'];
			$this->customLimit = $limit;
		}

		$startlimit = isset($options['startlimit']) ? $options['startlimit'] : 0;
		$startdatelimit = isset($options['startdatelimit']) ? $options['startdatelimit'] : '';

		if (!is_array($context) && strpos($context, '|') !== false) {
			$context = explode('|', $context);
		}

		// If viewer is null, we assume the caller wants to fetch from the current user's perspective.
		if (is_null($viewerId)) {
			$user = ES::user();
			$viewerId = $user->id;
		} else {
			$user = ES::user($viewerId);
		}

		// Ensure that the user id's are in an array form.
		$userId = (empty($userId)) ? $user->id : $userId;
		$userId	= ES::makeArray($userId);

		if (empty($context)) {
			$context = SOCIAL_STREAM_CONTEXT_TYPE_ALL;
		}

		$isFollow = false;

		if ($type == 'follow') {
			$this->filter 	= 'follow';

			// reset the type to user and update the isFollow flag.
			$type = SOCIAL_TYPE_USER;
			$isFollow = true;
		}

		$isBookmark = false;
		if ($type == 'bookmarks') {
			$this->filter = 'bookmarks';

			// reset the type to user and update the isBookmark flag.
			$type = SOCIAL_TYPE_USER;
			$isBookmark = true;
		}

		$isSticky = false;
		if ($type == 'sticky') {
			$this->filter 	= 'sticky';

			// reset the type to user and update the isSticky flag.
			$type = SOCIAL_TYPE_USER;
			$isSticky = true;
			$userStickyOnly = is_null($userStickyOnly) ? true : $userStickyOnly;
		}

		// check if $userStickyOnly is null or not. if yes, set it to false
		if (is_null($userStickyOnly)) {
			$userStickyOnly = false;
		}

		if ($listId) {
			$this->filter = 'list';
		}

		if ($guest) {
			$this->filter = 'everyone';
		}

		if ($onlyModerated) {
			$this->filter = 'moderation';
		}

		// Ensure that the tag is an array
		$tag = ES::makeArray($tag);

		if ($tag) {
			$this->filter = 'custom';
		}

		if ($isScheduled) {
			$this->filter = 'scheduled';
		}

		// Get stream model to fetch those records.
		$model = ES::model('Stream');
		$data = array();

		$keepSearching = true;
		$tryLimit      = 2;

		$options	= array(
								'actorid' => $actorId,
								'userid' => $userId,
								'list' => $listId,
								'profileId' => $profileId,
								'context' => $context,
								'type' => $type,
								'limitstart' => $limitStart,
								'limitend' => $limitEnd,
								'viewer' => $viewerId,
								'isfollow' => $isFollow,
								'isbookmark' => $isBookmark,
								'issticky' => $isSticky,
								'nosticky' => $noSticky,
								'userstickyonly' => $userStickyOnly,
								'includeclustersticky' => $includeClusterSticky,
								'direction' => $direction,
								'guest' => $guest,
								'tag' => $tag,
								'matchAllTags' => $matchAllTags,
								'ignoreUser' => $ignoreUser,
								'clusterId' => $clusterId,
								'clusterType' => $clusterType,
								'clusterCategory' => $clusterCategory,
								'startlimit' => $startlimit,
								'startdatelimit' => $startdatelimit,
								'limit' => $limit,
								'onlyModerated' => $onlyModerated,
								'customView' => $customView,
								'excludeStreamIds' => $excludeStreamIds,
								'anywhereId' => $anywhereId,
								'aspect' => $aspect,
								'includeApps' => $includeApps,
								'isStickyFilter' => $isStickyFilter,
								'isScheduled' => $isScheduled
							);

		if (isset($displayOptions['perspective']) && $displayOptions['perspective']) {
			$options['perspective'] = $displayOptions['perspective'];
			$this->perspective = $displayOptions['perspective'];
		}

		//trigger onBeforeGetStream
		$this->triggerBeforeGetStream($options);

		// Bind the context to the object
		$tmpContext = $options['context'];

		if (is_array($options['context'])) {
			$tmpContext = (count($options['context']) > 1) ? implode('|', $options['context']) : $options['context'][0];
		}

		$this->currentContext = $tmpContext;

		// since we allow options override, we need to perform checking only after the triggering
		$isCluster = ($options['clusterId'] || $options['clusterType'] || $options['clusterCategory']) ? true : false ;

		$this->isCluster = $isCluster;
		$this->clusterType = $clusterType;
		$this->clusterId = $clusterId;
		$this->options = $options;
		$this->startlimit = $startlimit;

		$result = null;

		if ($isCluster) {
			$result = $model->getClusterStreamData($options);
		}

		if (!$isCluster) {
			$result = $model->getStreamData($options);
		}

		// If there's nothing, just return a boolean value.
		if (!$result) {
			$this->startlimit = '';
			return $this;
		}

		$hasItems = true;

		// we need to get the pagination and total first before u can execute the format.
		// this is because during the format, the shares context type might overwrite the total due
		// to another call to stream lib the get function.

		$this->pagination = $model->getPagination();

		// now need to get the next date limit
		$this->startdatelimit = $model->getNextDateLimit();

		//determine if loadmore show be displayed or not.
		$total = $model->getTotalCount();

		// if ($total && ($total - ($startlimit + $limit)) >= 1) {
		if ($total && ($total >= 1)) {
			$this->startlimit = $startlimit + $limit;
		} else {
			$this->startlimit = '';
		}

		if ($direction == 'later') {
			$this->nextdate = $model->getCurrentStartDate();
		}

		$this->uids = $model->getUids();

		// now we are safe to run the format function.
		$requireSearch = $this->format($result, $context, $viewerId, true, $defaultEvent, $displayOptions);

		if ($requireSearch === true && $hasItems && !$isCluster && $direction != 'later') {

			// reset the options
			$options['startlimit'] = $startlimit + $limit;

			//startdatelimit
			$options['startdatelimit'] = $this->startdatelimit;

			$options['limit'] = 200;

			$result = $model->getStreamData($options);

			if ($result) {
				$requireSearch = $this->format($result, $context, $viewerId, true, $defaultEvent, $displayOptions, $limit);

				if ($requireSearch && is_array($requireSearch)) {

					// this is abit different since the limit is 50. we need to recalculate the next date limit
					foreach ($requireSearch as $item) {
						$modified =  $item->modified;
						// the item is now a StreamItem object and modified is a esdate object.
						if ($item->modified instanceof SocialDate) {
							$modified = $item->modified->toSql();
						}

						$this->startdatelimit = $modified;
					}
				}
			}
		}

		$this->data = $requireSearch;

		// triggering onAfterGetStream
		$this->triggerAfterGetStream($this->data);

		return $this;
	}

	/**
	 * Retrieves the total number of stream items in the current result set
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getCount()
	{
		if ($this->data && is_array($this->data)) {
			return count($this->data);
		}

		return 0;
	}

	/**
	 * Returns next start date used in stream pagination
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getNextStartDate()
	{
		return $this->nextdate;
	}

	/**
	 * Returns next end date used in stream pagination
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getNextEndDate()
	{
		return $this->enddate;
	}

	/**
	 * Returns next limit used in public stream pagination
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getNextStartLimit()
	{
		return $this->startlimit;
	}

	/**
	 * Returns next limit used in public stream pagination
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getNextStartDateLimit()
	{
		return $this->startdatelimit;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getUids()
	{
		return $this->uids;
	}

	/**
	 * Returns a html formatted data for the stream
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function html($loadmore = false, $customEmptyMsg = '', $options = array())
	{
		$theme = ES::themes();
		$output = '';

		// Determine if we should only get the content only
		$contentOnly = isset($options['contentOnly']) ? $options['contentOnly'] : false;

		// determine if we need to force to off autoload
		$forceAutoLoadOff = isset($options['forceAutoLoadOff']) ? $options['forceAutoLoadOff'] : false;

		// Get the current view
		$view = $this->input->get('view', '', 'cmd');

		if (!$view) {
			// try getting from the 'source'
			$view = $this->input->get('source', '', 'cmd');
		}

		if (!$view) {
			// if still empty, lets try getting from the 'perpective'
			$view = $this->perspective;
		}

		// Default options
		$isGuest = $this->guest;
		$story = false;
		$stickies = false;
		$context = $this->currentContext;
		$pagination = false;

		// Check again if the current viewer is a guest
		if (!$isGuest) {
			$isGuest = ($this->my->id == 0 && $view != 'groups') ? true : $isGuest;
		}

		// Get the context
		if (is_array($this->currentContext)) {
			$context = implode('|', $this->currentContext);
		}

		if (!empty($this->story)) {
			$story = $this->story;
		}

		// Exclude stream ids that will be load in pagination
		// Typically for stickies items.
		$excludeStreamIds = array();

		// Sticky posts
		if (isset($this->stickies) && $this->stickies) {
			$stickies = $this->stickies;

			// Exclude it from the main listings
			if ($stickies) {
				foreach ($stickies as $stick) {
					$excludeStreamIds[] = $stick->uid;
				}
			}
		}

		// Determines if we should display the translations.
		$language = $this->my->getLanguage();
		$siteLanguage = JFactory::getLanguage();
		$showTranslations = false;

		if (($language != $siteLanguage->getTag()) || $this->config->get('stream.translations.explicit')) {
			$showTranslations = true;
		}

		// Determine for the view whether that is under cluster single category layout or not
		// We need to convert the view name to layout name especially cluster
		$layout = $this->input->get('layout', '', 'cmd');
		$isClusterCategoryLayout = '';

		if (in_array($view, array(SOCIAL_TYPE_GROUPS, SOCIAL_TYPE_EVENTS, SOCIAL_TYPE_PAGES)) && $layout == 'category') {
			$isClusterCategoryLayout = $layout;
		}

		$theme->set('context', $context);
		$theme->set('view', $view);
		$theme->set('story', $story);
		$theme->set('stickies', $stickies);
		$theme->set('pagination', $pagination);
		$theme->set('showTranslations', $showTranslations);
		$theme->set('identifier', $this->identifier);
		$theme->set('excludeStreamIds', $excludeStreamIds);
		$theme->set('isClusterCategoryLayout', $isClusterCategoryLayout);

		if ($this->config->get('ads.enabled') && $this->data) {
			$this->data = $this->generateAdsStream($loadmore);
		}

		// Loadmore requests
		if ($loadmore) {

			if ($this->data && is_array($this->data)) {
				$pos = 1;
				foreach ($this->data as $stream) {

					// trigger onEasySocialStreamBeforeRender
					$renderBeforeText = $this->beforeDisplayStreamItem($stream, $pos, $view);
					if ($renderBeforeText) {
						$output .= $renderBeforeText;
					}

					if ($stream->getType() == SOCIAL_TYPE_ADVERTISEMENT) {
						$output .= $stream->html();
					} else {
						$namespace = $contentOnly ? 'site/stream/default/item.content' : 'site/stream/default/item';

						$options = array('stream' => $stream, 'showTranslations' => $showTranslations, 'view' => $view);
						$output .= $theme->loadTemplate($namespace, $options);
					}

					// trigger onEasySocialStreamAfterRender
					$renderAfterText = $this->afterDisplayStreamItem($stream, $pos++, $view);
					if ($renderAfterText) {
						$output .= $renderAfterText;
					}
				}
			}

			return $output;
		}

		// Single stream item
		if ($this->singleItem) {

			// Item is most likely unavailable to the current viewer
			if (!$this->data || (is_array($this->data) && count($this->data) == 0) || $this->data === true) {
				$output .= $theme->output('site/stream/default/unavailable');

				return $output;
			}


			$theme->set('stream', $this->data[0]);

			$namespace = $contentOnly ? 'site/stream/default/item.content' : 'site/stream/default/item';

			$output = $theme->output($namespace);

			return $output;
		}

		// Standard stream output
		// Define empty messages here
		$empty = $customEmptyMsg ? $customEmptyMsg : JText::_('COM_EASYSOCIAL_STREAM_NO_STREAM_ITEM');

		if ($this->filter == 'follow') {
			$empty = $customEmptyMsg ? $customEmptyMsg : JText::_('COM_EASYSOCIAL_STREAM_NO_STREAM_ITEM_FROM_FOLLOWING');
		}

		if ($this->filter == 'list') {
			$empty = $customEmptyMsg ? $customEmptyMsg : JText::_('COM_EASYSOCIAL_STREAM_NO_STREAM_ITEM_FROM_LIST');
		}

		if ($this->filter == 'moderation') {
			$empty = $customEmptyMsg ? $customEmptyMsg : JText::_('COM_EASYSOCIAL_STREAM_NO_STREAM_ITEM_FROM_MODERATION');
		}

		if ($this->filter == 'custom') {
			$empty = $customEmptyMsg ? $customEmptyMsg : JText::_('COM_EASYSOCIAL_STREAM_NO_STREAM_ITEM_FROM_CUSTOM');
		}

		if ($this->filter == 'scheduled') {
			$empty = $customEmptyMsg ? $customEmptyMsg : JText::_('COM_ES_STREAM_NO_STREAM_ITEM_FROM_SCHEDULED');
		}

		// TODO: later we will get from setting.
		$autoload = $this->config->get('stream.pagination.autoload');

		if ($autoload && $forceAutoLoadOff) {
			$autoload = false;
		}

		// custom limits
		$customlimit = ($this->customLimit) ? $this->customLimit : 0;

		$theme->set('empty', $empty);
		$theme->set('streams', $this->data);
		$theme->set('nextdate', $this->nextdate);
		$theme->set('enddate', $this->enddate);
		$theme->set('guest', $isGuest);
		$theme->set('nextlimit', $this->startlimit);
		$theme->set('nextdatelimit', $this->startdatelimit);
		$theme->set('autoload', $autoload);
		$theme->set('customlimit', $customlimit);
		$theme->set('identifier', $this->identifier);
		$theme->set('cluster', $this->isCluster);
		$theme->set('clusterId', $this->clusterId);
		$theme->set('clusterType', $this->clusterType);

		$output = $theme->output('site/stream/default/default');

		return $output;
	}

	/**
	 * Generate random ads id
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getRandomAdsId($ids, $ads, $session)
	{
		$randId = $ids[array_rand($ids)];
		$previousAdsId = $session->get('easysocial.previousads', 0, SOCIAL_SESSION_NAMESPACE);

		// Only perform this if there are more than 1 ads
		if (count($ads) > 1) {
			while ($randId === $previousAdsId) {
				$randId = $ids[array_rand($ids)];
			}
		}

		// store the current ads id to prevent duplication
		$session->set('easysocial.previousads', $randId, SOCIAL_SESSION_NAMESPACE);

		return $randId;
	}

	/**
	 * Generate advertisement stream item
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function generateAdsStream($loadmore = false, $data = array())
	{
		// Ads should not appear on scheduled post listing.
		$filter = $this->input->get('type', 'all', 'word');

		if ($filter == 'scheduled') {
			return $this->data;
		}

		$items = $this->data;

		if (!empty($data)) {
			// attempt to override the data source with the data passed by caller.

			// if caller passed data but the data is not an array,
			// we should stop here.
			if (!is_array($data)) {
				return $data;
			}

			$items = $data;
		}

		if (is_bool($items)) {
			return $items;
		}

		$model = ES::model('Ads');
		$ads = $model->getItems();

		if (!$ads) {
			return $items;
		}

		$adItems = [];
		$ids = [];

		foreach ($ads as $ad) {
			$table = ES::table('Ad');
			$table->bind($ad);

			$adItems[$table->id] = $table;
			$ids = array_merge($ids, array_fill(0, $table->priority, $table->id));
		}

		// show Ads on every X number of stream
		$frequency = $this->config->get('ads.frequency', 5);
		$streamCount = -1;
		$session = JFactory::getSession();

		if ($loadmore) {
			$streamCount = $session->get('easysocial.streamcount', -1, SOCIAL_SESSION_NAMESPACE);
		}

		for ($i=0; $i <= count($items); $i++) {

			// This is when the loadmore kicked in.
			if ($loadmore && $i == 0) {
				$i = 1;

				if ($streamCount == -1) {

					$randId = $this->getRandomAdsId($ids, $ads, $session);

					// Inject ads stream
					array_splice($items, $i, 0, array($adItems[$randId]));

					continue;
				}
			}

			if ($streamCount == $frequency) {

				$i--;

				$randId = $this->getRandomAdsId($ids, $ads, $session);

				// Inject ads stream
				array_splice($items, $i, 0, array($adItems[$randId]));

				// reset count
				$streamCount = -1;
			} else {
				$streamCount++;
			}
		}

		// Store the current count
		$session->set('easysocial.streamcount', $streamCount, SOCIAL_SESSION_NAMESPACE);

		return $items;
	}

	/**
	 * Generates the action for a stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function action()
	{
		$data = $this->data[0];

		$theme = ES::themes();
		$theme->set('stream', $data);

		$output = $theme->output('site/stream/actions/default');

		return $output;
	}

	/**
	 * Return the raw data for the stream.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function json()
	{
		//@TODO: Perhaps there's something that we need to modify here for json type?

		$json 	= ES::json();
		$output = $json->encode($this->data);

		return $output;
	}

	/**
	 * Return the raw data for the stream.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function toArray()
	{
		return $this->data;
	}

	/**
	 * Prepares stream actions.
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function onPrepareStreamActions(SocialStreamItem &$item)
	{
		// Get apps library.
		$apps 	= ES::getInstance('Apps');

		// Try to load user apps
		$state 	= $apps->load(SOCIAL_APPS_GROUP_USER);

		// By default return true.
		$result 	= true;

		if (!$state) {
			return false;
		}

		// Only go through dispatcher when there is some apps loaded, otherwise it's pointless.
		$dispatcher = ES::dispatcher();

		// Pass arguments by reference.
		$args = array(&$item);

		// @trigger: onPrepareStream for the specific context
		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onPrepareStreamActions', $args, $item->context);

		// @TODO: Check each actions and ensure that they are instance of ISocialStreamAction

		return true;
	}

	/**
	 * Prepares a stream item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		// Get apps library.
		$result = $this->onPrepareEvent('onPrepareStream', $item, $includePrivacy);
		return $result;
	}

	/**
	 * Prepares a REST API stream item.
	 *
	 * @since	3.1
	 * @access	public
	 */
	private function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = false)
	{
		$apps = ES::apps();
		$appGroup = $item->getGroup();
		$state = $apps->load($appGroup);

		if (!$state) {
			return true;
		}

		// Only go through dispatcher when there is some apps loaded, otherwise it's pointless.
		$dispatcher = ES::dispatcher();

		// Default vaue for contentObj
		$item->contentObj = false;

		// Default value for this item to show in the app
		$item->show = false;

		// Pass arguments by reference.
		$args = array(&$item, $includePrivacy, $viewer);

		// onPrepareStream for the specific context
		$result = $dispatcher->trigger($appGroup, 'onPrepareRestStream', $args, $item->context);

		return $result;
	}

	/**
	 * Prepares a digest item.
	 *
	 * @since	2.1
	 * @access	public
	 */
	private function onPrepareDigest(SocialStreamItem &$item)
	{
		// Get apps library.
		$result = $this->onPrepareEvent('onPrepareDigest', $item);
		return $result;
	}

	/**
	 * Prepares a stream item for activity logs
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		// Get apps library.
		$result = $this->onPrepareEvent(__FUNCTION__, $item, $includePrivacy);
		return $result;
	}

	/**
	 * Prepares the stream by rendering apps
	 *
	 * @since	1.4
	 * @access	public
	 */
	private function onPrepareEvent($eventName, SocialStreamItem &$item, $includePrivacy = true)
	{
		$apps = ES::apps();
		$appGroup = $item->getGroup();
		$state = $apps->load($appGroup);

		if (!$state) {
			return true;
		}

		// Only go through dispatcher when there is some apps loaded, otherwise it's pointless.
		$dispatcher = ES::dispatcher();

		// Pass arguments by reference.
		$args = array(&$item, $includePrivacy);

		// onPrepareStream for the specific context
		$result = $dispatcher->trigger($appGroup, $eventName, $args, $item->context);

		return $result;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function triggerAfterGetStream(&$items)
	{
		if (!$items) {
			return;
		}

		$view = $this->input->get('view', '', 'cmd');

		// Get apps library.
		$apps 	= ES::getInstance('Apps');

		// Determine the app group
		$group 	= SOCIAL_APPS_GROUP_USER;

		// Try to load user apps
		$state 	= $apps->load($group);

		// By default return true.
		$result 	= true;

		if (!$state) {
			return $result;
		}

		// Only go through dispatcher when there is some apps loaded, otherwise it's pointless.
		$dispatcher		= ES::dispatcher();

		// Pass arguments by reference.
		$args 			= array(&$items);

		// @trigger: onPrepareStream for the specific context
		$result 		= $dispatcher->trigger($group, 'onAfterGetStream', $args);


		return $result;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function triggerBeforeGetStream(&$options)
	{
		if (!$options) {
			return;
		}

		$view = $this->input->get('view', '', 'cmd');

		if (isset($options['customView']) && $options['customView']) {
			$view = strtolower($options['customView']);
		}

		if (isset($options['perspective']) && $options['perspective']) {
			$view = strtolower($options['perspective']);
		}

		// Get apps library.
		$apps = ES::getInstance('Apps');

		// Determine the app group user/group/page/event
		$appGroup = SOCIAL_APPS_GROUP_USER;

		// If it is in the group view we should render the apps based on the appropriate group
		if ($view && ($view == 'groups' || $view == 'events' || $view == 'stream' || $view == 'pages') && (isset($options['clusterType']) && $options['clusterType'])) {
			$appGroup = $options['clusterType'];
		}

		// Try to load user apps
		$state 	= $apps->load($appGroup);

		// By default return true.
		$result = true;

		if (!$state) {
			return false;
		}

		// Only go through dispatcher when there is some apps loaded, otherwise it's pointless.
		$dispatcher = ES::dispatcher();

		// Pass arguments by reference.
		$args = array(&$options, $view);

		$dispatcher->trigger($appGroup, 'onBeforeGetStream', $args);
	}


	/**
	 * Build the aggregated data
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function buildAggregatedData($activities)
	{
		// If there's no activity at all, it should fail here.
		// There should be at least 1 activity.
		if (!$activities)
		{
			return false;
		}

		$data 					= new stdClass();
		$data->contextIds		= array();
		$data->actors 			= array();
		$data->targets 			= array();
		$data->verbs 			= array();
		$data->params 			= array();

		// Temporary data
		$actorIds 		= array();
		$targetIds		= array();

		foreach($activities as $activity)
		{
			// Assign actor into temporary data only when actor id is valid.
			if ($activity->actor_id)
			{
				$actorIds[]			= $activity->actor_id;
			}

			// Assign target into temporary data only when target id is valid.
			if ($activity->target_id)
			{
				if (!($activity->context_type == 'photos' && $activity->verb == 'add')
					&& !($activity->context_type == 'shares' && $activity->verb == 'add.stream'))
				{
					$targetIds[]		= $activity->target_id;
				}
			}

			// Assign context ids.
			$data->contextIds[]	= $activity->context_id;

			// Assign the verbs.
			$data->verbs[]		= $activity->verb;

			// Assign the params
			$data->params[ $activity->context_id ]	= isset($activity->params) ? $activity->params : '';
		}

		// Pre load users.
		$userIds	= array_merge($data->actors, $data->targets);
		ES::user($userIds);


		// Build the actor's data
		if ($actorIds)
		{
			$actorIds = array_unique($actorIds);
			foreach($actorIds as $actorId)
			{
				$user 			= ES::user($actorId);

				$data->actors[]	= $user;
			}
		}

		// Build the target's data.
		if ($targetIds) {
			$targetIds = array_unique($targetIds);
			foreach ($targetIds as $targetId) {
				$user = ES::user($targetId);
				$data->targets[] = $user;
			}
		}

		return $data;
	}

	/**
	 * Renders the actions block that is used on any objects
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getActions($options = array())
	{
		$theme = ES::themes();

		// If the options is not a social stream item, we need to normalize the data
		if (!($options instanceof SocialStreamItem)) {
			$options = (object) $options;
		}

		// Set the default friendly date
		$friendlyDate = isset($options->friendlyDate) ? $options->friendlyDate : false;

		$date = isset($options->date) ? $options->date : null;
		$comments = isset($options->comments) ? $options->comments : '';
		$likes = isset($options->likes) ? $options->likes : '';
		$repost = isset($options->repost) ? $options->repost : '';
		$sharing = isset($options->sharing) ? $options->sharing : '';
		$uid = isset($options->uid) ? $options->uid : '';
		$privacy = isset($options->privacy) ? $options->privacy : '';
		$icon = isset($options->icon) ? $options->icon : '';
		$location = isset($options->location) ? $options->location : '';
		$commentLink = isset($options->commentLink) ? $options->commentLink : true;

		if (!is_null($date)) {
			$friendlyDate = ES::date($date)->toLapsed();
		}

		$showComments = false;
		$showCommentsListing = false;
		$showLikes = false;
		$showRepost = false;
		$showSharing = false;

		$access = $this->my->getAccess();

		if ($this->my->id && $likes && $likes instanceof SocialLikes) {
			$showLikes = true;
		}

		// If is guest, then we don't add the action link, but we still show the content if settings enabled
		if ($this->my->id && $comments && $access->allowed('comments.add') && $commentLink) {
			$showComments = true;
		}

		if ($this->my->id && $repost && $this->config->get('repost.enabled')) {
			$showRepost = true;

			// User that cannot post status update should not be able to repost story
			if (!$this->my->canPostStory()) {
				$showRepost = false;
			}
		}

		if ($this->my->id && $sharing && $this->config->get('sharing.enabled')) {
			$showSharing = true;
		}

		if ($comments && $access->allowed('comments.read') && $this->my->id) {
			$showCommentsListing = true;
		}

		if ($comments && $this->my->guest && $this->config->get('stream.comments.guestview')) {
			$showCommentsListing = true;
		}

		// Determines if the comment form should be visible
		$showCommentsForm = !$this->my->guest && $showComments;

		// Determines if the likes result should be shown
		$showLikesListing = $showLikes;

		if (isset($options->cluster_type) && $options->cluster_type == 'group' && $options->cluster_id) {
			$group = ES::group($options->cluster_id);

			if (!$group->isMember() && $group->isSemiOpen()) {
				$showLikes = false;
				$showComments = false;
				$showRepost = false;
				$showSharing = false;
				$showLikesListing = true;
				$showCommentsForm = false;
			}
		}

		// Determine whether the repost is available for non member(superadmin) for private and invite-only cluster.
		// By right, invite only page will not be visible.
		// Superadmin must be a member first before able to repost content.
		if (isset($options->cluster_type) && ($options->cluster_access == 2 || $options->cluster_access == 3)) {
			$group = ES::cluster($options->cluster_type, $options->cluster_id);

			if (!$group->isMember()) {
				$showRepost = false;
			}
		}

		$likesButton = '';

		// In the event where all reactions are disabled, we shouldn't display likes at all
		if ($showLikes) {
			$likesButton = $likes->button();

			if (!$likesButton) {
				$showLikes = false;
			}
		}

		// Privacy settings on the stream
		$theme->set('privacy', $privacy);

		// States to determine what should be visible
		$theme->set('showLikes', $showLikes);
		$theme->set('showLikesListing', $showLikesListing);
		$theme->set('showComments', $showComments);
		$theme->set('showCommentsForm', $showCommentsForm);
		$theme->set('showCommentsListing', $showCommentsListing);
		$theme->set('showRepost', $showRepost);
		$theme->set('showSharing', $showSharing);
		$theme->set('comments', $comments);
		$theme->set('likes', $likes);
		$theme->set('repost', $repost);
		$theme->set('sharing', $sharing);

		$output = $theme->output('site/stream/actions/default');

		return $output;
	}

	/**
	 * Translate stream's date time.
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function translateDate($day, $hour, $min)
	{
		$dayString  = '';
		$timeformat = '%I:%M %p';


		$day = ($day < 0) ? '0' : $day;
		$hour = ($hour < 0) ? '0' : $hour;
		$min = ($day < 0) ? '0' : $min;

		// today
		if ($day == 0)
		{
			if ($min > 60)
			{
				$dayString  = $hour . JText::_('COM_EASYSOCIAL_STREAM_X_HOURS_AGO');
			} else if ($min <= 0)
			{
				$dayString  = JText::_('COM_EASYSOCIAL_STREAM_LESS_THAN_ONE_MIN_AGO');
			}
			else
			{
				$dayString  = $min . JText::_('COM_EASYSOCIAL_STREAM_X_MINS_AGO');
			}
		}
		elseif ($day == 1)
		{
			$time 	= ES::date('-' . $min . ' mins');

			$dayString  = JText::_('COM_EASYSOCIAL_STREAM_YESTERDAY_AT') . $time->toFormat($timeformat);
		}
		elseif ($day > 1 && $day <= 7)
		{
			$dayString		= ES::get('Date', '-' . $min . ' mins')->toFormat('%A ' . JText::_('COM_EASYSOCIAL_STREAM_DATE_AT') . ' ' . $timeformat);
		}
		else
		{
			$dayString		= ES::get('Date', '-' . $min . ' mins')->toFormat('%b %d ' . JText::_('COM_EASYSOCIAL_STREAM_DATE_AT') . ' ' . $timeformat);
		}


		return $dayString;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	public static function getContentImage($item)
	{
		$image = new stdClass();
		$image->url = '';
		$image->width = '';
		$image->height = '';

		$content = $item->content . $item->preview;

		$pattern = '/\"background-image:\surl\(\'(.*)\'\);\"/i';
		preg_match($pattern, $content, $matches);

		if ($matches) {
			$imgPath = $matches[1];
			$image->url  = self::rel2abs($imgPath, JURI::root());

		} else {

			$img = '';
			$pattern = '#<img[^>]*>#i';
			preg_match($pattern, $content, $matches);

			if ($matches) {
				$img = $matches[0];
			}

			//image found. now we process further to get the absolute image path.
			if ($img) {
				//get the img source
				$pattern = '/src=[\"\']?([^\"\']?.*(png|jpg|jpeg|gif))[\"\']?/i';
				preg_match($pattern, $img, $matches);

				if ($matches) {
					$imgPath = $matches[1];
					$image->url = self::rel2abs($imgPath, JURI::root());
				}

				// Try to get width and height of the image to process og image tag
				$pattern = '/data-width="([^\"\']?.*)"/i';
				preg_match($pattern, $img, $matches);

				if ($matches) {
					$image->width = $matches[1];
				}

				// Try to get width and height of the image to process og image tag
				$pattern = '/data-height="([^\"\']?.*)"/i';
				preg_match($pattern, $img, $matches);

				if ($matches) {
					$image->height = $matches[1];
				}
			}
		}

		return $image;
	}

	/**
	 *
	 * @since	1.2
	 * @access	public
	 */
	public static function rel2abs($rel, $base)
	{
		/* return if already absolute URL */
		if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

		/* queries and anchors */
		if (@$rel[0]=='#' || @$rel[0]=='?') return $base.$rel;

		/* parse base URL and convert to local variables:
		   $scheme, $host, $path */
		extract(parse_url($base));

		/* remove non-directory element from path */
		$path = preg_replace('#/[^/]*$#', '', $path);

		/* destroy path if relative url points to root */
		if (@$rel[0] == '/') $path = '';

		/* dirty absolute URL */
		$abs = "$host$path/$rel";
		/* replace '//' or '/./' or '/foo/../' with '/' */
		$re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
		for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

		/* absolute URL is ready! */
		return $scheme.'://'.$abs;
	}

	/**
	 * Method to trigger onEasySocialStreamBeforeRender and display the output if there is any.
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function beforeDisplayStreamItem($stream, $pos, $view = '')
	{
		$content = '';
		ESDispatcher::trigger('onEasySocialStreamBeforeRender', array(&$content, &$stream, $pos, $view));
		return $content;
	}


	/**
	 * Method to trigger onEasySocialStreamAfterRender and display the output if there is any.
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function afterDisplayStreamItem($stream, $pos, $view = '')
	{
		$content = '';
		ESDispatcher::trigger('onEasySocialStreamAfterRender', array(&$content, &$stream, $pos, $view));
		return $content;
	}
}
