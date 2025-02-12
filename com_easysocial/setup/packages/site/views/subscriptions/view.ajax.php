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

class EasySocialViewSubscriptions extends EasySocialSiteView
{
	/**
	 * Post processing after a user is being subscribed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function follow(SocialSubscriptions $subscription)
	{
		$theme = ES::themes();
		$output = $theme->html('user.subscribe', $subscription->getTarget());

		return $this->ajax->resolve($output);
	}

	/**
	 * Post processing after a user is being unsubscribed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unfollow(SocialSubscriptions $subscriptions)
	{
		$theme = ES::themes();
		$output = $theme->html('user.subscribe', $subscriptions->getTarget());

		return $this->ajax->resolve($output);
	}

	/**
	 * Process cluster email digest subscription.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function digest()
	{
		$uid = $this->input->get('uid', 0, 'int');
		$utype = $this->input->get('utype', '', 'default');
		$interval = $this->input->get('interval', 1, 'int');

		$clusters = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE);

		if (!in_array($utype, $clusters)) {
			return $this->ajax->reject(JText::_('COM_ES_DIGEST_INVALID_CLUSTER_TYPE'));
		}

		$cluster = ES::cluster($utype, $uid);

		if (!$cluster->canSubsribeDigest()) {
			return $this->ajax->reject(JText::_('COM_ES_DIGEST_ONLY_MEMBER_CAN_SUBSCRIBE'));
		}

		$state = $cluster->subscribe($this->my->id, $interval);

		if (!$state) {
			return $this->ajax->reject(JText::_('COM_ES_DIGEST_SUBSCRIPTION_FAILED'));
		}

		return $this->ajax->resolve($state);
	}
}
