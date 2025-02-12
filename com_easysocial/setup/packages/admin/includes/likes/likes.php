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

class SocialLikes extends EasySocial
{
	public $data = array();
	public $uid = null;
	public $element = null;
	public $group = null;
	public $verb = null;
	public $options = array();
	public $stream_id = null;
	public $uri = null;
	public $react_as = null;
	public $reactions = [];

	// Determines if anyone reacted to the current item
	private $hasReaction = false;

	public function __construct($uid = null, $element = null, $verb = null, $group = SOCIAL_APPS_GROUP_USER, $streamId = null, $options = array())
	{
		parent::__construct();

		$this->uid = $uid;
		$this->element = $element;
		$this->group = $group;
		$this->verb = $verb;
		$this->stream_id = $streamId;

		// Try to get the viewas from url
		$this->react_as = $this->input->get('viewas', 'user', 'string');

		if (!empty($options['reactAs'])) {
			$this->react_as = $options['reactAs'];
		}

		if (!empty($options['clusterId'])) {
			$this->setOption('clusterId', $options['clusterId']);
			$this->setOption('clusterType', $group);

			// We only allow page owner to react as page
			$view = $this->input->get('view', '', 'cmd');

			$model = ES::model('Clusters');
			$isOwner = $model->isOwner($this->my->id, $options['clusterId']);

			if (!$isOwner) {
				$this->react_as = 'user';
			}
		}

		if (!empty($options['uri'])) {
			$this->uri = $options['uri'];
		}

		if (!is_null($uid) && !is_null($element)) {
			$this->get($uid, $element, $verb, $group, $streamId, $options);
		}
	}

	/**
	 * Determines if the current viewer is allowed to react to this object
	 *
	 * @since	2.2.6
	 * @access	public
	 */
	public function canReact()
	{
		// Check if user tries to react as page but he is not the admin
		if ($this->react_as === 'page') {

			$page = ES::page($this->options['clusterId']);

			if (!$page->id) {
				return false;
			}

			// Only the owner of the page will be able to react as page
			if (!$page->isOwner()) {
				return false;
			}
		}

		// if there is a stream id, lets use it.
		if ($this->stream_id) {

			$streamTable = ES::table('Stream');
			$streamTable->load($this->stream_id);

			if ($streamTable->id && (!$streamTable->isModerated() && !$streamTable->isTrashed())) {
				$items = ES::stream()->getItem($streamTable->id, $streamTable->cluster_id, $streamTable->cluster_type, false, array('perspective' => 'dashboard'));

				if ($items && is_array($items)) {
					// now we need to check if the item is belong to this stream or not. #3732
					$streamItem = $items[0];

					// check if this stream allow to 'reaction' or not.
					if ($streamItem->likes instanceof SocialLikes) {
						$streamLikeLib = $streamItem->likes;
						if ($streamLikeLib->uid == $this->uid && $streamLikeLib->element == $this->element && $streamLikeLib->group == $this->group) {
							return true;
						}
					}
				}

				// if stream lib return non array data, mean this user cannot view the stream
				return false;
			}
		}

		// Site admins should always be allowed to react
		if (ES::isSiteAdmin()) {
			return true;
		}

		if ($this->element == 'comments') {
			// for now we let it pass
			return true;
		}

		// no stream id. lets fall back to check the item's privacy / access
		$apps = ES::apps();
		$apps->load($this->group);

		$args = array('reaction', $this->element, $this->verb, $this->uid);
		$dispatcher = ES::dispatcher();
		$allowed = $dispatcher->trigger($this->group, 'isItemViewable', $args);

		if (in_array(true, $allowed)) {
			return true;
		}

		return false;
	}

	public static function factory($uid = null, $element = null, $verb = null, $group = SOCIAL_APPS_GROUP_USER, $streamId = null, $options = array())
	{
		return new self($uid, $element, $verb, $group, $streamId, $options);
	}

	/**
	 * Determines if there are reactions
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function hasReactions()
	{
		if ($this->reactions) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the provided user has liked the object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasLiked($uid = null, $element = null, $verb = null, $group = SOCIAL_APPS_GROUP_USER, $userId = null)
	{
		if (is_null($uid)) {
			$uid = $this->uid;
		}

		if (is_null($element)) {
			$element = $this->element;
		}

		if (is_null($verb)) {
			$verb = $this->verb;
		}


		if (is_null($userId)) {
			$userId = $this->my->id;
		}

		if ($this->group) {
			$group = $this->group;
		}

		// Form the key
		$key = $this->formKeys($element, $group, $verb);

		$model = ES::model('Likes');
		$hasLiked = $model->hasLiked($uid, $key, $userId);

		return $hasLiked;
	}

	/**
	 * Get's the likes data for a particular item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCount($uid = null, $element = null, $verb = null, $group = null, $options = array())
	{
		if (is_null($uid)) {
			$uid = $this->uid;
		}

		if (is_null($element)) {
			$element = $this->element;
		}

		if (is_null($verb)) {
			$verb = $this->verb;
		}

		if (is_null($group)) {
			$group = $this->group;
		}

		if (empty($group)) {
			$group = SOCIAL_APPS_GROUP_USER;
		}

		if (isset($this->options['clusterId']) && $this->options['clusterId']) {
			$options['clusterId'] = $this->options['clusterId'];
		}


		$key = $this->formKeys($element, $group, $verb);

		$model = ES::model('Likes');
		$count = $model->getLikesCount($uid, $key, $options);

		return $count;
	}

	/**
	 * Get's the likes data for a particular item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function get($id, $element, $verb = null, $group = SOCIAL_APPS_GROUP_USER, $streamId = false, $options = array(), $debug = false)
	{
		$debug = isset($options['debug']) ? $options['debug'] : $debug;
		$model = ES::model('Likes');

		// Get the likes
		if ($streamId) {
			$this->reactions = $model->getReactionsResult($streamId, 'stream', null, true, $options);
			$this->stream_id = $streamId;
			$this->data = $model->getLikes($streamId, 'stream', $options, $debug);
		} else {
			$key = $this->formKeys($element, $group, $verb);
			$this->reactions = $model->getReactionsResult($id, $key, null, true, $options);
			$this->data = $model->getLikes($id, $key, $options, $debug);
		}

		// Determines if there are any reactions
		foreach ($this->reactions as $reaction) {
			if ($reaction->getTotal() > 0) {
				$this->hasReaction = true;
				break;
			}
		}

		if (!empty($options['clusterId'])) {
			$this->setOption('clusterId', $options['clusterId']);
			$this->setOption('clusterType', $group);
		}

		$this->uid = $id;
		$this->element = $element;
		$this->group = $group;
		$this->verb = $verb;

		return $this;
	}

	/**
	 * Reloads the data
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function refresh()
	{
		$this->get($this->uid, $this->element, $this->verb, $this->group);
	}

	/**
	 * Get's the likes data based on stream item.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getStreamLike( $streamId )
	{
		$model 			= ES::model( 'Likes' );
		$this->data		= $model->getLikes( $streamId , 'stream' );

		if ($this->data) {
			$tmp = $this->data[0];

			$tmpData = explode( '.', $tmp->type);

			$this->uid		= $tmp->uid;
			$this->element	= $tmpData[0];
			$this->group	= $tmpData[1];

			unset($tmpData[0]);
			unset($tmpData[1]);

			$this->verb		= implode('.', $tmpData);
		}
	}

	/**
	 * Generates the key for likes
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	private function formKeys($element, $group, $verb = '')
	{
		$key = $element . '.' . $group;

		if ($verb) {
			$key = $key . '.' . $verb;
		}

		return $key;
	}

	public function setOption( $key, $value )
	{
		$this->options[ $key ] = $value;
	}

	/**
	 * Construct the key for this likes object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getKey()
	{
		// Build the key for likes
		$key = $this->element . '.' . $this->group;

		if ($this->verb) {
			$key = $key . '.' . $this->verb;
		}

		return $key;
	}

	/**
	 * Determines if we should use the stream id based on the current type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getStreamId()
	{
		if ($this->element == 'albums') {
			return 0;
		}

		return $this->stream_id;
	}

	/**
	 * Retrieves the like table object
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUserReaction($uid = null, $element = null, $verb = null, $group = SOCIAL_APPS_GROUP_USER, $userId = null, $reactAs = null)
	{
		if (is_null($uid)) {
			$uid = $this->uid;
		}

		if (is_null($element)) {
			$element = $this->element;
		}

		if (is_null($verb)) {
			$verb = $this->verb;
		}


		if (is_null($userId)) {
			$userId = $this->my->id;
		}

		if ($this->group) {
			$group = $this->group;
		}

		if (is_null($reactAs)) {
			$reactAs = $this->react_as;
		}

		// Form the key
		$key = $this->formKeys($element, $group, $verb);

		$table = ES::table('Likes');
		$table->load(array('type' => $key, 'created_by' => $userId, 'uid' => $uid, 'react_as' => $reactAs));

		return $table;
	}

	/**
	 * Generates the like link.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function button($buttonStyle = false)
	{
		ES::language()->loadSite();
		$model = ES::model('Likes');

		// We should respect the stream id or if it is photos, we won't use the stream id
		$streamId = !$this->stream_id || $this->element == 'photos' ? false : $this->stream_id;

		// Get a list of reactions
		$reactions = $model->getReactions();

		if (!$reactions) {
			return;
		}

		// We need to get the reaction of the user against this object
		$selectedReaction = $model->getUserReaction($this->uid, $this->formKeys($this->element, $this->group, $this->verb), $this->my->id, $streamId, $this->react_as);

		// Should we inject the stream id
		$streamid = $this->stream_id;

		if (!empty($this->options['streamid'])) {
			$streamid = $this->options['streamid'];
		}

		$clusterId = '';

		if (!empty($this->options['clusterId'])) {
			$clusterId = $this->options['clusterId'];
		}

		$baseReaction = $reactions[0];

		$theme = ES::themes();
		$theme->set('baseReaction', $baseReaction);
		$theme->set('selectedReaction', $selectedReaction);
		$theme->set('reactions', $reactions);
		$theme->set('buttonStyle', $buttonStyle);
		$theme->set('uid', $this->uid);
		$theme->set('element', $this->element);
		$theme->set('group', $this->group);
		$theme->set('verb', $this->verb);
		$theme->set('streamid', $streamid);
		$theme->set('clusterId', $clusterId);
		$theme->set('reactAs', $this->react_as);

		$output = $theme->output('site/likes/action');

		return $output;
	}

	/**
	 * Generates the likes output
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function html()
	{
		ES::language()->loadSite();

		$type = SOCIAL_TYPE_STREAM;

		if ($this->element == SOCIAL_TYPE_COMMENTS) {
			$type = SOCIAL_TYPE_COMMENTS;
		}

		// We only need the reactions list for activity stream
		$text = '';

		if ($type == SOCIAL_TYPE_STREAM) {
			$text = $this->hasReaction ? $this->toString() : '';
		}

		$theme = ES::themes();

		// No reactions enabled on the site
		if (!$this->reactions) {
			return;
		}

		if ($this->isMobile()) {
			$totalReactions = 0;
			$reactionKey = false;

			foreach ($this->reactions as $reaction) {
				$total = $reaction->getTotal();

				if ($total >= 1 && !$reactionKey) {
					$reactionKey = $reaction->getKey();
				}

				$totalReactions = $totalReactions + $total;
			}

			$theme->set('totalReactions', $totalReactions);
			$theme->set('reactionKey', $reactionKey);
		}

		$clusterId = '';

		if (!empty($this->options['clusterId'])) {
			$clusterId = $this->options['clusterId'];
		}

		$theme->set('hasReaction', $this->hasReaction);
		$theme->set('reactions', $this->reactions);
		$theme->set('text', $text);
		$theme->set('uid', $this->uid);
		$theme->set('element', $this->element);
		$theme->set('group', $this->group);
		$theme->set('verb', $this->verb);
		$theme->set('type', $type);
		$theme->set('clusterId', $clusterId);

		$namespace = 'site/likes/item.' . $type;

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Deprecated. Use $likes->html();
	 *
	 * @deprecated 1.4
	 */
	public function toHTML()
	{
		return $this->html();
	}

	/**
	 * Retrieves the likes text
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function toString($viewerId = null, $plaintext = false, $useSimple = false)
	{
		// Default return text.
		$text = '';

		// If there's no likes at all, we should just return an empty string.
		if (!$this->data) {
			return $text;
		}

		// Get current logged in user as we need to know if the viewer is themselves or not.
		$viewer = ES::user($viewerId);

		// Ensure that the data is an array
		$data = !is_array($this->data) ? array($this->data) : $this->data;

		// List of users which liked this item.
		$users = array();
		$cluster = false;
		$total = 0;

		$arrayIndex = 0;
		$clusterIndex = 0;

		// Retrieve only users
		foreach ($data as $like) {

			// If the like actor is page, we load the page
			if ($like->react_as == SOCIAL_TYPE_PAGE) {
				$clusterId = $like->getParams()->get('clusterId');
				$cluster = ES::cluster($clusterId);

				// We need to increase the total,
				// so that it would match the total users reacted
				$total = 1;

				// Need to keep the original position of page for later use
				$clusterIndex = $arrayIndex;
			} else {
				$users[] = $like->created_by;
			}

			$arrayIndex++;
		}

		$users = array_unique($users);

		// Determines if we should use the term YOU in the language string
		$useYou = in_array($viewer->id, $users);

		// Default to use 3rd party view of likes
		$language = 'COM_ES_USER_';

		// Unique the result
		$users = array_unique($users);

		// Get the total users in the likes
		$total += count($users);

		$remainder = 0;

		// Simple text
		if ($useSimple) {
			return JText::sprintf(ES::string()->computeNoun('COM_ES_TOTAL_REACTIONS', $total), $total);
		}

		// If we need to use "you" within the language string
		if ($useYou) {
			$language = 'COM_ES_YOU_';
		}

		// Possibilities
		// 1. You reacted to this
		if ($total == 1 && $useYou) {
			$language .= 'REACTED_TO_THIS';
		}

		// Possibilities
		// 1. user1 reacted to this
		if ($total == 1 && !$useYou) {
			$language .= 'REACTED_TO_THIS';
		}

		// Possibilities
		// 1. You and user1 reacted to this
		// 2. user1 and user2 reacted to this
		if ($total == 2) {
			$language .= 'AND_1USER_REACTED';
		}

		// Possibilities
		// 1. You,user1 and user2 likes this
		// 2. user1 , user2 and user3 likes this
		if ($total == 3) {
			$language .= 'AND_2USERS_REACTED';
		}

		// Possibilities
		// 1. You,user1, user2 and user3 likes this
		// 2. user1, user2, user3 and user4 likes this
		if ($total == 4) {
			$language .= 'AND_3USERS_REACTED';
		}

		// Possibilities
		// 1. You, user1, user2 and 24 others like this
		// 2. user1, user2, user3 and 24 others like this
		if ($total > 4) {
			$language .= 'AND_OTHERS_REACTED';

			$remainder = $total - 3;
		}

		// If user is in the list, we need to relocate the viewer to the first index
		if ($useYou) {
			// Get the current viewer
			$key = array_search($viewer->id, $users);

			if ($key !== false) {
				unset($users[$key]);

				array_unshift($users, $viewer->id);
			}
		}

		// Get users
		$userlist = ES::user($users);

		// somehow ES::users messed up the user ordering.
		// let resort the user list.
		$tmpUsers = array();
		foreach ($users as $id) {
			$tmpUsers[] = ES::user($id);
		}

		if ($cluster) {

			// If there is 'You' at the first index,
			// we move the cluster to the second index.
			if ($useYou && $clusterIndex == 0) {
				$clusterIndex = 1;
			}

			// insert the cluster at its original position
			array_splice($tmpUsers, $clusterIndex, 0, array($cluster));
		}

		$users = $tmpUsers;

		if ($total < 1) {
			return;
		}

		$theme = ES::themes();
		$theme->set('total', $total);
		$theme->set('language', $language);
		$theme->set('users', $users);
		$theme->set('verb', $this->verb);
		$theme->set('uid', $this->uid);
		$theme->set('element', $this->element);
		$theme->set('group', $this->group);
		$theme->set('remainder', $remainder);

		$text = $theme->output('site/likes/string');

		return $text;
	}

	public function toArray()
	{
		return $this->data;
	}

	/**
	 * Exports likes data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer)
	{
		$data = new stdClass();
		$data->uid = (int) $this->uid;
		$data->element = $this->formKeys($this->element, $this->group, $this->verb);
		$data->total = (int) $this->getCount();
		$data->liked = $this->hasLiked($this->uid, $this->element, $this->verb, $this->group, $viewer->id);

		// Get a list of users that liked the items
		$data->users = array();

		$users = $this->getParticipants();

		foreach ($users as $user) {
			$data->users[] = $user->toExportData($viewer);
		}

		return $data;
	}

	/**
	 * Allows 3rd party implementation to delete likes related to an object
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int			The unique item id that is being liked
	 * @param	string		The unique item element that is being liked
	 * @param	int 		The current user that liked the item
	 * @return 	boolean 	true or false.
	 */
	public function delete ($uid = null , $element = null, $verb = null, $group = SOCIAL_APPS_GROUP_USER, $userId = null, $all = false)
	{
		if (is_null($uid)) {
			$uid = $this->uid;
		}

		if (is_null($element)) {
			$element = $this->element;
		}

		if (is_null($verb )) {
			$verb = $this->verb;
		}

		$type = $this->formKeys($element, $group, $verb);

		if ($all) {
			// we need to remove all the reactions belong to an object
			$model = ES::model('Likes');
			$state = $model->delete($uid, $type);

			return $state;
		}


		if (is_null($userId)) {
			$userId = ES::user()->id;
		}

		$like = ES::table('Likes');

		// Check if the user has already liked this item before.
		$exists = $like->load( array( 'uid' => $uid , 'type' => $type , 'created_by' => $userId ) );

		// If item has been liked before, return false.
		if (!$exists) {
			return false;
		}

		$state = $like->delete();

		if (!$state) {
			return false;
		}

		$key = $uid . '.' . $type;
		$model = ES::model('Likes');
		$model->removeCache($key, $userId);

		return true;
	}

	/**
	 * Allows 3rd party implementation to toggle likes to an object
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int			The unique item id that is being liked
	 * @param	string		The unique item element that is being liked
	 * @param	int 		The current user that liked the item
	 * @return	SocialTableLikes
	 */
	public function toggle( $uid = null , $element = null , $verb = null, $group = SOCIAL_APPS_GROUP_USER, $userId = null )
	{
		if( is_null( $uid ) )
		{
			$uid = $this->uid;
		}

		if( is_null( $element ) )
		{
			$element = $this->element;
		}

		if( is_null( $verb ) )
		{
			$verb = $this->verb;
		}

		if( is_null( $userId ) )
		{
			$userId = ES::user()->id;
		}

		$like 	= ES::table( 'Likes' );

		// Check if the user has already liked this item before.
		$exists = $like->load( array( 'uid' => $uid , 'type' => $this->formKeys( $element, $group, $verb ) , 'created_by' => $userId ) );

		// If item has been liked before, return false.
		if( $exists )
		{
			$state 	= $this->delete( $uid , $element , $verb, $group, $userId );

			return $state;
		}

		return $this->add( $uid , $element , $verb, $group, $userId );
	}

	/**
	 * Allows 3rd party implementation to add likes to an object
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int			The unique item id that is being liked
	 * @param	string		The unique item element that is being liked
	 * @param	int 		The current user that liked the item
	 * @return	SocialTableLikes
	 */
	public function add( $uid = null , $element = null , $verb = null, $group = SOCIAL_APPS_GROUP_USER, $userId = null )
	{
		if( is_null( $uid ) )
		{
			$uid = $this->uid;
		}

		if( is_null( $element ) )
		{
			$element = $this->element;
		}

		if( is_null( $verb ) )
		{
			$verb = $this->verb;
		}

		if( is_null( $userId ) )
		{
			$userId = ES::user()->id;
		}

		$like 	= ES::table( 'Likes' );

		// Check if the user has already liked this item before.
		$exists = $like->load( array( 'uid' => $uid , 'type' => $this->formKeys( $element, $group, $verb ) , 'created_by' => $userId ) );

		// If item has been liked before, return false.
		if( $exists )
		{
			return false;
		}

		$like->uid 	= $uid;
		$like->type = $this->formKeys( $element, $group, $verb );
		$like->created_by  = $userId;

		$state 	= $like->store();

		if( !$state )
		{
			return false;
		}

		// add into static variable
		$key = $uid . '.' . $this->formKeys($element, $group, $verb);

		$model = ES::model('Likes');
		$model->insertCache($key, $like);

		return $like;
	}

	/**
	 * Retrieve a list of users who liked this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getParticipants( $userObject = true )
	{
		$model = ES::model( 'likes' );
		$users = $model->getLikerIds( $this->uid, $this->formKeys( $this->element, $this->group, $this->verb ) );

		$objects = array();

		if( $users && $userObject )
		{
			foreach( $users as $user )
			{
				$objects[] = ES::user( $user );
			}

			return $objects;
		}

		return $users;
	}

	/**
	 * Generate stream item that are associated with react action
	 *
	 * @since	3.0.0
	 * @access	private
	 */
	private function generateStream($streamId)
	{
		// Determine if we should pre generate the stream for this action
		if ($this->element == 'photos') {
			$generatePhotoStream = false;

			// Batch upload through story form
			if (!$streamId && ($this->verb == 'upload' || $this->verb == 'add' || $this->verb == 'create')) {
				$generatePhotoStream = true;
			}

			if ($generatePhotoStream) {
				$photo = ES::table('Photo');
				$photo->load($this->uid);

				if ($photo->id) {

					// if the stream sorting was set to 'latest first', we will need to take the current datetime as creation date
					// or else the push-to-top feature will not work properly for this stream item. #3982
					$streamSorting = $this->config->get('stream.pagination.ordering');
					$pushToTop = $this->config->get('stream.pushtop.reactions', 1);

					$photoCreationDate = $photo->created;

					// #4059
					$streamItem = ES::table('StreamItem');
					$streamItem->load(array('context_type' => 'photos', 'context_id' => $photo->id));
					if ($streamItem && $streamItem->id) {
						$photoCreationDate = $streamItem->created;
					}

					$createdDate = ($pushToTop && $streamSorting == 'created') ? ES::date()->toSql() : $photoCreationDate;

					// Generate the stream now. #2575
					$streamItem = $photo->addPhotosStream('create', $createdDate, false, $this->verb);

					// Now get the stream id
					if ($streamItem) {
						$streamId = $streamItem->uid;

						if ($this->verb == 'upload') {
							$this->verb = 'add';
						}
					}
				}
			}
		}

		return $streamId;
	}

	/**
	 * Allows caller to react to an item
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function react($reaction = 'like', $userId = false)
	{
		$streamId = $this->getStreamId();

		$user = $this->my;

		if ($userId) {
			$user = ES::user($userId);
		}

		$key = $this->getKey();

		$params = array();

		// We need to store the cluster to be used later
		if (isset($this->options['clusterId'])) {
			$params['clusterId'] = $this->options['clusterId'];
		}

		$model = ES::model('Likes');
		$state = $model->react($this->uid, $key, $user->id, $reaction, $streamId, $this->uri, $this->react_as, $params);

		if (!$state) {
			$this->setError($model->getError());
			return false;
		}

		// Refresh the data
		$this->refresh();

		// Now we need to update the associated stream id from the liked object
		$updateStream = $this->config->get('stream.pushtop.reactions', 1);

		if (!$streamId) {
			// Add custom points that are not related with streamid
			$this->processPoints('like');

			if (!$updateStream) {
				return $state;
			}

			// special handling for new reaction on album page. #5455
			if ($this->element == 'albums' && $this->verb == 'create') {

				// lets get the latest photo stream that tied to this album
				$albumsModel = ES::model('Albums');
				$streamId = $albumsModel->getStreamId($this->uid);

				if ($streamId) {
					$streamModel = ES::model('Stream');
					$totalItem = $streamModel->getStreamItemsCount($streamId);

					// Only update the stream if the album has more than one photo
					if ($totalItem == 1) {
						$streamId = false;
					}
				}
			}

			// Unfortunately none of the stream id exist
			if (!$streamId) {
				return $state;
			}
		}

		// // Now we need to update the associated stream id from the liked object
		// $updateStream = $this->config->get('stream.pushtop.reactions', 1);

		if ($updateStream && $this->element == 'photos') {
			$streamModel = ES::model('Stream');
			$totalItem = $streamModel->getStreamItemsCount($streamId);

			if ($totalItem > 1) {
				$updateStream = false;
			}
		}

		if ($updateStream) {
			$stream = ES::stream();
			$stream->updateModified($streamId, $user->id, SOCIAL_STREAM_LAST_ACTION_LIKE);
		}

		// since we have create new rule 'story.reaction.add.owner' which will assign point to the owner of the story post.
		// we no longer need below rule execuation.

		// Assign like points to the stream author.
		// if (isset($stream)) {
		// 	$streamActor = $stream->getStreamActor($streamId);

		// 	// Check if user trying to like his own post, do not add points.
		// 	if ($streamActor->id != $user->id) {
		// 		ES::points()->assign('post.like', 'com_easysocial', $streamActor->id);
		// 	}
		// }

		return true;
	}

	/**
	 * Allows caller to withdraw a reaction
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function withdraw()
	{
		$key = $this->getKey();
		$streamId = $this->getStreamId();

		$model = ES::model('Likes');
		$state = $model->withdraw($this->uid, $key, $this->my->id, $streamId, $this->react_as);

		if (!$state) {
			$this->setError($model->getError());
			return false;
		}

		// Refresh the data
		$this->refresh();

		if (!$streamId) {

			// Add custom points that are not related with streamid
			$this->processPoints('unlike');
			return $state;
		}

		// we need to revert the last action of this stream.
		if ($state && $streamId) {
			$stream = ES::stream();
			$stream->revertLastAction($streamId, $this->my->id, SOCIAL_STREAM_LAST_ACTION_LIKE);

			// since we have create new rule 'story.reaction.remove.owner' which will assign point to the owner of the story post.
			// we no longer need below rule execuation.

			// Assign unlike points to the stream author.
			// $streamActor = $stream->getStreamActor($streamId);

			// // Check if user trying to unlike his own post, do not deduct the points.
			// if ($streamActor->id != $this->my->id) {
			// 	ES::points()->assign('post.unlike', 'com_easysocial', $streamActor->id);
			// }
		}

		return true;
	}

	/**
	 * Process points integration that are associated with likes
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function processPoints($type)
	{
		// Add points for discussion apps
		if ($this->element == 'discussion') {

			// Get discussion data
			$discussion = ES::table('Discussion');
			$discussion->load($this->uid);

			if (!$discussion->id) {
				return;
			}

			// liked or unliked
			$likeType = $type . 'd';

			// groups.discussion.reply.liked
			$rule = $this->group . '.discussion.' . $likeType;

			// Reply
			if ($discussion->parent_id > 0) {

				// Seems older version already added those group discussion like point rule got extra s e.g. groups.discussion.reply.liked , groups.discussion.reply.unliked
				// so we need to add extra s for $this->group
				if ($this->group == SOCIAL_APPS_GROUP_GROUP) {
					$this->group = 'groups';
				}

				$rule = $this->group . '.discussion.reply.' . $likeType;
			}

			// Give points and badges to the post author
			if ($discussion->created_by != $this->my->id) {

				// Assign points.
				ES::points()->assign($rule, 'com_easysocial', $discussion->created_by);

				// Assign badges.
				ES::badges()->log('com_easysocial', $rule, $discussion->created_by, '');
			}
		}
	}

	/**
	 * Deprecated. Use @react instead
	 *
	 * @deprecated	2.1
	 */
	public function like()
	{
		return $this->react();
	}

	/**
	 * Deprecated. Use @withdraw instead
	 *
	 * @deprecated	2.1
	 */
	public function unlike()
	{
		return $this->withdraw();
	}
}
