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

ES::import('admin:/tables/table');

class SocialTableUrls extends SocialTable
{
	public $id = null;
	public $sefurl = null;
	public $rawurl = null;
	public $params = null;
	public $custom = null;

	public function __construct($db)
	{
		parent::__construct('#__social_urls', 'id', $db);
	}

	/**
	 * Update single entry in cache file.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function updateCacheEntry($oriSef)
	{
		$config = ES::config();

		if ($config->get('seo.cachefile.enabled')) {

			$cache = ES::fileCache();
			$filepath = $cache->getFilePath();

			$newSef = $this->sefurl;

			if (! $newSef) {
				return false;
			}

			$cache->updateCacheItem($newSef, $oriSef, $this);
		}

		return true;
	}

}
