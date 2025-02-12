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

class SocialAppHooks extends EasySocial
{
	/**
	 * Determines the language string to be used based on gender
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getGenderForLanguage($languageString, $userId)
	{
		$user = ES::user($userId);
		$genderLanguageString = $user->getGenderLang();

		$languageString .= $genderLanguageString;

		return $languageString;
	}

	/**
	 * Determines the plurality for language string
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getPlurality($languageString, $users)
	{
		$string = ES::string();
		$languageString = $string->computeNoun($languageString, count($users));

		return $languageString;
	}

	/**
	 * Retrieves a list of actors for likes
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getReactionUsers($uid, $type, $additionalUsers = array(), $excludeCurrentViewer = true)
	{
		// Get likes participants
		$model = ES::model('Likes');
		$users = $model->getLikerIds($uid, $type);

		if ($additionalUsers) {
			if (!is_array($additionalUsers)) {
				$additionalUsers = array($additionalUsers);
			}

			$users = !$users ? array() : $users;

			$users = array_merge($users, $additionalUsers);
		}

		$users = $this->getUniqueUsers($users, $excludeCurrentViewer);

		return $users;
	}

	/**
	 * Retrieves a list of reactions for particular item
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function getReactions($uid, $type)
	{
		// Get likes
		$model = ES::model('Likes');
		$likes = $model->getNotificationLikes($uid, $type, array($this->my->id), 3);

		$reactions = array();

		if ($likes) {
			foreach ($likes as $like) {
				$reactions[] = $like->reaction;
			}
		}

		return $reactions;
	}

	/**
	 * Convert a list of users into notification names
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getNames($users)
	{
		$names = ES::string()->namesToNotifications($users);

		return $names;
	}

	/**
	 * Given a list of users, unique the value
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUniqueUsers($users, $excludeCurrentViewer = true)
	{
		// Ensure that the values are unique
		$users = array_unique($users);
		$users = array_values($users);

		// Exclude myself from the list of users.
		if ($excludeCurrentViewer) {
			$index = array_search($this->my->id, $users);

			if ($index !== false) {
				unset($users[$index]);
				$users = array_values($users);
			}
		}

		return $users;
	}
}
