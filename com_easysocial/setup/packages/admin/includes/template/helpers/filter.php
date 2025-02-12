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

class ThemesHelperFilter
{
	/**
	 * Renders the user's group tree
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function lists($items = array(), $name = 'listitem', $selected = 'all', $initial = '', $initialValue = 'all')
	{
		$theme = ES::themes();

		$initial = JText::_($initial);

		$theme->set('initialValue', $initialValue);
		$theme->set('initial', $initial);
		$theme->set('name', $name);
		$theme->set('items', $items);
		$theme->set('selected', $selected);

		$contents = $theme->output('admin/html/filter.list');

		return $contents;
	}

	/**
	 * Renders the user's group tree
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function published($name = 'state', $selected = 'all', $extras = [])
	{
		$theme = ES::themes();

		$options = array();

		if ($extras) {
			foreach ($extras as $extra) {
				$obj = (object) $extra;

				$options[] = $obj;
			}
		}

		$theme->set('name', $name);
		$theme->set('selected', $selected);
		$theme->set('options', $options);

		$contents = $theme->output('admin/html/filter.published');

		return $contents;
	}

	/**
	 * Displays a search box in the filter
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function search($value = '', $name = 'search')
	{
		$theme = ES::themes();

		$theme->set('value', $value);
		$theme->set('name', $name);

		$contents = $theme->output('admin/html/filters/search');

		return $contents;
	}

	/**
	 * Displays the number of items per page selection
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function limit($selected = 5, $name = 'limit', $step = 5, $min = 5, $max = 100, $showAll = true)
	{
		$theme = ES::themes();

		$theme->set('selected', $selected);
		$theme->set('name', $name);
		$theme->set('step', $step);
		$theme->set('min', $min);
		$theme->set('max', $max);
		$theme->set('showAll', $showAll);

		$contents = $theme->output('admin/html/filter.limit');

		return $contents;
	}

	/**
	 * Renders the pending users filter
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function pendingUsers($name, $selected, $attributes = array())
	{
		$attributes = !empty($attributes) ? implode(' ', $attributes) : '';
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('COM_EASYSOCIAL_FILTER_SELECT_PENDING_STATE'));
		$options[] = JHTML::_('select.option', 'all', JText::_('COM_EASYSOCIAL_FILTER_ALL_PENDING_ACCOUNTS'));
		$options[] = JHTML::_('select.option', 'pending', JText::_('COM_EASYSOCIAL_FILTER_PENDING_APPROVAL'));
		$options[] = JHTML::_('select.option', 'verify', JText::_('COM_EASYSOCIAL_FILTER_PENDING_ACTIVIATION'));
		$options[] = JHTML::_('select.option', 'confirmation_approval', JText::_('COM_ES_FILTER_PENDING_CONFIRMATION_EMAIL_ACCOUNT'));

		$dropdown = JHtml::_('select.genericlist', $options, $name, $attributes . ' data-table-grid-filter="" class="o-form-control"', 'value', 'text', $selected);

		return $dropdown;
	}

	/**
	 * Renders a select list for profiles
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function profiles($name, $selected, $attributes = array())
	{
		$model = ES::model('Profiles');
		$profiles = $model->getProfileFilters(array('showCount' => false));

		$attributes = !empty($attributes) ? implode(' ', $attributes) : '';

		$childs[] = JHTML::_('select.option', -1, JText::_('COM_EASYSOCIAL_FILTER_SELECT_PROFILE'));

		foreach ($profiles as $profile) {
			$count = $profile->count;
			$title = $profile->title;

			$title = $count > 0 ? $title . ' (' . $count . ')' : $title;

			$childs[] = JHTML::_('select.option', $profile->id, $title);
		}

		//get un-assigned users
		$orphanCount = $model->getOrphanMembersCount(false);

		if ($orphanCount) {
			$title = JText::_('COM_EASYSOCIAL_FILTER_UNASSIGNED_PROFILE') . ' (' . $orphanCount . ')';
			$childs[] = JHTML::_('select.option', -2, $title);
		}

		return JHtml::_('select.genericlist', $childs, $name, $attributes . ' data-table-grid-filter="" class="o-form-control"', 'value', 'text', $selected);
	}

	/**
	 * Renders a select list for cluster categories
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function clusterCategories($name, $selected, $clusterType, $attribute = array())
	{
		$model = ES::model('GroupCategories');

		if ($clusterType == 'event') {
			$model = ES::model('EventCategories');
		}

		if ($clusterType == 'page') {
			$model = ES::model('PageCategories');
		}

		$categories = $model->getCategories();
		$attributes = !empty($attributes) ? implode(' ', $attributes) : '';

		$childs[] = JHTML::_('select.option', -1, JText::_('COM_EASYSOCIAL_FILTER_SELECT_CATEGORY'));

		foreach ($categories as $category) {
			$title = $category->getTitle();

			// If the category title exceeds an insane length, we'll truncate it or the dropdown would be messed up
			if (ESJString::strlen($title) > 60) {
				$title = ESJString::substr($title, 0, 60) . JText::_('COM_EASYSOCIAL_ELLIPSES');
			}

			$childs[] = JHTML::_('select.option', $category->id, $title);
		}

		return JHtml::_('select.genericlist', $childs, $name, $attributes . ' data-table-grid-filter="" class="o-form-control"', 'value', 'text', $selected);
	}

	/**
	 * Renders the user's group tree
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function usergroups($name, $selected = false, $exclude = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__usergroups', 'a');
		$sql->column('a.id', 'value');
		$sql->column('a.title', 'text');
		$sql->column('b.id', 'level', 'count distinct');

		$sql->leftjoin('#__usergroups', 'b');
		$sql->on('a.lft', 'b.lft', '>');
		$sql->on('a.rgt', 'b.rgt', '<');

		if (!empty($exclude)) {
			$sql->where('a.id', $exclude, 'not in');
		}

		// This only applies to joomla 1.6 onwards
		$sql->group('a.id', 'a.title', 'a.lft', 'a.rgt');
		$sql->order('a.lft', 'asc');

		$db->setQuery($sql);
		$options = $db->loadObjectList();

		// If there's an error, throw the error.
		if ($db->getErrorNum()) {
			throw ES::exception($db->getErrorMsg(), 500);
		}

		$childs = array();
		$childs[] = JHTML::_('select.option', -1, JText::_('COM_EASYSOCIAL_FILTER_SELECT_GROUP'));

		foreach ($options as &$option) {
			$text = str_repeat('- ', $option->level) . $option->text;
			$childs[] = JHTML::_('select.option', $option->value, $text);
		}

		return JHtml::_('select.genericlist', $childs, $name, ' data-table-grid-filter="" class="o-form-control"', 'value', 'text', $selected);
	}
}
