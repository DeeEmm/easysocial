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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelStore extends EasySocialModel
{
	private $data = null;
	protected $pagination = null;
	protected $limitstart = null;
	protected $limit = null;

	public function __construct($config = array())
	{
		parent::__construct('store', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initStates()
	{
		parent::initStates();

		$search = $this->getUserStateFromRequest('search', '');
		$type = $this->getUserStateFromRequest('type', '');
		$category = $this->getUserStateFromRequest('category', '');
		$company = $this->getUserStateFromRequest('company', '');

		$this->setState('category', $category);
		$this->setState('type', $type);
		$this->setState('company', $company);
	}

	/**
	 * Retrieves the local items
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function getLocalItems()
	{
		$db = ES::db();
		$query = 'SELECT `app_id` FROM `#__social_apps_store`';

		$db->setQuery($query);
		$result = $db->loadColumn();

		return $result;
	}

	/**
	 * Retrieves a list of applications from the directory
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getItemsWithState($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps_store');

		$featured = $this->normalize($options, 'featured', false);

		if (!$featured) {
			$sql->where('featured', 0);
		}

		$type = $this->getState('type', '');

		if ($type) {
			$sql->where('type', $type);
		}

		$search = $this->getState('search', '');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		$category = $this->getState('category', '');

		if ($category) {
			$sql->where('category', $category);
		}

		$company = $this->getState('company');

		if ($company) {
			$sql->where('stackideas', 1);
		}

		$limit = $this->getState('limit', 0);

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			// Get the list of users
			$result = parent::getData($sql);
		} else {

			// Set the total
			$this->setTotal($sql->getTotalSql());

			// Get the result using parent's helper
			$result = $this->getData($sql);
		}

		if (!$result) {
			return $result;
		}

		$apps = array();

		foreach ($result as $row) {
			$app = ES::store()->getApp($row);

			$apps[] = $app;
		}

		return $apps;
	}

	/**
	 * Retrieves a list of featured apps
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFeaturedApps($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_apps_store');
		$sql->where('featured', 1);

		// Set the total
		$this->setTotal($sql->getTotalSql());

		// Get the result using parent's helper
		$result = $this->getData($sql);

		if (!$result) {
			return $result;
		}

		$apps = array();

		foreach ($result as $row) {
			$app = ES::store()->getApp($row);

			$apps[] = $app;
		}

		return $apps;
	}

	/**
	 * Retrieves a list of app categories
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCategories()
	{
		$db = ES::db();

		$query = 'SELECT DISTINCT(' . $db->qn('category') . ') FROM ' . $db->qn('#__social_apps_store');
		$db->setQuery($query);

		$result = $db->loadColumn();

		return $result;
	}

	/**
	 * Retrieves a list of app types
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTypes()
	{
		$db = ES::db();

		$query = array();
		$query[] = 'SELECT DISTINCT(' . $db->qn('type') . ') FROM ' . $db->qn('#__social_apps_store');
		$query[] = 'WHERE ' . $db->qn('type') . '!=' . $db->Quote('');

		$query = implode(' ', $query);
		$db->setQuery($query);

		$result = $db->loadColumn();

		return $result;
	}

	/**
	 * Retrieves a list of apps from the store that we can track
	 *
	 * @since	2.0.9
	 * @access	public
	 */
	public function getTrackableApps()
	{
		$db = ES::db();

		// First, we'll get the list of apps that can be installed from EasySocial
		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__social_apps_store');
		$query[] = 'WHERE ' . $db->qn('element') . ' != ' . $db->Quote('');
		$query[] = 'AND ' . $db->qn('group') . ' != ' . $db->Quote('');
		$query[] = 'AND ' . $db->qn('type') . ' != ' . $db->Quote('');

		$query = implode(' ', $query);

		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves a list of track-able apps on the store and local installed apps
	 * to determine apps that requires updating
	 *
	 * @since	2.0.9
	 * @access	public
	 */
	public function getAppsRequiringUpdates()
	{
		$db = ES::db();

		// First, we'll get the list of apps that can be installed from EasySocial
		$result = $this->getTrackableApps();

		if (!$result) {
			return false;
		}

		$apps = array();

		foreach ($result as $row) {
			$table = ES::table('App');
			$exists = $table->loadByElement($row->element, $row->group, $row->type);

			// If it isn't installed, then skip this
			if (!$exists) {
				continue;
			}

			// Check if the app require updates
			if (!$table->isOutdated()) {
				continue;
			}

			$apps[] = $table;
		}

		return $apps;
	}
}
