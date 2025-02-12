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

ES::import('site:/controllers/controller');

class EasySocialControllerAds extends EasySocialController
{
	/**
	 * Update click count for ads
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function click()
	{
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Ad');
		$table->load($id);

		// Ensure that the ads are valid
		if (!$id || !$table->id) {
			die();
		}

		$table->click();
	}

	/**
	 * Update view count for ads
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function view()
	{
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Ad');
		$table->load($id);

		// Ensure that the ads are valid
		if (!$id || !$table->id) {
			die();
		}

		$table->view();
	}

	/**
	 * Saves an advertisement
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function save()
	{
		ES::checkToken();

		// Ensure that the user truly has access to create ads
		if (!ES::ad()->canCreate()) {
			die();
		}

		// Get the ad id from the request
		$id = $this->input->get('id', 0, 'int');
		$ad = ES::ad($id);

		$advertiser = $this->my->getAdvertiserAccount();

		if (!$advertiser) {
			return $this->redirectToView('ads', 'form');
		}

		$post = $this->input->post->getArray();

		$formRedirect = ESR::ads(['layout' => 'form']);

		if (empty($post['title'])) {
			return $this->view->redirect($formRedirect, 'COM_ES_ADS_EMPTY_TITLE', ES_ERROR);
		}

		$cover = $this->input->files->get('cover');

		if (!$ad->id && empty($cover['tmp_name'])) {
			return $this->view->redirect($formRedirect, 'COM_ES_ADS_EMPTY_COVER', ES_ERROR);
		}

		$start = ES::normalize($post, 'start_date', null);
		$end = ES::normalize($post, 'end_date', null);

		if (!$start) {
			return $this->view->redirect($formRedirect, 'COM_ES_ADS_PLEASE_ENTER_START_DATE', ES_ERROR);
		}

		if (!$end) {
			return $this->view->redirect($formRedirect, 'COM_ES_ADS_PLEASE_ENTER_END_DATE', ES_ERROR);
		}

		$operation = 'create';

		// Existing ads
		if ($id && $ad->id) {
			$operation = 'update';

			// Ensure that the current advertiser truly owns the ad
			if ($advertiser->id != $ad->getAdvertiser()->id) {
				return $this->view->redirect($formRedirect, 'You do not own this advertisement', ES_ERROR);
			}
		}

		$state = $ad->$operation($post, $advertiser, $cover);

		if (!$state) {
			return $this->view->redirect($formRedirect, $ad->getError(), ES_ERROR);
		}

		return $this->view->redirect(ESR::ads(), 'COM_ES_ADS_UPDATED_SUCCESS', 'success');
	}


	/**
	 * Allows caller to trigger the delete method
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$ad = ES::ad($id);

		if (!$ad->id || !$id) {
			$this->view->setMessage('COM_ES_ADS_INVALID_AD_ID_PROVIDED', ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

		$ad->delete();

		$this->view->setMessage('COM_ES_ADS_DELETED', SOCIAL_MSG_SUCCESS);
		return $this->view->call(__FUNCTION__);
	}
}
