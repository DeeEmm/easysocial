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

class EasySocialControllerAdvertiser extends EasySocialController
{
	/**
	 * Saves the advertiser account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function save()
	{
		ES::requireLogin();

		// Check if the user has privilege to save or request for an advertiser account
		if (!$this->my->canCreateAds()) {
			die();
		}

		$advertiser = $this->my->getAdvertiserAccount();
		$company = $this->input->get('company', '', 'default');
		$logo = $this->input->files->get('logo');

		$method = 'create';

		// User does not have an advertising account yet, request one
		if (!$advertiser) {
			$advertiser = ES::advertiser();
		}

		// User requesting to update their advertising account
		if ($advertiser) {
			$method = 'update';
		}

		$advertiser->$method($company, $logo, $this->my->id);

		// Use redirect to ensure that the url item id is correct. #4731
		$return = ESR::advertiser(['layout' => 'form']);
		return $this->view->redirect($return, 'COM_ES_ADVERTISER_ACCOUNT_REQUESTED', 'success');
	}
}
