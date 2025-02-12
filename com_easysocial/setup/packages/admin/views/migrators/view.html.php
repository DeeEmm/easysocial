<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewMigrators extends EasySocialAdminView
{
	/**
	 * Default migration page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Set page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_MIGRATORS', 'COM_EASYSOCIAL_DESCRIPTION_MIGRATORS');

		// // ---------------------------------------
		// // debug - do not remove
		// $mnt = ES::maintenance();
		// $files = $mnt->getScriptFiles();
		// foreach($files as $file)
		// {
		// 	// var_dump($file);
		//     $state = $mnt->runScript($file);
		// }

		// $mnt = ES::maintenance();
		// $file = '/Users/kfteh/Projects/solo/workbench/joomla25/administrator/components/com_easysocial/updates/1.3.0/GeoTest.php';
		// var_dump($mnt->runScript($file));
		// exit;
		// debug end here
		// // ---------------------------------------

		parent::display('admin/migrators/default/default');
	}

	/**
	 * Displays the JomSocial migration form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function jomsocial()
	{
		// Set page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_MIGRATORS_JOMSOCIAL', 'COM_EASYSOCIAL_DESCRIPTION_MIGRATORS_JOMSOCIAL');

		// Get the migrator library
		$migrator = ES::migrators(__FUNCTION__);
		$installed = $migrator->isInstalled();
		$version = $migrator->getVersion();

		$hasAmazonPhotos = $migrator->hasAmazonPhotos();

		if ($installed) {
			$jsFields = $migrator->getCustomFields();

			// Get our own fields list
			$appsModel = ES::model('Apps');
			$fields = $appsModel->getApps(array('type' => SOCIAL_APPS_TYPE_FIELDS, 'group' => SOCIAL_FIELDS_GROUP_USER));

			// lets reset the $fiels so that the index will be the element type.
			if ($fields) {
				$tmp = array();
				foreach ($fields as $field) {
					$tmp[ $field->element ] = $field;
				}
				$fields = $tmp;
			}

			$fieldsMap = $migrator->getFieldsMap();

			$this->set('fields', $fields);
			$this->set('jsFields', $jsFields);
			$this->set('fieldsMap', $fieldsMap);

			$this->displayPurgeButton('Jomsocial');
		}

		$this->set('type', __FUNCTION__);
		$this->set('installed', $installed);
		$this->set('version', $version);
		$this->set('hasAmazonPhotos', $hasAmazonPhotos);

		parent::display('admin/migrators/form/default');
	}

	/**
	 * Displays the migration form for Community Builder
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function cb()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_MIGRATORS_CB', 'COM_EASYSOCIAL_HEADING_MIGRATORS_CB_DESC');
		$this->setDescription('');

		// Get the migrator library
		$migrator = ES::migrators(__FUNCTION__);
		$installed = $migrator->isInstalled();
		$version = $migrator->getVersion();

		// Fetch available custom fields from CB
		if ($installed) {
			// Get custom fields from JomSocial
			$cbFields = $migrator->getCustomFields();

			// Get known field mapping
			$mapping = $migrator->getFieldsMap();

			// Get our own fields list
			$appsModel = ES::model('Apps');
			$fields = $appsModel->getApps(array('type' => SOCIAL_APPS_TYPE_FIELDS, 'group' => SOCIAL_FIELDS_GROUP_USER));

			// Reset the $fields so that the index will be the element type.
			if ($fields) {
				$tmp = array();
				foreach ($fields as $field) {
					$tmp[ $field->element ] = $field;
				}
				$fields = $tmp;
			}

			// Go through each of the cb fields
			foreach($cbFields as &$cbField) {

				$mapped = isset($mapping[ $cbField->type ]) ? $mapping[ $cbField->type ] : '';
				$code = strtolower($cbField->name);

				// For gender fields
				if ($mapped && ($mapped == 'dropdown' || $mapped == 'checkbox') && strpos($code , 'gender') !== false) {
					$mapped = 'gender';
				}

				// For full name field
				if ($mapped && $mapped == 'textbox' && (strpos($code , 'givenname') !== false || strpos($code , 'familyname') !== false)) {
					$mapped = 'joomla_fullname';
				}

				if ($mapped && $mapped == 'datetime' && (strpos($code , 'birthday') !== false || strpos($code , 'birthdate') !== false)) {
					$mapped = 'birthday';
				}

				// address
				if ($mapped && ($mapped == 'textarea' || $mapped == 'textbox')
					&& (strpos($code, 'cb_address') !== false
						|| strpos($code, 'cb_street1') !== false
						|| strpos($code, 'cb_street2') !== false)) {

					$mapped = 'address';
				}

				if ($mapped && $mapped == 'textbox'
					&& (strpos($code, 'cb_state') !== false
						|| strpos($code, 'cb_city') !== false
						|| strpos($code, 'cb_zip') !== false)) {

					$mapped = 'address';
				}

				if ($mapped && ($mapped == 'country' || $mapped == 'dropdown')
					&& (strpos($code, 'cb_country') !== false
						|| strpos($code, 'cb_state') !== false)) {

					$mapped = 'address';
				}

				foreach ($fields as &$field) {
					$cbField->map_id = false;

					if ($mapped) {
						$cbField->map_id = $fields[ $mapped ]->id;
					}
				}

			}

			$this->set('fields', $fields);
			$this->set('cbFields', $cbFields);

			$this->displayPurgeButton('Cb');
		}

		$this->set('type', __FUNCTION__);
		$this->set('installed', $installed);
		$this->set('version', $version);

		parent::display('admin/migrators/form/default');
	}

	/**
	 * Displays the JomSocial's Group migration form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function jomsocialgroup()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_MIGRATORS_JOMSOCIAL_GROUP', 'COM_EASYSOCIAL_DESCRIPTION_MIGRATORS_JOMSOCIAL_GROUP');

		// Get the migrator library
		$migrator = ES::migrators(__FUNCTION__);
		$installed = $migrator->isInstalled();
		$version = $migrator->getVersion();

		if ($installed) {
			$this->displayPurgeButton('JomsocialGroup');
		}

		$hasAmazonPhotos = $migrator->hasAmazonPhotos();

		$this->set('type', __FUNCTION__);
		$this->set('installed', $installed);
		$this->set('version', $version);
		$this->set('hasAmazonPhotos', $hasAmazonPhotos);

		parent::display('admin/migrators/form/default');
	}

	/**
	 * Displays the JomSocial's event migration form
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function jomsocialevent()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_MIGRATORS_JOMSOCIAL_EVENT', 'COM_EASYSOCIAL_DESCRIPTION_MIGRATORS_JOMSOCIAL_EVENT');

		// Get the migrator library
		$migrator = ES::migrators(__FUNCTION__);
		$installed = $migrator->isInstalled();
		$version = $migrator->getVersion();

		if ($installed) {
			$this->displayPurgeButton('JomsocialEvent');
		}

		$hasAmazonPhotos = $migrator->hasAmazonPhotos();

		$this->set('type', __FUNCTION__);
		$this->set('installed', $installed);
		$this->set('version', $version);
		$this->set('hasAmazonPhotos', $hasAmazonPhotos);

		parent::display('admin/migrators/form/default');
	}

	/**
	 * Displays the EasyBlog migration form
	 *
	 * @since	1.1
	 * @access	public
	 */
	public function easyblog()
	{
		// Set page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_MIGRATORS_EASYBLOG', 'COM_EASYSOCIAL_DESCRIPTION_MIGRATORS_EASYBLOG');

		$migrator = ES::migrators(__FUNCTION__);
		$installed = $migrator->isInstalled();

		if ($installed) {
			$this->displayPurgeButton('Easyblog');
		}

		$this->set('type', __FUNCTION__);
		$this->set('installed', $installed);

		parent::display('admin/migrators/form/default');
	}

	/**
	 * Displays the Joomla migration form
	 *
	 * @since	1.1
	 * @access	public
	 */
	public function joomla()
	{
		// Set page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_MIGRATORS_JOOMLA', 'COM_EASYSOCIAL_DESCRIPTION_MIGRATORS_JOOMLA');

		$migrator = ES::migrators(__FUNCTION__);

		$this->displayPurgeButton('Joomla');
		$this->set('type', __FUNCTION__);

		parent::display('admin/migrators/form/default');
	}

	/**
	 * Displays the Kunena migration form
	 *
	 * @since	1.1
	 * @access	public
	 */
	public function kunena()
	{
		// Set page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_MIGRATORS_KUNENA', 'COM_EASYSOCIAL_DESCRIPTION_MIGRATORS_KUNENA');

		// Get the migrator library
		$migrator = ES::migrators(__FUNCTION__);
		$installed = $migrator->isInstalled();

		$this->set('installed', $installed);

		if ($installed) {
			$this->displayPurgeButton('Kunena');
		}

		$this->set('type', __FUNCTION__);
		
		parent::display('admin/migrators/form/default');
	}

	/**
	 * Displays the JomSocial's event migration form
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function jomsocialvideo()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_MIGRATORS_JOMSOCIAL_VIDEO', 'COM_EASYSOCIAL_DESCRIPTION_MIGRATORS_JOMSOCIAL_VIDEO');

		$migrator = ES::migrators(__FUNCTION__);
		$installed = $migrator->isInstalled();
		$version = $migrator->getVersion();
		$isLocalFiles = true;

		if ($installed) {
			$this->displayPurgeButton('JomsocialVideo');

			// get JomSocial config.
			require_once(JPATH_ROOT . '/components/com_community/libraries/core.php');
			$jsConfig = CFactory::getConfig();

			if ($jsConfig->get('enable_zencoder')) {
				$isLocalFiles = false;
			}
		}

		$this->set('type', __FUNCTION__);
		$this->set('installed', $installed);
		$this->set('version', $version);
		$this->set('isLocalFiles', $isLocalFiles);

		parent::display('admin/migrators/form/default');
	}

	/**
	 * Post process after purging migration history
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function purgeHistory($type)
	{
		return $this->redirect('index.php?option=com_easysocial&view=migrators&layout=' . $type);
	}

	/**
	 * Renders the purge button
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	private function displayPurgeButton($type)
	{
		JToolbarHelper::custom(strtolower($type), 'trash', '', JText::_('COM_ES_PURGE_MIGRATION_HISTORY'), false);
	}
}
