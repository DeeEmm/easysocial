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

class SocialCrawlerSoundcloud extends SocialCrawlerAbstract
{
	public function process(&$result)
	{
		// Check if the url should be processed here.
		if (stristr($this->url, 'soundcloud.com') === false) {
			return;
		}

		$url = 'https://soundcloud.com/oembed?format=json&url=' . urlencode($this->url);

		$connector = ES::connector($url);
		$contents = $connector
						->execute()
						->getResult();

		$oembed = json_decode($contents);

		// Test if thumbnail_url is set so we can standardize this
		if (isset($oembed->thumbnail_url)) {
			$oembed->thumbnail = $oembed->thumbnail_url;
		}

		$result->oembed = $oembed;
	}
}
