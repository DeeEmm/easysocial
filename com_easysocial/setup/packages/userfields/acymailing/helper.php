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

class SocialAcyMailingHelper
{
	private $adapter = null;

	public function __construct()
	{
		$acymailing6Exists = $this->acymailing6Exists();
		$extensionName = 'acymailing';

		// Acymailing version 6
		if ($acymailing6Exists) {
			$extensionName = 'acym';
		}

		$file = __DIR__ . '/adapters/' . $extensionName . '.php';
		require_once($file);

		$className = 'SocialAcyMailingAdapter' . ucfirst($extensionName);
		$this->adapter = new $className();
	}

	/**
	 * Determines if Acymailing6 is enabled
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function acymailing6Exists()
	{
		static $exists = null;

		if (is_null($exists)) {
			$enabled = JComponentHelper::isEnabled('com_acym');
			$file = JPATH_ADMINISTRATOR . '/components/com_acym/helpers/helper.php';

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
	 * Determines if Acymailing is enabled
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isEnabled()
	{
		return $this->adapter->isEnabled();
	}

	/**
	 * Retrieves a list of acymailing lists
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getLists()
	{
		return $this->adapter->getLists();
	}

	/**
	 * Allow caller to call user subscriber library From Acymailing
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function subscriberLib()
	{
		return $this->adapter->subscriberLib();
	}

	/**
	 * Allow caller to get the subscriber from Acymailing for particular user
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getParticularSubscriber($user)
	{
		// AcyMailing6 return an object for this user
		// AcyMailing below version 6 return user id

		return $this->adapter->getParticularSubscriber($user);
	}

	/**
	 * Remove a new user from acymailing list
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function unsubscribe($lists, SocialUser &$user)
	{
		return $this->adapter->unsubscribe($lists, $user);
	}

	/**
	 * Determine that whether this user subscribed or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isSubscribed($listId, SocialUser &$user)
	{
		return $this->adapter->isSubscribed($listId, $user);
	}

	/**
	 * Inserts a new user in acymailing list
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function subscribe($lists, SocialUser &$user)
	{
		return $this->adapter->subscribe($lists, $user);
	}
}
