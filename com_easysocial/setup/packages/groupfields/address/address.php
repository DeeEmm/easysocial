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

ES::import('fields:/user/address/address');

class SocialFieldsGroupAddress extends SocialFieldsUserAddress
{
	public function onRegisterBeforeSave(&$post, &$group)
	{
		parent::onRegisterBeforeSave($post, $group);

		$this->beforeSave($post, $group);
	}

	public function onEditBeforeSave(&$post, &$group)
	{
		parent::onEditBeforeSave($post, $group);

		$this->beforeSave($post, $group);
	}

	public function beforeSave(&$post, &$group)
	{
		$address = $post[$this->inputName];

		$group->latitude = $address->latitude;
		$group->longitude = $address->longitude;
		$group->address = $address->toString();
	}
}
