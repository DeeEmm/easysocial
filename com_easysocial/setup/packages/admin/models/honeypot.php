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

ES::import('admin:/includes/model');

class EasySocialModelHoneypot extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('honeypot', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function initStates()
	{
		parent::initStates();
	}

	/**
	 * Purges all the logs from the database
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function purge()
	{
		$db = ES::db();

		$db->setQuery('DELETE FROM `#__social_honeypot`');
		return $db->Query();
	}


	/**
	 * Retrieves a list of points from the site.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_honeypot');

		// Determines if we need to filter by extension
		$type = $this->getState('type');

		if ($type != null && $type != 'all') {
			$sql->where('type', $type);
		}

		// Determines if we need to perform searches
		$search = $this->getState('search');

		if ($search) {
			$sql->where('data' , '%' . $search . '%', 'LIKE');
		}

		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction');

			$sql->order($ordering, $direction);
		}

		$limit = $this->getState('limit', 0);

		// If user passed in a custom limit, we need to respect that
		if (isset($options['limit'])) {
			$limit = $options['limit'];
		}

		$this->setState('limit', $limit);

		// Get the limitstart.
		$limitstart = $this->getUserStateFromRequest( 'limitstart' , 0 );
		$limitstart = ( $limit != 0 ? ( floor( $limitstart / $limit ) * $limit ) : 0 );

		$this->setState('limitstart', $limitstart);

		// Set the total number of items.
		$this->setTotal($sql->getTotalSql());

		// Get the list of users
		$result = parent::getData($sql->getSql());

		if (!$result) {
			return array();
		}

		$logs = array();

		foreach ($result as $row) {
			$log = ES::table('Honeypot');
			$log->bind($row);

			$logs[] = $log;
		}

		return $logs;
	}

	/**
	 * Returns the total number of items for the current query
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getTotal()
	{
		return $this->total;
	}
}
