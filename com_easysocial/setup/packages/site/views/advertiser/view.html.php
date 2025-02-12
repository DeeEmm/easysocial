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

class EasySocialViewAdvertiser extends EasySocialSiteView
{
	public function display($tpl = null)
	{
		return $this->form($tpl);
	}

	/**
	 * Renders the form to edit an existing advertiser account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function form($tpl = null)
	{
		ES::requireLogin();

		// Ensure that the user truly has permissions to create an advertiser account
		if (!$this->my->canCreateAds()) {
			$redirect = ESR::dashboard([], false);

			return $this->redirect($redirect, 'You are not allowed to access this page', 'error');
		}


		$this->page->title('COM_ES_ADVERTISER_ACCOUNT_TITLE');

		$inProgress = false;
		$advertiser = $this->my->getAdvertiserAccount();

		if ($advertiser && $advertiser->isUnderModeration()) {
			$inProgress = true;
		}

		$this->set('advertiser', $advertiser);
		$this->set('inProgress', $inProgress);

		parent::display('site/advertiser/form/default');
	}
}
