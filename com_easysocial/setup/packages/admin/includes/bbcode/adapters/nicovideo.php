<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialBBCodeNicoVideo
{
	private function getCode($url)
	{
		preg_match('/nicovideo.jp\/watch\/(.*)/is', $url, $matches);

		if (!empty($matches)) {
			return $matches[1];
		}

		return false;
	}

	/**
     * Retrieves the embed widget for nicovideo.com videos
     *
     * @since   2.1
     * @access  public
     */
	public function getEmbedHTML($url)
	{
		$code = $this->getCode($url);

		if ($code) {
			return '<script src="http://ext.nicovideo.jp/thumb_watch/' . $code . '?w=720&h=405&n=1"></script><noscript>Javascript is required to load the player</noscript>';
		}

		return false;
	}
}
