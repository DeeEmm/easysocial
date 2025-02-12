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

class SocialAdvancedSearchHelperGroup extends SocialAdvancedSearchHelperAbstract
{
	public $type = 'group';

	public function getLink()
	{
		$link = ESR::search(array('layout' => 'advanced', 'type' => SOCIAL_TYPE_GROUP));

		return $link;
	}

	public function search($options = array())
	{
		$limit = isset($options['limit']) ? $options['limit'] : ES::getLimit('search_limit');
		$nextlimit = isset($options['nextlimit']) ? $options[ 'nextlimit' ] : 0;

		$model = ES::model('SearchCluster');

		$options['clusterType'] = SOCIAL_TYPE_GROUP;

		$results = $model->getAdvSearchItems($options, $nextlimit, $limit);
		$this->total = $model->getCount();
		$this->nextlimit = $model->getNextLimit();

		return $results;
	}

	/**
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function loadFields($key, $element)
	{
		$db = ES::db();

		$query = 'select a.*';
		$query .= ' from `#__social_fields` as a';
		$query .= ' inner join `#__social_fields_steps` as fs on a.`step_id` = fs.`id` and fs.`type` = ' . $db->Quote('clusters');
		$query .= ' inner join `#__social_workflows` as w on fs.`workflow_id` = w.`id` and w.`type` = ' . $db->Quote('group');
		$query .= ' inner join `#__social_apps` as b on a.`app_id` = b.`id`';
		$query .= ' where a.`searchable` = ' . $db->Quote('1');
		$query .= ' and a.`state` = ' . $db->Quote('1');
		$query .= ' and a.`unique_key` = ' . $db->Quote($key);
		$query .= ' and b.`group` = ' . $db->Quote('group');
		$query .= ' and b.`element` = ' . $db->Quote($element);

		$db->setQuery($query);
		$fields = $db->loadObjectList();

		return $fields;
	}

	/**
	 * Retrieves all fields available for groups
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFields($elements = array())
	{
		static $fields = null;

		if (! $fields) {

			$db 	= ES::db();
			$sql 	= $db->sql();

			$query = 'select a.`unique_key`, a.`title`, b.`element`';
			$query .= ' from `#__social_fields` as a';
			$query .= ' inner join `#__social_fields_steps` as fs on a.`step_id` = fs.`id` and fs.`type` = ' . $db->Quote('clusters');
			$query .= ' inner join `#__social_workflows` as w on fs.`workflow_id` = w.`id` and w.`type` = ' . $db->Quote('group');
			$query .= ' inner join `#__social_apps` as b on a.`app_id` = b.`id` and b.`group` = ' . $db->Quote('group');
			$query .= ' where a.`searchable` = ' . $db->Quote('1');
			$query .= ' and a.`state` = ' . $db->Quote('1');
			$query .= ' and a.`unique_key` != ' . $db->Quote('');

			if ($elements) {
				// we only want these fields
				$query .= " AND b.`element` IN ('" . implode("','", $elements) . "')";
			}

			$query .= ' order by fs.`sequence`, a.`ordering`';

			$sql->raw($query);
			$db->setQuery($sql);
			$results = $db->loadObjectList();

			// manual grouping / distinct
			if ($results) {
				foreach ($results as $result) {
					$fields[$result->unique_key] = $result;
				}
			}

		}

		return $fields;
	}
}
