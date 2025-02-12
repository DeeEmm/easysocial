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

class SocialInstallerFields extends JObject
{
	private $installer = null;

	public function __construct($installer)
	{
		$this->installer = $installer;
	}

	/**
	 * Discovers the app
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discover()
	{
		// Check if this element is already installed before
		$element = $this->installer->element;
		$group = $this->installer->group;
		$type = $this->installer->type;

		$model = ES::model('Apps');
		$installed = $model->isAppInstalled($element , $group , $type);

		// If app has already been installed, skip this
		if ($installed) {
			return false;
		}

		// Store it into the database once the installation is successful.
		$app = ES::table('App');

		// Set the type of the application.
		$app->type = SOCIAL_APPS_TYPE_FIELDS;

		// Set the group of the application
		$app->group = $group;

		// Set the element of the application.
		$app->element = $element;

		// Determines if the app is a core app.
		$app->core = $this->installer->isCore();

		// Determines if this app is used for processing only
		$app->system = 0;

		// Determines if the app is a unique app.
		$app->unique = $this->installer->isUnique();

		// Set the application title.
		$app->title = $this->installer->getTitle();

		// Set the application alias
		$app->alias = $this->installer->getAlias();

		// Set the parameters for the app.
		$params = $this->installer->getParams();
		$app->params = $params->toString();

		// Determine if this app has a widget layout
		$app->widget = $this->installer->isWidget();

		// Determines if this app is installable by the user
		$app->installable = $this->installer->isInstallable();

		// Set the version for the app so we don't need to always query the file.
		$app->version = $this->installer->getVersion();

		// Set the app state to discovered
		$app->state = SOCIAL_APP_STATE_DISCOVERED;

		// Set the creation date for the app.
		$app->created = ES::date()->toMySQL();

		if (!$app->visible) {
			$app->visible = 1;
		}

		if (!$app->ordering) {
			$app->ordering = 0;
		}

		if (!$app->default) {
			$app->default = 0;
		}

		// Try to store the app.
		$state = $app->store();

		// If there's problem storing the app, set the errors here.
		if (!$state) {
			$this->setError($app->getError());

			return false;
		}

		return true;
	}

	/**
	 * Installation method
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function install()
	{
		// Get the element from the installer.
		$element = $this->installer->element;

		// Get the group from the installer.
		$group = $this->installer->group;

		// Get the type from the installer.
		$type = $this->installer->type;

		// Get the destination path.
		$destination = SOCIAL_FIELDS . '/' . $group . '/' . $element;

		// If field has been installed before, we remove the folder first for clean installation
		// If the installer manifest path is the same the destination path, then it is coming from discover, and as such don't clean up.
		$model = ES::model('Apps');

		// Normalize the folder path during checking especially for windows server user
		if ($model->isAppInstalled($element, $group, $type) && ES::cleanPath(dirname($this->installer->path)) !== ES::cleanPath($destination)) {
			$state = $this->installer->cleanup($destination);
		}

		// Create the destination folder if it doesn't exist.
		$state = $this->installer->createFolder($destination);

		// Copy packaged files into the destination folder.
		$this->installer->copyContents($destination, $element);

		// Copy manifest file into destination folder.
		$manifestFile = $destination . '/' . $element . '.xml';

		$this->installer->copyManifest($manifestFile);

		// Initialize any necessary SQL queries from the plugin.
		$this->installer->runQueries();

		// Install privacy rules of this field
		$privacyModel = ES::model('Privacy');
		$privacyFiles = JFolder::files($destination, '.privacy$', true, true);

		foreach ($privacyFiles as $file) {
			$privacyModel->install($file);
		}

		// Install alert rules of this field
		$alertModel = ES::model('Alert');
		$alertFiles = JFolder::files($destination, '.alert$', true, true);

		foreach ($alertFiles as $file) {
			$alertModel->install($file);
		}

		// Install point rules of this field
		$pointsModel = ES::model('Points');
		$pointsFiles = JFolder::files($destination, '.points$', true, true);

		foreach ($pointsFiles as $file) {
			$pointsModel->install($file);
		}

		// Install badge rules of this field
		$badgesModel = ES::model('Badges');
		$badgesFiles = JFolder::files($destination, '.badge$', true, true);

		foreach ($badgesFiles as $file) {
			$badgesModel->install($file);
		}

		// Allow application to run it's own `install` method if necessary.
		$callbackFile = $destination . '/install.php';
		$response = $this->installer->callback('install', $callbackFile);

		if ($response === false) {
			$this->installer->callback('error', $callbackFile);

			// Display some errors here.
			return false;
		}

		// Store it into the database once the installation is successful.
		$app = ES::table('App');
		$exists = $app->load(array('element' => $element, 'group' => $group, 'type' => SOCIAL_APPS_TYPE_FIELDS));

		// Only init the app in the db if the ap does not exist before or if the app is in discovered state
		if (!$exists || $app->state == SOCIAL_APP_STATE_DISCOVERED) {
			// Set the type of the application.
			$app->type = SOCIAL_APPS_TYPE_FIELDS;

			// Set the group of the application
			$app->group = $group;

			// Set the element of the application.
			$app->element = $element;

			// Set the application title.
			$app->title = $this->installer->getTitle();

			// Determines if the app is a core app.
			$app->core = $this->installer->isCore();

			// Determines if this app is used for processing only
			$app->system = 0;

			// Determines if the app is a unique app.
			$app->unique = $this->installer->isUnique();

			// Set the parameters for the app.
			$params = $this->installer->getParams();
			$app->params = $params->toString();

			// If this is new application, we try to unpublish it by default.
			if (is_null($app->id) || !$app->id) {
				$app->state = SOCIAL_STATE_UNPUBLISHED;
			}

			// Core apps should always be published, or
			// if admin attempt to install fields from discover. #2161
			if ($app->core || $app->state == SOCIAL_APP_STATE_DISCOVERED) {
				$app->state = SOCIAL_STATE_PUBLISHED;
			}

			// Determine if this app has a widget layout
			$app->widget = $this->installer->isWidget();

			// Set the creation date for the app.
			$app->created = ES::date()->toMySQL();

			// Set the application alias
			if (!$app->alias) {
				$app->alias = '';
			}

			if (!$app->visible) {
				$app->visible = 1;
			}

			if (!$app->ordering) {
				$app->ordering = 0;
			}

			if (!$app->default) {
				$app->default = 0;
			}

			// Determines if this app is installable by the user
			$app->installable = $this->installer->isInstallable();
		}

		// Set the version for the app from the manifest file
		$app->version = $this->installer->getVersion();

		// Try to store the app.
		$state = $app->store();

		// If there's problem storing the app, set the errors here.
		if (!$state) {
			$this->setError($app->getError());
		}

		// Triggers the success callback here. Plugin might want to perform specific stuffs.
		$result = new stdClass();
		$result->output = $this->installer->callback('success', $callbackFile);

		// Return the application description.
		$result->desc = $this->installer->getDescription();

		// Set a temporary variable.
		$app->result = $result;

		return $app;
	}

	public function uninstall()
	{
	}
}
