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

class SocialCrawlerPastebin extends SocialCrawlerAbstract
{
	public function process(&$result)
	{
		// Check if the url should be processed here.
		if (stristr($this->url, 'pastebin.com') === false) {
			return;
		}

		$oembed = $this->getOembed();

		if (!$oembed) {
			return;
		}

		$segment = str_ireplace('http://pastebin.com/', '', $this->url);

		$oembed->html = '<iframe src="http://pastebin.com/embed_iframe.php?i=' . $segment . '" style="border:none;width:100%"></iframe>';

		$result->oembed = $oembed;
	}
}
