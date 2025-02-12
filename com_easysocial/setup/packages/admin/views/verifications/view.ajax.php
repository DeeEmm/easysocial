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

class EasySocialViewVerifications extends EasySocialAdminView
{
	/**
	 * View verification message
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function viewMessage()
	{
		$id = $this->input->get('id');

		$table = ES::table('verification');
		$table->load($id);

		if (!$table->id) {
			return $this->ajax->reject();
		}

		$message = $table->message;

		if (!$message) {
			$message = JText::_('COM_ES_USER_VERIFIED_MESSAGE_DEFAULT');
		}

		$theme = ES::themes();
		$theme->set('message', nl2br($message));
		$contents = $theme->output('admin/verifications/dialogs/verification.message');

		return $this->ajax->resolve($contents);
	}
}
