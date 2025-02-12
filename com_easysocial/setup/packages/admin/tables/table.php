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

class SocialTable extends JTable
{
	private $composite = array();
	protected $privacy = null;
	protected $_supportNullValue = true;

	public function __construct($table, $key, $db, $dispatcher = null)
	{
		// Set internal variables.
		$this->_tbl = $table;
		$this->_tbl_key = $key;

		// For Joomla 3.2 onwards
		$this->_tbl_keys = array($key);

		$this->_db = $db;

		$this->input = ES::request();

		// Implement JObservableInterface:
		// Create observer updater and attaches all observers interested by $this class:
		if (!ES::isJoomla4()) {
			$this->_observers = new JObserverUpdater($this);
			JObserverMapper::attachAllObservers($this);
		} else {
			// Create or set a Dispatcher
			if (!is_object($dispatcher) || !($dispatcher instanceof DispatcherInterface)) {
				$dispatcher = JFactory::getApplication()->getDispatcher();
			}

			$this->setDispatcher($dispatcher);

			$event = Joomla\CMS\Event\AbstractEvent::create('onTableObjectCreate', array('subject' => $this));

			$this->getDispatcher()->dispatch('onTableObjectCreate', $event);
		}
	}

	/**
	 * Tired of fixing conflicts with JTable::getInstance . We'll overload their method here.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getInstance($type, $prefix = 'SocialTable', $config = array())
	{
		// Sanitize and prepare the table class name.
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix . ucfirst($type);

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($tableClass)) {
			// Search for the class file in the JTable include paths.
			$path = dirname(__FILE__) . '/' . strtolower($type) . '.php';

			// Import the class file.
			include_once $path;
		}

		return parent::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return  void
	 *
	 * @link    http://docs.joomla.org/JTable/reset
	 * @since   11.1
	 */
	public function reset()
	{
		$properties = get_object_vars($this);
		$columns = array();

		foreach($properties as $key => $value) {
			if ($key != $this->_tbl_key && strpos($key, '_') !== 0) {
				$columns[] = $value;
			}
		}

		return $columns;
	}

	/**
	 * Delete's item from the table.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function trash()
	{
		if (!property_exists($this, 'state')) {
			return false;
		}

		$this->state = SOCIAL_STATE_TRASHED;

		return $this->store();
	}

	/**
	 * Override JTable behaviour to perform additional cleanups
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		// If child class has `created` column, we'll check if it is set.
		if (property_exists($this, 'created') && !$this->created) {
			$this->created  = ES::get('Date')->toMySQL();
		}

		// Determine the verb for stream items.
		$verb = $this->id ? 'update' : 'create';

		// if this table as alias column, we need to update all
		// the related sef links. #1296
		if ($this->id && ES::isJoomlaSefEnabled() && is_callable(array($this, 'updateAliasSEFCache'))) {
			$this->updateAliasSEFCache();
		}

		// On Joomla 4, if table object contains array or objects, storing is problematic unlike Joomla 3.
		// To fix Joomla 4 storing issues, we override the store behavior and normalize the fields accordingly.
		if (ES::isJoomla4()) {
			$properties = get_object_vars($this);

			foreach ($properties as $key => $value) {
				if ($key != $this->_tbl_key && strpos($key, '_') !== 0) {

					// For Joomla 4, it does not convert array / objects into json strings
					if (is_object($value) || is_array($value)) {
						$this->$key = json_encode($value);
					}

					// For Joomla 4, it does not convert boolean value into 1 / 0
					if (is_bool($value)) {
						$this->$key = $value ? 1 : 0;
					}
				}
			}
		}

		$state = parent::store($updateNulls);

		// If this instance applies social stream, register it.
		if ($state && ($this instanceof ISocialStreamItemTable)) {
			$this->addStream($verb);
		}

		// If this instance applies indexer, register it.
		if ($state && ($this instanceof ISocialIndexerTable)) {
			$this->syncIndex();
		}

		return $state;
	}

	/**
	 * privacy hide?
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPrivacyHide()
	{
		if (!isset($this->context)) {
			return false;
		}

		if ($this->_privacy) {
			return $this->_privacy;
		} else {
			// get from database.
			$privacyObj = ES::privacy(ES::user()->id);
			$this->privacy = $privacyObj->getOption($this->id, $this->context, '');
		}

		return $this->privacy;
	}

	/**
	 * Adding privacy hide
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function addPrivacyHide($value = null)
	{
		if (!isset($this->context)) {
			return false;
		}

		$privacyObj = ES::privacy(ES::user()->id);
		$optionItem = $privacyObj->addOption($this->id, $this->context, $value);

		$this->privacy = $optionItem;

		return $optionItem;
	}

	/**
	 * Removing privacy
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function removePrivacyHide()
	{
		if (!isset($this->context)) {
			return false;
		}

		$privacyObj = ES::privacy(ES::user()->id);
		$state = $privacyObj->removeOption($this->id, $this->context);

		return $state;
	}

	/**
	 * Override JTable::delete to perform additional actions
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function delete($pk = null)
	{
		// we need to delete sef cache #1296, #3164
		if ($this->_tbl_key && ES::isJoomlaSefEnabled() && is_callable(array($this, 'deleteSEFCache'))) {
			$this->deleteSEFCache();
		}

		$state = parent::delete($pk);

		// If this instance applies social stream, register it.
		if ($state && ($this instanceof ISocialStreamItemTable)) {
			$this->removeStream();
		}

		// If this instance applies indexer, register it.
		if ($state && ($this instanceof ISocialIndexerTable)) {
			$this->deleteIndex();
		}

		return $state;
	}

	/**
	 * Determines whether or not the provided id is the creator of the item.
	 * Any subclasses trying to inherit this should have a column called `user_id`.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function isCreator($id = null)
	{
		// Subclasses implementing this might not have a `user_id` column.
		if (!property_exists($this, 'user_id')) {
			return false;
		}

		// If argument is null, we need to detect the current logged in user.
		if (is_null($id)) {
			$id = ES::user()->id;
		}

		if ($this->user_id == $id) {
			return true;
		}

		return false;
	}

	/**
	 * Sets the current `state` column in the table.
	 * Any subclasses trying to inherit this should have a column called `state`.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function setState($state)
	{
		// Subclasses implementing this might not have a `state` column.
		if (!property_exists($this, 'state')) {
			return false;
		}

		// Here, the `state` column exists.
		$this->state = $state;

		return $this;
	}

	/**
	 * Runs some count query to determine if there's any record.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function exists($wheres)
	{
		$db = ES::db();

		$query = 'SELECT COUNT(1) FROM ' . $db->nameQuote($this->_tbl) . ' WHERE 1 ';

		foreach ($wheres as $key => $value) {
			$query .= 'AND ' . $db->nameQuote($key) . '=' . $db->Quote($value) . ' ';
		}

		$db->setQuery($query);

		return $db->loadResult() > 0;
	}

	private function changeState($items, $state = SOCIAL_STATE_PUBLISHED)
	{
		$db = ES::db();
		$state = (int) $state;

		// Fix the values to avoid anyone from abusing it.
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = (int) $items[$i];
			$items[$i] = $db->Quote($items[$i]);
		}

		$items = $db->nameQuote($this->_tbl_key) . '=' . implode(' OR ' . $db->nameQuote($this->_tbl_key) . '=', $items);

		$query = 'UPDATE ' . $db->nameQuote($this->_tbl) . ' SET ' . $db->nameQuote('state') . '=' . $db->Quote($state) . ' WHERE (' . $items . ')';

		$db->setQuery($query);

		if (!$db->query()) {
			return false;
		}

		return true;
	}

	public function unpublish($items = array())
	{
		if (empty($items)) {
			$items = array($this->{$this->_tbl_key});
		}

		// Single item.
		if (!is_array($items)) {
			$items = array($items);
		}

		return self::changeState($items, SOCIAL_STATE_UNPUBLISHED);
	}

	/**
	 * Publishes a specific item.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function publish($items = array(), $state = 1, $userId = 0)
	{
		if (empty($items)) {
			$items  = array($this->{$this->_tbl_key});
		}

		// Ensure that the items is an array.
		$items = ES::makeArray($items);

		return self::changeState($items, SOCIAL_STATE_PUBLISHED);
	}

	public function renderParams($params, $file)
	{
		return ES::get('Parameter', $params, $file)->render();
	}

	/**
	 * Converts a table layer into a JSON encoded string
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toJSON()
	{
		$properties = get_class_vars(get_class($this));
		$result = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$result[$key] = is_null($this->get($key)) ? '' : $this->get($key);
			}
		}

		return ES::json()->encode($result);
	}

	/**
	 * Converts a table layer into an array
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toArray()
	{
		$properties = get_class_vars(get_class($this));
		$result = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$result[$key] = is_null($this->get($key)) ? '' : $this->get($key);
			}
		}

		return $result;
	}

	/**
	 * Allows caller to bind and store params to the column `params`
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function storeParams()
	{
		if (property_exists($this, 'params')) {
			$raw = $this->input->get('params', '', 'default');

			$param = ES::get('Parameter', '');
			$param->bind($raw);

			$this->params = $param->toJSON();

			$this->store();
		}
	}

	public function getState()
	{
		return $this->state;
	}

	/**
	 * Returns the current table's property translated
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function get($key, $default = '')
	{
		$val = JText::_($this->$key);

		if (empty($val)) {
			return $default;
		}

		return $val;
	}

	/**
	 * Returns a translated text from the table column.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function _($key, $default = '')
	{
		if (empty($this->$key)) {
			return $default;
		}

		return JText::_($this->$key);
	}

	/**
	 * Responsible to output all properties of the table object
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function export()
	{
		$obj = new stdClass();
		$properties = get_class_vars(get_class($this));

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$obj->$key = $this->$key;
			}
		}

		return (array) $obj;
	}

	/**
	 * If child table has a `params` column, this method serves as a helper to convert it into a JRegistry object
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getParams()
	{
		if (!isset($this->params) || !is_string($this->params)) {
			return;
		}

		$params = ES::registry($this->params);

		return $params;
	}

	/**
	 * Overwrites JTable's getNextOrder function by expecting an array of columns and values or SocialSql object
	 *
	 * @since   1.0
	 * @access  public
	 **/
	public function getNextOrder($where = '')
	{
		$string = '';

		if (is_string($where)) {
			$string = $where;
		}

		if (is_array($where)) {
			$db = ES::db();

			$string = array();

			foreach ($where as $k => $v) {
				$string[] = $db->nameQuote($k) . ' = ' . $db->quote($v);
			}

			$string = implode(' AND ', $string);
		}

		if (is_object($where) && get_class($where) === 'SocialSql') {
			$string = $where->getConditionSql();
		}

		return parent::getNextOrder($string);
	}

	/**
	 * Override parent's hit behavior
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hit($pk = null)
	{
		$input = JFactory::getApplication()->input;

		// Get users ip address
		$ip = $input->server->get('REMOTE_ADDR');

		// Match only known browsers
		$agent = $input->server->get('HTTP_USER_AGENT', '', 'default');
		$pattern = '/(Mozilla.*(Gecko|KHTML|MSIE|Presto|Trident)|Opera).*/i';

		preg_match($pattern, $agent, $trackHits);

		if ($ip && !empty($this->id) && !empty($trackHits)) {
			$token = md5($ip . $this->id . $this->_tbl);

			$session = JFactory::getSession();
			$exists = $session->get($token, false);

			if ($exists) {
				return true;
			}

			$session->set($token, 1);
		}

		return parent::hit();
	}

	/**
	 * Override parent's hit behavior
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function addHit()
	{
		// this function is deprecated
		return $this->hit();
	}
}

