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

class EasySocialViewAds extends EasySocialSiteView
{
	/**
	 * Renders the ads listing
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::requireLogin();

		$advertiser = $this->my->getAdvertiserAccount();

		if (!$advertiser) {
			$this->info->set(JText::_('COM_ES_CREATE_ADVERTISER_ACCOUNT_FIRST'));

			$url = ESR::advertiser(['layout' => 'form'], false);

			return $this->redirect($url);
		}
		$model = ES::model('Ads');
		$ads = $model->getAds($advertiser->id);

		$this->set('ads', $ads);

		parent::display('site/ads/default/default');
	}

	/**
	 * Renders the create new ad page
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function form()
	{
		ES::requireLogin();

		if (!ES::ad()->canCreate()) {
			die();
		}

		$id = $this->input->get('id', 0, 'int');

		$ad = ES::ad($id);

		// Ensure that the user can truly edit the ad
		if (!$ad->canEdit()) {
			die();
		}

		$reject = false;

		if ($ad->isDraft()) {
			$reject = $ad->getRejectData();
		}

		if (!$ad->id) {
			$ad->start_date = JFactory::getDate()->format('Y-m-d H:i');
			$ad->end_date = JFactory::getDate('+2 weeks')->format('Y-m-d H:i');
		}

		$this->set('reject', $reject);
		$this->set('ad', $ad);

		parent::display('site/ads/form/default');
	}

	/**
	 * Post process when a ad is deleted
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function delete()
	{
		ES::info()->set($this->getMessage());

		$this->redirect(ESR::ads(array(), false));
	}
}
