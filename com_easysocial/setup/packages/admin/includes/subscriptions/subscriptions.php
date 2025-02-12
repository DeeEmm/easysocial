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

class SocialSubscriptions extends EasySocial
{
	public $table = null;

	public function __construct()
	{
		parent::__construct();

		// Assign the table
		$this->table = ES::table("Subscription");
	}

	/**
	 * Loads a subscription record
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function load($uid, $type, $group, $userId = null)
	{
		if (is_null($userId)) {
			$userId = $this->my->id;
		}

		$exists = $this->table->load(array('uid' => $uid, 'type' => $type . '.' . $group, 'user_id' => $userId));


		return $exists;
	}

	/**
	 * Magic methods to bind subscription properties to the table
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function set($key, $value)
	{
		$this->table->$key = $value;
	}

	/**
	 * Magic method to get properties which don't exist on this object but on the table
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function __get($key)
	{
		if (isset($this->table->$key)) {
			return $this->table->$key;
		}

		if (isset($this->$key)) {
			return $this->$key;
		}

		return $this->table->$key;
	}

	/**
	 * Allows caller to subscribe a person
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function subscribe($uid, $type, $group, $currentViewerId = null)
	{
		$currentViewer = ES::user($currentViewerId);

		// User should never be allowed to follow themselves.
		if ($currentViewer->id == $uid) {
			$this->setError('COM_EASYSOCIAL_FOLLOWERS_NOT_ALLOWED_TO_FOLLOW_SELF');
			return false;
		}

		// apply flood control here.
		// if subscribe being called from friends lib, we do not need to check for flood control.
		// when currentViewerId is not null, most likley is called from friend lib.

		if (!ES::isFromAdmin() && is_null($currentViewerId) && !$this->canSubscribe($currentViewer->id)) {
			$this->setError('COM_ES_FOLLOWERS_NOT_ALLOWED');
			return false;
		}

		// Determine if the current user is already a follower
		$subscribed = $this->isSubscribed($uid, $type, $group, $currentViewerId);

		// If it's already following, throw proper message
		if ($subscribed) {
			$this->setError('COM_EASYSOCIAL_SUBSCRIPTIONS_ERROR_ALREADY_FOLLOWING_USER');
			return false;
		}

		// If the user isn't alreayd following, create a new subscription record.
		$this->table->uid = $uid;
		$this->table->type = $type . '.' . $group;
		$this->table->user_id = $currentViewer->id;

		// Try to save the subscription now
		$state = $this->table->store();

		if (!$state) {
			$this->setError($this->table->getError());
			return false;
		}

		// Get the target user
		$user = ES::user($uid);

		// @badge: followers.follow
		ES::badges()->log('com_easysocial', 'followers.follow', $currentViewer->id, 'COM_EASYSOCIAL_FOLLOWERS_BADGE_FOLLOWING_USER');
		ES::badges()->log('com_easysocial', 'followers.followed', $user->id, 'COM_EASYSOCIAL_FOLLOWERS_BADGE_FOLLOWED');

		// @points: profile.follow
		ES::points()->assign('profile.follow', 'com_easysocial', $currentViewer->id);
		ES::points()->assign('profile.followed', 'com_easysocial', $user->id);


		// Share this on the stream.
		$stream = ES::stream();
		$streamTemplate = $stream->getTemplate();

		// Set the actor.
		$streamTemplate->setActor($currentViewer->id, SOCIAL_TYPE_USER);
		$streamTemplate->setContext($this->table->id , SOCIAL_TYPE_FOLLOWERS);
		$streamTemplate->setVerb('follow');
		$streamTemplate->setAccess('followers.view');
		$stream->add($streamTemplate);

		// Set the email options
		$emailOptions   = array(
			'title'     	=> 'COM_EASYSOCIAL_EMAILS_NEW_FOLLOWER_SUBJECT',
			'template'		=> 'site/followers/new.followers',
			'actor'     	=> $currentViewer->getName(),
			'actorAvatar'   => $currentViewer->getAvatar(SOCIAL_AVATAR_SQUARE),
			'actorLink'     => $currentViewer->getPermalink(true, true),
			'target'		=> $user->getName(),
			'targetLink'	=> $user->getPermalink(true, true),
			'totalFriends'		=> $currentViewer->getTotalFriends(),
			'totalFollowing'	=> $currentViewer->getTotalFollowing(),
			'totalFollowers'	=> $currentViewer->getTotalFollowers()
		);

		// Notify the target
		$state = ES::notify('profile.followed' , array($user->id), $emailOptions, array('url' => $currentViewer->getPermalink(false, false, false), 'actor_id' => $currentViewer->id , 'uid' => $uid));

		return true;
	}

	/**
	 * Allows caller to unsubscribe from an object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canSubscribe($userId)
	{
		// in seconds
		$config = ES::config();
		$second_limit = (int) $config->get('followers.flood.limit', 0);

		if ($second_limit <= 0) {
			// flood control disabled.
			return true;
		}

		$db = ES::db();
		$now = ES::date()->toSql();

		// apply flod control here.
		$query = "select `id`, TIMESTAMPDIFF(SECOND, `created`, " . $db->Quote($now) . ") as secdiff";
		$query .= " from `#__social_subscriptions`";
		$query .= " where `user_id` = " . $db->Quote($userId);
		$query .= " order by `id` desc limit 1";

		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result && $result->id) {
			$prev = $result->secdiff;

			if ($prev <= $second_limit) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Allows caller to unsubscribe from an object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unsubscribe()
	{
		// Check if this record really exists
		if (!$this->table->id) {
			return false;
		}

		// Try to delete the record from the table first
		if (!$this->table->delete()) {
			$this->setError($this->table->getError());
			return false;
		}

		// Once unfollowed a user, delete the previously created streams
		$stream	= ES::stream();
		$stream->delete($this->table->id, SOCIAL_TYPE_FOLLOWERS);

		// Points integrations
		ES::points()->assign('profile.unfollow', 'com_easysocial', $this->table->user_id);
		ES::points()->assign('profile.unfollowed','com_easysocial', $this->table->uid);

		return true;
	}

	/**
	 * Determines if the user has already subscribed before
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isSubscribed($uid, $element, $group = SOCIAL_APPS_GROUP_USER, $userId = null)
	{
		if (is_null($userId)) {
			$userId = ES::user()->id;
		}

		// This is the key of the subscriptions
		$key = $element . '.' . $group;

		$model = ES::model("Subscriptions");
		return $model->isFollowing($uid, $key, $userId);
	}

	/**
	 * Gets the target object of the subscription record
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTarget()
	{
		$target = ES::user($this->uid);

		return $target;
	}

	/**
	 * Method to process email digeset subscriptions
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function processDigest()
	{
		$now = ES::date()->toSql();

		$model = ES::model('Subscriptions');
		$userIds = $model->getDigestSubscribers($now);

		// nothing to process
		if (! $userIds) {
			return false;
		}

		$config = ES::config();

		if (!$config->get('notifications.email.enabled')) {
			return true;
		}

		foreach ($userIds as $userId) {

			$items = $model->getDigestEmailSubscriptions($now, $userId);

			if (! $items) {
				// nothing to process
				continue;
			}

			// $user = JFactory::getUser($userId);
			$user = ES::User($userId);

			$clusters = array();

			foreach ($items as $item) {

				// build cluster url
				$alias = $item->cluster_id . ':' . JFilterOutput::stringURLSafe($item->alias);
				$options = array('id' => $alias, 'layout' => 'item');
				$options['external'] = true;
				$options['sef'] = true;

				$url = ESR::groups($options, true);

				$obj = new stdClass();
				$obj->id = $item->cluster_id;
				$obj->title = $item->title;
				$obj->link = $url;
				$obj->posts = array();

				$clusters[$item->cluster_id] = $obj;
			}

			$stream = ES::stream();
			$displayOptions = array();
			$displayOptions['commentLink'] = false;
			$displayOptions['commentForm'] = false;
			$displayOptions['perspective'] = 'groups';

			$posts = array();

			// annoucements
			$results = $model->getDigestPosts($items, $now, 'news');
			$results = $stream->format($results, SOCIAL_STREAM_CONTEXT_TYPE_ALL, $userId, false, 'onPrepareDigest', $displayOptions);
			if ($results && $results !== true) {
				$posts['news'] = $results;
			}

			// discussions
			$results = $model->getDigestPosts($items, $now, 'discussions');
			$results = $stream->format($results, SOCIAL_STREAM_CONTEXT_TYPE_ALL, $userId, false, 'onPrepareDigest', $displayOptions);
			if ($results && $results !== true) {
				$posts['discussions'] = $results;
			}

			// tasks
			$results = $model->getDigestPosts($items, $now, 'tasks');
			$results = $stream->format($results, SOCIAL_STREAM_CONTEXT_TYPE_ALL, $userId, false, 'onPrepareDigest', $displayOptions);
			if ($results && $results !== true) {
				$posts['tasks'] = $results;
			}

			// events
			$results = $model->getDigestPosts($items, $now, 'events');
			$results = $stream->format($results, SOCIAL_STREAM_CONTEXT_TYPE_ALL, $userId, false, 'onPrepareDigest', $displayOptions);
			if ($results && $results !== true) {
				$posts['events'] = $results;
			}

			// others
			$results = $model->getDigestPosts($items, $now, 'others');
			$results = $stream->format($results, SOCIAL_STREAM_CONTEXT_TYPE_ALL, $userId, false, 'onPrepareDigest', $displayOptions);
			if ($results && $results !== true) {
				$posts['others'] = $results;
			}


			$clusterHtml = '';

			if ($posts) {
				// group the posts
				foreach($posts as $type => $rows) {

					foreach ($rows as $row) {

						// now we format to what the email template can understand.
						$item = $this->formatDigest($row);

						$clusterId = $row->cluster_id;
						if ($row->cluster_type == 'event') {
							$event = ES::event($row->cluster_id);

							$x = $event->getCluster();
							$clusterId = $x->id;
						}

						// manually group the data by cluster.
						$clusters[$clusterId]->posts[$type][] = $item;

					}
				}

				$namespace = "site/emails/subscriptions/digest.clusters";

				$theme = ES::themes();
				$theme->set('clusters', $clusters);
				$clusterHtml = $theme->output($namespace);
			}

			// proceed to email sending if there are something.
			if ($clusterHtml) {

				$subject = JText::sprintf('COM_ES_DIGEST_EMAIL_SUBJECT', ES::date()->format(JText::_('COM_EASYSOCIAL_DATE_DMY')), ES::jconfig()->getValue('sitename'));

				$params = array(
						'sitename' => ES::jconfig()->getValue('sitename'),
						'actorName' => $user->name,
						'now' => ES::date()->format(JText::_('COM_EASYSOCIAL_DATE_DMY')),
						'content' => $clusterHtml
					);

				ES::load('Mailer');
				$mailerData = new SocialMailerData();

				$mailerData->setTitle($subject);
				$mailerData->setRecipient($user->name, $user->email);
				$mailerData->setTemplate('site/subscriptions/digest');
				$mailerData->setParams($params);
				$mailerData->setFormat(1);
				$mailerData->setLanguage($user->getLanguage());


				// // add into mail queue
				ES::mailer()->create($mailerData);
			}

			// now update subscriptions sent
			$model->updateDigestSentOut($items);

		}

		return true;
	}

	/**
	 * Method to format the content so that the email template can display correctly.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function formatDigest($data)
	{
		$item = new SocialSubscriptionsDigestItem();

		$item->title = $data->title;
		$item->preview = $data->preview;

		if (!isset($data->link)) {
			if ($data instanceof SocialStreamItem) {
				$item->link = $data->getPermalink(true, true);
			} else {
				$item->link = '';
			}
		} else {
			$item->link = $data->link;
		}

		return $item;
	}
}


class SocialSubscriptionsDigestItem extends EasySocial
{
	public $title = null;
	public $preview = null;
	public $link = null;

	public function __construct($options = array())
	{
		parent::__construct();
	}
}
