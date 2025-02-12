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

// Include the fields library
ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserFile extends SocialFieldItem
{
	public function getUploadHtml()
	{
		$ajax = ES::ajax();
		$key = $this->input->get('key', 0, 'int');

		$theme = ES::themes();
		$theme->set('inputName', $this->inputName);
		$theme->set('key', $key);

		$html = $theme->output('fields/user/file/upload');

		$ajax->resolve($html);
		return true;
	}

	public function upload()
	{
		$ajax = ES::ajax();

		$tmp = $this->input->files->get($this->inputName, [], 'raw');
		$key = $this->input->get('key', 0, 'int');

		if (empty($tmp)) {
			$ajax->reject($this->getErrorHtml(JText::_('PLG_FIELDS_FILE_VALIDATION_INVALID_FILE')));
			return false;
		}

		// Reconstruct the file array
		$file = array();

		foreach($tmp[$key] as $k => $v) {
			$file[$k] = $v;
		}

		// Check for file validity
		if (empty($file['tmp_name'])) {
			$ajax->reject($this->getErrorHtml(JText::_('PLG_FIELDS_FILE_VALIDATION_INVALID_FILE')));
			return false;
		}

		$maths = ES::math();
		$limit = $this->params->get('size_limit', 2);
		$limit = $maths->convertUnits($limit, 'MB', 'B');
		$size = filesize($file['tmp_name']);

		if ($size > $limit) {
			$ajax->reject($this->getErrorHtml(JText::_('PLG_FIELDS_FILE_VALIDATION_FILE_SIZE_EXCEEDED')));
			return false;
		}

		$allowed = $this->params->get('allowed');
		$allowed = ES::makeArray($allowed, ',');
		$info = pathinfo($file['name']);

		if (!isset($info['extension']) || (!empty($allowed) && !in_array($info['extension'] , $allowed))) {
			$ajax->reject($this->getErrorHtml(JText::_('PLG_FIELDS_FILE_VALIDATION_FILE_EXTENSION_NOT_ALLOWED')));
			return false;
		}

		$base = SOCIAL_TMP;

		if (!JFolder::exists($base) && !JFolder::create($base)) {
			$ajax->reject($this->getErrorHtml(JText::_('PLG_FIELDS_FILE_ERROR_UNABLE_TO_CREATE_TEMPORARY_LOCATION')));
			return false;
		}

		$session = JFactory::getSession()->getId();

		$hash = md5($session . $this->inputName . $file['name']);

		// Import necessary library
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$state = JFile::copy($file['tmp_name'], $base . '/' . $hash);

		if(!$state) {
			$ajax->reject($this->getErrorHtml(JText::_('PLG_FIELDS_FILE_ERROR_UNABLE_TO_MOVE_FILE')));
			return false;
		}

		// Store this data into temporary table
		$tmp = ES::table('tmp');
		$tmp->uid = $this->field->id;
		$tmp->type = SOCIAL_APPS_TYPE_FIELDS;
		$tmp->key = $this->inputName;
		$tmp->value = array(
			'name' => $file['name'],
			'mime' => $file['type'],
			'size' => $file['size'],
			'hash' => $hash,
			'path' => $base
		);

		$state = $tmp->store();

		if(!$state)
		{
			$ajax->reject($this->getErrorHtml(JText::_('PLG_FIELDS_FILE_ERROR_UNABLE_TO_STORE_FILE_DATA')));
			return false;
		}

		$data = new stdClass();
		$data->name = $file['name'];
		$data->id = $tmp->id;

		$theme = ES::themes();
		$theme->set('file', $data);
		$theme->set('tmp', true);
		$theme->set('key', $key);
		$theme->set('inputName', $this->inputName);
		$theme->set('field', $this->field);
		$theme->set('params', $this->params);

		$html = $theme->output('fields/user/file/control');

		$ajax->resolve($html);
		return true;
	}

	public function delete()
	{
		$ajax = ES::ajax();
		$config = ES::config();
		$session = JFactory::getSession()->getId();

		$id = $this->input->get('fileid', 0, 'int');
		$tmp = $this->input->get('tmp', 0, 'int');
		$key = $this->input->get('key', 0, 'int');

		// We only delete now if it is newly uploaded file.
		// Existing uploaded file should be handled in onBeforeSave triggers
		if ($tmp) {
			// Get the tmp table
			$table = ES::table('tmp');
			$table->load($id);

			$value = ES::json()->decode($table->value);

			$fullpath = $value->path . '/' . $value->hash;

			JFile::delete($fullpath);

			$table->delete();
		}

		$theme = ES::themes();
		$theme->set('inputName', $this->inputName);
		$theme->set('key', $key);

		$html = $theme->output('fields/user/file/upload');

		$ajax->resolve($html);
		return true;
	}

	private function getErrorHtml($msg)
	{
		$theme = ES::themes();
		$theme->set('error', $msg);

		$html = $theme->output('fields/user/file/error');

		return $html;
	}
}
