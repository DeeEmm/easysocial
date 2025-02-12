<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsGroupDescription extends SocialFieldItem
{
	/**
	 * Executes before the group is created.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$data, &$cluster)
	{
		$desc = $this->input->get($this->inputName, '', 'raw');
		$desc = ES::string()->filterHtml($desc);

		if (!$desc) {
			$desc = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';
		}

		// Set the description on the group
		$cluster->description = $desc;

		unset($data[$this->inputName]);
	}

	/**
	 * Executes before the group is saved.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onEditBeforeSave(&$data, &$cluster)
	{
		$desc = $this->input->get($this->inputName, '', 'raw');
		$desc = ES::string()->filterHtml($desc);

		if (!$desc) {
			$desc = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';
		}

		// Set the description on the group
		$cluster->description = $desc;

		unset($data[$this->inputName]);
	}

	/**
	 * Executes before the group is saved.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$data, &$cluster)
	{
		$desc = $this->input->get($this->inputName, '', 'raw');
		$desc = ES::string()->filterHtml($desc);

		if (!$desc) {
			$desc = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';
		}

		// Set the description on the group
		$cluster->description = $desc;

		unset($data[$this->inputName]);
	}

	/**
	 * Displays the group description textbox.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onEdit(&$data, &$cluster, $errors)
	{
		$desc = $this->input->get($this->inputName, $cluster->description, 'raw');
		$desc = ES::string()->filterHtml($desc);

		$error = $this->getError($errors);
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
	 * Displays the group description textbox.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function onAdminEdit(&$data, &$cluster, $errors)
	{
		$clusterDesc = JText::_($this->params->get('default'), true);

		if ($cluster->id) {
			$clusterDesc = $cluster->description;
		}

		$desc = $this->input->get($this->inputName, $clusterDesc, 'raw');
		$desc = ES::string()->filterHtml($desc);

		$error = $this->getError($errors);
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
	 * Displays the field input for user when they register their account.
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function onRegister(&$post, &$registration)
	{
		$desc = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->input->get($this->inputName, $this->params->get('default'), 'raw');
		$desc = ES::string()->filterHtml($desc);

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
			return $this->setError(JText::_('PLG_FIELDS_GROUP_DESCRIPTION_VALIDATION_INPUT_REQUIRED'));
		}

		return true;
	}


	/**
	 * Responsible to output the html codes that is displayed to a user.
	 *
	 * @since   1.2
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
	* Retrieves the editor name.
	*
	* @since   3.2.1
	* @access  public
	*/
	public function getEditorName()
	{
		$config = ES::config();
		$defaultEditor = $config->get('groups.editor','none');

		// If the settings is inherit means we will use joomla default editor itself
		if ($defaultEditor == 'inherit') {
			$defaultEditor = JFactory::getConfig()->get('editor');
		}

		return $defaultEditor;
	}

	/**
	* Retrieves the editor object.
	*
	* @since   2.0
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
	 * Format the data for this description field.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onFormatData(&$post)
	{
		$config = ES::config();
		$defaultEditor = $config->get('groups.editor','none');

		if (!empty($post[$this->inputName]) && $defaultEditor != 'none') {
			// we need to get the raw value.
			$rawData = $this->input->get($this->inputName, '', 'raw');
			if ($rawData) {
				$post[$this->inputName] = $rawData;
			}
		}
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  3.1.0
	 * @access public
	 */
	public function onGetValue($cluster)
	{
		return $cluster->getDescription();
	}

}
