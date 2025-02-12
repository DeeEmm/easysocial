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

ES::import('admin:/includes/fields/dependencies');
ES::import('fields:/group/permalink/helper');

class SocialFieldsGroupPermalink extends SocialFieldItem
{
	/**
	 * Given a title, generate an appropriate permalink for the title
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function generate()
	{
		$ajax = ES::ajax();

		$title = $this->input->get('title', '', 'default');
		$permalink = ES::generatePermalink($title);
		$tmpPermalink = $permalink;

		$i = 0;

		while (SocialFieldsGroupPermalinkHelper::exists($tmpPermalink)) {
			$i++;

			$tmpPermalink = $permalink . '-' . $i;
		}

		$permalink = $tmpPermalink;

		return $ajax->resolve($permalink);
	}

	/**
	 * Validates the username.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isValid()
	{
		// Render the ajax lib.
		$ajax = ES::ajax();

		// Get the group id
		$groupId = $this->input->get('groupid' , 0, 'int');

		// Set the current username
		$current = '';

		if (!empty($groupId)) {
			$group = ES::group($groupId);
			$current = $group->alias;
		}

		// Get the provided permalink
		$permalink = $this->input->get('permalink' , '', 'default');

		// Check if the field is required
		if (!$this->field->isRequired() && empty($permalink)) {
			return true;
		}

		// Check if the permalink provided is allowed to be used.
		$allowed = SocialFieldsGroupPermalinkHelper::allowed($permalink);

		if (!$allowed) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_PERMALINK_NOT_ALLOWED'));
		}

		// Check if the permalink provided is valid
		if (!SocialFieldsGroupPermalinkHelper::valid($permalink , $this->params)) {
			return $ajax->reject(JText::_('PLG_FIELDS_GROUP_PERMALINK_INVALID_PERMALINK'));
		}

		// Test if permalink exists
		if (SocialFieldsGroupPermalinkHelper::exists($permalink) && $permalink != $current) {
			return $ajax->reject(JText::_('PLG_FIELDS_GROUP_PERMALINK_NOT_AVAILABLE'));
		}

		$text = JText::_('PLG_FIELDS_GROUP_PERMALINK_AVAILABLE');

		return $ajax->resolve($text);
	}
}
