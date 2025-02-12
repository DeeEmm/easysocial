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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelWorkflows extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('workflows', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function initStates()
	{
		$ordering = $this->getUserStateFromRequest('ordering', 'id');
		$direction = $this->getUserStateFromRequest('direction', 'ASC');
		$type = $this->getUserStateFromRequest('type', '');

		parent::initStates();

		$this->setState('type', $type);
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Retrieves a list of workflows from the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_workflows');

		// Check for search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Check for ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$sql->order($ordering, $direction);
		}

		$type = $this->getState('type');

		if ($type) {
			$sql->where('type', $type);
		}

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$results = $this->getData($sql);

		if (!$results) {
			return false;
		}

		$workflows = array();
		$total = count($results);

		foreach ($results as $result) {
			$workflows[] = ES::workflows($result);
		}

		return $workflows;
	}

	/**
	 * Determines if workflow title already exists
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function titleExists($title, $workflowId = null)
	{
		$db = $this->db;

		$query = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_workflows') . ' '
				. 'WHERE ' . $db->nameQuote('title') . '=' . $db->Quote($title);

		if ($workflowId) {
			$query .= ' AND ' . $db->nameQuote('id') . '!=' . $db->Quote($workflowId);
		}

		$db->setQuery($query);

		return $db->loadResult() > 0 ? true : false;
	}

	/**
	 * Retrieve list of workflows based on the type
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getWorkflowByType($type)
	{
		$db = $this->db;

		$query = 'SELECT * FROM ' . $db->nameQuote('#__social_workflows');
		$query .= ' WHERE ' . $db->nameQuote('type') . ' = ' . $db->Quote($type);

		$db->setQuery($query);
		$result = $db->loadObjectList();

		$workflows = array();

		if ($result) {
			foreach ($result as $workflow) {
				$workflows[] = ES::workflows($workflow, $workflow->type);
			}
		}

		return $workflows;
	}

	/**
	 * Retrieve total items that using the workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTotalItems($id)
	{
		$db = $this->db;

		$query = 'SELECT count(1) FROM ' . $db->nameQuote('#__social_workflows_maps');
		$query .= ' WHERE ' . $db->nameQuote('workflow_id') . ' = ' . $db->Quote($id);

		$db->setQuery($query);
		$total = $db->loadResult();

		if (!$total) {
			$total = 0;
		}

		return $total;
	}
}
