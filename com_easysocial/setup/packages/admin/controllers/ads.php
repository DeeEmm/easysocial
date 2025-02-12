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

class EasySocialControllerAds extends EasySocialController
{
	public $ad_url = 'index.php?option=com_easysocial&view=ads&layout=form';
	public $advertiser_url = 'index.php?option=com_easysocial&view=ads&layout=advertiserForm';

	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');

		$this->registerTask('saveAdvertiser', 'saveAdvertiser');
		$this->registerTask('applyAdvertiser', 'saveAdvertiser');
		$this->registerTask('publishAdvertiser', 'togglePublishAdvertiser');
		$this->registerTask('unpublishAdvertiser', 'togglePublishAdvertiser');
		$this->registerTask('deleteAdvertiser', 'deleteAdvertiser');
	}

	/**
	 * Approves an advertisement
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function approve()
	{
		$ids = $this->input->get('cid', [], 'int');

		foreach ($ids as $id) {
			$ad = ES::ad($id);

			if (!$ad->isUnderModeration()) {
				$this->view->setMessage('COM_ES_UNABLE_TO_APPROVE_FOR_NON_MODERATED_ADS', 'error');
				return $this->redirectToView('ads');
			}

			$ad->approve();

			$this->actionlog->log('COM_ES_ACTION_LOG_ADS_APPROVED', 'advertisement', ['link' => $this->ad_url . '&id=' . $ad->id, 'name' => $ad->title]);
		}

		$this->view->setMessage('COM_ES_ADS_APPROVED_SUCCESS');
		return $this->redirectToView('ads');
	}

	/**
	 * Rejects an advertisement
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function reject()
	{
		$ids = $this->input->get('cid', [], 'int');
		$message = $this->input->get('message', '', 'default');
		$deleteAd = $this->input->get('deleteAd', false, 'bool');

		foreach ($ids as $id) {
			$ad = ES::ad((int) $id);

			if (!$ad->isUnderModeration()) {
				$this->view->setMessage('COM_ES_UNABLE_TO_REJECT_FOR_NON_MODERATED_ADS', 'error');
				return $this->redirectToView('ads');
			}

			$ad->reject($message, $deleteAd);

			$this->actionlog->log('COM_ES_ACTION_LOG_ADS_REJECTED', 'advertisement', ['link' => $this->ad_url . '&id=' . $ad->id, 'name' => $ad->title]);
		}

		$this->view->setMessage('COM_ES_ADS_REJECTED_SUCCESS');
		return $this->redirectToView('ads');
	}

	/**
	 * Rejects an advertiser
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function rejectAdvertiser()
	{
		$ids = $this->input->get('cid', [], 'int');

		foreach ($ids as $id) {
			$advertiser = ES::advertiser((int) $id);

			$advertiser->reject();

			$this->actionlog->log('COM_ES_ACTION_LOG_ADVERTISER_REJECTED', 'advertisement', ['link' => $this->advertiser_url . '&id=' . $advertiser->id, 'name' => $advertiser->getCompanyName()]);
		}

		$this->view->setMessage('COM_ES_ADVERTISERS_REJECTED');
		return $this->redirectToView('ads', 'pendingAdvertisers');
	}

	/**
	 * Approves an advertiser
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function approveAdvertiser()
	{
		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_ADS_INVALID_AD_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$advertiser = ES::advertiser($id);

			if (!$advertiser->isUnderModeration()) {
				$this->view->setMessage('Selected advertisers are currently not under moderation', ES_ERROR);
				return $this->redirectToView('ads', 'advertisers');
			}

			$advertiser->approve();

			$this->actionlog->log('COM_ES_ACTION_LOG_ADVERTISER_APPROVED', 'advertisement', ['link' => $this->advertiser_url . '&id=' . $advertiser->id, 'name' => $advertiser->getCompanyName()]);
		}

		$this->view->setMessage('COM_ES_ADVERTISERS_APPROVED');
		return $this->redirectToView('ads', 'advertisers');
	}


	/**
	 * Removes ads from the site
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_ADS_INVALID_AD_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$ad = ES::ad((int) $id);
			$ad->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_ADS_REMOVED', 'advertisement', ['name' => $ad->title]);
		}

		$this->view->setMessage('COM_ES_ADS_DELETED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Removes advertiser from the site
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function deleteAdvertiser()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_ADS_INVALID_AD_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$ad = ES::table('Advertiser');
			$ad->load((int) $id);
			$ad->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_ADVERTISER_REMOVED', 'advertisement', ['name' => $ad->name]);
		}

		$this->view->setMessage('COM_ES_ADS_ADVERTISERS_DELETED');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Toggles the publish state for the ads
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_ADS_INVALID_AD_ID_PROVIDED');
		}

		$task = $this->getTask();

		foreach ($ids as $id) {
			$ad = ES::ad((int) $id);

			if ($ad->isUnderModeration()) {
				$this->view->setMessage('COM_ES_UNABLE_TO_CHANGE_STATE_FOR_MODERATION_ADS', 'error');
				return $this->redirectToView('ads');
			}

			$ad->$task();

			$this->actionlog->log('COM_ES_ACTION_LOG_ADS_' . strtoupper($task), 'advertisement', ['link' => $this->ad_url . '&id=' . $ad->id, 'name' => $ad->title]);
		}

		$message = 'COM_ES_ADS_PUBLISHED';

		if ($task == 'unpublish') {
			$message = 'COM_ES_ADS_UNPUBLISHED';
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $task);
	}

	/**
	 * Saves a ad from the back end
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();

		// Get the ad id from the request
		$id = $this->input->get('id', 0, 'int');

		$ad = ES::ad($id);

		$post = $this->input->post->getArray();

		if (!$post['advertiser_id']) {
			$this->view->setMessage('COM_ES_ADS_EMPTY_ADVERTISER', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
		}

		if (empty($post['title'])) {
			$this->view->setMessage('COM_ES_ADS_EMPTY_TITLE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
		}

		$cover = $this->input->files->get('cover');

		if (!$ad->id && empty($cover['tmp_name'])) {
			$this->view->setMessage('COM_ES_ADS_EMPTY_COVER', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
		}

		$startDate = '0000-00-00 00:00:00';
		$endDate = '0000-00-00 00:00:00';

		if ($post['enable_limit']) {
			// Get the starting and ending date
			$start = $this->input->get('start_date', '', 'default');
			$end = $this->input->get('end_date', '', 'default');

			if (empty($start) || empty($end)) {
				$this->view->setMessage('COM_ES_ADS_EMPTY_DATE', ES_ERROR);
				return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
			}

			$startDate = ES::date($start, false);
			$endDate = ES::date($end, false);

			$startDate = $startDate->toMySQL();
			$endDate = $endDate->toMySQL();
		}

		$post['start_date'] = $startDate;
		$post['end_date'] = $endDate;

		// If the ad is under moderation, we shouldn't be binding the state
		if ($ad->isUnderModeration()) {
			unset($post['state']);
		}

		$ad->bind($post);

		$task = 'update';

		if (!$ad->id) {
			$task = 'create';
			$ad->created = ES::date()->toSql();
		}

		// Save the ad
		if ($ad->isUnderModeration()) {
			$ad->approve();

			if (!empty($cover['tmp_name'])) {
				$state = $ad->uploadCover($cover);
			}

			$this->actionlog->log('COM_ES_ACTION_LOG_ADS_APPROVED', 'advertisement', ['link' => $this->ad_url . '&id=' . $ad->id, 'name' => $ad->title]);

			$this->view->setMessage('COM_ES_ADS_APPROVED_SUCCESS');
			return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
		}

		$state = $ad->save();

		if (!empty($cover['tmp_name'])) {
			$state = $ad->uploadCover($cover);
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_ADS_' . strtoupper($task), 'advertisement', ['link' => $this->ad_url . '&id=' . $ad->id, 'name' => $ad->title]);

		$this->view->setMessage('COM_ES_ADS_UPDATED_SUCCESS');

		if (!$state) {
			$this->view->setMessage($ad->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $this->getTask(), $ad);
	}

	/**
	 * Saves a ad from the back end
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function saveAdvertiser()
	{
		ES::checkToken();

		// Get the ad id from the request
		$id = $this->input->get('id', 0, 'int');

		$advertiser = ES::advertiser($id);

		$post = $this->input->post->getArray();

		if (empty($post['name'])) {
			$this->view->setMessage('COM_ES_ADS_EMPTY_ADVERTISER_NAME', ES_ERROR);
			return $this->view->call(__FUNCTION__, $this->getTask(), $advertiser);
		}

		$post['user_id'] = $advertiser->user_id ? $advertiser->user_id : $this->my->id;

		$advertiser->bind($post);

		// If the admin is saving the advertiser, then the advertiser is being approved
		if ($advertiser->isUnderModeration()) {
			$advertiser->approve();

			$this->actionlog->log('COM_ES_ACTION_LOG_ADVERTISER_APPROVED', 'advertisement', ['link' => $this->advertiser_url . '&id=' . $advertiser->id, 'name' => $advertiser->getCompanyName()]);

			$this->view->setMessage('COM_ES_ADVERTISERS_APPROVED');
			return $this->view->call(__FUNCTION__, $this->getTask(), $advertiser);
		}

		$advertiser->created = ES::date()->toSql();

		$state = $advertiser->save();

		$logo = $this->input->files->get('logo');

		if (!empty($logo['tmp_name'])) {
			$state = $advertiser->uploadLogo($logo);
		}

		$task = $id ? 'update' : 'create';

		$this->actionlog->log('COM_ES_ACTION_LOG_ADVERTISER_' . strtoupper($task), 'advertisement', ['link' => $this->advertiser_url . '&id=' . $advertiser->id, 'name' => $advertiser->getCompanyName()]);

		$this->view->setMessage('COM_ES_ADS_UPDATED_SUCCESS');

		if (!$state) {
			$this->view->setMessage($advertiser->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $this->getTask(), $advertiser);
	}

	/**
	 * Toggles the publish state for the ads
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function togglePublishAdvertiser()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_ES_ADS_INVALID_AD_ID_PROVIDED');
		}

		$action = str_replace('Advertiser', '', $this->getTask());

		foreach ($ids as $id) {
			$ad = ES::table('Advertiser');
			$ad->load((int) $id);

			$ad->$action();

			$this->actionlog->log('COM_ES_ACTION_LOG_ADVERTISER_' . strtoupper($action), 'advertisement', ['link' => $this->advertiser_url . '&id=' . $ad->id, 'name' => $ad->name]);
		}

		$message = 'COM_ES_ADVERTISER_PUBLISHED';

		if ($action == 'unpublish') {
			$message = 'COM_ES_ADVERTISER_UNPUBLISHED';
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__);
	}
}
