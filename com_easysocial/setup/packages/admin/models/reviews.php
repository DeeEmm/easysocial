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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelReviews extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('reviews', $config);
	}

	/**
	 * Determine whether the user has voted or not
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function hasVoted($uid, $type, $userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_reviews');
		$sql->column('COUNT(1)');

		$sql->where('uid', $uid);
		$sql->where('type', $type);
		$sql->where('created_by', $userId);

		$db->setQuery($sql);
		$count = (int) $db->loadResult();

		return $count > 0 ? true : false;
	}

	/**
	 * Preload a ratings
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function preloadRatings($clusterIds = array())
	{
		$db = ES::db();
		$query = 'SELECT AVG(`value`) AS ratings, COUNT(1) AS total, `uid`'
				.' FROM `#__social_ratings` as a'
				.' WHERE a.`uid` IN(' . implode(',', $clusterIds) . ')'
				.' AND `type` = ' . $db->Quote(SOCIAL_TYPE_PAGE)
				.' GROUP BY `uid`';

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result) {
			return array();
		}

		$ratings = array();

		foreach ($result as $row) {
			$obj = new stdClass();
			$obj->ratings = round($row->ratings);
			$obj->total = $row->total;

			$ratings[$row->uid] = $obj;
		}

		return $ratings;
	}

	/**
	 * Retrieving Page reviews
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getReviews($clusterId, $type, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_reviews');
		$sql->where('uid', $clusterId);
		$sql->where('type', $type);

		if (isset($options['userId']) && $options['userId']) {
			$sql->where('created_by', $options['userId']);
		}

		$pending = isset($options['pending']) ? $options['pending'] : '';

		if ($pending) {
			$sql->where('published', SOCIAL_REVIEW_STATE_PENDING);
		} else {
			$sql->where('published', SOCIAL_REVIEW_STATE_PUBLISHED);
		}

		$sql->order('created', 'desc');

		$limit = isset($options['limit']) ? $options['limit'] : '';

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Run pagination here.
			$this->setTotal($sql->getTotalSql());

			$result = $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		$items = array();

		if ($result) {
			foreach ($result as $row) {
				$review = ES::table('Reviews');
				$review->bind($row);
				$review->author = $review->getAuthor();

				$items[] = $review;
			}
		}

		return $items;
	}

	/**
	 * Retrieves the total number of reviews
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getTotalReviews($clusterId, $type, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_reviews', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.uid', $clusterId);
		$sql->where('a.type', $type);

		$pending = isset($options['pending']) ? $options['pending'] : '';

		if ($pending) {
			$sql->where('a.published', SOCIAL_REVIEW_STATE_PENDING);
		} else {
			$sql->where('a.published', SOCIAL_REVIEW_STATE_PUBLISHED);
		}

		$db->setQuery($sql);
		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the ratings of the item
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAverageRatings($clusterId, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'SELECT AVG(`value`) AS ratings from `#__social_reviews`';
		$query .= ' where `uid` = ' . $db->Quote($clusterId);
		$query .= ' and `type` = ' . $db->Quote($type);
		$query .= ' and `published` = ' . SOCIAL_REVIEW_STATE_PUBLISHED;
		$query .= ' GROUP BY `uid`';

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadResult();

		if (!$result) {
			return 0;
		}

		return round($result);
	}

	public function deleteReviewStreams($reviewId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "delete a, b from `#__social_stream` as a";
		$query .= "     inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
		$query .= " where a.`context_type` = 'reviews'";
		$query .= " and b.`context_id` = '$reviewId'";

		$sql->raw($query);
		$db->setQuery($sql);

		$db->query();

		return true;
	}

	/**
	 * Retrieves a list of reviews created by a particular user for gdpr
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getReviewsGDPR($options = array())
	{
		$db = ES::db();
		$query = array();

		$userId = $this->normalize($options, 'userid', null);

		$query[] = 'SELECT * FROM ' . $db->nameQuote('#__social_reviews');
		$query[] = ' WHERE ' . $db->nameQuote('created_by') . ' = ' . $db->Quote($userId);

		$inclusions = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE);
		$inclusions = ES::makeArray($inclusions);
		$inclusionsType = array();

		foreach ($inclusions as $inclusion) {
			$inclusionsType[] = $db->Quote($inclusion);
		}

		$inclusionsType = implode(',', $inclusionsType);
		$query[] = ' AND ' . $db->nameQuote('type') . ' IN (' . $inclusionsType . ')';

		$exclusions = $this->normalize($options, 'exclusion', array());

		if ($exclusions) {
			$exclusions = ES::makeArray($exclusions);
			$exclusionIds = array();

			foreach ($exclusions as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = ' AND ' . $db->nameQuote('id') . ' NOT IN (' . $exclusionIds . ')';
		}

		$limit = (int) $this->normalize($options, 'limit', 20);

		$query[] = 'LIMIT ' . $limit;

		$query = implode(' ', $query);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}
	
		return $result;	
	}
}
