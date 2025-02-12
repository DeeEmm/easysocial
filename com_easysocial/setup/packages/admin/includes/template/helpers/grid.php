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

class ThemesHelperGrid
{
	/**
	 * Renders a checkbox for each row.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function id($number, $id, $allowed = true, $checkedOut = false, $name = 'cid')
	{
		$theme = ES::themes();
		$theme->set('allowed', $allowed);
		$theme->set('number', $number);
		$theme->set('name', $name);
		$theme->set('checkedOut', $checkedOut);
		$theme->set('id', $id);

		$contents = $theme->output('admin/html/grid.id');

		return $contents;
	}

	/**
	 * Renders publish / unpublish icon.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function userActivation($allowed = true, $obj = null, $controllerName = '')
	{
		// By default the key is the `state` column
		$key = 'state';

		$publishTask = 'publish';
		$unpublishTask = 'unpublish';

		switch ($obj->$key) {
			case SOCIAL_USER_STATE_ENABLED:
				$class = 'publish';
				$tooltip = JText::_( 'COM_EASYSOCIAL_GRID_USERS_ACTIVATE_USER' );
				break;

			case SOCIAL_USER_STATE_DISABLED:
				$class = 'unpublish';
				$tooltip = JText::_( 'COM_EASYSOCIAL_GRID_USERS_DEACTIVATE_USER' );
				break;
		}

		if( !$allowed )
		{
			$tooltip = JText::_( 'You cannot block yourself' );
		}

		$task			= $obj->$key ? $unpublishTask : $publishTask;

		$theme 			= ES::get( 'Themes' );
		$theme->set( 'allowed'	, $allowed );
		$theme->set( 'class'	, $class );
		$theme->set( 'task'		, $task );
		$theme->set( 'tooltip'	, $tooltip );

		return $theme->output( 'admin/html/grid.published' );
	}

	/**
	 * Renders publish / unpublish icon.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function userPublished($allowed = true, $obj = null, $controllerName = '')
	{
		// By default the key is the `state` column
		$key = 'block';

		$publishTask = 'publish';
		$unpublishTask = 'unpublish';

		switch( $obj->$key )
		{
			case SOCIAL_JOOMLA_USER_UNBLOCKED:
				$class = 'publish';
				$tooltip = ES::_('COM_EASYSOCIAL_GRID_TOOLTIP_UNPUBLISH_USER', true);
				break;

			case SOCIAL_JOOMLA_USER_BLOCKED:
				$class = 'unpublish';
				$tooltip = ES::_('COM_EASYSOCIAL_GRID_TOOLTIP_PUBLISH_USER', true);
				break;
		}

		if (!$allowed) {
			$tooltip = JText::_('COM_EASYSOCIAL_GRID_NOT_ALLOWED_TO_BLOCK_YOURSELF');
		}

		$task = !$obj->$key ? $unpublishTask : $publishTask;

		$theme = ES::themes();
		$theme->set('allowed', $allowed);
		$theme->set('class', $class);
		$theme->set('task', $task);
		$theme->set('tooltip', $tooltip);
		$theme->set('controllerName', $controllerName);

		return $theme->output('admin/html/grid.published');
	}

	/**
	 * Renders publish / unpublish icon.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function published($obj, $controllerName = '', $key = '', $tasks = [], $tooltips = [], $classes = [], $allowed = true)
	{
		// If primary key is not provided, then we assume that we should use 'state' as the key property.
		$key = !empty($key) ? $key : 'state';

		// array_replace is only supported php>5.3
		// While array_replace goes by base, replacement
		// Using + changes the order where base always goes last

		$classes += [
			-1 => 'trash',
			0 => 'unpublish',
			1 => 'publish',
			2 => 'pending',
			4 => 'pending',
		];

		$tooltips += [
			-1 => 'COM_EASYSOCIAL_GRID_TOOLTIP_TRASHED_ITEM',
			0 => 'COM_EASYSOCIAL_GRID_TOOLTIP_PUBLISH_ITEM',
			1 => 'COM_EASYSOCIAL_GRID_TOOLTIP_UNPUBLISH_ITEM',
			4 => 'COM_EASYSOCIAL_GRID_TOOLTIP_SCHEDULED_ITEM'
		];

		$task = $tasks;

		if (is_array($tasks)) {
			$tasks += [
				-1 => 'publish',
				0 => 'publish',
				1 => 'unpublish'
			];

			$task = isset($tasks[$obj->$key]) ? $tasks[$obj->$key] : '';
		}

		$class = isset($classes[$obj->$key]) ? $classes[$obj->$key] : '';

		$tooltip = isset($tooltips[$obj->$key]) ? JText::_($tooltips[$obj->$key]) : '';

		$theme = ES::themes();
		$theme->set('allowed', $allowed);
		$theme->set('tooltip', $tooltip);
		$theme->set('task', $task);
		$theme->set('class', $class);
		$theme->set('controllerName', $controllerName);

		return $theme->output('admin/html/grid.published');
	}

	/**
	 * Renders the order save button in a grid
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function order($rows)
	{
		$count = count($rows);

		if (!$rows || !$count) {
			return '';
		}

		$theme = ES::themes();
		$theme->set('total', $count);

		$contents = $theme->output('admin/html/grid.order');
		return $contents;
	}

	/**
	 * Renders the up and down ordering in a grid
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function roworder($name, $index, $totalItems, $allowed = false)
	{
		$theme = ES::themes();

		$theme->set('total', $totalItems);
		$theme->set('allowed', $allowed);
		$theme->set('index', $index);

		$contents = $theme->output('admin/html/grid.roworder');

		return $contents;
	}

	/**
	 * Renders the ordering in a grid
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function ordering($total, $current, $showOrdering = true, $ordering = 0)
	{
		$theme = ES::themes();

		$theme->set('current', $current);
		$theme->set('total', $total);
		$theme->set('ordering', $ordering);
		$theme->set('showOrdering', $showOrdering);

		$contents = $theme->output('admin/html/grid.ordering');

		return $contents;
	}

	/**
	 * Renders the ordering in a grid
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function sort($column , $text , $currentOrdering , $direction )
	{
		$text = JText::_($text);
		$theme 	= ES::themes();

		// Ensure that the direction is always in lowercase because we will check for it in the theme file.
		$direction = ESJString::strtolower( $direction );
		$currentOrdering = ESJString::strtolower( $currentOrdering );
		$column = ESJString::strtolower( $column );

		$theme->set( 'column'	, $column );
		$theme->set( 'text'		, $text );
		$theme->set( 'currentOrdering'	, $currentOrdering );
		$theme->set( 'direction'		, $direction );

		$contents 	= $theme->output( 'admin/html/grid.sort' );

		return $contents;
	}

	/**
	 * Renders feature / unfeature icon.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function featured($obj , $controllerName = '' , $key = '' , $task = '' , $allowed = true , $tooltip = array() )
	{
		// If primary key is not provided, then we assume that we should use 'state' as the key property.
		$key	= !empty( $key ) ? $key : 'default';

		$task 	= !empty( $task ) ? $task : 'toggleDefault';

		switch( $obj->$key )
		{
			case SOCIAL_STATE_PUBLISHED:
				$class 		= 'featured';
				$tooltip 	= '';

				if( $allowed )
				{
					$tooltip	= isset( $tooltip[ 1 ] ) ? $tooltip[ 1 ] : ES::_('COM_EASYSOCIAL_GRID_TOOLTIP_UNFEATURE_ITEM', true);
				}


				break;

			default:
				$class 		= 'default';

				$tooltip	= isset( $tooltip[ 0 ] ) ? $tooltip[ 0 ] : ES::_('COM_EASYSOCIAL_GRID_TOOLTIP_FEATURE_ITEM', true);

				break;
		}

		$theme = ES::themes();
		$theme->set('task', $task);
		$theme->set('class', $class);
		$theme->set('tooltip', $tooltip);
		$theme->set('allowed', $allowed);
		$theme->set('controllerName', $controllerName);

		return $theme->output('admin/html/grid.published');
	}

	/**
	 * Renders a Yes / No input.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function boolean( $name , $value , $id = '' , $attributes = '' , $tips = array() , $text = array() )
	{
		// Load language strings from back end.
		JFactory::getLanguage()->load( 'com_easysocial' , JPATH_ROOT . '/administrator' );

		// Ensure that id is set.
		$id 		= empty( $id ) ? $name : $id;

		// Determine if the input should be checked.
		$checked	= $value ? true : false;

		$theme 		= ES::get( 'Themes' );

		if( is_array( $attributes ) )
		{
			$attributes	= implode( ' ' , $attributes );
		}

		$onText		= JText::_( 'COM_EASYSOCIAL_GRID_YES' );
		$offText 	= JText::_( 'COM_EASYSOCIAL_GRID_NO' );

		if( isset( $text[ 'on' ] ) )
		{
			$onText 	= $text[ 'on' ];
		}

		if( isset( $text[ 'off' ] ) )
		{
			$offText 	= $text[ 'off' ];
		}

		$theme->set( 'onText'	, $onText );
		$theme->set( 'offText'	, $offText );
		$theme->set( 'attributes' , $attributes );
		$theme->set( 'tips'		, $tips );
		$theme->set( 'id'		, $id );
		$theme->set( 'name'		, $name );
		$theme->set( 'checked' 	, $checked );

		return $theme->output( 'admin/html/grid.boolean' );
	}

	/**
	 * Renders a Yes / No input.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function checkbox($key , $value , $label = '' , $id = '', $attributes = '')
	{
		// Ensure that id is set.
		$id = empty($id) ? $key : $id;

		// Determine if the input should be checked.
		$checked = $value ? true : false;

		if (is_array($attributes)) {
			$attributes = implode(' ', $attributes);
		}

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('key', $key);
		$theme->set('label', $label);
		$theme->set('checked', $checked);
		$theme->set('attributes', $attributes);

		return $theme->output('admin/html/grid.checkbox');
	}

	/**
	 * Renders the check all checkbox
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function checkAll()
	{
		$themes = ES::themes();
		$output = $themes->output('admin/html/grid.checkAll');

		return $output;
	}

	/**
	 * Renders a select list
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function selectlist($name, $selected, $arr, $id = '', $attributes = '', $key = 'value', $text = 'text')
	{
		$options = array();

		// array should only contain option list.
		unset($arr['attributes']);


		if (count($arr) > 0) {

			foreach ($arr as $element) {

				if (is_array($element)) {
					$val = ( isset( $element[$key] ) ) ? $element[$key] : '';
					$txt = ( isset( $element[$text] ) ) ? $element[$text] : '';
				} else {
					$val = $element->$key;
					$txt = $element->$text;
				}

				// ensure ampersands are encoded
				$val = JFilterOutput::ampReplace($val);
				$txt = JFilterOutput::ampReplace($txt);

				$options[$val] = JText::_($txt);
			}
		}

		// Bind classes
		$class = 'o-form-control';

		if (is_array($attributes) && isset($attributes['class'])) {
			$class .= ' ' . $attributes['class'];

			unset($attributes['class']);
		}

		$isMultiple = false;

		if (is_array($attributes)) {
			if( isset( $attributes['multiple'] ) )
			{
				$isMultiple = true;
				unset( $attributes['multiple'] );
			}

			$attributes	= implode( ' ' , $attributes );
		}

		$theme = ES::themes();
		$theme->set('class', $class);
		$theme->set('options', $options);
		$theme->set('selected', $selected);
		$theme->set('name', $name );
		$theme->set('id', $id );
		$theme->set('attributes', $attributes);
		$theme->set('ismultiple', $isMultiple);

		return $theme->output('admin/html/grid.selectlist');
	}

	/**
	 * Renders a input box
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function inputbox($name, $value = '', $id = '', $attributes = '')
	{
		$defaultClass = 'o-form-control';

		if (is_array($attributes) && isset($attributes['class'])) {
			$defaultClass = 'o-form-control ' . $attributes['class'];

			unset($attributes['class']);
		}

		if (is_array($attributes)) {
			$attributes = implode(' ', $attributes);
		}

		// If value is an array, implode it with a comma as a separator
		if (is_array($value)) {
			$value 	= implode(',', $value);
		}

		$theme = ES::themes();
		$theme->set('defaultClass', $defaultClass);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('id', $id);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/html/grid.inputbox');

		return $output;
	}

	/**
	 * Renders a textarea
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function textarea($name, $value = '', $id = '', $attributes = '')
	{
		if (is_array($attributes)) {
			$attributes	= implode(' ' , $attributes);
		}

		$theme = ES::get('Themes');
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('id', $id);
		$theme->set('attributes', $attributes);

		return $theme->output('admin/html/grid.textarea');
	}

	/**
	 * Renders a hidden input
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hidden( $name, $value = '', $id = '', $attributes = '' )
	{
		if( is_array( $attributes ) )
		{
			$attributes	= implode( ' ' , $attributes );
		}

		$theme = ES::get( 'Themes' );
		$theme->set( 'name'			, $name );
		$theme->set( 'value'		, $value );
		$theme->set( 'id'			, $id );
		$theme->set( 'attributes'	, $attributes );

		return $theme->output( 'admin/html/grid.hidden' );
	}

	/**
	 * Renders a date form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function dateform( $name, $value = '', $id = '', $attributes = '', $withOffset = true)
	{
		if (is_array($attributes)) {
			$attributes	= implode( ' ' , $attributes );
		}

		if (empty($value)) {
			$value = ES::date('now', $withOffset);
		} else {
			$value = ES::date($value, $withOffset);
		}

		$theme = ES::get( 'Themes' );
		$theme->set( 'name'			, $name );
		$theme->set( 'value'		, $value );
		$theme->set( 'id'			, $id );
		$theme->set( 'attributes'	, $attributes );

		return $theme->output( 'admin/html/grid.dateform' );
	}

}
