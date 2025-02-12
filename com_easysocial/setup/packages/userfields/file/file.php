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

ES::import('admin:/includes/fields/dependencies');

// Import necessary library
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class SocialFieldsUserFile extends SocialFieldItem
{
	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegister(&$post, &$registration)
	{
		// Get error.
		$error = $registration->getErrors($this->inputName);

		// Get the value.
		$value = !empty($post[$this->inputName]) ? ES::json()->decode($post[$this->inputName]) : array();

		$count = empty($value) ? 0 : count($value);

		// determine whether need to show the file preview image or not
		// we need check for the user if they click previous button during registration
		$tmp = $count ? true : false;

		if ($tmp) {

			$tmpdata = $value;
			$tmpfiles = array();

			foreach ($tmpdata as $item) {

				// load back the temporary file data
				$tmpTbl = ES::table('tmp');
				$tmpTbl->load($item->id);

				$obj = ES::makeObject($tmpTbl->value);
				$data = new stdClass();

				// file temporary id and file name
				$data->id = $tmpTbl->id;
				$data->name = $obj->name;

				$tmpfiles[] = $data;
			}

			$value = $tmpfiles;
		}

		$this->set('error', $error);
		$this->set('limit', $this->params->get('file_limit', 0));
		$this->set('value', $value);
		$this->set('count', $count);
		$this->set('tmp', $tmp);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegisterValidate(&$post, &$registration)
	{
		// Check if this field is required and if there are files uploaded
		if ($this->isRequired()) {
			if (empty($post[$this->inputName])) {
				$this->setError(JText::_('PLG_FIELDS_FILE_VALIDATION_REQUIRED_TO_UPLOAD'));
				return false;
			}

			$files = json_decode($post[$this->inputName]);

			if (empty($files)) {
				$this->setError(JText::_('PLG_FIELDS_FILE_VALIDATION_REQUIRED_TO_UPLOAD'));
				return false;
			}
		}

		return true;
	}

	/**
	 * Once a user registration is completed, the field should automatically
	 * move the temporary file into the user's folder if required.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegisterAfterSave(&$post, $object)
	{
		// Copy the files over
		if (!empty($post[$this->inputName])) {

			$result = array();
			$filenames = array();

			$files = json_decode($post[$this->inputName]);

			foreach ($files as $row) {

				$state = true;

				$data = new stdClass();

				// If it is a tmp file, then we gotta move the file out to user directory
				if ($row->tmp) {
					$data = $this->copyFromTemporary($row->id, $object);

					if ($data === false) {
						continue;
					}

					$result[] = $data;
					$filenames[] = $data->name;
				}
			}

			$post[$this->inputName] = array(
				'data' => json_encode($result),
				'raw' => implode(' ', $filenames)
			);
		}

		return true;
	}

	/**
	 * Displays the field form when user is being edited.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onEdit(&$post, &$object, $errors)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		if (is_array($value) && isset($value['data'])) {
			$value = $value['data'];
		}

		$value = json_decode($value);
		$value = $this->prepareFiles($value, $object);

		$count = empty($value) ? 0 : count($value);
		$limit = $this->params->get('file_limit', 0);
		$error = $this->getError($errors);

		$tmp = $count ? false : true;

		$this->set('user', $object);
		$this->set('error', $error);
		$this->set('value', $value);
		$this->set('count', $count);
		$this->set('limit', $limit);
		$this->set('tmp', $tmp);

		return $this->display();
	}

	/**
	 * Determines whether there are errors during editing process.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEditValidate(&$post)
	{
		if ($this->isRequired()) {
			if (empty($post[$this->inputName])) {
				$this->setError(JText::_('PLG_FIELDS_FILE_VALIDATION_REQUIRED_TO_UPLOAD'));
				return false;
			}

			$files = json_decode($post[$this->inputName]);
			$file = false;

			// Check if the array of data is empty
			foreach ($files as $data) {

				if (empty($data)) {
					continue;
				}

				// If it reached here means the data is exists.
				$file = true;
			}

			if (!$file) {

				// Reset back the file data.
				$post[$this->inputName] = '';

				$this->setError(Jtext::_('PLG_FIELDS_FILE_VALIDATION_REQUIRED_TO_UPLOAD'));
				return false;
			}
		}

		return true;
	}

	/**
	 * Before an object is saved, this would get triggered
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$object)
	{
		$result = array();
		$filenames = array();

		if (is_array($this->field->data)) {
			$originals = json_decode($this->field->data['data']);
		}

		if (empty($originals)) {
			$originals = array();
		}

		$existings = array();

		if (!empty($post[$this->inputName])) {

			$files = json_decode($post[$this->inputName]);

			foreach ($files as $row) {
				if (!$row) {
					continue;
				}

				$state = true;

				$data = new stdClass();

				// Copy the file to the respective folder
				if ($row->tmp) {
					$data = $this->copyFromTemporary($row->id, $object);

					if ($data === false) {
						continue;
					}

				} else {
					// If it is not a tmp file, means it is an existing file

					$state = false;

					// Search for the data from the originals
					foreach ($originals as $original) {
						if ($row->id == $original->id) {
							$state = true;

							$existings[] = $original->id;

							$data = $original;

							break;
						}
					}
				}

				if ($state) {
					$result[] = $data;
					$filenames[] = $data->name;
				}
			}
		}

		// If the original files are no longer in the set of existing files, we need to delete them from the filesystem
		foreach ($originals as $original) {

			if (!in_array($original->id, $existings)) {
				$file = ES::table('file');
				$file->load($original->id);

				// We need to append the correct path
				$appendPath = $object->getType() . '/' . $object->id;

				// Prior to 2.0, files are stored in /media/com_easysocial/files/fields/[FIELD_ID]/[HASH]
				$legacyFile = $file->getStoragePath() . '/' . $file->getHash();
				$legacyExists = JFile::exists($legacyFile);

				if ($legacyExists) {
					$file->delete();
				} else {
					$file->delete(null, $appendPath);
				}
			}
		}

		$post[$this->inputName] = array(
			'data' => json_encode($result),
			'raw' => implode(' ', $filenames)
		);
	}

	/**
	 * Copies file from the temporary folder into the appropriate folder
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function copyFromTemporary($id, $object)
	{
		// Get tmp table
		$tmp = ES::table('tmp');
		$state = $tmp->load($id);

		if (!$state) {
			$this->setError('PLG_FIELDS_FILE_ERROR_UNABLE_TO_LOAD_TEMPORARY_DATA');
			return false;
		}

		$tmpFile = json_decode($tmp->value);

		// Get file table
		$file = ES::table('file');
		$file->name = $tmpFile->name;
		$file->size = $tmpFile->size;
		$file->mime = $tmpFile->mime;
		$file->uid = $this->field->id;
		$file->type = SOCIAL_APPS_TYPE_FIELDS;
		$file->state = 1;

		// The user id does not necessary have to be the profile id because this file could be uploaded by admin for this user
		$file->user_id = $this->my->id;

		// Get the source path
		$source = $tmpFile->path . '/' . $tmpFile->hash;
		$storage = $file->getStoragePath() . '/' . $this->group . '/' . $object->id;

		// If the folder already exists, do not create it
		$state = true;

		if (!JFolder::exists($storage)) {
			$state = JFolder::create($storage);
		}

		if (!$state) {
			$this->setError('PLG_FIELDS_FILE_ERROR_UNABLE_TO_CREATE_STORAGE_FOLDER');
			return false;
		}

		// Get the destination path.
		$destination = $storage . '/' . $file->getHash();

		// Move the file from tmp directory to user path
		$state = JFile::move($source, $destination);

		if (!$state) {
			$this->setError('PLG_FIELDS_FILE_ERROR_UNABLE_TO_MOVE_FILE');
			return false;
		}

		$state = $file->store();

		if (!$state) {
			$this->setError('PLG_FIELDS_FILE_ERROR_UNABLE_TO_STORE_FILE_DATA');
			return false;
		}

		$data = new stdClass();

		$data->id = $file->id;
		$data->name = $file->name;

		return $data;
	}

	/**
	 * Renders the preview of the file when object's item is being viewed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onDisplay($object)
	{
		if (empty($this->value)) {
			return;
		}

		if (!$this->allowedPrivacy($object)) {
			return;
		}

		$value = $this->value;

		if (is_array($value) && isset($value['data'])) {
			$value = $value['data'];
		}

		$value = json_decode($value);
		$files = $this->prepareFiles($value, $object);

		$count = empty($files) ? 0 : count($files);

		if ($count < 1) {
			return ;
		}

		$result = array();

		$this->set('count', $count);
		$this->set('files', $files);

		return $this->display();
	}

	/**
	 * Prepares the data of the files
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function prepareFiles($value, $object)
	{
		if (!is_array($value)) {
			$value = array();
		}

		$files = array();

		foreach ($value as $file) {

			$table = ES::table('file');
			$state = $table->load($file->id);

			if ($state) {

				$table->downloadLink = ESR::fields(array('group' => $this->group, 'element' => $this->element, 'task' => 'download', 'id' => $this->field->id, 'uid' => $file->id, 'external' => true, 'clusterId' => $object->id, 'clusterType' => $object->getType()));
				$table->previewLink = ESR::fields(array('group' => $this->group, 'element' => $this->element, 'task' => 'preview', 'id' => $this->field->id, 'uid' => $file->id, 'external' => true, 'clusterId' => $object->id, 'clusterType' => $object->getType()));

				$files[] = $table;
			}
		}

		return $files;
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onFieldCheck($object)
	{
		if (!$this->isRequired()) {
			return true;
		}

		if (empty($this->value)) {
			$this->setError(JText::_('PLG_FIELDS_FILE_VALIDATION_REQUIRED_TO_UPLOAD'));
			return false;
		}

		$value = json_decode($value);
		$value = $this->prepareFiles($value, $object);

		$count = empty($value) ? 0 : count($value);

		if (empty($value) || empty($count)) {
			$this->setError(JText::_('PLG_FIELDS_FILE_VALIDATION_REQUIRED_TO_UPLOAD'));
			return false;
		}

		return true;
	}

	/**
	 * Checks if this field is filled in.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onProfileCompleteCheck($user)
	{
		if (!$this->config->get('user.completeprofile.strict') && !$this->isRequired()) {
			return true;
		}

		if (empty($this->value)) {
			return false;
		}

		$obj = ES::makeObject($this->value);

		if (empty($obj)) {
			return false;
		}

		return true;
	}
}
