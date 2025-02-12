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

class SocialStreamItem extends EasySocial
{
	public $uid = null;
	public $display = null;

	public $actor_id = null;
	public $alias = null;

	// app that associated with this stream
	public $appid = null;

	public $post_as = null;
	public $anywhere_id = null;

	public $background_id = null;

	// Content
	public $title = null;
	public $content = '';
	public $content_raw = null;
	public $preview = null;

	// Dates
	public $created = null;
	public $modified = null;
	public $lapsed = null;
	public $friendlyTS = null;
	public $friendlyDate = null;
	public $scheduled = null;

	// States
	public $isNew = null;
	public $state = null;

	// Permissions
	public $deleteable = false;
	public $editable = false;

	// Only polls app can determines if the stream item can edit or not the poll item.
	public $editablepoll = false;

	// used by any other app steam that do not support stream editing.
	public $edit_link = false;

	// Attributes
	public $color = '';
	public $icon = '';
	public $label = true;
	public $custom_label = '';
	public $type = null;
	public $favicon;

	// Actions
	public $comments = true;
	public $likes = true;
	public $repost = true;
	public $sharing = true;

	// Meta
	public $bookmarked = false;
	public $sticky = false;
	public $mood = false;
	public $with = array();
	public $tags = array();
	public $location = null;

	// Last action performed on the stream
	public $lastaction = '';
	public $last_action = false;
	public $last_userid = null;
	public $last_action_date = null;

	// Privacy
	public $privacy = null;
	public $access = null;
	public $custom_access = null;

	//cluster
	public $cluster_id = null;
	public $cluster_type = null;
	public $cluster_access = null;

	public $input = null;
	public $view = null;
	public $og = null;

	public $perspective = null;
	public $giphy = null;
	public $edited = null;

	public $meta = null;
	public $actor = null;
	public $context = null;
	public $aggregatedItems = null;
	public $actors = null;
	public $targets = null;
	public $contextParams = null;
	public $contextId = null;
	public $contextIds = null;
	public $verb = null;
	public $targetUser = null;
	public $actions = null;
	public $params = [];
	public $commentLink = null;
	

	public function __construct($options = array())
	{
		parent::__construct();

		static $opengraph = null;

		// We should only allow stream items to add opengraph description
		// on stream item pages
		$this->view = $this->input->get('view', '', 'cmd');

		// Allow caller to override the perspective
		if (isset($options['perspective']) && $options['perspective']) {
			$this->view = $options['perspective'];
		}

		$this->meta = ES::meta();
	}

	/**
	 * Given an array / object, map it to itself
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bind($data = array())
	{
		// Ensure that it is an object
		if (is_array($data)) {
			$data = (object) $data;
		}

		// Get a list of bindable data
		$bindable = array('isNew', 'state', 'edited', 'content', 'title', 'params', 'cluster_id', 'cluster_type', 'cluster_access',
						'access', 'custom_access', 'bookmarked', 'sticky', 'modified', 'last_action', 'last_userid', 'last_action_date', 'post_as', 'anywhere_id', 'background_id');

		foreach ($bindable as $key) {
			if (isset($data->$key)) {
				$this->$key = $data->$key;
			}
		}

		// Set the content
		$this->content = $data->content;
		$this->content_raw = $data->content;

		// Set the stream uid / activity id.
		$this->uid = $data->id;

		// Set stream lapsed time
		$this->lapsed = ES::date($data->created)->toLapsed();
		$this->friendlyDate = $this->lapsed;

		$this->created = ES::date($data->created);
		$this->modified = ES::date($data->modified);

		// Formatting scheduled date for displaying.
		if ($this->isScheduled()) {
			$timeformat = ($this->config->get('stream.scheduled.timeformat', '24') == '24') ? 'COM_EASYSOCIAL_DATE_DMY24H' : 'COM_EASYSOCIAL_DATE_DMY12H';
			$this->scheduled = ES::date($data->scheduled, true)->toFormat($timeformat);
		}

		$templateConfig = ES::themes()->getConfig();

		// Set the actor with the user object.
		$this->actor = ES::user($data->actor_id);

		// Set the context type.
		$this->context  = $data->context_type;

		// stream display type
		$this->display = $data->stream_type;

		// Format the mood
		if (isset($data->mood_id) && $data->mood_id) {
			$this->mood = $this->bindMood($data);
		}

		// Format the users that are tagged in this stream.
		if (isset($data->location_id) && $data->location_id) {
			$this->location = $this->bindLocation($data);
		}

		// Since our stream has a unique favi on for each item. We need to get it here.
		// Each application is responsible to override this favicon, or stream wil use the context type.
		$this->favicon = $data->context_type;
		$this->type = $data->context_type;
	}

	/**
	 * Determine if the stream type is for avatar photo
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAvatarStream()
	{
		return $this->context == 'photos' && $this->verb == 'uploadAvatar';
	}

	/**
	 * Determine if the stream type is for cover photo
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isCoverStream()
	{
		return $this->context == 'photos' && $this->verb == 'updateCover';
	}

	/**
	 * Determines if the stream item is posted in a cluster
	 *
	 * @since	1.3.8
	 * @access	public
	 */
	public function isCluster()
	{
		return $this->cluster_id > 0;
	}

	/**
	 * Get the app group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getGroup()
	{
		$group = SOCIAL_APPS_GROUP_USER;

		if (!$this->isCluster()) {
			return $group;
		}

		$group = $this->getCluster()->getType();

		return $group;
	}

	/**
	 * Retrieves the giphy url associated with the stream
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getGiphy()
	{
		$contextParams = $this->getContextParams();
		$giphy = $contextParams->get('giphy', false);

		if ($giphy) {
			return $giphy;
		}

		return false;
	}

	/**
	 * Retrieves the cluster object
	 *
	 * @since	1.3.8
	 * @access	public
	 */
	public function getCluster()
	{
		if (!$this->isCluster()) {
			return false;
		}

		// Get the cluster object
		$cluster = ES::cluster($this->cluster_type, $this->cluster_id);

		return $cluster;
	}

	/**
	 * Allow caller to set a custom actor alias
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setActorAlias($object)
	{
		$this->alias = $object;
	}

	/**
	 * Sets the likes on the stream
	 *
	 * @since	1.3.8
	 * @access	public
	 */
	public function setLikes($group, $useStreamId, $uid = null, $context = null, $verb = null)
	{
		$uid = is_null($uid) ? $this->uid : $uid;
		$context = is_null($context) ? $this->context : $context;
		$verb = is_null($verb) ? $this->verb : $verb;

		$likes = ES::likes();
		$likes->get($uid, $context, $verb, $group, $useStreamId);

		$this->likes = $likes;
	}

	/**
	 * Sets the comments on the stream
	 *
	 * @since	1.3.8
	 * @access	public
	 */
	public function setComments($group, $useStreamId, $options = array(), $uid = null, $context = null, $verb = null)
	{
		$uid = is_null($uid) ? $this->uid : $uid;
		$context = is_null($context) ? $this->context : $context;
		$verb = is_null($verb) ? $this->verb : $verb;

		// Retrieve the comments object
		$comments = ES::comments($uid, $context, $verb, $group, $options, $useStreamId);
		$this->comments = $comments;
	}

	/**
	 * Sets the repost on the stream
	 *
	 * @since	1.3.8
	 * @access	public
	 */
	public function setRepost($group, $element, $uid = null)
	{
		$uid = is_null($uid) ? $this->uid : $uid;

		// Get the repost object
		$repost = ES::get('Repost', $this->uid, $element, $group);
		$this->repost = $repost;
	}

	/**
	 * Determines if there is a last action for the stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasLastAction()
	{
		// @TODO: rules: only users who are friend of the last_action_user_id should see.
		if ($this->last_userid && $this->last_action && $this->my->id != $this->last_userid) {

			$model = ES::model('Blocks');
			$table = ES::table('Users');

			// user being blocked and deleted from site. #2339 #3430
			if ($model->isBlocked($this->my->id, $this->last_userid, true, true) || !$table->exists($this->last_userid)) {
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Determines if this stream item has an alias
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasAlias()
	{
		if (is_null($this->alias)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if should show the page title for anywhere stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function showPageTitle()
	{
		$params = $this->getParams();

		if (!$params) {
			return false;
		}

		if ($this->hasAnywhereId() && $this->getPerspective() == 'DASHBOARD' && $params->get('pageTitle')) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if this stream item has an anywhere_id
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasAnywhereId()
	{
		if (is_null($this->anywhere_id)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if this stream item has privacy settings
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasPrivacy()
	{
		if (!$this->config->get('privacy.enabled') || $this->privacy === false) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if there is a preview for the stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasPreview()
	{
		return (isset($this->preview) && !empty($this->preview));
	}

	/**
	 * Determines if there is any giphy associated with this stream
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function hasGiphy()
	{
		$contextParams = $this->getContextParams();
		$giphy = $contextParams->get('giphy', false);

		if ($giphy) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the translate button should be visible
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function showTranslateButton()
	{
		if (!$this->config->get('stream.translations.azure') || !$this->config->get('stream.translations.azurekey')) {
			return false;
		}

		$contents = trim($this->content);

		if (!$contents) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if this is currently a stream view
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function isStreamView()
	{
		if ($this->view !== 'stream') {

			// Double check if the view is really not stream
			// since the perspective can alter the original view of the stream #416
			$view = $this->input->get('view', '', 'cmd');

			if ($view !== 'stream') {
				return false;
			}
		}

		return true;
	}

	/**
	 * Tries to aggregate the data
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function aggregate($aggregatedData)
	{

		// Set the actors (Aggregated actors)
		$this->actors = $aggregatedData->actors;

		// Set the targets (Aggregated targets)
		$this->targets = $aggregatedData->targets;

		// Set the context params. (Aggregated params)
		$this->contextParams = $aggregatedData->params;

		// Set the context id.
		$this->contextIds = $aggregatedData->contextIds;
		$this->contextId = $aggregatedData->contextIds[0];

		// Set the verb for this item.
		$this->verb = $aggregatedData->verbs[0];
	}

	/**
	 * Adds image into the opengraph library
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function addOgImage($image)
	{
		if (!$this->isStreamView()) {
			return false;
		}

		$this->meta->setMeta('image', $image);

		return true;
	}

	/**
	 * Handles adding the opengraph data
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function addOgDescription($content = '')
	{
		if (!$this->isStreamView()) {
			return false;
		}

		// Always use the stream content first
		$ogContent = $this->content;

		// If no content, we prioritize whatever provided
		if (empty($ogContent) && !empty($content)) {
			$ogContent = $content;
		}

		// If no content, use the stream title
		if (empty($ogContent) && !empty($this->title)) {
			$ogContent = $this->title;
		}

		$this->meta->setMeta('description', $ogContent);

		return true;
	}

	/**
	 * Retrieves the actor of the stream
	 *
	 * @since	1.3.8
	 * @access	public
	 */
	public function getActor()
	{
		return $this->actor;
	}

	/**
	 * Retrieves the params for the stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPostActor($obj)
	{
		if ($this->post_as == SOCIAL_TYPE_PAGE && $obj->getType() == SOCIAL_TYPE_PAGE) {
			$this->setActorAlias($obj);
			return $obj;
		}

		return $this->getActor();
	}

	/**
	 * Retrieves the actor alias if any
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getActorAlias()
	{
		if (!$this->alias) {
			return $this->getActor();
		}

		return $this->alias;
	}

	/**
	 * Determines whether the stream is moderated
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function isModerated()
	{
		return $this->state == SOCIAL_STREAM_STATE_MODERATE;
	}

	/**
	 * Determines whether the stream is scheduled
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isScheduled()
	{
		return $this->state == SOCIAL_STREAM_STATE_SCHEDULED;
	}

	/**
	 * Determines if the stream is full stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isFull()
	{
		return $this->display == SOCIAL_STREAM_DISPLAY_FULL;
	}

	/**
	 * Determines if the stream item is mini feed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isMini()
	{
		return $this->display == SOCIAL_STREAM_DISPLAY_MINI;
	}

	/**
	 * Normalize the method co caller know what object is this
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getType()
	{
		return SOCIAL_TYPE_STREAM;
	}

	/**
	 * Retrieves the meta html codes
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getMetaHtml()
	{
		if (!$this->with && !$this->location && !$this->mood) {
			return;
		}

		$theme = ES::themes();
		$theme->set('stream', $this);
		$output = $theme->output('site/stream/meta/default');

		return $output;
	}

	/**
	 * Given the mood data, we bind it to the table without running additional queries
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bindMood($data)
	{
		$mood = ES::table('Mood');

		$mood->id = $data->md_id;
		$mood->namespace = $data->md_namespace;
		$mood->namespace_uid = $data->md_namespace_uid;
		$mood->icon = $data->md_icon;
		$mood->verb = $data->md_verb;
		$mood->subject = $data->md_subject;
		$mood->custom = $data->md_custom;
		$mood->text = $data->md_text;
		$mood->user_id = $data->md_user_id;
		$mood->created = $data->md_created;

		return $mood;
	}

	/**
	 * Given the location data, bind it to the table without running additional sql queries to load it
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function bindLocation($data)
	{
		$location = ES::table('Location');

		$location->id = $data->loc_id;
		$location->uid = $data->loc_uid;
		$location->type = $data->loc_type;
		$location->user_id = $data->loc_user_id;
		$location->created = $data->loc_created;
		$location->short_address = $data->loc_short_address;
		$location->address = $data->loc_address;
		$location->latitude = $data->loc_latitude;
		$location->longitude = $data->loc_longitude;
		$location->params = $data->loc_params;

		return $location;
	}

	/**
	 * Get the last action performed on the stream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLastAction()
	{
		if (!$this->hasLastAction()) {
			return;
		}

		$last_actor = ES::themes()->html('html.user', $this->last_userid);

		$cluster = $this->getCluster();

		// [Page Compatibility]
		if ($cluster && $cluster->getType() == SOCIAL_TYPE_PAGE && $cluster->isAdmin($this->last_userid)) {
			$last_actor = ES::themes()->html('html.cluster', $cluster);
		}

		$date = ($this->last_action_date && $this->last_action_date != '0000-00-00 00:00:00') ? ES::date($this->last_action_date) : ES::date($this->modified);
		$text = JText::sprintf('COM_EASYSOCIAL_STREAM_LASTACTION_' . strtoupper($this->last_action), $last_actor, $date->toLapsed());

		return $text;
	}

	/**
	 * Retrieves the perspective of the stream item that is being viewed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPerspective()
	{
		if ($this->perspective) {
			return $this->perspective;
		}

		$view = $this->view;

		if ($view) {

			// Array of views that will use dashboard perspective
			$dashboardPerpectives = array('profile', 'profiles', 'activities');

			if (in_array($view, $dashboardPerpectives)) {
				$view = 'dashboard';
			}

			return strtoupper($view);
		}

		// @TODO: Remove these hardcodes
		return 'DASHBOARD';
	}

	/**
	 * Allow caller to assign perspective for this item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setPerspective($perspective = 'DASHBOARD')
	{
		$this->perspective = strtoupper($perspective);
	}

	/**
	 * Retrieves the title string to be used for the stream. This serves as a helper
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTitle($text)
	{
		$text = $text . '_' . $this->getPerspective();

		return $text;
	}

	/**
	 * Retrieves targets for this stream
	 *
	 * @since	1.3.8
	 * @access	public
	 */
	public function getTargets()
	{
		if (!isset($this->targets) || !$this->targets) {
			return false;
		}

		if (count($this->targets) == 1) {
			return $this->targets[0];
		}

		return $this->targets;
	}

	/**
	 * Retrieves a set of assets associated with this stream item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAssets($uid = '')
	{
		$model 	= ES::model('Stream');

		$uid = ($uid) ? $uid : $this->uid;

		if (!$this->type || !$uid) {
			return array();
		}

		$result	= $model->getAssets($uid , $this->type);

		$assets	= array();

		foreach ($result as $row) {
			$assets[] = ES::registry($row->data);
		}
		return $assets;
	}

	/**
	 * Retrieves the context params associated with the stream item.
	 *
	 * Please take note that this method should only be called after @aggregate is being called
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getContextParams()
	{
		static $registries = array();

		$index = $this->uid;

		if (!isset($registries[$index])) {

			if (!$this->contextParams || !isset($this->contextParams[0])) {
				$registries[$index] = new JRegistry();

				return $registries[$index];
			}

			$registries[$index] = new JRegistry($this->contextParams[0]);
		}

		return $registries[$index];
	}

	/**
	 * Retrieves the registry for the stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getParams()
	{
		$registry = ES::registry();

		if (isset($this->params) && $this->params) {
			$registry = ES::registry($this->params);
		}

		return $registry;
	}

	/**
	 * Prepares the content
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function formatContent($currentView)
	{
		// Check if the content is not empty. We need to perform some formatings
		if (!$this->content) {
			return false;
		}

		$content = $this->content;

		// Format mentions
		$content = $this->formatMentions($content, $currentView);

		// Replace e-mail with proper hyperlinks
		$content = ES::string()->replaceEmails($content);

		// Apply bbcode
		$content = ES::string()->parseBBCode($content, array('escape' => false, 'links' => true, 'code' => true));

		// Some app might want the raw contents so we don't need to update the raw contents
		$this->content = $content;
	}

	/**
	 * Processes mentions in a stream object
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function formatMentions($content, $view, $restFormat = false, $restEditFormat = false, $debug = false)
	{
		// Get tags for the stream
		$tags = isset($this->tags) ? $this->tags : array();

		// If there is no tags, just skip this and escape the content
		if (!$tags) {
			if ($restFormat) {
				return $content;
			}

			$content = ES::string()->escape($content);
			return $content;
		}

		// We need to store the changes in an array and replace it accordingly based on the counter.
		$items = array();

		// We need to merge the mentions and hashtags since we are based on the offset.
		$i = 0;
		$links = array();

		foreach ($tags as &$tag) {

			if ($tag->type == 'user') {
				$replace = '<a href="' . $tag->user->getPermalink() . '" data-popbox="module://easysocial/profile/popbox" data-popbox-position="top-left" data-user-id="' . $tag->user->id . '" class="mentions-user">' . $tag->user->getName() . '</a>';

				if ($restFormat) {
					$tag->identifier = uniqid();

					// Default identifier.
					$replace = '[[object]]' . $tag->identifier . '[[object]]';

					if ($restEditFormat) {
						$replace = '@[' . $tag->user->getName() . '](' . $tag->user->id . ')';
					}
				}
			}

			if ($tag->type == 'hashtag') {

				$alias = $tag->title;
				$url = '';

				if ($this->isCluster()) {

					// We default it to dashboard view
					$url = ESR::dashboard(array('layout' => 'hashtag', 'tag' => $alias));

					$cluster = $this->getCluster();
					$options = array('layout' => 'item', 'id' => $cluster->getAlias(), 'tag' => $alias);
					$url = call_user_func_array(array('ESR', $cluster->getTypePlural()), array($options));

				} else {
					$url = ESR::dashboard(array('layout' => 'hashtag' , 'tag' => $alias));
				}

				$replace = '<a href="' . $url . '" class="mentions-hashtag">#' . $tag->title . '</a>';

				if ($restFormat) {
					$tag->identifier = uniqid();

					// Default identifier.
					$replace = '[[object]]' . $tag->identifier . '[[object]]';

					if ($restEditFormat) {
						$replace = '#[#' . $tag->title . '](' . $tag->id . ')';
					}
				}
			}

			if ($tag->type == 'emoticon') {

				// Load the emoticon using title
				$table = ES::table('emoticon');
				$table->load(array('title' => $tag->title));

				if (!$table->id) {
					continue;
				}

				$replace = $table->getIcon();

				if ($restFormat) {
					$tag->identifier = uniqid();

					// Default identifier.
					$replace = '[[object]]' . $tag->identifier . '[[object]]';

					if ($restEditFormat) {
						$replace = ':(' . $table->title . ')';
					}

					// Since all mobile do support raw unicode, no further processing is required
					if ($table->type == 'unicode') {
						$replace = $table->icon;
					}
				}
			}

			$links[$i] = $replace;

			$replace = '[si:mentions]' . $i . '[/si:mentions]';
			$content = ESJString::substr_replace($content , $replace , $tag->offset , $tag->length);

			$i++;
		}

		// Once we have the content, escape it
		if (!$restFormat) {
			$content = ES::string()->escape($content);
		}

		if ($links) {
			for ($x =0; $x < count($links); $x++) {
				$content = str_ireplace('[si:mentions]' . $x . '[/si:mentions]', $links[$x], $content);
			}
		}

		return $content;
	}

	public function getPrivacyUid()
	{
		static $items = array();

		if (!isset($items[$this->uid])) {
			$model = ES::model('Stream');
			$item = $model->getActivityItem($this->uid, 'uid');

			if (count($this->contextIds) == 1 && $item) {
				$items[$this->uid] = $item[0]->id;
			} else {
				$items[$this->uid] = $this->uid;
			}
		}

		return $items[$this->uid];
	}

	/**
	 * Retrieves the privacy object for the stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPrivacyHtml()
	{
		// Clusters has no privacy
		if ($this->isCluster()) {
			return;
		}

		// There is a possibility that the app has already inserted the html here
		if (!is_null($this->privacy)) {
			return $this->privacy;
		}

		// If it reaches here, we'll construct our own privacy form
		$privacy = $this->my->getPrivacy();

		// $tmpStreamId = ($defaultEvent == 'onPrepareActivityLog') ? $row->uid : $row->id;

		// Get the uid that is associated with the privacy
		$privacyUid = $this->getPrivacyUid();

		$uid = $this->aggregatedItems[0]->uid;

		$form = ES::privacy()->form($privacyUid, SOCIAL_TYPE_ACTIVITY, $this->actor->id, null, false, $uid, array(), array('iconOnly' => true));

		return $form;
	}

	/**
	 * Retrieves the permalink of the stream item.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getPermalink($xhtml = false, $external = false, $sef = true, $adminSef = false, $useRelative = false)
	{
		// If this has anywhere id, point it to that url
		if (!empty($this->anywhere_id)) {
			return ESR::_($this->anywhere_id);
		}

		$option = array('id' => $this->uid, 'layout' => 'item', 'sef' => $sef, 'adminSef' => $adminSef);

		if ($external) {
			$option['external'] = true;
		}

		$link = ESR::stream($option, $xhtml);

		if ($useRelative) {
			$subFolder = JURI::root(true);
			$link = str_replace(JURI::root(), '/', $link);

			// Append back the subfolder if there is
			if ($subFolder) {
				$link = rtrim($subFolder, '/') . $link;
			}
		}

		// If this is a mini stream, it doesn't make sense to link to the item layout
		if ($this->isMini()) {
			$link = ESR::dashboard(array(), $xhtml);
		}

		return $link;
	}

	/**
	 * Determines if the stream can be editable
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isEditable()
	{
		return $this->editable || $this->editablepoll;
	}

	/**
	 * Determines if the stream has been edited before
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isEdited()
	{
		return $this->edited && $this->edited != '0000-00-00 00:00:00';
	}

	/**
	 * Determine if the viewer is the actor of stream item.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function isOwner()
	{
		$my = ES::user();

		if ($my->id == 0) {
			return false;
		}

		return $my->id == $this->actor->id;
	}

	/**
	 * Determines if the viewer can view the dropdown actions for a stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canViewActions()
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Guests should not be able to view this
		if ($this->my->guest) {
			return false;
		}

		// Moderated stream items should be seen by admin and cluster admins
		if ($this->isModerated() && $this->isCluster() && $this->cluster_type == SOCIAL_TYPE_GROUP && ($this->my->isSiteAdmin() || $this->getCluster()->isAdmin() || $this->getCluster()->isOwner())) {
			return true;
		}

		// Some users with privileges to hide and submit report should see it too
		$access = $this->my->getAccess();

		// Allow hide, delete or report the stream
		if ($access->allowed('stream.hide') || $this->canDelete() || $this->canReport()) {
			return true;
		}

		// Pinned the stream
		if ($this->canSticky()) {
			return true;
		}

		// Favourite the stream
		if ($this->config->get('stream.bookmarks.enabled') && !$this->isModerated()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can view the stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canView()
	{
		// Clusters has no privacy
		if ($this->isCluster()) {
			return true;
		}

		// If it reaches here, we'll construct our own privacy form
		$privacy = $this->my->getPrivacy();
		$privacyUid = $this->getPrivacyUid();

		if (!$privacy->validate('core.view', $privacyUid, SOCIAL_TYPE_ACTIVITY, $this->actor->id)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if commenting is possible for this stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canComment()
	{
		if ($this->isMini()) {
			return false;
		}

		// The caller or other 3rd party apps may explicitly turn comments off.
		if ($this->comments === false) {
			return false;
		}

		$privacy = $this->my->getPrivacy();

		if (!$privacy->validate('story.post.comment', $this->actor->id, SOCIAL_TYPE_USER)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if likes is possible for this stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canLike()
	{
		if ($this->isMini()) {
			return false;
		}

		// The caller or other 3rd party apps may explicitly turn comments off.
		if ($this->likes === false) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if reposting is possible for this stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canRepost()
	{
		if (!$this->config->get('repost.enabled')) {
			return false;
		}

		if ($this->isMini()) {
			return false;
		}

		// The caller or other 3rd party apps may explicitly turn comments off.
		if ($this->likes === false) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if report are allowed for this stream item
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function canReport($userId = null)
	{
		$user = ES::user($userId);

		// Get the user's access
		$access = $user->getAccess();

		if (!$this->config->get('reports.enabled')) {
			return false;
		}

		if (!$access->allowed('reports.submit')) {
			return false;
		}

		// Scheduled post can never be reported.
		if ($this->isScheduled()) {
			return false;
		}

		// Check if user is viewing their own stream item
		if ($this->actor->isViewer()) {
			return false;
		}

		// Moderated stream should not show report button as it is not being posted yet.
		if ($this->isModerated()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if copy link is possible for this stream item
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function canPublish()
	{
		if ($this->isModerated() && $this->isCluster() && ($this->my->isSiteAdmin() || $this->getCluster()->isAdmin() || $this->getCluster()->isOwner())) {
			return true;
		}

		if ($this->isScheduled() && ($this->my->isSiteAdmin() || $this->isOwner())) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if copy link is possible for this stream item
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function canCopyLink()
	{
		// Do not proceed if this is a mini stream item since it does not have its own single stream item
		if ($this->isMini()) {
			return false;
		}

		if (!$this->config->get('stream.copylink.enabled')) {
			return false;
		}

		// Scheduled post can never be copy link.
		if ($this->isScheduled()) {
			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->my->getAccess()->allowed('stream.copylink')) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if add favourite link is possible for this stream item
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function canFavourite()
	{
		if (!$this->config->get('stream.bookmarks.enabled')) {
			return false;
		}

		if ($this->isModerated()) {
			return false;
		}

		// Scheduled post can never add favourite.
		if ($this->isScheduled()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if sharing is possible for this stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canShare()
	{
		if (!$this->config->get('sharing.enabled')) {
			return false;
		}

		if (!$this->sharing) {
			return false;
		}

		// Scheduled post can never be shared.
		if ($this->isScheduled()) {
			return false;
		}

		// Worldwide user will not be able to access private cluster, let alone its stream when shared.
		if ($this->isCluster()) {
			$cluster = $this->getCluster();

			if ($cluster->isClosed() || $cluster->isInviteOnly()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determines if the stream item can be deleted by the user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canDelete($userId = null)
	{
		$user = ES::user($userId);

		// Avatar and cover photo update will not be able to delete the stream. #3076
		if ($this->isAvatarStream() || $this->isCoverStream()) {
			return false;
		}

		if ($user->isSiteAdmin()) {
			return true;
		}

		// Get the user's access
		$access = $user->getAccess();

		if ($this->isCluster()) {
			$cluster = $this->getCluster();

			if ($cluster->isAdmin() || ($access->allowed('stream.delete') && $user->id == $this->actor->id)) {
				return true;
			}

			return false;
		}

		if ($access->allowed('stream.delete') && $user->id == $this->actor->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the stream item can be made sticky
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function canSticky()
	{
		if (!$this->config->get('stream.pin.enabled')) {
			return false;
		}

		// If the stream is moderated, it shouldn't be allowed to be stickied
		if ($this->isModerated()) {
			return false;
		}

		// Scheduled post can never be sticky.
		if ($this->isScheduled()) {
			return false;
		}

		// Always allow site admin to sticky
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->isCluster()) {
			$cluster = ES::cluster($this->cluster_type, $this->cluster_id);

			// if user is not the cluster owner or the admin, then dont alllow to sticky
			if (!$cluster->isOwner() && !$cluster->isAdmin()) {
				return false;
			}

		} else {
			if (!$this->isOwner()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determines if this stream item can be hide.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function canHide($userId = null)
	{
		if ($this->isModerated()) {
			return false;
		}

		if ($this->isScheduled()) {
			return false;
		}

		$user = ES::user($userId);

		$access = $user->getAccess();

		if ($access->allowed('stream.hide')) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if this stream item has edit link belong to stream's object.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function hasObjectEditLink()
	{
		if ($this->edit_link) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if this stream item has edit link belong to stream's object.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getObjectEditLink()
	{
		return $this->edit_link;
	}

	/**
	 * Method to export the stream data for REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData($viewer)
	{
		$item = new stdClass();
		$item->id = $this->uid;
		$item->title = strip_tags($this->title);

		// if ($this->privacy !== false) {
		// 	dump($this->privacy);
		// }

		// Run get content object process first before assigning the raw content
		// as $this->content_raw might get formatted within the getContentObj() method.
		// $item->content = $this->getContentObj($viewer);

		// Check if this is not built-in app (3rd party) and dont have any content, skip it.
		// For now we focus on builtin first
		$cluster = $this->getCluster();

		$contentObj = isset($this->contentObj) && $this->contentObj ? $this->contentObj : '';
		$item->contentObj = $contentObj;
		$item->stream_content = $this->formatStreamContent($viewer);
		$item->actor = $this->getPostActor($cluster)->toExportData($viewer);
		$item->verb = $this->verb;
		$item->type = $this->type;
		$item->created = $this->created->toMySQL();
		$item->timestamp = ES::date($this->created)->toLapsed();
		$item->mini = $this->display == SOCIAL_STREAM_DISPLAY_MINI ? true : false;
		$item->cluster_type = !is_null($this->cluster_type) ? $this->cluster_type : false;
		$item->cluster_id = !is_null($this->cluster_id) ? $this->cluster_id : 0;
		$item->location = $this->location;
		$item->mood = $this->mood ? $this->mood->toExportData($viewer) : false;
		$item->hasGiphy = $this->hasGiphy() ? $this->getGiphy() : false;

		$item->lastAction = false;

		if ($this->hasLastAction()) {
			$last_actor = ES::user($this->last_userid);

			// [Page Compatibility]
			if ($cluster && $cluster->getType() == SOCIAL_TYPE_PAGE && $cluster->isAdmin($this->last_userid)) {
				$last_actor = $cluster;
			}

			$lastObj = new stdClass();
			$lastObj->actorId = $last_actor->id;
			$lastObj->actorName = $last_actor->getName();
			$lastObj->type = $last_actor->getType();
			$lastObj->action = $this->last_action;

			$date = ($this->last_action_date && $this->last_action_date != '0000-00-00 00:00:00') ? ES::date($this->last_action_date) : ES::date($this->modified);

			$lastObj->date = $date->toLapsed();

			$item->lastAction = $lastObj;
		}

		$with = array();

		if ($this->with) {
			foreach ($this->with as $user) {
				$userObj = new stdClass;
				$userObj->id = $user->id;
				$userObj->name = $user->getName();
				$userObj->avatar = $user->getAvatar();

				$with[] = $userObj;
			}
		}

		$item->with = $with;

		$target = is_array($this->targets) && count($this->targets) > 0 ? $this->targets[0] : '';

		if (!is_array($this->targets)) {
			$target = $this->targets;
		}

		$item->target = $target ? $target->toExportData($viewer) : '';

		if (!is_bool($this->repost)) {
			$item->repostCount = $this->repost->getCount();
		}

		// permission related items
		$acl = $this->my->getAccess();
		$item->canViewTools = $this->canViewActions();
		$item->canDelete = $this->canDelete();
		$item->canHide = $acl->allowed('stream.hide') && !$this->isModerated() ? true : false;
		$item->canSticky = $this->canSticky(); // pin
		$item->canReport = $this->canReport(); // report
		$item->canBookmark = $this->config->get('stream.bookmarks.enabled') && !$this->isModerated() ? true : false; // report
		$item->canEdit = $this->isEditable();

		// Exclude some of the stream item from being edit for now due to some technical limitation on the app.
		if ($item->canEdit) {
			$exclude = array('polls', 'blog', 'files');

			if (in_array($item->type, $exclude)) {
				$item->canEdit = false;
			}
		}

		$item->alias = $this->hasAlias() ? $this->getActorAlias()->toExportData($viewer) : false;
		$item->post_as = $item->alias ? $item->alias->objectType : $this->post_as;

		return $item;
	}

	/**
	 * Method to format the stream content to REST API format.
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function formatStreamContent($viewer)
	{
		// If this is not it's context, we don't want to do anything here.
		if (!$this->content_raw) {
			$streamContent = new stdClass();
			$streamContent->raw = '';
			$streamContent->edit = '';
			$streamContent->formatted = '';
			$streamContent->object = '';

			return $streamContent;
		}

		// Format the content to satisfy the REST API requirement.
		$rawContent = $this->content_raw;
		$editContent = $rawContent;

		$streamContent = new stdClass();
		$streamContent->raw = $rawContent;

		$formatType = ['edit', 'formatted'];

		foreach ($formatType as $type) {
			$formattedContent = $this->formatMentions($rawContent, 'stream', true, $type === 'edit' ? true : false);

			if ($type === 'formatted') {
				// Replace e-mail with proper hyperlinks
				$formattedContent = ES::string()->replaceEmails($formattedContent, true, $this->tags);

				// Apply bbcode
				$formattedContent = ES::string()->parseBBCode($formattedContent, array('escape' => false, 'links' => true, 'code' => true, 'restFormat' => true), $this->tags);

				// Remove <br> since newline is already present in the string.
				$formattedContent = str_ireplace(array('<br>', '</br>'), '', $formattedContent);
			}

			$streamContent->$type = $formattedContent;
		}

		// Format the object tags within the content
		$streamContent->object = $this->formatStreamObjects($viewer, $streamContent->formatted);

		return $streamContent;
	}

	/**
	 * Method to process stream tags to satisfy the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function formatStreamObjects($viewer, $formattedContent)
	{
		$objects = array();

		// process all the objects that are tagged within the stream content. (emoticon, hashtag, etc)
		foreach ($this->tags as $tag) {
			if (!isset($tag->identifier)) {
				continue;
			}

			$object = new stdClass();
			$object->identifier = $tag->identifier;
			$object->type = $tag->type;

			// Process user mention
			if ($tag->type == 'user' && isset($tag->user) && $tag->user instanceof SocialUser) {
				$user = $tag->user;
				$object->user = $user->toExportData($viewer);
			}

			if ($tag->type == 'emoticon') {

				if (!isset($tag->source)) {
					$table = ES::table('emoticon');
					$table->load(array('title' => $tag->title));

					if (!$table->id) {
						continue;
					}

					$tag->source = $table->icon;
				}

				if (stristr($tag->source , 'http://') === false && stristr($tag->source , 'https://') === false) {
					$subFolder = JURI::root(true);
					$tag->source = ltrim($tag->source, '/');

					if ($subFolder) {
						$subFolder = ltrim($subFolder, '/');
						$parts = explode('/', $tag->source);

						// Determine if the source already included the sub folder
						if ($parts[0] !== $subFolder) {
							$tag->source = rtrim(JURI::root(), '/') . '/' . $tag->source;
						} else {
							$uri = JURI::getInstance();
							$root = $uri->toString(array('scheme', 'host'));
							$root = rtrim($root, '/');

							if (isset($uri->port) && $uri->port) {
								$root = $root . '/' . ltrim($uri->port, '/');
							}

							$tag->source = $root . '/' . $tag->source;
						}

					} else {
						$tag->source = rtrim(JURI::root(), '/') . '/' . $tag->source;
					}
				}

				$object->source = $tag->source;
			}

			if ($tag->type == 'hashtag') {
				$object->title = $tag->title;
			}

			if ($tag->type == 'external_url') {
				$object->url = $tag->url;
			}

			if ($tag->type == 'email') {
				$object->email = $tag->value;
			}

			$objects[$object->identifier] = $object;
		}

		// Split the content so that the app can re-assemble the content part by part.
		$contentObject = explode('[[object]]', $formattedContent);
		$newContentObject = array();

		foreach ($contentObject as $string) {
			$obj = new stdClass();

			if (!isset($objects[$string])) {

				// Nothing to process
				if (strlen($string) === 0) {
					continue;
				}

				$obj->type = 'string';
				$obj->value = $string;

				$newContentObject[] = $obj;
				continue;
			}

			$obj->type = 'object';
			$obj->value = $objects[$string];
			$newContentObject[] = $obj;
		}

		return $newContentObject;
	}
}
