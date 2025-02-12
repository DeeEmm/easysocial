<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsEventDescription extends SocialFieldItem
{
	/**
	 * Support for generic getFieldValue('DESCRIPTION')
	 *
	 * @since  1.3.9
	 * @access public
	 */
	public function getValue()
	{
		$container = $this->getValueContainer();

		if ($this->field->type == SOCIAL_TYPE_EVENT && !empty($this->field->uid)) {
			$event = ES::event($this->field->uid);

			$container->value = $event->getDescription();

			$container->data = $event->description;
		}

		return $container;
	}

	/**
	 * Displays the field for edit.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEdit(&$post, &$cluster, $errors)
	{
		$desc = $this->input->get($this->inputName, $cluster->description, 'raw');
		$desc = ES::string()->filterHtml($desc);

		// Get the error.
		$error = $this->getError($errors);

		// Get the editor
		$editor = $this->getEditor();

		// Retrieve for the editor name
		$editorName = $this->getEditorName();

		$this->set('editorName', $editorName);
		$this->set('editor', $editor);
		$this->set('value', $desc);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Displays the field for admin edit.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onAdminEdit(&$post, &$cluster, $errors)
	{
		$clusterDesc = JText::_($this->params->get('default'), true);

		if ($cluster->id) {
			$clusterDesc = $cluster->description;
		}

		$desc = $this->input->get($this->inputName, $clusterDesc, 'raw');
		$desc = ES::string()->filterHtml($desc);

		$error = $this->getError($errors);

		// Get the editor.
		$editor = $this->getEditor();

		// Retrieve for the editor name
		$editorName = $this->getEditorName();

		$this->set('editorName', $editorName);
		$this->set('editor', $editor);
		$this->set('value', $desc);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Responsible to output the html codes that is displayed to a user.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onDisplay($cluster)
	{
		// Push variables into theme.
		$value = $cluster->getDescription();

		if (!$value) {
			return;
		}

		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get the value from posted data if it's available.
		$desc = $this->input->get($this->inputName, $this->params->get('default'), 'raw');
		$desc = ES::string()->filterHtml($desc);

		// Try to retrieve the value from post data
		if (!$desc) {
			if (isset($post[$this->inputName])) {
				$desc = $post[$this->inputName];
			}
		}

		// Get any errors for this field.
		$error = $registration->getErrors($this->inputName);

		// Get the editor that is configured
		$editor = $this->getEditor();

		// Retrieve for the editor name
		$editorName = $this->getEditorName();

		$this->set('editorName', $editorName);
		$this->set('editor', $editor);
		$this->set('value', $desc);
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Validates the event creation
	 *
	 * @since   1.4.9
	 * @access  public
	 */
	public function onRegisterValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$valid = $this->validate($value);

		return $valid;
	}

	/**
	 * Validates the event editing
	 *
	 * @since   1.4.9
	 * @access  public
	 */
	public function onEditValidate(&$post)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$valid = $this->validate($value);

		return $valid;
	}

	/**
	 * General validation function
	 *
	 * @since   1.4.9
	 * @access  public
	 */
	private function validate($value)
	{
		if ($this->isRequired() && empty($value)) {
			return $this->setError(JText::_('PLG_FIELDS_EVENT_DESCRIPTION_VALIDATION_INPUT_REQUIRED'));
		}

		return true;
	}

	/**
	 * Executes before the event is created.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$cluster)
	{
		$desc = $this->input->get($this->inputName, '', 'raw');
		$desc = ES::string()->filterHtml($desc);

		if (!$desc) {
			$desc = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';
		}

		// Set the description on the event
		$cluster->description = $desc;

		unset($post[$this->inputName]);
	}

	/**
	 * Executes before the event is saved.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$cluster)
	{
		$desc = $this->input->get($this->inputName, '', 'raw');
		$desc = ES::string()->filterHtml($desc);

		$cluster->description = $desc;

		unset($post[$this->inputName]);
	}

	/**
	 * Executes before the event is saved.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$post, &$cluster)
	{
		$desc = $this->input->get($this->inputName, '', 'raw');
		$desc = ES::string()->filterHtml($desc);

		if (!$desc) {
			$desc = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';
		}

		$cluster->description = $desc;

		unset($post[$this->inputName]);
	}

	/**
	* Retrieves the editor name.
	*
	* @since   3.2.1
	* @access  public
	*/
	public function getEditorName()
	{
		// Get the default editor
		$defaultEditor = $this->params->get('editor');

		if ($defaultEditor == 'inherit') {
			$config = ES::config();
			$defaultEditor = $config->get('events.editor','none');

			// If the settings is inherit means we will use joomla default editor itself
			if ($defaultEditor == 'inherit') {
				$defaultEditor = JFactory::getConfig()->get('editor');
			}
		}

		return $defaultEditor;
	}

	/**
	 * Retrieves the editor object.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function getEditor()
	{
		$defaultEditor = $this->getEditorName();

		// Fix issues with Joomla 3.7.0 doesn't render core js by default
		$editor = ES::editor()->getEditor($defaultEditor);

		return $editor;
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  3.1.0
	 * @access public
	 */
	public function onGetValue($cluster)
	{
		return $this->getValue();
	}

	/**
	 * Format the data for this description field.
	 *
	 * @since   3.2.12
	 * @access  public
	 */
	public function onFormatData(&$post)
	{
		$defaultEditor = $this->getEditorName();

		if (!empty($post[$this->inputName]) && $defaultEditor != 'none') {

			// we need to get the raw value.
			$rawData = $this->input->get($this->inputName, '', 'raw');

			if ($rawData) {
				$post[$this->inputName] = $rawData;
			}
		}
	}
}
