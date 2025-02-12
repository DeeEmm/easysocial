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

class EasySocialViewProfiles extends EasySocialAdminView
{
	/**
	 * Processes the request to return a DefaultAvatar object in JSON format.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function uploadDefaultAvatars($avatar)
	{
		$avatars = array($avatar);

		$theme = ES::themes();
		$theme->set('defaultAvatars', $avatars);
		$output	= $theme->output('admin/profiles/avatar.item');

		return $this->ajax->resolve($output);
	}

	/**
	 * Confirmation to delete a profile avatar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmRemoveProfileAvatar()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/profiles/dialogs/delete.profile.avatar');

		return $this->ajax->resolve($output);
	}

	/**
	 * Confirmation to restore the profile badge image
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function confirmRestoreProfileBadgeImage()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/profiles/dialogs/restore.profile.badge.image');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays a dialog confirmation before deleting a default avatar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDeleteAvatar()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/profiles/dialogs/delete.avatar');
		
		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to browse for a profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function browse()
	{
		// Determine if there's a jscallback
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$output	= $theme->output('admin/profiles/dialogs/browse');

		return $this->ajax->resolve($output);
	}

	/**
	 * Retrieves the cluster template
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getClusterTemplate()
	{
		$ids = $this->input->get('clusters', array(), 'array');
		$clusterType = $this->input->get('clusterType');

		if (!$ids) {
			return $this->ajax->reject();
		}

		$clusters = array();
		
		foreach ($ids as $id) {
			$cluster = ES::cluster($id);
			$clusters[] = $cluster;
		}

		$theme = ES::themes();
		$theme->set('clusters', $clusters);

		$html = $theme->output('admin/profiles/form/' . $clusterType . '/item');

		return $this->ajax->resolve($html);
	}

	/**
	 * Renders confirmation dialog to delete a profile type
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/profiles/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	public function getFieldValues( $values )
	{
		return $this->ajax->resolve($values);
	}

	public function deleteField($state)
	{
		return $this->ajax->resolve($state);
	}

	public function deletePage($state)
	{
		return $this->ajax->resolve($state);
	}

	public function saveFields($data)
	{
		if ($data === false) {
			return $this->ajax->reject($this->getError());
		}
		return $this->ajax->resolve($data);
	}

	/**
	 * Renders a dialog when the ACL hits an error
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getAclErrorDialog()
	{
		$key = $this->input->get('key', '', 'word');

		$message = 'COM_EASYSOCIAL_MAXUPLOADSIZE_ERROR_' . strtoupper($key);
		$message = JText::_($message);

		$theme = ES::themes();
		$theme->set('message', $message);
		$contents = $theme->output('admin/profiles/dialogs/acl.error');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after deleting profile avatar
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function deleteProfileAvatar()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after restoring profile badge image
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function restoreProfileBadgeImage()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Creates a blank profile
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function createBlankProfile($data)
	{
		return $this->ajax->resolve($data);
	}
}