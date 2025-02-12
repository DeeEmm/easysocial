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

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsMarketplaceDescription extends SocialFieldItem
{
	/**
	 * Executes before the item is created.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$data, &$item)
	{
		$desc = $this->input->get($this->inputName, '', 'raw');
		$desc = ES::string()->filterHtml($desc);

		if (!$desc) {
			$desc = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';
		}

		// Set the description on the item
		$item->description = $desc;

		unset($data[$this->inputName]);
	}

	/**
	 * Executes before the item is saved.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$data, &$item)
	{
		$desc = $this->input->get($this->inputName, '', 'raw');
		$desc = ES::string()->filterHtml($desc);

		if (!$desc) {
			$desc = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';
		}

		// Set the description on the marketplace item
		$item->description = $desc;

		unset($data[$this->inputName]);
	}

	/**
	 * Executes before the marketplace item is saved.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$data, &$item)
	{
		$desc = $this->input->get($this->inputName, '', 'raw');
		$desc = ES::string()->filterHtml($desc);

		if (!$desc) {
			$desc = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';
		}

		// Set the description on the marketplace item
		$item->description = $desc;

		unset($data[$this->inputName]);
	}

	/**
	 * Displays the marketplace item description textbox.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onEdit(&$data, &$item, $errors)
	{
		$desc = $this->input->get($this->inputName, $item->description, 'raw');
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
	 * Displays the marketplace description textbox.
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function onAdminEdit(&$data, &$item, $errors)
	{
		$itemDesc = JText::_($this->params->get('default'), true);

		if ($item->id) {
			$itemDesc = $item->description;
		}

		$desc = $this->input->get($this->inputName, $itemDesc, 'raw');
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
	 * @since   3.3
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
	 * @since   3.3
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
	 * @since   3.3
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
	 * @since   3.3
	 * @access  public
	 */
	private function validate($value)
	{
		if ($this->isRequired() && empty($value)) {
			return $this->setError(JText::_('PLG_FIELDS_MARKETPLACE_DESCRIPTION_VALIDATION_INPUT_REQUIRED'));
		}

		return true;
	}

	/**
	* Retrieves the editor name.
	*
	* @since   3.3
	* @access  public
	*/
	public function getEditorName()
	{
		$config = ES::config();
		$defaultEditor = $config->get('marketplaces.editor','none');

		// If the settings is inherit means we will use joomla default editor itself
		if ($defaultEditor == 'inherit') {
			$defaultEditor = JFactory::getConfig()->get('editor');
		}

		return $defaultEditor;
	}

	/**
	* Retrieves the editor object.
	*
	* @since   3.3
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
	 * @since   3.3
	 * @access  public
	 */
	public function onFormatData(&$post)
	{
		$config = ES::config();
		$defaultEditor = $config->get('marketplaces.editor','none');

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
	 * @since  3.3
	 * @access public
	 */
	public function onGetValue($listing)
	{
		return $listing->getDescription();
	}

}
