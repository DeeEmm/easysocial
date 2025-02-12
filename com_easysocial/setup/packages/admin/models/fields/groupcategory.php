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

require_once(__DIR__ . '/abstract.php');

class JFormFieldEasySocial_GroupCategory extends JFormFieldEasySocial
{
	protected $type = 'EasySocial_GroupCategory';

	protected function getInput()
	{
		$this->page->start();

		$label = (string) $this->element['label'];
		$name = (string) $this->name;
		$title = JText::_('COM_EASYSOCIAL_JFIELD_SELECT_GROUP_CATEGORY');

		if ($this->value) {
			$category = ES::table('GroupCategory');
			$category->load($this->value);

			$title = $category->get('title');
		}

		static $loaded = false;

		$theme = ES::themes();
		$theme->set('loaded', $loaded);
		$theme->set('name', $name);
		$theme->set('id', $this->id);
		$theme->set('value', $this->value);
		$theme->set('label', $label);
		$theme->set('title', $title);
		$theme->set('isJoomla4', ES::isJoomla4());
		$output	= $theme->output('admin/jfields/groupcategory');

		$loaded = true;

		$this->page->end();

		return $output;
	}
}