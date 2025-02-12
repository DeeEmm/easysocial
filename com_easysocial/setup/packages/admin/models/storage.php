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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelStorage extends EasySocialModel
{
	public function __construct( $config = array())
	{
		parent::__construct('storage', $config);
	}

	/**
	 * Method to compute storage usage
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function syncUsage($userId = null)
	{
		if ($userId) {
			return $this->syncUserUsage($userId);
		}

		return $this->syncAll();
	}

	/**
	 * Method to compute all user's storage usage
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function syncAll()
	{
		$db = $this->db;

		$query = 'TRUNCATE TABLE `#__social_storage_usage`';

		$db->setQuery($query);
		$db->query();

		$query = array();
		$query[] = 'INSERT INTO `#__social_storage_usage` (`user_id`, `size`, `notify`)';
		$query[] = 'SELECT t.`user_id`, IFNULL(SUM(t.`size`), 0) as size, ' . $db->Quote('0') . ' as notify FROM ';
		$query[] = '(';
		$query[] = 'SELECT `id` as `user_id`, null as size FROM `#__users`';
		$query[] = 'UNION ALL';

		$query[] = 'SELECT `user_id`, `total_size` as size FROM `#__social_photos`';
		$query[] = 'WHERE `state` = ' . $db->Quote('1');
		$query[] = 'UNION ALL';

		$query[] = 'SELECT `user_id`, `size` as size FROM `#__social_files`';
		// $query[] = 'WHERE `state` = ' . $db->Quote('1');
		$query[] = 'UNION ALL';

		$query[] = 'SELECT `user_id`, `size` as size FROM `#__social_videos`';
		$query[] = 'WHERE `state` != ' . $db->Quote('0');
		$query[] = 'UNION ALL';

		$query[] = 'SELECT `user_id`, `size` as size FROM `#__social_audios`';
		$query[] = 'WHERE `state` != ' . $db->Quote('0');

		$query[] = ')';
		$query[] = 'AS t group by t.`user_id`';

		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->query();

		return true;
	}

	/**
	 * Method to compute user's storage usage
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function syncUserUsage($userId = null)
	{
		$db = $this->db;
		$query = array();
		$endQuery = '';
		$userQuery = 'WHERE `user_id` = ' . $db->Quote($userId);

		$table = ES::table('storageUsage');
		$table->load(array('user_id' => $userId));

		if ($table->id) {
			$query[] = 'UPDATE `#__social_storage_usage` SET `size` = (';
			$query[] = 'SELECT IFNULL(SUM(t.`size`), 0) AS size FROM';
			$endQuery = ' ) ' . $userQuery;
		} else {
			$query[] = 'INSERT INTO `#__social_storage_usage` (`user_id`, `size`, `notify`)';
			$query[] = 'SELECT t.`user_id`, IFNULL(SUM(t.`size`), 0) as size, ' . $db->Quote('0') . ' as notify FROM ';
		}

		$query[] = '(';

		$query[] = 'SELECT `id` as user_id, null as size from `#__users`';
		$query[] = 'WHERE `id` = ' . $db->Quote($userId);
		$query[] = 'UNION ALL';

		$query[] = 'SELECT `user_id`, `total_size` as size FROM `#__social_photos`';
		$query[] = $userQuery;
		$query[] = 'AND `state` = ' . $db->Quote('1');
		$query[] = 'UNION ALL';

		$query[] = 'SELECT `user_id`, `size` as size FROM `#__social_files`';
		$query[] = $userQuery;
		// $query[] = 'AND `state` = ' . $db->Quote('1');
		$query[] = 'UNION ALL';

		$query[] = 'SELECT `user_id`, `size` as size FROM `#__social_videos`';
		$query[] = $userQuery;
		$query[] = 'AND `state` != ' . $db->Quote('0');
		$query[] = 'UNION ALL';

		$query[] = 'SELECT `user_id`, `size` as size FROM `#__social_audios`';
		$query[] = $userQuery;
		$query[] = 'AND `state` != ' . $db->Quote('0');

		$query[] = ')';
		$query[] = 'AS t group by t.`user_id`';
		$query[] = $endQuery;

		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->query();

		return true;
	}
}
