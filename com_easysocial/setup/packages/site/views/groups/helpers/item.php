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
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewGroupsItemHelper extends EasySocial
{
	/**
	 * Retrieves the about permalink for the group
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAboutPermalink()
	{
		static $permalink = null;

		if (is_null($permalink)) {
			$group = $this->getActiveGroup();
			$defaultDisplay = $this->getDefaultDisplay();

			$permalink = ESR::groups(array('id' => $group->getAlias(), 'type' => 'info', 'layout' => 'item'));


			if ($defaultDisplay == 'info') {
				$permalink = $group->getPermalink();
			}
		}

		return $permalink;
	}

	/**
	 * Determines the default display page of the group
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getDefaultDisplay()
	{
		static $default = null;

		if (is_null($default)) {
			$default = $this->config->get('groups.item.display', 'timeline');
		}

		return $default;
	}

	/**
	 * Determines the group that is currently being viewed
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveGroup()
	{
		static $group = null;

		if (is_null($group)) {
			$id = $this->input->get('id', 0, 'int');
			$group = ES::group($id);

			// Check if the group is valid
			if (!$id || !$group->id || !$group->canAccess()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_GROUPS_GROUP_NOT_FOUND'));
			}
		}

		return $group;
	}
}
