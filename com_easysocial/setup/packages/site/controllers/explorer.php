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

class EasySocialControllerExplorer extends EasySocialController
{
	/**
	 * Service Hook for explorer
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function hook()
	{
		// Get the event object
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');

		// Load up the explorer library
		$explorer = ES::explorer($uid, $type);

		// Determine if the viewer can really view items
		if (!$explorer->hook('canViewItem')) {
			$exception = ES::response('You are not allowed to view this section', SOCIAL_MSG_ERROR);
			return $this->view->call(__FUNCTION__, $exception);
		}

		// Get the hook
		$hook = $this->input->get('hook', '', 'cmd');

		// Get the result
		$result = $explorer->hook($hook);

		$exception = ES::response('Folder retrieval successful', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $exception, $result);
	}

	/**
	 * Check the file before upload
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function checkFileSize()
	{
		ES::requireLogin();
		ES::checkToken();

		$filesize = $this->input->get('filesize', array(), 'array');
		$totalsize = $this->input->get('totalsize', 0, 'int');
		$uid = $this->input->get('uid', 0, 'int');
		$utype = $this->input->get('utype', SOCIAL_TYPE_USER, 'default');

		// Check for upload limit
		$explorer = ES::explorer($uid, $utype);
		$uploadLimit = $explorer->hook('getMaxSize');
		$maxUploadSize = ES::math()->convertBytes($uploadLimit);

		// Check for individual file size
		foreach ($filesize as $size) {
			if ($size > $maxUploadSize) {
				return $this->ajax->reject(JText::sprintf('COM_ES_UPLOAD_EXCEEDED_UPLOAD_LIMIT_SIZE', $uploadLimit));
			}
		}

		// Check for storage limit of the total size
		$storage = ES::storage();
		$isLimit = $storage->isLimit($this->my->id, $totalsize);

		if ($isLimit) {
			$message = JText::_('COM_ES_STORAGE_INSUFFICIENT_STORAGE_FILES');
			return $this->ajax->reject($message);
		}

		return $this->ajax->resolve();
	}
}
