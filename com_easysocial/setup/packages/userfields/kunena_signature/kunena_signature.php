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

ES::import('fields:/user/textarea/textarea');

use Kunena\Forum\Libraries\Factory\KunenaFactory;

class SocialFieldsUserKunena_signature extends SocialFieldsUserTextarea
{
	private function exists()
	{
		static $loaded = false;

		if (!$loaded) {
			$paths = [
				JPATH_ADMINISTRATOR . '/components/com_kunena/api.php',
				JPATH_ADMINISTRATOR . '/components/com_kunena/api/api.php'
			];

			foreach ($paths as $path) {
				if (file_exists($path)) {
					require_once($path);

					$loaded = true;

					return $loaded;
				}
			}
		}

		return $loaded;
	}

	private function getUserObject($id)
	{
		$user = KunenaFactory::getUser($id);

		return $user;
	}

	public function onRegisterAfterSave(&$post, &$user)
	{
		if (!$this->exists())
		{
			return;
		}

		if (!empty($post[$this->inputName]))
		{
			$sig = $this->escape($post[$this->inputName]);

			$kUser = $this->getUserObject($user->id);

			$kUser->signature = $sig;

			$kUser->save();
		}

		unset($post[$this->inputName]);
	}

	public function onEdit(&$post, &$user, $errors)
	{
		if (!$this->exists())
		{
			return;
		}

		$value = null;

		if (!empty($post[$this->inputName]))
		{
			$value = $post[$this->inputName];
		}
		else
		{
			$kUser = $this->getUserObject($user->id);
			$value = $kUser->signature;
		}

		// Set the value.
		$this->set( 'value', $this->escape( $value ) );

		// Get the error.
		$error = $this->getError( $errors );

		// Set the error.
		$this->set( 'error', $error );

		return $this->display();
	}

	public function onEditAfterSave(&$post, &$user)
	{
		if (!$this->exists())
		{
			return;
		}

		if (!empty($post[$this->inputName]))
		{
			$sig = $this->escape($post[$this->inputName]);

			$kUser = $this->getUserObject($user->id);

			$kUser->signature = $sig;

			$kUser->save();
		}

		unset($post[$this->inputName]);
	}

	public function onProfileCompleteCheck($user)
	{
		// If kunena doesn't exist, then we consider this field as completed
		if (!$this->exists()) {
			return true;
		}

		if (!ES::config()->get('user.completeprofile.strict') && !$this->isRequired()) {
			return true;
		}

		$kUser = $this->getUserObject($user->id);
		$value = $kUser->signature;

		return !empty($value);
	}
}
