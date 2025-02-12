<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelLanguages extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('languages', $config);
	}

	/**
	 * Populates the state
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initStates()
	{
		parent::initStates();

		$profile = $this->getUserStateFromRequest('profile');
		$group = $this->getUserStateFromRequest('group');
		$published = $this->getUserStateFromRequest('published', '');
		$ordering = $this->getUserStateFromRequest('ordering', 'id');
		$direction = $this->getUserStateFromRequest('direction', 'asc');

		$this->setState('published', $published);
		$this->setState('group', $group);
		$this->setState('profile', $profile);
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Determines if the language rows has been populated
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initialized()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_languages');
		$sql->column('COUNT(1)');

		$db->setQuery($sql);

		$initialized = $db->loadResult() > 0;

		return $initialized;
	}

	/**
	 * Retrieves languages
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLanguages()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_languages');

		$search = $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		$published = $this->getState('published');

		if ($published) {

			if ($published == 'installed') {
				$sql->where('state', 3, '=');
				$sql->where('state', 1, '=', 'OR');
			}

			if ($published == 'notinstalled') {
				$sql->where('state', 0);
			}
			
		}

		$order = $this->getState('ordering');

		if ($order) {
			$direction = $this->getState('direction');

			$sql->order($order, $direction);
		}

		$limit = $this->getState('limit', 0);

		if ($limit > 0) {
			$this->setState('limit' , $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart' , 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart' , $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			$result = $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		return $result;
	}

	/**
	 * Purges non installed languages
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purge()
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->delete('#__social_languages');
		$sql->where('state', SOCIAL_LANGUAGES_NOT_INSTALLED);

		$db->setQuery($sql);

		return $db->Query();
	}
}
