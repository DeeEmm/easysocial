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

class EasySocialModelClusterNews extends EasySocialModel
{
	public function __construct( $config = array() )
	{
		parent::__construct( 'clusternews' , $config );
	}

	/**
	 * Deletes all news from a specific cluster
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function delete( $id )
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->delete( '#__social_clusters_news' );
		$sql->where( 'cluster_id' , $id );

		$db->setQuery( $sql );

		return $db->Query();
	}


	/**
	 * Retrieves the total number of announcements
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getTotalNews($clusterId, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_news', 'a');
		$sql->column('COUNT(1)');
		$sql->where('a.cluster_id', $clusterId);

		// If we should exclude specific items
		$exclude = isset($options['exclude']) ? $options['exclude'] : '';

		if ($exclude) {
			$sql->where('a.id', $exclude, 'NOT IN');
		}

		$db->setQuery($sql);
		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of news item from a particular page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getNews($clusterId, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters_news', 'a');
		$sql->where('a.cluster_id', $clusterId);

		// If we should exclude specific items
		$exclude = isset($options['exclude']) ? $options['exclude'] : '';

		if ($exclude) {
			$sql->where('a.id', $exclude, 'NOT IN');
		}

		$sql->order('created', 'DESC');

		$limit = isset($options['limit']) ? $options['limit'] : '';

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Run pagination here.
			$this->setTotal($sql->getTotalSql());

			$result	= $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}
		// it seems like the result loaded multiple times and causing the sql 0,0,0,0 error
		// $result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$items = array();

		foreach ($result as $row) {
			$news = ES::table('ClusterNews');
			$news->bind($row);
			$news->author = $news->getAuthor();

			$items[] = $news;
		}

		return $items;
	}

	/**
	 * Retrieves a list of news created by a specific user
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getNewsGDPR($userId, $options = array())
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select('#__social_clusters_news', 'a');
		$sql->where('a.created_by', $userId);

		// If we should exclude specific items
		$exclude = isset($options['exclude']) ? $options['exclude'] : '';

		if ($exclude) {
			$sql->where('a.id', $exclude, 'NOT IN');
		}

		$limit = isset($options['limit']) ? $options['limit'] : '';

		if ($limit) {
			$this->setState('limit', $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart', $limitstart);

			// Run pagination here.
			$this->setTotal($sql->getTotalSql());

			$result	= $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$items = array();

		foreach ($result as $row) {
			$news = ES::table('ClusterNews');
			$news->bind($row);
			$news->author = $news->getAuthor();

			$items[] = $news;
		}

		return $items;
	}
}
