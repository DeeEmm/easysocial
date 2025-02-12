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

class EasySocialControllerCrawler extends EasySocialController
{
	/**
	 * Validates to see if the remote url really exists
	 *
	 * @since	1.4.8
	 * @access	public
	 */
	public function validate()
	{
		// Get the url
		$url = $this->input->get('url', '', 'default');

		// Get the crawler
		$connector = ES::connector($url);
		$content = $connector
						->execute()
						->getResult();

		$valid = true;

		if (!$content) {
			$valid = false;
		}

		return $this->view->call(__FUNCTION__, $valid);
	}

	/**
	 * Allows caller to pass in one or more urls to scrape
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function fetch()
	{
		// Check for request forgeries!
		$url = $this->input->get('url', '', 'default');

		if (!$url) {
			return $this->view->exception('COM_EASYSOCIAL_CRAWLER_INVALID_URL_PROVIDED');
		}

		// Get the link object
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

		return $this->view->call(__FUNCTION__, $link, $result);
	}
}
