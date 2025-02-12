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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelBackground extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('background', $config);
	}

	/**
	 * Retrieve a list of backgrounds already created on the site
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getItemsWithState($options = array())
	{
		$db = ES::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__social_backgrounds');
		$query[] = 'WHERE 1';

		// Determines if user is filtering the items
		$state = $this->getState('published');

		if ($state != 'all' && !is_null($state)) {
			$query[] = 'AND ' . $db->qn('state') . '=' . $db->Quote($state);
		}

		// Determines if user is searching for a custom background
		$search = $this->getState('search');

		if ($search) {
			$query[]  = 'AND ' . $db->qn('title') . ' LIKE ' . $db->Quote('%' . $search . '%');
		}

		$sql = $db->sql();
		$sql->raw($query);

		$this->setTotal($sql->getTotalSql());

		$result = parent::getData($sql->getSql());

		if (!$result) {
			return $result;
		}

		$backgrounds = array();

		foreach ($result as $row) {
			$table = ES::table('Background');
			$table->bind($row);

			$backgrounds[] = $table;
		}

		return $backgrounds;
	}

	/**
	 * Retrieve a list of backgrounds already created on the site
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPresetBackgrounds($options = array())
	{
		static $_cache = null;

		$db = ES::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__social_backgrounds');
		$query[] = 'WHERE ' . $db->qn('state') . '=' . $db->Quote(1);

		$results = array();

		if (!$options) {
			// if there is no options specified, lets use the cache copies.

			if (is_null($_cache)) {
				$db->setQuery($query);

				$_cache = $db->loadObjectList();
			}

			$results = $_cache;

		} else {
			// process options here.

			$db->setQuery($query);

			$results = $db->loadObjectList();
		}

		if (!$results) {
			return $results;
		}

		$backgrounds = array();

		foreach ($results as $row) {
			$table = ES::table('Background');
			$table->bind($row);

			$table->params = new JRegistry($table->params);

			$backgrounds[] = $table;
		}

		return $backgrounds;
	}
}
