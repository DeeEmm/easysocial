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

require_once(__DIR__ . '/abstract.php');

class SocialCrawlerVK extends SocialCrawlerAbstract
{
	public $oembed = null;

	public function process(&$result)
	{
		// Check if the url should be processed here.
		if (stristr($this->url, 'vk.com') === false) {
			return;
		}

		// If the vk video link copied from mobile
		// We need to convert the video link to the web video link
		if (stristr($this->url, 'm.vk.com') !== false) {
			$this->url = str_replace('m.vk.com', 'vk.com', $this->url);
		}

		// Correct video url: https://vk.com/video-353324_456239667 . It should not contain ? in the query string
		if (stristr($this->url, '?') !== false) {

			// Reconstruct the correct url
			$url = $this->getVideoUrl();
			$contents = $this->scrape($url);
			$result->opengraph = $this->getOpengraphData($contents);
		}

		if (stristr($this->url, '?') === false) {
			$contents = $this->scrape($this->url);

			$result->opengraph = $this->getOpengraphData($contents);
		}

		if (!isset($result->opengraph->video) || !$result->opengraph->video) {
			return;
		}

		// Generate embed codes. Because live videos cannot be embedded, we need to take care of this
		$embedCodes = $this->generateVideoEmbed($result->opengraph->video);

		if ($embedCodes !== false) {
			$result->title = $result->opengraph->title;
			$result->oembed->type = 'embed';
			$result->oembed->thumbnail = $result->opengraph->image;
			$result->oembed->html = $embedCodes;

			if (isset($result->opengraph->video_duration)) {
				$result->oembed->duration = $result->opengraph->video_duration;
			}
		}
	}

	/**
	 * Crawls the site to obtain the video contents
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	protected function scrape($url)
	{
		$connector = ES::connector($url);
		$contents = $connector
						->setUserAgent('facebookexternalhit/1.1')
						->execute()
						->getResult();

		// Vk.com uses windows-1251 specific character encoding, we need to convert the text accordingly
		$contents = mb_convert_encoding($contents, "utf-8", "windows-1251");

		return $contents;
	}

	/**
	 * Generate the correct video url
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	protected function getVideoUrl()
	{
		$parts = explode('video-', $this->url);

		if (isset($parts[1])) {
			$parts = explode('%2F', $parts[1]);
			return 'https://vk.com/video-' . $parts[0];
		}

		$parts = explode('video', $this->url);
		$parts = explode('%2F', $parts[1]);
		return 'https://vk.com/video' . $parts[0];
	}

	/**
	 * Because vk.com does not have an API, we need to extract these data ourselves
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	private function generateVideoEmbed($video)
	{
		$video = str_replace('&amp;', '&', $video);
		$tmp = explode('&', $video);

		// Without the embed hash, vk.com wouldn't allow embedding
		if (count($tmp) == 1) {
			return false;
		}

		$oid = explode('=', $tmp[0]);
		$oid = $oid[1];

		$id = explode('=', $tmp[1]);
		$id = $id[1];

		$hash = explode('=', $tmp[2]);
		$hash = $hash[1];

		$output = '<iframe src="https://vk.com/video_ext.php?oid=' . $oid . '&id=' . $id . '&hash=' . $hash . '&hd=2" width="853" height="480" frameborder="0" allowfullscreen></iframe>';

		return $output;
	}
}
