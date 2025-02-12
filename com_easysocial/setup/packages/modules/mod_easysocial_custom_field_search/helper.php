<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialModCustomFieldSearchHelper
{
	public static function getFields(&$params)
	{
		$group = $params->get('searchtype', SOCIAL_TYPE_USER);
		$uid = $group == SOCIAL_TYPE_USER ? (int) $params->get('profile_id', 0) : (int) $params->get($group . '_category', 0);

		static $data = array();

		$idx = $group . '-' . $uid;

		if (!isset($data[$idx]) || !$data[$idx]) {
			// later we need to respect the module settings.
			$elements = array('dropdown','checkbox', 'boolean');
			
			$fieldStepType = $group == SOCIAL_TYPE_USER ? 'profiles' : 'clusters';

			$db = ES::db();
			$sql = $db->sql();

			$query = 'select a.`id`, a.`unique_key`, a.`title`, b.`element`, a.`params`';
			$query .= ' from `#__social_fields` as a';
			$query .= ' inner join `#__social_fields_steps` as fs on a.`step_id` = fs.`id` and fs.`type` = ' . $db->Quote($fieldStepType);
			$query .= ' inner join `#__social_workflows_maps` as wm on wm.`workflow_id` = fs.`workflow_id` and wm.`type` = ' . $db->Quote($group) . ' and wm.`uid` = ' . $db->Quote($uid);

			if ($fieldStepType == 'profiles') {
				$query .= ' inner join `#__social_profiles` as p on wm.`uid` = p.`id`';
			} else {
				$query .= ' inner join `#__social_clusters_categories` as p on wm.`uid` = p.`id`';
			}

			$query .= ' inner join `#__social_apps` as b on a.`app_id` = b.`id` and b.`group` = ' . $db->Quote($group);
			$query .= ' where a.`searchable` = ' . $db->Quote('1');
			$query .= ' and a.`state` = ' . $db->Quote('1');
			$query .= ' and a.`unique_key` != ' . $db->Quote('');
			$query .= ' and p.`state` = ' . $db->Quote('1');

			$string = "'" . implode("','", $elements) . "'";
			$query .= ' and b.`element` IN (' . $string . ')';

			// fields ordering
			$query .= ' order by a.`step_id`, a.`ordering`';

			$sql->raw($query);

			// echo $sql;exit;

			$db->setQuery($sql);
			$results = $db->loadObjectList();
			$fields = array();

			$data[$idx] = false;

			// manual grouping / distinct
			if ($results) {
				foreach ($results as $result) {
					$field = ES::table('Field');
					$field->load($result->id);

					// Get the options
					if ($field->getOptions()) {
						$field->options = $field->getOptions()['items'];
					}

					$field->element = $result->element;
					$fields[] = $field;
					// $fields[$result->element] = $result;
				}

				$data[$idx] = $fields;
			}
		}

		return $data[$idx];
	}

	/**
	 * Get the title and value for the field options
	 *
	 * @since   3.0
	 * @access  public
	 */
	public static function getOptions($fieldId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_fields_options');
		$sql->where('parent_id', $fieldId);
		$sql->order('key');

		$db->setQuery($sql);

		return $db->loadObjectList();
	}

	/**
	 * Get the title and value for the field options
	 *
	 * @since   3.0
	 * @access  public
	 */
	public static function normalizeOperator($element, $filterMode)
	{
		if ($element == 'checkbox') {
			return $filterMode == 'equal' ? 'contain' : 'notcontain';
		}

		return $filterMode;
	}
}
