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

class ThemesHelperMarketplace extends ThemesHelperAbstract
{
	/**
	 * Renders the group type label
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function type(SocialMarketplace $listing, $tooltipPlacement = 'bottom', $listingView = false, $showIcon = true)
	{
		$theme = ES::themes();
		$theme->set('showIcon', $showIcon);
		$theme->set('placement', $tooltipPlacement);
		$theme->set('listing', $listing);

		$output = $theme->output('site/helpers/marketplace/type');

		return $output;
	}

	/**
	 * Renders the listing's admin button
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function adminActions(SocialMarketplace $listing, $returnUrl = '')
	{
		// Check for privileges
		if (!$this->my->isSiteAdmin() && !$listing->isOwner()) {
			return;
		}

		if (!$returnUrl) {
			$returnUrl = base64_encode(ES::getURI(true));
		}

		$listingAdminStart = false;
		$listingAdminEnd = false;
		$showAdminAction = false;

		if (($this->my->isSiteAdmin() || $listing->isOwner()) && !$listing->isDraft()) {
			// Check whether the action is exists.
			$listingAdminStart = ES::themes()->render('widgets', 'marketplace', 'marketplaces', 'marketplaceAdminStart', array($listing));
			$listingAdminEnd = ES::themes()->render('widgets', 'marketplace', 'marketplaces', 'marketplaceAdminEnd' , array($listing));

			if (!empty($listingAdminStart) || !empty($listingAdminEnd)) {
				$showAdminAction = true;
			}
		}

		$theme = ES::themes();
		$theme->set('listing', $listing);
		$theme->set('listingAdminStart', $listingAdminStart);
		$theme->set('listingAdminEnd', $listingAdminEnd);
		$theme->set('showAdminAction', $showAdminAction);
		$theme->set('returnUrl', $returnUrl);

		$output = $theme->output('site/helpers/marketplace/admin.actions');

		return $output;
	}

	/**
	 * Generates a report link for markeptlace lsiting
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function report(SocialMarketplace $listing, $wrapper = 'list')
	{
		static $output = array();

		$index = $listing->id . $wrapper;

		if (!isset($output[$index])) {

			// Ensure that the user is allowed to report objects on the site
			if ($listing->isOwner() || !$this->config->get('reports.enabled') || !$this->access->allowed('reports.submit')) {
				return;
			}

			$reports = ES::reports();

			// Reporting options
			$options = [
				'dialogTitle' => 'COM_ES_MARKETPLACES_REPORT_LISTING',
				'dialogContent' => 'COM_ES_MARKETPLACES_REPORT_LISTING_DESC',
				'title' => $listing->getTitle(),
				'permalink' => $listing->getPermalink(true, true),
				'type' => 'link',
				'showIcon' => false,
				'actorid' => $listing->user_id
			];

			$output[$index] = $reports->form(SOCIAL_TYPE_MARKETPLACE, $listing->id, $options);
		}

		return $output[$index];
	}
}
