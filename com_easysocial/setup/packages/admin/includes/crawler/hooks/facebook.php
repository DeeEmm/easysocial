<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialCrawlerFacebook extends SocialCrawlerAbstract
{
	public $oembed = null;

	public function process(&$result)
	{

		if ((stristr($this->url, 'facebook.com') === false && stristr($this->url, 'fb.watch') === false) || !isset($result->oembed)) {
			return;
		}

		// For facebook page, we want to remove the timeline
		if (isset($result->oembed->html) && stristr($result->oembed->html, 'fb-page') !== false) {
			// Remove the timeline from the embed widget
			$result->oembed->html = str_ireplace('data-show-posts="1"', '', $result->oembed->html);

			// Set a max width of 500
			$result->oembed->html = str_ireplace('class="fb-page"', 'class="fb-page" data-width="500"', $result->oembed->html);
			$result->oembed->width = 500;
			$result->oembed->type = 'embed';
		}

		// It is unfortunate that facebook doesn't implement oembed properly / correctly
		if (stristr($this->url, 'facebook.com') && stristr($this->url, 'videos') === false && stristr($this->url, 'watch') === false) {
			return;
		}

		// let check if there is the height attr or not.
		if ((property_exists($result->oembed, 'height') && !$result->oembed->height) || !isset($result->oembed->height)) {

			$oriUrl = rtrim($this->url, '/');

			$segments = explode('/', $oriUrl);
			$videoId = array_pop($segments);
			// $url = 'https://www.facebook.com/video.php?v=' . $videoId;

			// we need to use this plugin url so that the video content is not being populate by javascript. #582
			$url = 'https://www.facebook.com/plugins/video.php?href=' . urlencode($this->url) . '&show_text=false&appId=589470227789490';
			$graphApiUrl = 'https://graph.facebook.com/' . $videoId;

			$connector = ES::connector($url);
			$mainContents = $connector
								->setUserAgent('facebookexternalhit/1.1')
								->execute()
								->getResult();

			if ($mainContents) {
				// let to get from the video tag inside the html attr.
				$pattern = '/\<video+.*data-video-width="([\d]+)"+.*data-video-height="([\d]+)"+.*\>\<\/video\>/i';

				preg_match_all($pattern, $mainContents, $matches);

				if ($matches && isset($matches[1]) && isset($matches[2]) && $matches[1] && $matches[2]) {
					$width = $matches[1][0];
					$height = $matches[2][0];

					$result->oembed->width = $width;
					$result->oembed->height = $height;
				}
			}


			// Crawl the image now.
			$connector = ES::connector($graphApiUrl);
			$contents = $connector
							->execute()
							->getResult();

			$contents = json_decode($contents);

			if (isset($contents->picture) && $contents->picture) {
				$result->oembed->thumbnail_url = $contents->picture;
			} else {
				// lets try getting from the main content.
				if ($mainContents) {
					$pattern = '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/i';

					preg_match($pattern, $mainContents, $matches);

					if ($matches && isset($matches[1])) {
						$thumbnail = $matches[1];
						$result->oembed->thumbnail_url = str_replace('&amp;', '&', $thumbnail);
					}
				}
			}

			// display proper video image if available #3256
			if (isset($result->oembed->thumbnail_url) && $result->oembed->thumbnail_url) {
				$result->oembed->images = array($result->oembed->thumbnail_url);
				$result->images = array($result->oembed->thumbnail_url);
			}
		}

		$video = urlencode($this->url);

		$result->description = html_entity_decode($result->description);
		$result->oembed->html = '<iframe src="https://www.facebook.com/plugins/video.php?href=' . $video . '&show_text=false&appId=589470227789490" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>';

	}
}
