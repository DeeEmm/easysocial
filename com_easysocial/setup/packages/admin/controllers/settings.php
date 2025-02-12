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

class EasySocialControllerSettings extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('apply', 'save');
	}

	/**
	 * Resets the settings to the factory settings
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reset()
	{
		ES::checkToken();

		$page = $this->input->get('section', '', 'word');

		// there are few thing we cannot reset.
		// get the apikey 1st.
		$config = ES::config();
		$key = $config->get('general.key', '');
		$environment = $config->get('general.environment');

		$data = array();
		$data['general']['key'] = $key;
		$data['general']['environment'] = $environment;

		$json = json_encode($data);

		$table = ES::table('Config');
		$state = $table->load(array('type' => 'site'));

		if ($state) {
			// lets just store the apikey
			$table->value = $json;
			$state = $table->store();

			if (!$state) {
				$this->view->setMessage('COM_EASYSOCIAL_SETTINGS_ERROR_RESET', ES_ERROR);
				return $this->view->call(__FUNCTION__, $page);
			}

			$this->actionlog->log('COM_ES_ACTION_LOG_SETTINGS_RESET', 'settings');
		}

		$this->view->setMessage('COM_EASYSOCIAL_SETTINGS_RESET_SUCCESS');
		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Imports the settings from a json file
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function import()
	{
		ES::checkToken();

		$page = $this->input->get('page', '', 'word');

		$table = ES::table('Config');
		$state = $table->load(array('type' => 'site'));

		if ($state) {
			$file = $this->input->files->get('settings_file', []);

			if (!isset($file['tmp_name'])) {
				$this->view->setMessage('COM_EASYSOCIAL_SETTINGS_IMPORT_FILE_ERROR', ES_ERROR);
				return $this->view->call(__FUNCTION__, $page);
			}

			$path = $file['tmp_name'];
			$contents = file_get_contents($path);

			// Ensure that this is a json object
			$obj = json_decode($contents);

			if ($obj === false) {
				$this->view->setMessage('COM_EASYSOCIAL_SETTINGS_IMPORT_FILE_ERROR_INVALID', ES_ERROR);
				return $this->view->call(__FUNCTION__, $page);
			}

			$table->value = $contents;
			$state = $table->store();

			if (!$state) {
				$this->view->setMessage('COM_EASYSOCIAL_SETTINGS_IMPORT_ERROR', ES_ERROR);
				return $this->view->call(__FUNCTION__, $page);
			}

			$this->actionlog->log('COM_ES_ACTION_LOG_SETTINGS_IMPORTED', 'settings');
		}

		$this->view->setMessage('COM_EASYSOCIAL_SETTINGS_IMPORT_SUCCESS');

		return $this->view->call(__FUNCTION__, $page);
	}

	/**
	 * Allows caller to save the settings
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function save()
	{
		ES::checkToken();

		// Since there are more than 1 tasks are linked here, get the appropriate task here.
		$task = $this->getTask();
		$method = $task;
		$page = $this->input->get('page', '', 'default');
		$tab = $this->input->get('active', '', 'cmd');

		// Get the posted data.
		$post = $this->input->post->getArray();

		// Only load the config that is already stored.
		// We don't want to store everything as we want to have hidden settings.
		$configTable = ES::table('Config');
		$config = ES::registry();

		if ($configTable->load('site')) {
			$config->load($configTable->value);
		}

		$token = ES::token();

		if (!$post) {
			return $this->view->exception('COM_EASYSOCIAL_INVALID_POST_DATA');
		}

		// Some post vars are unwanted / unecessary because of the hidden inputs.
		$ignored = array('task', 'option', 'controller', 'view', $token, 'page', 'active');

		$updatedUserIndexing = false;
		$purgeSefCache = false;

		foreach ($post as $key => $value) {

			if (!in_array($key, $ignored)) {

				// Replace all _ with .
				$key = str_ireplace( '_' , '.' , $key );

				// If the value is an array, and there's only 1 index,
				// the input might need to be checked if it needs to be in an array form.
				// E.g: some,values,here,should,be,an,array
				if (is_array($value) && count($value) == 1) {
					$value = ES::makeArray($value[0], ',');
				}

				if ($key == 'users.indexer.name' || $key == 'users.indexer.email') {
					$previousVal = $config->get($key);
					if ($previousVal != $value) {
						$updatedUserIndexing = true;
					}
				}

				if ($key == 'seo.useid' || $key == 'seo.mediasef') {
					$previousVal = $config->get($key);
					if ($previousVal != $value) {
						$purgeSefCache = true;
					}
				}

				// check if system allow to enable this file cache or not.
				if ($key == 'seo.cachefile.enabled') {
					$previousVal = $config->get($key);

					// this mean, user is enabling the file cache setting.
					if ($previousVal != $value && $previousVal == 0) {
						$check = ES::verifySefCacheWrite();
						$hasError = $check->hasError;

						if ($hasError) {
							// skip enabling.
							// set warning message
							$config->set('seo.cachefile.warning', $check->message);
							continue;
						}
					}
				}

				// Prevent saving user everyone filter if the default filter is set to everyone. #4560
				if ($key == 'users.dashboard.everyone' && $value == '0' && $config->get('users.dashboard.start') == 'everyone') {
					$value = '1';
				}

				$config->set($key, $value);
			}
		}

		// special handling for Facebook scopes permission setting.
		$defaultScope = array('email');

		if (isset($post['oauth_facebook_scopes']) && $post['oauth_facebook_scopes']) {
			$scopes = array_merge($defaultScope, $post['oauth_facebook_scopes']);
			$scopes = implode(',', $scopes);

			$config->set('oauth.facebook.scopes', $scopes);
		} else {

			$scopes = implode(',', $defaultScope);
			$config->set('oauth.facebook.scopes', $scopes);
		}

		// special handling for amazon upload photo setting.
		if (isset($post['storage_photos'])) {
			if (!isset($post['storage_amazon_upload_photo'])) {
				$config->set('storage.amazon.upload.photo', 0);
			} else {
				$config->set('storage.amazon.upload.photo', 1);
			}
		}

		// Convert the config object to a json string.
		$jsonString = $config->toString();

		$configTable = ES::table('Config');

		if (!$configTable->load('site')) {
			$configTable->type = 'site';
		}

		$configTable->set('value', $jsonString);

		// Try to store the configuration.
		if (!$configTable->store()) {
			$this->view->setMessage($configTable->getError(), ES_ERROR);
			return $this->view->call($method, $page);
		}

		// Store the image for login page.
		$file = $this->input->files->get('login_image', '');

		// Try to upload the profile's avatar if required
		if (!empty($file['tmp_name'])) {
			ES::login()->uploadImage($file);
		}

		// Check if any of the configurations are stored as non local
		if ($config->get('storage.amazon.access') && $config->get('storage.amazon.secret') && $page == 'storage') {

			// Ensure that there is a settings that uses amazon, otherwise it's pointless to even create any buckets
			$storages = array('avatars', 'files', 'photos', 'videos', 'links');
			$enabled = false;

			foreach ($storages as $storageType) {
				$storagePlace = $config->get('storage.' . $storageType);

				if ($storagePlace == 'amazon') {
					$enabled = true;
					break;
				}
			}

			if ($enabled) {
				$bucket = $config->get('storage.amazon.bucket');

				$storage = ES::storage('Amazon');

				// If the bucket is set, check if it exists.
				if ($bucket && !$storage->containerExists($bucket)) {
					$storage->createContainer($bucket);
				}

				// If the bucket is empty, we initialize a new bucket based on the domain name
				if (!$bucket) {
					// Initialize the remote storage
					$bucket = $storage->init();
					$config->set('storage.amazon.bucket', $bucket);

					$configTable->set('value', $config->toString());
					$configTable->store();
				}
			}
		}

		// Process the default avatar and cover
		$defaultPictures = array('user', 'group', 'page', 'event');

		foreach ($defaultPictures as $group) {

			$file = $this->input->files->get($group . '_avatar', '');

			if (isset($file['tmp_name']) && !$file['error']) {
				$configTable->updateDefaultAvatar($file, $group);
			}

			$file = $this->input->files->get($group . '_cover', '');

			if (isset($file['tmp_name']) && !$file['error']) {
				$configTable->updateDefaultCover($file, $group);
			}
		}

		// Apple key file
		$appleKeyfile = $this->input->files->get('apple_keyfile', '');

		if (isset($appleKeyfile['tmp_name']) && !$appleKeyfile['error']) {
			$keyUpload = $configTable->updateAppleKeyfile($appleKeyfile);

			if ($keyUpload) {
				$config->set('oauth.apple.keyfile', $appleKeyfile['name']);
				$configTable->set('value', $config->toString());
				$configTable->store();
			}
		}

		// System logo
		$file = $this->input->files->get('email_logo', '');

		if (isset($file['tmp_name']) && !$file['error']) {
			$configTable->updateLogo($file);
		}

		// System logo
		$sharerLogo = $this->input->files->get('sharer_logo', '');

		if (isset($sharerLogo['tmp_name']) && !$sharerLogo['error']) {
			$configTable->updateSharerLogo($sharerLogo);
		}

		// Mobile shortcut icon
		$icon = $this->input->files->get('mobile_icon', '');

		if (isset($icon['tmp_name']) && !$icon['error']) {
			$configTable->updateIcon($icon);
		}

		// Video logo
		$videoLogo = $this->input->files->get('video_logo', '');

		if (isset($videoLogo['tmp_name']) && !$videoLogo['error']) {
			$configTable->updateVideoLogo($videoLogo);
		}

		// Video watermark
		$videoWatermark = $this->input->files->get('video_watermark', '');

		if (isset($videoWatermark['tmp_name']) && !$videoWatermark['error']) {

			// check if videomark dimension
			if (!$configTable->checkUploadImageDimension($videoWatermark, SOCIAL_VIDEO_WATERMARK_WIDTH, SOCIAL_VIDEO_WATERMARK_HEIGHT)) {

				$message = JText::sprintf('COM_ES_SETTINGS_VIDEO_WATERMARK_DIMENSION_ERROR', SOCIAL_VIDEO_WATERMARK_WIDTH, SOCIAL_VIDEO_WATERMARK_HEIGHT);

				$this->view->setMessage($message, ES_ERROR);
				return $this->view->call($method, $page, $tab);
			}

			$configTable->updateVideoWatermark($videoWatermark);
		}

		// purge sef cache if needed
		if ($purgeSefCache) {

			$urlModel = ES::model('Urls');

			// before purge, we need to get all the customized urls
			$customUrls = $urlModel->getCustomUrls();

			$state = $urlModel->purge();

			if ($state) {
				$cache = ES::fileCache();
				$cache->purge($customUrls);
			}
		}

		$message = ($updatedUserIndexing) ? 'COM_EASYSOCIAL_SETTINGS_SAVED_SUCCESSFULLY_WITH_USER_INDEXING_UPDATED' : 'COM_EASYSOCIAL_SETTINGS_SAVED_SUCCESSFULLY';

		$this->actionlog->log('COM_ES_ACTION_LOG_SETTINGS_SAVED', 'settings', [
				'section' => ucfirst($page),
				'sectionLink' => 'index.php?option=com_easysocial&view=settings&layout=form&page=' . $page
			]);

		$this->view->setMessage($message);

		return $this->view->call($method, $page, $tab);
	}

	/**
	 * Restores the logo to the default logo
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function restoreImage()
	{
		ES::checkToken();

		$type = $this->input->get('type');

		$folder = explode('_', $type);
		$foldersType = array('avatar', 'cover');

		if (!ES::hasOverride($type)) {
			return $this->view->exception('COM_ES_NO_LOGO');
		}

		if (in_array($folder[1], $foldersType)) {
			$path = JPATH_ROOT . '/images/easysocial_override/' . $folder[0] . '/' . $folder[1];
			JFolder::delete($path);
		} else {
			$path = JPATH_ROOT . '/images/easysocial_override/' . $type . '.png';
			JFile::delete($path);
		}

		return $this->ajax->resolve();
	}

	/**
	 * Allow caller to delete the login image
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteLoginImage()
	{
		$login = ES::login();

		$login->deleteImage();

		return $this->ajax->resolve();
	}

	/**
	 * Purge text avatars from the site
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function purgeTextAvatars()
	{
		$textavatar = ES::textavatar();
		$textavatar->purgeCache();

		$this->actionlog->log('COM_ES_ACTION_LOG_TEXT_AVATARS_PURGED', 'settings');

		return $this->view->call('reset', 'users');
	}

	/**
	 * Allow caller to validate the api key of the client
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function validateApiKey()
	{
		$key = $this->config->get('general.key');
		$product = 'easysocial';

		$ch = curl_init(SOCIAL_API_VALIDATER);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('key' => $key, 'product' => $product, 'option' => 'com_accounts', 'task' => 'dashboard.verify'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		$result = json_decode($result);

		if ($result->code != 200) {
			return ES::ajax()->resolve(false);;
		}

		return ES::ajax()->resolve(true);
	}

	/**
	 * Get JWT token from server
	 *
	 * @since   3.2
	 * @access  public
	 */
	public function generateJWTToken()
	{
		$token = ES::generateJWTToken();

		if (!$token) {
			return ES::ajax()->resolve(JText::_('COM_ES_GENERATE_TOKEN_FAILED'));
		}

		return ES::ajax()->resolve($token);
	}
}
