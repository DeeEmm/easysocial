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

class EasySocialControllerInstallationFields extends EasySocialSetupController
{
	/**
	 * Installs required custom fields
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		// Get the group of apps to install.
		$group = $this->input->get('group', '', 'cmd');

		// Get the temporary path to the archive
		$tmpPath = $this->input->get('path', '', 'default');

		// Get the archive path
		$archivePath = $tmpPath . '/' . $group . 'fields.zip';

		// Where the extracted items should reside.
		$path = $tmpPath . '/' . $group . 'fields';

		// Detect if the target folder exists
		$target = JPATH_ROOT . '/media/com_easysocial/apps/fields/' . $group;

		// Try to extract the archive first
		$state = $this->extractArchive($archivePath, $path);

		if (!$state) {
			$result = new stdClass();
			$result->state = false;
			$result->message = "There was some errors when extracting fields.zip. Please check folder's permission.";

			return $this->output($result);
		}

		// If the apps folder does not exist, create it first.
		if (!JFolder::exists($target)) {
			$state 	= JFolder::create( $target );

			if (!$state) {
				$result = new stdClass();
				$result->state = false;
				$result->message = JText::sprintf('There was some permission errors when trying to create the folder below:<br /><br />%1%s', $target);

				return $this->output($result);
			}
		}

		// Get a list of apps within this folder.
		$fields = JFolder::folders( $path , '.' , false , true );
		$totalFields = 0;

		// If there are no apps to install, just silently continue
		if (!$fields) {
			$result = new stdClass();
			$result->state = true;
			$result->message = 'There are no fields to be installed currently. Skipping this.';

			return $this->output($result);
		}

		$results = array();

		// Go through the list of apps on the site and try to install them.
		foreach ($fields as $field) {
			$results[] = $this->installField($field, $group);
			$totalFields += 1;
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

	/**
	 * Installs a single custom field
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function installField($path, $group = 'user')
	{
		$this->engine();

		// Retrieve the installer library.
		$installer = ES::get('Installer');

		// Get the element
		$element = basename($path);

		// Try to load the installation from path.
		$state = $installer->load($path);

		// Try to load and see if the previous field apps already has a record
		$oldField = ES::table('App');
		$fieldExists = $oldField->load(array('type' => SOCIAL_APPS_TYPE_FIELDS , 'element' => $element, 'group' => $group));

		// If there's an error, we need to log it down.
		if (!$state) {

			$result = $this->getResultObj(JText::sprintf( 'COM_EASYSOCIAL_INSTALLATION_FIELD_ERROR_LOADING_FIELD', ucfirst($element)), false);

			return $result;
		}

		// Let's try to install it now.
		$app = $installer->install();

		// If there's an error installing, log this down.
		if ($app === false) {

			$result = $this->getResultObj(JText::sprintf('COM_EASYSOCIAL_INSTALLATION_FIELD_ERROR_INSTALLING_FIELD', ucfirst($element)), false);

			return $result;
		}

		// If the field apps already exist, use the previous title.
		if ($fieldExists) {
			$app->title = $oldField->title;
			$app->alias = $oldField->alias;
		}

		// Ensure that the field apps is published
		$app->state	= $fieldExists ? $oldField->state : SOCIAL_STATE_PUBLISHED;
		$app->store();

		$result = $this->getResultObj(JText::sprintf('COM_EASYSOCIAL_INSTALLATION_FIELD_SUCCESS_INSTALLING_FIELD', ucfirst($element)), true);

		return $result;
	}
}
