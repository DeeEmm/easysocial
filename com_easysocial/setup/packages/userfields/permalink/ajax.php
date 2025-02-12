<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Include dependencies
ES::import('admin:/includes/fields/dependencies');
ES::import('fields:/user/permalink/helper');

class SocialFieldsUserPermalink extends SocialFieldItem
{
	/**
	 * Ensures that the permalink is valid
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function isValid()
	{
		// Get the user's id
		$id = $this->input->get('userid', 0, 'int');

		// Default
		$currentPermalink = '';
		$currentUserId = '';

		// If user's id is provided, get the permalink for this user
		if ($id) {
			$user = ES::user($id);
			$currentPermalink = $user->permalink;
			$currentUserId = $user->id;
		}

		// Get the provided permalink
		$permalink = $this->input->get('permalink', '', 'default');

		// Check if the field is required
		if (!$this->field->isRequired() && !$permalink) {
			return true;
		}

		// Check if the permalink provided is allowed to be used.
		$allowed = SocialFieldsUserPermalinkHelper::allowed($permalink);
		if (!$allowed) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_PERMALINK_NOT_ALLOWED'));
		}

		// Check if the permalink provided is valid
		$valid = SocialFieldsUserPermalinkHelper::valid($permalink, $this->params);

		if (!$valid) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_PERMALINK_INVALID_PERMALINK'));
		}

		// Test if permalink exists
		$exists = SocialFieldsUserPermalinkHelper::exists($permalink, $currentPermalink, $currentUserId);

		if ($exists) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_PERMALINK_NOT_AVAILABLE'));
		}

		$message = JText::_('PLG_FIELDS_PERMALINK_AVAILABLE');

		return $this->ajax->resolve($message);
	}
}
