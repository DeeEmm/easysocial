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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerInstallationWorkflows extends EasySocialSetupController
{
	/**
	 * Install default workflows
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$this->engine();
		$results = array();

		$db = ES::db();
		$sql = $db->sql();

		// We check by the existing of each categories type.
		// User profiles
		$sql->select('#__social_profiles');
		$sql->column('id');
		$sql->limit(0, 1);

		$db->setQuery($sql);
		$id = $db->loadResult();

		if (!$id) {
			$workflow = ES::workflows(0, 'user');
			$workflow->createDefaultWorkflow();

			$results[] = $this->getResultObj('Created default user workflow successfully.', true);
		} else {
			$results[] = $this->getResultObj('Skipping user workflows creation as the workflow already exists on the site.', true);
		}

		$previous = $this->getPreviousVersion('scriptversion');
		$legacy = false;

		// Check if this is upgraded from version 1.x
		$parts = explode('.', $previous);

		if ($parts[0] == 1) {
			$legacy = true;
		}

		// Clusters
		$types = array('group', 'event', 'page');

		// Create default workflows for each types above
		foreach ($types as $type) {
			$sql = $db->sql();

			$sql->select('#__social_clusters_categories');
			$sql->column('COUNT(1)');
			$sql->where('type', $type);

			$db->setQuery($sql);
			$total = $db->loadResult();

			if (!$total) {
				$workflow = ES::workflows(0, $type);
				$workflow->createDefaultWorkflow($legacy);
				$results[] = $this->getResultObj('Created default ' . $type . ' workflow successfully.', true);
			} else {
				$results[] = $this->getResultObj('Skipping ' . $type . ' workflows creation as the workflow already exists on the site.', true);
			}
		}

		// Marketplace
		$sql = $db->sql();

		$sql->select('#__social_marketplaces_categories');
		$sql->column('COUNT(1)');

		$db->setQuery($sql);
		$total = $db->loadResult();

		if (!$total) {
			$workflow = ES::workflows(0, 'marketplace');
			$workflow->createDefaultWorkflow();
			$results[] = $this->getResultObj('Created default marketplace workflow successfully.', true);
		} else {
			$results[] = $this->getResultObj('Skipping marketplace workflows creation as the workflow already exists on the site.', true);
		}

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class = $obj->state ? 'success' : 'error';
			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		return $this->output($result);
	}
}
