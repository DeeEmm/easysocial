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

class SocialAcyMailingAdapterAcym
{
	/**
	 * Determines if Acymailing is enabled
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isEnabled()
	{
		// Skip this because already validate this under construct function.
		return true;
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

		$lib = acym_get('class.list');

		$lists = $lib->getAll();

		// You can then do a foreach on this variable (which contains an array of objects) and use ->name
		// You can get a specific list like this

		// $myList = $lib->getOneById(34);

		// Or multiple lists like this

		// $myLists = $lib->getListsByIds(array(22, 48));

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

		$lib = acym_get('class.user');

		return $lib;
	}

	/**
	 * Allow caller to get the subscriber object from Acymailing for particular user
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

		// Get the subscriber object from Acymailing for particular user
		$subscriber = $lib->getOneByCMSId($user->id);

		return $subscriber;
	}

	/**
	 * Inserts a new user in acymailing list
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function unsubscribe($listIds, SocialUser &$user)
	{
		// Skip this if not enabled
		if (!$this->isEnabled()) {
			return false;
		}

		$lib = $this->subscriberLib();

		// Get the subscriber object from Acymailing for particular user
		$subscriber = $lib->getOneByCMSId($user->id);

		if (!$subscriber) {
			return false;
		}

		return $lib->unsubscribe($subscriber->id, $listIds);
	}

	/**
	 * Determine that whether this user subscribed or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isSubscribed($lists, SocialUser &$user)
	{
		$lib = $this->subscriberLib();

		// Get the subscriber object from Acymailing for particular user
		$subscriber = $lib->getOneByCMSId($user->id);

		// Get user a list of subscriptions
		$subscriptions = $lib->getUserSubscriptionById($subscriber->id);

		// Retrieve current user subscribed which list in Acymailing
		$subscriberListIds = array();

		foreach ($subscriptions as $subscription) {

			if ($subscription->status == '1') {
				$subscriberListIds[] = $subscription->id;
			}
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
	public function subscribe($listIds, SocialUser &$user)
	{
		// Skip this if not enabled
		if (!$this->isEnabled()) {
			return false;
		}

		$lib = $this->subscriberLib();

		// Get the subscriber object from Acymailing for particular user
		$subscriber = $lib->getOneByCMSId($user->id);

		if (!$subscriber) {
			return false;
		}

		return $lib->subscribe($subscriber->id, $listIds);
	}
}
