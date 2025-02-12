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

class ThemesHelperPanel
{
	/**
	 * Renders the panel heading at the back-end
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function heading($text, $description = '', $helpLink = '')
	{
		if (!$description) {
			$description = $text . '_DESC';
		}

		$header = JText::_($text);
		$description = JText::_($description);

		if ($helpLink) {
			$helpLink = 'https://stackideas.com/docs/easysocial/' . ltrim($helpLink, '/');
		}

		$theme = ES::themes();
		$theme->set('header', $text);
		$theme->set('desc', $description);
		$theme->set('helpLink', $helpLink);

		$output = $theme->output('admin/html/panel/heading');

		return $output;
	}

	/**
	 * Generates a settings row in the panel body
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function label($text, $help = true, $helpText = '', $columns = 5, $required = false)
	{
		if ($help && !$helpText) {
			$helpText = JText::_($text . '_HELP');

			// Added backward compactibilty.
			if ($helpText == $text . '_HELP') {
				$helpText = JText::_($text . '_DESC');
			}
		}

		$text = JText::_($text);

		// Generate a short unique id for each label
		$uniqueId = ESJString::substr(md5($text), 0, 16);

		$theme = ES::themes();
		$theme->set('columns', $columns);
		$theme->set('uniqueId', $uniqueId);
		$theme->set('text', $text);
		$theme->set('help', $help);
		$theme->set('helpText', $helpText);
		$theme->set('required', $required);

		$output = $theme->output('admin/html/panel/label');

		return $output;
	}

	/**
	 * Generates a standard form line for a form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function formInput($title, $name, $value = '', $desc = '')
	{
		$desc = !$desc ? $title . '_DESC' : $desc;
		$desc = JText::_($desc);
		$title = JText::_($title);

		$theme = ES::themes();

		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('name', $name);
		$theme->set('value', $value);

		return $theme->output('admin/html/panel/form.input');
	}


	/**
	 * Generates a standard form line for a form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function formBoolean($title, $name, $value = '', $desc = '', $readonly = false)
	{
		$desc = !$desc ? $title . '_DESC' : $desc;
		$desc = JText::_($desc);
		$title = JText::_($title);

		$theme = ES::themes();

		$theme->set('title', $title);
		$theme->set('desc', $desc);
		$theme->set('name', $name);
		$theme->set('value', $value);

		$attributes = '';
		if ($readonly) {
			$attributes = ' disabled="disabled"';
		}

		$theme->set('attributes', $attributes);

		return $theme->output('admin/html/panel/form.boolean');
	}
}
