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

class JFormFieldEasySocial_User extends JFormFieldEasySocial
{
	protected $type = 'EasySocial_User';

	protected function getInput()
	{
		$this->page->start();

		$label = (string) $this->element['label'];
		$name = (string) $this->name;
		$title = JText::_('COM_EASYSOCIAL_JFIELD_SELECT_USER');

		if ($this->value) {

			$id = (int) $this->value;

			if (! $id) {
				$model = ES::model('Users');
				$id = (int) $model->getUserIdFromAlias($this->value);

				if ($id) {
					$this->value = $id . ':' . $this->value;
				}
			}

			$user = ES::user($id);
			$title = $user->getName();
		}

		static $loaded = false;

		$theme = ES::themes();
		$theme->set('name', $name);
		$theme->set('id', $this->id);
		$theme->set('value', $this->value);
		$theme->set('label', $label);
		$theme->set('title', $title);
		$theme->set('loaded', $loaded);
		$theme->set('isJoomla4', ES::isJoomla4());

		$output = $theme->output('admin/jfields/user');

		$loaded = true;

		$this->page->end();

		return $output;
	}
}
