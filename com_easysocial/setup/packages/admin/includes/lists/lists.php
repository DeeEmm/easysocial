<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialLists extends EasySocial
{
	public $table = null;

	public function __construct($listId = null)
	{
		parent::__construct();

		// Get the friend object
		$this->table = ES::table('List');

		if ($listId) {
			$this->load($listId);
		}
	}

	/**
	 * Magic method to get properties which don't exist on this object but on the table
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function __get($key)
	{
		if (isset($this->table->$key)) {
			return $this->table->$key;
		}

		if (isset($this->$key)) {
			return $this->$key;
		}

		return $this->$key;
	}

	public static function factory($listId = null)
	{
		$obj = new self($listId);

		return $obj;
	}

	/**
	 * Determines if the user can assign people to the list
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canAssign($userId = null)
	{
		if ($user->isSiteAdmin()) {
			return true;
		}

		return true;
	}

	/**
	 * Determine if the user can create playlists
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function canCreatePlaylist()
	{
		if ($this->my->guest) {
			return false;
		}

		$access = $this->my->getAccess();

		// Check if audio upload is enabled
		// Only uploaded audio can be added into playlist
		if (!$this->config->get('audio.uploads')) {
			$this->setError('COM_ES_AUDIO_PLAYLISTS_ACCESS_NOT_ALLOWED');

			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Check if the user is allowed to create playlist
		if (!$access->allowed('audios.playlist.enabled')) {
			$this->setError('COM_ES_AUDIO_PLAYLISTS_ACCESS_NOT_ALLOWED');

			return false;
		}

		// This will be a new playlist, check if the user has already reached the limit
		$listModel = ES::model('Lists');
		$totalPlaylist = $listModel->getTotalLists($this->my->id, SOCIAL_TYPE_AUDIOS);

		// Check if user exceeded their limit
		if ($access->exceeded('audios.playlist.limit', $totalPlaylist)) {
			$this->setError('COM_ES_AUDIO_PLAYLISTS_ACCESS_LIMIT_EXCEEDED');
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user can create lists
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canCreateList($userId = null)
	{
		$user = ES::user($userId);
		$access = $user->getAccess();

		if ($user->isSiteAdmin()) {
			return true;
		}

		// Check if the user is allowed to create friend lists
		if (!$access->allowed('friends.list.enabled')) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_LISTS_ACCESS_NOT_ALLOWED');

			return false;
		}

		// This will be a new friend list, check if the user has already reached the limit
		$listModel = ES::model('Lists');

		// Get the total friends list a user has
		$totalFriendsList = $listModel->getTotalLists($user->id);

		// Check if user exceeded their limit
		if ($access->exceeded('friends.list.limit', $totalFriendsList)) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_LISTS_ACCESS_LIMIT_EXCEEDED');
			return false;
		}

		return true;
	}

	public function bind($data = array())
	{
		$this->table->bind($data);
	}

	public function load($listId)
	{
		return $this->table->load($listId);
	}

	private function validate()
	{
		// Check if the user owns this list item.
		if ($this->table->id && $this->my->id != $this->table->user_id && !$this->my->isSiteAdmin()) {
			$this->setError('COM_EASYSOCIAL_LISTS_ERROR_LIST_IS_NOT_OWNED');
			return false;
		}

		// Check for title
		if (!$this->table->title) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_LISTS_TITLE_REQUIRED');
			return false;
		}

		return true;
	}

	/**
	 * Save playlist
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function savePlaylist($Ids = array())
	{
		// Validate the data
		$state = $this->validate();

		if ($state === false) {
			return false;
		}

		$access = $this->my->getAccess();

		if (!$access->get('audios.playlist.enabled')) {
			return $this->setError('COM_ES_AUDIO_PLAYLISTS_ACCESS_NOT_ALLOWED');
		}

		$this->table->user_id = $this->my->id;


		// Try to store the list.
		$state = $this->table->store();

		if (!$state) {
			$this->setError($this->table->getError());
			return false;
		}

		// Assign these audios into the list.
		if ($Ids) {
			$this->addAudio($Ids);
		}

		return true;
	}

	public function save($friendIds = array())
	{
		// Validate the data
		$state = $this->validate();

		if ($state === false) {
			return false;
		}

		$access = $this->my->getAccess();

		if (!$access->get('friends.list.enabled')) {
			return $this->setError('COM_EASYSOCIAL_FRIENDS_LISTS_ACCESS_NOT_ALLOWED');
		}

		$this->table->user_id = $this->my->id;

		// Prepare the dispatcher
		ES::apps()->load(SOCIAL_TYPE_USER);

		$dispatcher = ES::dispatcher();
		$args = array(&$list);

		// @trigger: onFriendListBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onFriendListBeforeSave', $args);

		// Try to store the list.
		$state = $this->table->store();

		if (!$state) {
			$this->setError($this->table->getError());
			return false;
		}

		// Assign these friends into the list.
		if ($friendIds) {
			$this->addPeople($friendIds);
		}

		// @trigger: onFriendListBeforeSave
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onFriendListAfterSave' , $args);

		return true;
	}

	/**
	 * Adds items into an existing list
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function addAudio($ids = array())
	{
		if (!is_array($ids)) {
			$ids = array($ids);
		}

		if (!$ids) {
			return false;
		}

		// Ensure that the current list belongs to the user
		if (!$this->table->isOwner()) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_LIST_NOT_OWNER');
			return false;
		}

		$listMapIds = array();

		foreach ($ids as $id) {

			$map = ES::table('ListMap');

			$map->list_id = $this->table->id;
			$map->target_id = $id;
			$map->target_type = SOCIAL_TYPE_AUDIO;

			$map->store();

			$listMapIds[$map->id] = $id;
		}

		return $listMapIds;
	}

	/**
	 * Adds friends into an existing list
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addPeople($ids = array(), $type = SOCIAL_TYPE_USER)
	{
		// Ensure that it's an array.
		$ids = ES::makeArray($ids);

		if (!$ids) {
			return false;
		}

		// Ensure that the current list belongs to the user
		if (!$this->table->isOwner()) {
			$this->setError('COM_EASYSOCIAL_FRIENDS_LIST_NOT_OWNER');
			return false;
		}

		$model = ES::model('Friends');

		foreach ($ids as $id) {

			if ($model->isFriends($this->table->user_id, $id)) {

				$map = ES::table('ListMap');
				$exists	= $map->load(array('list_id' => $this->table->id , 'target_id' => $id, 'target_type' => $type));

				// Item already exist.
				if ($exists) {
					continue;
				}

				$map->list_id = $this->table->id;
				$map->target_id = $id;
				$map->target_type = $type;
				$map->store();

				// Assign points when the user inserts a friend into the list.
				ES::points()->assign('friends.list.add', 'com_easysocial', $this->table->user_id);
			}
		}

		return true;
	}
}
