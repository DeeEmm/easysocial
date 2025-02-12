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

class EasySocialViewGroupsEditHelper extends EasySocial
{
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
			if (!$id || !$group->id || (!$group->isPublished() && !$group->isPending() && !$group->isDraft()) || !$group->canAccess()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_GROUPS_GROUP_NOT_FOUND'));
			}
		}

		return $group;
	}

	/**
	 * Retrieve the group steps
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getGroupSteps()
	{
		static $steps = null;

		if (is_null($steps)) {

			$group = $this->getActiveGroup();

			// Load up the category
			$category = ES::table('GroupCategory');
			$category->load($group->category_id);

			// Get the steps model
			$model = ES::model('Steps');
			$steps = $model->getSteps($category->getWorkflow()->id, SOCIAL_TYPE_CLUSTERS, SOCIAL_PROFILES_VIEW_EDIT);
		}

		return $steps;
	}

	/**
	 * Get current active step
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getActiveStep()
	{
		$activeStep = $this->input->get('activeStep', 0, 'int');
		return $activeStep;
	}
}
