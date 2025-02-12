<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsEventOwnerstate extends SocialFieldItem
{
	public function onRegister(&$post, &$session)
	{
		$guestState = isset($post['ownerstate']) ? $post['ownerstate'] : SOCIAL_EVENT_GUEST_GOING;

		$this->set('guestState', $guestState);

		return $this->display();
	}

	public function onRegisterAfterSave(&$post, &$event)
	{
		if (!isset($post['ownerstate'])) {
			return;
		}

		$owner = $event->getOwner();

		$guest = $event->getGuest($event->creator_uid);

		$guest->state = $post['ownerstate'];

		$guest->store();

		unset($post['ownerstate']);
	}
}
