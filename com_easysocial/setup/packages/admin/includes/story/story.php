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

require_once(__DIR__ . '/plugin.php');
require_once(__DIR__ . '/panel.php');
require_once(__DIR__ . '/attachment.php');

class SocialStory extends EasySocial
{
	private $story = null;
	public $id = null;
	public $moduleId = null;
	public $content = '';
	public $overlay = '';
	public $hashtags = array();
	public $mentions = array();

	public $mood = null;

	public $cluster = null;
	public $clusterType = null;

	public $showPostTo = false;

	public $requirePrivacy = true;
	public $requirePostAs = false;

	public $target   = null;
	public $targetType  = null;

	public $type = null;

	// Used to determine story form filter
	public $filter = null;

	// Metas
	public $attachments = array();
	public $panels = array();
	public $plugins = array();
	public $autoposts = array();

	// Mobile Metas
	public $panelsMain = array();
	public $panelsSecondary = array();

	public $anywhereId = null;
	public $hashtagEditable = true;

	// Custom params
	public $params = null;

	// Determine which panel should be listed first in mobile view
	public $priorityPanels = array('photos', 'videos');

	public $scheduledDate = null;

	public function __construct($type)
	{
		parent::__construct();

		$this->type = $type;
		$this->requirePrivacy = true;

		// Generate a unique id for the current stream object.
		$this->id = uniqid();
		$this->moduleId = 'story-' . $this->id;

		$this->params = new JRegistry();
	}

	public function setMentions($mentions)
	{
		$this->mentions = $mentions;
	}

	public function setMood($mood)
	{
		$this->mood = $mood;
	}

	/**
	 * Set the story scheduled date.
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function setScheduledDate($scheduledDate)
	{
		$this->scheduledDate = ES::date($scheduledDate);
	}

	/**
	 * Used to render the story form; whether this story form is for scheduled form.
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function isScheduling()
	{
		if (!is_null($this->filter) && $this->filter == 'scheduled') {
			return true;
		}

		return false;
	}

	/**
	 * Returns scheduled date.
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function getScheduled()
	{
		if (is_null($this->scheduledDate)) {
			$date = ES::date();

			// When the scheduled date is not available, set to 30 minutes.
			$date->modify("+30 minutes");

			$this->scheduledDate = $date;
		}

		// Check if the existing scheduled date is behind from today. #4807
		if ($this->scheduledDate) {
			$date = new DateTime($this->scheduledDate->toMySQL(true));
			$now = new DateTime();

			if ($date < $now) {
				$this->scheduledDate = ES::date();
				$this->scheduledDate->modify("+30 minutes");
			}
		}

		return $this->scheduledDate->toMySQL(true);
	}

	/**
	 * Set story filter.
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function setFilter($filter = null)
	{
		$this->filter = $filter;
	}

	/**
	 * Allows caller to specify the cluster this story belong.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function setCluster($clusterId, $clusterType)
	{
		$this->cluster = $clusterId;
		$this->clusterType = $clusterType;
	}

	public function isCluster()
	{
		return ($this->cluster) ? true : false;
	}

	public function getClusterId()
	{
		return $this->cluster;
	}

	public function getClusterType()
	{
		return $this->clusterType;
	}

	public function getCluster()
	{
		if (!$this->isCluster()) {
			return false;
		}

		$id = $this->getClusterId();
		$type = $this->getClusterType();

		$cluster = ES::cluster($type, $id);

		return $cluster;
	}

	public function showPrivacy($require = true)
	{
		$this->requirePrivacy = $require;
	}

	/**
	 * Determines if the story form requires privacy
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function requirePrivacy()
	{
		if (!$this->config->get('privacy.enabled')) {
			return false;
		}

		return $this->requirePrivacy;
	}

	/**
	 * Allows caller to determine if the story should allow posting to selection
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function showPostTo()
	{
		if (!$this->config->get('stream.quickposting.enabled')) {
			return false;
		}

		// Group, pages or events needs to be enbaled, otherwise it does not make sense to display the post to button
		if (!$this->config->get('groups.enabled') && !$this->config->get('pages.enabled') && !$this->config->get('events.enabled')) {
			return false;
		}

		if ($this->isScheduling()) {
			return false;
		}

		$this->showPostTo = true;
	}

	public function showPostAs($require = true)
	{
		$this->requirePostAs = $require;
	}

	public function requirePostAs()
	{
		return $this->requirePostAs;
	}

	/**
	 * Allows caller to specify the target id.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function setTarget($targetId, $targetType = SOCIAL_TYPE_USER)
	{
		$this->target = $targetId;
	}

	/**
	 * Allows caller to specify the anywhere id.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function setAnywhereId($anywhereId)
	{
		$this->anywhereId = $anywhereId;
	}

	/**
	 * Allows caller to specify additional params
	 *
	 * @since   2.0.15
	 * @access  public
	 */
	public function setParams($key, $value)
	{
		$this->params->set($key, $value);
	}

	/**
	 * Returns the anywhere id.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAnywhereId()
	{
		return $this->anywhereId;
	}

	/**
	 * Allows caller to specify the initial content
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function setContent($content='')
	{
		$this->content = $content;
	}

	/**
	 * Returns the mention form
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getMentionsForm($resetToDefault=false)
	{
		$theme = ES::themes();

		$tmp = array();

		$this->overlay = $this->content;

		if ($this->mentions) {

			// Store mentions temporarily to avoid escaping
			$i = 0;

			foreach ($this->mentions as $mention) {

				if ($mention->utype == 'user') {
					$user       = ES::user($mention->uid);
					$replace    = '<span>' . $user->getName() . '</span>';
				}

				if ($mention->utype == 'hashtag') {
					$replace    = '<span>' . "#" . $mention->title . '</span>';
				}

				if ($mention->utype == 'emoticon') {
					$replace    = '<span>' . ":" . $mention->title . '</span>';
				}

				$tmp[$i]        = $replace;

				$replace        = '[si:mentions]' . $i . '[/si:mentions]';
				$this->overlay  = ESJString::substr_replace($this->overlay, $replace, $mention->offset, $mention->length);

				$i++;
			}
		}

		$this->overlay  = ES::string()->escape($this->overlay);

		for ($x = 0; $x < count($tmp); $x++) {
			$this->overlay  = str_ireplace('[si:mentions]' . $x . '[/si:mentions]', $tmp[$x], $this->overlay);
		}

		$theme->set('story', $this);
		$theme->set('defaultOverlay', $resetToDefault ? $story->overlay : '');
		$theme->set('defaultContent', $resetToDefault ? $story->content : '');

		$contents   = $theme->output('site/mentions/form');

		return $contents;
	}

	public function setHashtags($tags=array(), $editable = true)
	{
		if (count($tags) < 1) return;

		$content = '#' . implode(' #', $tags);
		$overlay = '<span>#' . implode('</span> <span>#', $tags) . '</span>';

		$overlay .= ' <br/>';
		$content .= " \r\n";

		$this->content = ' ' . $content;
		$this->overlay = ' ' . $overlay;
		$this->hashtags = $tags;
		$this->hashtagEditable = $editable;
	}

	/**
	 * Returns the target id.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * Retrieves a list of colors to be used on the story form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getColors()
	{
		static $colors = array();

		if (!$colors) {
			$colors = array(
				'#0D47A1',
				'#4A148C',
				'#1A237E',
				'#1B5E20',
				'#DD2C00',
				'#37474F',
				'#424242',
				'#E65100',
				'#004D40',
				'#0091EA',
				'#01579B'
			);
		}

		return $colors;
	}

	/**
	 * Creates a new stream item
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function create($args = array())
	{
		// The content of the story
		$content = isset($args['content']) ? $args['content'] : '';

		// Context ids, and type that are related to the story
		$contextIds = isset($args['contextIds']) ? $args['contextIds'] : '';
		$contextType = isset($args['contextType']) ? $args['contextType'] : '';

		// The person that created this new story
		$actorId = isset($args['actorId']) ? $args['actorId'] : '';

		// If the object is posting on another object, the target object id should be passed in here.
		$targetId = isset($args['targetId']) ? $args['targetId'] : null;

		// If the story is associated with a location, it should be processed.
		$location = isset($args['location']) ? $args['location'] : null;

		// If the story is being tagged with other users.
		$with = isset($args['with']) ? $args['with'] : null;

		// If the content of the story contains mentions using @ and # tags.
		$mentions = isset($args['mentions']) ? $args['mentions'] : array();

		// If the story belongs in a cluster
		$cluster = isset($args['cluster']) ? $args['cluster'] : '';
		$clusterType = isset($args['clusterType']) ? $args['clusterType'] : SOCIAL_TYPE_GROUP;

		// Check for the post actor. Only applicable to Page
		$postActor = isset($args['postActor']) ? $args['postActor'] : null;

		// This is special for Stream Anywhere Module
		$anywhereId = isset($args['anywhereId']) ? $args['anywhereId'] : null;
		$pageTitle = isset($args['pageTitle']) ? $args['pageTitle'] : null;

		// If the story contains a mood
		$mood = isset($args['mood']) ? $args['mood'] : null;

		// Background
		$backgroundId = ES::normalize($args, 'backgroundId', 0);

		// Store this into the stream now.
		$stream = ES::stream();

		// Set the scheduled.
		// Value: 0000-00-00 00:00:00 / false
		$scheduled = ES::normalize($args, 'scheduled', false);
		$stream->scheduled = $scheduled;

		// Ensure that context ids are always array
		if (!is_array($contextIds)) {
			$contextIds = array($contextIds);
		}

		// Determines which trigger group to call
		$group = $cluster ? $clusterType : SOCIAL_TYPE_USER;

		// Load apps
		ES::apps()->load($group);

		// Load up the dispatcher so that we can trigger this.
		$dispatcher = ES::dispatcher();

		// This is to satisfy the setContext method.
		$contextId = isset($contextIds[0]) ? $contextIds[0] : 0;

		// Get the stream template
		$template = $stream->getTemplate();

		$item_params = null;

		// Set the post actor
		if (!is_null($postActor)) {
			$template->setPostAs($postActor);
		}

		// Set the anywhere Id
		if (!empty($anywhereId)) {
			$template->setCurrentUrl($anywhereId);

			// We need to store the page title as well.
			$params = new stdClass;
			$params->pageTitle = !empty($pageTitle) ? $pageTitle : $this->doc->getTitle();;
			$template->setParams($params);
		}

		$contextParams = array();

		// If giphy data is being sent, we should store it as context params
		if (isset($args['giphy'])) {
			$contextParams = $args['giphy'];
		}

		$template->setActor($actorId, $this->type);
		$template->setContext($contextId, $contextType, $contextParams);
		$template->setContent($content);

		$verb = ($contextType == 'photos') ? 'share' : 'create';

		$template->setVerb($verb);

		$privacyRule = isset($args['privacyRule']) ? $args['privacyRule'] : null;
		$privacyValue = isset($args['privacyValue']) ? $args['privacyValue'] : null;
		$privacyCustom = isset($args['privacyCustom']) ? $args['privacyCustom'] : null;
		$privacyField = isset($args['privacyField']) ? $args['privacyField'] : null;

		if (!$privacyRule) {
			$privacyRule = 'story.view';

			if ($contextType == 'photos') {
				$privacyRule = 'photos.view';
			} else if ($contextType == 'polls') {
				$privacyRule = 'polls.view';
			} else if ($contextType == 'videos') {
				$privacyRule = 'videos.view';
			}
		}

		if ($privacyValue && is_string($privacyValue) ) {
			$privacyValue = ES::privacy()->toValue($privacyValue);
		}

		if ($privacyCustom) {
			$privacyCustom = explode( ',', $privacyCustom );
		}

		if ($privacyField) {
			$privacyField = explode( ';', $privacyField );
		}

		$template->setBackground($backgroundId);

		// Set this stream to be public
		$template->setAccess($privacyRule, $privacyValue, $privacyCustom, $privacyField);

		// Set mentions
		$template->setMentions($mentions);

		// Set the users tagged in the  stream.
		$template->setWith($with);

		// Set the location of the stream
		$template->setLocation($location);

		if (isset($args['params'])) {
			$registry = new JRegistry($args['params']);
			$template->setParams($registry);
		}

		// Set the mood
		if (!is_null($mood)) {
			$template->setMood($mood);
		}

		// If there's a target, we want it to appear on their stream too
		if ($targetId) {
			$template->setTarget($targetId);
		}

		if ($contextType == 'photos') {

			if (count($contextIds) > 0) {
				foreach ($contextIds as $photoId) {
					$template->setChild($photoId);
				}
			}
		}

		if ($cluster) {

			$clusterObj = ES::cluster($clusterType, $cluster);

			if ($clusterObj) {

				// Set the params to cache the group data
				$registry = ES::registry();
				$registry->set($clusterType, $clusterObj);

				// Set the params to cache the group data
				$template->setParams($registry);

				$template->setCluster($cluster, $clusterType, $clusterObj->type);
			} else {
				$template->setCluster($cluster, $clusterType, 1);
			}
		}

		// Build the arguments for the trigger
		$args = array(&$template, &$stream, &$content);

		// @trigger onBeforeStorySave
		$dispatcher->trigger($group, 'onBeforeStorySave', $args);

		// Create the new stream item.
		$streamItem = $stream->add($template);

		// Store link items
		$this->storeLinks($stream, $streamItem, $template);

		// Set the notification type
		$notificationType = SOCIAL_TYPE_STORY;

		// Construct our new arguments
		$args = array(&$stream, &$streamItem, &$template);

		// @trigger onAfterStorySave
		$dispatcher->trigger($group, 'onAfterStorySave', $args);

		// Send a notification to the recipient if needed.
		if ($targetId && $actorId != $targetId) {
			$this->notify($targetId, $streamItem, $template->content, $contextIds, $contextType , $notificationType);
		}

		// need to pass cluster object into notifyMention for future processing
		$clusterObj = false;
		if ($cluster) {
			$clusterObj = ES::cluster($clusterType, $cluster);
		}

		// Send a notification alert if there are mentions
		if ($mentions && !empty($mentions)) {
			$this->notifyMentions($streamItem, $mentions, $contextType, $contextIds , $template->content , $targetId, $clusterObj, $stream->scheduled);
		}

		// notify cluster if this stream is under moderation.
		if ($cluster && ($clusterType == SOCIAL_TYPE_GROUP || $clusterType == SOCIAL_TYPE_PAGE)) {

			$clusterObj = ES::cluster($clusterType, $cluster);

			if ($clusterObj) {

				$clusterParams = $clusterObj->getParams();

				$moderation = $clusterParams->get('stream_moderation', false);
				$moderationNoti = $clusterParams->get('stream_moderation_notification', false);

				if ($moderation && $moderationNoti) {
					$emaildata = array(
						'userId' => $streamItem->actor_id,
						'content' => $template->content,
						'title' => 'COM_EASYSOCIAL_EMAILS_STREAM_NEW_POST_MODERATION_TITLE',
						'template' => 'site/stream/post.moderation',
						'uid' => $streamItem->uid
						);
					$clusterObj->notifyAdminsModeration($emaildata);
				}
			}
		}


		// Update user social goals progress
		ES::user($actorId)->updateGoals('poststatus');

		$access = $this->my->getAccess();
		if ($access->get('story.flood.user')) {
			ES::user($actorId)->updateLastStoryTime();
		}

		return $streamItem;
	}

	/**
	 * Stores any link assets
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function storeLinks( $stream , $streamItem , $template )
	{
		// Get the link information from the request
		$link = $this->app->input->get('links_url', '', 'default');
		$title = $this->app->input->get('links_title', '', 'default');
		$content = $this->app->input->get('links_description', '', 'default');
		$image = $this->app->input->get('links_image', '', 'default');

		// If there's no data, we don't need to store in the assets table.
		if (empty($title) && empty($content) && empty($image)) {
			return false;
		}

		// Cache the image if necessary
		$links = ES::links();
		$fileName = $links->cacheImage($image);

		$link = ES::string()->escape($link);

		$registry = ES::registry();
		$registry->set('title', $title);
		$registry->set('content', $content);
		$registry->set('image', $image);
		$registry->set('link', $link);
		$registry->set('cached', false);

		// Image link should only be modified when the file exists
		if ($fileName !== false) {
			$registry->set('cached', true);
			$registry->set('image', $fileName);
		}

		// Store the link object into the assets table
		$assets = ES::table('StreamAsset');
		$assets->stream_id = $streamItem->uid;
		$assets->type = 'links';
		$assets->data = $registry->toString();

		// Store the assets
		$state = $assets->store();

		return $state;
	}

	/**
	 * Notify users that is mentioned in the story
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function notifyMentions( $stream , $mentions , $contextType , $contextIds , $content , $targetId, $cluster = false, $scheduled = false)
	{
		$recipients = array();

		if (!$mentions) {
			return;
		}

		foreach ($mentions as $mention) {

			if (!isset($mention->type)) {
				continue;
			}

			// Only process items with users tagging since we only want to notify users.
			if ($mention->type != 'entity'){
				continue;
			}

			$parts = explode(':', $mention->value);

			if (count($parts) != 2) {
				continue;
			}

			$type = $parts[0];
			$id = $parts[1];

			$doAdd = true;
			if ($cluster !== false) {
				if (!$cluster->canViewItem($id)) {
					$doAdd = false;
				}
			}

			if ($doAdd) {
				$recipients[] = ES::user($id);
			}
		}

		// if no one to notify, stop here.
		if (!$recipients) {
			return;
		}

		$actor = ES::user($stream->actor_id);

		// Add notification to the requester that the user accepted his friend request.
		$state = null;

		foreach ($recipients as $recipient) {

			// If the recipient is being mentioned in a post that is posted on their own stream, we shouldn't need to notify them again.
			if ($recipient->id == $targetId) {
				continue;
			}

			// $stream defined as SocialTableStreamItem class
			// $stream->id = #__social_stream_item id
			// $stream->uid = #__social_stream_item uid = #__social_stream id

			// Set the email options
			$emailOptions   = array(
				'title' => 'COM_EASYSOCIAL_EMAILS_USER_MENTIONED_YOU_IN_A_POST_SUBJECT',
				'template' => 'site/profile/post.mentions',
				'permalink' => $stream->getPermalink(false, true),
				'actor' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(SOCIAL_AVATAR_SQUARE),
				'actorLink' => $actor->getPermalink(true, true),
				'message' => $content,
				'sid' => $stream->uid
			);

			$systemOptions  = array(
				'uid' => $stream->id,
				'context_type' => $contextType,
				'context_ids' => ES::json()->encode($contextIds),
				'type' => SOCIAL_TYPE_STORY,
				'url' => $stream->getPermalink(false, false, false),
				'actor_id' => $actor->id,
				'target_id' => $recipient->id,
				'aggregate' => true,
				'content' => $content,
				'sid' => $stream->uid
			);

			// Added scheduled params for notification processing.
			if ($scheduled) {
				$emailOptions['scheduled'] = $scheduled;
				$systemOptions['scheduled'] = $scheduled;

				// Do not aggregate scheduled post.
				$systemOptions['aggregate'] = false;
			}

			// Send notification to the target
			$state = ES::notify('stream.tagged', array($recipient->id), $emailOptions, $systemOptions);
		}

		return $state;
	}

	/**
	 * Notifies a user when someone posted something on their timeline
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function notify($id, $stream, $content, $contextIds, $contextType, $notificationType)
	{
		$recipient = ES::user($id);
		$actor = ES::user($stream->actor_id);

		$systemOptions = array(
			// The unique node id here is the #__social_friend id.
			'uid' => $stream->id,
			// Used by scheduled post.
			'sid' => $stream->id,
			'content' => $content,
			'actor_id' => $actor->id,
			'target_id' => $recipient->id,
			'context_ids' => ES::json()->encode( $contextIds ),
			'context_type' => 'post.user.timeline',
			'type' => $notificationType,
			'url' => $stream->getPermalink(false, false, false)
		);

		// We should parse emoticons for email content
		$parseBBCodeOptions = array('escape' => false, 'links' => true, 'code' => true);
		$content = ES::string()->normalizeContent($content, $parseBBCodeOptions);

		$emailOptions = array(
			'title' => 'COM_EASYSOCIAL_EMAILS_USER_POSTED_ON_YOUR_TIMELINE_SUBJECT',
			'template' => 'site/profile/post.story',
			'params' => array(
				'actor' => $actor->getName(),
				'actorAvatar' => $actor->getAvatar(),
				'actorLink' => $actor->getPermalink(true, true),
				'permalink' => $stream->getPermalink(false, true),
				'content' => $content
				),
			// Used by scheduled post.
			'sid' => $stream->id,
		);

		// Added scheduled params for notification processing.
		if (isset($stream->scheduled) && $stream->scheduled) {
			$emailOptions['scheduled'] = $stream->scheduled;
			$systemOptions['scheduled'] = $stream->scheduled;
		}

		$state = ES::notify('profile.story', array($recipient->id), $emailOptions, $systemOptions);

		return $state;
	}

	/**
	 * Object initialisation for the class to fetch the appropriate user
	 * object.
	 *
	 * @since   1.0
	 * @access  public
	 * @param   null
	 * @return  SocialStream    The stream object.
	 */
	public static function factory( $type )
	{
		return new self( $type );
	}

	/**
	 * Get's a template object for story.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function createPlugin($name, $type='plugin')
	{
		$pluginClass = 'SocialStory' . ucfirst($type);

		$plugin = new $pluginClass($name, $this);

		return $plugin;
	}

	/**
	 * Retrieve available panels on the site
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAvailablePanels()
	{
		// Prepare the panels
		$this->prepare();

		$panels = array();

		// Default panel
		$obj = new stdClass();
		$obj->title = JText::_('COM_EASYSOCIAL_STORY_STATUS');
		$obj->type = 'text';
		$obj->className = 'far fa-edit';

		$panels[] = $obj;

		if ($this->panels) {
			foreach ($this->panels as $panel) {

				$obj = new stdClass();
				$obj->title = $panel->title;
				$obj->type = $panel->name;
				$obj->className = $panel->getButtonClassname();

				$panels[] = $obj;
			}
		}

		return $panels;
	}

	/**
	 * Trigger to prepare the story item before being output.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function prepare($panelType = null)
	{
		// Load up the necessary apps
		ES::apps()->load($this->type);

		// Pass arguments by reference.
		$args = array(&$this);

		// Only go through dispatcher when there is some apps loaded, otherwise it's pointless.
		$dispatcher = ES::dispatcher();

		// StoryAttachment service
		$panels = $dispatcher->trigger($this->type, 'onPrepareStoryPanel', $args);

		if ($panels) {

			foreach ($panels as $panel) {

				if ($panel === false) {
					continue;
				}

				if ($panel instanceof SocialStoryPanel) {

					// Check for specific panel
					if ($panelType) {
						if ($panel->name == $panelType) {
							$this->panels = array($panel);
							$this->plugins = array($panel);
						}
					} else {
						if (in_array($panel->name, $this->priorityPanels)) {
							$this->panelsMain[] = $panel;
						} else {
							$this->panelsSecondary[] = $panel;
						}

						$this->plugins[] = $panel;
					}
				}
			}
		}

		$totalMainPanels = count($this->panelsMain);
		$totalPriorityPanels = count($this->priorityPanels);

		// In case they disabled photos and videos applications, we need to replace it with another menus
		if ($totalMainPanels < $totalPriorityPanels) {
			foreach ($this->panelsSecondary as $key => $mobilePanel) {
				$this->panelsMain[] = $mobilePanel;
				unset($this->panelsSecondary[$key]);

				$totalMainPanels++;

				if ($totalMainPanels >= $totalPriorityPanels) {
					break;
				}
			}
		}

		// Merge panels
		if (!$this->panels) {
			$this->panels = array_merge($this->panelsMain, $this->panelsSecondary);
		}

		$autoposts = $dispatcher->trigger($this->type, 'onPrepareStoryAutopost', $args);

		if ($autoposts) {
			foreach ($autoposts as $autopost) {
				if ($autopost === false || $autopost == null) {
					continue;
				}

				$this->autoposts[] = $autopost;
			}
		}

		return true;
	}

	/**
	 * Check if user are allow to view or post anything via story form
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function checkAccess()
	{
		if (!$this->my->canPostClusterStory($this->clusterType, $this->cluster)) {
			return false;
		}

		if (!$this->my->canPostStory() && !$this->clusterType) {
			return false;
		}

		// Let's test if the current viewer is allowed to view this profile.
		if ($this->requirePrivacy()) {
			if ($this->target && $this->my->id != $this->target) {

				$privacy = $this->my->getPrivacy();
				$state = $privacy->validate('profiles.post.status', $this->target, SOCIAL_TYPE_USER);

				if (!$state) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Determine if user viewing others user's timeline
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function viewedUser($targetId = null)
	{
		if (!is_null($targetId)) {
			return ES::user($targetId);
		}

		if (is_null($this->target) || $this->isCluster()) {
			return false;
		}

		return ES::user($this->target);
	}

	/**
	 * Renders the html output for the story form
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function html($fromModule = false, $panelType = false, $options = array())
	{
		// Check if user has access to story form
		if (!$this->checkAccess()) {
			return;
		}

		$showClusterAvatar = $this->normalize($options, 'showClusterAvatar', false);
		$singlePanel = false;

		if ($panelType) {
			$singlePanel = true;
		}

		// Prepare the story form
		$this->prepare($panelType);

		// Determines if the story form should be expanded by default.
		$expanded = false;

		if (!empty($this->content) || $fromModule) {
			$expanded = true;
		}

		// Get moods
		$gender = $this->my->getGenderLang();
		$moods = $this->getMoods($gender);

		// Get a list of custom params
		$customParams = $this->params->toArray();

		// determine whether need to show the dropdown icon on mobile mode.
		$appExists = count($this->panels) <= 2 ? true : false;

		// Process the story form placeholder text
		$placeholderText = JText::_('COM_EASYSOCIAL_STORY_PLACEHOLDER');

		$user = $this->viewedUser();

		// If the user is viewing other's profile
		// Change to a proper placeholder text
		if ($user && !$user->isViewer()) {
			$placeholderText = JText::sprintf('COM_ES_STORY_PLACEHOLDER_USER', $user->getName());
		}

		$submitBtnText = JText::_("COM_EASYSOCIAL_STORY_SHARE");

		// Determine whether this story form is a scheduled form.
		if ($this->isScheduling()) {
			$submitBtnText = JText::_("COM_ES_STORY_SCHEDULE");
		}

		// Determines which plugins should be visible
		if ($this->panels) {
			$i = 1;
			$initialLimit = $this->config->get('stream.story.limit');

			// Get user params
			$userStoryPreferences = $this->my->getUserPreferences();

			$limitPanels = $this->config->get('stream.story.enablelimits');

			// If user preferences exists, we assume that we should respect what they have configured
			if ($userStoryPreferences && $this->config->get('stream.story.favourite')) {
				$tmpPanels = array();
				$tmp = array();

				foreach ($this->panels as $panel) {
					$tmp[$panel->name] = $panel;
					$tmp[$panel->name]->visible = false;

					// When panel limits is disabled, we shouldn't hide anything
					if (!$limitPanels) {
						$tmp[$panel->name]->visible = true;
					}
				}

				foreach ($userStoryPreferences as $panelName) {
					if (isset($tmp[$panelName])) {
						$tmp[$panelName]->visible = true;
						$tmpPanels[] = $tmp[$panelName];
					}
				}

				foreach ($this->panels as $panel) {
					if (!in_array($panel->name, $userStoryPreferences)) {
						$tmpPanels[] = $panel;
					}
				}

				$this->panels = $tmpPanels;
			}

			if (!$userStoryPreferences || !$this->config->get('stream.story.favourite')) {
				$visiblePanels = array();
				$userStoryPreferencesExists = $this->my->isStoryPreferencesExists();

				foreach ($this->panels as &$panel) {
					$panel->index = $i;
					$panel->visible = false;

					// Nothing is stored yet. Let's create a default preference for user.
					if (!$userStoryPreferencesExists) {

						if (!$limitPanels) {
							$panel->visible = true;
						}

						if ($limitPanels && $i < $initialLimit) {
							$panel->visible = true;
							$visiblePanels[] = $panel->name;
						}
					}

					$i++;
				}

				// Store initial default preference for user
				if ($limitPanels && !$userStoryPreferences && $this->config->get('stream.story.favourite')) {
					$this->my->addFavouriteStory($visiblePanels);
				}
			}
		}


		$modelEmoticon = ES::model('Emoticons');
		$emoticons = $modelEmoticon->getJsonEmoticons();

		$model = ES::model('Background');
		$presets = $model->getPresetBackgrounds();

		// Determines if we should be showing the cluster's avatar instead of the user's avatar
		$cluster = null;

		// Default actor is the logged in user
		$actor = $this->my;

		if ($this->cluster) {
			$session = JFactory::getSession();
			$viewAs = $session->get('easysocial.viewas', 'user', SOCIAL_SESSION_NAMESPACE);
			$viewAs = $this->input->get('viewas', $viewAs, 'string');
			$view = $this->input->get('view', 'dashboard', 'string');

			// if this is page story form, there is possiblity to post as page
			if ($view == 'pages' && $viewAs == SOCIAL_TYPE_PAGE) {
				$actor = $this->getCluster();
			}

			if ($showClusterAvatar) {
				$cluster = $this->getCluster();
			}
		}

		// Determines if we should show the post to selection
		$showPostTo = $this->showPostTo;

		$theme = ES::themes();
		$theme->set('showPostTo', $showPostTo);
		$theme->set('cluster', $cluster);
		$theme->set('showClusterAvatar', $showClusterAvatar);
		$theme->set('presets', $presets);
		$theme->set('customParams', $customParams);
		$theme->set('moods', $moods);
		$theme->set('expanded', $expanded);
		$theme->set('story', $this);
		$theme->set('fromModule', $fromModule);
		$theme->set('appExists', $appExists);
		$theme->set('singlePanel', $singlePanel);
		$theme->set('panelType', $panelType);
		$theme->set('placeholderText', $placeholderText);
		$theme->set('emoticons', $emoticons);
		$theme->set('submitBtnText', $submitBtnText);
		$theme->set('actor', $actor);

		$output = $theme->output('site/story/default');

		return $output;
	}

	/**
	 * Renders the html output for the story form
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function editForm($resetToDefault=false, $streamId = null)
	{
		if (is_null($streamId)) {
			return;
		}

		// Load the stream
		$stream = ES::table('Stream');
		$stream->load($streamId);

		$currentLocation = false;

		// Get the location fir this stream
		if ($stream->location_id) {
			$currentLocation = ES::table('location');
			$currentLocation->load($stream->location_id);
		}

		// Get the current mood if any
		$currentMood = false;

		// Get the mood for this stream
		if ($stream->mood_id) {
			$currentMood = ES::table('Mood');
			$currentMood->load($stream->mood_id);
		}

		// If this stream is a scheduled stream, we'll need to set the filter.
		// We'll need this to display the scheduled date picker.
		if ($stream->state == SOCIAL_STATE_SCHEDULED) {
			$this->filter = 'scheduled';

			$schTable = ES::table('StreamScheduled');
			$schTable->load(array('stream_id' => $streamId));

			$this->scheduledDate = ES::date($schTable->scheduled);
		}

		$tmp = array();

		$this->overlay = $this->content;

		$mentionedUsers = false;
		$mentions = false;

		if ($this->mentions) {

			// Store mentions temporarily to avoid escaping
			$i = 0;
			$replace = '';

			$nextOffset = 0;

			$sortedMentions = array();

			$iterator = 0;
			foreach ($this->mentions as $mention) {
				if ($iterator == 0) {
					$sortedMentions[] = $mention;
				} else {

					$curIdx = count($sortedMentions) - 1;

					if ($mention->offset > $sortedMentions[$curIdx]->offset) {
						array_push($sortedMentions, $mention);

					} else {
						array_unshift($sortedMentions, $mention);
					}
				}

				$iterator++;
			}

			$this->mentions = $sortedMentions;
			$mentionedUsers = array();

			foreach ($this->mentions as $mention) {

				$text = '';

				if ($mention->utype == 'user') {
					$user = ES::user($mention->uid);

					if ($mention->with != 1) {
						$replace = '<span>' . $user->getName() . '</span>';
						$text = $user->getName();
						$mentions[] = $mention;
					} else {
						$mentionedUsers[] = $user;
					}
				}

				if ($mention->utype == 'hashtag') {
					$text = '#' . $mention->title;
					$replace = '<span>' . "#" . $mention->title . '</span>';
					$mentions[] = $mention;
				}

				if ($mention->utype == 'emoticon') {
					$text = ':' . $mention->title;
					$replace = '<span>' . ":(" . $mention->title . ')</span>';
					$mentions[] = $mention;
				}

				if ($mention->with != 1) {
					$tmp[$i] = $replace;

					$replace = '[si:mentions]' . $i . '[/si:mentions]';

					$this->overlay = ESJString::substr_replace($this->overlay, $replace, $mention->offset + $nextOffset, $mention->length);

					// we need to factor in the extra characters ':(' if it is emoticon;
					$extraCharsLen = 0;
					if ($mention->utype == 'emoticon') {
						$extraCharsLen = 2;
					}

					$nextOffset += strlen($replace) - (strlen($text) + $extraCharsLen);

					$i++;
				}
			}
		}

		$this->overlay  = ES::string()->escape($this->overlay);

		for ($x = 0; $x < count($tmp); $x++) {
			$this->overlay  = str_ireplace('[si:mentions]' . $x . '[/si:mentions]', $tmp[$x], $this->overlay);
		}

		// Prepare the story form
		$this->prepare();

		// Expended will always be true since we are editing
		$expanded = true;

		// Get moods
		$gender = $this->my->getGenderLang();
		$moods = $this->getMoods($gender);

		// Process the story form placeholder text
		$placeholderText = JText::_('COM_EASYSOCIAL_STORY_PLACEHOLDER');

		if (!$stream->isCluster()) {
			$user = $this->viewedUser($stream->target_id);

			// If the user is viewing other's profile
			// Change to a proper placeholder text
			if ($user && $user->id && !$user->isViewer()) {
				$placeholderText = JText::sprintf('COM_ES_STORY_PLACEHOLDER_USER', $user->getName());
			}
		}

		$model = ES::model('Background');
		$presets = $model->getPresetBackgrounds();

		$modelEmoticon = ES::model('Emoticons');
		$emoticons = $modelEmoticon->getJsonEmoticons();

		$currentBackground = $stream->background_id;

		// Get the current giphy if there is
		$streamItemTable = ES::table('StreamItem');
		$streamItemTable->load(array('uid' => $streamId));

		$theme = ES::themes();
		$theme->set('currentBackground', $currentBackground);
		$theme->set('presets', $presets);
		$theme->set('moods', $moods);
		$theme->set('expanded', $expanded);
		$theme->set('story', $this);
		$theme->set('streamId', $streamId);
		$theme->set('mentionedUsers', $mentionedUsers);
		$theme->set('mentions', $mentions);
		$theme->set('currentLocation', $currentLocation);
		$theme->set('currentMood', $currentMood);
		$theme->set('defaultOverlay', $resetToDefault ? $story->overlay : '');
		$theme->set('defaultContent', $resetToDefault ? $story->content : '');
		$theme->set('placeholderText', $placeholderText);
		$theme->set('emoticons', $emoticons);
		$theme->set('streamItemTable', $streamItemTable);
		$theme->set('contextType', $stream->context_type);
		$output = $theme->output('site/story/edit/default');

		return $output;
	}

	/**
	 * Get a list of preset moods
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getMoods($gender="_NOGENDER")
	{
		$moods = array();
		// @TODO: In the future, we could scan for moods
		$verbs = array('feeling');

		foreach ($verbs as $verb) {

			$file = dirname(__FILE__) . '/moods/' . $verb . '.mood';
			$verb = ES::makeObject($file);

			// Apppend gender suffix to language keys
			foreach ($verb->moods as $mood) {
				$mood->text    .= $gender;
				$mood->subject .= $gender;
			}

			$moods[$verb->key] = $verb;
		}

		return $moods;
	}

	/**
	 * Get's the content in json form.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function json()
	{
		$json = ES::json();
		$obj = (object) $this->story;

		$output = $json->encode($obj);

		return $output;
	}
}
