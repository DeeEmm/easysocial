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

require_once(__DIR__ . '/abstract.php');

class SocialCrawlerApple extends SocialCrawlerAbstract
{
	public function process(&$result)
	{
		if (stristr($this->url, 'podcasts.apple.com') === false) {
			return;
		}

		$images = $this->parser->find('meta[property=og:image]');
		$oembed = $this->simulateOembed($this->url);

		foreach ($images as $meta) {

			if (!$meta->content) {
				continue;
			}

			$url = $meta->content;

			if (stristr($url, 'http://') === false && stristr($url, 'https://') === false) {
				$url = 'http://' . $url;
			}

			$image = $url;

			$oembed->thumbnail = $image;
			$oembed->thumbnail_url = $image;
		}

		$result->oembed = $oembed;
	}

	/**
	 * Simulate oembed data
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function simulateOembed($link)
	{
		$oembed = new stdClass();
		$url = str_ireplace('https://podcasts.apple.com/', 'https://embed.podcasts.apple.com/', $link);

		$parsed = parse_url($url, PHP_URL_QUERY);
		$wrapperClass = 'is-podcasts-list';

		if ($parsed) {
			parse_str($parsed, $fragments);

			if (isset($fragments['i'])) {
				$wrapperClass = 'is-podcasts-single';
			}
		}

		$oembed->height = 270;
		$oembed->width = 480;
		$oembed->wrapperClass = $wrapperClass;
		$oembed->html = '<iframe src="' . $url . '" height="450px" frameborder="0" sandbox="allow-forms allow-popups allow-same-origin allow-scripts allow-top-navigation-by-user-activation" allow="autoplay *; encrypted-media *;"></iframe>';
		$oembed->thumbnail = '';
		$oembed->thumbnail_url = '';

		return $oembed;
	}
}
