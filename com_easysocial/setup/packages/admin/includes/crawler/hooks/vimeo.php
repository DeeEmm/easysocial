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

class SocialCrawlerVimeo extends SocialCrawlerAbstract
{
	public $error = null;

	public function process(&$result)
	{
		// Check if the url should be processed here.
		if (stristr($this->url, 'vimeo.com') === false) {
			return;
		}

		$oembed = $this->getOembed();

		if (!$oembed) {
			$result->oembedError = $this->error;
			return $result;
		}

		$result->oembed = $oembed;
		$result->url = $this->url;

		return $result;
	}

	public function getOembed()
	{
		$encodedURL = urlencode($this->url);
		$serviceUrl = 'https://vimeo.com/api/oembed.json?url=' . $encodedURL;

		$connector = ES::connector($serviceUrl);
		$contents = $connector
						->execute()
						->getResult();

		$object = json_decode($contents);

		// if the status code return you 403 mean this video only embed on the specific domain site
		if (isset($object->domain_status_code) && ($object->domain_status_code == 403)) {

			// Retrieve the current domain site URL
			$domain = JURI::root();

			$connector = ES::connector($serviceUrl);
			$contents = $connector
							->addReferrer($domain)
							->execute()
							->getResult();

			$object = json_decode($contents);
		}

		if (!$object || (isset($object->domain_status_code) && $object->domain_status_code != 200)) {

			// error status code described at https://developer.vimeo.com/api/oembed/videos#embedding-videos-with-domain-privacy
			// just render this default vimeo error message if the response return empty and domin status code is 403
			$this->error = JText::_('COM_ES_VIDEO_VIMEO_OEMBED_ERROR_DOMAIN_PRIVACY');

			if (isset($object->domain_status_code) && ($object->domain_status_code == 404)) {
				$this->error = JText::_('COM_ES_VIDEO_VIMEO_OEMBED_ERROR_ACCESS_PERMISSION');
			}

			return false;
		}

		if (isset($object->thumbnail_url)) {
			$object->thumbnail = $object->thumbnail_url;
		}

		return $object;
	}
}
