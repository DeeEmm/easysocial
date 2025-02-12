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

class SocialCronHooksPhotos
{
	public function execute(&$states)
	{
		$states[] = $this->cleanupPhotos();
	}

	/**
	 * Cleanup unusued photos size variation that no longer use since 2.0
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function cleanupPhotos()
	{
		$config = ES::config();
		$model = ES::model('Photos');

		$photos = $model->getLegacyPhotos();

		// Removed all of the legacy photos from the site
		if ($photos) {
			$total = 0;

			foreach ($photos as $photo) {
				$meta = ES::table('PhotoMeta');
				$meta->load($photo->id);

				$path = $meta->value;

				// Legacy path might include the root folder in the database
				// so we first check the path directly
				if (JFile::exists($path)) {
					JFile::delete($path);
				} else {

					// Add root path to the relative path
					$path = JPATH_ROOT . '/' . $meta->value;

					if (JFile::exists($path)) {
						JFile::delete($path);
					} else {
						// Normalize the meta for latest path structure
						$photoTable = ES::table('photo');
						$photoTable->load($photo->photo_id);
						$photoTable->normalizeMetaValue($meta);

						// Get the photo storage container
						$container = $config->get('photos.storage.container');
						$container = ES::cleanPath($container);

						$path = JPATH_ROOT . '/' . $container . $meta->value;

						if (JFile::exists($path)) {
							JFile::delete($path);
						}
					}
				}

				// Remove the data regardless if the file exists or not
				$meta->delete();

				$total++;
			}

			return JText::sprintf('COM_ES_CRONJOB_LEGACY_IMAGES_CLEANED', $total);
		}

		return JText::_('COM_ES_CRONJOB_LEGACY_IMAGES_NOTHING_TO_CLEAN');
	}
}
