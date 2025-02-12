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

class EasySocialModelAds extends EasySocialModel
{
	private $_nextlimit = 0;

	public function __construct()
	{
		parent::__construct('ads');
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

	/**
	 * Retrieve a list of ads from a particular advertiser
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getAds($advertiserId, $options = [])
	{
		$db = ES::db();


		$query = [
			'select * from `#__social_ads`',
			'where `advertiser_id`=' . $db->Quote((int) $advertiserId)
		];

		$state = ES::normalize($options, 'state', null);

		if (!is_null($state)) {
			$query[] = 'AND `state`=' . $db->Quote($state);
		}

		$db->setQuery($query);

		$ads = $db->loadObjectList();

		if ($ads) {
			foreach ($ads as &$ad) {
				$ad = ES::ad($ad);
			}
		}

		return $ads;
	}

	/**
	 * Retrieve a list of ads from the site
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getItemsWithState($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_ads');

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
		$pending = ES::normalize($options, 'pending', false);

		if ($pending) {
			$sql->where('state', SOCIAL_ADS_DRAFT, '=', 'OR');
			$sql->where('state', SOCIAL_ADS_MODERATION, '=', 'OR');
		}

		if (!$pending) {
			$state = $this->getState('state');

			if ($state != 'all' && !is_null($state)) {
				$sql->where('state', $state);
			}

			$sql->where('state', SOCIAL_ADS_DRAFT, '!=');
			$sql->where('state', SOCIAL_ADS_MODERATION, '!=');
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

			// Get the list of items
			$result = $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$ads = array();

		foreach ($result as $row) {
			$ad = ES::table('Ad');
			$ad->bind($row);

			$ads[] = $ad;
		}

		return $ads;
	}

	/**
	 * Get all ads on the site
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$now = ES::date()->toSql();

		$query = [
			'SELECT a.* FROM `#__social_ads` AS a',
			'INNER JOIN `#__social_advertisers` AS b',
			'ON a.`advertiser_id` = b.`id`',
			'WHERE a.`state`=' . $db->Quote(SOCIAL_STATE_PUBLISHED)
		];

		$title = ES::normalize($options, 'title', '');

		if ($title) {
			$query[] = 'AND a.`title`=' . $db->Quote($title);
		}

		$priority = ES::normalize($options, 'priority', '');

		if ($priority && $priority != 'all') {
			$query[] = 'AND a.`priority`=' . $db->Quote($priority);
		}

		$advertiser = ES::normalize($options, 'advertiser', '');

		if ($advertiser) {
			$query[] = 'AND a.`advertiser_id`=' . $db->Quote($advertiser);
		}

		$query[] = 'AND b.`state`=' . $db->Quote(SOCIAL_ADS_PUBLISHED);
		$query[] = 'AND (a.`start_date` <= ' . $db->Quote($now);
		$query[] = 'AND a.`end_date` >= ' . $db->Quote($now);
		$query[] = 'OR a.`start_date` = ' . $db->Quote('0000-00-00 00:00:00') . ')';

		$order = ES::normalize($options, 'order', 'title');

		if ($order == 'random') {
			$query[] = 'ORDER BY RAND() ASC';
		} else {
			$query[] = 'ORDER BY a.' . $db->nameQuote($order) . ' ASC';
		}

		if (isset($options['limit']) && $options['limit']) {
			$query[] = ' limit ' . $options['limit'];
		}

		$query = implode(' ', $query);

		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves the pending count for advertisements
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getPendingCount()
	{
		$db = ES::db();

		$query = [
			'SELECT COUNT(1) FROM `#__social_ads` WHERE `state`=' . $db->Quote(SOCIAL_ADS_MODERATION)
		];

		$db->setQuery($query);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Unpublish ads from a specific advertiser account
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateAdvertiserAdsToModeration($advertiserId)
	{
		$db = ES::db();

		$query = [
			'UPDATE `#__social_ads` SET `state`=' . $db->Quote(SOCIAL_ADS_MODERATION),
			'WHERE `advertiser_id`=' . $db->Quote((int) $advertiserId)
		];

		$db->setQuery($query);
		return $db->Query();
	}
}
