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

class SocialSharer extends EasySocial
{
	/**
	 * Crawls a link and retrieves meta data about the link
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function crawl($url)
	{
		// Crawl the link
		$link = ES::links($url);
		$result = false;

		// Determines if we should be caching the links data
		$cache = $this->config->get('links.cache.data', true);

		if (!$link->hasCache() || !$cache) {

			// Get the crawler
			$crawler = ES::crawler();
			$result = $crawler->scrape($url);

			// Now we need to cache the link so that the next time, we don't crawl it again.
			if ($cache) {
				$link->cache($result);
			}
		}

		if (!$result) {
			$result = $link->getData();
		}

		// if still canot get anything, lets return false
		if (!$result) {
			return false;
		}

		$meta = new stdClass();
		$meta->title = $result->title;
		$meta->desc = $result->description;
		$meta->url = $result->url;
		$meta->image = $this->getPlaceholderImage();

		$this->getOpengraphData($meta, $result);

		// Ensure description is not too excessively long
		if ($meta && $meta->desc) {
			$length = ESJString::strlen($meta->desc);

			if ($length > 200) {
				$meta->desc = ESJString::substr($meta->desc, 0, 200) . JText::_('COM_EASYSOCIAL_ELLIPSES');
			}
		}

		return $meta;
	}

	/**
	 * Retrieves opengraph data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getOpengraphData(&$meta, $result)
	{
		if (isset($result->opengraph->title) && !empty($result->opengraph->title)) {
			$meta->title = $result->opengraph->title;
		}

		if (isset($result->opengraph->image) && !empty($result->opengraph->image)) {
			$meta->image = $result->opengraph->image;
		}

		if (isset($result->opengraph->desc) && !empty($result->opengraph->desc)) {
			$meta->desc = $result->opengraph->desc;
		}
	}

	/**
	 * Retrieves opengraph data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPlaceholderImage()
	{
		$url = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/sharer-placeholder.png';

		return $url;
	}

	/**
	 * Retrieves the default logo for sharer
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getDefaultLogo()
	{
		static $logo = null;

		if (is_null($logo)) {
			$logo = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/mobileicon.png';
		}

		return $logo;
	}

	/**
	 * Retrieves the logo that should be used in the embed button
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getLogo()
	{
		static $logo = null;

		if (is_null($logo)) {
			// Default to fallback to the mobileicon
			$logo = $this->getDefaultLogo();

			$hasOverride = ES::hasOverride('sharer_logo');

			if ($hasOverride) {
				$logo = rtrim(JURI::root(), '/') . '/images/easysocial_override/sharer_logo.png';
			}
		}

		return $logo;
	}
}
