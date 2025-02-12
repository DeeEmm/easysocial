<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialAdvancedSearchHelperUser extends SocialAdvancedSearchHelperAbstract
{
	public $type = 'user';

	public function getLink()
	{
		$link = ESR::search(array('layout' => 'advanced'));

		return $link;
	}

	public function search($options = array())
	{
		$limit = $this->normalize($options, 'limit', ES::getLimit('search_limit'));
		$nextlimit = $this->normalize($options, 'nextlimit', 0);

		$model = ES::model('Search');
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
		$query .= ' inner join `#__social_fields_steps` as fs on a.`step_id` = fs.`id` and fs.`type` = ' . $db->Quote('profiles');
		$query .= ' inner join `#__social_workflows` as w on fs.`workflow_id` = w.`id` and w.`type` = ' . $db->Quote('user');
		$query .= ' inner join `#__social_apps` as b on a.`app_id` = b.`id`';
		$query .= ' where a.`searchable` = ' . $db->Quote('1');
		$query .= ' and a.`state` = ' . $db->Quote('1');
		$query .= ' and a.`unique_key` = ' . $db->Quote($key);
		$query .= ' and b.`group` = ' . $db->Quote('user');
		$query .= ' and b.`element` = ' . $db->Quote($element);

		$db->setQuery($query);
		$fields = $db->loadObjectList();

		return $fields;
	}

	public function getFields($elements = array())
	{
		// load backend custom fields language strings.
		JFactory::getLanguage()->load('com_easysocial' , JPATH_ROOT . '/administrator');

		static $fields = null;

		if (! $fields) {
			$db 	= ES::db();
			$sql 	= $db->sql();

			$query = 'select a.`unique_key`, a.`title`, b.`element`';
			$query .= ' from `#__social_fields` as a';
			$query .= ' inner join `#__social_fields_steps` as fs on a.`step_id` = fs.`id` and fs.`type` = ' . $db->Quote('profiles');
			$query .= ' inner join `#__social_workflows` as w on fs.`workflow_id` = w.`id` and w.`type` = ' . $db->Quote('user');
			$query .= ' inner join `#__social_apps` as b on a.`app_id` = b.`id` and b.`group` = ' . $db->Quote('user');
			$query .= ' where a.`searchable` = ' . $db->Quote('1');
			$query .= ' and a.`state` = ' . $db->Quote('1');
			$query .= ' and a.`unique_key` != ' . $db->Quote('');

			// Exclude fields that are not searchable
			$query .= " AND b.`element` NOT IN ('" . implode("','", $this->getFieldExclusion()) . "')";

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

	/**
	 * Lists of fields that should not be searchable based on element.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getFieldExclusion()
	{
		return array(
			'avatar',
			'code_generator',
			'cover',
			'file',
			'header',
			'html',
			'mailchimp',
			'password',
			'recaptcha',
			'separator',
			'terms',
			'text',
			'joomla_user_editor'
		);
	}
}
