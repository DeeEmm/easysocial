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

ES::import('admin:/views/views');

class EasySocialViewFields extends EasySocialAdminView
{
	/**
	 * Retrieve a list of fields.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFields($fields = array())
	{
		return $this->ajax->resolve($fields);
	}

	/**
	 * Renders a placeholder field
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPlaceholder()
	{
		$uid = $this->input->get('uid', 0, 'int');

		$theme = ES::themes();
		$theme->set('uid', $uid);
		$output = $theme->output('admin/fields/editor/item.placeholder');

		return $this->ajax->resolve($output);
	}

	/**
	 * Translate the text used in field edit form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTranslation()
	{
		$text = $this->input->get('text', '', 'string');
		if ($text) {
			$text = JText::_($text);
		}
		return $this->ajax->resolve($text);
	}

	/**
	 * Renders the page menu on sidebar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPageMenu()
	{
		$uid = $this->input->get('uid', 0, 'int');
		$pageNumber = 0;

		// Simulate an existing page
		$step = ES::table('FieldStep');
		$step->id = $uid;
		$step->title = JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CORE_TITLE_DEFAULT');
		$step->description = JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CORE_DESCRIPTION_DEFAULT');

		$theme = ES::themes();
		$theme->set('step', $step);
		$theme->set('pageNumber', $pageNumber);

		$output = $theme->output('admin/fields/editor/step.item');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders the new page contents
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPage()
	{
		$uid = $this->input->get('uid', 0, 'int');

		$isNew = true;

		// Simulate an existing page
		$step = ES::table('FieldStep');
		$step->id = $uid;
		$step->title = JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CORE_TITLE_DEFAULT');
		$step->description = JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_PARAMS_CORE_DESCRIPTION_DEFAULT');

		if ($isNew) {
			$step->fields = array();
		}

		$theme = ES::themes();
		$theme->set('step', $step);
		$theme->set('pageNumber', 0);
		$theme->set('isNew', $isNew);

		$output = $theme->output('admin/fields/editor/page');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders the configuration theme file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function loadConfiguration()
	{
		// Determines if this is the page / field configuration
		$type = $this->input->get('type', '', 'cmd');
		$method = 'loadConfiguration' . ucfirst($type);

		return $this->$method();
	}

	public function loadConfigurationPage()
	{
		// Get the default settings
		$path = SOCIAL_CONFIG_DEFAULTS . '/fields.header.json';
		$raw = file_get_contents($path);

		$params = json_decode($raw);

		foreach ($params as $name => &$field) {

			// Only try to JText the label field if it exists.
			if (isset($field->label)) {
				$field->label = JText::_($field->label);
			}

			// Only try to JText the tooltip field if it exists.
			if (isset($field->tooltip)) {
				$field->tooltip	= JText::_($field->tooltip);
			}

			// If there are options set, we need to jtext them as well.
			if (isset($field->option)) {
				$field->option = ES::makeArray($field->option);

				foreach ($field->option as &$option) {
					$option->label = JText::_($option->label);
				}
			}
		}

		// Get any page id
		$pageId = $this->input->get('pageid', 0, 'int');

		// Load the field step
		$table = ES::table('FieldStep');

		if (!empty($pageId)) {
			$table->load($pageId);
		} else {
			foreach ($params as $name => &$field) {
				$table->$name = $field->default;
			}
		}

		$theme = ES::themes();
		$theme->set('title', JText::_('COM_EASYSOCIAL_PROFILES_FORM_PAGE_CONFIGURATION'));
		$theme->set('params', $params);
		$theme->set('values', $table);

		$output = $theme->output('admin/fields/config/page');

		return $this->ajax->resolve($output, $table, $params);
	}

	/**
	 * Load workflow steps configuration page
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function loadStepConfiguration()
	{
		// Get the default settings
		$path = SOCIAL_CONFIG_DEFAULTS . '/fields.header.json';
		$raw = file_get_contents($path);

		$params = json_decode($raw);

		foreach ($params as $name => &$field) {

			// Only try to JText the label field if it exists.
			if (isset($field->label)) {
				$field->label = JText::_($field->label);
			}

			// Only try to JText the tooltip field if it exists.
			if (isset($field->tooltip)) {
				$field->tooltip	= JText::_($field->tooltip);
			}

			// If there are options set, we need to jtext them as well.
			if (isset($field->option)) {
				$field->option = ES::makeArray($field->option);

				foreach ($field->option as &$option) {
					$option->label = JText::_($option->label);
				}
			}
		}

		// Get any page id
		$pageId = $this->input->get('id', 0, 'default');

		// Load the field step
		$table = ES::table('FieldStep');
		$table->load($pageId);

		if (!$table->id) {
			$table->id = $pageId;

			foreach ($params as $name => &$field) {
				$table->$name = $field->default;
			}
		}

		$theme = ES::themes();
		$theme->set('title', JText::_('COM_EASYSOCIAL_PROFILES_FORM_PAGE_CONFIGURATION'));
		$theme->set('params', $params);
		$theme->set('values', $table);

		$output = $theme->output('admin/workflows/form/browser/step');

		return $this->ajax->resolve($output, $table, $params);
	}

	/**
	 * Renders the settings for a custom field
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function loadFieldConfiguration()
	{
		// Get properties from request
		$appId = $this->input->get('appId', 0, 'int');

		// If this is empty, it is a new field item that's being added to the form.
		$fieldId = $this->input->get('fieldId', '', 'raw');

		// Retrieve previous data if this
		$previousData = $this->input->get('previousData', '', 'raw');

		// Get current available field on the form
		$availableFields = $this->input->get('availableFields', array(), 'array');

		// Application id should never be empty.
		if (!$appId) {
			return $this->exception('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_INVALID_APP_ID_PROVIDED');
		}

		// Load frontend's language file
		ES::language()->loadSite();

		$fields = ES::fields();

		// getFieldConfigParameters is returning a stdClass object due to deep level data
		$values = $fields->getFieldConfigParameters($appId, true);

		// getFieldConfigValues is returning a JRegistry object
		$params = $fields->getFieldConfigValues($appId, $fieldId);
		$data = $params->toObject();

		// Render the form
		$html = $fields->getForm($appId, $fieldId, $previousData, $availableFields);

		return $this->ajax->resolve($html, $data, $values);
	}

	/**
	 * Generates an error that prevents user from saving the current form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSaveError()
	{
		$message = $this->input->get('message', '', 'default');
		$message = JText::_($message);

		$theme = ES::themes();
		$theme->set('message', $message);
		$output = $theme->output('admin/fields/dialogs/save.error');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders confirmation to delete a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDeletePage()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/fields/dialogs/delete.page');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders confirmation to delete a custom field
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDeleteField()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/fields/dialogs/delete.field');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders the confirmation to move a field
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmMoveField()
	{
		// Get the profile id
		$id = $this->input->get('id', 0, 'int');
		$group = $this->input->get('group', 'user');

		$type = SOCIAL_TYPE_PROFILES;

		if ($group != 'user') {
			$type = SOCIAL_TYPE_CLUSTERS;
		}

		// Load profile
		$profile = ES::table('profile');
		$profile->load($id);

		// Get a list of workflows for this profile type.
		$model = ES::model('Steps');
		$steps = $model->getSteps($profile->getWorkflow()->id, $type);

		$theme = ES::themes();
		$theme->set('steps', $steps);
		$output = $theme->output('admin/fields/dialogs/field.move');

		return $this->ajax->resolve($output);
	}

	/**
	 * Retrieves the custom field's page configuration
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function renderPageConfig($params, $values)
	{
	}


	/**
	 * Render's field params
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function renderConfiguration( $manifest, $params, $html )
	{
		$ajax 	= ES::ajax();

		return $ajax->resolve( $manifest, $params->toObject(), $html );
	}

	public function update()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select( '#__social_fields', 'a' )
			->column( 'a.id' )
			->column( 'a.app_id' )
			->column( 'b.element' )
			->leftjoin( '#__social_apps', 'b' )
			->on( 'a.app_id', 'b.id' );

		$db->setQuery( $sql );

		$result = $db->loadObjectList();

		$elements = array();

		foreach( $result as $row )
		{
			$table = ES::table( 'field' );
			$table->load( $row->id );

			$table->unique_key = strtoupper( $row->element ) . '-' . $row->id;
			$table->store();
		}

		ES::ajax()->resolve();
	}

}
