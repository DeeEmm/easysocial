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

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateWorkflowsData extends SocialMaintenanceScript
{
	public static $title = 'Update Workflows Data For Profiles And Clusters';
	public static $description = 'Update Profiles And Clusters Field Data To Utilize The New Workflow System';

	public function main()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'SELECT `type`, count(id) as total FROM `#__social_workflows` GROUP BY `type`';
		$db->setQuery($query);
		$results = $db->loadObjectList();

		$workflows = array();

		foreach ($results as $workflow) {
			$workflows[$workflow->type] = $workflow->total;
		}

		if (!isset($workflows['user'])) {
			// Retrieve all existing profile types
			$query = 'SELECT `id`, `title`, `description` FROM `#__social_profiles`';

			$db->setQuery($query);
			$profiles = $db->loadObjectList();

			// Store the workflows for profiles
			foreach ($profiles as $profile) {
				$workflow = ES::table('Workflow');
				$workflow->title = $profile->title;
				$workflow->description = $profile->description;
				$workflow->type = SOCIAL_TYPE_USER;

				$workflow->store();

				// Update steps uid to workflow_id
				$query = 'UPDATE `#__social_fields_steps` SET `workflow_id` = ' . $db->Quote($workflow->id) . 'WHERE `uid` = ' . $db->Quote($profile->id);
				$query .= ' AND `type` = ' . $db->Quote('profiles');

				$sql->raw($query);
				$db->setQuery($sql);
				$db->query();

				// Map the workflows
				$workflowMap = ES::table('WorkflowMap');
				$workflowMap->uid = $profile->id;
				$workflowMap->workflow_id = $workflow->id;
				$workflowMap->type = SOCIAL_TYPE_USER;

				$workflowMap->store();
			}
		}

		// Clusters Categories
		$query = 'SELECT `id`, `title`, `description`, `type` FROM `#__social_clusters_categories` WHERE `type` IN("group", "page", "event")';

		$db->setQuery($query);
		$clusters = $db->loadObjectList();

		// Store the workflows for clusters
		foreach ($clusters as $cluster) {

			if (isset($workflows[$cluster->type])) {
				continue;
			}

			$stepsQuery = 'SELECT count(1) FROM `#__social_fields_steps` WHERE `uid` = ' . $db->Quote($cluster->id);
			$db->setQuery($stepsQuery);
			$exists = $db->loadResult();

			if ($exists) {
				$workflow = ES::table('Workflow');
				$workflow->title = $cluster->title;
				$workflow->description = $cluster->description;
				$workflow->type = $cluster->type;

				$workflow->store();

				$query = 'UPDATE `#__social_fields_steps` SET `workflow_id` = ' . $db->Quote($workflow->id) . 'WHERE `uid` = ' . $db->Quote($cluster->id);
				$query .= ' AND `type` = ' . $db->Quote('clusters');

				$sql->raw($query);
				$db->setQuery($sql);
				$db->query();
			} else {
				$workflow = ES::table('Workflow');
				$workflow->load(array('type' => $cluster->type));
			}

			// Map the workflows
			$workflowMap = ES::table('WorkflowMap');
			$workflowMap->uid = $cluster->id;
			$workflowMap->workflow_id = $workflow->id;
			$workflowMap->type = $cluster->type;

			$workflowMap->store();
		}

		// Check for unassigned mandatory fields
		$this->checkMandatoryFields();

		return true;
	}

	public function checkMandatoryFields()
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get a list of available field apps
		$groups = array(SOCIAL_TYPE_USER, SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT);
		$model = ES::model('Apps');
		$mandatoryApps = array();
		$appId = array();

		foreach ($groups as $group) {
			$apps = $model->getApps(array('type' => SOCIAL_APPS_TYPE_FIELDS, 'group' => $group, 'state' => SOCIAL_STATE_PUBLISHED, 'core' => 1));

			foreach ($apps as $app) {
				$mandatoryApps[$group][$app->id] = $app;
				$appId[$group][$app->id] = $app->id;
			}
		}

		$query = 'SELECT `id`, `type` FROM `#__social_workflows`';
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($result) {
			foreach ($result as $workflow) {
				$stepIdQuery = 'SELECT `id` FROM `#__social_fields_steps` WHERE `workflow_id` = ' . $db->Quote($workflow->id);

				$query = 'SELECT * FROM `#__social_fields` WHERE `step_id` IN(' . $stepIdQuery . ') AND `app_id` IN(' . implode(',', $appId[$workflow->type]) . ')';
				$db->setQuery($query);
				$results = $db->loadObjectList();
				$exists = array();

				if ($results) {

					foreach ($results as $result) {
						$exists[$result->app_id] = $result->app_id;
					}

					foreach ($mandatoryApps[$workflow->type] as $mandatoryApp) {
						if (!in_array($mandatoryApp->id, $exists)) {
							$this->saveField($workflow->id, $mandatoryApp);
						}
					}
				} else {

					// Not a single mandatory field found. Just add all the mandatory field inside.
					foreach ($mandatoryApps[$workflow->type] as $mandatoryApp) {
						$this->saveField($workflow->id, $mandatoryApp);
					}
				}
			}
		}
	}

	/**
	 * Add field into the workflow
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function saveField($workflowId, $app)
	{
		$workflow = ES::workflows($workflowId);
		$workflow->saveField($app->id);
	}
}