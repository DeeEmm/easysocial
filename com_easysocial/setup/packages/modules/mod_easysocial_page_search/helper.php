<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialModPageSearchHelper
{

	/**
	 * Retrieves page categories
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function formatData($days = array(), $start = '00:00', $end = '23:00')
	{
		$data = new stdClass();

		if (!$days) {
			// default day
			$data->day[] = 'all';
			$data->start = '00:00';
			$data->end = '24:00';
		} else {
			$cnt = count($days);

			for ($i = 0; $i < $cnt; $i++) {
				$data->day[] = $days[$i];
			}

			$data->start = $start;
			$data->end = $end;
		}



		return $data;
	}

	public static function hasHourField()
	{
		$db = ES::db();

		$query = "select `id` from `#__social_apps`";
		$query .= " where `type` = " . $db->Quote(SOCIAL_APPS_TYPE_FIELDS);
		$query .= " and `group` = " . $db->Quote(SOCIAL_FIELDS_GROUP_PAGE);
		$query .= " and element = " . $db->Quote('hours');
		$query .= " and state = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$db->setQuery($query);
		$result = $db->loadResult();

		return ($result) ? true : false;
	}


	/**
	 * Retrieves page categories
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function getCategories($ids)
	{
		$db = ES::db();

		$query = "select `id`, `title` from `#__social_clusters_categories`";
		$query .= " where `id` IN (" . implode(',', $ids) . ")";
		$query .= " and `state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query .= " order by `title` ASC";

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		return $categories;
	}

	/**
	 * Retrieves page creators
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function getCreators($ids = array())
	{
		$db = ES::db();

		$query = "select distinct b.`id`, b.`name` from `#__social_clusters` as a";
		$query .= " inner join `#__users` as b on a.`creator_uid` = b.`id` and a.`creator_type` = 'user'";
		$query .= " where a.`cluster_type` = " . $db->Quote(SOCIAL_TYPE_PAGE);
		$query .= " and a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
		if ($ids) {
			$query .= " and b.`id` IN (" . implode(',', $ids) . ")";
		}

		$query .= " order by b.`name` ASC";


		$db->setQuery($query);
		$authors = $db->loadObjectList();

		return $authors;
	}

	/**
	 * Retrieves the days in a week
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function getDays()
	{
		$items = array('MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY');
		$days = array();

		$i = 1;
		foreach ($items as &$item) {
			$day = new stdClass();
			$day->id = $i;
			$day->title = JText::_($item);
			$day->value = $i;

			$days[] = $day;

			$i++;
		}

		return $days;
	}

	/**
	 * Retrieves the hours
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function getHours()
	{
		$hours = array();

		for($i = 0; $i <= 24; $i++) {
			$text = str_pad($i, 2, '0', STR_PAD_LEFT);

			$option = new stdClass();
			$option->id = $text;

			$title = str_pad($i, 2, '0', STR_PAD_LEFT);
			$title = $title . ':00';

			$option->title = JText::_($title);
			$option->value = $text .':00';

			$hours[] = $option;
		}

		// midnight
		// $title = JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_MIDNIGHT');
		// $option = new stdClass();
		// $option->id = '23:59';
		// $option->title = JText::_($title);
		// $option->value = '23:59';
		// $hours[] = $option;


		return $hours;
	}

	public static function getPages(&$params)
	{
		$my = ES::user();
		$model = ES::model('Pages');

		// Get filter type
		$filter = $params->get('filter', 0);

		// Get the ordering of the pages
		$ordering = $params->get('ordering', 'latest');

		// Default options
		$options = array();

		// Limit the number of pages based on the params
		$options['limit'] = $params->get('display_limit', 5);
		$options['ordering'] = $ordering;
		$options['state'] = SOCIAL_STATE_PUBLISHED;
		$options['inclusion'] = $params->get('page_inclusion');

		$category = $params->get('category');

		if ($category) {
			$options['category'] = $category;
		}

		if ($filter == 0) {
			$pages = $model->getPages($options);
		}

		// Featured pages only
		if ($filter == 2) {
			$options['featured'] = true;

			$pages = $model->getPages($options);
		}

		// Pages from logged in user
		if ($filter == 3) {
			$options['types'] = 'currentuser';
			$options['userid'] = $my->id;
			$pages = $model->getPages($options);
		}

		return $pages;
	}
}
