<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableReviews extends SocialTable
{
	public $id = null;
	public $uid = null;
	public $type = null;
	public $title = null;
	public $created_by = null;
	public $value = null;
	public $created = null;
	public $published = null;
	public $message = null;

	public function __construct($db)
	{
		parent::__construct('#__social_reviews', 'id', $db);
	}

	/**
	 * Override parent's store behavior
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function store($updateNulls = array())
	{
		$isNew = !$this->id;
		$state = parent::store();

		// If it is a new item, we want to run some other stuffs here.
		if ($isNew && $state && $this->isPublished()) {
			if ($this->type == SOCIAL_TYPE_USER) {
				$this->createUserStream();
			} else {
				$this->createStream();
			}
		}

		return $state;
	}

	/**
	 * Allow caller to create a stream
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function createStream()
	{
		// Get the cluster
		$cluster = ES::cluster($this->uid);

		// Get the permalink of this news item
		$permalink = $this->getPermalink(false, true);

		// Create a new stream item for this discussion
		$stream = ES::stream();

		// Get the stream template
		$tpl = $stream->getTemplate();
		$tpl->setActor($this->created_by, SOCIAL_TYPE_USER);
		$tpl->setContext($this->id, 'reviews');
		$tpl->setCluster($this->uid, $cluster->getType(), $cluster->type);
		$tpl->setVerb('create');

		$registry = ES::registry();
		$registry->set('reviews', $this);

		// Set the params
		$tpl->setParams($registry);

		$tpl->setAccess('core.view');

		// Add the stream
		$stream->add($tpl);
	}

	/**
	 * Allow caller to create a user stream
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function createUserStream()
	{
		// Get the cluster
		$user = ES::user($this->uid);

		// Get the permalink of this news item
		$permalink = $this->getPermalink(false, true);

		// Create a new stream item for this discussion
		$stream = ES::stream();

		// Get the stream template
		$tpl = $stream->getTemplate();
		$tpl->setActor($this->created_by, SOCIAL_TYPE_USER);
		$tpl->setTarget($user->id);
		$tpl->setContext($this->id, 'reviews');
		$tpl->setVerb('create');

		$registry = ES::registry();
		$registry->set('reviews', $this);

		// Set the params
		$tpl->setParams($registry);

		$tpl->setAccess('core.view');

		// Add the stream
		$stream->add($tpl);
	}

	/**
	 * Removes a stream item
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function removeStream()
	{
		$stream = ES::stream();
		$result = $stream->delete($this->id, 'reviews', $this->created_by, 'create');

		return $result;
	}

	/**
	 * Retrieve the author for this reviews
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAuthor()
	{
		return ES::user($this->created_by);
	}

	/**
	 * Retrieves the permalink to the review
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $external = false, $sef = true, $adminSef = false)
	{
		static $apps = array();

		if ($this->type == SOCIAL_TYPE_USER) {
			$cluster = ES::user($this->uid);
			$options = array('group' => SOCIAL_TYPE_USER, 'type' => SOCIAL_APPS_TYPE_APPS, 'state' => SOCIAL_STATE_PUBLISHED, 'element' => 'reviews');
			$app = ES::table('App');
			$app->load($options);
		} else {
			$cluster = ES::cluster($this->type, $this->uid);
			$app = $cluster->getApp('reviews');
		}


		if (!isset($apps[$this->type])) {
			$apps[$this->type] = $app;
		}

		$options = array();
		$options['layout'] = 'canvas';
		$options['customView'] = 'item';
		$options['uid'] = $cluster->getAlias();
		$options['type'] = $this->type;
		$options['id'] = $apps[$this->type]->getAlias();
		$options['reviewId'] = $this->id;
		$options['external'] = $external;
		$options['sef'] = $sef;
		$options['adminSef'] = $adminSef;

		$permalink = ESR::apps($options, $xhtml);

		return $permalink;
	}

	/**
	 * Retrieves the edit permalink to the reviews
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getEditPermalink($xhtml = true, $external = false, $sef = true)
	{
		static $apps = array();

		if ($this->type == SOCIAL_TYPE_USER) {
			$cluster = ES::user($this->uid);
			$options = array('group' => SOCIAL_TYPE_USER, 'type' => SOCIAL_APPS_TYPE_APPS, 'state' => SOCIAL_STATE_PUBLISHED, 'element' => 'reviews');
			$app = ES::table('App');
			$app->load($options);
		} else {
			$cluster = ES::cluster($this->type, $this->uid);
			$app = $cluster->getApp('reviews');
		}

		if (!isset($apps[$this->type])) {
			$apps[$this->type] = $app;
		}

		$options = array();
		$options['layout'] = 'canvas';
		$options['customView'] = 'edit';
		$options['uid'] = $cluster->getAlias();
		$options['type'] = $cluster->getType();
		$options['id'] = $apps[$this->type]->getAlias();
		$options['reviewId'] = $this->id;

		$permalink = ESR::apps($options, $xhtml);

		return $permalink;
	}

	/**
	 * Retrieves the created date
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCreatedDate()
	{
		$date = ES::date($this->created);

		return $date;
	}

	/**
	 * Delete reviews
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if ($state) {
			// Remove the stream that belongs to this review.
			$model = ES::model('Reviews');
			$model->deleteReviewStreams($this->id);
		}

		return $state;
	}

	/**
	 * Publish reviews
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function publish($items = array(), $state = 1, $userId = 0)
	{
		$this->published = SOCIAL_REVIEW_STATE_PUBLISHED;

		$state = parent::store();

		// If stored, create a stream.
		if ($state) {
			if ($this->type == SOCIAL_TYPE_USER) {
				$this->createUserStream();
			} else {
				$this->createStream();
			}
		}
	}

	/**
	 * Determine if the review is in pending state
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isPending()
	{
		return $this->published == SOCIAL_REVIEW_STATE_PENDING;
	}

	/**
	 * Determine if the review is in published state
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isPublished()
	{
		return $this->published == SOCIAL_REVIEW_STATE_PUBLISHED;
	}

	/**
	 * Get reviewed object
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getReviewedItem()
	{
		static $item = null;

		if (isset($item[$this->id])) {
			return $item[$this->id];
		}

		// Get the cluster
		if ($this->type == SOCIAL_TYPE_USER) {
			$reviewedObj = ES::user($this->uid);

			$options = array('group' => SOCIAL_TYPE_USER, 'type' => SOCIAL_APPS_TYPE_APPS, 'state' => SOCIAL_STATE_PUBLISHED, 'element' => 'reviews');
			$app = ES::table('App');
			$app->load($options);

			$reviewedObj->permalink = ESR::profile(array('id' => $reviewedObj->getAlias(), 'appId' => $app->getAlias()));
		} else {
			// Get the cluster
			$reviewedObj = ES::cluster($this->uid);
			$reviewedObj->permalink = $reviewedObj->getAppPermalink('reviews', false);
		}

		$item[$this->id] = $reviewedObj;

		return $item[$this->id];
	}

	/**
	 * Determine if user can delete the review
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function canDelete()
	{
		if (ES::user()->isSiteAdmin()) {
			return true;
		}

		// Cluster
		if ($this->type != SOCIAL_TYPE_USER) {
			$reviewedObj = $this->getReviewedItem();

			if ($reviewedObj->isAdmin() || ES::user()->isSiteAdmin()) {
				return true;
			}

			// The owner can delete their review in cluster
			if ($this->created_by == ES::user()->id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if user can approve the review
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function canApprove()
	{
		if (ES::user()->isSiteAdmin()) {
			return true;
		}

		$reviewedObj = $this->getReviewedItem();

		if ($this->type != SOCIAL_TYPE_USER) {
			$reviewedObj = $this->getReviewedItem();

			if ($reviewedObj->isAdmin()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if user can reject the review
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function canReject()
	{
		// Basically user that can approve will also able to reject it
		return $this->canApprove();
	}

}
