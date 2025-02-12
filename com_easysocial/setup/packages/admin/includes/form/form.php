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

class SocialForm extends EasySocial
{
	private $form	= null;
	private $params = [];

	public static function factory()
	{
		return new self();
	}

	/**
	 * Loads the form data
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load($data = null)
	{
		$this->form = ES::makeObject($data);
	}

	/**
	 * Bind the params
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function bind($params = null)
	{
		if (is_object($params)) {
			$this->params = $params;

			return;
		}

		if (is_file($params)) {
			$params = file_get_contents($params);
		}

		$this->params = ES::registry($params);
	}


	/**
	 * Renders the form's output
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function render($tabs = false, $sidebarTabs = false, $active = '', $prefix = '', $processActiveTab = true)
	{
		$theme = ES::themes();
		$type = $tabs ? 'tabs' : 'default';

		if (!$this->form) {
			return;
		}

		// Replacements for invalid keys
		$invalidKeys = array(' ', ',', '&', '.', '*', "'");

		$i = 0;

		foreach ($this->form as &$form) {

			$form->title = JText::_($form->title);
			$form->id = strtolower(str_ireplace($invalidKeys, '', $form->title));
			$form->desc = !isset($form->desc) ? false : JText::_($form->desc);

			// Determines if the form is active
			$form->active = ($i == 0 && !$active) ? true : false;

			if ($active && $active == $form->id) {
				$form->active = true;
			}

			if (!isset($form->fields)) {
				$form->fields = array();
			}

			foreach ($form->fields as &$field) {

				// Normalize properties
				$field->label = !isset($field->label) ? false : $field->label;
				$field->default = !isset($field->default) ? false : $field->default;
				$field->suffix = !isset($field->suffix) ? '' : $field->suffix;
				$field->suffix = JText::_($field->suffix);
				$field->inputName = $prefix ? $prefix . '[' . $field->name . ']' : $field->name;
				$field->output = isset($field->output) ? $field->output : false;

				// Custom renderer based on type
				// Need to support apps renderer as well
				// apps:/path/to/file or apps:/[group]/[element]/renderer/[nameInCamelCase]
				// class SocialFormRenderer[NameInCamelCase]
				$rendererType = $field->type;
				$file = dirname(__FILE__) . '/renderer/' . strtolower($rendererType) . '.php';

				// Check for :
				if (strpos($field->type, ':') !== false) {

					list($protocol, $path) = explode(':', $field->type);

					$segments = explode('/', $path);

					$rendererType = array_pop($segments);

					$base = defined('SOCIAL_' . strtoupper($protocol)) ? constant('SOCIAL_' . strtoupper($protocol)) : SOCIAL_ADMIN;

					$file = $base . $path . '.php';
				}

				if (file_exists($file)) {
					require_once($file);

					$className 	= 'SocialFormRenderer' . ucfirst($rendererType);

					if (class_exists($className)) {
						$renderer = new $className;

						$renderer->render($field, $this->params);
					}
				}
			}

			$i++;
		}

		// Generate unique id so multiple tabs form on a single page would not conflict
		$uid = uniqid();

		$theme->set('invalidKeys', $invalidKeys);
		$theme->set('uid', $uid);
		$theme->set('processActiveTab', $processActiveTab);
		$theme->set('prefix', $prefix);
		$theme->set('active', $active);
		$theme->set('sidebarTabs', $sidebarTabs);
		$theme->set('tabs', $tabs);
		$theme->set('params', $this->params);
		$theme->set('forms', $this->form);

		$template = 'admin/forms/' . $type;

		$contents = $theme->output($template);

		return $contents;
	}
}
