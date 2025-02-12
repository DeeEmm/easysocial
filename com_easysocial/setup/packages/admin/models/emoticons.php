<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelEmoticons extends EasySocialModel
{
	private $_nextlimit = 0;

	public function __construct()
	{
		parent::__construct('emoticons');
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function initStates()
	{
		$callback = $this->input->get('jscallback', '', 'default');
		$defaultFilter = $callback ? SOCIAL_STATE_PUBLISHED : 'all';

		$filter = $this->getUserStateFromRequest('state', $defaultFilter);

		$this->setState('state', $filter);

		parent::initStates();
	}

	// Debuging purpose
	public function initEmoticons()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_emoticons');
		$sql->column('id');
		$sql->limit(0, 1);

		$db->setQuery($sql);
		$id = $db->loadResult();

		// We don't have to do anything since there's already a default emoticons
		if ($id) {
			return;
		}

		$library = SOCIAL_LIB . '/bbcode/adapters/decoda/library/config/emoticons.json';

		$contents = file_get_contents($library);
		$result = json_decode($contents);

		$insertValues = array();
		$count = 1;

		$now = ES::date()->toSql();

		foreach ($result as $key => $value) {
			$icon = 'media/com_easysocial/images/icons/emoji/' . $key . '.png';
			$insertValues[] = "(" . $db->Quote($count) . ", " . $db->Quote($key) . ", " . $db->Quote($icon) . ", 1, " . $db->Quote($now) . ")";
			$count++;
		}

		$query = "INSERT INTO `#__social_emoticons` (`id`, `title`, `icon`, `state`, `created`) VALUES " . implode(',', $insertValues);

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Retrieve a list of emoticons from the site
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getItemsWithState($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_emoticons');

		// Check for search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Check for ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$sql->order($ordering, $direction);
		}

		// Check for state
		$state = $this->getState('state');

		if ($state != 'all' && !is_null($state)) {
			$sql->where('state', $state);
		}

		$limit = $this->getState('limit');

		if ($limit != 0) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			// Get the list of users
			$result = $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$emoticons = array();

		foreach ($result as $row) {
			$emoticon = ES::table('Emoticon');
			$emoticon->bind($row);

			$emoticons[] = $emoticon;
		}

		return $emoticons;
	}

	/**
	 * Get all emoticons on the site
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getItems($options = array())
	{
		static $_cache = null;

		$db = ES::db();
		$sql = $db->sql();

		// lets try to load from cache when there is no option pass in
		if (!$options) {
			 if (is_null($_cache)) {

				$sql->select('#__social_emoticons', 'a');
				$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
				$sql->order('a.title', 'asc');

				$db->setQuery($sql);
				$_cache = $db->loadObjectList();
			 }

			 return $_cache;
		}


		$sql->select('#__social_emoticons', 'a');

		if (isset($options['title']) && $options['title']) {
			$sql->where('a.title', $options['title']);
		}

		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->order('a.title', 'asc');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieve a list of emoticons
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getJsonEmoticons()
	{
		static $_cache = null;

		if (is_null($_cache)) {

			$items = $this->getItems();

			foreach ($items as $item) {

				$table = ES::table('Emoticon');
				$table->bind($item);

				$emoticon = new stdClass();

				$emoticon->title = ':(' . $table->title . ')';
				$emoticon->type = 'emoticon';
				$emoticon->menuHtml = $table->getIcon(true) . ' ' . $table->title;

				$_cache[] = $emoticon;
			}
		}

		return json_encode($_cache, JSON_HEX_APOS | JSON_HEX_QUOT);
	}

	/**
	 * validate duplicate title
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function validateTitle($title, $id = false)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_emoticons', 'a');
		$sql->where('a.title', $title);

		if ($id) {
			$sql->where('a.id', $id, '!=');
		}

		$db->setQuery($sql);

		return $db->loadObjectList();
	}

	/**
	 * Searches for a particular emoticon given the current keyword
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function search($keyword)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_emoticons', 'a');
		$sql->where('a.state', SOCIAL_STATE_PUBLISHED);
		$sql->where('a.title', '%' . $keyword . '%', 'LIKE');
		$sql->group('a.title');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}
}
