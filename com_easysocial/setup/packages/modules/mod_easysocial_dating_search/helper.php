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

class EasySocialModDatingSearchHelper
{
	/**
	 * Retrieve supported user's custom fields.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public static function getFields(&$params)
	{
		$db = ES::db();
		$sql = $db->sql();

		static $fields = null;

		if (!$fields) {
			// later we need to respect the module settings.
			$elements = ['address','birthday','gender','joomla_fullname', 'joomla_username', 'relationship'];

			$db = ES::db();
			$sql = $db->sql();

			$query = 'select a.`unique_key`, a.`title`, b.`element`, a.`params`';
			$query .= ' from `#__social_fields` as a';
			$query .= ' inner join `#__social_fields_steps` as fs on a.`step_id` = fs.`id` and fs.`type` = ' . $db->Quote('profiles');
			$query .= ' inner join `#__social_workflows_maps` as wm on wm.`workflow_id` = fs.`workflow_id` and wm.`type` = ' . $db->Quote('user');
			$query .= ' inner join `#__social_profiles` as p on wm.`uid` = p.`id`';
			$query .= ' inner join `#__social_apps` as b on a.`app_id` = b.`id` and b.`group` = ' . $db->Quote('user');
			$query .= ' where a.`searchable` = ' . $db->Quote('1');
			$query .= ' and a.`state` = ' . $db->Quote('1');
			$query .= ' and a.`unique_key` != ' . $db->Quote('');
			$query .= ' and p.`state` = ' . $db->Quote('1');

			$string = "'" . implode("','", $elements) . "'";
			$query .= ' and b.`element` IN (' . $string . ')';

			$sql->raw($query);

			// echo $sql;exit;

			$db->setQuery($sql);
			$results = $db->loadObjectList();

			// manual grouping / distinct
			if ($results) {
				foreach ($results as $result) {
					//$fields[ $result->unique_key ] = $result;
					$fields[ $result->element ] = $result;
				}
			}
		}

		return $fields;
	}

	/**
	 * Generate options for relationship field
	 *
	 * @since	3.1
	 * @access	public
	 */
	public static function getRelationshipOptions($relationshipField)
	{
		$options = [];
		$json = ES::json();

		$ignoreStatus = ['relationship', 'engaged', 'married', 'complicated'];
		$relationshipParams = $json->decode($relationshipField->params);
		$shownRelationship = [];

		if ($relationshipParams && isset($relationshipParams->relationshiptype)) {
			$shownRelationship = $relationshipParams->relationshiptype;
		}

		// load up relationship options
		$file = JPATH_ROOT . '/media/com_easysocial/apps/fields/user/relationship/config/config.json';
		$contents = file_get_contents($file);

		$data = $json->decode($contents);

		if ($data && isset($data->relationshiptype) && isset($data->relationshiptype->option)) {
			foreach ($data->relationshiptype->option as $item) {

				if ($shownRelationship && !in_array($item->value, $shownRelationship)) {
					continue;
				}

				// we do not want to show those status with targe,
				// e.g. 'relationship with ...', 'engaged with ...' and etc.

				if (!in_array($item->value, $ignoreStatus)) {
					$obj = new stdClass();
					$obj->title = $item->value == 'open' ? JText::_('MOD_EASYSOCIAL_DATING_OPEN_RELATIONSHIP') : JText::_($item->label);

					// now we remove the 'notarget' so that the search will match 'relationship' and 'relationshipnotarget' due to
					// relationship are using LIKE query search.

					$obj->value = str_replace('notarget', '', $item->value);
					$options[] = $obj;
				}
			}
		}

		return $options;
	}

	/**
	 * Generate options for gender field
	 *
	 * @since	2.0
	 * @access	public
	 */
	public static function getGenderOptions($genderField)
	{
		$custom = $genderField->custom;

		$fieldCode = $genderField->unique_key;
		$fieldType = $genderField->element;

		$options = [];

		// Male
		$obj = new stdClass();
		$obj->title = JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_MALE');
		$obj->value = '1';
		$options[] = $obj;

		// Female
		$obj = new stdClass();
		$obj->title = JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_FEMALE');
		$obj->value = '2';
		$options[] = $obj;

		if ($custom) {
			$model = ES::model('Search');
			$items = $model->getFieldOptionList($fieldCode, $fieldType);
			if ($items) {
				foreach ($items as $item) {

					if (!$item->value) {
						continue;
					}

					$obj = new stdClass();
					$obj->title = JText::_($item->title);
					$obj->value = $item->value;
					$options[] = $obj;
				}
			}

			// Other
			$obj = new stdClass();
			$obj->title = JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_GENDER_OTHERS');
			$obj->value = '3';
			$options[] = $obj;
		}

		return $options;
	}
}
