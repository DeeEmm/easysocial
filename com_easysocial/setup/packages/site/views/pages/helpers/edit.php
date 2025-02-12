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

class EasySocialViewPagesEditHelper extends EasySocial
{
	/**
	 * Determines the page that is currently being viewed
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActivePage()
	{
		static $page = null;

		if (is_null($page)) {
			$id = $this->input->get('id', 0, 'int');
			$page = ES::page($id);

			// Check if the page is valid
			if (!$id || !$page->id || (!$page->isPublished() && !$page->isPending() && !$page->isDraft()) || !$page->canAccess()) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_PAGES_PAGE_NOT_FOUND'));
			}
		}

		return $page;
	}

	/**
	 * Retrieve the page steps
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPageSteps()
	{
		static $steps = null;

		if (is_null($steps)) {

			$page = $this->getActivePage();

			// Load up the category
			$category = ES::table('PageCategory');
			$category->load($page->category_id);

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
