<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

class SocialAcyMailingAdapterAcymailing
{
	/**
	 * Determines if Acymailing is enabled
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isEnabled()
	{
		static $exists = null;

		if (is_null($exists)) {
			$enabled = JComponentHelper::isEnabled('com_acymailing');
			$file = JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';

			$fileExists = JFile::exists($file);
			$exists = false;

			if ($enabled && $fileExists) {
				$exists = true;
				require_once($file);
			}
		}

		return $exists;
	}

	/**
	 * Retrieves a list of acymailing lists
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getLists()
	{
		// Skip this if not enabled
		if (!$this->isEnabled()) {
			return false;
		}

		$lib = acymailing_get('class.list');

		$lists = $lib->getLists();

		return $lists;
	}

	/**
	 * Allow caller to call user subscriber library From Acymailing
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function subscriberLib()
	{
		// Skip this if not enabled
		if (!$this->isEnabled()) {
			return false;
		}

		$lib = acymailing_get('class.subscriber');

		return $lib;
	}

	/**
	 * Allow caller to get the subscriber id from Acymailing for particular user
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getParticularSubscriber($user)
	{
		// Skip this if not enabled
		if (!$this->isEnabled()) {
			return false;
		}

		$lib = $this->subscriberLib();
		$subscriberId = $lib->subid($user->id);

		return $subscriberId;
	}

	/**
	 * Remove a new user from acymailing list
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function unsubscribe($lists, SocialUser &$user)
	{
		// Skip this if not enabled
		if (!$this->isEnabled()) {
			return false;
		}

		$lib = $this->subscriberLib();

		$newSubscription = array();

		foreach ($lists as $id) {
			$newList = array();
			$newList['status'] = -1;

			$newSubscription[$id] = $newList;
		}
		// Get subscription id for this particular user
		$subscriberId = $lib->subid($user->id);

		if (!$subscriberId) {
			return false;
		}

		return $lib->saveSubscription($subscriberId, $newSubscription);
	}

	/**
	 * Determine that whether this user subscribed or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isSubscribed($lists, SocialUser &$user)
	{
		$userClass = $this->subscriberLib();

		$subscribedLists = $userClass->subid($user->id);

		$lib = acymailing_get('class.listsub');
		$subscriptions = $lib->getSubscription($subscribedLists);

		// Retrieve current user subscribed which list in Acymailing
		$subscriberListIds = array();

		foreach ($subscriptions as $subscription) {

			if ($subscription->status == '1') {
				$subscriberListIds[] = $subscription->listid;
			}

			// if ($subscription->listid == $listId && $subscription->status == '1') {
			// 	return true;
			// }
		}

		// Skip this if user doesn't have subscribe any list in Acymailing
		if (!$subscriberListIds) {
			return false;
		}

		// Retrieve total of lists as what admin set from custom field
		$totalOfLists = count($lists);

		// Determine whether user subscribed all the list ids as what admin set from the custom field
		$hasMatches = array_intersect($subscriberListIds, $lists);

		// Skip this if user doesn't have subscribe any list id
		if (!$hasMatches) {
			return false;
		}

		// Retrieve total of matched list ids
		$totalOfSubscriberIds = count($hasMatches);

		// Skip this if user doesn't subscribe all the list ids from custom field
		// So it will not checked the checkbox from this custom field
		// If there only subscribed one of list id, then always mark it as not subscribed
		if ($totalOfLists != $totalOfSubscriberIds) {
			return false;
		}

		// Ensure that current user subscribed all these list ids then only mark it as subscribed
		return true;
	}

	/**
	 * Inserts a new user in acymailing list
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function subscribe($lists, SocialUser &$user)
	{
		// Skip this if not enabled
		if (!$this->isEnabled()) {
			return false;
		}

		$lib = $this->subscriberLib();

		$newSubscription = array();

		foreach ($lists as $id) {
			$newList = array();
			$newList['status'] = 1;

			$newSubscription[$id] = $newList;
		}

		// Get subscription id for this particular user
		$subscriberId = $lib->subid($user->id);

		if (!$subscriberId) {
			return false;
		}

		return $lib->saveSubscription($subscriberId, $newSubscription);
	}
}
