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

class EasySocialModelTags extends EasySocialModel
{
	private $data = null;
	static $_data = array();

	public function __construct()
	{
		parent::__construct('tags');
	}

	/**
	 * Retrieve a list of tags that is associated with the targets
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getTags($targetId, $targetType, $tagType = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$rows = array();

		if (isset(self::$_data[$targetType]) && isset(self::$_data[$targetType][$targetId])) {

			$rows =  self::$_data[$targetType][$targetId];

		} else {
			$sql->select('#__social_tags', 'a');
			$sql->where('a.target_id', $targetId);
			$sql->where('a.target_type', $targetType);

			if ($tagType) {
				$sql->where('a.type', $tagType);
			}

			$sql->order('a.offset', 'DESC');

			$db->setQuery($sql);
			$rows = $db->loadObjectList();
		}

		if (!$rows) {
			return $rows;
		}

		$tags = array();

		foreach ($rows as $row) {
			$tag = ES::table('Tag');

			$tag->bind($row);

			$tags[] = $tag;
		}

		return $tags;
	}

	public function setTagBatch($ids, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		// prefill the data
		foreach ($ids as $id) {
			self::$_data[$type][$id] = array();
		}

		$query = "select * from `#__social_tags`";
		$query .= " where `target_id` IN (" . implode(',', $ids) . ")";
		$query .= " and `target_type` = '$type'";
		$query .= ' order by `offset` DESC';

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadObjectList();

		if ($result) {
			foreach($result as $row) {
				self::$_data[$row->target_type][$row->target_id][] = $row;
			}
		}
	}

	public function cleanTags($targetId, $targetType, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'delete from `#__social_tags` where `target_id` = ' . $db->Quote($targetId);
		$query .= ' and `target_type` = ' . $db->Quote($targetType);

		if ($type) {
			$query .= ' AND `type` = ' . $db->Quote($type);
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$db->query();

		return true;
	}
}
