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

class SocialUserAppUsers extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'users') {
			return;
		}

		// the only place that user can submit coments / react on this app is via stream.
		// if the stream id is missing, mean something is fishy.
		// just return false.
		return false;
	}

	/**
	 * Determines if there's activity log
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function hasActivityLog()
	{
		return false;
	}

	/**
	 * Responsible to generate the stream contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'users') {
			return;
		}

		if ($item->verb == 'login') {
			$this->prepareLoginStream($item);
		}
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params = $this->getParams();

		$enabled = $params->get('stream_login', false);
		if(! $enabled) {
			$exclude['users'] = true;
		}
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
		if ($item->context_type != 'users') {
			return false;
		}

		// Check if the settings is enabled.
		$params = $this->getParams();

		if (!$params->get('stream_login', false)) {
			return false;
		}

		$my = ES::user();
		$privacy = ES::privacy($my->id);

		$item->cnt = 1;

		if ($includePrivacy) {
			$uid = $item->id;
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

	private function prepareLoginStream(SocialStreamItem &$item)
	{
		$params = $this->getParams();

		// Check if the settings is enabled.
		if (!$params->get('stream_login', false)) {
			$item->title = '';

			return;
		}

		// Decorate the stream
		$item->color = '#EF9033';
		$item->fonticon	= 'fa-lock';
		$item->label = ES::_('APP_USER_USERS_LOGIN_STREAM_TOOLTIP', true);
		$item->display = SOCIAL_STREAM_DISPLAY_MINI;

		// Set the actor
		$actor = $item->actor;

		// check if actor is an ESAD user, if yes, do not render the stream.
		if (!$actor->hasCommunityAccess()) {
			$item->title = '';
			return;
		}

		$this->set('actor', $actor);

		$item->title = parent::display('streams/login.title');

		if (isset($item->opengraph) && $item->opengraph instanceof SocialOpengraph) {
			// Append the opengraph tags
			$item->addOgDescription($item->title);
		}
	}
}
