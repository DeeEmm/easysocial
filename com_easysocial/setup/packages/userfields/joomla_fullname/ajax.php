<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');
ES::import('fields:/user/joomla_fullname/helper');

class SocialFieldsUserJoomla_Fullname extends SocialFieldItem
{
	/**
	 * Determine if the name is allowed.
	 *
	 * @since   3.2.24
	 * @access  public
	 */
	public function isAllowed()
	{
		$fieldId = $this->input->get('id', 0, 'int');
		$userId = $this->input->get('userId', 0, 'int');
		$type = $this->input->get('type', '', 'string');

		$current = '';

		if ($userId && $type) {
			$current = SocialFieldsUserJoomlaFullnameHelper::getKeyNameValue($fieldId, $userId, $type);
		}

		$name = $this->input->get('name', '', 'default');

		// By pass for backend
		if (!ES::isFromAdmin() && !SocialFieldsUserJoomlaFullnameHelper::allowed($name, $this->params, $current)) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_JOOMLA_FULLNAME_NOT_ALLOWED'));
		}

		return $this->ajax->resolve();
	}
}
