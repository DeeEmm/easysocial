<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/apps/apps');

class SocialUserAppProfiles extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'profiles') {
			return;
		}

		// this user app should not even reach here.
		// just return false
		return false;
	}

	/**
	 * Responsible to generate the activity contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'profiles') {
			return;
		}

		$actor	= $item->actor;
		$genderValue = $actor->getFieldData('GENDER');
		$gender = 'THEIR';

		if ($genderValue == 1) {
			$gender = 'MALE';
		}

		if ($genderValue == 2) {
			$gender = 'FEMALE';
		}

		$this->set('gender', $gender);
		$this->set('actor', $item->actor);

		$item->title = parent::display('streams/' . $item->verb . '.title');

		if ($includePrivacy) {
			$privacy = $this->my->getPrivacy();
			$item->privacy = $privacy->form($item->uid , SOCIAL_TYPE_ACTIVITY, $item->actor->id, 'core.view', false, $item->aggregatedItems[0]->uid);
		}

		return true;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'profiles') {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {
			$my = ES::user();
			$privacy = ES::privacy($my->id);

			$sModel = ES::model('Stream');
			$aItem = $sModel->getActivityItem($item->id, 'uid');

			if ($aItem) {
				$uid = $aItem[0]->id;

				if (!$privacy->validate('core.view', $uid , SOCIAL_TYPE_ACTIVITY , $item->actor_id)) {
					$item->cnt = 0;
				}
			}

		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 * @since	1.2
	 * @access	public
	 * @param	array
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$excludeVerb = [];

		if(!$params->get('stream_update', true)) {
			$excludeVerb[] = 'update';
		}

		if (!$params->get('stream_register', true)) {
			$excludeVerb[] = 'register';
		}

		if ($excludeVerb !== false) {
			$exclude['profiles'] = $excludeVerb;
		}
	}


	/**
	 * Responsible to generate the stream content for profiles apps.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	object	$params		A standard object with key / value binding.
	 *
	 * @return	none
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'profiles') {
			return;
		}

		// Do not render the stream if the user is not activated or being blocked
		// to respect the user privacy
		if ($item->actor->block) {
			return;
		}

		// Get the application params
		$params = $this->getParams();

		$actor = $item->actor;

		if (!$actor->hasCommunityAccess()) {
			$item->title = '';
			return;
		}

		if ($item->verb == 'update' && !$params->get('stream_update', true)) {
			return;
		}

		if ($item->verb == 'register' && !$params->get('stream_register', true)) {
			return;
		}

		$item->display = SOCIAL_STREAM_DISPLAY_MINI;

		$genderValue = $actor->getFieldData('GENDER');

		$gender = 'THEIR';

		if ($genderValue == 1) {
			$gender = 'MALE';
		}

		if ($genderValue == 2) {
			$gender = 'FEMALE';
		}

		$this->set('gender', $gender);
		$this->set('actor', $item->actor);

		$item->title = parent::display('streams/' . $item->verb . '.title');

		return true;
	}

	/**
	 * Responsible to generate the stream content for profiles apps.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'profiles') {
			return;
		}

		// Do not render the stream if the user is not activated or being blocked
		// to respect the user privacy
		if ($item->actor->block) {
			return;
		}

		// Get the application params
		$params = $this->getParams();

		$actor = $item->actor;

		if (!$actor->hasCommunityAccess()) {
			$item->title = '';
			return;
		}

		if ($item->verb == 'update' && !$params->get('stream_update', true)) {
			return;
		}

		if ($item->verb == 'register' && !$params->get('stream_register', true)) {
			return;
		}

		$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		$item->show = true;

		return true;
	}
}
