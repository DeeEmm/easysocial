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

ES::import('site:/views/views');

class EasySocialViewBlocks extends EasySocialSiteView
{
	/**
	 * Dialog to confirm a report.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmBlock()
	{
		// Determines if this feature is allowed
		if (!$this->config->get('users.blocking.enabled')) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_BLOCKING_FEATURE_IS_NOT_ENABLED'));
		}

		// Get the target id
		$targetId = $this->input->get('target', 0, 'int');

		if (!$targetId) {
			return $this->ajax->reject(JText::_('COM_ES_INVALID_USER_ID_PROVIDED'));
		}

		// Prevent blocking yourself.
		if ($targetId == $this->my->id) {
			return $this->ajax->reject(JText::_('COM_ES_BLOCKED_YOURSELF'));
		}

		// Prevent multiple user blocks.
		$user = ES::user($targetId);

		if ($user->isBlockedBy($this->my->id)) {
			return $this->ajax->reject(JText::_('COM_ES_YOU_HAVE_BLOCKED_THIS_USER'));
		}

		// Get the target user
		$target = ES::user($targetId);
		$theme = ES::themes();

		$theme->set('target', $target);

		$output = $theme->output('site/blocks/dialogs/form');

		return $this->ajax->resolve($output);
	}

	/**
	 * Dialog to confirm a report.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmUnblock()
	{
		// Determines if this feature is allowed
		if (!$this->config->get('users.blocking.enabled')) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_BLOCKING_FEATURE_IS_NOT_ENABLED'));
		}

		// Get the target id
		$targetId = $this->input->get('target', 0, 'int');

		if (!$targetId) {
			return $this->ajax->reject(JText::_('COM_ES_INVALID_USER_ID_PROVIDED'));
		}

		// Get the target user
		$target = ES::user($targetId);
		$theme = ES::themes();

		$theme->set('target', $target);

		$output = $theme->output('site/blocks/dialogs/unblock');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after an unblock occurs
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function unblock($target = null)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$targetUser = ES::user($target);

		$theme = ES::themes();
		$theme->set('user', $targetUser);
		$output = $theme->output('site/blocks/dialogs/unblocked');

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after a block occurs
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function store($target = null)
	{
		if ($this->hasErrors()) {
			return $this->ajax->reject($this->getMessage());
		}

		$targetUser = ES::user($target);

		$theme = ES::themes();
		$theme->set('user', $targetUser);
		$output = $theme->output('site/blocks/dialogs/blocked');

		return $this->ajax->resolve($output);
	}
}
