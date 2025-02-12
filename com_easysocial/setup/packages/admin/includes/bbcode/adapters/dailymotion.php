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

class SocialBBCodeDailyMotion
{
	private function getCode($url)
	{
		preg_match('/\/video\/([a-z0-9A-Z]*)/i', $url, $matches);

		if (!empty($matches)) {
			return $matches[1];
		}

		return false;
	}

	/**
     * Retrieves the embed widget for dailymotion.com videos
     *
     * @since   2.1
     * @access  public
     */
	public function getEmbedHTML($url)
	{
		$code = $this->getCode($url);

		if ($code) {
			$html = '<div class="es-video es-video--16by9"><iframe frameborder="0" width="720" height="405" src="http://www.dailymotion.com/embed/video/' . $code . '"></iframe></div>';

			return $html;
		}

		return false;
	}
}
