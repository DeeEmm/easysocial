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

class EasySocialModelOptimizer extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('optimizer');
	}

	/**
	 * Retrieves a list of image attachments that are not processed by the image optimizer yet
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getPhotosToOptimize($limit = 10)
	{
		$db = ES::db();
		$limit = (int) $limit;

		$query = [
			'SELECT a.* FROM `#__social_photos_meta` AS a',
			'INNER JOIN `#__social_photos` AS b ON a.`photo_id` = b.`id`',
			'WHERE a.`group` = ' . $db->Quote(SOCIAL_PHOTOS_META_PATH),
			'AND b.`state`=1',
			'AND b.`storage` = ' . $db->Quote(SOCIAL_STORAGE_JOOMLA),
			'AND NOT EXISTS(',
			'SELECT x.`uid` FROM `#__social_optimizer` AS x',
			'WHERE x.`uid` = a.`id`',
			'AND x.`type` = "' . SOCIAL_TYPE_PHOTO . '"',
			')',
			'LIMIT 0,' .  $limit
		];

		$query = implode(' ', $query);
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (!$items) {
			return $items;
		}

		return $items;
	}

	/**
	 * Retrieves a list of image files that are not processed by the image optimizer yet
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getFilesToOptimize($limit = 10)
	{
		$db = ES::db();
		$limit = (int) $limit;

		$imageTypes = array('"image/jpeg"', '"image/png"');

		$query = [
			'SELECT a.* FROM `#__social_files` AS a',
			'WHERE a.`mime` IN (' . implode(',', $imageTypes) . ')',
			'AND a.`storage` = ' . $db->Quote(SOCIAL_STORAGE_JOOMLA),
			'AND a.`type` != ' . $db->Quote('fields'),
			'AND NOT EXISTS(',
			'SELECT x.`uid` FROM `#__social_optimizer` AS x',
			'WHERE x.`uid` = a.`id`',
			'AND x.`type` = ' . $db->Quote(SOCIAL_TYPE_FILES),
			')',
			'LIMIT 0,' .  $limit
		];

		$query = implode(' ', $query);
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (!$items) {
			return $items;
		}

		return $items;
	}
}
