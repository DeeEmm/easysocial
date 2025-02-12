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

jimport('joomla.filesystem.file');

class SocialStorage
{
	private $adapter = null;

	public function __construct($storage = 'joomla')
	{
		// Always lowercase the storage name
		$storage = strtolower($storage);

		$file = __DIR__ . '/' . $storage . '/' . $storage . '.php';
		require_once($file);

		$className = 'SocialStorage' . ucfirst($storage);

		$this->adapter = new $className();
	}

	public function factory($storage = 'joomla')
	{
		return new self($storage);
	}

	/**
	 * Maps back the call method functions to the helper.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __call($method, $args)
	{
		$refArray = array();

		if ($args) {
			foreach ($args as &$arg) {
				$refArray[]	=& $arg;
			}
		}

		return call_user_func_array(array($this->adapter, $method), $refArray);
	}

	/**
	 * Determine if storage management feature is enabled
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isStorageManagementEnabled()
	{
		static $enabled = null;

		if (is_null($enabled)) {
			$enabled = ES::config()->get('storage.size.enable', false);
		}

		return $enabled;
	}

	/**
	 * Retrieve the maximum storage size limit for this user
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getStorageSizeLimit($userId = null, $includeUnit = false, $unit = 'MB')
	{
		$acl = ES::user($userId)->getAccess();
		$size = $acl->get('storage.media.maxsize');

		// Add trigger for listener to override the size limit
		ESDispatcher::trigger('onEasySocialGetStorageSizeLimit', array($userId, &$size));

		if (!$size) {
			if ($includeUnit) {
				return '&#8734;';
			}

			return 0;
		}

		$size = ES::math()->convertBytes($size . 'M');

		if ($includeUnit) {
			$size = ES::math()->convertUnits($size, 'B', $unit, true, true);
		}

		return $size;
	}

	/**
	 * Method to compute and sync all existing storage usage on the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function syncUsage($userId = null)
	{
		if (!$this->isStorageManagementEnabled()) {
			return true;
		}

		$model = ES::model('storage');
		$state = $model->syncUsage($userId);

		$this->notify($userId);

		return $state;
	}

	/**
	 * Add the storage usage
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function addUsage($userId, $size)
	{
		if (!$this->isStorageManagementEnabled()) {
			return true;
		}

		$table = ES::table('storageUsage');
		$table->load(array('user_id' => $userId));

		if (!$table->id) {
			$this->syncUsage($userId);
		} else {
			$table->size = $table->size + ES::math()->convertBytes($size);
			$table->store();
		}

		$this->notify($userId);

		return true;
	}

	/**
	 * Remove storage usage
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function removeUsage($userId, $size)
	{
		if (!$this->isStorageManagementEnabled()) {
			return true;
		}

		$table = ES::table('storageUsage');
		$table->load(array('user_id' => $userId));

		if (!$table->id) {
			$this->syncUsage($userId);
		} else {
			$table->size = $table->size - ES::math()->convertBytes($size);
			$table->store();
		}

		return true;
	}

	/**
	 * Retrieve the total usage of the storage
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getTotalUsage($userId = null, $includeUnit = false, $unit = 'MB')
	{
		$user = ES::user($userId);

		$table = ES::table('storageUsage');
		$table->load(array('user_id' => $user->id));

		if (!$table->id) {
			$this->syncUsage($user->id);

			// Reload the table
			$table = ES::table('storageUsage');
			$table->load(array('user_id' => $user->id));
		}

		if (!$table->id) {
			if ($includeUnit) {
				return '0' . $unit;
			}

			return 0;
		}

		// Something is not right with the size. Let's re-sync it again
		if ($table->size < 0) {
			$this->syncUsage($user->id);

			$table = ES::table('storageUsage');
			$table->load(array('user_id' => $user->id));
		}

		$size = $table->size;

		if ($includeUnit) {
			$size = ES::math()->convertUnits($size, 'B', $unit, true, true, 2);
		}

		return $size;
	}

	/**
	 * Method to determine if user storage reached maximum limit
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function isLimit($userId, $size)
	{
		if (!$this->isStorageManagementEnabled()) {
			return false;
		}

		$maxLimit = $this->getStorageSizeLimit($userId);
		$currentUsage = $this->getTotalUsage($userId);

		// Unlimited storage
		if ($maxLimit === 0) {
			return false;
		}

		// Current usage already reached max limit
		if ($currentUsage >= $maxLimit) {
			return true;
		}

		$nextUsage = $currentUsage + $size;

		if ($nextUsage > $maxLimit) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve usage percentage
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getUsagePercentage($userId = null)
	{
		$usage = $this->getTotalUsage($userId, false);
		$limit = $this->getStorageSizeLimit($userId, false);

		// Unlimited storage
		if ($limit == 0) {
			return 0;
		}

		$percentage = floor($usage / $limit * 100);

		if ($percentage >= 100) {
			return 100;
		}

		return $percentage;
	}

	/**
	 * Usage status
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getUsageStatus($userId = null)
	{
		$percentage = $this->getUsagePercentage($userId);
		$warningUsage = ES::config()->get('storage.size.warning', 80);

		// storage is plenty
		$status = SOCIAL_STORAGE_STATUS_NOT_FULL;

		if ($percentage >= $warningUsage) {
			$status = SOCIAL_STORAGE_STATUS_ALMOST_FULL;
		}

		if ($percentage >= 100) {
			$status = SOCIAL_STORAGE_STATUS_FULL;
		}

		return $status;
	}

	/**
	 * Notify the user when storage is almost full
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function notify($userId)
	{
		if (!$this->isStorageManagementEnabled()) {
			return true;
		}

		if (!$userId) {
			return;
		}

		$table = ES::table('storageUsage');
		$table->load(array('user_id' => $userId));
		$totalUsage = $this->getTotalUsage($userId, true);
		$storageLimit = $this->getStorageSizeLimit($userId, true);
		$status = $this->getUsageStatus($userId);
		$usagePercentage = $this->getUsagePercentage($userId);

		if ($status === SOCIAL_STORAGE_STATUS_ALMOST_FULL || $status === SOCIAL_STORAGE_STATUS_FULL) {

			if (!$table->notify) {
				$user = ES::user($userId);

				// Set the email options
				$emailOptions = array(
					'title' => 'COM_ES_EMAILS_STORAGE_WARNING',
					'template' => 'site/storage/warning',
					'params' => array(
						'totalUsage' => $totalUsage,
						'storageLimit' => $storageLimit,
						'usagePercentage' => $usagePercentage,
						'editProfileUrl' => ESR::profile(array('layout' => 'edit', 'external' => true))
					)
				);

				$systemOptions = array(
					'title' => JText::sprintf('COM_ES_STORAGE_SIZE_WARNING_SYSTEM_TITLE', $usagePercentage),
					'context_type' => 'warning',
					'url' => ESR::profile(array('layout' => 'edit')),
					'actor_id' => $user->id
				);

				// Notify user
				ES::notify('storage.size.warning', array($userId), $emailOptions, $systemOptions);

				// Update notify column
				$table->notify = 1;
			}
		} else {

			// Reset notify column
			$table->notify = 0;
		}

		$table->store();
	}

	// /**
	//  * Retrieve the upload limit that can be override by trigger listener
	//  *
	//  * @since	3.3.0
	//  * @access	public
	//  */
	// public function getUserUploadLimit($key, $userId = null)
	// {
	// 	$user = ES::user($userId);
	// 	$access = $user->getAccess();
	// 	$size = (int) $access->get($key);

	// 	// Add trigger for listener to override the size limit
	// 	ESDispatcher::trigger('onEasySocialGetUploadSizeLimit', array($user->id, &$size));

	// 	return $size;
	// }
}

interface SocialStorageInterface
{
	public function init();

	public function createContainer($container);

	public function getPermalink($relativePath);

	public function push($fileName, $path, $relativePath, $mimeType = 'application/octet-stream');

	public function pull($relativePath);

	public function delete($relativePath, $folder = false);

	public function download($filePath, $fileName);
}
