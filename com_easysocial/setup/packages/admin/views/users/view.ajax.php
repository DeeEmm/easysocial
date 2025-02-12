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

ES::import('admin:/views/views');

class EasySocialViewUsers extends EasySocialAdminView
{
	/**
	 * Retrieves the total number of pending users on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalPending($total)
	{
		return $this->ajax->resolve($total);
	}

	/**
	 * Confirmation before deleting a user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$ids = $this->input->get('id', '', 'default');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$contents = $theme->output('admin/users/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Confirmation to approve a user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmApprove()
	{
		$ids = $this->input->get('id', '', 'default');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$contents = $theme->output('admin/users/dialogs/approve');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays confirmation dialog to reject users
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmReject()
	{
		$ids = $this->input->get('id', '', 'default');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$contents = $theme->output('admin/users/dialogs/reject');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Confirmation before purging download requests
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function confirmPurgeDownloads()
	{
		$theme 	= ES::themes();
		$contents = $theme->output('admin/users/dialogs/purge.downloads');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Form for admin to enter a custom message for points assignments
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function assignPoints()
	{
		$uids = $this->input->get('uid', '', 'default');
		$uids = ES::makeArray($uids);

		$model = ES::model('Points');
		$rules = $model->getRules();

		$theme = ES::themes();
		$theme->set('rules', $rules);
		$theme->set('uids', $uids);

		$output = $theme->output('admin/users/dialogs/assign.points');

		return $this->ajax->resolve($output);
	}

	/**
	 * Assign custom badge with message
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function assignBadgeMessage()
	{
		$theme = ES::themes();

		// Get the badge to insert
		$id = $this->input->get('id', 0, 'int');
		$badge = ES::table('Badge');
		$badge->load($id);

		$uids = $this->input->get('uid', '', 'default');
		$uids = ES::makeArray($uids);

		$theme->set('uids', $uids);
		$theme->set('badge', $badge);


		$output = $theme->output('admin/users/dialogs/assign.badge');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays the new user form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function newUserForm()
	{
		$theme = ES::themes();

		$output	= $theme->output('admin/users/dialogs/new.user');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays the switch profile form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function switchProfileForm()
	{
		// Get the id's of the user that we are trying to modify
		$ids = $this->input->get('ids', array(), 'array');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$output = $theme->output('admin/users/dialogs/switch.profile');

		return $this->ajax->resolve($output);
	}

	/**
	 * Browses for badge to be assigned to user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function assignBadge()
	{
		$ids = $this->input->get('ids', '', 'default');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$output = $theme->output('admin/users/dialogs/browse.badge');

		return $this->ajax->resolve($output);
	}

	/**
	 * Allows admin to browse groups and insert user into the group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function browse()
	{
		$callback = $this->input->get( 'jscallback', '', 'word');

		$title = $this->input->get( 'dialogTitle' , JText::_( 'COM_EASYSOCIAL_USERS_ASSIGN_USER_GROUP_DIALOG_TITLE' ), 'string');

		$theme = ES::themes();
		$theme->set('dialogTitle' , $title);
		$theme->set('callback' , $callback);

		$output = $theme->output('admin/users/dialogs/browse');

		return $this->ajax->resolve($output);
	}

	/**
	 * Assigns user into a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function assign()
	{
		$ids = $this->input->get('ids', array(), 'array');
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$output = $theme->output('admin/users/dialogs/assign');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders confirmation dialog to remove a user's badge
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmRemoveBadge()
	{
		$id = $this->input->get('id', 0, 'int');
		$userid = $this->input->get('userid', 0, 'int');

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('userid', $userid);

		$output	= $theme->output('admin/users/dialogs/remove.badge');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders an error dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function showFormError()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/users/dialogs/save.error');

		return $this->ajax->resolve($contents);
	}

	// /**
	//  * View verification message
	//  *
	//  * @since	2.1
	//  * @access	public
	//  */
	// public function viewVerificationMessage()
	// {
	// 	$id = $this->input->get('id');

	// 	$table = ES::table('verification');
	// 	$table->load($id);

	// 	if (!$table->id) {
	// 		return $this->ajax->reject();
	// 	}

	// 	$message = $table->message;

	// 	if (!$message) {
	// 		$message = JText::_('COM_ES_USER_VERIFIED_MESSAGE_DEFAULT');
	// 	}

	// 	$theme = ES::themes();
	// 	$theme->set('message', nl2br($message));
	// 	$contents = $theme->output('admin/users/dialogs/verification.message');

	// 	return $this->ajax->resolve($contents);
	// }
}
