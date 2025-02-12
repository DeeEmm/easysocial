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

class SocialCrawlerYoutube extends SocialCrawlerAbstract
{
	public $oembed = null;

	public function process(&$result)
	{
		// Check if the url should be processed here.
		if (stristr($this->url, 'youtube.com') === false || strstr($this->url, 'results?search_query') || strstr($this->url, 'playlist?list=')) {
			return;
		}

		// Check if the URL contain 'live' parameter query string
		// Then we need to throw a warning to tell the user need to copy the link from the live video share
		$tmpUrl = explode('/', $this->url);
    	$lastPart = array_pop($tmpUrl);

    	if ($lastPart === 'live') {
			$result->oembedError = JText::_('COM_ES_YOUTUBE_VIDEO_LINK_EMBED_ERROR');
			return $result;
    	}

		parse_str(parse_url($this->url, PHP_URL_QUERY), $data);

		if (!$data) {
			return;
		}

		$oembed = $this->getOembed();

		if (!$oembed) {
			$result->oembedError = $this->error;
			return $result;
		}

		$result->oembed = $this->oembed;
		$result->url = $this->url;

		return $result;
	}

	public function getOembed()
	{
		// Process youtube API
		$state = $this->youtubeAPI();

		// Only process this if the site doesn't have YouTube API key and it doesn't have any error log into $this->error
		if (!$state && !$this->error) {

			$encodedURL = urlencode($this->url);
			$serviceUrl = 'https://www.youtube.com/oembed/?format=json&url=' . $encodedURL;

			$connector = ES::connector($serviceUrl);
			$contents = $connector
							->execute()
							->getResult();

			$object = json_decode($contents);

			if (!$object || is_null($object)) {
				$this->error = JText::_('COM_EASYSOCIAL_VIDEO_LINK_EMBED_NOT_SUPPORTED');
				return false;
			}

			$this->simulateOembed($object);
			$this->getThumbnail();

			// Since we no longer crawl the YouTube video page directly to get the video data (because YouTube will block the website if keep crawl their video page directly)
			// so we can't retrieve the video duration time now. (for those user who want to get the duration, they need to use YouTube API key)

			// Try to get the duration from the contents
			// 	$this->getDuration();
		}

		return true;
	}

	/**
	 * Normalize the youTube video id
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	public function getVideoId()
	{
		// some of the youtube link contain & instead of ? so we need to replace it from here
		// $videoURL = str_replace('&t=', '?t=', $this->url);
		$videoURL = $this->url;

		$startTime = '';

		// playlist
		if (strpos($videoURL, '&list=') !== false) {

			// check for the URL whether have contain video start time
			$url = explode('&list=', $videoURL);
			$playlist = '';

			// retrieve the video id
			parse_str(parse_url($url[0], PHP_URL_QUERY), $videoId);

			$videoId = $videoId['v'];

			// Ensure that url have start time URL query string
			if (isset($url[1]) && $url[1]) {
				$playlist = '?list=' . $url[1];
			}

			$normalizedVideoQueryString = $playlist;

		} else {

			// check for the URL whether have start time or without any extra parameter
			$url = explode('&t=', $videoURL);

			// retrieve the video id
			parse_str(parse_url($url[0], PHP_URL_QUERY), $videoId);

			$videoId = $videoId['v'];

			// Ensure that url have start time URL query string
			if (isset($url[1]) && $url[1]) {
				$startTime = '?start=' . $url[1];
			}

			$normalizedVideoQueryString = "?feature=oembed";

			// Ensure that only add this if the URL contain the video start time
			if ($startTime) {
				$normalizedVideoQueryString = $startTime . "&feature=oembed";
			}
		}

		$videoObj = new stdClass();
		$videoObj->id = $videoId;
		$videoObj->parameter = $normalizedVideoQueryString;

		return $videoObj;
	}

	/**
	 * Process youtube via API v3
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function youtubeAPI()
	{
		$config = ES::config();
		$key = trim($config->get('youtube.api.key'));
		$enabled = $config->get('youtube.api.enabled');

		if (!$enabled || !$key) {
			return false;
		}

		$videoObj = $this->getVideoId();

		$parts = "&fields=items(id,snippet(title,description,thumbnails(standard)),contentDetails(duration))&part=snippet,contentDetails";

		// Connect to youtube api.
		$url = "https://www.googleapis.com/youtube/v3/videos?id=". $videoObj->id ."&key=" . $key . $parts;

		$connector = ES::connector($url);
		$contents = $connector
						->setReferer(JURI::root())
						->execute()
						->getResult();

		$obj = json_decode($contents);

		// If connection failed, return to default oembed value.
		if (!$obj || (isset($obj->items) && !$obj->items)) {
			$this->error = JText::_('COM_EASYSOCIAL_VIDEO_LINK_EMBED_NOT_SUPPORTED');
			return false;
		}

		// There are some errors when trying to validate the key
		if (isset($obj->error)) {

			// // Debug
			// dump($obj->error);

			return false;
		}

		$oembed = new stdClass();

		// Assign oembed data
		foreach ($obj->items as $item) {
			$oembed->html = '<iframe width="480" height="270" src="https://www.youtube.com/embed/'. $item->id . $videoObj->parameter . '" frameborder="0" allowfullscreen></iframe>';
			$oembed->width = 480;
			$oembed->height = 270;

			$snippet = isset($item->snippet) ? $item->snippet : null;

			// bind the video snippet
			if ($snippet) {
				$oembed->title = $snippet->title;
				$oembed->description = $snippet->description;
				$oembed->thumbnail = 'https://img.youtube.com/vi/' . $videoObj->id . '/hqdefault.jpg';
				$oembed->thumbnail_url = 'https://img.youtube.com/vi/' . $videoObj->id . '/hqdefault.jpg';

				$thumbnails = isset($snippet->thumbnails) && isset($snippet->thumbnails->standard) ? $snippet->thumbnails : null;

				// Use the provided thumbnails if exists.
				if ($thumbnails) {
					$oembed->thumbnail = $thumbnails->standard->url;
					$oembed->thumbnail_url = $thumbnails->standard->url;
				}
			}

			// Get duration
			$oembed->duration = $item->contentDetails->duration;
		}

		$this->oembed = $oembed;

		// Format the duration
		$this->getDuration();

		return true;
	}

	/**
	 * Simulate oembed data
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function simulateOembed($content = null)
	{
		$videoObj = $this->getVideoId();

		$oembed = new stdClass();

		if ($content) {

			if (isset($content->title) && $content->title) {
				$oembed->title = $content->title;
			}

			// It sems like now the YouTube oembed data no longer show description, in order to retrieve the description from the video
			// You need to setup YouTube API key since we no longer crawl the YouTube video page directly to avoid blocked from YouTube side.
			if (isset($content->description) && $content->description) {
				$oembed->description = $content->description;
			}

			// if the oembed doesn't have description then we need to add this description key into this oembed variable for prevent the stream preview error.
			if (!isset($oembed->description)) {
				$oembed->description = '';
			}
		}

		// Hard code the neccessary value.
		$oembed->height = 270;
		$oembed->width = 480;
		$oembed->html = '<iframe width="480" height="270" src="https://www.youtube.com/embed/'. $videoObj->id . $videoObj->parameter . '" frameborder="0" allowfullscreen></iframe>';
		$oembed->thumbnail = 'https://img.youtube.com/vi/'. $videoObj->id .'/sddefault.jpg';
		$oembed->thumbnail_url = 'https://img.youtube.com/vi/'. $videoObj->id .'/sddefault.jpg';

		$this->oembed = $oembed;
	}

	/**
	 * Get video thumbnails
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getThumbnail()
	{
		// We want to get the HD version of the thumbnail
		$thumbnail = str_ireplace('sddefault.jpg', 'hqdefault.jpg', $this->oembed->thumbnail);

		// Try to get the sd details
		$connector = ES::connector($thumbnail);

		try {
			$headers = $connector->useHeadersOnly()
							->execute()
							->getResult();
		} catch (Exception $e) {

			// Just use the default and do not modify anything
			return;
		}

		$this->oembed->thumbnail = $thumbnail;
		$this->oembed->thumbnail_url = $thumbnail;
	}

	/**
	 * Convert video duration from  ISO 8601 format to seconds.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getDuration()
	{
		$duration = false;

		// Get the duration
		if (isset($this->oembed->duration) && $this->oembed->duration) {
			$duration = $this->oembed->duration;
		} else {
			$node = $this->parser->find('[itemprop=duration]');

			if ($node) {
				$node = $node[0];
				$duration = $node->attr['content'];
			}
		}

		if (!$duration) {
			$this->oembed->duration = 0;
			return;
		}

		// Match the duration
		$pattern = '/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/';
		preg_match_all($pattern, $duration, $matches);

		$seconds = 0;

		// Get the hour
		if (isset($matches[1]) && $matches[1]) {
			if ($matches[1][0] === "") {
				$matches[1][0] = 0;
			}

			$seconds = $matches[1][0] * 60 * 60;
		}

		// Minutes
		if (isset($matches[2]) && $matches[2]) {
			if ($matches[2][0] === "") {
				$matches[2][0] = 0;
			}

			$seconds = $seconds + ($matches[2][0] * 60);
		}

		// Seconds
		if (isset($matches[3]) && $matches[3]) {
			if ($matches[3][0] === "") {
				$matches[3][0] = 0;
			}

			$seconds = $seconds + $matches[3][0];
		}

		$this->oembed->duration = (int) $seconds;
	}
}
