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

class EasySocialControllerThemes extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('toggleDefault', 'makeDefault');
		$this->registerTask('apply', 'store');
		$this->registerTask('save', 'store');
	}

	/**
	 * Saves the contents of a theme file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveFile()
	{
		ES::checkToken();
		$element = $this->input->get('element', '', 'cmd');
		$id = $this->input->get('id', '', 'default');
		$contents = $this->input->get('contents', '', 'raw');

		$model = ES::model('Themes');
		$file = $model->getFile($id, $element);

		// Save the file now
		$state = $model->write($file, $contents);

		if (!$state) {
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_THEMES_SAVE_ERROR', $file->override), ES_ERROR);
			return $this->view->call(__FUNCTION__, $file);
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_THEMES_SAVED_FILE', 'themes', [
				'theme' => ucwords($element),
				'filename' => $file->title,
				'link' => 'index.php?option=com_easysocial&view=themes&layout=edit&element=' . $element
			]);

		// Document the changes
		$table = ES::table('ThemeOverrides');
		$table->load(array('file_id' => $file->override));
		$table->file_id = $file->override;
		$table->notes = $this->input->get('notes', '', 'default');
		$table->contents = $contents;
		$table->store();

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_THEMES_SAVE_SUCCESS', $file->override));
		return $this->view->call(__FUNCTION__, $file);
	}

	/**
	 * Save custom.css
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function saveCustomCss()
	{
		ES::checkToken();

		$model = ES::model('themes');
		$path = $model->getCustomCssTemplatePath();

		$contents = $this->input->get('contents', '', 'raw');

		JFile::write($path, $contents);

		$this->actionlog->log('COM_ES_ACTION_LOG_THEMES_SAVED_CSS', 'themes');

		$this->info->set(JText::sprintf('COM_ES_THEMES_CUSTOM_CSS_SAVE_SUCCESS', $path), 'success');

		$redirect = 'index.php?option=com_easysocial&view=themes&layout=custom';

		return ES::redirect($redirect);
	}

	/**
	 * Allows caller to revert a theme file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function revert()
	{
		ES::checkToken();

		$element = $this->input->get('element', '', 'cmd');
		$id = $this->input->get('id', '', 'default');
		$contents = $this->input->get('contents', '', 'raw');

		$model = ES::model('Themes');
		$file = $model->getFile($id, $element);

		// Save the file now
		$state = $model->revert($file);

		if (!$state) {
			$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_THEMES_DELETE_ERROR', $file->override), ES_ERROR);
			return $this->view->call(__FUNCTION__, $file);
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_THEMES_REVERTED_FILE', 'themes', [
				'theme' => ucwords($element),
				'filename' => $file->title,
				'link' => 'index.php?option=com_easysocial&view=themes&layout=edit&element=' . $element
			]);

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_THEMES_DELETE_SUCCESS', $file->override));

		return $this->view->call(__FUNCTION__, $file);
	}

	/**
	 * Set's the template as the default template
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function makeDefault()
	{
		// Check for request forgeries
		ES::checkToken();

		$element = $this->input->get('cid', '', 'default');
		$element = $element[0];
		$element = strtolower($element);

		// Get the configuration object
		$configTable = ES::table('Config');
		$config = ES::registry();

		if ($configTable->load('site')) {
			$config->load($configTable->value);
		}

		// Convert the config object to a json string.
		$config->set('theme.site', $element);

		// Convert the configuration to string
		$jsonString = $config->toString();

		// Store the setting
		$configTable->value	= $jsonString;

		if (!$configTable->store()) {
			$this->view->setMessage($configTable->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_THEMES_MAKE_DEFAULT', 'themes', [
				'theme' => ucwords($element),
				'link' => 'index.php?option=com_easysocial&view=themes&layout=edit&element=' . $element
			]);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Stores the theme parameter
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();

		$element = $this->input->get('element', '', 'word');

		if (!$element) {
			return $this->view->exception('COM_EASYSOCIAL_THEMES_INVALID_ELEMENT_PROVIDED');
		}

		$model = ES::model('Themes');
		$data = $this->input->post->getArray();

		// Remove unwanted stuffs from the post data.
		unset($data[ES::token()]);
		unset($data['option']);
		unset($data['controller']);
		unset($data['task']);
		unset($data['element']);

		$state = $model->update($element, $data);

		if (!$state) {
			return $this->view->exception($model->getError());
		}

		$this->view->setMessage(JText::sprintf('COM_EASYSOCIAL_THEMES_SETTINGS_SAVED_SUCCESS', $element));

		return $this->view->call(__FUNCTION__, $this->getTask(), $element);
	}

	/**
	 * Installs a new theme on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload()
	{
		// Get the file from the server.
		$file = $this->input->files->get('package', null, 'raw');

		// Allowed extensions for file name.
		$allowedExtension = array('zip');
		$allowedMimeType = array('application/zip', 'application/x-zip-compressed');

		// There could be possibility the server reject the file upload
		if (empty($file['tmp_name'])) {
			$this->view->setMessage('COM_EASYSOCIAL_INSTALL_UPLOAD_ERROR_INVALID_TYPE', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// We just ensure that the mime is a zip file.
		if (!in_array($file['type'], $allowedMimeType)) {
			$this->view->setMessage('COM_EASYSOCIAL_INSTALL_UPLOAD_ERROR_INVALID_TYPE', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get information about the file that was uploaded
		$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

		// Double check to ensure that the file name really contains .zip_close(zip)
		if (!in_array($extension, $allowedExtension)) {
			$this->view->setMessage('COM_EASYSOCIAL_INSTALL_UPLOAD_ERROR_INVALID_TYPE', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Get the themes model
		$model = ES::model('Themes');
		$element = $model->install($file);

		if (!$element) {
			$this->view->setMessage('COM_EASYSOCIAL_INSTALL_UPLOAD_ERROR_INVALID_TYPE', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_THEMES_INSTALLED_NEW', 'themes', [
				'theme' => ucwords($element),
				'link' => 'index.php?option=com_easysocial&view=themes&layout=edit&element=' . $element
			]);

		$this->view->setMessage('COM_EASYSOCIAL_INSTALL_UPLOAD_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}
}
