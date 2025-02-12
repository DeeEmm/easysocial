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

class ThemesHelperSettings
{
	/**
	 * Renders a color picker settings
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function colorpicker($name, $title, $desc = '', $reset = '', $wrapperAttributes = '', $options = array())
	{
		$theme = ES::themes();

		if (empty($desc)) {
			$desc = $title . '_DESC';
		}

		if (is_array($wrapperAttributes)) {
			$wrapperAttributes = implode(' ', $wrapperAttributes);
		}

		$wrapperClass = '';

		if (isset($options['wrapperClass'])) {
			$wrapperClass = $options['wrapperClass'];
		}

		$theme->set('wrapperClass', $wrapperClass);
		$theme->set('reset', $reset);
		$theme->set('name', $name);
		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('wrapperAttributes', $wrapperAttributes);

		$contents = $theme->output('admin/html/settings/colorpicker');

		return $contents;
	}

	/**
	 * Renders a dropdown settings
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function dropdown($name, $title, $desc = '', $options = array(), $attributes = '', $notes = '')
	{
		$theme = ES::themes();

		if (empty($desc)) {
			$desc = $title . '_DESC';
		}

		if ($notes) {
			$notes = JText::_($notes);
		}

		$theme = ES::themes();
		$theme->set('options', $options);
		$theme->set('notes', $notes);
		$theme->set('name', $name);
		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('attributes', $attributes);

		$contents = $theme->output('admin/html/settings/dropdown');

		return $contents;
	}

	/**
	 * Renders a textbox for settings
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public static function textbox($name, $title, $desc = '', $options = array(), $instructions = '', $class = '', $wrapperAttributes = '')
	{
		$theme = ES::themes();

		if (empty($desc)) {
			$desc = $title . '_DESC';
		}

		$size = ES::normalize($options, 'size', '');
		$postfix = ES::normalize($options, 'postfix', '');
		$prefix = ES::normalize($options, 'prefix', '');
		$attributes = ES::normalize($options, 'attributes', '');
		$type = ES::normalize($options, 'type', 'text');
		$wrapperClass = ES::normalize($options, 'wrapperClass', '');

		if (!$size && ($postfix || $prefix)) {
			$size = 6;
		}

		$theme->set('wrapperClass', $wrapperClass);
		$theme->set('wrapperAttributes', $wrapperAttributes);
		$theme->set('attributes', $attributes);
		$theme->set('type', $type);
		$theme->set('size', $size);
		$theme->set('class', $class);
		$theme->set('instructions', $instructions);
		$theme->set('name', $name);
		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('prefix', $prefix);
		$theme->set('postfix', $postfix);

		$contents = $theme->output('admin/html/settings/textbox');

		return $contents;
	}

	/**
	 * Renders a textbox for settings
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public static function textarea($name, $title, $desc = '', $options = array(), $instructions = '', $class = '')
	{
		$theme = ES::themes();

		if (empty($desc)) {
			$desc = $title . '_DESC';
		}

		$attributes = '';
		$rows = 5;

		if (isset($options['attributes'])) {
			$attributes = $options['attributes'];
		}

		if (isset($options['rows'])) {
			$rows = $options['rows'];
		}

		$theme->set('rows', $rows);
		$theme->set('attributes', $attributes);
		$theme->set('class', $class);
		$theme->set('instructions', $instructions);
		$theme->set('name', $name);
		$theme->set('title', $title);
		$theme->set('desc', $desc);

		$contents = $theme->output('admin/html/settings/textarea');

		return $contents;
	}

	/**
	 * Renders a toggle button
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function toggle($name, $title, $desc = '', $attributes = '', $note = '', $wrapperAttributes = '', $wrapperClass = '')
	{
		$theme = ES::themes();

		if (empty($desc)) {
			$desc = $title . '_DESC';
		}

		if ($note) {
			$note = JText::_($note);
		}

		if (is_array($wrapperAttributes)) {
			$wrapperAttributes = implode(' ', $wrapperAttributes);
		}

		if (is_array($wrapperClass)) {
			$wrapperClass = implode(' ', $wrapperClass);
		}

		$theme->set('note', $note);
		$theme->set('name', $name);
		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('attributes', $attributes);
		$theme->set('wrapperAttributes', $wrapperAttributes);
		$theme->set('wrapperClass', $wrapperClass);

		$contents = $theme->output('admin/html/settings/toggle');

		return $contents;
	}
}
