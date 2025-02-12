<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

jimport('joomla.filesystem.file');

class SocialFieldViewUserFile extends SocialFieldView
{
	/**
	 * Allows viewer to download a file from the custom field
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function download()
	{
		$id = $this->input->get('uid', 0, 'int');
		$clusterId = $this->input->get('clusterId', 0, 'int');
		$clusterType = $this->input->get('clusterType', SOCIAL_TYPE_USER, 'cmd');

		if (!$id | !$clusterId) {
			$this->setError('PLG_FIELDS_FILE_ERROR_INVALID_FILE_ID');
			ES::info()->set(false, 'PLG_FIELDS_FILE_ERROR_INVALID_FILE_ID', SOCIAL_MSG_ERROR);
			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Try to load the file now
		$file = ES::table('File');
		$exists = $file->load($id);

		if (!$exists || !$this->params->get('allow_download')) {
			$this->setError('PLG_FIELDS_FILE_ERROR_DOWNLOAD_NOT_ALLOWED');
			ES::info()->set(false, 'PLG_FIELDS_FILE_ERROR_DOWNLOAD_NOT_ALLOWED', SOCIAL_MSG_ERROR);
			return $this->redirect(ESR::dashboard(array(), false));
		}

		if ($clusterType == SOCIAL_TYPE_USER) {
			$object = ES::user($clusterId);
		} else {
			$object = ES::cluster($clusterType, $clusterId);
		}

		if (!$object) {
			$this->setError('PLG_FIELDS_FILE_ERROR_UNKNOWN_ERROR_OCCURED');
			ES::info()->set(false, 'PLG_FIELDS_FILE_ERROR_UNKNOWN_ERROR_OCCURED', SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::dashboard(array(), false));
		}

		// There could be a possibility that the file stored is in legacy path
		// /media/com_easysocial/files/fields/[FIELD_ID]/[HASH]
		$legacyFile = $file->getStoragePath() . '/' . $file->getHash();
		$exists = JFile::exists($legacyFile);

		if ($exists) {
			return $file->preview();
		}

		// Since 2.0, we now store the files in a different path
		// We need to append the file path
		$appendPath = $object->getType() . '/' . $object->id;

		return $file->download($appendPath);
	}

	/**
	 * Allows viewer to preview a file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function preview()
	{
		$id = $this->input->get('uid', 0, 'int');
		$clusterId = $this->input->get('clusterId', 0, 'int');
		$clusterType = $this->input->get('clusterType', SOCIAL_TYPE_USER, 'cmd');

		if (!$id | !$clusterId) {
			$this->setError('PLG_FIELDS_FILE_ERROR_INVALID_FILE_ID', SOCIAL_MSG_ERROR);
			ES::info()->set(false, 'PLG_FIELDS_FILE_ERROR_INVALID_FILE_ID', SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::dashboard(array(), false));
		}

		// Try to load the file now
		$file = ES::table('File');
		$exists = $file->load($id);

		if (!$exists || !$file->hasPreview() || !$this->params->get('allow_preview')) {
			$this->setError('PLG_FIELDS_FILE_ERROR_PREVIEW_NOT_ALLOWED', SOCIAL_MSG_ERROR);
			ES::info()->set(false, 'PLG_FIELDS_FILE_ERROR_PREVIEW_NOT_ALLOWED', SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::dashboard(array(), false));
		}

		if ($clusterType == SOCIAL_TYPE_USER) {
			$object = ES::user($clusterId);
		} else {
			$object = ES::cluster($clusterType, $clusterId);
		}

		if (!$object) {
			$this->setError('PLG_FIELDS_FILE_ERROR_UNKNOWN_ERROR_OCCURED');
			ES::info()->set(false, 'PLG_FIELDS_FILE_ERROR_UNKNOWN_ERROR_OCCURED', SOCIAL_MSG_ERROR);

			return $this->redirect(ESR::dashboard(array(), false));
		}

		// There could be a possibility that the file stored is in legacy path
		// /media/com_easysocial/files/fields/[FIELD_ID]/[HASH]
		$legacyFile = $file->getStoragePath() . '/' . $file->getHash();
		$exists = JFile::exists($legacyFile);

		if ($exists) {
			return $file->preview();
		}

		// Since 2.0, we now store the files in a different path
		// We need to append the file path
		$appendPath = $object->getType() . '/' . $object->id;

		$file->preview($appendPath);
	}
}
