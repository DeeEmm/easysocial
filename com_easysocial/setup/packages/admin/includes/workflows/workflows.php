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

// Dependencies
ES::import('admin:/includes/fields/triggers');
ES::import('admin:/includes/fields/handlers');
ES::import('admin:/includes/apps/apps');

class SocialWorkflows extends EasySocial
{
	public $table = null;
	public $type = null;

	static $tmpFields = array();
	static $defaultSaveOptions = array(
		'copy' => false
	);

	private $saveOptions = array();

	public function __construct($id = null, $type = SOCIAL_TYPE_USER)
	{
		parent::__construct();

		if ($id instanceof SocialTableWorkflow) {
			$this->table = $id;
		} else {
			$this->table = ES::table('Workflow');
		}

		if (is_array($id) || is_object($id)) {
			$this->table->bind($id);
		} else {
			$this->table->load($id);
		}

		// Predefined workflow type
		if (!$this->table->id) {
			$this->type = $type;
		}
	}

	/**
	 * Magic method to access table's property
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function __get($property)
	{
		if (!property_exists($this, $property) && isset($this->table->$property)) {
			return $this->table->$property;
		}
	}

	/**
	 * Magic method to route calls to table method
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->table, $method), $arguments);
	}

	/**
	 * Method to bind the workflow data
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function bind($data)
	{
		// Bind fields data if there is any
		$this->fieldsData = isset($data['fields']) ? json_decode($data['fields']) : array();
		unset($data['fields']);

		// Normalize data
		if (!isset($data['title'])) {
			$data['title'] = $this->generateTitle();
		}

		if (!isset($data['description'])) {
			$data['description'] = $this->generateDescription();
		}

		$this->table->bind($data);
	}

	/**
	 * Method to save workflows
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function save($options = array())
	{
		// Get any save options if available
		$options = array_merge(array(), self::$defaultSaveOptions, $options);

		$this->saveOptions = $options;

		// Reset id and regenerate title
		if ($this->saveOptions['copy']) {

			// Check for empty fields
			if (empty($this->fieldsData)) {
				$this->fieldsData = $this->getStepsData();
			}

			$this->table->id = null;

			$this->table->title = $this->generateTitle();
		}

		$skipSave = isset($options['skipSave']) ? $options['skipSave'] : false;

		// Save workflows
		if (!$skipSave) {
			$state = $this->table->store();
		}

		// Save fields data
		$this->saveFields($options);
	}

	/**
	 * Method to save custom fields for the workflows
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function saveFields($options = array())
	{
		if (!empty($this->fieldsData)) {
			$workflowId = isset($options['stepId']) ? $options['stepId'] : $this->id;

			$fieldsLib = ES::fields();
			$fieldsLib->saveFields($workflowId, $this->getCategory(), $this->fieldsData, $this->saveOptions);
		}

		return true;
	}

	/**
	 * Method to generate the title of the workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function generateTitle()
	{
		$title = $this->table->title;

		if (!$title) {
			$title = JText::sprintf('COM_ES_WORKFLOW_DEFAULT_TITLE', ucfirst($this->type));
		}

		$model = ES::model('workflows');

		$i = 1;

		$tmp = $title;

		while ($model->titleExists($title, $this->id)) {
			$title = $tmp . ' - ' . $i;
			$i++;
		}

		return $title;
	}

	/**
	 * Method to generate the description of the workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function generateDescription()
	{
		$description = JText::sprintf('COM_ES_WORKFLOW_DEFAULT_DESCRIPTION', ucfirst($this->type));
		return $description;
	}

	/**
	 * Method to to revoked the workflows from the item
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function unassignedWorkflows($uid, $type)
	{
		$table = ES::table('WorkflowMap');
		$table->load(array('uid' => $uid, 'type' => $type));

		return $table->delete();
	}

	/**
	 * Assign workflow to profiles or clusters
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function assignWorkflows($uid, $type)
	{
		$table = ES::table('WorkflowMap');
		$table->load(array('uid' => $uid, 'type' => $type));

		// Delete existing workflow
		if ($table->id) {
			$table->delete();
		}

		$table = ES::table('WorkflowMap');
		$table->uid = $uid;
		$table->type = $type;
		$table->workflow_id = $this->id;

		$state = $table->store();

		// Reset completed fields checking of each users under this profiles
		if ($type == SOCIAL_TYPE_USER) {
			$usersModel = ES::model('Users');
			$usersModel->resetCompletedFieldsByProfileId($uid);
		}

		return true;
	}

	/**
	 * Determine the category of this workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getCategory()
	{
		if ($this->getType() == SOCIAL_TYPE_USER) {
			return SOCIAL_TYPE_PROFILES;
		}

		if ($this->getType() == SOCIAL_TYPE_MARKETPLACE) {
			return SOCIAL_TYPE_MARKETPLACES;
		}

		return SOCIAL_TYPE_CLUSTERS;
	}

	/**
	 * Retrieve the list of steps in this workflow
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getSteps()
	{
		// This is where we retrieve all the steps for a workflow.
		$steps = array();
		$models = ES::model('Steps');

		// Get a list of workflows for this workflow type.
		if ($this->isNew()) {
			$steps = $models->getDefaultSteps($this->id, $this->getCategory(), $this->getType());
		} else {
			$steps = $models->getStepsWithFields($this->id, $this->getCategory(), $this->getType());
		}

		if ($steps) {
			foreach ($steps as $step) {
				foreach ($step->fields as $field) {

					$appId = isset($field->app_id) ? $field->app_id : $field->id;

					// temporarily store the fields to be use later
					self::$tmpFields[$appId] = $field;
				}
			}
		}

		return $steps;
	}

	/**
	 * Get lists of available fields apps for this workflow type
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getInstalledFields()
	{
		// Get a list of available field apps
		$model = ES::model('Apps');
		$defaultFields = $model->getApps(array('type' => SOCIAL_APPS_TYPE_FIELDS, 'group' => $this->getType(), 'state' => SOCIAL_STATE_PUBLISHED));

		// There are no fields available
		if (!$defaultFields) {
			return false;
		}

		$fields = new stdClass();
		$fields->core = array();
		$fields->unique = array();
		$fields->standard = array();
		$fields->hidden = array('core' => false, 'unique' => false, 'standard' => false);

		// Necessary to determine the total use of core and unique fields
		$core = 0;
		$unique = 0;

		// Format the fields based on the types
		foreach ($defaultFields as $field) {
			$field->hidden = false;
			$isStandard = true;

			if ($field->core) {
				$fields->core[] = $field;
				$isStandard = false;
			}

			if (!$field->core && $field->unique) {
				$fields->unique[] = $field;
				$isStandard = false;
			}

			if ($isStandard) {
				$fields->standard[] = $field;
			}

			if (isset(self::$tmpFields[$field->id]) && $field->core) {
				$field->hidden = true;
				$core++;
			}

			if (isset(self::$tmpFields[$field->id]) && !$field->core && $field->unique) {
				$field->hidden = true;
				$unique++;
			}
		}

		if (count($fields->core) == $core) {
			$fields->hidden['core'] = true;
		}

		if (count($fields->unique) == $unique) {
			$fields->hidden['unique'] = true;
		}

		return $fields;
	}

	/**
	 * Determine if this is a new workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isNew()
	{
		return $this->id <= 0;
	}

	/**
	 * Get the type of this workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get the title of workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTitle()
	{
		$title = $this->title;

		if (!$title) {
			$title = $this->generateTitle();
		}

		return $title;
	}

	/**
	 * Get the description of the workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getDescription()
	{
		$description = $this->description;

		if (!$description) {
			$description = $this->generateDescription();
		}

		return $description;
	}

	/**
	 * Get the workflow by given uid and type
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getWorkflow($uid, $type)
	{
		static $_cache = array();

		if (!isset($_cache[$type])) {
			$model = ES::model('Workflows');

			$items = $model->getWorkflowByType($type);

			foreach ($items as $item) {
				$_cache[$type][$item->id] = $item;
			}
		}

		$table = ES::table('WorkflowMap');
		$table->load(array('uid' => $uid, 'type' => $type));

		$workflow = $this;

		if ($table->id) {

			if (isset($_cache[$type][$table->workflow_id])) {
				// lets load from cache
				$workflow = $_cache[$type][$table->workflow_id];
			} else {
				$workflow = ES::workflows($table->workflow_id, $type);
			}
		}

		return $workflow;
	}

	/**
	 * Get total items that using this workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTotalItems()
	{
		$model = ES::model('Workflows');

		$total = $model->getTotalItems($this->id);

		return $total;
	}

	/**
	 * Method to delete the workflows
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function delete()
	{
		// Need to check if there are any items using this workflow. If got do not proceed with the delete
		if ($this->getTotalItems() > 0) {
			$this->setError('To delete a workflow, please ensure that there are no items associated with the workflow');
			return false;
		}

		// Delete the steps and fields from this workflow
		$steps = $this->getSteps();

		if ($steps) {
			foreach ($steps as $step) {
				$step->delete();
			}
		}

		// Lastly, delete the workflow
		$this->table->delete();

		return true;
	}

	/**
	 * Method to create default workflows upon fresh installation
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function createDefaultWorkflow($legacyUpgrade = false)
	{
		// We need the workflow type in order to proceed
		if (!$this->getType()) {
			return false;
		}
		// Get the default steps based on the type
		$steps = $this->getSteps();

		$workflowSteps = array();

		// Format each steps
		foreach ($steps as $data) {
			$step = new stdClass();
			$step->stepId = 0;
			$step->isNew = true;

			$stepParams = array();

			$param = new stdClass();
			$param->name = 'title';
			$param->value = $data->title;

			$stepParams[] = $param;

			$param = new stdClass();
			$param->name = 'description';
			$param->value = $data->description;

			$stepParams[] = $param;

			$step->params = $stepParams;

			$fields = array();

			// Format fields
			if ($data->fields) {
				foreach ($data->fields as $field) {
					$obj = new stdClass();
					$obj->fieldId = $field->id;
					$obj->appId = $field->app_id;
					$obj->isNew = true;

					$fieldsParams = array();

					foreach ($field as $configName => $configValue) {
						$config = new stdClass();
						$config->name = $configName;
						$config->value = $configValue;

						$fieldsParams[] = $config;
					}

					$obj->params = $fieldsParams;

					$fields[] = $obj;
				}
			}

			$step->fields = $fields;
			$workflowSteps[] = $step;
		}

		$workflow = new stdClass();
		$workflow->steps = $workflowSteps;

		$bindData = array();
		$bindData['fields'] = json_encode($workflow);
		$bindData['title'] = $this->generateTitle();
		$bindData['description'] = $this->generateDescription();
		$bindData['type'] = $this->getType();

		$this->bind($bindData);

		$saveData = array();

		if ($legacyUpgrade) {
			$saveData['stepId'] = $this->generateStepId();
			$saveData['skipSave'] = true;
		}

		$this->save($saveData);

		return true;
	}

	/**
	 * Method to add single field into this workflow based on app id
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function saveField($appId)
	{
		$appTable = ES::table('App');
		$appTable->load($appId);

		// Get the first step
		$steps = $this->getSteps();
		$stepId = $steps[0]->id;

		// Construct basic field data
		$field = new stdClass();
		$field->fieldTitle = $appTable->title;
		$field->fieldId = '';
		$field->fieldElement = $appTable->element;
		$field->appId = $appId;
		$field->isNew = true;

		$fieldTmp = ES::fields()->saveField($field, $stepId);

		return;
	}

	/**
	 * Generate workflow id to avoid duplicate id in fields steps
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function generateStepId()
	{
		// Get the last uid value from fields steps table
		$db = ES::db();
		$query = 'SELECT `uid` from `#__social_fields_steps` where `type` = ' . $db->Quote($this->getCategory()) . ' ORDER BY `uid` DESC LIMIT 1';

		$db->setQuery($query);
		$id = $db->loadResult();

		return $id + 1;
	}

	/**
	 * Get the existing steps data of this workflow
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStepsData()
	{
		$data = new stdClass();
		$data->steps = array();
		$data->deleted = new stdClass();

		$steps = $this->getSteps();

		if ($steps) {
			$ordering = 0;

			foreach ($steps as $step) {
				$formattedStep = new stdClass;
				$formattedStep->stepId = $step->id;
				$formattedStep->ordering = $ordering;
				$formattedStep->isNew = false;

				$fields = array();

				if ($step->fields) {
					foreach ($step->fields as $field) {
						$formattedField = new stdClass();
						$formattedField->fieldTitle = $field->title;
						$formattedField->fieldId = $field->id;
						$formattedField->fieldElement = $field->element;
						$formattedField->appId = $field->app_id;
						$formattedField->ordering = $field->ordering;
						$formattedField->isNew = false;

						$fields[] = $formattedField;
					}
				}

				$formattedStep->fields = $fields;
				$ordering++;

				$data->steps[] = $formattedStep;
			}
		}

		return $data;
	}
}
