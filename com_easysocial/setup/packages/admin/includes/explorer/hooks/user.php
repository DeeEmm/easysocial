<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

require_once(dirname(__FILE__) . '/abstract.php');

class SocialExplorerHookUser extends SocialExplorerHooks
{
	private $user = null;

	public function __construct($uid, $type)
	{
		parent::__construct($uid, $type);
		$this->user = ES::user($uid);
	}

	/**
	 * Removes a folder from the group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function removeFolder($fileId = null)
	{
		// Check if the user has access to delete files
		if (!$this->user->isViewer()) {
			return ES::response(JText::_('COM_EASYSOCIAL_EXPLORER_NO_ACCESS_TO_DELETE_FOLDER'));
		}

		$id = $this->input->get('id', 0, 'int');

		$fileId = is_null($fileId) ? $id : $fileId;

		if (!$fileId) {
			return ES::response(JText::_('COM_EASYSOCIAL_EXPLORER_INVALID_FOLDER_ID_PROVIDED'));
		}

		$collection = ES::table('FileCollection');
		$collection->load($fileId);

		if (($collection->user_id != $this->my->id) && !$this->my->isSiteAdmin()) {
			return ES::response(JText::_('Sorry, but you are not allowed to delete this folder.'));
		}

		if (!$collection->delete()) {
			return ES::response($collection->getError());
		}

		return ES::response($fileId, JText::_('Folder removed successfully.'), SOCIAL_MSG_SUCCESS);
	}

	/**
	 * Removes a file from a group.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function removeFile()
	{
		// Check if the user has access to delete files
		if (!$this->user->isViewer()) {
			return ES::response(JText::_('COM_EASYSOCIAL_EXPLORER_NO_ACCESS_TO_DELETE'));
		}

		// Get the file id
		$ids = $this->input->get('id', 0, 'int');
		$ids = ES::makeArray($ids);

		if (!$ids) {
			return array();
		}

		foreach ($ids as $id) {
			$file = ES::table('File');
			$file->load($id);

			if (!$id || !$file->id) {
				return ES::response(JText::_('COM_EASYSOCIAL_EXPLORER_INVALID_FILE_ID_PROVIDED'));
			}

			$state = $file->delete();

			if (!$state) {
				return ES::response(JText::_($file->getError()));
			}
		}

		return $ids;
	}

	/**
	 * Override parent's behavior to insert a file
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function addFile($title = null)
	{
		// Run the parent's logics first
		$result = parent::addFile($title);

		if ($result instanceof SocialResponse) {
			return $result;
		}

		$createStream = $this->input->get('createStream', false, 'bool');

		// Create a new stream for the user upload
		if ($createStream) {
			$stream = ES::stream();
			$tpl = $stream->getTemplate();

			// Set the actor
			$tpl->setActor($this->my->id, SOCIAL_TYPE_USER);

			// Set the context
			$tpl->setContext($result->id, SOCIAL_TYPE_FILES);

			// Set the verb
			$tpl->setVerb('uploaded');

			// Set the access for this stream item
			$tpl->setAccess('core.view');

			// Insert the stream now
			$streamItem = $stream->add($tpl);
		}

		// Add points for the user when they upload a file.
		ES::points()->assign('files.upload', 'com_easysocial', $this->my->id);

		return $result;
	}

	/**
	 * Returns the maximum file size allowed
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getMaxSize()
	{
		$access = $this->my->getAccess();

		$max = $access->get('files.maxsize') . 'M';

		return $max;
	}

	/**
	 * Determines if the current person has access to upload files
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function hasWriteAccess()
	{
		// @TODO: Check for access
		return true;
	}

	/**
	 * The user should always have access to delete their own files.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function hasDeleteAccess(SocialTableFile $file)
	{
		return true;
	}

	/**
	 * Determines if the user has access to delete the file folder on the event
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasDeleteFolderAccess(SocialTableFileCollection $collection)
	{
		if ($this->my->id == $collection->owner_id && $this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the viewer can view the explorer
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function canViewItem()
	{
		return $this->my->id == $this->uid;
	}
}
