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

class SocialCrawlerTwitch extends SocialCrawlerAbstract
{
	public function process(&$result)
	{
		// Skip this if that is Twitch blog post
		if (stristr($this->url, 'blog.twitch.tv') !== false) {
			return;
		}

		// Check if the url should be processed here.
		if (stristr($this->url, 'twitch.tv') === false) {
			return;
		}

		// The site must support SSL https
		$uri = JURI::getInstance();
		$scheme = $uri->toString(array('scheme'));
		$protocol = str_replace('://', '', $scheme);

		if ($protocol != 'https') {
			$result->oembedError = JText::_('COM_ES_VIDEO_TWITCH_CRAWL_ERROR_SITE_NOT_SUPPORT_SSL');
			return $result;
		}

		$config = ES::config();
		$clientKey = trim($config->get('video.twitch.clientId'));
		$clientSecret = trim($config->get('video.twitch.clientSecret'));

		$table = ES::table('OAuth');
		$table->load(array('client' => 'twitch'));

		// Skip this if doesn't have authenticate token with the site owner Twitch account
		if (!$table->token) {
			$result->oembedError = JText::_('COM_ES_VIDEO_TWITCH_CRAWL_ERROR');
			return $result;
		}

		// trim the last forward slash
		$this->url = rtrim($this->url, '/');

		// Generate the oembed url
		$oembed = $this->crawl($this->url, $clientKey, $table->token);

		if (!$oembed) {
			$result->oembedError = JText::_('COM_ES_VIDEO_TWITCH_NOT_FOUND');
			return $result;
		}

		// Currently Twitch video do not have render oembed data at all
		// Require to manually embed the video content
		$oembedData = isset($oembed->data[0]) && $oembed->data[0] ? $oembed->data[0] : '';
		$data = "";

		// Determine the video type here
		$isTwitchVideoType = $this->isTwitchVideoType($this->url);
		$isTwitchClipsType = $this->isTwitchClipsType($this->url);

		if ($oembedData) {

			if ($isTwitchVideoType) {

				$data = $this->embedVideoProcess($result, $oembedData);

			} elseif ($isTwitchClipsType) {

				$data = $this->embedClipsProcess($result, $oembedData);

			} else {

				$data = $this->embedLiveChannelVideoProcess($result, $oembedData);
			}
		}

		if (!$data) {
			$result->oembedError = JText::_('COM_ES_VIDEO_TWITCH_NOT_FOUND');
			return $result;
		}

		return $data;
	}

	/**
	 * Process Twitch video information
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function embedVideoProcess($result, $oembedDataFromTwitch)
	{
		$config = ES::config();

		// Get the video width and height
		$imageHeight = $config->get('video.twitch.thumbnailSize');
		$imageWidth = '1920';

		if ($imageHeight == '720') {
			$imageWidth = '1280';
		}

		// oembed data
		$embedCodes = $this->generateVideoEmbed($this->url);

		$result->oembed->type = 'embed';
		$result->oembed->html = $embedCodes;

		// Retrieve the thumbnail image from the video
		if (isset($oembedDataFromTwitch->thumbnail_url) && $oembedDataFromTwitch->thumbnail_url) {

			$thumbnail = $oembedDataFromTwitch->thumbnail_url;

			$thumbnailSize = $imageWidth . 'x' . $imageHeight;

			// set the default width and height for this thumbnail image
			$thumbnail = str_replace("%{width}x%{height}", $thumbnailSize, $thumbnail);

			$result->oembed->thumbnail = $thumbnail;
			$result->opengraph->image = $thumbnail;
		}

		// Retrieve the duration from the video
		if (isset($oembedDataFromTwitch->duration) && $oembedDataFromTwitch->duration) {

			// calculate for the duration timestamp if there got duration data
			$durationTimeStamp = $this->getDuration($oembedDataFromTwitch->duration);

			$result->oembed->duration = $durationTimeStamp;
		}

		// Meta data and Opengraph data
		if (isset($oembedDataFromTwitch->title) && $oembedDataFromTwitch->title) {
			$result->opengraph->title = $oembedDataFromTwitch->title;
		}

		if (isset($oembedDataFromTwitch->description) && $oembedDataFromTwitch->description) {
			$result->opengraph->description = $oembedDataFromTwitch->description;
			$result->opengraph->desc = $oembedDataFromTwitch->description;
		}

		return $result;
	}

	/**
	 * Process Twitch clips information
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function embedClipsProcess($result, $oembedDataFromTwitch)
	{
		// oembed data
		$embedCodes = $this->generateVideoEmbed($this->url);

		$result->oembed->type = 'embed';
		$result->oembed->html = $embedCodes;

		// Retrieve the thumbnail image from the video
		if (isset($oembedDataFromTwitch->thumbnail_url) && $oembedDataFromTwitch->thumbnail_url) {
			$result->oembed->thumbnail = $oembedDataFromTwitch->thumbnail_url;
			$result->opengraph->image = $oembedDataFromTwitch->thumbnail_url;
		}

		// Meta data and Opengraph data
		if (isset($oembedDataFromTwitch->title) && $oembedDataFromTwitch->title) {
			$result->opengraph->title = $oembedDataFromTwitch->title;
		}

		if (isset($oembedDataFromTwitch->creator_name) && $oembedDataFromTwitch->creator_name) {
			$result->opengraph->description = JText::_('COM_ES_VIDEO_TWITCH_CLIPPED_BY') . $oembedDataFromTwitch->creator_name;
			$result->opengraph->desc = JText::_('COM_ES_VIDEO_TWITCH_CLIPPED_BY') . $oembedDataFromTwitch->creator_name;
		}

		return $result;
	}

	/**
	 * Process Twitch live video information
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function embedLiveChannelVideoProcess($result, $oembedDataFromTwitch)
	{
		// Reads this URL html content
		$htmlContent = @file_get_contents($this->url);

		// Retrieve the opengraph data
		$ogContent = $this->getOpengraphData($htmlContent);

		// Manually generate embed codes.
		$embedCodes = $this->generateVideoEmbed($this->url);

		// assign the data into oembed property
		$result->oembed->type = 'embed';
		$result->oembed->html = $embedCodes;

		if (isset($oembedDataFromTwitch->display_name) && $oembedDataFromTwitch->display_name) {
			$result->title = $oembedDataFromTwitch->display_name;
			$result->opengraph->title = $oembedDataFromTwitch->display_name;
		}

		if (isset($oembedDataFromTwitch->description) && $oembedDataFromTwitch->description) {
			$result->description = $oembedDataFromTwitch->description;
			$result->opengraph->description = $oembedDataFromTwitch->description;
			$result->opengraph->desc = $oembedDataFromTwitch->description;
		}

		// Retrieve the thumbnail image from the user profile image
		if (isset($oembedDataFromTwitch->profile_image_url) && $oembedDataFromTwitch->profile_image_url) {
			$result->oembed->thumbnail = $oembedDataFromTwitch->profile_image_url;
			$result->opengraph->image = $oembedDataFromTwitch->profile_image_url;
		}

		if (isset($ogContent->video_duration)) {
			$result->oembed->duration = $ogContent->video_duration;
		}

		return $result;
	}

	/**
	 * Retrieve the last segment from the Twitch URL
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function getLastSegmentName($url)
	{
		$url = explode('/', $url);

		// Retrieve the channel username
		$lastElement = end($url);

		return $lastElement;
	}

	/**
	 * Determine Twitch video type from the URL
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function isTwitchVideoType($url)
	{
		// Twitch video default segments
		$videoSegments = "twitch.tv/videos";

		// Determine if this is "video" type
		if (strpos($url, $videoSegments) !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Determine Twitch Clips type from the URL
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function isTwitchClipsType($url)
	{
		// Twitch video default segments
		$videoSegments = "clips.twitch.tv";

		// Determine if this is "clips" type
		if (strpos($url, $videoSegments) !== false) {
			return true;
		}

		// Need to check there got another clips URL format
		// e.g. https://www.twitch.tv/trainwreckstv/clip/WildCulturedWaterCopyThis
		$segments = explode('/', $url);

		if (in_array('clip', $segments)) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve the data from the video
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function crawl($url, $clientKey, $accessToken)
	{
		// Need to ensure the URL is always twitch.tv
		$url = str_ireplace('go.twitch.tv', 'twitch.tv', $url);

		// Retrieve the URL last segment name
		$lastElement = $this->getLastSegmentName($url);

		// Determine the video type here
		$isTwitchVideoType = $this->isTwitchVideoType($url);
		$isTwitchClipsType = $this->isTwitchClipsType($url);

		// This is endpoint for retrieve the channel user video
		$endpoint = 'https://api.twitch.tv/helix/users?login=' . $lastElement;

		if ($isTwitchVideoType) {
			$endpoint = 'https://api.twitch.tv/helix/videos?id=' . $lastElement;
		}

		if ($isTwitchClipsType) {
			$endpoint = 'https://api.twitch.tv/helix/clips?id=' . $lastElement;
		}

		// Construct headers
		$header = array();
		$header[] = 'Client-ID: ' . $clientKey;
		$header[] = 'Authorization: Bearer ' . $accessToken;

		$ch = curl_init();

		// Construct curl options
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$oembed = curl_exec($ch);
		$oembed = json_decode($oembed);

		// either empty data or status = 400 then consider dont have oembed data
		// https://www.twitch.tv/videos/556990041 <- video type
		// https://www.twitch.tv/esl_csgo <- channel type
		// https://clips.twitch.tv/IncredulousLachrymosePeachNotATK <- clips type

		if ((isset($oembed->status) && $oembed->status == 400) || (isset($oembed->data) && !$oembed->data)) {
			$oembed = false;
		}

		return $oembed;
	}

	/**
	 * Since some of the Twitch video doesn't have the consistence data, we need to extract these data ourselves
	 *
	 * @since	2.1.8
	 * @access	public
	 */
	private function generateVideoEmbed($url)
	{
		// Determine the video type here
		$isTwitchVideoType = $this->isTwitchVideoType($url);
		$isTwitchClipsType = $this->isTwitchClipsType($url);

		// breaks the URL into an array
		$url = explode('/', $url);

		// Retrieve the last element value either channel name or video id
		$key = array_pop($url);

		// determine for the video embed format
		if ($isTwitchVideoType) {
			$parts = 'https://player.twitch.tv/?video=v' . $key;

		} elseif ($isTwitchClipsType) {
			$parts = 'https://clips.twitch.tv/embed?clip=' . $key;

		} else {
			$parts = 'https://player.twitch.tv/?channel=' . $key;
		}

		$output = '<iframe src="' . $parts . '&autoplay=false" width="100%" height="480" frameborder="0" allowfullscreen></iframe>';

		return $output;
	}

	/**
	 * Convert video duration to seconds.
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function getDuration($duration)
	{
		if (!$duration) {
			return false;
		}

		// Match the duration format e.g. 1h10m10s
		$pattern = '/(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?$/';

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

		$durationTimeStamp = (int) $seconds;

		return $durationTimeStamp;
	}
}
