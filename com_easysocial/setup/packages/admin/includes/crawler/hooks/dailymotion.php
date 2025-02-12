<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialCrawlerDailymotion extends SocialCrawlerAbstract
{
	public function process(&$result)
	{
		// Check if the url should be processed here.
		if (stristr($this->url, 'dailymotion.com') === false && stristr($this->url, 'dai.ly') === false) {
			return;
		}

		$oembed = $this->getOembed();

		if (!$oembed) {
			$result->oembedError = $this->error;
			return $result;
		}

		// Fix http url in https issue
		$oembed = $this->fixOembedUrl($oembed);

		// If we can't get any oembed data from youtube, we will then simulate this.
		if (!$oembed) {
			return;
		}

		// Try to get the duration from the contents
		$duration = $this->getDuration();

		if ($duration) {
			$oembed->duration = $this->getDuration();
		}

		$result->oembed = $oembed;
	}

	public function getOembed()
	{
		$serviceUrl = 'http://www.dailymotion.com/services/oembed?url=' . $this->url;

		$connector = ES::connector($serviceUrl);
		$contents = $connector
						->execute()
						->getResult();

		$object = json_decode($contents);

		if (!$object || is_null($object)) {
			$this->error = JText::_('COM_EASYSOCIAL_VIDEO_LINK_EMBED_NOT_SUPPORTED');
			return false;
		}

		if (isset($object->thumbnail_url)) {
			$object->thumbnail = $object->thumbnail_url;
		}

		$object->isWordpress = false;

		return $object;
	}

	public function getDuration()
	{
		// Get the video id
		$pattern = '/\/video\/(.*)/is';
		preg_match_all($pattern, $this->url, $matches);

		$parts = explode('_', $matches[1][0]);

		$videoId = $parts[0];

		$url = 'https://api.dailymotion.com/video/' . $videoId . '?fields=duration';

		$connector = ES::connector($url);
		$contents = $connector
						->execute()
						->getResult();

		$obj = json_decode($contents);

		$duration = '';

		if (isset($obj->duration) && $obj->duration) {
			$duration = (int) $obj->duration;
		}

		return $duration;
	}
}
