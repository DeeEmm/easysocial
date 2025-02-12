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

class ArticleModel extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('apparticles');
	}

	/**
	 * Retrieves a list of tasks created by a particular user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItems($userId, $limit = 0)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__content');
		$sql->where('created_by', $userId);
		$sql->where('state', 1);

		// Pagination
		$this->setTotal($sql, true);

		// Get the limitstart.
		$limitstart = $limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->getUserStateFromRequest('limitstart', 0);
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limitstart', $limitstart);

		if ($limit) {
			$this->setLimit($limit);
		}

		// Always order by creation date
		$sql->order('created', 'DESC');

		$result = $this->getData($sql);

		return $result;
	}

	/**
	 * Retrieves the total number of articles created
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotalArticles($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->column('count(1)');
		$sql->select('#__content');
		$sql->where('created_by', $userId);
		$sql->where('state', 1);

		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}
}
