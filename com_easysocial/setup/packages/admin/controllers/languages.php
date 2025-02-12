<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerLanguages extends EasySocialController
{
	/**
	 * Purges the cache of language items
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purge()
	{
		ES::checkToken();

		$model = ES::model('Languages');
		$model->purge();

		$this->view->setMessage('COM_EASYSOCIAL_LANGUAGES_PURGED_SUCCESSFULLY');

		$this->actionlog->log('COM_ES_ACTION_LOG_LANGUAGES_PURGE_CACHE', 'languages', [
			'link' => 'index.php?option=com_easysocial&view=languages'
		]);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to remove languages
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function uninstall()
	{
		ES::checkToken();

		// Get the list of items to be deleted
		$ids = $this->input->get('cid', array(), 'array');

		foreach ($ids as $id) {
			$id = (int) $id;

			$table = ES::table('Language');
			$table->load($id);

			if (!$table->isInstalled()) {
				$table->delete();
				continue;
			}

			$table->uninstall();
			$table->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_LANGUAGES_UNINSTALL_LANGUAGE', 'modules', [
				'link' => 'index.php?option=com_easysocial&view=languages',
				'languageTitle' => $table->title
			]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_LANGUAGES_UNINSTALLED_SUCCESS');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Installs a language file
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_LANGUAGES_INVALID_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$table = ES::table('Language');
			$table->load($id);

			$state = $table->install();

			if (!$state) {
				$error = ES::getMessageQueue();
				$this->view->setMessage($error->message, $error->type);
				return $this->view->call(__FUNCTION__);
			}

			$this->actionlog->log('COM_ES_ACTION_LOG_LANGUAGES_INSTALLED_LANGUAGE', 'modules', [
				'link' => 'index.php?option=com_easysocial&view=languages',
				'languageTitle' => $table->title
			]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_LANGUAGES_INSTALLED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Updates the site with the latest language files
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function update()
	{
		ES::checkToken();

		$config = ES::config();
		$key = $config->get('general.key');

		$connector = ES::connector(SOCIAL_UPDATER_LANGUAGE);

		$contents = $connector
						->setMethod('POST')
						->addQuery('key', $key)
						->execute()
						->getResult();

		if (!$contents) {
			$result = new stdClass();
			$result->message = 'No language found';

			return $this->ajax->reject($result->message);
		}

		// Decode the result
		$result	= json_decode($contents);

		if ($result->code != 200) {
			$return = base64_encode('index.php?option=com_easysocial&view=languages');

			return $this->ajax->reject($result->error);
		}

		// Go through each of the languages now
		foreach ($result->languages as $language) {

			$language = (object) $language;

			// Check if the language was previously installed thorugh our system.
			// If it does, load it instead of overwriting it.
			$table = ES::table('Language');
			$exists = $table->load(array('locale' => $language->locale));

			// We do not want to bind the id
			unset($language->id);

			// Since this is the retrieval, the state should always be disabled
			if (!$exists) {
				$table->state = SOCIAL_STATE_UNPUBLISHED;
			}

			// If the language file has been installed, we want to check the last updated time
			if ($exists && $table->state == SOCIAL_LANGUAGES_INSTALLED) {

				// Then check if the language needs to be updated. If it does, update the ->state to SOCIAL_LANGUAGES_NEEDS_UPDATING
				// We need to check if the language updated time is greater than the local updated time
				$languageTime = strtotime($language->updated);
				$localLanguageTime = strtotime($table->updated);

				if ($languageTime > $localLanguageTime && $table->state == SOCIAL_LANGUAGES_INSTALLED) {
					$table->state = SOCIAL_LANGUAGES_NEEDS_UPDATING;
				}
			}

			// Set the title
			$table->title = $language->title;
			$table->locale = $language->locale;
			$table->translator = $language->translator;
			$table->updated = $language->updated;
			$table->progress = $language->progress;

			// Update the table with the appropriate params
			$params = ES::registry();

			$params->set('download', $language->download);
			$params->set('md5', $language->md5);
			$table->params = $params->toString();

			$table->store();
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_LANGUAGES_DISCOVER', 'languages', [
			'link' => 'index.php?option=com_easysocial&view=languages'
		]);

		return $this->view->call(__FUNCTION__, $result->languages);
	}
}
