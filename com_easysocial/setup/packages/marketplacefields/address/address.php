<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('fields:/user/address/address');

class SocialFieldsMarketplaceAddress extends SocialFieldsUserAddress
{
	public function onRegisterBeforeSave(&$post, &$listing)
	{
		parent::onRegisterBeforeSave($post, $listing);

		$this->beforeSave($post, $listing);
	}

	public function onEditBeforeSave(&$post, &$listing)
	{
		parent::onEditBeforeSave($post, $listing);

		$this->beforeSave($post, $listing);
	}

	public function beforeSave(&$post, &$listing)
	{
		$address = $post[$this->inputName];

		$listing->latitude = $address->latitude;
		$listing->longitude = $address->longitude;
		$listing->address = $address->toString();
	}
}
