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

ES::import('site:/views/views');

class EasySocialViewFields extends EasySocialSiteView
{
	public function display($tpl = null)
	{
		$id = $this->input->get('id', 0, 'int');
		$task = $this->input->get('task', '', 'word');

		$field = ES::table('Field');
		$exists = $field->load($id);

		if (!$exists) {
			return $this->exception('COM_EASYSOCIAL_FIELDS_INVALID_ID');
		}

		// Get the app for this field
		$app = $field->getApp();

		if (!$app) {
			return $this->exception('COM_EASYSOCIAL_FIELDS_APP_DOES_NOT_EXIST');
		}

		$base = SOCIAL_FIELDS . '/' . $app->group . '/' . $app->element . '/views';
		$classname = 'SocialFieldView' . ucfirst($app->group) . ucfirst($app->element);

		

		if (!class_exists($classname)) {

			$viewFileExists = JFile::exists($base . '/' . $app->element . '.php');

			if (!$viewFileExists) {
				return $this->exception(JText::sprintf('COM_EASYSOCIAL_FIELDS_VIEW_DOES_NOT_EXIST', $app->element));
			}

			require_once($base . '/' . $app->element . '.php');
		}

		if (!class_exists($classname)) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_FIELDS_CLASS_DOES_NOT_EXIST', $classname));
		}

		$view = new $classname($app->group, $app->element);

		if (!is_callable(array($view, $task))) {
			return $this->exception(JText::sprintf('COM_EASYSOCIAL_FIELDS_METHOD_DOES_NOT_EXIST', $task));
		}

		$view->init($field);

		return $view->$task();
	}
}
