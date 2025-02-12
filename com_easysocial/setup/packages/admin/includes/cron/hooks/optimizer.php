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

class SocialCronHooksOptimizer extends EasySocial
{
	public function execute(&$states)
	{
		if (!$this->config->get('photos.optimizer.enabled') || !$this->config->get('photos.optimizer.key') || !$this->config->get('photos.optimizer.cron')) {
			return;
		}

		// Process photos
		$states[] = $this->processPhotos();

		// Process files
		$states[] = $this->processFiles();
	}

	/**
	 * Optimizes image photos from #__social_photos table
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function processPhotos()
	{
		$model = ES::model('Optimizer');

		// Get a list of images that are not processed by yet
		$images = $model->getPhotosToOptimize();

		// Nothing to process
		if (!$images) {
			return;
		}

		$optimizer = ES::imageoptimizer();
		$container = ES::cleanPath($this->config->get('photos.storage.container'));

		foreach ($images as $image) {
			// This is to fix legacy image values by ensuring that the container value isn't stored in the value of the meta table
			$file = str_ireplace('/' . $container, '', $image->value);

			$absolutePath = JPATH_ROOT . '/' . $container . $file;

			$optimizer->optimize($absolutePath, $image->id, SOCIAL_TYPE_PHOTO);
		}
	}

	/**
	 * Optimizes image files from #__social_files table
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function processFiles()
	{
		$model = ES::model('Optimizer');

		// Get a list of images that are not processed by yet
		$images = $model->getFilesToOptimize();

		// Nothing to process
		if (!$images) {
			return;
		}

		$optimizer = ES::imageoptimizer();

		foreach ($images as $image) {
			$table = ES::table('File');
			$table->bind($image);

			$container = $table->getStorageContainer();

			$absolutePath = $container . '/' . $table->uid . '/' . $table->hash;

			$state = $optimizer->optimize($absolutePath, $table->id, SOCIAL_TYPE_FILES);

			// Update the filesize
			if ($state) {
				$table->size = filesize($absolutePath);
				$table->store();
			}
		}
	}
}
