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

class SocialBBCodeYoutube
{
	private function getCode($url)
	{
		/* match http://www.youtube.com/watch?v=TB4loah_sXw&feature=fvst */
		preg_match('/youtube.com\/watch\?v=(.*)(?=&)/is', $url, $matches);
		
		if (!empty($matches)) {
			return $matches[1];
		}

		/* match http://www.youtube.com/watch?v=sr1eb3ngYko */
		preg_match('/youtube.com\/watch\?v=(.*)/is', $url, $matches);
		
		if (!empty($matches)) {
			return $matches[1];
		}

		preg_match('/youtu.be\/(.*)/is', $url, $matches);

		if (!empty($matches)) {
			return $matches[1];
		}

		return false;
	}

	/**
	 * Retrieves the embed widget for youtube.com videos
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getEmbedHTML($url)
	{
		$code = $this->getCode($url);

		if ($code) {
			return '<div class="es-video es-video--16by9"><iframe title="YouTube video player" width="720" height="405" src="https://www.youtube.com/embed/' . $code . '?wmode=transparent" frameborder="0" allowfullscreen></iframe></div>';
		} else {
			// this video do not have a code. so include the url directly.
			return '<div class="es-video es-video--16-9"><iframe title="YouTube video player" width="720" height="405" src="' . $url . '&wmode=transparent" frameborder="0" allowfullscreen></iframe></div>';
		}
		
		return false;
	}
}
