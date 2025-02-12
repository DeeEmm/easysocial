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

class EasySocialViewMarketplacesEditHelper extends EasySocial
{
	/**
	 * Determines the listing that is currently being viewed
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getActiveListing()
	{
		static $listing = null;

		if (is_null($listing)) {

			$id = $this->input->get('id', 0, 'int');

			// Load the listing
			$listing = ES::marketplace($id);

			if (!$listing || !$listing->id || (!$listing->isPublished() && !$listing->isPending() && !$listing->isDraft())) {
				return ES::raiseError(404, JText::_('COM_ES_MARKETPLACES_INVALID_LISTING_ID'));
			}
		}

		return $listing;
	}

	/**
	 * Retrieve the steps of the listing
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getListingSteps()
	{
		static $steps = null;

		if (is_null($steps)) {
			$listing = $this->getActiveListing();

			$category = ES::table('MarketplaceCategory');
			$category->load($listing->category_id);

			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($category->getWorkflow()->id, SOCIAL_TYPE_MARKETPLACES, SOCIAL_EVENT_VIEW_EDIT);
		}

		return $steps;
	}

	/**
	 * Get current active step
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getActiveStep()
	{
		$activeStep = $this->input->get('activeStep', 0, 'int');
		return $activeStep;
	}
}
