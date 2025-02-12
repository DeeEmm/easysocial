<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelSteps extends EasySocialModel
{
	private $data = null;

	public function __construct()
	{
		parent::__construct('steps');
	}

	/**
	 * Get default steps with fields preloaded
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getDefaultSteps($uid, $category, $type)
	{
		$path = SOCIAL_ADMIN_DEFAULTS . '/fields/' . $type . '.json';

		if (!JFile::exists($path)) {
			return false;
		}

		$steps = ES::makeObject($path);

		// If there's a problem decoding the file, log some errors here.
		if (!$steps) {
			$this->setError('Empty default object');
			return false;
		}

		$sequence = 1;

		$uniqueIdArray = array();

		foreach ($steps as $step) {

			// Temporarily set the sequence id for now since the saving part will handle everything
			$step->id = $sequence;
			$step->sequence = $sequence;

			$sequence++;
			$ordering = 0;

			if (!$step->fields) {
				continue;
			}

			$fields = array();

			foreach ($step->fields as $field) {
				$appTable = ES::table('App');
				$state = $appTable->load(array('element' => $field->element, 'group' => $type, 'type' => SOCIAL_APPS_TYPE_FIELDS));

				if ($state && ($appTable->state == SOCIAL_STATE_PUBLISHED || $appTable->core == SOCIAL_STATE_PUBLISHED)) {

					// Generate unique id for each field
					$uniqueId = ES::generateUniqueId($uniqueIdArray);

					$table = ES::table('Field');
					$table->bind($field);
					$table->id = $uniqueId;
					$table->app_id = $appTable->id;
					$table->ordering = $ordering;

					// Initialize default values
					$table->processParams(array(), true);

					// Map override value
					foreach ($field as $key => $value) {
						if (isset($table->$key)) {
							$table->$key = $value;
						}
					}

					$fields[] = $table;

					$uniqueIdArray[] = $uniqueId;
				}

				$ordering++;
			}

			$step->fields = $fields;
		}

		return $steps;
	}

	/**
	 * Creates a default workflow item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createDefaultStep($uid, $type)
	{
		$step = ES::table('FieldStep');

		// Use the default step title.
		$step->title = JText::_('COM_EASYSOCIAL_STEPS_DEFAULT_STEP');

		// Set the default description.
		$step->description = JText::_('COM_EASYSOCIAL_STEPS_DEFAULT_DESCRIPTION');

		// Link the foreign keys.
		$step->uid = $uid;
		$step->type = $type;

		// Set the default state to be published since this step can never be unpublished.
		$step->state = SOCIAL_STATE_PUBLISHED;

		// The sequence will always be 1 since this is the first step created.
		$step->sequence = 1;

		// Let's store this
		$state = $step->store();

		if (!$state) {
			$this->setError($step->getError());
			return false;
		}

		return $step;
	}

	public function getFields($profileId)
	{
		$db = ES::db();

		$query = 'SELECT d.* FROM ' . $db->nameQuote('#__social_fields_steps') . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote('#__social_profiles') . ' AS b '
				. 'ON b.id=a.profile_id '
				. 'LEFT JOIN ' . $db->nameQuote('#__social_profile_types_fields') . ' AS c '
				. 'ON c.profile_id=b.id '
				. 'LEFT JOIN ' . $db->nameQuote('#__social_fields') . ' AS d '
				. 'ON d.id=c.field_id '
				. 'WHERE a.`id`=' . $db->Quote($profileId);

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	public function getProfiles($options = array())
	{
		$query = 'SELECT a.* FROM ' . $this->_db->nameQuote('#__social_profiles') . ' AS a '
				. 'LEFT JOIN #__social_fields_steps AS b '
				. 'ON a.id = b.profile_id '
				. ' WHERE ';

		$sql = array();

		if (is_array($options)) {
			foreach ($options as $key => $value) {
				$sql[] = ' a.' . $this->_db->nameQuote($key) . '=' . $this->_db->Quote($value);
			}
		}

		if (!empty($sql)) {
			$query	.= implode(' AND ', $sql);
		}

		$query .= ' AND b.' . $this->_db->nameQuote('profile_id') . ' IS NULL';

		return $this->getData($query);
	}

	public function getPagination($reload = null)
	{
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}

	/**
	 * Retrieve steps for a specific workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getSteps($uid, $type = SOCIAL_TYPE_PROFILES, $mode = null, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_fields_steps');
		$sql->where('workflow_id', $uid);
		$sql->where('type', $type);

		// Only filter by mode if it is frontend
		if (!empty($mode)) {
			$sql->where('visible_' . $mode, SOCIAL_STATE_PUBLISHED);
		}

		// If there's an exclusion list, exclude it
		$exclusion = isset($options['exclusion']) ? $options['exclusion'] : '';

		if (!empty($exclusion)) {

			// Ensure that it's an array
			$exclusionIds = ES::makeArray($exclusion);

			if (!empty($exclusionIds)) {
				$sql->where('id', $exclusionIds, 'NOT IN');
			}
		}

		$sql->order('sequence');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$steps = array();

		if (!$result) {
			return array();
		}

		foreach ($result as $row) {
			$step = ES::table('FieldStep');
			$step->bind($row);

			$steps[] = $step;
		}

		return $steps;
	}

	/**
	 * Get Steps With Fields Preloaded
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getStepsWithFields($uid, $category, $type)
	{
		$stepsModel = ES::model('Steps');
		$steps = $stepsModel->getSteps($uid, $category);

		// Group the fields to each workflow properly
		if ($steps) {

			// Get a list of fields for this workflow
			$fieldsModel = ES::model('Fields');
			$fields = $fieldsModel->getCustomFields(array('workflow_id' => $uid, 'state' => 'all', 'type' => $category));

			foreach ($steps as $step) {

				$step->fields = array();

				if (!empty($fields)) {

					foreach ($fields as $field) {
						if ($field->step_id == $step->id) {
							$step->fields[] = $field;
						}
					}
				}
			}
		}

		return $steps;
	}
}
