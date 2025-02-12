<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class JFormFieldEasySocial_Note extends JFormFieldEasySocial
{
	protected $type = 'EasySocial_Note';

	/**
	 * Display note. Same implemention of Joomla JField Type - Note
	 *
	 * @since	3.1
	 * @access	public
	 */
	protected function getLabel()
	{
		if (empty($this->element['label']) && empty($this->element['description'])) {
			return '';
		}

		$title = $this->element['label'] ? (string) $this->element['label'] : ($this->element['title'] ? (string) $this->element['title'] : '');
		$heading = $this->element['heading'] ? (string) $this->element['heading'] : 'h4';
		$description = (string) $this->element['description'];
		$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$close = (string) $this->element['close'];

		$html = array();

		if ($close) {
			$close = $close == 'true' ? 'alert' : $close;
			$html[] = '<button type="button" class="close" data-dismiss="' . $close . '">&times;</button>';
		}

		$html[] = !empty($title) ? '<' . $heading . '>' . JText::_($title) . '</' . $heading . '>' : '';
		$html[] = !empty($description) ? JText::_($description) : '';

		return '</div><div ' . $class . '>' . implode('', $html);
	}

	protected function getInput()
	{
		return '';
	}
}
