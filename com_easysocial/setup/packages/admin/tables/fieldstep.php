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

ES::import('admin:/tables/table');

class SocialTableFieldStep extends SocialTable
{
	public $id = null;
	public $uid = null;
	public $workflow_id = null;
	public $type = null;
	public $title = null;
	public $description = null;
	public $state = null;
	public $created = null;
	public $sequence = null;
	public $visible_registration = null;
	public $visible_edit = null;
	public $visible_display = null;

	public $_isCopy = false;

	public function __construct(&$db)
	{
		parent::__construct('#__social_fields_steps', 'id', $db);
	}

	/**
	 * Retrieves a particular step based on the sequence and the workflow id.
	 *
	 * @deprecated	Depcreated since 1.3. Use native load function instead.
	 * @since 	2.1
	 * @access	public
	 */
	public function loadBySequence($uid, $type = SOCIAL_TYPE_USER, $sequence = 0)
	{
		return parent::load(array('workflow_id' => $uid, 'type' => $type, 'sequence' => $sequence));
	}

	/**
	 * Override's parent store method as we need to get the sequence if it's not being set.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		// Get next sequence
		if (!$this->sequence) {
			$lastSequence = $this->getLastSequence();
			$this->sequence	= $lastSequence + 1;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Renders the permalink for editing a step
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getEditLink($objectId = null, $routerType = null, $xhtml = true)
	{
		$defaultOptions = array('layout' => 'edit', 'activeStep' => $this->id);

		if ($routerType == 'profile') {
			$permalink = ESR::profile($defaultOptions);

			$arguments = array(&$permalink);

			$dispatcher = ES::dispatcher();
			$dispatcher->trigger(SOCIAL_TYPE_USER, 'onBeforeRenderStepEditLink', $arguments);

			return $permalink;
		}

		$permalink = ESR::$routerType(array_merge(array('id' => $objectId), $defaultOptions));

		return $permalink;
	}

	/**
	 * Determine's the next sequence in this series.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getNextSequence($mode = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select($this->_tbl);
		$sql->column('sequence');
		$sql->where('workflow_id', $this->workflow_id);
		$sql->where('type', $this->type);
		$sql->where('sequence', $this->sequence, '>');

		if (!empty($mode)) {
			$sql->where('visible_' . $mode, 1);
		}

		$sql->order('sequence');
		$sql->limit(1);

		$db->setQuery($sql);
		$result = $db->loadResult();

		if (empty($result)) {
			return false;
		}

		return $result;
	}

	/**
	 * Get the last sequence number in the field step series. This was previously combined in getNextSequence(), and splitted out here for better clarity.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function getLastSequence($mode = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select($this->_tbl);
		$sql->column('sequence', 'sequence', 'max');
		$sql->where('workflow_id', $this->workflow_id);
		$sql->where('type', $this->type);

		if (!empty($mode)) {
			$sql->where('visible_' . $mode, 1);
		}

		$db->setQuery($sql);
		$result = $db->loadResult();

		if (empty($result)) {
			return 0;
		}

		return $result;
	}

	/**
	 * Retrieves the associated workflow for this step
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getWorkflow()
	{
		$workflow = ES::table('Workflow');
		$workflow->load($this->workflow_id);

		return $workflow;
	}

	/**
	 * Update the sequence for any sequence larger or lesser given the conditions.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateSequence($currentSequence, $workflowId, $operation = 'add')
	{
		$operation = $operation == 'add' ? '+' : '-';

		$db = ES::db();
		$query = 'UPDATE ' . $db->nameQuote($this->_tbl) . ' '
				. 'SET ' . $db->nameQuote('sequence') . ' = (' . $db->nameQuote('sequence') . ' ' . $operation . ' 1) '
				. 'WHERE ' . $db->nameQuote('sequence') . ' > ' . $db->Quote($currentSequence) . ' '
				. 'AND ' . $db->nameQuote('workflow_id') . ' = ' . $db->Quote($workflowId);

		$db->setQuery($query);
		$db->Query();
	}

	/**
	 * Override the parent's delete method as we need to update the sequence when a
	 * workflow is deleted.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$stepid = $this->id;
		$result = parent::delete($pk);

		if ($result) {
			$this->updateSequence($this->sequence, $this->workflow_id, 'substract');

			if ($stepid != 0) {

				// Get all the fields in this step
				$fields = $this->getStepFields();

				foreach ($fields as $field) {

					// Delete the fields
					$field->delete();
				}
			}
		}

		return $result;
	}

	/**
	 * Determines if this step is the last step.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isFinalStep($mode = null)
	{
		// If this sequence and the last sequence in the same series is the same, then we are on the final step
		return $this->sequence == $this->getLastSequence($mode);
	}

	/**
	 *	This will get all the fields in this step
	 *
	 *	@since	2.1
	 *	@access	public
	 */
	public function getStepFields()
	{
		$fieldsModel = ES::model('fields');

		// Get all the fields in this step
		$fields = $fieldsModel->getCustomFields(array('step_id' => $this->id));

		return $fields;
	}

	/**
	 * Function to check if this step is a new step
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isNew()
	{
		return !$this->id;
	}

	/**
	 * Given a set of parameter, process the argument as params of this page
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function processParams($step)
	{
		static $default = null;

		if (empty($default)) {
			$path = SOCIAL_CONFIG_DEFAULTS . '/fields.header.json';
			$raw = file_get_contents($path);

			$default = ES::json()->decode($raw);
		}

		if (isset($step->params)) {
			foreach ($step->params as $param) {
				$name = $param->name;
				$value = $param->value;

				$step->$name = $value;
			}
		}

		$params = array('title', 'description', 'visible_registration', 'visible_edit', 'visible_display');

		foreach ($params as $param) {
			if (isset($step->$param)) {
				$this->$param = $step->$param;
			} else {
				if (!$this->_isCopy && $this->isNew()) {
					$this->$param = $default->$param->default;
				}
			}
		}

		return $this;
	}
}
