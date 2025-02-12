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

class EasySocialViewExplorer extends EasySocialSiteView
{
	/**
	 * Responsible to return data for file explorer
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function hook($exception = false, $result = array())
	{
		if ($exception->type != SOCIAL_MSG_SUCCESS) {
			return $this->ajax->reject($exception);
		}

		// Get the hook that's used
		$hook = $this->input->get('hook', '', 'cmd');

		if ($hook == 'removeFolder') {
			$id = $this->input->get('id', 0, 'int');

			return $this->ajax->resolve($id, $result);
		}

		return $this->ajax->resolve($result);
	}

	/**
	 * Renders the confirmation to delete a collection
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDeleteFolder()
	{
		$id = $this->input->get('id', 0, 'int');

		$folder = ES::table('FileCollection');
		$folder->load($id);

		// We need to determine if the user is allowed to delete 
		if (!$folder->hasDeleteFolderAccess($folder)) {
			return $ajax->reject(JText::_('COM_EASYSOCIAL_GROUP_DELETE_FOLDER_PERMISSION_ERROR'));
		}

		$theme = ES::themes();
		$theme->set('folder', $folder);
		$contents = $theme->output('site/explorer/dialogs/delete.folder');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the delete file confirmation
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmDeleteFile()
	{
		$id = $this->input->get('id', 0, 'int');

		$file = ES::table('File');
		$file->load($id);

		$theme = ES::themes();
		$theme->set('file', $file);

		$contents = $theme->output('site/explorer/dialogs/delete.file');
		
		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the file browser
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function browser()
	{
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', '', 'cmd');
		$url = $this->input->get('url', '', 'default');

		// Get the controller
		$controllerName = $this->input->get('controllerName', '', 'string');

		// Load up the explorer library
		$explorer = ES::explorer($uid, $type);

		// We need to determine if the user is allowed to access
		$hasReadAccess = $explorer->hook('hasReadAccess');

		if (!$hasReadAccess) {
			return $this->ajax->reject();
		}

		// Allow uploading
		$allowUpload = $explorer->hook('allowUpload');
		$maxSize = $explorer->hook('getMaxSize');

		$options = array(
			'allowUpload' => $allowUpload,
			'uploadLimit' => $maxSize,
		);

		if (!empty($controllerName)) {
			$options['controllerName'] = $controllerName;
		}

		$html = $explorer->render($url, $options);

		return $this->ajax->resolve($html);
	}
}
