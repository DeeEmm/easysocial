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

class SocialImageOptimizer extends EasySocial
{
	/**
	 * Retrieves information about a file
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getImageInfo($path)
	{
		static $items = array();

		if (!isset($items[$path])) {
			$items[$path] = false;
			$data = @getimagesize($path);

			if ($data !== false) {
				$items[$path] = $data;
			}
		}

		return $items[$path];
	}

	/**
	 * Retrieves the log record
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getLog($uid, $type)
	{
		$table = ES::table('Optimizer');
		$table->load([
			'uid' => $uid,
			'type' => $type
		]);

		return $table;
	}

	/**
	 * Optimize images
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function optimize($pathToImage, $uid, $type)
	{
		$serviceKey = $this->config->get('photos.optimizer.key');

		if (!$this->config->get('photos.optimizer.enabled') || !$serviceKey) {
			return false;
		}

		// Get the file info
		$fileInfo = $this->getImageInfo($pathToImage);

		$log = $this->getLog($uid, $type);
		$log->filepath = $pathToImage;
		$log->uid = $uid;
		$log->type = $type;
		$log->created = JFactory::getDate()->toSql();

		// Ensure that the file truly exists
		$exists = JFile::exists($pathToImage);

		if (!$exists) {
			$log->log = new stdClass();
			$log->log->error = 'File does not exists on the site any longer';
			$log->log = json_encode($log->log);
			$log->status = -1;
			$log->store();

			return false;
		}

		$post = array(
			'file' => class_exists('CURLFile', false) ? new CURLFile($pathToImage, $fileInfo['mime']) : "@" . $pathToImage,
			'service_key' => $serviceKey,
			'domain' => rtrim(JURI::root(), '/')
		);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, SOCIAL_OPTIMIZER_SERVER);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		// We need to know the response status
		if ($code == 400) {
			$log->log = $result;
			$log->status = -1;
			$log->store();

			$result = json_decode($result);

			return false;
		}

		$log->status = 1;
		$log->store();

		// Resave the file
		$state = JFile::write($pathToImage, $result);

		return $state;
	}
}
