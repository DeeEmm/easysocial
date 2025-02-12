<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerReviews extends EasySocialController
{
	/**
	 * Saves a review
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function saveReview()
	{
		ES::requireLogin();
		ES::checkToken();

		// Id of the review data
		$id = $this->input->get('id', 0, 'int');
		$ratings = $this->input->get('score', 0, 'int');
		$title = trim($this->input->get('title', '', 'default'));
		$reviewMessage = trim($this->input->get('message', '', 'default'));

		// Get the uid and type
		$uid = $this->input->get('uid', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'string');

		if ($type == SOCIAL_TYPE_USER) {
			$reviewedObj = ES::user($uid);

			if (!$reviewedObj->canSubmitReview()) {
				$this->view->setMessage('APP_REVIEW_SUBMIT_NO_PERMISSION', ES_ERROR);
				return $this->view->call(__FUNCTION__, $uid);
			}

			$options = array('group' => SOCIAL_TYPE_USER, 'type' => SOCIAL_APPS_TYPE_APPS, 'state' => SOCIAL_STATE_PUBLISHED, 'element' => 'reviews');
			$app = ES::table('App');
			$app->load($options);

			$isOwner = $this->my->isSiteAdmin();
			$permalink = ESR::profile(array('id' => $reviewedObj->getAlias(), 'appId' => $app->getAlias()));
		} else {
			$reviewedObj = ES::cluster($type, $uid);

			if (!$reviewedObj->canSubmitReview()) {
				$this->view->setMessage('APP_REVIEW_SUBMIT_NO_PERMISSION', ES_ERROR);
				return $this->view->call(__FUNCTION__, $uid);
			}

			$app = $reviewedObj->getApp('reviews');

			$isOwner = $reviewedObj->isAdmin();
			$permalink = $reviewedObj->getAppPermalink('reviews');
		}

		$params = $app->getParams();

		$moderation = $params->get('enable_moderation', false);
		$needModeration = (!$isOwner && $moderation) ? true : false;

		// Load the filter table
		$review = ES::table('Reviews');

		$isNew = true;

		if ($id) {
			$isNew = false;
			$review->load($id);
		}

		if (!$title) {
			$this->view->setMessage('APP_REVIEWS_NO_TITLE', ES_ERROR);
			return $this->view->call(__FUNCTION__, $uid);
		}

		if (!$ratings) {
			$this->view->setMessage('APP_REVIEWS_NO_RATINGS', ES_ERROR);
			return $this->view->call(__FUNCTION__, $uid);
		}

		$message = 'APP_REVIEWS_UPDATED_SUCCESSFULLY';

		if ($isNew) {
			$message = 'APP_REVIEWS_SUBMITTED_SUCCESSFULLY';
			$review->created_by = $this->my->id;
		}

		// Set the filter attributes
		$review->uid = $uid;
		$review->type = $type;
		$review->value = $ratings;
		$review->published = $needModeration ? SOCIAL_REVIEW_STATE_PENDING : SOCIAL_REVIEW_STATE_PUBLISHED;
		$review->title = $title;
		$review->message = $reviewMessage;

		$state = $review->store();

		if (!$state) {
			$this->view->setMessage('APP_REVIEWS_SAVE_FAILED', ES_ERROR);
			return $this->view->call(__FUNCTION__, $uid);
		}

		if ($needModeration) {
			$this->notify($reviewedObj, $review);
		} else {
			ES::points()->assign($reviewedObj->getTypePlural() . '.review.added', 'com_easysocial', $this->my->id);
		}

		$this->view->setMessage($message);

		return $this->view->call(__FUNCTION__, $permalink);
	}

	/**
	 * Process notification
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function notify($reviewedObj, $review)
	{
		$message = 'APP_REVIEWS_SUBMITTED_FOR_MODERATION';

		if ($reviewedObj->getType() == SOCIAL_TYPE_USER) {

		} else {
			// We need to notify cluster admin
			$reviewedObj->notifyAdmins('moderate.review', array('userId' => $this->my->id, 'reviewId' => $review->id, 'message' => $review->message, 'permalink' => $review->getPermalink(), 'title' => $review->title));
		}
	}

	/**
	 * Allows caller to delete an review
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$review = ES::table('Reviews');
		$review->load($id);

		if (!$review->id || !$id) {
			return $this->view->exception('APP_REVIEWS_INVALID_REVIEWS_ID');
		}

		if (!$review->canDelete()) {
			return $this->view->exception('APP_REVIEWS_NOT_ALLOWED_TO_DELETE');
		}

		$reviewedObj = $review->getReviewedItem();
		$review->delete();

		$this->view->setMessage('APP_REVIEWS_DELETED_SUCCESS', SOCIAL_MSG_SUCCESS);

		return $this->view->call(__FUNCTION__, $reviewedObj->permalink);
	}

	/**
	 * Allows caller to approve a review
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function approve()
	{
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$review = ES::table('Reviews');
		$review->load($id);

		if (!$review->id || !$id) {
			return $this->view->exception('APP_REVIEWS_INVALID_REVIEWS_ID');
		}

		// check if review is currently under pending moderation or not.
		// if not, do not process further.
		if (!$review->isPending()) {
			return $this->view->exception('APP_REVIEWS_INVALID_REVIEWS_ID');
		}

		if (!$review->canApprove()) {
			return $this->view->exception('APP_REVIEWS_NOT_ALLOWED_TO_APPROVE');
		}

		$reviewedObj = $review->getReviewedItem();

		// First we removed any existing stream for this review id
		$review->removeStream();
		$review->publish();

		ES::points()->assign($reviewedObj->getTypePlural() . '.review.added', 'com_easysocial', $review->created_by);

		$this->view->setMessage('APP_REVIEWS_APPROVED_SUCCESS');

		return $this->view->call(__FUNCTION__, $reviewedObj->permalink);
	}

	/**
	 * Allows caller to reject a review
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function reject()
	{
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$review = ES::table('Reviews');
		$review->load($id);

		if (!$review->id || !$id) {
			return $this->view->exception('APP_REVIEWS_INVALID_REVIEWS_ID');
		}

		// check if review is currently under pending moderation or not.
		// if not, do not process further.
		if (!$review->isPending()) {
			return $this->view->exception('APP_REVIEWS_INVALID_REVIEWS_ID');
		}

		if (!$review->canReject()) {
			return $this->view->exception('APP_REVIEWS_NOT_ALLOWED_TO_REJECT');
		}

		$reviewedObj = $review->getReviewedItem();

		$review->delete();

		$this->view->setMessage('APP_REVIEWS_REJECTED_SUCCESS');

		return $this->view->call(__FUNCTION__, $reviewedObj->permalink);
	}

	/**
	 * Allows caller to withdraw a review
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function withdraw()
	{
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');

		$review = ES::table('Reviews');
		$review->load($id);

		if (!$review->id || !$id) {
			return $this->view->exception('APP_REVIEWS_INVALID_REVIEWS_ID');
		}

		// check if review is currently under pending moderation or not.
		// if not, do not process further.
		if (!$review->isPending()) {
			return $this->view->exception('APP_REVIEWS_INVALID_REVIEWS_ID');
		}

		if ($this->my->id != $review->created_by) {
			return $this->view->exception('APP_REVIEWS_NOT_ALLOWED_TO_WITHDRAW');
		}

		$review->delete();

		$this->view->setMessage('APP_REVIEWS_DELETED_SUCCESS');

		if ($review->type == SOCIAL_TYPE_USER) {
			$reviewedObj = ES::user($review->uid);
			$options = array('group' => SOCIAL_TYPE_USER, 'type' => SOCIAL_APPS_TYPE_APPS, 'state' => SOCIAL_STATE_PUBLISHED, 'element' => 'reviews');
			$app = ES::table('App');
			$app->load($options);
			$permalink = ESR::profile(array('id' => $reviewedObj->getAlias(), 'appId' => $app->getAlias()));
		} else {
			// Get the cluster
			$reviewedObj = ES::cluster($review->uid);
			$permalink = $reviewedObj->getAppPermalink('reviews', false);
		}

		return $this->view->call(__FUNCTION__, $permalink);
	}

	/**
	 * Retrieve reviews for provided cluster id
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getReviews()
	{
		$id = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'cmd');

		if ($type == SOCIAL_TYPE_USER) {
			$reviewedObj = ES::user($id);
			$isOwner = $this->my->isSiteAdmin();

			$options = array('group' => SOCIAL_TYPE_USER, 'type' => SOCIAL_APPS_TYPE_APPS, 'state' => SOCIAL_STATE_PUBLISHED, 'element' => 'reviews');
			$app = ES::table('App');
			$app->load($options);
		} else {
			$reviewedObj = ES::cluster($id);

			if (!$reviewedObj->canViewItem()) {
				return $this->view->exception('APP_REVIEWS_NOT_ALLOWED_TO_VIEW');
			}

			$isOwner = $reviewedObj->isAdmin();
			$app = $reviewedObj->getApp('reviews');
		}

		$filter = $this->input->get('filter', 'all', 'cmd');
		$options = array();

		if ($filter != 'all') {
			$options[$filter] = true;
		}

		$params = $app->getParams();
		$options['limit'] = $params->get('total', ES::getLimit());

		if ($filter == 'pending' && !$isOwner) {
			$options['userId'] = $this->my->id;
		}

		$model = ES::model('Reviews');
		$reviews = $model->getReviews($reviewedObj->id, $reviewedObj->getType(), $options);
		$pagination = $model->getPagination();

		if ($type == SOCIAL_TYPE_USER) {
			$pagination->setVar('view', 'profile');
		} else {
			$pagination->setVar('view', $reviewedObj->getTypePlural());
			$pagination->setVar('layout', 'item');
		}

		$pagination->setVar('id', $reviewedObj->getAlias());
		$pagination->setVar('appId', $app->id);
		$pagination->setVar('filter', $filter);

		return $this->view->call(__FUNCTION__, $reviewedObj, $reviews, $pagination, $app, $isOwner);
	}
}
